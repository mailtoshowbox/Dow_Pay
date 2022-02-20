<?php



namespace App\Http\Controllers;



use App\Mail\Forgetpw;

use App\Mail\Notification;

use App\Member;

use App\Product;

use App\Staff;

use DateTime;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Cookie;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Mail;



class SiteController extends Controller

{

    public function __construct()

    {



        $this->middleware(function ($request, $next) {

            $pages_top    = cache()->rememberForever('pages_top', function () {

                return DB::table('pages')->select('id', 'title')

                    ->where('status', 'Published')

                    ->where('location', 'Top')

                    ->where('is_home', '0')

                    ->orderBy('sort', 'ASC')

                    ->get();
            });

            $pages_bottom = cache()->rememberForever('pages_bottom', function () {

                return DB::table('pages')->select('id', 'title')

                    ->where('status', 'Published')

                    ->where('is_home', '0')

                    ->where('location', 'Bottom')

                    ->orderBy('sort', 'ASC')

                    ->get();
            });



            \request()->session()->put(array(

                'pages_top'    => $pages_top,

                'pages_bottom' => $pages_bottom,

            ));


            return $next($request);
        });
    }



    public function index()

    {

        if (env('ENABLE_CMS') !== true) {

            return redirect('../');
        }

        $data = array(

            'title'          => 'Welcome to ' . env('APP_NAME'),

            'home_page'      => cache()->rememberForever('home_page', function () {
                return select('content', 'pages', array('is_home' => 1));
            }),

            'news'           => cache()->rememberForever('news', function () {

                return DB::table('news')->select('id', 'title', 'body', 'date')->orderBy('id', 'DESC')->limit(15)

                    ->get();
            }),

            'top_sliders'    => cache()->rememberForever('top_sliders', function () {

                return DB::table('sliders')->select('id', 'image', 'url')->where('status', 'Published')

                    ->where('placement', 'Top')->get();
            }),

            'right_sliders'  => cache()->rememberForever('right_sliders', function () {

                return DB::table('sliders')->select('id', 'image', 'url')->where('status', 'Published')

                    ->where('placement', 'Right')->get();
            }),

            'middle_sliders' => cache()->rememberForever('middle_sliders', function () {

                return DB::table('sliders')->select('id', 'image', 'url')->where('status', 'Published')

                    ->where('placement', 'Middle')->get();
            }),

            'bottom_sliders' => cache()->rememberForever('bottom_sliders', function () {

                return DB::table('sliders')->select('id', 'image', 'url')->where('status', 'Published')

                    ->where('placement', 'Bottom')->get();
            }),

            'widgets'        => cache()->rememberForever('all_widgets', function () {

                return DB::table('widgets')->select('id', 'title', 'body')->orderBy('sort', 'ASC')->get();
            }),

        );

        return view('site.home', $data);
    }



    public function send_otp($id)

    {

        $otp = random_int(10000, 99999);

        \request()->session()->put('otp_login', $otp);

        $get_member_data = Member::select('name', 'phone', 'email')->where('email', trim($id))

            ->orWhere('id', $this->$this->id_filter($id))->orWhere('username', $this->$this->id_filter($id))

            ->orWhere('phone', $this->$this->id_filter($id))->first();

        if (env('SMS_ENABLE') == true && $get_member_data->phone > 1000)

            sms($get_member_data->phone, 'Hi ' . $get_member_data->name . ', Your Login OTP is: ' . $otp . '. Login here: www.' . $_SERVER['HTTP_HOST']);

        if (trim(session('xemail')) !== '') :

            $msg   = 'Hi ' . $get_member_data->name . ', Your Login OTP is: ' . $otp . '. Login here: www.' . $_SERVER['HTTP_HOST'];

            $order = array(

                'title'    => 'OTP Login',

                'subject'  => 'OTP to login ' . env('APP_NAME'),

                'msg'      => $msg,

                'url'      => '',

                'urltitle' => '',

            );

            if (env('EMAIL_ENABLE') == true) {

                if (env('QUEUE_ENABLE') == true) :

                    Mail::to(session('xemail'))->queue(new Notification($order));

                else :

                    Mail::to(session('xemail'))->send(new Notification($order));

                endif;
            }

        endif;

        echo 'OTP sent successfully to email and Phone.';
    }



    public function page($id)

    {

        $page = cache()->rememberForever('page_' . $id, function () use ($id) {

            return DB::table('pages')->select('title', 'content', 'featured_image', 'url')->where('id', $id)

                ->where('is_home', 0)->first();
        });

        if (!blank($page->url)) {

            return redirect($page->url);
        }

        $data = array(

            'title' => $page->title,

            'page'  => $page,

        );

        return view('site.page', $data);
    }



    //#---------------------------------------- Member Registration Part------------------------------------



    public function ecom_register(Request $e)

    {

        $e->validate(
            [

                'name'  => 'required',

                'email' => 'nullable|email|unique:members',

                'phone' => 'nullable|integer|min:7|unique:members',

            ],

            [

                'phone.integer' => 'Enter Correct phone no without prefixing 0 or international code.',

            ]
        );



        session()->put(array(

            'xsponsor' => $e->sponsor ?: env('TOP_ID'),

            'xname'    => $e->name,

            'xphone'   => $e->phone,

            'xemail'   => $e->email,

        ));

        return redirect(url('/ecom-register-final'));
    }



    public function ecom_register_final()

