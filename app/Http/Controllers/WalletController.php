<?php

namespace App\Http\Controllers;

use App\Exports\PendingWithdraw;
use App\Staff;
use Coinpayments;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use niklasravnsborg\LaravelPdf\Facades\Pdf;

class WalletController extends Controller
{

    public function retrieve_fund(Request $e)
    {
        $qry = \App\Member::select('id', 'name')->where('id', id_filter($e->id))->orWhere('email', $e->id)
                          ->orWhere('username', $e->id)->first();
        if (!empty($qry)) {
            $name = $qry->name;
            $id   = $qry->id;
        }
        $qry    = DB::table('wallet')->where('id', $id)->get();
        $wallet = '';
        foreach ($qry as $x) {
            $balance = $x->balance ?: 0;
            $wallet  .= $x->type . ':' . env('CURRENCY_SIGN') . $balance . '<br/>';
        }
        if (count($qry) > 0) {
            echo json_encode(array('name' => $name, 'id' => $id, 'balance' => $wallet));
        } else {
            return false;
        }
    }

    public function update_fund(Request $e)
    {
        $qry = \App\Member::select('id', 'name')->where('id', id_filter($e->id))->orWhere('email', $e->id)
                          ->orWhere('username', $e->id)->first();
        if (!empty($qry)) {
            $id = id_filter($qry->id);
        }
        $qry     = DB::table('wallet')->where('id', $id)->where('type', $e->wallet)->first();
        $balance = $qry ? $qry->balance : 0;
        if (!empty($qry)) {
            $new_balance = $balance - $e->amount;
            $credit      = 'Credit';
            $name        = 'Fund Deducted by Admin';
            if ($e->type === 'add') {
                $name        = 'Fund Added by Admin';
                $new_balance = $balance + $e->amount;
            }
            DB::transaction(function () use ($id, $e, $new_balance) {
                DB::table('wallet')
                  ->where(array(
                              'id' => $id, 'type' => $e->wallet,
                          ))
                  ->update(array('balance' => $new_balance));
                DB::table('balance_transfer_report')->insert(array(
                                                                 'receiver'        => $id,
                                                                 'amount'          => $e->amount,
                                                                 'type'            => $e->type,
                                                                 'updated_balance' => $new_balance,
                                                             ));
            });
            wallet_txns($id, $e->amount, $name, $credit);
            if ($e->type === 'add') {
                $array = array(
                    'sender_id'    => 'Admin',
                    'receiver_id'  => id_filter($id),
                    'amount'       => $e->amount,
                    'date'         => date('Y-m-d H:i:s'),
                    'initiated_by' => 'Admin: ' . session('admin_id'),
                );
                DB::table('fund_transfer_records')->insert($array);
                sms_to_member($id, 'Admin has added balance of ' . $e->amount . ' ' . env('CURRENCY_SIGN'));
            }
            echo $new_balance;
        } else {
            return false;
        }
    }

    public function bulk_manage_fund(Request $e)
    {
        $get_password = Staff::select('password')->where('id', session('admin_id'))->first();
        if (!password_verify($e->password, $get_password->password)) {
            echo errorrecord('Provided password is wrong', url('/bulk-manage-funds'));
            return;
        }

        $credit = 'Debit';
        if ($e->type == 'add') {
            $credit = 'Credit';
        }
        if ($e->type == 'add') {
            DB::transaction(function () use ($e) {
                DB::table('wallet')
                  ->where(array(
                              'type' => $e->wallet,
                          ))
                  ->update(array('balance' => DB::raw('balance+' . $e->amount)));
            });
        } else {
            DB::transaction(function () use ($e) {
                DB::table('wallet')
                  ->where(array(
                              'type' => $e->wallet,
                          ))
                  ->update(array('balance' => DB::raw('balance-' . $e->amount)));
            });
        }
        $wallets = DB::table('wallet')->select('id')->where('type', $e->wallet)->get();
        foreach ($wallets as $x):
            wallet_txns($x->id, $e->amount, 'Admin Fund Managed.', $credit);
        endforeach;
        echo success('Bulk Fund Management Completed.');
    }

    public function transfer_user_funds()
    {
        $data = array(
            'title'   => 'Transfer Funds from User to User',
            'wallets' => DB::table('wallet')->select('type', 'balance')->where('id', env('TOP_ID'))->get(),
        );
        return view('admin.earning.transfer_fund_form', $data);
    }

