<?php

namespace App\Http\Controllers;

use App\Exports\DbExport;
use App\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Maatwebsite\Excel\Facades\Excel;
use mysqli;
use Spatie\DbDumper\Compressors\GzipCompressor;
use Spatie\DbDumper\Databases\MySql;
use Spatie\DbDumper\Databases\PostgreSql;
use Spatie\ImageOptimizer\OptimizerChainFactory;

class Admin extends Controller
{

    public function sum($field, $table, $where = 1)
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
    function count_all($table, $where = 1)
    {
        $result = DB::table($table)->where($where)->count('id');
        if (!empty($result)) {
            return $result;
        }
        return 0;
    }
    public function index()
    {


        $net_sale          = DB::table('members')->sum('my_topup');
        $investments       = DB::table('investments')->sum('amount');
        $net_sale_today    = DB::table('members')->where('topup_date', date('Y-m-d'))->sum('my_topup');
        $investments_today = DB::table('investments')->where('date', date('Y-m-d'))->sum('amount');

        $expenses                = $this->sum('amount', 'expenses');
        $withdrawal              = $this->sum('amount', 'withdrawal_records');
        $earnings_today          = $this->sum('amount', 'earnings', ['date' => date('Y-m-d')]);
        $withdrawal_today        = $this->sum('amount', 'withdrawal_records', ['date' => date('Y-m-d')]);
        $wallet_txn_credit_today = $this->sum('amount', 'wallet_txns', ['date' => date('Y-m-d'), 'type' => 'Credit', 'description' => 'Admin Fund Managed.']);
        $wallet_txn_debit_today  = $this->sum('amount', 'wallet_txns', ['date' => date('Y-m-d'), 'type' => 'Debit', 'description' => 'Admin Fund Managed.']);
        $salaries                = $this->sum('amount', 'salaries');


        $data = array(
            'title'         => 'Dashboard',
            'red_members'   => $this->count_all('user_relations', array('status' => 0)),
            'green_members' => $this->count_all('user_relations', array('status' => 1)),
            'todays_signup' => \App\Member::where('created_at', '>=', date('Y-m-d'))->count(),
            'months_signup' => \App\Member::where('created_at', '>=', date('Y-m-01'))
                                          ->where('created_at', '<=', date('Y-m-d 23:59:59'))->count(),
            'net_income'    => $net_sale + $investments,
            'todays_income' => $net_sale_today + $investments_today,
            'expenses'      => $expenses + $withdrawal + $salaries,
            'total_debit'   => $withdrawal_today + $wallet_txn_debit_today,
            'total_credit'  => $earnings_today + $wallet_txn_credit_today,
            'members'       => \App\Member::orderBy('serial', 'DESC')->paginate(7),
        );

        
        return view('admin.dashboard', $data);
    }

    public function login_hostory()
    {
        $data = array(
            'title'  => 'Login History',
            'logins' => DB::table('sessions')
                          ->where(array(
                                      'user_id'   => session('admin_id'),
                                      'user_type' => 'Staff',
                                  ))
                          ->orderBy('id', 'DESC')
                          ->paginate(10),
        );
        return view('admin.login_history', $data);
    }

    public function staff_setting()
    {
        $data = array(
            'title' => 'Account Setting',
            'data'  => Staff::select('email', 'phone')->where('id', session('admin_id'))->first(),
        );

        return view('admin.staff_setting', $data);
    }

    public function save_staff_setting(Request $e)
    {
        $e->validate([
                         'email'            => 'email|required',
                         'phone'            => 'numeric|required',
                         'current_password' => 'required',
                         'password'         => 'nullable|min:5',
                         'retype_password'  => 'nullable|min:5|same:password',
                     ]);
        $count    = select('id', 'staffs', array('email' => $e->email));
        $password = select('password', 'staffs', array('id' => session('admin_id')));
        if ($count !== null && $count !== session('admin_id'))
            return redirect()->back()
                             ->with('msg', '<div class="alert alert-danger"><i class="icon-close"></i> Email ID is already in use.</div>');
        if (!password_verify($e->current_password, $password))
            return redirect()->back()
                             ->with('msg', '<div class="alert alert-danger"><i class="icon-close"></i> Current Password is wrong.</div>');

        $staff        = Staff::find(session('admin_id'));
        $staff->email = $e->email;
        $staff->phone = $e->phone;
        if ($e->password !== '')
            $staff->password = password_hash($e->password, 1);
        $staff->save();
        return redirect()->back()->with('msg', msg('Successfully Changed your setting.', 'success', false));
    }


    public function install(Request $e)
    {
        $array = array(
            'DB_DATABASE'   => $e->name,
            'DB_USERNAME'   => $e->username,
            'DB_PASSWORD'   => $e->password,
            'DB_CONNECTION' => $e->type,
            'DB_HOST'       => $e->host,
            'DB_PORT'       => $e->port,
        );
        $this->setenv($array);
        artisan('migrate:fresh --seed');
        unlink(public_path('/install.php'));
        Storage::delete(Storage::files('products'));
        Storage::delete(Storage::files('pics'));
        Storage::delete(Storage::files('page_img'));
        Storage::delete(Storage::files('receipts'));
        Storage::delete(Storage::files('debugbar'));
        Storage::delete(Storage::files('logs'));
        $this->do_maintenance();
        return redirect('/staff')->with('msg', msg('Installation Successful. Default Username and Password is: <strong>admin</strong>'));
    }

