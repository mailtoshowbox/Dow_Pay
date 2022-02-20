<?php



namespace App\Http\Controllers;



use App\Mail\Notification;

use App\Member;

use App\Product;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Mail;

use Intervention\Image\Facades\Image;

use Spatie\ImageOptimizer\OptimizerChainFactory;



class Admin_member extends Controller

{

    // This controller is for admin to handle Member related tasks



    public function search_members(Request $e)

    {

        $members = Member::select('id', 'sponsor', 'name', 'email', 'my_topup', 'created_at', 'status');

        if (!blank($e->fromdt)) {

            $members = $members->where('created_at', '>=', $e->fromdt);

        }

        if (!blank($e->todt)) {

            $members = $members->where('created_at', '<=', $e->todt);

        }

        if (!blank($e->userid)) {

            $members = $members->where('id', id_filter($e->userid));

        }

        if (!blank($e->status)) {

            if ($e->status == 'Green') {

                $members = $members->where('my_topup', '>', 0);

            }

            if ($e->status == 'Red') {

                $members = $members->where('my_topup', '<=', 0);

            }

        }

        if (!blank($e->text)) {

            $members = $members->where('name', 'like', '%' . $e->text . '%')

                               ->orWhere('id', 'like', '%' . id_filter($e->text) . '%')

                               ->orWhere('phone', 'like', '%' . $e->text . '%')

                               ->orWhere('email', 'like', '%' . $e->text . '%');

        }

        $members = $members->orderBy('serial', 'DESC')->paginate(20)->appends(request()->query());



        $data = array(

            'title'   => 'Search Results',

            'members' => $members,

        );

        return view('admin.member.members', $data);

    }



    public function members($type = null)

    {

        if ($type === 'block') {

            $title   = 'Suspended Member\'s List';

            $members = Member::select('id', 'sponsor', 'name', 'email', 'created_at', 'my_topup', 'status')

                             ->where('status', 'Suspended')->orderBy('serial', 'DESC')->paginate(30);

        } else {

            $title   = 'All Members List';

            $members = Member::select('id', 'sponsor', 'name', 'email', 'created_at', 'my_topup', 'status')

                             ->where('status', '!=', 'Suspended')->orderBy('serial', 'DESC')->paginate(30);

        }

        $data = array(

            'title'   => $title,

            'members' => $members,

        );

        return view('admin.member.members', $data);

    }



    public function topup_members()

    {

        $title   = 'Topup Members';

        $members = Member::select('id', 'sponsor', 'name', 'email', 'created_at', 'status')->where('my_topup', '<=', 0)

                         ->orWhere('my_topup', null)->orderBy('serial', 'DESC')->paginate(30);



        $data = array(

          //RICK   run_queue();   => $title,

            'members' => $members,

        );

        return view('admin.member.topup_members', $data);

    }



    public function do_topup(Request $e)