    public function transfer_user_fund_save(Request $e)
    {
        if (in_array($e->wallet, config('config.transfer_ban_wallet'))) {
            echo errorrecord('Fund transfer not allowed from: ' . $e->wallet . ' wallet.', url('/transfer-user-funds'));
            return;
        }
        $deduction = 0;
        if (env('CROSS_WALLET_TRANSFER') === true && $e->wallet !== $e->to_wallet) {
            $deduction = $e->amount * env('CROSS_WALLET_TRANSFER_CHARGE') / 100;
        }

        $get_balance = select('balance', 'wallet', array('id' => $e->from_user_id, 'type' => $e->wallet)) - $deduction;
        if (($get_balance < $e->amount) || $get_balance <= 0 || $e->amount <= 0) {
            echo errorrecord('Your ' . $e->wallet . ' wallet does not have sufficient fund. You must have atleast ' . env('CURRENCY_SIGN') . ' ' . ($e->amount + $deduction) . ' to transfer ' . env('CURRENCY_SIGN') . ' ' . $e->amount . '', url('/transfer-user-fund'));
            return;
        }
        if (check_user($e->from_user_id) == 0) {
            echo errorrecord('The User Id you have enetered is invalid.', url('/transfer-user-funds'));
            return;
        }
        $to_wallet = $e->to_wallet ? $e->to_wallet : $e->wallet;

        DB::transaction(function () use ($e, $get_balance, $to_wallet, $deduction) {
            DB::table('wallet')->where(array(
                                           'id' => $e->from_user_id, 'type' => $e->wallet,
                                       ))->update(array('balance' => DB::raw('balance-' . ($e->amount + $deduction))));
            DB::table('wallet')->where(array(
                                           'id' => id_filter($e->to_user_id), 'type' => $to_wallet,
                                       ))->update(array('balance' => DB::raw('balance+' . $e->amount)));
            wallet_txns($e->from_user_id, $e->amount, 'Fund Transferred to ' . env('ID_EXT') . $e->to_user_id, 'Debit');
            wallet_txns(id_filter($e->to_user_id), $e->amount, 'Fund Received from ' . env('ID_EXT') . $e->from_user_id);
            if ($deduction > 0) {
                wallet_txns(session('member_id'), $deduction, 'Fund Transfer Charge Deducted', 'Debit');
            }
            if (env('TXN_SMS') == true) {
                sms_to_member($e->to_user_id, $e->from_user_id . 'has sent you ' . $e->amount . ' ' . env('CURRENCY_SIGN'));
            }

            $array = array(
                'sender_id'    => $e->from_user_id,
                'receiver_id'  => id_filter($e->to_user_id),
                'amount'       => $e->amount,
                'date'         => date('Y-m-d H:i:s'),
                'initiated_by' => 'Admin: ' . session('admin_id'),
            );
            DB::table('fund_transfer_records')->insert($array);
        });
        echo success('Fund Transfer Completed.');
    }

    public function user_pending_withdrawals()
    {
        $data = array(
            'title' => 'Pending Withdrawal Payments',
            'data'  => DB::table('withdrawal_records')->where('status', 'Processing')->paginate(30),
        );
        return view('admin.earning.pending_withdrawals', $data);
    }

    public function user_hold_withdrawals()
    {
        $data = array(
            'title' => 'Holden Withdrawal Payments',
            'data'  => DB::table('withdrawal_records')->where('status', 'Hold')->paginate(30),
        );
        return view('admin.earning.hold_withdrawals', $data);
    }

    public function pay_all()
    {
        $amount = sum('net_paid', 'withdrawal_records', array('status' => 'Processing'));
        $data   = array(
            'title'  => 'Pay to All Withdrawals',
            'amount' => $amount,
        );
        return view('admin.earning.pay_all', $data);
    }

    public function pay_all_post(Request $e)
    {
        if ($e->api == 1) {
            $records = DB::table('withdrawal_records')->where('status', 'Processing')->limit(50)->get();
            foreach ($records as $x):

                $bank = DB::table('member_profile')
                          ->select('btc_address', 'bank_ac_holder', 'bank_ac_no', 'bank_ifsc', 'upi_address', 'paypal_email', 'razorpay_id', 'razorpay_fund_id')
                          ->where(array('id' => $x->user_id))->first();
                if (!blank($bank->bank_ac_no) && !blank($bank->bank_ifsc)):
                    $vars = array(
                        'user_id'          => $x->user_id,
                        'razorpay_id'      => $bank->razorpay_id,
                        'razorpay_fund_id' => $bank->razorpay_fund_id,
                        'bank_ac_no'       => $bank->bank_ac_no,
                        'bank_ac_holder'   => $bank->bank_ac_holder,
                        'bank_ifsc'        => $bank->bank_ifsc,
                        'net_paid'         => $x->net_paid,
                        'btc_address'      => $bank->btc_address,
                        'withdraw_id'      => $e->id,
                    );
                    app('\App\Http\Controllers\Withdraw_Api')->initiate(env('PAYOUT_GATEWAY'), $vars);
                    $array = array(
                        'status'   => 'Completed',
                        'note'     => $e->note,
                        'pay_date' => date('Y-m-d'),
                    );
                    DB::table('withdrawal_records')->where('status', 'Processing')->update($array);
                endif;
            endforeach;
        } else {
            DB::transaction(function () use ($e) {
                $array = array(
                    'status'   => 'Completed',
                    'note'     => $e->note,
                    'pay_date' => date('Y-m-d'),
                );
                DB::table('withdrawal_records')->where('status', 'Processing')->update($array);
            });
        }
        echo success('Payment marked as Paid for All Withdrawals');
    }

    public function pay_withdraw($id)
    {
        $record = DB::table('withdrawal_records')
                    ->where('withdrawal_records.id', $id)
                    ->leftJoin('members', 'withdrawal_records.user_id', '=', 'members.id')
                    ->leftJoin('member_profile', 'withdrawal_records.user_id', '=', 'member_profile.id')
                    ->select('withdrawal_records.user_id', 'withdrawal_records.net_paid', 'members.name', 'member_profile.bank_ac_holder', 'member_profile.bank_name', 'member_profile.bank_ac_no', 'member_profile.bank_ifsc', 'member_profile.bank_branch', 'member_profile.btc_address', 'member_profile.upi_address')
                    ->first();
        $data   = array(
            'title' => 'Withdrawal Detail',
            'data'  => $record,
            'id'    => $id,
        );
        return view('admin.earning.pay_withdrawal', $data);
    }