    public function save_business_things(Request $e)
    {
        $e->validate([
                         'developer_password' => 'required',
                         'TOP_ID'             => 'required|numeric',
                     ]);
        if ($e->developer_password !== str_ireplace('www.', '', $_SERVER['HTTP_HOST']) . '@') ## Developer password is domain name followed by @ and without www. example: example.com@
        {
            return redirect()->back()
                             ->with('msg', '<div class="alert alert-danger"><i class="icon-close"></i> Developer Password is wrong.</div>');
        }

        $PG_LIST = "";
        if ($e->Paypal === 'Paypal')
            $PG_LIST .= $e->Paypal . ",";
        if ($e->CoinPayments === 'CoinPayments')
            $PG_LIST .= $e->CoinPayments . ",";
        if ($e->Skrill === 'Stripe')
            $PG_LIST .= $e->Stripe . ",";
        if ($e->Instamojo === 'Instamojo')
            $PG_LIST .= $e->Instamojo . ",";
        if ($e->Paykun === 'Paykun')
            $PG_LIST .= $e->Paykun . ",";
        if ($e->Payumoney === 'Payumoney')
            $PG_LIST .= $e->Payumoney . ",";
        if ($e->Ccavenue === 'Ccavenue')
            $PG_LIST .= $e->Ccavenue . ",";
        if ($e->payTM === 'payTM')
            $PG_LIST .= $e->payTM . ",";
        if ($e->Cashfree === 'Cashfree')
            $PG_LIST .= $e->Cashfree . ",";
        if ($e->Razorpay === 'Razorpay')
            $PG_LIST .= $e->Razorpay . ",";
        if ($e->Block_io === 'Block.io')
            $PG_LIST .= $e->Block_io . ",";
        $PG_LIST = substr($PG_LIST, 0, -1);

        $array = array(
            'TOP_ID'                       => $e->TOP_ID,
            'LEG_NUMBER'                   => $e->LEG_NUMBER,
            'NO_OF_LEVELS'                 => $e->NO_OF_LEVELS,
            'SHOW_PLACEMENT'               => $e->SHOW_PLACEMENT,
            'SHOW_LEG_CHOOSE'              => $e->SHOW_LEG_CHOOSE,
            'AUTOPOOL'                     => $e->AUTOPOOL,
            'MEMBER_AUTOPOOL'              => $e->MEMBER_AUTOPOOL,
            'SHOW_REG_PRODUCT'             => $e->SHOW_REG_PRODUCT,
            'PV_BASED_PLAN'                => $e->PV_BASED_PLAN,
            'AUTO_DELIVER_REG_PRODUCT'     => $e->AUTO_DELIVER_REG_PRODUCT,
            'REG_FEE'                      => $e->REG_FEE,
            'EMI_ENABLE'                   => $e->EMI_ENABLE,
            'INCOME_ON_EMI_ENABLE'         => $e->INCOME_ON_EMI_ENABLE,
            'MIN_WITHDRAW_AMOUNT'          => $e->MIN_WITHDRAW_AMOUNT,
            'MAX_WITHDRAW_AMOUNT'          => $e->MAX_WITHDRAW_AMOUNT,
            'ALLOW_INCOME_FOR_PARTIAL_FEE' => $e->ALLOW_INCOME_FOR_PARTIAL_FEE,
            'LEVEL_EARNING_PLACEMENT_WISE' => $e->LEVEL_EARNING_PLACEMENT_WISE,
            'FEE_TYPE'                     => $e->FEE_TYPE,
            'ENABLE_FRANCHISEE'            => $e->ENABLE_FRANCHISEE,
            'SUSPENDED_ID_INCOME'          => $e->SUSPENDED_ID_INCOME,
            'ENABLE_EPIN'                  => $e->ENABLE_EPIN,
            'ENABLE_PG'                    => $e->ENABLE_PG,
            'ENABLE_TOPUP'                 => $e->ENABLE_TOPUP,
            'SHOW_CATEGORY_WISE_TOPUP'     => $e->SHOW_CATEGORY_WISE_TOPUP,
            'TOPUP_FROM_WALLET'            => $e->TOPUP_FROM_WALLET,
            'ENABLE_DEALER_REPURCHASE'     => $e->ENABLE_DEALER_REPURCHASE,
            'ENABLE_MEMBER_REPURCHASE'     => $e->ENABLE_MEMBER_REPURCHASE,
            'ENABLE_REPURCHASE'            => $e->ENABLE_REPURCHASE,
            'ENABLE_PRODUCT'               => $e->ENABLE_PRODUCT,
            'ENABLE_CMS'                   => $e->ENABLE_CMS,
            'ENABLE_LOAN_ON_PAYOUT'        => $e->ENABLE_LOAN_ON_PAYOUT,
            'ENABLE_UPGRADE_PLAN'          => $e->ENABLE_UPGRADE_PLAN,
            'ENABLE_LMS'                   => $e->ENABLE_LMS,
            'ENABLE_BOARD_PLAN'            => $e->ENABLE_BOARD_PLAN,
            'BOARD_LEG_NUMBER'             => $e->BOARD_LEG_NUMBER,
            'BOARD_EXIT_LEVEL'             => $e->BOARD_EXIT_LEVEL,
            'ENABLE_COUPON'                => $e->ENABLE_COUPON,
            'UPGRADE_LEG_NUMBER'           => $e->UPGRADE_LEG_NUMBER,
            'UPGRADE_ENTRY_LEVEL'          => $e->UPGRADE_ENTRY_LEVEL,
            'ENABLE_ADVT_PLAN'             => $e->ENABLE_ADVT_PLAN,
            'ENABLE_RECHARGE'              => $e->ENABLE_RECHARGE,
            'ENABLE_SURVEY'                => $e->ENABLE_SURVEY,
            'ENABLE_DONATION_PLAN'         => $e->ENABLE_DONATION_PLAN,
            'DONATION_PLAN_TYPE'           => $e->DONATION_PLAN_TYPE,
            'ENABLE_ECOM_FRONTEND'         => $e->ENABLE_ECOM_FRONTEND,
            'ECOM_FIRST_PORTAL'            => $e->ECOM_FIRST_PORTAL,
            'ECOM_TO_MLM_POINTS'           => $e->ECOM_TO_MLM_POINTS,
            'ENABLE_REWARDS'               => $e->ENABLE_REWARDS,
            'DISABLE_REGISTRATION'         => $e->DISABLE_REGISTRATION,
            'CROSS_WALLET_TRANSFER'        => $e->CROSS_WALLET_TRANSFER,
            'CROSS_WALLET_TRANSFER_CHARGE' => $e->CROSS_WALLET_TRANSFER_CHARGE,
            'NEED_KYC'                     => $e->NEED_KYC,
            'SHOW_USERNAME'                => $e->SHOW_USERNAME,
            'NEED_NOMINEE'                 => $e->NEED_NOMINEE,
            'ALLOW_WITHDRAW'               => $e->ALLOW_WITHDRAW,
            'ALLOW_EPIN_GENERATE'          => $e->ALLOW_EPIN_GENERATE,
            'ALLOW_RED_ID_WITHDRAW'        => $e->ALLOW_RED_ID_WITHDRAW,
            'QUEUE_ENABLE'                 => $e->QUEUE_ENABLE,
            'QUEUE_CONNECTION'             => $e->QUEUE_CONNECTION,
            'APP_DEBUG'                    => $e->APP_DEBUG,
            'APP_MAINTENANCE'              => $e->APP_MAINTENANCE,
            'SESSION_DRIVER'               => $e->SESSION_DRIVER,
            'CACHE_DRIVER'                 => $e->CACHE_DRIVER,
            'REDIS_HOST'                   => $e->REDIS_HOST,
            'REDIS_PASSWORD'               => $e->REDIS_PASSWORD,
            'REDIS_PORT'                   => $e->REDIS_PORT,
            'INVESTMENT_PLAN'              => $e->INVESTMENT_PLAN,
            'SHOW_TOPUP_ALWAYS'            => $e->SHOW_TOPUP_ALWAYS,
            'MATCHING_INCOME'              => $e->MATCHING_INCOME,
            'DEDUCTION_PAIRS'              => $e->DEDUCTION_PAIRS,
            'SPONSOR_INCOME_TYPE'          => $e->SPONSOR_INCOME_TYPE,
            'MATCHING_FIRST_RATIO'         => $e->MATCHING_FIRST_RATIO,
            'MATCHING_SECOND_RATIO'        => $e->MATCHING_SECOND_RATIO,
            'CLOSING_TYPE'                 => $e->CLOSING_TYPE,
            'CAPPING'                      => $e->CAPPING,
            'SPONSOR_INCOME_PERCENT'       => $e->SPONSOR_INCOME_PERCENT,
            'SPONSOR_INCOME_NAME'          => $e->SPONSOR_INCOME_NAME,
            'AUTOMATED_WITHDRAW'           => $e->AUTOMATED_WITHDRAW,
            'FUND_CREDIT'                  => $e->FUND_CREDIT,
            'TDS'                          => $e->TDS,
            'SERVICE_CHARGE'               => $e->SERVICE_CHARGE,
            'OTHER_CHARGE'                 => $e->OTHER_CHARGE,
            'ENABLE_EARNING'               => $e->ENABLE_EARNING,
            'ALLOW_RED_ID_INCOME'          => $e->ALLOW_RED_ID_INCOME,
            'ALLOW_FUND_TRANSFER'          => $e->ALLOW_FUND_TRANSFER,
            'PAYOUT_GATEWAY'               => $e->PAYOUT_GATEWAY,
            'PG_LIST'                      => $PG_LIST,
            'PG_FEE'                       => $e->PG_FEE,
            'COINPAYMENTS_MERCHANT_ID'     => $e->COINPAYMENTS_MERCHANT_ID,
            'COINPAYMENTS_PUBLIC_KEY'      => $e->COINPAYMENTS_PUBLIC_KEY,
            'COINPAYMENTS_PRIVATE_KEY'     => $e->COINPAYMENTS_PRIVATE_KEY,
            'COINPAYMENTS_IPN_SECRET'      => $e->COINPAYMENTS_IPN_SECRET,
            'COINPAYMENTS_IPN_URL'         => $e->COINPAYMENTS_IPN_URL,
            'API_KEY'                      => $e->API_KEY,
            'AUTH_TOKEN'                   => $e->AUTH_TOKEN,
            'PAYKUN_MRCNT_ID'              => $e->PAYKUN_MRCNT_ID,
            'PAYKUN_ACCESS_TOKEN'          => $e->PAYKUN_ACCESS_TOKEN,
            'PAYKUN_API_KEY'               => $e->PAYKUN_API_KEY,
            'RAZOR_KEY_ID'                 => $e->RAZOR_KEY_ID,
            'RAZOR_SECRET'                 => $e->RAZOR_SECRET,
            'RAZORX_AC_NO'                 => $e->RAZORX_AC_NO,
            'PAYPAL_CURRENCY'              => $e->PAYPAL_CURRENCY,
            'PAYPAL_LIVE_API_USERNAME'     => $e->PAYPAL_LIVE_API_USERNAME,
            'PAYPAL_LIVE_API_PASSWORD'     => $e->PAYPAL_LIVE_API_PASSWORD,
            'PAYPAL_LIVE_API_SECRET'       => $e->PAYPAL_LIVE_API_SECRET,
            'PAYU_KEY'                     => $e->PAYU_KEY,
            'PAYU_SALT'                    => $e->PAYU_SALT,
            'CCAVENUE_MRCNT_ID'            => $e->CCAVENUE_MRCNT_ID,
            'CCAVENUE_WRKN_KEY'            => $e->CCAVENUE_WRKN_KEY,
            'CCAVENUE_ACCESS_KEY'          => $e->CCAVENUE_ACCESS_KEY,
            'CASHFREE_APP_ID'              => $e->CASHFREE_APP_ID,
            'CASHFREE_SECRET'              => $e->CASHFREE_SECRET,
            'CASHFREE_CLIENT_SECRET'       => $e->CASHFREE_CLIENT_SECRET,
            'CASHFREE_CLIENT_ID'           => $e->CASHFREE_CLIENT_ID,
            'PAYTM_MERCHANT_ID'            => $e->PAYTM_MERCHANT_ID,
            'PAYTM_MERCHANT_KEY'           => $e->PAYTM_MERCHANT_KEY,
            'HYPTO_API'                    => $e->HYPTO_API,
            'PAYRACKS_API'                 => $e->PAYRACKS_API,
            'BLOCK_IO_API'                 => $e->BLOCK_IO_API,
            'BLOCK_IO_PIN'                 => $e->BLOCK_IO_PIN,
            'BLOCK_IO_CURRENCY'            => $e->BLOCK_IO_CURRENCY,
            'BANKOPEN_API'                 => $e->BANKOPEN_API,
            'BANKOPEN_SECRET'              => $e->BANKOPEN_SECRET,
        );
        $extra = '';
        if ($e->TOP_ID !== env('TOP_ID')) {
            $extra = ' <strong class="badge badge-danger">You have changed Top ID. Please <a href="' . url('/db-tool') . '" class="text-decoration-underline text-white">reset Database</a>, else errors may occur.</strong>';
        }
        $this->setenv($array);
        if ($e->APP_MAINTENANCE == 'TRUE') {
            artisan('down --secret="secret');
        } else {
            artisan('up');
        }
        return redirect()->back()->with('msg', msg('Successfully Changed your setting.' . $extra, 'success', false));
    }