    {

        if (blank($e->id)) {

            echo msg('User ID is required', 'danger');

            return;

        }

        $product_detail = cache()->remember('product_' . $e->package, 3600, function () use ($e) {

            return DB::table('products')->select('id', 'name', 'amount', 'bv', 'gst', 'capping', 'roi_amount')->where('id', $e->package)

                     ->first();

        });



        $get_last_topup = select('my_topup', 'members', ['id' => id_filter($e->id)]);

        if ($get_last_topup == $product_detail->amount) {

            echo msg('This UserID has been topped up already with .' . env('CURRENCY_SIGN') . $product_detail->amount, 'danger');

            return;

        }

        DB::transaction(function () use ($product_detail, $e) {

            $member              = Member::find(id_filter($e->id));

            $sponsor             = $member->sponsor;

            $position            = $member->position;

            $member->my_topup    = $product_detail->amount;

            $member->reg_product = $product_detail->id;

            $member->topup_date  = date('Y-m-d');

            $member->capping     = $product_detail->capping;

            $member->save();

            $e->session()->put('my_topup', $product_detail->amount);

            DB::table('user_relations')->where('id', id_filter($e->id))->update(array(

                                                                                    'status' => 1,

                                                                                    'my_bv'  => DB::raw('my_bv+' . $product_detail->bv),

                                                                                ));

            app('\App\Http\Controllers\Models')->product_income(id_filter($e->id), $e->package, $sponsor, $position);

            $status = 'Processing';

            if (env('AUTO_DELIVER_REG_PRODUCT') == true) {

                $status = 'Delivered';

            }

            $product = array(

                'product_id'    => $product_detail->id,

                'product_name'  => $product_detail->name,

                'product_price' => $product_detail->amount,

                'product_qty'   => 1,

                'product_tax'   => ($product_detail->amount * $product_detail->gst) / 100,

            );

            $saleid  = DB::table('sale_orders')->insertGetId(array(

                                                                 'userid'       => id_filter($e->id),

                                                                 'products'     => serialize($product),

                                                                 'total_amount' => $product_detail->amount,

                                                                 'status'       => $status,

                                                                 'created_at'   => date('Y-m-d H:i:s'),

                                                             ));



            if (env('AUTO_DELIVER_REG_PRODUCT') == true) {

                if ($product_detail->roi_amount > 0) {

                    $inserts = array(

                        'product_id' => $product_detail->id,

                        'userid'     => id_filter($e->id),

                        'qty'        => 1,

                        'sale_id'    => $saleid,

                        'created_at' => date('Y-m-d'),

                    );

                    DB::table('product_rois')->insert($inserts);

                }

            }

            if (session('xpackage') > 0) {

                $product = Product::find($product_detail->id);

                --$product->available_qty;

                ++$product->sold_qty;

                $product->save();

            }

        });

        app('\App\Http\Controllers\Models')->level_update(id_filter($e->id));

        echo msg('Topup Done Successfully !');

        run_queue();

        return;



    }



    public function power_legs()

    {

        $members = Member::select('id', 'sponsor', 'name', 'phone', 'power_leg', 'power_side', 'binary_value')

                         ->where('power_leg', '>', 0)->orderBy('serial', 'DESC')->paginate(30);

        $data    = array(

            'title'   => 'List of Powered Legs',

            'members' => $members,

        );

        return view('admin.member.powerlegs', $data);

    }



    public function view_member($id)

    {

        $data = array(

            'title'    => 'Member Detail',

            'members'  => Member::find($id),

            'leg_info' => DB::table('leg_info')->where('id', $id)->first(),

            'profile'  => DB::table('member_profile')->where('id', $id)->first(),

            'wallet'   => DB::table('wallet')->where('id', $id)->get(),

        );

        return view('admin.member.member_detail', $data);

    }



    public function edit_member($id)

    {

        $data = array(

            'title'   => 'Edit Member',

            'mdetail' => Member::find($id),

            'profile' => DB::table('member_profile')->where('id', $id)->first(),

        );

        return view('admin.member.edit_member', $data);

    }



    public function update_members(Request $e)

