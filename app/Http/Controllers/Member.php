<?php

namespace App\Http\Controllers;

use App\Cart;
use App\Product;
use App\User_relation;
use App\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use niklasravnsborg\LaravelPdf\Facades\Pdf;
use Intervention\Image\Facades\Image;
use Spatie\ImageOptimizer\OptimizerChainFactory;


class Member extends Controller
{

    function count_all($table, $where = 1)
    {
    
        $result = DB::table($table)->where($where)->count('id');
        if (!empty($result)) {
            return $result;
        }
        return 0;
    } 

    function sum($field, $table, $where = 1)
    {
      
        $result = DB::table($table);
        if ($where !== 1) {
            $result = $result->where($where);
        }
        $result = $result->sum($field);
        if (!empty($result)) {
            return $result;
        }
        return 0;
   
    }
    public function index()
    {
 
       
        \request()->session()->forget(array('topup_true', 'package', 'topup_id'));
        $emis = null;
        if (env('EMI_ENABLE') == true) {
            $emis = DB::table('my_emis')->where('user_id', session('member_id'))->orderBy('date', 'ASC')->paginate(10);
        }
        $data = array(
            'auth' =>true,
            'title'             => session('name') . ' - ' . env('APP_NAME'),
            'heading'           => 'Hello ' . session('name') . ' !',
            'subtitle'          => 'Welcome back to ' . env('MY_APP_NAME'),
            'msg_count'         => $this->count_all('supports', array(
                'receiver_id' => session('member_id'), 'status' => 'Unread',
            )),
            'mdetail'           => \App\Member::select('name', 'phone', 'email', 'reg_product', 'sponsor', 'avatar', 'rank', 'created_at')->where('id', session('member_id'))->first(),
            'earning'           => DB::table('earnings')->select('amount', 'type', 'date')
                                     ->where('userid', session('member_id'))->orderBy('id', 'DESC')->paginate(10),
            'total_earn'        => DB::table('earnings')->where('userid', session('member_id'))->sum('amount'),
            'total_withdrawals' => DB::table('withdrawal_records')->where('user_id', session('member_id'))->sum('amount'),
            'wallet'            => $this->sum('balance', 'wallet', array('id' => session('member_id'))),
            'direct_referred'   => $this->count_all('members', array('sponsor' => session('member_id'))),
            'leg_info'          => DB::table('leg_info')->where('id', session('member_id'))
                                     ->select('total_a', 'total_b', 'total_c', 'total_d', 
                                     'total_e', 'total_f', 'total_g', 'total_h', 'total_i', 'total_j')
                                     ->first(),
            'my_emis'           => $emis,
            'login_history'     => DB::table('sessions')
                                     ->where(array(
                                                 'user_id'   => session('member_id'),
                                                 'user_type' => 'Member',
                                             ))
                                     ->orderBy('id', 'DESC')
                                     ->limit(3)
                                     ->get(),
        );



        return view('member.dashboard', $data);
    }
    function cache_select($data, $table, $where = null, $or_where = null)
    {
        $serialize = serialize($where);
        $qry       = cache()->remember($data . $table . $serialize, 14440, function () use ($data, $table, $where, $or_where) {
            $qry = \Illuminate\Support\Facades\DB::table($table)->select($data);
            if (!blank($where)) {
                $qry = $qry->where($where);
            }
            if (!blank($or_where)) {
                $qry = $qry->orWhere($or_where);
            }
            $result = $qry->first();
            if (!empty($result)) {
                return $result->$data;
            }
            return null;
        });
        return $qry;
    }
    public function donation_index()
    {
        \request()->session()->forget(array('topup_true', 'package', 'topup_id'));
        $package       = null;
        $get_last_help = null;
        if (env('DONATION_PLAN_TYPE') === 'PH') {
            $get_last_help = DB::table('ph_pending_commitments')->select('plan_no', 'status')
                               ->where('user_id', session('member_id'))->orderBy('id', 'DESC')->first();
            if (empty($get_last_help)) {
                $get_last_help = 0;
                $plan_no       = 0;
            } else {
                $plan_no = $get_last_help->plan_no;
            }
            if (env('REG_FEE') === 'Paid') {
                $last_topup = select('reg_product', 'members', ['id' => session('member_id')]);
                $package    = cache()->rememberForever('ph_donation_plans' . $plan_no, function () use ($last_topup) {
                    return DB::table('ph_donation_plans')
                             ->where('registration_product', $last_topup)
                             ->orderBy('donation_serial', 'ASC')->first();
                });
            } else {
                $package = cache()->rememberForever('ph_donation_plans_non' . $plan_no, function () use ($plan_no) {
                    return DB::table('ph_donation_plans')->select('id', 'plan_name', 'ph_minimum_amt')
                             ->where('id', '>=', $plan_no)
                             ->orderBy('donation_serial', 'ASC')->first();
                });
            }
        }

        $wallets = cache()->remember('wallets', 43200, function () {
            return DB::table('wallet')->select('type')->where('id', env('TOP_ID'))->get();
        });
        $data    = array(
            'title'              => session('name') . ' - ' . env('APP_NAME'),
            'heading'            => 'DASHBOARD',
            'subtitle'           => 'Welcome to ' . env('APP_NAME'),
            'last_help'          => ($get_last_help !== 0) ? $get_last_help : null,
            'package'            => $package,
            'wallets'            => $wallets,
            'mdetail'            => \App\Member::select('avatar')->where('id', session('member_id'))->first(),
            'msg_count'          => $this->count_all('supports', array(
                'receiver_id' => session('member_id'), 'status' => 'Unread',
            )),
            'sent_donations'     => sum('amount', 'donations', array(
                'sender_id' => session('member_id'), 'status' => 'Accepted',
            )),
            'received_donations' => sum('amount', 'donations', array(
                'receiver_id' => session('member_id'), 'status' => 'Accepted',
            )),
            'send_donations'     => DB::table('donations')->select('id', 'receiver_id', 'amount', 'expires_by')
                                      ->where(array(
                                                  'sender_id' => session('member_id'),
                                                  'status'    => 'Pending',
                                              ))
                                      ->paginate(5),
            'receive_donations'  => DB::table('donations')
                                      ->select('id', 'sender_id', 'amount', 'txn_detail', 'receipt', 'status', 'expires_by')
                                      ->where(array(
                                                  'receiver_id' => session('member_id'),
                                              ))
                                      ->where(function ($query) {
                                          $query->where('status', 'Sent')
                                                ->orWhere('status', 'Pending');
                                      })
                                      ->paginate(5),
        );
        return view('member.donations.dashboard', $data);
    }