    private function setenv(array $values)
    {

        $envFile = app()->environmentFilePath();
        $str     = file_get_contents($envFile);

        if (count($values) > 0) {
            foreach ($values as $envKey => $envValue) {
                if (is_int($envValue) || $envValue == '' || ($envValue !== 'TRUE' && $envValue !== 'true' && $envValue !== 'false' && $envValue !== 'null' && $envValue !== 'NULL' && $envValue !== 'FALSE')) {
                    $envValue = "\"$envValue\"";
                }
                $str               .= "\n"; // In case the searched variable is in the last line without \n
                $keyPosition       = strpos($str, "{$envKey}=");
                $endOfLinePosition = strpos($str, "\n", $keyPosition);
                $oldLine           = substr($str, $keyPosition, $endOfLinePosition - $keyPosition);

                // If key does not exist, add it
                if (!$keyPosition || !$endOfLinePosition || !$oldLine) {
                    $str .= "{$envKey}={$envValue}\n";
                } else {
                    $str = str_replace($oldLine, "{$envKey}={$envValue}", $str);
                }

            }
        }

        $str = substr($str, 0, -1);
        $str = ltrim($str);
        $str = rtrim($str);
        if (!file_put_contents($envFile, $str)) {
            return false;
        }
        return true;
    }

    public function save_basic_things(Request $e)
    {
        $e->validate([
                         'current_password' => 'required',
                         'logo'             => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webpp,JPG,PNG|max:2048',
                         'favicon'          => 'nullable|image|mimes:png|max:2048',
                     ]);
        $password = select('password', 'staffs', array('id' => session('admin_id')));
        if (!password_verify($e->current_password, $password))
            return redirect()->back()
                             ->with('msg', '<div class="alert alert-danger"><i class="icon-close"></i> Current Password is wrong.</div>');

        # Upload Site Logo
        $optimizerChain = OptimizerChainFactory::create();
        if ($e->logo):
            $e->logo->move(public_path('images'), 'logo.png');
            $logo = app(Image::class)::make(public_path('images/logo.png'))->resize(300, null, function ($constraint) {
                $constraint->aspectRatio();
            });
            $logo->save(public_path('images/logo.png'));
            $optimizerChain->optimize(public_path('images/logo.png'));
        endif;
        if ($e->favicon):
            $e->favicon->move(public_path('images'), 'favicon.png');
            $favicon = app(Image::class)::make(public_path('images/favicon.png'))
                                        ->resize(200, null, function ($constraint) {
                                            $constraint->aspectRatio();
                                        });
            $favicon->save(public_path('images/favicon.png'));
            $optimizerChain->optimize(public_path('images/favicon.png'));
        endif;
        #############

        $array = array(
            'LEGAL_NAME'            => $e->LEGAL_NAME,
            'APP_NAME'              => $e->APP_NAME,
            'GST_NO'                => $e->GST_NO,
            'COMPANY_PAN'           => $e->COMPANY_PAN,
            'ADDRESS'               => $e->ADDRESS,
            'COMPANY_PHONE'         => $e->COMPANY_PHONE,
            'COMPANY_MAIL'          => $e->COMPANY_MAIL,
            'SYSTEM_MAIL'           => $e->SYSTEM_MAIL,
            'MAIL_HOST'             => $e->MAIL_HOST,
            'MAIL_PORT'             => $e->MAIL_PORT,
            'MAIL_USERNAME'         => $e->MAIL_USERNAME,
            'MAIL_PASSWORD'         => $e->MAIL_PASSWORD,
            'CURRENCY_ISO'          => $e->CURRENCY_ISO,
            'CURRENCY_SIGN'         => $e->CURRENCY_SIGN,
            'ID_EXT'                => $e->ID_EXT,
            'OTP_VERIFICATION'      => trim($e->OTP_VERIFICATION) ? $e->OTP_VERIFICATION : "FALSE",
            'OTP_LOGIN'             => trim($e->OTP_LOGIN) ? $e->OTP_LOGIN : "FALSE",
            'EMAIL_ENABLE'          => trim($e->EMAIL_ENABLE) ? $e->EMAIL_ENABLE : "FALSE",
            'SMS_ENABLE'            => trim($e->SMS_ENABLE) ? $e->SMS_ENABLE : "FALSE",
            'NOTIFY_ADMIN_VIA_SMS'  => trim($e->NOTIFY_ADMIN_VIA_SMS) ? $e->NOTIFY_ADMIN_VIA_SMS : "FALSE",
            'NOTIFY_MEMBER_VIA_SMS' => trim($e->NOTIFY_MEMBER_VIA_SMS) ? $e->NOTIFY_MEMBER_VIA_SMS : "FALSE",
            'NOTIFY_DEALER_VIA_SMS' => trim($e->NOTIFY_DEALER_VIA_SMS) ? $e->NOTIFY_DEALER_VIA_SMS : "FALSE",
            'SMS_PASS_RESET'        => trim($e->SMS_PASS_RESET) ? $e->SMS_PASS_RESET : "FALSE",
            'TXN_SMS'               => trim($e->TXN_SMS) ? $e->TXN_SMS : "FALSE",
            'SMS_API'               => $e->SMS_API,
        );
        $this->setenv($array);
        clearstatcache();
        return redirect()->back()->with('msg', msg('Successfully Changed your setting.', 'success', false));
    }