    {

        if (env('OTP_VERIFICATION') == true && blank(session('otp_submit'))) {

            $otp = random_int(10000, 99999);

            session()->put(['motp' => $otp]);

            sms(session('xphone'), 'Hi ' . session('xname') . ', Your OTP to verify your mobile no is: ' . $otp . '. Enter here: www.' . $_SERVER['HTTP_HOST']);

            $data = array(

                'title' => 'Verify your Mobile OTP',

            );

            return view('ecommerce.pages.otp_verification', $data);
        }

        $user_id  = $this->generateid(random_int(100000, 999999));  ## This should be minimum 6, else it will conflict with dealer ID system.

        $password = substr(uniqid(), 0, 6);

        DB::transaction(function () use ($user_id, $password) {

            $member           = new Member();

            $member->id       = $user_id;

            $member->username = $user_id;

            $member->name     = session('xname');

            $member->sponsor  = session('xsponsor');

            $member->email    = session('xemail');

            $member->phone    = session('xphone');

            $member->password = password_hash($password, 1);

            $member->save();

            DB::table('levels')->insert(array(

                'id' => $user_id,

            ));

            DB::table('member_profile')->insert(array(

                'id' => $user_id,

            ));

            foreach (config('config.wallet_types') as $wallet) :

                $array[] = array(

                    'id'   => $user_id,

                    'type' => $wallet,

                );

            endforeach;

            DB::table('wallet')->insert($array);

            /// DB::table('wallet')->where('id', $user_id)->where('type', 'Voucher')->update(array('balance' => 99));

            /// Incase you want to give default balance to a wallet on signup.

        });

        if (env('SMS_ENABLE') == true && session('xphone') > 1000)

            sms(session('xphone'), 'Hi ' . session('xname') . ', Welcome to ' . env('APP_NAME') . '. Your User ID is: ' . env('ID_EXT') . $user_id . ' and Password is: ' . $password . '. Login here: www.' . $_SERVER['HTTP_HOST']);

        if (trim(session('xemail')) !== '') :

            $msg   = "Hi " . session('xname') . ", Welcome to " . env('APP_NAME') . " Family. We are excited to have you as our valuable member. <p>Now you may login your account at " . $_SERVER['HTTP_HOST'] . " or clicking on the below button.</p> Your Your <strong>User ID is: " . env('ID_EXT') . $user_id . "</strong> <br/> <strong>Password</strong> is: " . $password;

            $order = array(

                'title'    => 'Registration Completed',

                'subject'  => 'Welcome to ' . env('APP_NAME'),

                'msg'      => $msg,

                'url'      => url('/member'),

                'urltitle' => 'Login Member Panel',

            );

            if (env('EMAIL_ENABLE') == true) {

                if (env('QUEUE_ENABLE') == true) :

                    Mail::to(session('xemail'))->queue(new Notification($order));

                else :

                    Mail::to(session('xemail'))->send(new Notification($order));

                endif;
            }

        endif;

        session()->flush();

        return $this->generate_member_session($user_id);
    }



    public function upgrade_member(Request $e)

    {

        /*************************************************************************************************************************

         *

         *  When member will upgrade their account from ecommerce to MLM, this part will work.

         *

         *************************************************************************************************************************/

        $position_id = $this->$this->id_filter($e->position);

        $leg         = $e->leg ?: 'A';

        if (blank($e->sponsor)) {

            $e->sponsor = $sponsor_id = env('TOP_ID');
        }

        $sponsor_id = $this->$this->id_filter($e->sponsor);

        if (blank($position_id) || $sponsor_id == $position_id) {

            $position_id = get_extreme_leg($sponsor_id, $leg);
        }

        if (env('SHOW_REG_PRODUCT') == true)

            $product = Product::select('id', 'name', 'min_payable_amount', 'emi_amount', 'no_of_emi', 'available_qty', 'sold_qty', 'bv', 'gst', 'capping')

                ->where('id', $e->package)->first();



        if (env('REG_FEE') !== 'Free' && (env('FEE_TYPE') === 'Both' || env('FEE_TYPE') === 'PG')) {

            if (strlen(trim($e->epin)) <= 1) {

                $pg = true;
            }
        }

        ##### End Assignments



        #### Critical Validations

        if (count_all('members', array('id' => $sponsor_id)) <= 0)

            return redirect('/upgrade-network')->withInput()

                ->with('msg', '<div class="alert alert-danger"><i class="icon-close"></i> Wrong Sponsor ID entered</div>');

        if (strlen(trim($this->$this->id_filter($e->position))) > 1)

            if (count_all('members', array('id' => $this->$this->id_filter($e->position))) <= 0)

                return redirect('/upgrade-network')->withInput()

                    ->with('msg', '<div class="alert alert-danger"><i class="icon-close"></i> Wrong Placement ID entered</div>');

        if (!blank($e->position) && $position_id !== $sponsor_id) {

            if (select($leg, 'members', array(

                'id' => $this->$this->id_filter($e->position),

            )) !== 0) {



                return redirect('/upgrade-network')->withInput()

                    ->with('msg', '<div class="alert alert-danger"><i class="icon-close"></i> Placement ID you have chosen has already been filled by another member.</div>');
            }
        }





        #### End Validations



        #### Generate Tree Structure ########

        if (env('AUTOPOOL') === true) {

            if (env('MEMBER_AUTOPOOL') == true)

                $data = find_autopool($this->$this->id_filter($e->sponsor));

            else

                $data = find_autopool();

            $position_id = $data['position'];

            $leg         = $data['leg'];
        }

        $user_id  = session('member_id');  ## This should be minimum 6, else it will conflict with dealer ID system.

        $username = $e->username;

        if (blank($e->username)) {

            $username = $user_id;
        }

        if (env('LEG_NUMBER') == 1 && env('AUTOPOOL') !== true) {

            $position_id = $sponsor_id;
        }

        DB::transaction(function () use ($e, $sponsor_id, $position_id, $leg) {

            $member           = Member::find(session('member_id'));

            $member->sponsor  = $sponsor_id;

            $member->position = $position_id;

            $member->leg      = $leg;

            $member->save();

            ### Build Tree

            $member = Member::find($position_id);



            if (env('LEG_NUMBER') == 1 && env('AUTOPOOL') !== true) {
            } else {

                $member->$leg = session('member_id');
            }

            $member->save();





            ### Insert Important Records

            DB::table('user_relations')->insert(array(

                'id'         => session('member_id'),

                'parent_id'  => $position_id,

                'status'     => 1,

                'created_at' => date('Y-m-d H:i:s'),

            ));

            DB::table('leg_info')->insert(array(

                'id'        => session('member_id'),

                'parent_id' => $position_id,

            ));
        });

        return redirect('/my-account-dashboard')->with('msg', $this->msg('Your Account has been upgraded to the Network. Congratulation !'));
    }