    public function ecom_dashboard()
    {
        \request()->session()->forget(array('topup_true', 'package', 'topup_id'));
        $data = array(
            'title'    => session('name') . ' - ' . env('APP_NAME'),
            'orders'   => DB::table('sale_orders')->where('userid', session('member_id'))->orderBy('id', 'DESC')
                            ->paginate(10),
            'earnings' => \App\Earning::where('userid', session('member_id'))->orderBy('id', 'DESC')->paginate(10),
            'mdetail'  => \App\Member::select('sponsor', 'position', 'name', 'email', 'phone', 'available_points', 'used_points')
                                     ->where('id', session('member_id'))->first(),
            'products' => Cart::with(array(
                                         'products' => function ($query) {
                                             $query->select('id', 'name', 'amount', 'gst');
                                         },
                                     ))->select('id', 'product_id', 'qty', 'options')
                              ->where('user_id', session('member_id'))->get(),
            'wishlist' => Wishlist::with(array(
                                             'products' => function ($query) {
                                                 $query->select('id', 'name');
                                             },
                                         ))->select('id', 'product_id')->where('user_id', session('member_id'))
                                  ->paginate(10),

            'profile'                   => DB::table('member_profile')->where('id', session('member_id'))->first(),
            'my_pv'                     => User_relation::select('my_bv')->where('id', session('member_id'))->first(),
            'repurcahse_wallet_balance' => select('balance', 'wallet', array(
                'id'   => session('member_id'),
                'type' => config('config.repurchase_wallet'),
            )),
        );
        return view('member.ecom_dashboard', $data);
    }