    public function save_website_setup(Request $e)
    {
        $e->validate([
                         'current_password' => 'required',
                     ]);
        $password = select('password', 'staffs', array('id' => session('admin_id')));
        if (!password_verify($e->current_password, $password))
            return redirect()->back()
                             ->with('msg', '<div class="alert alert-danger"><i class="icon-close"></i> Current Password is wrong.</div>');
        $array = array(
            'SITE_PICKUP_LINE'       => $e->SITE_PICKUP_LINE,
            'SITE_PICKUP_LINE_LEFT'  => $e->SITE_PICKUP_LINE_LEFT,
            'HOMEPAGE_TITLE'         => $e->HOMEPAGE_TITLE,
            'GOOGLE_ANALYTICS_ID'    => $e->GOOGLE_ANALYTICS_ID,
            'SITE_CUSTOM_HTML_PAGES' => $e->SITE_CUSTOM_HTML_PAGES,
            'SITE_CUSTOM_HTML_HOME'  => $e->SITE_CUSTOM_HTML_HOME,
            'FB_ADDRESS'             => $e->FB_ADDRESS,
            'TWITTER_ADDRESS'        => $e->TWITTER_ADDRESS,
            'INSTAGRAM_ADDRESS'      => $e->INSTAGRAM_ADDRESS,
        );

        $this->setenv($array);
        clearstatcache();

        return redirect()->back()->with('msg', msg('Successfully Changed your setting.', 'success', false));
    }