    public function pay(Request $e)
    {
        $detail = DB::table('withdrawal_records')->select('net_paid', 'user_id')->where('id', $e->id)->first();
        $note   = 1;
        if ($e->api == 1) {
            $bank = DB::table('member_profile')
                      ->select('btc_address', 'bank_ac_holder', 'bank_ac_no', 'bank_ifsc', 'upi_address', 'paypal_email', 'razorpay_id', 'razorpay_fund_id')
                      ->where(array('id' => $detail->user_id))->first();
            $vars = array(
                'user_id'          => $detail->user_id,
                'razorpay_id'      => $bank->razorpay_id,
                'razorpay_fund_id' => $bank->razorpay_fund_id,
                'bank_ac_no'       => $bank->bank_ac_no,
                'bank_ac_holder'   => $bank->bank_ac_holder,
                'bank_ifsc'        => $bank->bank_ifsc,
                'net_paid'         => $detail->net_paid,
                'btc_address'      => $bank->btc_address,
                'withdraw_id'      => $e->id,
            );
            $note = app('\App\Http\Controllers\Withdraw_Api')->initiate(env('PAYOUT_GATEWAY'), $vars);
        }
        if (!blank($note)) {
            if ($note == 1) {
                $note = '';
            }
            $array = array(
                'status'   => 'Completed',
                'note'     => $e->note . ' Txn ID:' . $note,
                'pay_date' => date('Y-m-d'),
            );
            echo DB::table('withdrawal_records')->where('id', $e->id)->update($array);
            if (env('TXN_SMS') == true) {
                sms_to_member($detail->user_id, 'Payment of ' . $detail->net_paid . ' ' . env('CURRENCY_ISO') . ' has been sent.');
            }

            echo success('Payment Successful');
            return;
        }
    }

    public function hold_withdrawal($id)
    {
        DB::table('withdrawal_records')->where('id', $id)->update([
                                                                      'status' => 'Hold',
                                                                      'note'   => 'Payout on Hold by Admin',
                                                                  ]);
        echo success('Payment Marked as Hold');
    }

    public function unhold_withdrawal($id)
    {
        $array = array(
            'status' => 'Processing',
        );
        DB::table('withdrawal_records')->where('id', $id)->update($array);
        echo success('Payment Released from Hold Status. You can now make payment');
    }

    public function searchpendinghwithdrawals(Request $e)
    {
        $result = DB::table('withdrawal_records')->where('status', 'Processing')->orderBy('id', 'DESC');
        if (!blank(id_filter($e->userid))) {
            $result->where('user_id', id_filter($e->userid));
        }
        if (!blank($e->fromdt)) {
            $result->where('date', '>=', $e->fromdt);
        }
        if (!blank($e->todt)) {
            $result->where('date', '<=', $e->todt . ' 23:59:59');
        }
        $result = $result->paginate(20)->appends(request()->query());
        $data   = array(
            'title' => 'Search Results of Pending Withdrawal Payments',
            'data'  => $result,
        );
        return view('admin.earning.pending_withdrawals', $data);
    }

    public function export_withdrawals()
    {
        $data = array(
            'data' => $this->pending_withdrawal_data(),
        );
        $pdf  = Pdf::loadView('admin.earning.withdrawal_print_list', $data);
        $pdf->stream('payment_list.pdf');
    }

    public function pending_withdrawal_data()
    {

        $data = DB::table('withdrawal_records')
                  ->where('withdrawal_records.status', 'Processing')
                  ->leftJoin('members', 'withdrawal_records.user_id', '=', 'members.id')
                  ->leftJoin('member_profile', 'withdrawal_records.user_id', '=', 'member_profile.id')
                  ->select('withdrawal_records.user_id', 'withdrawal_records.net_paid', 'members.name', 'member_profile.bank_ac_holder', 'member_profile.bank_name', 'member_profile.bank_ac_no', 'member_profile.bank_ifsc', 'member_profile.bank_branch', 'member_profile.btc_address', 'member_profile.upi_address')
                  ->get();

        return $data;
    }

    public function export_withdrawals_excel()
    {
        return Excel::download(new PendingWithdraw(), 'payment_list.xlsx');
    }

    public function delete_withdrawal($id)
    {
        $data = DB::table('withdrawal_records')->select('amount', 'user_id', 'wallet')->where(array('id' => $id))
                  ->first();
        DB::transaction(function () use ($data, $id) {
            DB::table('wallet')->where(array(
                                           'id' => $data->user_id, 'type' => $data->wallet,
                                       ))->update(array('balance' => DB::raw('balance+' . $data->amount)));
            DB::table('withdrawal_records')->delete($id);
        });
        echo success('Withdrawal has been deleted and Balance of <br/>' . env('CURRENCY_SIGN') . ' ' . $data->amount . ' Transferred to: ' . $data->wallet . ' Wallet.');
        return;
    }