    {

        $e->validate(array(

                         'name'          => 'required',

                         'email'         => 'email|nullable',

                         'phone'         => 'numeric|nullable',

                         'avatar'        => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',

                         'id_proof'      => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',

                         'address_proof' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',

                     ),

                     [

                         'avatar.max'        => 'You can upload maximum 2MB at profile picture',

                         'id_proof.max'      => 'You can upload maximum 2MB at ID Proof',

                         'address_proof.max' => 'You can upload maximum 2MB at Address Proof',

                     ]);

        $members = Member::find(id_filter($e->id));



        if ($members->email !== $e->email) {

            $get_email = count_all('members', array('email' => $e->email));

            if ($get_email > 0) {

                return redirect()->back()->with('msg', msg('Email ID is already in use.', 'danger'));

            }

        }



        $members  = Member::find(id_filter($e->id));

        $password = password_hash($e->password, 1);

        if (blank($e->password)) {

            $password = $members->password;

        }

        $avatar         = $members->avatar;

        $id_proof       = $members->id_proof;

        $address_proof  = $members->address_proof;

        $optimizerChain = OptimizerChainFactory::create();

        if ($e->avatar):

            $avatar = uniqid('', true) . session('member_id') . '.' . $e->avatar->extension();

            $e->avatar->move(storage_path('pics/'), $avatar);

            $logo = app(Image::class)::make(storage_path('pics/' . $avatar))->resize(400, null, function ($constraint) {

                $constraint->aspectRatio();

            });

            $logo->save(storage_path('pics/' . $avatar));

            $optimizerChain->optimize(storage_path('pics/' . $avatar));

            if (!blank($members->avatar)) {

                unlink(storage_path('pics/' . $members->avatar));

            }

        endif;

        if ($e->id_proof):

            $id_proof = uniqid('', true) . session('member_id') . '.' . $e->id_proof->extension();

            $e->id_proof->move(storage_path('pics/'), $id_proof);

            $logo = app(Image::class)::make(storage_path('pics/' . $id_proof))

                                     ->resize(1200, null, function ($constraint) {

                                         $constraint->aspectRatio();

                                     });

            $logo->save(storage_path('pics/' . $id_proof));

            $optimizerChain->optimize(storage_path('pics/' . $id_proof));

            if (!blank($members->id_proof)) {

                unlink(storage_path('pics/' . $members->id_proof));

            }

        endif;

        if ($e->address_proof):

            $address_proof = uniqid('', true) . session('member_id') . '.' . $e->address_proof->extension();

            $e->address_proof->move(storage_path('pics/'), $address_proof);

            $logo = app(Image::class)::make(storage_path('pics/' . $address_proof))

                                     ->resize(1200, null, function ($constraint) {

                                         $constraint->aspectRatio();

                                     });

            $logo->save(storage_path('pics/' . $address_proof));

            $optimizerChain->optimize(storage_path('pics/' . $address_proof));

            if (!blank($members->address_proof)) {

                unlink(storage_path('pics/' . $members->address_proof));

            }

        endif;

        $members->name          = $e->name;

        $members->phone         = $e->phone;

        $members->email         = $e->email;

        $members->status        = $e->status;

        $members->avatar        = $avatar;

        $members->password      = $password;

        $members->topup_date    = $e->topup_date;

        $members->created_at    = $e->created_at;

        $members->id_proof      = $id_proof;

        $members->address_proof = $address_proof;

        $members->save();



        $array = array(

            'address'         => $e->address,

            'city'            => $e->city,

            'pin'             => $e->pin,

            'nominee_name'    => $e->nominee_name,

            'nominee_address' => $e->nominee_address,

            'pan_card'        => $e->pan_card,

            'aadhar'          => $e->aadhar,

            'created_at'      => $e->created_at,

            'bank_ac_holder'  => $e->bank_ac_holder,

            'bank_name'       => $e->bank_name,

            'bank_ac_no'      => $e->bank_ac_no,

            'bank_ifsc'       => $e->bank_ifsc,

            'bank_branch'     => $e->bank_branch,

            'btc_address'     => $e->btc_address,

            'upi_address'     => $e->upi_address,

        );

        DB::table('member_profile')->where('id', $e->id)->update($array);

        return redirect(url('/members'))->with('msg', msg('Profile updated successfully.'));

    }



    public function delete_member($id)

    {

        if ($id === env('TOP_ID')) {

            echo errorrecord('You cannot delete Company ID.');

        } else {

            $data = array(

                'title' => 'Delete Member',

                'id'    => $id,

            );

            return view('admin.member.delete_member', $data);

        }

    }



    public function delete_member_final(Request $e)