    public function save_api(Request $e)
    {
        $array = array(
            'RECH_API'    => $e->recharge_api,
            'DTH_API'     => $e->dth_api,
            'UTILITY_API' => $e->utility_api,
        );

        $this->setenv($array);
        clearstatcache();

        return redirect()->back()->with('msg', msg('Successfully saved API.', 'success', true));
    }

    public function save_api_things(Request $e)
    {
        $e->validate([
                         'current_password' => 'required',
                     ]);
        $password = select('password', 'staffs', array('id' => session('admin_id')));
        if (!password_verify($e->current_password, $password))
            return redirect()->back()
                             ->with('msg', '<div class="alert alert-danger"><i class="icon-close"></i> Current Password is wrong.</div>');
        $api = env('APP_API_KEY');
        if ($e->gen_key == 1)
            $api = md5(encrypt($_SERVER['HTTP_HOST'] . time(), false));
        $array = array(
            'APP_API_KEY' => strip_tags($api),
            'API_IP'      => strip_tags($e->API_IP),
        );
        $this->setenv($array);
        clearstatcache();
        return redirect()->back()->with('msg', msg('Successfully Updated API Key detail..', 'success', false));
    }

    public function import_db(Request $e)
    {
        $e->validate([
                         'current_password' => 'required',
                         'raw_file'         => 'required|mimes:sql,txt,xls,xlsx',
                     ]);
        $password = select('password', 'staffs', array('id' => session('admin_id')));
        if (!password_verify($e->current_password, $password)) {
            return redirect()->back()
                             ->with('msg', '<div class="alert alert-danger"><i class="icon-close"></i> Current Password is wrong.</div>');
        }
        if ($e->type === 'sql' && env('DB_CONNECTION') === 'mysql') {
            $lines  = file_get_contents($_FILES['raw_file']['tmp_name']);
            $mysqli = new mysqli(env('DB_HOST'), env('DB_USERNAME'), env('DB_PASSWORD'), env('DB_DATABASE'));
            $mysqli->multi_query($lines);

        } else {
            $path         = $e->file('raw_file')->getRealPath();
            $data         = $collection = (new FastExcel())->import($path);
            $insert_data  = [];
            $insert_data2 = [];
            if ($data->count() > 0) {
                foreach ($data->toArray() as $key => $value) {
                    foreach ($value as $row) {
                        $insert_data[] = array(
                            'id'       => $row['id'],
                            'sponsor'  => $row['sponsor'],
                            'position' => $row['position'],
                            'name'     => $row['name'],
                            'username' => $row['username'],
                            'password' => $row['password'],
                            'email'    => $row['email'],
                            'phone'    => $row['phone'],
                            'leg'      => $row['leg'],
                            'A'        => $row['A'],
                            'B'        => $row['B'],
                            'C'        => $row['C'],
                            'D'        => $row['D'],
                            'E'        => $row['E'],
                            'F'        => $row['F'],
                            'G'        => $row['G'],
                            'H'        => $row['H'],
                            'I'        => $row['I'],
                            'J'        => $row['J'],
                        );
                    }
                    $insert_data2[] = array(
                        'id'        => $row['id'],
                        'parent_id' => $row['position'],
                    );
                    $insert_data3[] = array(
                        'id' => $row['id'],
                    );
                }

                if (!empty($insert_data)) {
                    DB::table('members')->insert($insert_data);
                    DB::table('leg_info')->insert($insert_data2);
                    DB::table('user_relations')->insert($insert_data2);
                    DB::table('wallet')->insert($insert_data3);
                    DB::table('member_profile')->insert($insert_data3);
                }
            }

        }
        Cache::flush();
        return redirect()->back()->with('msg', msg('Database Imported Successfully.'));
    }