    public function create_member(Request $e)

    {

        $e->validate(
            [

                'name'            => 'required',

                'email'           => 'nullable|email',

                'phone'           => 'nullable|integer|min:7',

                'username'        => 'nullable|min:5|unique:members',

                'password'        => 'required|min:6|max:100',

                'retype_password' => 'required|same:password',

            ],

            [

                'phone.integer' => 'Enter Correct phone no without prefixing 0 or international code.',

            ]
        );

        ##### Assign default Data



        $position_id = $this->$this->id_filter($e->position);

        $leg         = $e->leg ?: 'A';

        if (blank($e->sponsor)) {

            $e->sponsor = $sponsor_id = env('TOP_ID');
        }

        $sponsor_id = $this->$this->id_filter($e->sponsor);

        if (blank($position_id) || $sponsor_id == $position_id) {

            $position_id = get_extreme_leg($sponsor_id, $leg);
        }

        if (env('SHOW_REG_PRODUCT') == true)

            $product = Product::select('id', 'name', 'min_payable_amount', 'emi_amount', 'no_of_emi', 'available_qty', 'sold_qty', 'bv', 'gst', 'capping')

                ->where('id', $e->package)->first();



        if (env('REG_FEE') !== 'Free' && (env('FEE_TYPE') === 'Both' || env('FEE_TYPE') === 'PG')) {

            if (strlen(trim($e->epin)) <= 1) {

                $pg = true;
            }
        }

        ##### End Assignments



        #### Critical Validations

        if (count_all('members', array('id' => $sponsor_id)) <= 0)

            return redirect('/register')->withInput()

                ->with('msg', '<div class="alert alert-danger"><i class="icon-close"></i> Wrong Sponsor ID entered</div>');

        if (strlen(trim($this->$this->id_filter($e->position))) > 1)

            if (count_all('members', array('id' => $this->$this->id_filter($e->position))) <= 0)

                return redirect('/register')->withInput()

                    ->with('msg', '<div class="alert alert-danger"><i class="icon-close"></i> Wrong Placement ID entered</div>');

        if (!blank($e->position) && $position_id !== $sponsor_id) {

            if (select($leg, 'members', array(

                'id' => $this->$this->id_filter($e->position),

            )) !== 0) {



                return redirect('/register')->withInput()

                    ->with('msg', '<div class="alert alert-danger"><i class="icon-close"></i> Placement ID you have chosen has already been filled by another member.</div>');
            }
        }

        if (strlen(trim($e->epin)) > 1) {

            $epin_value = DB::table('epins')->select('amount')

                ->where(array(

                    'epin'      => $e->epin,

                    'used_date' => null,

                ))

                ->first();

            if (empty($epin_value))

                return redirect('/register')->withInput()

                    ->with('msg', '<div class="alert alert-danger"><i class="icon-close"></i> Wrong E-PIN entered</div>');
        }

        if (env('SHOW_REG_PRODUCT') === true && (env('FEE_TYPE') === 'Epin' || strlen(trim($e->epin)) > 1) && env('REG_FEE') !== 'Free') {

            $payable_amt = $product->min_payable_amount + (($product->min_payable_amount * $product->gst) / 100);

            if ($payable_amt != $epin_value->amount)

                return redirect('/register')->withInput()

                    ->with('msg', '<div class="alert alert-danger"><i class="icon-close"></i> Epin amount must be same as package/product amount + tax(' . $product->gst . '%) = <strong>' . env('CURRENCY_SIGN') . ' ' . $payable_amt . '</strong>. Provided E-Pin value is: ' . $epin_value->amount . '</div>');

            if ($product->available_qty == 0)

                return redirect('/register')->withInput()

                    ->with('msg', '<div class="alert alert-danger"><i class="icon-close"></i> Not enough Product/Package to purchase.</div>');
        }



        if (env('SHOW_REG_PRODUCT') === true && env('FEE_TYPE') === 'Epin' && strlen(trim($e->epin)) <= 1 && env('REG_FEE') !== 'Free') {

            return redirect('/register')->withInput()

                ->with('msg', '<div class="alert alert-danger"><i class="icon-close"></i> You need to enter E-Pin to proceed with registration</div>');
        }



        #### End Validations



        #### Generate Tree Structure ########

        if (env('AUTOPOOL') === true) {

            if (env('MEMBER_AUTOPOOL') == true)

                $data = find_autopool($this->$this->id_filter($e->sponsor));

            else

                $data = find_autopool();

            $position_id = $data['position'];

            $leg         = $data['leg'];
        }

        $user_id  = $this->generateid(random_int(100000, 999999));  ## This should be minimum 6, else it will conflict with dealer ID system.

        $username = $e->username;

        if (trim($e->username) === '') {

            $username = $user_id;
        }

        if (env('LEG_NUMBER') == 1 && env('AUTOPOOL') !== true) {

            $position_id = $sponsor_id;
        }

        $array = array(

            'xid'         => $user_id,

            'xsponsor'    => $sponsor_id,

            'xposition'   => $position_id,

            'xname'       => $e->name,

            'xaddress'    => $e->address,

            'xcountry'    => $e->country,

            'xstate'      => $e->state,

            'xcity'       => $e->city,

            'xpin'        => $e->pin,

            'xepin'       => $e->epin,

            'xusername'   => $username,

            'xpassword'   => $e->password,

            'xemail'      => $e->email,

            'xphone'      => $e->phone,

            'xleg'        => $leg,

            'xpackage'    => $e->package,

            'xpackamt'    => $product->min_payable_amount,

            'xpackname'   => $product->name,

            'xbv'         => $product->bv,

            'xcapping'    => $product->capping,

            'xno_of_emi'  => $product->no_of_emi,

            'xemi_amount' => $product->emi_amount,

        );

        $e->session()->put($array);

        if (env('SHOW_REG_PRODUCT') === true && ((env('FEE_TYPE') === 'Both' || env('FEE_TYPE') === 'PG') && strlen(trim($e->epin)) <= 1) && env('REG_FEE') !== 'Free') {

            return redirect('/gateway-form-register');
        }

        $e->session()->put('completed', true);

        return redirect('/complete-registration');
    }