    {

        if ($e->id === env('TOP_ID')) {

            echo errorrecord('You cannot delete Company ID.');

        } else {

            $member = Member::find(id_filter($e->id));

            if ($e->type === 'Member'):

                $find_downline = count_all('members', array('position' => id_filter($e->id)));

                if ($find_downline > 0) {

                    echo errorrecord('You cannot delete this user as this user has some downlines. If you still want to delete the user, you must delete the complete tree. To do so, close this window and reopen and click on "Delete Member and Complete Tree"');

                    return;

                }

          //RICK   run_queue(); table('members')->where('id', $member->position)->update([$member->leg => 0]);

                ### Create a relational database structure here

                if ($member->avatar !== null) {

                    unlink(asset('storage/pics/' . $member->avatar));

                }

                if ($member->id_proof !== null) {

                    unlink(asset('storage/pics/' . $member->id_proof));

                }

                if ($member->address_proof !== null) {

                    unlink(asset('storage/pics/' . $member->address_proof));

                }

                $member->history()->forceDelete();

                DB::table('upgrade_plans')->where('id', id_filter($e->id))->delete();

                DB::table('investments')->where('investor_id', id_filter($e->id))->delete();

                DB::table('donation_rois')->where('user_id', id_filter($e->id))->delete();

                DB::table('my_emis')->where('user_id', id_filter($e->id))->delete();

                DB::table('donations')->where('sender_id', id_filter($e->id))->orWhere('receiver_id', $x->id)->delete();

                echo success('User has been deleted');

                return;

            else:

                $all_members = get_downline_list_all(id_filter($e->id));

                foreach ($all_members as $x) {

                    $member_downline = Member::find($x->id);

                    DB::table('upgrade_plans')->where('id', $x->id)->delete();

                    DB::table('investments')->where('investor_id', $x->id)->delete();

                    DB::table('donation_rois')->where('user_id', $x->id)->delete();

                    DB::table('my_emis')->where('user_id', $x->id)->delete();

                    DB::table('donations')->where('sender_id', $x->id)->orWhere('receiver_id', $x->id)->delete();

                    if ($member_downline->avatar !== null) {

                        unlink(asset('storage/pics/' . $member_downline->avatar));

                    }

                    if ($member_downline->id_proof !== null) {

                        unlink(asset('storage/pics/' . $member_downline->id_proof));

                    }

                    if ($member_downline->address_proof !== null) {

                        unlink(asset('storage/pics/' . $member_downline->address_proof));

                    }

                    $member_downline->forceDelete();

                }

                DB::table('members')->where('id', $member->position)->update([$member->leg => 0]);

                ### Create a relational database structure here

                if ($member->avatar !== null) {

                    unlink(asset('storage/pics/' . $member->avatar));

                }

                if ($member->id_proof !== null) {

                    unlink(asset('storage/pics/' . $member->id_proof));

                }

                if ($member->address_proof !== null) {

                    unlink(asset('storage/pics/' . $member->address_proof));

                }

                $member->forceDelete();

                DB::table('upgrade_plans')->where('id', id_filter($e->id))->delete();

                DB::table('investments')->where('investor_id', id_filter($e->id))->delete();

                DB::table('donation_rois')->where('user_id', id_filter($e->id))->delete();

                DB::table('my_emis')->where('user_id', id_filter($e->id))->delete();

                DB::table('donations')->where('sender_id', id_filter($e->id))->orWhere('receiver_id', $x->id)->delete();

                echo success('User and related downlines has been deleted');

                return;

            endif;



        }



    }





    public function save_powerleg(Request $e)

    {

        $member               = Member::find($e->userid);

        $member->power_side   = $e->leg;

        $member->power_leg    = $e->leg_number ? $e->leg_number : 0;

        $member->binary_value = $e->binary_value ? $e->binary_value : 0;

        $member->save();

        echo 'done';

    }



    public function edit_powerleg(Request $e)

    {

        $member               = Member::find(id_filter($e->userid));

        $member->power_leg    = $e->leg_number ? $e->leg_number : 0;

        $member->binary_value = $e->binary_value ? $e->binary_value : 0;

        $member->save();

        echo 'done';

    }



    public function delete_power($id)

    {

        $member             = Member::find($id);

        $member->power_side = null;

        $member->power_leg  = null;

        $member->save();

        return redirect('/power-leg')->with('msg', msg('Power Leg has been deleted.'));

    }



    public function create_member(Request $e)