    public function export_db(Request $e)
    {
        $e->validate([
                         'current_password' => 'required',
                     ]);
        $password = select('password', 'staffs', array('id' => session('admin_id')));
        if (!password_verify($e->current_password, $password)) {
            return redirect()->back()
                             ->with('msg', '<div class="alert alert-danger"><i class="icon-close"></i> Current Password is wrong.</div>');
        }
        if ($e->type === 'sql' && env('DB_CONNECTION') === 'mysql') {
            MySql::create()->setDbName(env('DB_DATABASE'))
                 ->setUserName(env('DB_USERNAME'))
                 ->setPassword(env('DB_PASSWORD'))
                 ->addExtraOption('--add-drop-database')
                 ->dumpToFile(storage_path() . '/sql_backup.sql');
            return response()->download(storage_path() . '/sql_backup.sql');
        }

        if ($e->type === 'sql' && env('DB_CONNECTION') === 'pgsql') {
            PostgreSql::create()
                      ->setDbName(env('DB_DATABASE'))
                      ->setUserName(env('DB_USERNAME'))
                      ->setPassword(env('DB_PASSWORD'))
                      ->addExtraOption('--add-drop-database')
                      ->dumpToFile(storage_path() . '/sql_backup.sql');
            return response()->download(storage_path() . '/sql_backup.sql');
        }

        if ($e->tables === 'members') {
            $data     = \App\Member::query()
                                   ->leftJoin('member_profile', 'member_profile.id', '=', 'members.id')
                                   ->select('members.id', 'members.name', 'members.sponsor', 'members.position', 'members.email', 'members.phone', 'member_profile.address', 'members.my_topup', 'members.created_at', 'members.A', 'members.B', 'members.C', 'members.D', 'members.E', 'members.F', 'members.G', 'members.H', 'members.I', 'members.J');
            $headings = [
                'ID',
                'Name',
                'Sponsor ID',
                'Placement ID',
                'Email ID',
                'Phone No',
                'Address',
                'Topup/Investment',
                'Registration Date',
                'A',
                'B',
                'C',
                'D',
                'E',
                'F',
                'G',
                'H',
                'I',
                'J',
            ];
        }
        if ($e->tables === 'staffs') {
            $data     = Staff::query()
                             ->select('id', 'branch_id', 'name', 'email', 'phone', 'ac_no', 'ifsc', 'address', 'emergency_contact', 'created_at');
            $headings = [
                'ID',
                'Branch ID',
                'Name',
                'Email',
                'Phone',
                'Bank A/C No',
                'IFSC/Swipe Code',
                'Address',
                'emergency_contact',
                'Join Date',
            ];
        }
        if ($e->tables === 'franchisees') {
            $data     = \App\Franchisee::query()
                                       ->select('id', 'parent', 'business_name', 'owner_name', 'email', 'phone', 'address', 'pin', 'state', 'country', 'created_at');
            $headings = [
                'ID',
                'Parent ID',
                'Business Name',
                'Owner Name',
                'Email',
                'Phone',
                'Address',
                'Pin/Zip',
                'State',
                'Country',
                'Create Date',
            ];
        }
        if ($e->tables === 'earnings') {
            $data     = \App\Earning::query()->select('id', 'userid', 'amount', 'type', 'usertype', 'refid', 'date');
            $headings = [
                'SN',
                'Member ID',
                'Amount (' . env('CURRENCY_ISO') . ')',
                'Earning Type',
                'User Type',
                'Referral ID',
                'Date',
            ];
        }
        if ($e->tables === 'balance') {
            $data     = \App\Wallet::query()->select('id', 'balance', 'type');
            $headings = [
                'Member ID',
                'Amount (' . env('CURRENCY_ISO') . ')',
                'Wallet Type',
            ];
        }
        if ($e->tables === 'products') {
            $data     = \App\Product::query()
                                    ->select('id', 'category', 'name', 'type', 'mrp_amount', 'dealer_price', 'amount', 'bv', 'gst', 'reward_points', 'available_qty', 'sold_qty', 'direct_income', 'level_income', 'matching_income', 'capping', 'dealer_commission');
            $headings = [
                'Product ID',
                'Category ID',
                'Name',
                'Type',
                'MRP Amount',
                'Dealer Price',
                'Member Price',
                'PV/BV',
                'GST/Tax',
                'Reward Points',
                'Available Qty',
                'Sold Qty',
                'Direct Income',
                'Level Income',
                'Matching Income',
                'Capping',
                'Dealer Commission',
            ];
        }
        return Excel::download(new DbExport($data, $headings), 'excel_database_backup.xlsx');
        //return redirect()->back()->with('msg', msg('Database Exported Successfully.'));
    }

    public function reset_db(Request $e)
    {
        $e->validate([
                         'developer_password' => 'required',
                     ]);
        if ($e->developer_password !== str_ireplace('www.', '', $_SERVER['HTTP_HOST']) . '@') ## Developer password is domain name followed by @ and without www. example: example.com@
        {
            return redirect()->back()
                             ->with('msg', '<div class="alert alert-danger"><i class="icon-close"></i> Developer Password is wrong.</div>');
        }
        if ($e->type === 'sql' && env('DB_CONNECTION') === 'mysql') {
            MySql::create()->setDbName(env('DB_DATABASE'))
                 ->setUserName(env('DB_USERNAME'))
                 ->setPassword(env('DB_PASSWORD'))
                 ->addExtraOption('--add-drop-database')
                 ->useCompressor(new GzipCompressor())
                 ->dumpToFile(storage_path() . '/sql_backup.' . date('Y-m-d H:i:s') . '.sql.gz');
        }

        if ($e->type === 'sql' && env('DB_CONNECTION') === 'pgsql') {
            PostgreSql::create()
                      ->setDbName(env('DB_DATABASE'))
                      ->setUserName(env('DB_USERNAME'))
                      ->setPassword(env('DB_PASSWORD'))
                      ->addExtraOption('--add-drop-database')
                      ->useCompressor(new GzipCompressor())
                      ->dumpToFile(storage_path() . '/sql_backup.' . date('Y-m-d H:i:s') . '.sql.gz');
        }

        if ($e->tables === '*') {
            Storage::delete(Storage::files('products'));
            Storage::delete(Storage::files('pics'));
            Storage::delete(Storage::files('page_img'));
            Storage::delete(Storage::files('receipts'));
            Storage::delete(Storage::files('debugbar'));
            Storage::delete(Storage::files('logs'));
            artisan('migrate:fresh --seed');
        } else if ($e->tables === 'members') {
            DB::table('members')->delete();
            DB::table('upgrade_plans')->where('id', '!=', env('TOP_ID'))->delete();
            DB::table('upgrade_plans')->where('id', env('TOP_ID'))->update([
                                                                               'A'       => 0,
                                                                               'B'       => 0,
                                                                               'C'       => 0,
                                                                               'D'       => 0,
                                                                               'E'       => 0,
                                                                               'F'       => 0,
                                                                               'G'       => 0,
                                                                               'H'       => 0,
                                                                               'I'       => 0,
                                                                               'J'       => 0,
                                                                               'total_a' => 0,
                                                                               'total_b' => 0,
                                                                               'total_c' => 0,
                                                                               'paid_a'  => 0,
                                                                               'paid_b'  => 0,
                                                                           ]);
            DB::table('product_rois')->delete();
            DB::table('sale_orders')->delete();
            DB::table('sessions')->delete();
            DB::table('txns')->delete();
            DB::table('withdrawals')->delete();
            DB::table('withdrawal_records')->delete();
            DB::table('system_jobs')->delete();
            DB::table('investments')->delete();
            DB::table('rewards')->delete();
            DB::table('donations')->delete();
            DB::table('my_emis')->delete();
            DB::table('ph_pending_commitments')->delete();
            DB::table('epins')->where('owner', '!=', env('TOP_ID'))->delete();
            artisan('db:seed --class=MemberSeeder');
            Storage::delete(Storage::files('pics'));
            Storage::delete(Storage::files('receipts'));
        } else if ($e->tables === 'earnings') {
            DB::table('earnings')->truncate();
        } else if ($e->tables === 'epins') {
            DB::table('epins')->truncate();
        } else if ($e->tables === 'products') {
            DB::table('products')->truncate();
            Storage::delete(Storage::files('products'));
        } else if ($e->tables === 'dealers') {
            DB::table('franchisees')->delete();
        } else {
            return redirect()->back()->with('msg', msg('Please select an option to reset database.', 'danger'));
        }
        Cache::flush();
        return redirect()->back()->with('msg', msg('Database Reset Completed.'));
    }