    private function generateid($id)

    {

        $count = Member::where('id', $id)->count('id');

        if ($count > 0) {

            return $this->generateid($id + 1);
        }

        return $id;
    }



    public function gateway_form_register()

    {

        return view('site.gateway_form_register', ['title' => 'Pay using payment gateway']);
    }



    public function otp_verification(Request $e)

    {

        $e->validate([

            'otp' => 'required|min:5|numeric',

        ]);

        if ((int)$e->otp == (int)session('motp')) {

            session()->put(['otp_submit' => true]);

            if (env('ECOM_FIRST_PORTAL') == true) :

                return $this->ecom_register_final();

            else :

                return $this->complete_registration();

            endif;
        }

        return redirect()->back()->with('msg', $this->msg('Invalid OTP entered. New OTP Generated and sent again.', 'danger'));
    }



    public function complete_registration($script = false)

    {

        if (session('xid') <= 0 || session('completed') !== true)

            return redirect('/register')->with('msg', $this->msg('Please register again', 'danger'));



        if (env('OTP_VERIFICATION') == true && blank(session('otp_submit'))) {

            $otp = random_int(10000, 99999);

            session()->put(['motp' => $otp]);

            sms(session('xphone'), 'Hi ' . session('xname') . ', Your OTP to verify your account is: ' . $otp . '. Enter here: www.' . $_SERVER['HTTP_HOST']);

            $data = array(

                'title' => 'Verify your Mobile OTP',

            );

            if (env('ENABLE_ECOM_FRONTEND') == true) :

                return view('ecommerce.pages.otp_verification', $data);

            else :

                return view('site.otp_verification', $data);

            endif;
        }

        ### Insert member data

        DB::transaction(function () {

            $leg                 = session('xleg');

            $member              = new Member();

            $member->id          = session('xid');

            $member->sponsor     = session('xsponsor');

            $member->position    = session('xposition');

            $member->name        = session('xname');

            $member->username    = session('xusername');

            $member->password    = password_hash(session('xpassword'), 1);

            $member->email       = session('xemail');

            $member->reg_product = session('xpackage');

            $member->phone       = session('xphone');

            if (env('REG_FEE') !== 'Free') :

                $member->my_topup = session('xpackamt');

            endif;

            $member->leg = $leg;

            $member->save();

            ### Build Tree

            $member = Member::find(session('xposition'));



            if (env('LEG_NUMBER') == 1 && env('AUTOPOOL') !== true) {
            } else {

                $member->$leg = session('xid');
            }

            $member->capping = (!blank(session('xcapping'))) ? session('xcapping') : 0;

            $member->save();



            ### Update EPIN status



            if (strlen(trim(session('xepin'))) > 1) {

                DB::table('epins')->where('epin', session('xepin'))->update(array(

                    'used_by'   => session('xid'),

                    'used_date' => date('Y-m-d'),

                ));
            }



            ### Insert Important Records

            DB::table('levels')->insert(array(

                'id' => session('xid'),

            ));

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

            if (env('DONATION_PLAN_TYPE') === 'PH' && env('ENABLE_DONATION_PLAN') == true) {

                $status = 0;
            }

            DB::table('user_relations')->insert(array(

                'id'         => session('xid'),

                'parent_id'  => session('xposition'),

                'status'     => $status,

                'created_at' => date('Y-m-d H:i:s'),

            ));

            DB::table('user_relations')->where('id', session('xid'))->update(['my_bv' => session('xbv')]);

            DB::table('leg_info')->insert(array(

                'id'        => session('xid'),

                'parent_id' => session('xposition'),

            ));

            foreach (config('config.wallet_types') as $wallet) :

                $array[] = array(

                    'id'   => session('xid'),

                    'type' => $wallet,

                );

            endforeach;

            DB::table('wallet')->insert($array);



            if (env('SHOW_REG_PRODUCT') == true) :

                $status = 'Processing';

                if (env('AUTO_DELIVER_REG_PRODUCT') == true) {

                    $status = 'Delivered';
                }

                $product = array(

                    'product_id'    => session('xpackage'),

                    'product_name'  => session('xpackname'),

                    'product_price' => session('xpackamt'),

                    'product_qty'   => 1,

                    'product_tax'   => (session('xpackamt') * session('xpackgst')) / 100,

                );



                $saleid = DB::table('sale_orders')->insertGetId(array(

                    'userid'       => session('xid'),

                    'products'     => serialize($product),

                    'total_amount' => session('xpackamt'),

                    'status'       => $status,

                    'created_at'   => date('Y-m-d H:i:s'),

                ));






                if (session('xpackage') > 0) {

                    $product = Product::find(session('xpackage'));

                    --$product->available_qty;

                    ++$product->sold_qty;

                    $product->save();
                }

            endif;



            if (session('xno_of_emi') > 0) {

                $date            = new DateTime(date('Y-m-d H:i:s'));

                $monthly_array[] = $date->format('Y-m-d');

                for ($i = 0, $iMax = session('xno_of_emi'); $i < $iMax; $i++) {

                    $date   = $date->modify('+1 month');

                    $month  = $date->format('Y-m-d');

                    $emis[] = array(

                        'user_id'    => session('xid'),

                        'product_id' => session('xpackage'),

                        'amount'     => session('xemi_amount'),

                        'date'       => $month,

                    );
                }

                DB::table('my_emis')->insert($emis);
            }

            #### Generate Income ############

            if (env('REG_FEE') !== 'Free') {

                if (env('REG_FEE') !== 'Partial' || env('ALLOW_INCOME_FOR_PARTIAL_FEE') === true) {

                    if (env('AUTO_DELIVER_REG_PRODUCT') == true)

                        app('\App\Http\Controllers\Models')->product_income(session('xid'), session('xpackage'), session('xsponsor'), session('xposition'));

                    app('\App\Http\Controllers\Models')->level_update(session('xid'));
                }
            }

            #################################

            if (env('ENABLE_DONATION_PLAN') === true)

                app('\App\Http\Controllers\Donation')->generate_donation(session('xid'), 1);
        });

        #### Send SMS/Email ############

        if (env('SMS_ENABLE') == true && session('xphone') > 1000)

            sms(session('xphone'), 'Hi ' . session('xname') . ', Welcome to ' . env('APP_NAME') . '. Your User ID is: ' . env('ID_EXT') . session('xid') . ' and Password is: ' . session('xpassword') . '. Login here: www.' . $_SERVER['HTTP_HOST']);

        if (trim(session('xemail')) !== '') :

            $msg   = "Hi " . session('xname') . ", Welcome to " . env('APP_NAME') . " Family. We are excited to have you as our valuable member. <p>Now you may login your account at " . $_SERVER['HTTP_HOST'] . " or clicking on the below button.</p> Your Your <strong>User ID is: " . env('ID_EXT') . session('xid') . "</strong> <br/> <strong>Password</strong> is: " . session('xpassword');

            $order = array(

                'title'    => 'Registration Completed',

                'subject'  => 'Welcome to ' . env('APP_NAME'),

                'msg'      => $msg,

                'url'      => url('/member'),

                'urltitle' => 'Login Member Panel',

            );

            if (env('EMAIL_ENABLE') == true) {

                if (env('QUEUE_ENABLE') == true) :

                    Mail::to(session('xemail'))->queue(new Notification($order));

                else :

                    Mail::to(session('xemail'))->send(new Notification($order));

                endif;
            }

        endif;

        #################################

        $data = array(

            'id'       => session('xid'),

            'username' => session('xusername'),

            'name'     => session('xname'),

            'title'    => 'Registration is Completed',

        );

        Cookie::queue('member_id', session('xid'), 2);

        //RICK     //RICK   run_queue();  ();

        if ($script == false) {

            session()->flush();

            return view('site.registration_complete', $data);
        }

        echo script_redirect(url('/complete_registration_sc'));
    }