    public function wallet_balances()
    {
        $data = array(
            'title' => 'Wallet Balances',
            'data'  => DB::table('wallet')
                         ->leftJoin('members', 'members.id', '=', 'wallet.id')
                         ->select('members.name', 'wallet.*')
                         ->orderBy('balance', 'DESC')
                         ->paginate(30)->appends(request()->query()),
        );
        return view('admin.earning.wallet_balances', $data);
    }

    public function user_withdrawal_history()
    {
        $data = array(
            'title' => 'Old Withdrawal Reports',
            'data'  => DB::table('withdrawal_records')
                         ->leftJoin('members', 'members.id', '=', 'withdrawal_records.user_id')
                         ->select('withdrawal_records.*', 'members.name')
                         ->where('withdrawal_records.status', 'Completed')->orderBy('withdrawal_records.pay_date', 'DESC')
                         ->paginate(30)->appends(request()->query()),
        );
        return view('admin.earning.withdrawal_history', $data);
    }

    public function searchwithdrawals(Request $e)
    {
        $result = DB::table('withdrawal_records')
                    ->leftJoin('members', 'members.id', '=', 'withdrawal_records.user_id')
                    ->select('withdrawal_records.*', 'members.name')
                    ->where('withdrawal_records.status', 'Completed')->orderBy('withdrawal_records.id', 'DESC');
        if (!blank(id_filter($e->userid))) {
            $result->where('withdrawal_records.user_id', id_filter($e->userid));
        }
        if (!blank($e->fromdt)) {
            $result->where('withdrawal_records.date', '>=', $e->fromdt);
        }
        if (!blank($e->todt)) {
            $result->where('withdrawal_records.date', '<=', $e->todt . ' 23:59:59');
        }
        $result = $result->paginate(10)->appends(request()->query());
        $data   = array(
            'title' => 'Search Results of Old Withdrawal Payments',
            'data'  => $result,
        );
        return view('admin.earning.withdrawal_history', $data);
    }

    public function create_admin_withdrawal()
    {
        $wallets = cache()->remember('wallets', 43200, function () {
            return DB::table('wallet')->select('type')->where('id', env('TOP_ID'))->get();
        });
        $data    = array(
            'title'   => 'Create withdrawal from admin',
            'wallets' => $wallets,
        );
        return view('admin.earning.admin_withdrawal', $data);
    }

    public function withdraw_user_fund(Request $e)
    {
        if (!blank($e->amount) && $e->amount < env('MIN_WITHDRAW_AMOUNT')) {
            echo errorrecord('Minimum Withdrawal amount is: ' . env('MIN_WITHDRAW_AMOUNT'), url('/create-admin-withdrawal'));
            return;
        }
        if ($e->amount > (int)env('MAX_WITHDRAW_AMOUNT') && (int)env('MAX_WITHDRAW_AMOUNT') > 0) {
            echo errorrecord('Maximum Withdrawal amount is: ' . env('MAX_WITHDRAW_AMOUNT'), url('/create-admin-withdrawal'));
            return;
        }
        $get_password = Staff::select('password')->where('id', session('admin_id'))->first();
        if (!password_verify($e->password, $get_password->password)) {
            echo errorrecord('Provided password is wrong', url('/create-admin-withdrawal'));
            return;
        }
        if (!blank($e->userid)) {
            $get_balance = select('balance', 'wallet', array('id' => $e->userid, 'type' => $e->wallet));
            if (!blank($e->amount) && $get_balance < $e->amount) {
                echo errorrecord('User Does not have sufficient fund to cover this transaction', url('/create-admin-withdrawal'));
                return;
            }
            if ($get_balance < env('MIN_WITHDRAW_AMOUNT')) {
                echo errorrecord('User Does not have sufficient fund to cover this transaction', url('/create-admin-withdrawal'));
                return;
            }
        }
        $db = DB::table('wallet')->where('type', $e->wallet);
        if (!blank($e->userid)) {
            $db->where('id', $e->userid);
        }
        if (!blank($e->amount)) {
            $db->where('balance', '>=', $e->amount);
        } else {
            $db->where('balance', '>=', env('MIN_WITHDRAW_AMOUNT'));
        }
        $db = $db->get();
        DB::transaction(function () use ($e, $db) {
            foreach ($db as $x) {
                $amount          = !blank($e->amount) ? $e->amount : $x->balance;
                $tds_deduction   = $amount * env('TDS') / 100;
                $sc_deduction    = $amount * env('SERVICE_CHARGE') / 100;
                $oc_deduction    = $amount * env('OTHER_CHARGE') / 100;
                $total_deduction = $tds_deduction + $sc_deduction + $oc_deduction;
                $net_payable     = $amount - $total_deduction;
                if (env('AUTOMATED_WITHDRAW') === true && env('PAYOUT_GATEWAY') === 'Coinpayments' && env('FUND_CREDIT') === 'BTC') {
                    $btc_address = select('btc_address', 'member_profile', array('id' => $x->id));
                    if (!blank($btc_address)) {
                        $conversion = curl('https://blockchain.info/tobtc?currency=' . env('CURRENCY_ISO') . '&value=' . $net_payable);
                        if ((float)$conversion < (float)'0.0008') {
                            return redirect()->back()
                                             ->with('msg', msg('Minimum Withdrawal amount must be 0.0008 BTC after deduction.', 'danger'));
                        }
                        $withdrawal = Coinpayments::createWithdrawal($conversion, 'BTC', $btc_address, false);
                        $id         = $this->withdraw($x->id, $amount, $net_payable, $tds_deduction, $sc_deduction, $oc_deduction, config('config.wallet_types')[0]);
                        DB::table('system_jobs')->insert(
                            array(
                                'ref_id'      => $withdrawal['ref_id'],
                                'link_ref_id' => $id,
                                'type'        => 'Coinpayments Withdrawal',
                            )
                        );
                        DB::table('withdrawal_records')->where('id', $id)->update([
                                                                                      'status'   => 'Completed',
                                                                                      'pay_date' => date('Y-m-d H:i:s'),
                                                                                      'note'     => 'Paid through Coinpayments. Ref No: ' . $withdrawal['ref_id'],
                                                                                  ]);
                    }
                } else {
                    $this->withdraw($x->id, $amount, $net_payable, $tds_deduction, $sc_deduction, $oc_deduction, $e->wallet);
                }
            }
        });
        echo success('Withdrawal Made successfully, You can now check status at Pending Withdrawal List');
    }