    public function do_topup_member(Request $e)
    {
        if (blank(id_filter($e->id))) {
          //  echo msg('User ID is required', 'danger');
            return;
        }
        $product_detail = DB::table('products')->select('id', 'name', 'amount', 'bv', 'gst', 'capping', 'roi_amount')->where('id', $e->package)
                            ->first();
        $product_amount = $product_detail->amount + ($product_detail->amount * $product_detail->gst / 100);
        if (blank($e->epin) && blank($e->pg) && env('ENABLE_EPIN') === true && env('TOPUP_FROM_WALLET') === false) {
           // echo msg('Please Enter E-PIN', 'danger');
            return;
        }
        $get_last_topup = select('my_topup', 'members', ['id' => $e->id]);
        if ($get_last_topup == $product_detail->amount) {
           // echo msg('This UserID has been topped up already with .' . env('CURRENCY_SIGN') . $product_detail->amount, 'danger');
            return;
        }
        $wallet_balance = select('balance', 'wallet', array(
            'id'   => session('member_id'),
            'type' => config('config.wallet_types')[0],
        ));

        if ((blank($e->epin) && blank($e->pg)) && (env('TOPUP_FROM_WALLET') === true && $wallet_balance < $product_amount)) {
          //  echo msg('Please Select a Payment Method or Add fund at wallet.', 'danger');
            return;
        }
        if (!blank($e->epin)) {
            $epin_value = select('amount', 'epins', array('epin' => $e->epin, 'used_by' => null));
            if ($epin_value < $product_amount) {
              //  echo msg('Either E-PIN is invalid/Used or Epin amount is less than the package amount + Tax (' . $product_detail->gst . '%)=<strong>' . env('CURRENCY_SIGN') . ' ' . $product_amount . '</strong>.', 'danger');
                return;
            }
            ####### Topup Start
            DB::transaction(function () use ($product_detail, $e) {
                $member              = \App\Member::find(id_filter($e->id));
                $is_topup            = $member->my_topup;
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
                if ($is_topup !== $product_detail->amount) {
                    app('\App\Http\Controllers\Models')->product_income(id_filter($e->id), $e->package, $sponsor, $position);
                }
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
                $array = array(
                    'used_by'   => id_filter($e->id),
                    'used_date' => date('Y-m-d'),
                );
                DB::table('epins')->where('epin', $e->epin)->update($array);
            });
            ######### End topup
            app('\App\Http\Controllers\Models')->level_update(id_filter($e->id));
          //  echo msg('Top-up Done Successfully !');
              //RICK   run_queue(); 
            return;
        }
        if (!blank($e->pg) || env('TOPUP_FROM_WALLET') === true) {
            if (env('TOPUP_FROM_WALLET') === true) {

                if ($wallet_balance < $product_amount) {
                    \request()->session()->put(array('topup_id' => id_filter($e->id), 'package' => $product_detail));
                    echo script_redirect(url('/topup-pg-form'));
                    return;
                }

                DB::transaction(function () use ($product_detail, $e, $product_amount) {
                    $member              = \App\Member::find(id_filter($e->id));
                    $is_topup            = $member->my_topup;
                    $sponsor             = $member->sponsor;
                    $position            = $member->position;
                    $member->my_topup    = $product_detail->amount;
                    $member->reg_product = $product_detail->id;
                    $member->topup_date  = date('Y-m-d');
                    $member->capping     = $product_detail->capping;
                    $member->save();
                    $e->session()->put('my_topup', $product_detail->amount);

                    ####### Topup Start
                    DB::table('user_relations')->where('id', id_filter($e->id))->update(array(
                                                                                            'status' => 1,
                                                                                            'my_bv'  => DB::raw('my_bv+' . $product_detail->bv),
                                                                                        ));
                    if ($is_topup !== $product_detail->amount) {
                        app('\App\Http\Controllers\Models')->product_income(id_filter($e->id), $e->package, $sponsor, $position);
                    }
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
                    DB::table('wallet')->where(array(
                                                   'id'   => session('member_id'),
                                                   'type' => config('config.wallet_types')[0],
                                               ))
                      ->update(array('balance' => DB::raw('balance-' . $product_amount)));
                });
                app('\App\Http\Controllers\Models')->level_update(id_filter($e->id));
              //  echo msg('Topup done successfully !');
                  //RICK   run_queue(); 
                return;
                ##### Topup End

            }
            \request()->session()->put(array('topup_id' => id_filter($e->id), 'package' => $product_detail));
            echo script_redirect(url('/topup-pg-form'));
            return;
        }
    }

    public function topup_pg_form()
    {
        return view('member.wallet.topup_pg_form', [
            'title'   => 'Topup your account Online',
            'heading' => 'Topup your account Online',
        ]);
    }