    public function complete_registration_sc()

    {

        $data = array(

            'id'       => session('xid'),

            'username' => session('xusername'),

            'name'     => session('xname'),

            'title'    => 'Registration is Completed',

        );

        session()->flush();

        return view('site.registration_complete', $data);
    }



    //#---------------------------------------- Member Registration Ends------------------------------------





    //#---------------------------------------- Member Login Part------------------------------------




    public function member_login(Request $e)

    {
        $get_member_data = Member::select('id', 'password', 'status')->where('email', trim($e->email))

            ->orWhere('id', $this->id_filter($e->email))->orWhere('username', $this->id_filter($e->email))

            ->first();



        if (!empty($get_member_data)) {

            if ($get_member_data->status !== 'Active')

                return redirect('/member')->with('msg', $this->msg('Your account has been suspended, Contact Our Customer Care.', 'danger', false));

            if (env('OTP_LOGIN') == true) {

                if (session('otp_login') == $e->otp) {

                    if ($e->cookie === 1) {

                        Cookie::queue('member_id', $get_member_data->id, 10080);
                    }

                    \request()->session()->forget('otp_login');

                    return $this->generate_member_session($get_member_data->id);
                }
            }

            if (password_verify($e->password, $get_member_data->password) === true) {

                if ($e->cookie === 1) {

                    Cookie::queue('member_id', $get_member_data->id, 10080);
                }

                return $this->generate_member_session($get_member_data->id);
            }

            return redirect('/member')->with('msg', $this->msg('Invalid Login Detail', 'danger'));
        }

        return redirect('/member')->with('msg', $this->msg('Invalid Login Detail', 'danger'));
    }

    function msg($msg, $type = 'success', $close = true)
    {
        $icon = asset('images/tick.png');
        if ($type === 'info')
            $icon = asset('images/info.png');
        if ($type === 'warning')
            $icon = asset('images/warning.png');
        if ($type === 'danger')
            $icon = asset('images/danger.png');
        $str = '<div role="alert" class="alert mb-2 mt-1 alert-dismissible fade show alert-' . $type . '"><img src="' . $icon . '"></span> ' . $msg;
        $str .= '<button type="button" class="close" data-dismiss="alert" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button></div>';
        return $str;
    }

    private function generate_member_session($member_id)