    private function withdraw($member_id, $amount, $net_payable, $tds_deduction, $sc_deduction, $oc_deduction, $wallet)
    {

        ##
        $array = array(
            'user_id'        => $member_id,
            'amount'         => $amount,
            'net_paid'       => $net_payable,
            'tds'            => $tds_deduction,
            'wallet'         => $wallet,
            'service_charge' => $sc_deduction,
            'other_charge'   => $oc_deduction,
            'date'           => date('Y-m-d H:i:s'),
        );
        if (env('AUTOMATED_WITHDRAW') === true && env('PAYOUT_GATEWAY') !== 'Coinpayments') {
            $bank = DB::table('member_profile')
                      ->select('btc_address', 'bank_ac_holder', 'bank_ac_no', 'bank_ifsc', 'upi_address', 'paypal_email', 'razorpay_id', 'razorpay_fund_id')
                      ->where(array('id' => session('member_id')))->first();
            if (env('FUND_CREDIT') === 'Bank' && blank($bank->bank_ac_no)) {
                return redirect()->back()->with('msg', msg('Please Complete bank account detail', 'danger'));
            }
            if (env('FUND_CREDIT') === 'UPI' && blank($bank->upi_address)) {
                return redirect()->back()->with('msg', msg('Fill up UPI address at profile section', 'danger'));
            }
            if (env('FUND_CREDIT') === 'Paypal' && blank($bank->paypal_email)) {
                return redirect()->back()->with('msg', msg('Fill up Paypal Email ID at profile section', 'danger'));
            }
            $member = \App\Member::select('phone', 'email')->where('id', $member_id)->first();
            $ref    = app('\App\Http\Controllers\Withdraw_Api')->initiate(env('PAYOUT_GATEWAY'), array(
                'user_id'          => $member_id,
                'razorpay_id'      => $bank->razorpay_id,
                'razorpay_fund_id' => $bank->razorpay_fund_id,
                'bank_ac_holder'   => $bank->bank_ac_holder,
                'bank_ac_no'       => $bank->bank_ac_no,
                'bank_ifsc'        => $bank->bank_ifsc,
                'net_paid'         => $net_payable,
                'btc_address'      => $bank->btc_address,
                'phone'            => $member->phone ?: env('COMPANY_PHONE'),
                'email'            => $member->email ?: env('COMPANY_MAIL'),
            ));
            if ($ref === true) {
                return null;
            }
            $array = array(
                'user_id'        => $member_id,
                'amount'         => $amount,
                'net_paid'       => $net_payable,
                'tds'            => $tds_deduction,
                'wallet'         => $wallet,
                'status'         => 'Completed',
                'service_charge' => $sc_deduction,
                'other_charge'   => $oc_deduction,
                'date'           => date('Y-m-d H:i:s'),
                'pay_date'       => date('Y-m-d H:i:s'),
                'note'           => 'Paid through IMPS. Ref No: ' . $ref . '<hr/> A/C No: ' . $bank->bank_ac_no . '<br/> IFSC: ' . $bank->bank_ifsc,
            );
            $id    = DB::table('withdrawal_records')->insertGetId($array);
        } else {
            $id = DB::table('withdrawal_records')->insertGetId($array);
        }
        if ($id) {
            if (blank($wallet)) {
                $wallet = config('config.wallet_types')[0];
            }
            DB::table('wallet')->where(array(
                                           'id'   => $member_id,
                                           'type' => $wallet,
                                       ))->update(array('balance' => DB::raw('balance-' . $amount)));
            wallet_txns($member_id, $amount, 'Fund Withdrawn', 'Debit');
            return $id;
        }
        return redirect()->back()->with('msg', msg('Some error happened ! Please try again after sometime', 'danger'));
    }

    public function user_fund_transfer_history()
    {
        $data = array(
            'title'          => 'Fund Transactions History',
            'total_transfer' => DB::table('fund_transfer_records')->sum('amount'),
            'data'           => DB::table('fund_transfer_records')->orderBy('id', 'DESC')->paginate(10),
        );
        return view('admin.earning.transfer_fund_history', $data);
    }