    public function profile()
    {
        $data = array(
            'title'    => 'My Profile',
            'heading'  => 'My Profile',
            'subtitle' => 'Update your profile',
            'mdetail'  => \App\Member::select('name', 'email', 'phone', 'avatar', 'id_proof', 'address_proof')
                                     ->where('id', session('member_id'))->first(),
            'profile'  => DB::table('member_profile')->where('id', session('member_id'))->first(),
        ); 
        return view('member.profile.profile', $data);
    }

    public function detail_little($id)
    {
        $data = array(
            'detail'  => \App\Member::find($id),
            'profile' => DB::table('member_profile')->where('id', $id)->first(),
        );
        return view('member.tree.detail_little', $data);
    }


    public function passwords()
    {
        $data = array(
            'title'    => 'My Passwords',
            'heading'  => 'My Passwords',
            'subtitle' => 'Modify your password',
        );
        return view('member.profile.password', $data);
    }

    public function update_member_password(Request $e)
    {
        $e->validate(array(
                         'new_password'     => 'required|min:6',
                         'current_password' => 'required',
                         'retype_password'  => 'required|min:6|same:new_password',
                     ));
        $mdetail = \App\Member::select('password')->where('id', session('member_id'))->first();
        if (!password_verify($e->current_password, $mdetail->password)) {
            return redirect()->back()->with('msg', "Please Enter Your Correct Current Password RICK");
        }
        $member           = \App\Member::find(session('member_id'));
        $member->password = password_hash($e->new_password, 1);
        $member->save();
        return redirect()->back()->with('msg', "");
    }


    public function change_pic()
    {
        $data = array(
            'mdetail' => \App\Member::select('avatar')->where('id', session('member_id'))->first(),
        );
        return view('member.profile.change_pic', $data);
    }

    public function save_profile_pic(Request $e)
    {
        $mdetail = \App\Member::select('password', 'avatar')->where('id', session('member_id'))->first();
        if (!password_verify($e->current_password, $mdetail->password)) {
            echo errorrecord('Entered password is wrong');
            return;
        }
        $avatar = $mdetail->avatar;
        if ($e->avatar):
            $optimizerChain = OptimizerChainFactory::create();
            $avatar         = uniqid('', true) . session('member_id') . '.' . $e->avatar->extension();
            $e->avatar->move(storage_path('pics/'), $avatar);
            $logo = app(Image::class)::make(storage_path('pics/' . $avatar))->resize(400, null, function ($constraint) {
                $constraint->aspectRatio();
            });
            $logo->save(storage_path('pics/' . $avatar));
            $optimizerChain->optimize(storage_path('pics/' . $avatar));
            if (!blank($mdetail->avatar)) {
                unlink(storage_path('pics/' . $mdetail->avatar));
            }
            $e->session()->put('member_avtar', $avatar);
        endif;

        DB::table('members')->where('id', session('member_id'))->update(['avatar' => $avatar]);
        echo success('Profile Picture has been updated');
    }

    public function welcome_letter()
    {
        $data = array(
            'title'    => 'My Welcome letter',
            'heading'  => 'My Welcome Letter',
            'subtitle' => 'Welcome to our team',
            'mdetail'  => \App\Member::select('name', 'email', 'phone', 'sponsor', 'created_at')
                                     ->where('id', session('member_id'))->first(),
        );
        return view('member.profile.welcome_letter', $data);
    }