    {

        $get_member_data = Member::select('id', 'sponsor', 'name', 'phone', 'my_topup', 'topup_date', 'created_at', 'avatar')

            ->where('id', $member_id)->first();

        $array           = array(

            'member_id'    => $get_member_data->id,

            'name'         => $get_member_data->name,

            'my_topup'     => $get_member_data->my_topup,

            'member_avtar' => $get_member_data->avatar,

            'topup_date'   => $get_member_data->topup_date,

            'join_date'    => date('Y-m-d', strtotime($get_member_data->created_at)),

        );

        \request()->session()->put($array);

        DB::table('sessions')->insert(array(

            'user_id'   => $get_member_data->id,

            'user_type' => 'Member',

            'ip'        => \request()->getClientIp(),

            'browser'   => \request()->userAgent(),

            'time'      => time(),

        ));

        /// SMS notification to member for each new browser login

        if ((int)\request()->cookie('member_browser') !== 1 && env('SMS_ENABLE') === true && env('NOTIFY_MEMBER_VIA_SMS') === true) {

            sms($get_member_data->phone, 'Hi ' . $get_member_data->name . ', You have just logged in from a new browser. -' . env('APP_NAME'));

            if (env('ENABLE_ECOM_FRONTEND') === true && env('ECOM_FIRST_PORTAL') == true) {

                return redirect(url('/my-account-dashboard'))->cookie('member_browser', 1, 100080);
            }

            return redirect('/member-dash')->cookie('member_browser', 1, 100080);
        }

        if (\request()->session()->has('redirect_url')) {

            $url = session('redirect_url');

            \request()->session()->forget('redirect_url');

            return redirect($url);
        }

        if (env('ENABLE_ECOM_FRONTEND') === true) {

            return redirect(url('/my-account-dashboard'));
        }

        return redirect('/member-dash');
    }



    public function user_forgotpw(Request $e)

    {

        $get_member_data = Member::select('id', 'name', 'email', 'phone')->where('email', trim($e->email))

            ->orWhere('id', $this->$this->id_filter($e->email))->first();

        if (!empty($get_member_data)) {

            $token = md5($get_member_data->id . time() . 'member');

            DB::table('password_resets')->insert(array(

                'user_id' => $get_member_data->id,

                'type'    => 'Member',

                'token'   => $token,

            ));

            $msg   = "Hi " . $get_member_data->name . ", You have recently requested to reset your password. To proceed with this request, click on the below Link within 24 hours.";

            $order = array(

                'title'    => 'Forgot Password ?',

                'subject'  => 'Recover your Password',

                'msg'      => $msg,

                'url'      => url('/requestpass/' . $token),

                'urltitle' => 'Click to Reset',

            );

            if (env('EMAIL_ENABLE') == true) :

                if (env('QUEUE_ENABLE') == true) :

                    Mail::to($get_member_data->email)->queue(new Forgetpw($order));

                // RICK     //RICK     //RICK   run_queue();  ();

                else :

                    Mail::to($get_member_data->email)->send(new Forgetpw($order));

                endif;

            endif;

            if (env('SMS_ENABLE') === true && env('SMS_PASS_RESET') === true)

                sms($get_member_data->phone, 'To reset your password check your email or click this link: ' . urlencode(url('/requestpass/' . $token)));
        }

        return redirect('/forgot-user')->with('msg', $this->msg('If your Information is correct, we\'ll sent you a Email/SMS soon.', 'success', false));
    }



    public function member_logout()

    {

        \request()->session()->flush();

        $cookie = Cookie::forget('member_id');

        return redirect('/member')->with('msg', $this->msg('You are logged out !'))->withCookie($cookie);
    }





    public function login_member($id)

    {

        return $this->generate_member_session($id);
    }



    public function check_member_login($type = null)

    {


        if (session('member_id') > 0) {

            return redirect('/member-dash');
        }


        \request()->session()->forget('redirect_url');

        if (\request('redirect')) {

            \request()->session()->put('redirect_url', \request('redirect'));
        }

        if (\request()->cookie('member_id') > 0) {

            return $this->generate_member_session(Cookie::get('member_id'));
        }

        if ($type === 'Mobile') {

            return view('site.member_login_mobile', ['title' => 'Login Member Area']);
        }

        return view('site.member_login', ['title' => 'Login Member Area']);
    }



    //#---------------------------------------- Member Login Ends------------------------------------





    //#---------------------------------------- Admin Login Part------------------------------------



    public function check_staff_login()

    {

        if (\request()->cookie('admin_id') > 0) {

            return $this->generate_admin_session(Cookie::get('admin_id'));
        }

        return view('site.admin_login');
    }



    private function generate_admin_session($admin_id)

    {

        $get_staff_data = Staff::select('id', 'name', 'phone', 'branch_id', 'designation')->where('id', $admin_id)

            ->first();

        $permissions    = DB::table('designations')->select('title', 'permissions')

            ->where('id', $get_staff_data->designation)->first();

        $des_title      = 'Administrator';

        if (!empty($permissions)) :

            $des_title   = $permissions->title;

            $permissions = $permissions->permissions;

        endif;

        $array = array(

            'admin_id'    => $get_staff_data->id,

            'branch_id'   => $get_staff_data->branch_id,

            'name'        => $get_staff_data->name,

            'designation' => $get_staff_data->designation,

            'des_title'   => $des_title,

            'permissions' => $permissions,

        );

        \request()->session()->put($array);

        DB::table('sessions')->insert(array(

            'user_id'   => $get_staff_data->id,

            'user_type' => 'Staff',

            'ip'        => \request()->getClientIp(),

            'browser'   => \request()->userAgent(),

            'time'      => time(),

        ));

        /// SMS notification to admin for each new browser login

        /* if ((int)\request()->cookie('admin_browser') !== 1 && env('SMS_ENABLE') === true && env('NOTIFY_ADMIN_VIA_SMS') === true) {

            sms($get_staff_data->phone, 'Hi ' . $get_staff_data->name . ', You have just logged in from a new browser. -' . env('APP_NAME'));

            return redirect('/staff-dash')->cookie('admin_browser', 1, 100080);
        }
 */
        return redirect('/staff-dash');
    }



    public function staff_login(Request $e)

    {

        $get_staff_data = Staff::select('id', 'password', 'status')->where('email', trim($e->email))->first();

        if (!empty($get_staff_data)) {

            if ($get_staff_data->status !== 'Active') {

                return redirect('/staff')->with('msg', $this->msg('Your account has been suspended', 'danger', false));
            }

            if (password_verify($e->password, $get_staff_data->password) == true) {

                if ($e->cookie == 1) {

                    Cookie::queue('admin_id', $get_staff_data->id, 10080);
                }

                return $this->generate_admin_session($get_staff_data->id);
            }

            return redirect('/staff')->with('msg', $this->msg('Invalid Login Detail', 'danger'));
        }

        return redirect('/staff')->with('msg', $this->msg('Invalid Login Detail', 'danger'));
    }