    #### Member Part

    public function transfersearchadmin(Request $e)
    {
        $topid  = env('TOP_ID');
        $result = DB::table('fund_transfer_records');
        if (!blank(id_filter($e->userid))) {
            $topid = id_filter($e->userid);
            $result->where(function ($query) use ($e) {
                $query->where('sender_id', id_filter($e->userid))->orWhere('receiver_id', id_filter($e->userid));
            });
        }
        if (!blank($e->fromdt)) {
            $result->where('date', '>=', $e->fromdt);
        }
        if (!blank($e->todt)) {
            $result->where('date', '<=', $e->todt);
        }
        $result = $result->orderBy('id', 'DESC')->paginate(10)->appends(request()->query());
        $data   = array(
            'title'          => 'Fund Transaction Search Result',
            'fromdt'         => $e->fromdt,
            'todt'           => $e->todt,
            'total_transfer' => DB::table('fund_transfer_records')->where('sender_id', $topid)->sum('amount'),
            'data'           => $result,
        );
        return view('admin.earning.transfer_fund_history', $data);
    }

    public function subscriber_wallet()
    {
        $data = array(
            'title'       => 'My Wallet',
            'heading'     => 'My Wallet',
            'subtitle'    => 'View and manage wallet balance and Transactions',
            'latest_txns' => DB::table('wallet_txns')->where('user_id', session('member_id'))->orderBy('id', 'DESC')
                               ->paginate(10),
            'wallets'     => DB::table('wallet')->where('id', session('member_id'))->get(),
        );
        return view('member.wallet.wallet', $data);
    }

    public function add_fund_form()
    {
        return view('member.wallet.add_fund_form');
    }

    public function add_fund_final(Request $e)
    {
        if ($e->gateway === 'pg') {
            $data = array(
                'amount' => $e->amount,
            );
            return view('member.wallet.add_fund', $data);
        }

        if ($e->gateway === 'epin') {
            $get_value = select('amount', 'epins', array('epin' => $e->epin, 'used_by' => null));
            if (blank($get_value) or $get_value <= 0) {
                echo errorrecord('E-PIN not valid, Please close the window and try again');
                return;
            }
            DB::transaction(function () use ($e, $get_value) {
                DB::table('wallet')->where('id', session('member_id'))
                  ->update(array('balance' => DB::raw('balance+' . $get_value)));
                DB::table('epins')->where('epin', $e->epin)->update(array(
                                                                        'used_by'   => session('member_id'),
                                                                        'used_date' => date('Y-m-d'),
                                                                    ));
                wallet_txns(session('member_id'), $get_value);
            });
            echo success('Balance has been added. <br/>Refresh the page to see your updated balance');
            return;
        }
    }

    public function transfer_fund_form()
    {
        $data = array(
            'title'   => 'Transfer Wallet funds',
            'heading' => 'Transfer Wallet funds',
            'wallets' => DB::table('wallet')->select('type', 'balance')->where('id', session('member_id'))->get(),
            'balance' => select('balance', 'wallet', array(
                'id'   => session('member_id'),
                'type' => config('config.wallet_types')[0],
            )),
        );  
        return view('member.wallet.transfer_fund_form', $data);
    }

    public function transfer_fund(Request $e)
    {
        if (in_array($e->wallet, config('config.transfer_ban_wallet'))) {
            echo errorrecord('Fund transfer not allowed from: ' . $e->wallet . ' wallet.', url('/transfer-user-funds'));
            return;
        }
        $deduction = 0;
        if (env('CROSS_WALLET_TRANSFER') === true && $e->wallet !== $e->to_wallet) {
            $deduction = $e->amount * env('CROSS_WALLET_TRANSFER_CHARGE') / 100;
        }
        $get_balance = select('balance', 'wallet', array(
                'id' => session('member_id'), 'type' => $e->wallet,
            )) - $deduction;
        if (($get_balance < $e->amount) || $get_balance <= 0 || $e->amount <= 0) {
            echo errorrecord('Your ' . $e->wallet . ' wallet does not have sufficient fund. You must have atleast ' . env('CURRENCY_SIGN') . ' ' . ($e->amount + $deduction) . ' to transfer ' . env('CURRENCY_SIGN') . ' ' . $e->amount . '', url('/transfer-user-fund'));
            return;
        }

        if (check_user($e->user_id) == 0) {
            echo errorrecord('The User Id you have enetered is invalid.', url('/transfer-fund-form'));
            return;
        }
        $to_wallet = $e->to_wallet ? $e->to_wallet : $e->wallet;

        DB::transaction(function () use ($e, $deduction, $to_wallet) {
            DB::table('wallet')->where(array(
                                           'id' => session('member_id'), 'type' => $e->wallet,
                                       ))->update(array('balance' => DB::raw('balance-' . ($e->amount + $deduction)))); //Minus the Wallet amount - FROM the Sender
            DB::table('wallet')->where(array(
                                           'id' => id_filter($e->user_id), 'type' => $to_wallet,
                                       ))->update(array('balance' => DB::raw('balance+' . $e->amount))); //Plus the Waller amount - To the receiver  
            wallet_txns(session('member_id'), $e->amount, 'Fund Transferred to ' . env('ID_EXT') . $e->user_id, 'Debit');
            wallet_txns(id_filter($e->user_id), ($e->amount), 'Fund Received from ' . env('ID_EXT') . session('member_id'));
            if ($deduction > 0) {
                wallet_txns(session('member_id'), $deduction, 'Fund Transfer Charge Deducted', 'Debit');
            }
            $array = array(
                'sender_id'    => session('member_id'),
                'receiver_id'  => id_filter($e->user_id),
                'amount'       => $e->amount,
                'date'         => date('Y-m-d H:i:s'),
                'initiated_by' => 'Self',
            );
            DB::table('fund_transfer_records')->insert($array);
        });
        echo msg('Fund has been transferred successfully. Please wait....');
        echo script_redirect(url('/the-wallet'));
    }