    {

        $e->validate([

                         'name'     => 'required',

                         'email'    => 'nullable|email',

                         'phone'    => 'nullable|integer|min:7',

                         'username' => 'nullable|min:5|unique:members',

                         'userid'   => 'nullable|unique:members,id',

                         'password' => 'required|min:6|max:100',

                     ],

                     [

                         'phone.integer' => 'Enter Correct phone no without prefixing 0 or international code.',

                     ]);

        ##### Assign default Data



        $sponsor_id  = id_filter($e->sponsor);

        $position_id = id_filter($e->position);

        $leg         = $e->leg ?: 'A';



        if (blank($sponsor_id)) {

            $e->sponsor = $sponsor_id = env('TOP_ID');

        }



        if (blank($position_id) || $sponsor_id == $position_id) {

            $position_id = get_extreme_leg($sponsor_id, $leg);

        }

        if (env('SHOW_REG_PRODUCT') == true) {

            $product = Product::select('id', 'name', 'amount', 'available_qty', 'sold_qty', 'bv', 'capping')

                              ->where('id', $e->package)->first();

        }

        #### Critical Validations

        if (count_all('members', array('id' => $sponsor_id)) <= 0)

            return redirect('/new-member')->withInput()

                                          ->with('msg', '<div class="alert alert-danger"><i class="icon-close"></i> Wrong Sponsor ID entered</div>');

        if (strlen(trim(id_filter($e->position))) > 1)

            if (count_all('members', array('id' => id_filter($e->position))) <= 0)

                return redirect('/new-member')->withInput()

                                              ->with('msg', '<div class="alert alert-danger"><i class="icon-close"></i> Wrong Placement ID entered</div>');

        if (!blank($e->position)) {

            if (select($leg, 'members', array(

                    'id' => id_filter($e->position),

                )) !== 0) {



                return redirect('/register')->withInput()

                                            ->with('msg', '<div class="alert alert-danger"><i class="icon-close"></i> Placement ID you have chosen has already been filled by another member.</div>');

            }

        }

        #### End Validations



        #### Generate Tree Structure ########

        if (env('AUTOPOOL') === true) {

            if (env('MEMBER_AUTOPOOL') == true)

                $data = find_autopool(id_filter($e->sponsor));

            else

                $data = find_autopool();

            $position_id = $data['position'];

            $leg         = $data['leg'];

        }

        if (!blank($e->userid)) {

            $user_id = filter_var($e->userid, FILTER_SANITIZE_NUMBER_INT);

        } else {

            $user_id = $this->generateid(random_int(100000, 999999)); ## This should be minimum 6, else it will conflict with dealer ID system.

        }

        $username = $e->username;

        if (trim($e->username) === '') {

            $username = $user_id;

        }

        if (env('LEG_NUMBER') == 1 && env('AUTOPOOL') !== true) {

            $position_id = $sponsor_id;

        }

        $array = array(

            'xid'       => $user_id,

            'xsponsor'  => $sponsor_id,

            'xposition' => $position_id,

            'xname'     => $e->name,

            'xaddress'  => $e->address,

            'xdate'     => $e->date,

            'xcountry'  => $e->country,

            'xstate'    => $e->state,

            'xcity'     => $e->city,

            'xpin'      => $e->pin,

            'xepin'     => $e->epin,

            'xusername' => $username,

            'xpassword' => $e->password,

            'xemail'    => $e->email,

            'xphone'    => $e->phone,

            'xleg'      => $leg,

            'xpackage'  => $e->package,

            'xpackamt'  => $product->amount,

            'xpackname' => $product->name,

            'xbv'       => $product->bv,

            'xcapping'  => $product->capping,

        );

        session($array);

        $e->session()->put('completed', true);

        DB::transaction(function () {

            $leg              = session('xleg');

            $member           = new Member();

            $member->id       = session('xid');

            $member->sponsor  = session('xsponsor');

            $member->position = session('xposition');

            $member->name     = session('xname');

            $member->username = session('xusername');

            $member->password = password_hash(session('xpassword'), 1);

            $member->email    = session('xemail');

            $member->phone    = session('xphone');

            if (env('REG_FEE') !== 'Free'):

                $member->my_topup = session('xpackamt');

            endif;

            $member->leg        = $leg;

            $member->created_at = session('xdate');

            $member->save();

            DB::table('user_relations')->where('id', session('xid'))->update(['my_bv' => session('xbv')]);

            ### Build Tree

            $member = Member::find(session('xposition'));

            if (env('LEG_NUMBER') == 1 && env('AUTOPOOL') !== true) {

            } else {

                $member->$leg = session('xid');

            }

            $member->capping = (!blank(session('xcapping'))) ? session('xcapping') : 0;

            $member->save();



            ### Update EPIN status





            ### Insert Important Records

            DB::table('member_profile')->insert(array(

                                                    'id'      => session('xid'),

                                                    'address' => session('xaddress'),

                                                    'country' => session('xcountry'),

                                                    'state'   => session('xstate'),

                                                    'city'    => session('xcity'),

                                                    'pin'     => session('xpin'),

                                                ));

            $status = 0;

            if (env('REG_FEE') === 'Paid') {

                $status = 1;

            }

            DB::table('user_relations')->insert(array(

                                                    'id'         => session('xid'),

                                                    'parent_id'  => session('xposition'),

                                                    'status'     => $status,

                                                    'created_at' => date('Y-m-d H:i:s'),

                                                ));

            DB::table('leg_info')->insert(array(

                                              'id'        => session('xid'),

                                              'parent_id' => session('xposition'),

                                          ));



            DB::table('levels')->insert(array(

                                            'id' => session('xid'),

                                        ));



            foreach (config('config.wallet_types') as $wallet):

                $array[] = array(

                    'id'   => session('xid'),

                    'type' => $wallet,

                );

            endforeach;

            DB::table('wallet')->insert($array);



            if (env('SHOW_REG_PRODUCT') == true):

                $status = 'Processing';

                if (env('AUTO_DELIVER_REG_PRODUCT') == true) {

                    $status = 'Delivered';

                }



                $product = array(

                    'product_id'    => session('xpackage'),

                    'product_name'  => session('xpackname'),

                    'product_price' => session('xpackamt'),

                    'product_qty'   => 1,

                );

                $saleid  = DB::table('sale_orders')->insert(array(

                                                                'userid'       => session('xid'),

                                                                'products'     => serialize($product),

                                                                'total_amount' => session('xpackamt'),

                                                                'status'       => $status,

                                                                'created_at'   => date('Y-m-d H:i:s'),

                                                            ));

             /*    $rank = "RICK";;   if (env('AUTO_DELIVER_REG_PRODUCT') == true) {

                    if (cache_select('roi_amount', 'products', ['id' => session('xpackage')]) > 0) {

                        $inserts = array(

                            'product_id' => session('xpackage'),

                            'userid'     => session('xid'),

                            'qty'        => 1,

                            'sale_id'    => $saleid,

                            'created_at' => date('Y-m-d'),

                        );

                        DB::table('product_rois')->insert($inserts);

                    }

                }
 */


                if (session('xpackage') > 0) {

                    $product = Product::find(session('xpackage'));

                    --$product->available_qty;

                    ++$product->sold_qty;

                    $product->save();

                }

            endif;

            #### Generate Income ############

            app('\App\Http\Controllers\Models')->level_update(id_filter(session('xid')));

            app('\App\Http\Controllers\Models')->product_income(session('xid'), session('xpackage'), session('xsponsor'), session('xposition'));

            #################################



            if (env('ENABLE_DONATION_PLAN') === true)

                app('\App\Http\Controllers\Donation')->generate_donation(session('xid'), 1);

        });

        #### Send SMS/Email ############

        if (env('SMS_ENABLE') == true && session('xphone') > 1000)

            sms(session('xphone'), 'Hi ' . session('xname') . ', Welcome to ' . env('APP_NAME') . '. Your User ID is: ' . session('xid') . ' and Password is: ' . session('xpassword'));

        if (trim(session('xemail')) !== ''):

            $msg   = "Hi " . session('xname') . ", Welcome to " . env('APP_NAME') . " Family. We are excited to have you as our valuable member. <p>Now you may login your account at " . env('APP_URL') . " or clicking on the below button.</p> Your Your <strong>User ID is: " . session('xid') . "</strong> <br/> <strong>Password</strong> is: " . session('xpassword');

            $order = array(

                'title'    => 'Registration Completed',

                'subject'  => 'Welcome to ' . env('APP_NAME'),

                'msg'      => $msg,

                'url'      => url('/member'),

                'urltitle' => 'Login Member Panel',

            );

            if (env('EMAIL_ENABLE') == true) {

                if (env('QUEUE_ENABLE') == true):

                    Mail::to(session('xemail'))->queue(new Notification($order));

                else:

                    Mail::to(session('xemail'))->send(new Notification($order));

                endif;

            }

        endif;

        run_queue();

        return redirect('/new-member')->with('msg', msg('New Member has been created. ID is: ' . $user_id));

    }



    private function generateid($id)

    {

        $count = Member::where('id', $id)->count('id');

        if ($count > 0) {

            return $this->generateid($id + 1);

        }

        return $id;

    }

}