    public function staff_logout()

    {

        \request()->session()->flush();

        $cookie = Cookie::forget('admin_id');

        return redirect('/staff')->with('msg', $this->msg('You are logged out !'))->withCookie($cookie);
    }



    public function staff_forgotpw(Request $e)

    {

        $get_staff_data = Staff::select('id', 'name', 'email')->where('email', trim($e->email))->first();

        if (!empty($get_staff_data)) {

            $token = md5($get_staff_data->id . time() . 'staff');

            DB::table('password_resets')->insert(array(

                'user_id' => $get_staff_data->id,

                'type'    => 'Staff',

                'token'   => $token,

            ));

            $msg   = "Hi " . $get_staff_data->name . ", You have recently requested to reset your password. To proceed with this request, click on the below Link within 24 hours.";

            $order = array(

                'title'    => 'Forgot Password ?',

                'subject'  => 'Recover your Password',

                'msg'      => $msg,

                'url'      => url('/requestpass/' . $token),

                'urltitle' => 'Click to Reset',

            );

            if (env('EMAIL_ENABLE') == true) :

                if (env('QUEUE_ENABLE') == true) :

                    Mail::to($get_staff_data->email)->queue(new Forgetpw($order));

                //RICK      //RICK     //RICK   run_queue();  ();

                else :

                    Mail::to($get_staff_data->email)->send(new Forgetpw($order));

                endif;

            endif;
        }

        return redirect('/forgotpw-staff')->with('msg', $this->msg('If your Information is correct, we\'ll sent you a Email/SMS soon.', 'success', false));
    }



    //#---------------------------------------- Admin Login Ends------------------------------------





    //#---------------------------------------- Password Reset Request Part------------------------------------

    public function requestpass($token)

    {

        $get_detail = DB::table('password_resets')->select('token', 'type')->where('token', $token)

            ->where('status', 'Active')->where('created_at', '>', date('Y-m-d H:i:s', strtotime('-1 day')))

            ->orderBy('id', 'DESC')->first();

        if (empty($get_detail))

            return redirect('/member')->with('msg', $this->msg('Invalid Request or Link Expired. Try to request password once again.', 'danger', false));

        return view('site.password_reset_form', ['detail' => $get_detail, 'title' => 'Enter New Password']);
    }



    public function reset_forgotpw(Request $e)

    {

        $e->validate([

            'new_password'    => 'required|min:5',

            'retype_password' => 'required|same:new_password',

        ]);

        $get_detail = DB::table('password_resets')->select('id', 'user_id', 'type')->where('token', $e->token)

            ->where('status', 'Active')->where('created_at', '>', date('Y-m-d H:i:s', strtotime('-1 day')))

            ->orderBy('id', 'DESC')->first();

        if (empty($get_detail))

            return redirect('/member')->with('msg', $this->msg('Invalid Request or Link Expired. Try to request password once again.', 'danger', false));

        DB::table('password_resets')->where('id', $get_detail->id)->update(array('status' => 'Used'));

        if ($get_detail->type === 'Staff') :

            Staff::where('id', $get_detail->user_id)->update(array('password' => password_hash($e->new_password, 1)));

            return redirect('/staff')->with('msg', $this->msg('Your Password has been changed.'));

        endif;

        if ($get_detail->type === 'Member') :

            Member::where('id', $get_detail->user_id)->update(array('password' => password_hash($e->new_password, 1)));

            return redirect('/member')->with('msg', $this->msg('Your Password has been changed.'));

        endif;

        if ($get_detail->type === 'Dealer') :

        /*    Dealer::where('id', $get_detail->user_id)->update(array('password' => password_hash($e->new_password, 1)));

            return redirect('/dealer')->with('msg', $this->msg('Your Password has been changed.')); */

        endif;
    }





    //#---------------------------------------- Password Reset Request Ends------------------------------------





    //#---------------------------------------- Dealer Login Part------------------------------------



    public function dealer_login(Request $e)

    {

        $get_dealer_data = \App\Franchisee::select('id', 'password', 'status')->where('email', trim($e->email))

            ->orWhere('id', $this->$this->id_filter($e->email))->first();

        if (!empty($get_dealer_data)) {

            if ($get_dealer_data->status !== 'Active')

                return redirect('/dealer')->with('msg', $this->msg('Your account has been suspended, Contact Our Customer Care.', 'danger', false));

            if (password_verify($e->password, $get_dealer_data->password) === true) {

                if ($e->cookie === 1) {

                    Cookie::queue('dealer_id', $get_dealer_data->id, 10080);
                }

                return $this->generate_dealer_session($get_dealer_data->id);
            }

            return redirect('/dealer')->with('msg', $this->msg('Invalid Login Detail', 'danger'));
        }

        return redirect('/dealer')->with('msg', $this->msg('Invalid Login Detail', 'danger'));
    }



    private function generate_dealer_session($dealer_id)

    {

        $get_dealer_data = \App\Franchisee::select('id', 'owner_name', 'phone')->where('id', $dealer_id)->first();

        $array           = array(

            'dealer_id' => $get_dealer_data->id,

            'name'      => $get_dealer_data->owner_name,

        );

        \request()->session()->put($array);

        DB::table('sessions')->insert(array(

            'user_id'   => $get_dealer_data->id,

            'user_type' => 'Dealer',

            'ip'        => \request()->getClientIp(),

            'browser'   => \request()->userAgent(),

            'time'      => time(),

        ));

        /// SMS notification to admin for each new browser login

        if ((int)\request()->cookie('dealer_browser') !== 1 && env('SMS_ENABLE') === true && env('NOTIFY_DEALER_VIA_SMS') === true) {

            sms($get_dealer_data->phone, 'Hi ' . $get_dealer_data->owner_name . ', You have just logged in from a new browser. -' . env('APP_NAME'));

            return redirect('/dealer-dash')->cookie('dealer_browser', 1, 100080);
        }

        return redirect('/dealer-dash');
    }