    public function save_profile(Request $e)
    {
        $e->validate(array(
                         'name'             => 'required',
                         'email'            => 'email|nullable',
                         'phone'            => 'numeric|nullable',
                         'current_password' => 'required',
                         'id_proof'         => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                         'address_proof'    => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                     ));
        $member = \App\Member::find(session('member_id'));
        if (!password_verify($e->current_password, $member->password)) {
            return redirect('/profile')->with('msg', "d");
        }

        if ($member->email !== $e->email) {
            $get_email = $this->count_all('members', array('email' => $e->email));
            if ($get_email > 0) {
                return redirect('/profile')->with('msg', "d");
            }
        }
        $id_proof       = $member->id_proof;
        $address_proof  = $member->address_proof;
        $optimizerChain = OptimizerChainFactory::create();
        if ($e->id_proof):
            $id_proof = uniqid('', true) . session('member_id') . '.' . $e->id_proof->extension();
            $e->id_proof->move(storage_path('pics/'), $id_proof);
            $logo = app(Image::class)::make(storage_path('pics/' . $id_proof))
                                     ->resize(600, null, function ($constraint) {
                                         $constraint->aspectRatio();
                                     });
            $logo->save(storage_path('pics/' . $id_proof));
            $optimizerChain->optimize(storage_path('pics/' . $id_proof));
            if (!blank($member->id_proof)) {
                unlink(storage_path('pics/' . $member->id_proof));
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
            if (!blank($member->address_proof)) {
                unlink(storage_path('pics/' . $member->address_proof));
            }
        endif;
        $member->name          = $e->name;
        $member->email         = $e->email;
        $member->phone         = $e->phone;
        $member->id_proof      = $id_proof;
        $member->address_proof = $address_proof;
        $member->save();

        $array = array(
            'address'         => $e->address,
            'city'            => $e->city,
            'pin'             => $e->pin,
            'nominee_name'    => $e->nominee_name,
            'nominee_address' => $e->nominee_address,
            'pan_card'        => $e->pan_card,
            'aadhar'          => $e->aadhar,
            'bank_ac_holder'  => $e->bank_ac_holder,
            'bank_name'       => $e->bank_name,
            'bank_ac_no'      => $e->bank_ac_no,
            'bank_ifsc'       => $e->bank_ifsc,
            'bank_branch'     => $e->bank_branch,
            'btc_address'     => $e->btc_address,
            'upi_address'     => $e->upi_address,
        );
        DB::table('member_profile')->where('id', session('member_id'))->update($array);
        return redirect()->back()->with('msg', "Profile Updated YUSUSFf");
    }

    public function upgrade_network()
    {
        $mdetail = \App\Member::find(session('member_id'));
        if ($mdetail->available_points < env('ECOM_TO_MLM_POINTS'))
            return redirect()->back()
                             ->with('msg',"d");
        $data = array(
            'title'   => 'Upgrade to Network',
            'mdetail' => $mdetail,
        );
        return view('ecommerce.pages.upgrade_to_network');
    }

    public function buy_policy(Request $e)
    {
        $product = \App\Product::find($e->package);
        if (!$product) {
         //   echo msg('Invalid Product Selected', 'danger');
            return;
        }
        $emi_amt   = $product->emi_amount;
        $no_of_emi = $product->no_of_emi;
        $this->generate_emi($e->id, $e->package, $emi_amt, $no_of_emi);
       // echo msg('Successfully Completed. Pay your EMIs now');
    }

    public function generate_emi($user_id, $package, $emi_amt, $no_of_emi)
    {
        $date            = new \DateTime(date('Y-m-d H:i:s'));
        $monthly_array[] = $date->format('Y-m-d');
        for ($i = 0, $iMax = $no_of_emi; $i < $iMax; $i++) {
            $date   = $date->modify('+1 month');
            $month  = $date->format('Y-m-d');
            $emis[] = array(
                'user_id'    => $user_id,
                'product_id' => $package,
                'amount'     => $emi_amt,
                'date'       => $month,
            );
        }
        DB::table('my_emis')->insert($emis);
    }

    public function download_profile()
    {
        $data = array(
            'mdetail'            => \App\Member::find(session('member_id')),
            'profile'            => DB::table('member_profile')->where('id', session('member_id'))->first(),
            'total_balance'      => sum('balance', 'wallet', ['id' => session('member_id')]),
            'total_earning'      => sum('amount', 'earnings', ['userid' => session('member_id')]),
            'total_investments'  => sum('amount', 'investments', ['investor_id' => session('member_id')]),
            'total_withdrawal'   => sum('amount', 'withdrawal_records', ['user_id' => session('member_id')]),
            'total_used_epins'   => DB::table('epins')->where(['owner' => session('member_id')])->where('used_by', '>', 0)->count(),
            'total_unused_epins' => DB::table('epins')->where(['owner' => session('member_id')])->where('used_by', null)->count(),
            'transferred_epins'  => DB::table('epins')->where(['transferred_by' => session('member_id')])->count(),
            'leg_info'           => DB::table('leg_info')->where('id', session('member_id'))->first(),
        );
        $pdf  = Pdf::loadView('member.profile.download_profile', $data);
        $pdf->stream('profile.pdf');
    }

    ######### DANGER
    public function suspend_self(Request $e)
    {
        $member         = \App\Member::find(session('member_id'));
        $member->status = 'Suspended';
        $member->save();
        echo '<script>alert("Your account has been suspended for not providing help in time.")</script>';
        echo script_redirect('/member');
        return;
    }
}