    public function conditional_income()
    {
        $data = array(
            'title'   => 'Manage Conditional Incomes',
            'incomes' => DB::table('conditional_income')->orderBy('id', 'ASC')->paginate(10),
        );
        return view('admin.setup.conditional_income', $data);
    }

    public function rank_setting()
    {
        $data = array(
            'title' => 'Manage Member Ranks',
            'ranks' => DB::table('member_ranks')->orderBy('id', 'ASC')->paginate(10),
        );
        return view('admin.setup.rank_setting', $data);
    }

    public function delete_income($id)
    {
        DB::table('conditional_income')->where('id', $id)->delete();
        echo success('Income Setting Deleted successfully.');
    }

    public function edit_income($id)
    {
        $data = array(
            'title'  => 'Edit Conditional Incomes',
            'income' => DB::table('conditional_income')->where('id', $id)->first(),
        );
        return view('admin.setup.edit_conditional_income', $data);
    }

    public function create_income(Request $e)
    {
        if (blank($e->amount)) {
            echo errorrecord('Earning Amount field is required');
            return;
        }
        DB::table('conditional_income')->insert(array(
                                                    'income_name'     => $e->income_name,
                                                    'amount'          => $e->amount,
                                                    'base_condition'  => $e->base_condition,
                                                    'direct_required' => $e->direct_required,
                                                    'counting'        => $e->counting,
                                                    'level'           => $e->level,
                                                    'total_a'         => $e->A,
                                                    'total_b'         => $e->B,
                                                    'total_c'         => $e->C,
                                                    'total_d'         => $e->D,
                                                    'total_e'         => $e->E,
                                                    'total_f'         => $e->F,
                                                    'total_g'         => $e->G,
                                                    'total_h'         => $e->H,
                                                    'total_i'         => $e->I,
                                                    'total_j'         => $e->J,
                                                    'duration'        => $e->duration,
                                                ));

        echo success('Income Created Successfully.');
        echo script_redirect(url('/conditional-income'));
    }

    public function create_rank(Request $e)
    {
        if (blank($e->rank_name)) {
            echo errorrecord('Rank Name field is required');
            return;
        }
        DB::table('member_ranks')->insert(array(
                                              'rank_name'       => $e->rank_name,
                                              'base_condition'  => $e->base_condition,
                                              'direct_required' => $e->direct_required,
                                              'counting'        => $e->counting,
                                              'total_a'         => $e->A,
                                              'total_b'         => $e->B,
                                              'total_c'         => $e->C,
                                              'total_d'         => $e->D,
                                              'total_e'         => $e->E,
                                              'total_f'         => $e->F,
                                              'total_g'         => $e->G,
                                              'total_h'         => $e->H,
                                              'total_i'         => $e->I,
                                              'total_j'         => $e->J,
                                              'duration'        => $e->duration,
                                          ));

        echo success('Rank Created Successfully.');
        echo script_redirect(url('/rank-setting'));
    }


    public function reward_settings()
    {
        $data = array(
            'title'    => 'Manage Rewards',
            'settings' => DB::table('reward_setting')->orderBy('id', 'ASC')->paginate(10),
        );
        return view('admin.setup.reward_setting', $data);
    }

    public function delete_reward($id)
    {
        DB::table('reward_setting')->where('id', $id)->delete();
        $image = select('reward_image', 'reward_setting', array('id' => $id));
        unlink(storage_path('/pics/' . $image));
        echo success('Reward Setting Deleted successfully.');
    }

    public function delete_rank($id)
    {
        DB::table('member_ranks')->where('id', $id)->delete();
        echo success('Member Rank has been Deleted successfully.');
    }

    public function edit_reward($id)
    {
        $data = array(
            'title'  => 'Edit Reward Settings',
            'reward' => DB::table('reward_setting')->where('id', $id)->first(),
        );
        return view('admin.setup.edit_reward_setting', $data);
    }

    public function edit_rank($id)
    {
        $data = array(
            'title' => 'Edit Rank Settings',
            'rank'  => DB::table('member_ranks')->where('id', $id)->first(),
        );
        return view('admin.setup.edit_rank_setting', $data);
    }