    public function dealer_forgotpw(Request $e)

    {

        /*  $get_dealer_data = Dealer::select('id', 'owner_name', 'email', 'phone')->where('email', trim($e->email))

                                 ->orWhere('id', $this->$this->id_filter($e->email))->first();

        if (!empty($get_dealer_data)) {

            $token = md5($get_dealer_data->id . time() . 'member');

            DB::table('password_resets')->insert(array(

                                                     'user_id' => $get_dealer_data->id,

                                                     'type'    => 'Dealer',

                                                     'token'   => $token,

                                                 ));

            $msg   = "Hi " . $get_dealer_data->owner_name . ", You have recently requested to reset your password. To proceed with this request, click on the below Link within 24 hours.";

            $order = array(

                'title'    => 'Forgot Password ?',

                'subject'  => 'Recover your Password',

                'msg'      => $msg,

                'url'      => url('/requestpass/' . $token),

                'urltitle' => 'Click to Reset',

            );

            if (env('EMAIL_ENABLE') == true):

                if (env('QUEUE_ENABLE') == true):

                    Mail::to($get_dealer_data->email)->queue(new Forgetpw($order));

                       //RICK     //RICK   run_queue();  ();

                else:

                    Mail::to($get_dealer_data->email)->send(new Forgetpw($order));

                endif;

            endif;

            if (env('SMS_ENABLE') === true && env('SMS_PASS_RESET') === true)

                sms($get_dealer_data->phone, 'To reset your password check your email or click this link: ' . urlencode(url('/requestpass/' . $token)));

        } */

        return redirect('/forgot-dealer')->with('msg', $this->msg('If your Information is correct, we\'ll sent you a Email/SMS soon.', 'success', false));
    }



    public function dealer_logout()

    {

        \request()->session()->flush();

        $cookie = Cookie::forget('dealer_id');

        return redirect('/dealer')->with('msg', $this->msg('You are logged out !'))->withCookie($cookie);
    }





    public function check_dealer_login()

    {

        if (\request()->cookie('dealer_id') > 0) {

            return $this->generate_dealer_session(Cookie::get('dealer_id'));
        }

        return view('site.dealer_login', ['title' => 'Login Dealer Area']);
    }





    //#---------------------------------------- Dealer Login Ends------------------------------------



    public function checkuser($id)

    {

        $count = Member::where('username', $id)->count('id');

        if ($count <= 0) {

            echo '<strong class="text-success">Username Available !</strong>';
        } else {

            echo '<strong class="text-danger">Username Not Available !</strong>';
        }
    }


    function  id_filter($id)
    {
        return trim(str_ireplace(env('ID_EXT'), '', $id));
    }
    public function getuser($id)

    {



        $result = Member::select('name')->where('id', $this->id_filter($id))->first();


        if (!empty($result)) {


            echo '<strong class="text-success">' . $result->name . '</strong>';
        } else {

            echo '<strong class="text-danger">User Not Available !</strong>';
        }
    }



    public function contact_us_save(Request $e)

    {

        if (
            $e->session()->has('last_msg_time_cntct_us') && $e->session()

            ->pull('last_msg_time_cntct_us') > (time() - 300)
        ) {

            echo errorrecord('You have recently sent us a message. Please wait sometime to send another');

            return;
        }

        $id = DB::table('contact_us')->insertGetId(array(

            'name'    => $e->name,

            'email'   => $e->email,

            'phone'   => $e->phone,

            'subject' => $e->subject,

            'message' => $e->message,

            'date'    => date('Y-m-d H:i:s'),

        ));

        if ($id) {

            $e->session()->put('last_msg_time_cntct_us', time());

            echo success('Thank you for contacting us. We\'ll get back to you soon.');

            return;
        }

        echo errorrecord('Some Error occurred. Try again', url('/contact-us'));

        return;
    }



    public function newsletter_create(Request $e)

    {

        if (count_all('newsletter', array('email' => $e->email)) > 0) {

            echo '<strong style="color: #ff7818">You are already a subscriber</strong>';

            return;
        }

        $id = DB::table('newsletter')->insertGetId(array(

            'email'      => $e->email,

            'date'       => date('Y-m-d H:i:s'),

            'ip_address' => $e->getClientIp(),

        ));

        if ($id) {

            echo '<strong class="success">Thank you for subscribing</strong>';

            return;
        }
    }



    public function unsubscribe_newsletter()

    {

        $email = select('email', 'members', array('id' => session('member_id')));

        if (count_all('newsletter', array('email' => $email, 'status' => 'Subscribed')) == 0) {

            echo errorrecord('You are not subscribed.');

            return;
        }

        $id = DB::table('newsletter')->where('email', $email)->update(array('status' => 'Un-Subscribed'));

        if ($id) {

            echo success('You are now unsubscribed from our newsletter.');

            return;
        }
    }



    public function id_card($id)

    {

        $member = Member::select('id', 'name', 'phone', 'my_topup', 'avatar', 'created_at')->where('id', $id)->first();

        if ($member->my_topup <= 0) {

            echo '<h1>This member is not active yet</h1>';

            return;
        }

        $data = [

            'title'   => $member->name,

            'mdetail' => $member,

        ];

        return view('site.id_card', $data);
    }



    public function anything()

    {

        $database = DB::table('wallet')->select('id')

            ->distinct()->where('type', '!=', 'Bonus 6')->get();

        foreach ($database as $x) {

            foreach (config('config.wallet_types') as $wallet) :

                $array = array(

                    'id'   => $x->id,

                    'type' => $wallet,

                );



                DB::table('wallet')->insert($array);

            endforeach;
        }
    }
}