    public function transfer_fund_history()
    {

      
        $data = array(
            'title'          => 'Fund Transfered History',
            'heading'        => 'Fund Transfered History',
            'subtitle'       => 'Search your detailed fund transfer report',
            'total_transfer' => DB::table('fund_transfer_records')->where('sender_id', session('member_id'))
                                  ->sum('amount'),
            'total_received' => DB::table('fund_transfer_records')->where('receiver_id', session('member_id'))
                                  ->sum('amount'),
            'data'           => DB::table('fund_transfer_records')->where('sender_id', session('member_id'))
                                  ->orderBy('id', 'DESC')->paginate(10),
        );   
        return view('member.wallet.transfer_fund_history', $data);
    }

    public function fund_received_history()
    {
        $data = array(
            'title'          => 'Fund Received History',
            'heading'        => 'Fund Received History',
            'subtitle'       => 'Search your detailed fund received report',
            'total_transfer' => DB::table('fund_transfer_records')->where('sender_id', session('member_id'))
                                  ->sum('amount'),
            'total_received' => DB::table('fund_transfer_records')->where('receiver_id', session('member_id'))
                                  ->sum('amount'),
            'data'           => DB::table('fund_transfer_records')->where('receiver_id', session('member_id'))
                                  ->orderBy('id', 'DESC')->paginate(10),
        );
        return view('member.wallet.received_fund_history', $data);
    }

    public function fund_search(Request $e)
    {
        $result = DB::table('fund_transfer_records')
                    ->where(function ($query) {
                        $query->where('receiver_id', session('member_id'))->orWhere('sender_id', session('member_id'));
                    });
        if (!blank(id_filter($e->userid))) {
            $result->where(function ($query) use ($e) {
                $query->where('receiver_id', id_filter($e->userid))->orWhere('sender_id', id_filter($e->userid));
            });
        }
        if (!blank($e->fromdt)) {
            $result->where('date', '>=', $e->fromdt);
        }
        if (!blank($e->todt)) {
            $result->where('date', '<=', $e->todt . ' 23:59:59');
        }
        $result = $result->orderBy('id', 'DESC')->paginate(15)->appends(request()->query());
        $data   = array(
            'title'          => 'Fund Transaction Search Result',
            'heading'        => 'Fund Transaction Search Result',
            'fromdt'         => $e->fromdt,
            'todt'           => $e->todt,
            'subtitle'       => 'Search result about your fund transactions',
            'total_transfer' => DB::table('fund_transfer_records')->where('sender_id', session('member_id'))
                                  ->sum('amount'),
            'total_received' => DB::table('fund_transfer_records')->where('receiver_id', session('member_id'))
                                  ->sum('amount'),
            'data'           => $result,
        );
        return view('member.wallet.transfer_fund_history', $data);
    }

    public function withdraw_fund()
    {
        $data = array(
            'title'           => 'Withdraw Fund from Wallet',
            'heading'         => 'Withdraw Fund from Wallet',
            'subtitle'        => 'Remember that once you withdraw, it cannot be undone',
            'last_withdrawal' => DB::table('withdrawal_records')->select('amount', 'date')
                                   ->where('user_id', session('member_id'))->first(),
            'balance'         => select('balance', 'wallet', array(
                'id'   => session('member_id'),
                'type' => config('config.wallet_types')[0],
            )),
        );
        return view('member.wallet.withdraw_fund', $data);
    }