    public function create_reward(Request $e)
    {
        if (blank($e->reward_name)) {
            echo errorrecord('Reward Name field is required');
            return;
        }
        if (!is_int($e->A) && !blank($e->A)) {
            echo errorrecord('Counting of particular leg field must be Numeric');
            return;
        }
        $image = null;
        if ($e->image):
            $optimizerChain = OptimizerChainFactory::create();
            $avatar         = uniqid('', true) . session('admin_id') . '.' . $e->image->extension();
            $e->image->move(storage_path('pics/'), $avatar);
            $logo = app(Image::class)::make(storage_path('pics/' . $avatar))->resize(500, null, function ($constraint) {
                $constraint->aspectRatio();
            });
            $logo->save(storage_path('pics/' . $avatar));
            $optimizerChain->optimize(storage_path('pics/' . $avatar));
            $image = $avatar;
        endif;
        DB::table('reward_setting')->insert(array(
                                                'reward_name'     => $e->reward_name,
                                                'reward_image'    => $image,
                                                'amount'          => $e->amount,
                                                'base_condition'  => $e->base_condition,
                                                'direct_required' => $e->direct_required,
                                                'counting'        => $e->counting,
                                                'level'           => $e->level,
                                                'total_a'         => $e->A,
                                                'total_b'         => $e->B,
                                                'total_c'         => $e->C,
                                                'total_d'         => $e->D,
                                                'total_e'         => $e->E,
                                                'total_f'         => $e->F,
                                                'total_g'         => $e->G,
                                                'total_h'         => $e->H,
                                                'total_i'         => $e->I,
                                                'total_j'         => $e->J,
                                                'duration'        => $e->duration,
                                            ));

        echo success('Reward Created Successfully.');
        echo script_redirect(url('/reward-setting'));
    }

    public function save_reward(Request $e)
    {
        if (blank($e->reward_name)) {
            echo errorrecord('Reward Name field is required');
            return;
        }
        if (!is_int($e->A) && !blank($e->A)) {
            echo errorrecord('Counting of particular leg field must be Numeric');
            return;
        }
        $image = $e->image_file;
        if ($e->image):
            $optimizerChain = OptimizerChainFactory::create();
            $avatar         = uniqid('', true) . session('admin_id') . '.' . $e->image->extension();
            $e->image->move(storage_path('pics/'), $avatar);
            $logo = app(Image::class)::make(storage_path('pics/' . $avatar))->resize(500, null, function ($constraint) {
                $constraint->aspectRatio();
            });
            $logo->save(storage_path('pics/' . $avatar));
            $optimizerChain->optimize(storage_path('pics/' . $avatar));
            $image = $avatar;
            unlink(storage_path('/pics/' . $e->image_file));
        endif;
        DB::table('reward_setting')->where('id', $e->id)->update(array(
                                                                     'reward_name'     => $e->reward_name,
                                                                     'reward_image'    => $image,
                                                                     'amount'          => $e->amount,
                                                                     'base_condition'  => $e->base_condition,
                                                                     'direct_required' => $e->direct_required,
                                                                     'counting'        => $e->counting,
                                                                     'level'           => $e->level,
                                                                     'total_a'         => $e->A,
                                                                     'total_b'         => $e->B,
                                                                     'total_c'         => $e->C,
                                                                     'total_d'         => $e->D,
                                                                     'total_e'         => $e->E,
                                                                     'total_f'         => $e->F,
                                                                     'total_g'         => $e->G,
                                                                     'total_h'         => $e->H,
                                                                     'total_i'         => $e->I,
                                                                     'total_j'         => $e->J,
                                                                     'duration'        => $e->duration,
                                                                 ));
        $e->session()->flash('msg', msg('Reward has been updated'));
        echo script_redirect(url('/reward-setting'));
    }

    public function save_income(Request $e)
    {
        if (blank($e->amount)) {
            echo errorrecord('Earning Amount field is required');
            return;
        }
        DB::table('conditional_income')->where('id', $e->id)->update(array(
                                                                         'income_name'     => $e->income_name,
                                                                         'amount'          => $e->amount,
                                                                         'base_condition'  => $e->base_condition,
                                                                         'direct_required' => $e->direct_required,
                                                                         'counting'        => $e->counting,
                                                                         'level'           => $e->level,
                                                                         'total_a'         => $e->A,
                                                                         'total_b'         => $e->B,
                                                                         'total_c'         => $e->C,
                                                                         'total_d'         => $e->D,
                                                                         'total_e'         => $e->E,
                                                                         'total_f'         => $e->F,
                                                                         'total_g'         => $e->G,
                                                                         'total_h'         => $e->H,
                                                                         'total_i'         => $e->I,
                                                                         'total_j'         => $e->J,
                                                                         'duration'        => $e->duration,
                                                                     ));
        cache()->flush();
        echo success('Income updated Successfully.');
        echo script_redirect(url('/conditional-income'));
    }

    public function save_updated_rank(Request $e)
    {
        if (blank($e->rank_name)) {
            echo errorrecord('Rank Name is required');
            return;
        }
        DB::table('member_ranks')->where('id', $e->id)->update(array(
                                                                   'rank_name'       => $e->rank_name,
                                                                   'base_condition'  => $e->base_condition,
                                                                   'direct_required' => $e->direct_required,
                                                                   'counting'        => $e->counting,
                                                                   'total_a'         => $e->A,
                                                                   'total_b'         => $e->B,
                                                                   'total_c'         => $e->C,
                                                                   'total_d'         => $e->D,
                                                                   'total_e'         => $e->E,
                                                                   'total_f'         => $e->F,
                                                                   'total_g'         => $e->G,
                                                                   'total_h'         => $e->H,
                                                                   'total_i'         => $e->I,
                                                                   'total_j'         => $e->J,
                                                                   'duration'        => $e->duration,
                                                               ));
        cache()->flush();
        echo success('Rank updated Successfully.');
        echo script_redirect(url('/rank-setting'));
    }

    public function do_maintenance($type = null)
    {
        if ($type === 'optimize') {
            $tables = DB::select('SHOW TABLES');
            $tables = array_map('current', $tables);
            $table  = '';
            foreach ($tables as $e) {
                $table .= $e . ',';
            }
            $table = substr($table, 0, -1);
            DB::statement('OPTIMIZE TABLE ' . $table);
            DB::statement('REPAIR TABLE ' . $table);
        } else {
            cache()->flush();
            artisan('view:clear');
            artisan('route:clear');
            artisan('config:clear');
            artisan('cache:clear');
        }
        echo success('Database Maintenance completed successfully.');
        return;
    }
}