    public function withdraw_fund_submit(Request $e)
    {
        if ($e->amount < env('MIN_WITHDRAW_AMOUNT')) {
            return redirect('/withdraw-fund')->with('msg', msg('Minimum Withdrawal amount is: ' . env('MIN_WITHDRAW_AMOUNT'), 'danger'));
        }
        if ($e->amount > (int)env('MAX_WITHDRAW_AMOUNT') && (int)env('MAX_WITHDRAW_AMOUNT') > 0) {
            return redirect('/withdraw-fund')->with('msg', msg('Maximum Withdrawal amount is: ' . env('MAX_WITHDRAW_AMOUNT'), 'danger'));
        }
        $get_password = \App\Member::select('password')->where('id', session('member_id'))->first();
        if (!password_verify($e->password, $get_password->password)) {
            return redirect('/withdraw-fund')->with('msg', msg('Invalid password entered', 'danger'));
        }
        $get_balance = select('balance', 'wallet', array(
            'id'   => session('member_id'),
            'type' => config('config.wallet_types')[0],
        ));
        if ($get_balance < $e->amount) {
            return redirect('/withdraw-fund')->with('msg', msg('Your wallet does not have sufficient fund to cover this transaction', 'danger'));
        }
        $amount          = $e->amount;
        $tds_deduction   = $amount * env('TDS') / 100;
        $sc_deduction    = $amount * env('SERVICE_CHARGE') / 100;
        $oc_deduction    = $amount * env('OTHER_CHARGE') / 100;
        $total_deduction = $tds_deduction + $sc_deduction + $oc_deduction;
        $net_payable     = $amount - $total_deduction;

        if (env('AUTOMATED_WITHDRAW') === true && env('FUND_CREDIT') === 'BTC' && env('PAYOUT_GATEWAY') === 'Coinpayments') {
            $btc_address = select('btc_address', 'member_profile', array('id' => session('member_id')));
            if (!blank($btc_address)) {
                $conversion = curl('https://blockchain.info/tobtc?currency=' . env('CURRENCY_ISO') . '&value=' . $net_payable);
                if ((float)$conversion < (float)'0.0008') {
                    return redirect()->back()
                                     ->with('msg', msg('Minimum Withdrawal amount must be 0.0008 BTC after deduction.', 'danger'));
                }
                $id = DB::transaction(function () use ($conversion, $amount, $tds_deduction, $sc_deduction, $oc_deduction, $net_payable, $btc_address) {
                    $withdrawal = Coinpayments::createWithdrawal($conversion, 'BTC', $btc_address, false);
                    $id         = $this->withdraw(session('member_id'), $amount, $net_payable, $tds_deduction, $sc_deduction, $oc_deduction, config('config.wallet_types')[0]);
                    if ($id > 0) {
                        DB::table('system_jobs')->insert(
                            array(
                                'ref_id'      => $withdrawal['ref_id'],
                                'link_ref_id' => $id,
                                'type'        => 'Coinpayments Withdrawal',
                            )
                        );
                        DB::table('withdrawal_records')->where('id', $id)->update([
                                                                                      'status'   => 'Completed',
                                                                                      'pay_date' => date('Y-m-d H:i:s'),
                                                                                      'note'     => 'Paid through Coinpayments. Ref No: ' . $withdrawal['ref_id'],
                                                                                  ]);
                    }
                    return $id;
                });
                if ($id > 0) {
                    return redirect('/withdraw-history')->with('msg', msg('Your withdrawal request has accepted successfully'));
                }
                return redirect(url('/withdraw-fund'))->with('msg', session('tmsg'));
            }
            return redirect()->back()->with('msg', msg('Bitcoin Address is required. <a href="' . url('/profile') . '">Update Here &rarr;</a>',
                                                       'danger'));
        }
        $id = $this->withdraw(session('member_id'), $amount, $net_payable, $tds_deduction, $sc_deduction, $oc_deduction, config('config.wallet_types')[0]);
        if ($id > 0) {
            return redirect('/withdraw-history')->with('msg', msg('Your withdrawal request has accepted successfully'));
        }
        return redirect()->back()->with('msg', session('tmsg'));
    }

    public function withdraw_history()
    {
        $data = array(
            'title'    => 'Withdrawal History',
            'heading'  => 'Withdrawal History',
            'subtitle' => 'Search your detailed fund withdrawal report',
            'data'     => DB::table('withdrawal_records')->where('user_id', session('member_id'))->orderBy('id', 'DESC')
                            ->paginate(10),
        );
        return view('member.wallet.withdraw_history', $data);
    }

    public function deduction_history()
    {
        $data = array(
            'title'    => 'Deduction Report',
            'heading'  => 'Deduction Report',
            'subtitle' => 'Search your detailed fund withdrawal report',
            'data'     => DB::table('withdrawal_records')->where('user_id', session('member_id'))->orderBy('id', 'DESC')
                            ->paginate(10),
        );
        return view('member.wallet.deduction_history', $data);
    }

    public function search_deduction(Request $e)
    {
        $qry = DB::table('withdrawal_records')->where('user_id', session('member_id'));
        if (!blank($e->fromdt)) {
            $qry->where('date', '>=', $e->fromdt);
        }
        if (!blank($e->todt)) {
            $qry->where('date', '<=', $e->todt . ' 23:59:59');
        }
        $qry  = $qry->orderBy('id', 'DESC')->paginate(10)->appends(request()->query());
        $data = array(
            'title'    => 'Deduction History',
            'heading'  => 'Deduction History',
            'subtitle' => 'Search your detailed fund withdrawal and Deduction report',
            'data'     => $qry,
        );
        return view('member.wallet.deduction_history', $data);
    }

    public function search_withdraw(Request $e)
    {
        $qry = DB::table('withdrawal_records')->where('user_id', session('member_id'));
        if (!blank($e->fromdt)) {
            $qry->where('date', '>=', $e->fromdt);
        }
        if (!blank($e->todt)) {
            $qry->where('date', '<=', $e->todt . ' 23:59:59');
        }
        $qry  = $qry->orderBy('id', 'DESC')->paginate(10)->appends(request()->query());
        $data = array(
            'title'    => 'Withdrawal History',
            'heading'  => 'Withdrawal History',
            'subtitle' => 'Search your detailed fund withdrawal report',
            'data'     => $qry,
        );
        return view('member.wallet.withdraw_history', $data);
    }
}
