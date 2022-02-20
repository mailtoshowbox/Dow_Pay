<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Epin extends Controller
{
    public function generate_epin(Request $e)
    {
        if (blank($e->amount)) {
            echo errorrecord('E-Pin amount is Required', url('/create-epin'));
            return;
        }
        if (blank($e->qty)) {
            echo errorrecord('E-Pin Quantity is Required', url('/create-epin'));
            return;
        }

        if ($e->qty > 1000) {
            echo errorrecord('Maximum E-Pin Quantity is 1000', url('/create-epin'));
            return;
        }

        $data = array();
        for ($i = 0; $i < $e->qty; $i++) {
            $epin      = random_int(10000000, 99999999);
            $new_array = array(
                'epin'   => $epin,
                'owner'  => $e->user_id ? id_filter($e->user_id) : env('TOP_ID'),
                'amount' => $e->amount,
                'type'   => $e->type,
            );
            $data[]    = $new_array;
        }
        DB::table('epins')->insert($data);
        $e->session()->flash('msg', msg('E-Pins has been generated'));
        echo success('E-Pins has been generated');
        echo script_redirect(url('/epins/Un-Used'));
        return;
    }

    public function epins($type)
    {
        $qry = DB::table('epins');
        if (!blank($type)) {
            if ($type === 'Used') {
                $qry = $qry->where('used_date', '!=', null);
            }
            if ($type === 'Un-Used') {
                $qry = $qry->where('used_date', null);
            }
        }

        $data = array(
            'title' => 'Manage ' . $type . ' E-Pins',
            'data'  => $qry->orderBy('id', 'DESC')->paginate(20),
        );
        return view('admin.epin.epins', $data);
    }

    public function transfer_record()
    {
        $qry  = DB::table('epins');
        $qry  = $qry->where('transferred_by', '!=', null);
        $data = array(
            'title' => 'Transferred E-Pin Records',
            'data'  => $qry->paginate(30),
        );
        return view('admin.epin.transfer_record', $data);
    }

    public function requests()
    {
        $qry = DB::table('epin_requests')
                 ->leftJoin('members', 'members.id', '=', 'epin_requests.userid')
                 ->select('members.name', 'epin_requests.*')
                 ->orderBy('epin_requests.id', 'DESC')->paginate(20);

        $data = array(
            'title' => 'E-pin Requests',
            'data'  => $qry,
        );
        return view('admin.epin.epin_requests', $data);
    }

    public function delete_request($id)
    {
        $get_receipt = select('receipt', 'epin_requests', ['id' => $id]);
        DB::table('epin_requests')->where('id', $id)->delete();
        unlink(storage_path('/receipts/' . $get_receipt));
        return redirect('/epin-requests')->with('msg', msg('E-PIN Request has been cancelled.'));
    }

    public function accept_request($id)
    {
        $get_data = DB::table('epin_requests')->where('id', $id)->first();
        for ($i = 0; $i < $get_data->qty; $i++) {
            $epin      = random_int(100000000, 999999999);
            $new_array = array(
                'epin'   => $epin,
                'owner'  => $get_data->userid,
                'amount' => $get_data->amount,
                'type'   => 'Single Use',
            );
            $data[]    = $new_array;
        }
        DB::transaction(function () use ($data, $id) {
            DB::table('epins')->insert($data);
            DB::table('epin_requests')->where('id', $id)->update([
                                                                     'status' => 'Completed',
                                                                 ]);
        });
        return redirect('/epin-requests')->with('msg', msg('E-PIN Request has been Accepted and ' . $data->qty . ' Epins generated .'));
    }

    public function reject_request($id)
    {
        DB::table('epin_requests')->where('id', $id)->update([
                                                                 'status' => 'Rejected',
                                                             ]);
        return redirect('/epin-requests')->with('msg', msg('E-PIN Request has been Rejected .'));
    }

    public function member_epin_generate_history()
    {
        $qry  = DB::table('epins');
        $qry  = $qry->where('generated_by', '!=', null);
        $data = array(
            'title'   => 'Generated E-Pin\'s Records',
            'heading' => 'Generated E-Pin\'s Records',
            'data'    => $qry->paginate(30),
        );
        return view('admin.epin.generate_record', $data);
    }

    public function src_transfer_epins(Request $e)
    {
        $qry = DB::table('epins');
        if (!blank($e->fromdt)) {
            $qry = $qry->where('transferred_date', '>=', $e->fromdt);
        }
        if (!blank($e->todt)) {
            $qry = $qry->where('transferred_date', '<=', $e->todt);
        }
        if (!blank($e->transferred_by)) {
            $qry = $qry->where('transferred_by', id_filter($e->transferred_by));
        }
        $qry  = $qry->where('transferred_by', '!=', null);
        $data = array(
            'title' => 'Transferred E-Pin Records',
            'data'  => $qry->paginate(30)->appends(request()->query()),
        );
        return view('admin.epin.transfer_record', $data);
    }

    public function src_generate_epins(Request $e)
    {
        $qry = DB::table('epins');
        if (!blank($e->fromdt)) {
            $qry = $qry->where('created_at', '>=', $e->fromdt);
        }
        if (!blank($e->todt)) {
            $qry = $qry->where('created_at', '<=', $e->todt);
        }
        if (!blank($e->transferred_by)) {
            $qry = $qry->where('generated_by', $e->transferred_by);
        }
        $qry  = $qry->where('generated_by', '!=', null);
        $data = array(
            'title' => 'E-Pin Generate Records',
            'data'  => $qry->paginate(30)->appends(request()->query()),
        );
        return view('admin.epin.generate_record', $data);
    }

    public function src_epins(Request $e)
    {
        $qry = DB::table('epins');
        if (!blank($e->fromdt)) {
            $qry = $qry->where('used_date', '>=', $e->fromdt);
        }
        if (!blank($e->todt)) {
            $qry = $qry->where('used_date', '<=', $e->todt);
        }
        if (!blank($e->userid)) {
            $qry = $qry->where('owner', id_filter($e->userid));
        }
        if (!blank($e->type)) {
            $qry = $qry->where('type', $e->type);
        }
        if (!blank($e->amount)) {
            $qry = $qry->where('amount', $e->amount);
        }
        if (!blank($e->status)) {
            if ($e->status === 'Used') {
                $qry = $qry->where('used_date', '!=', null);
            }
            if ($e->status === 'Un-Used') {
                $qry = $qry->where('used_date', null);
            }
        }

        $data = array(
            'title' => 'Manage E-Pins',
            'data'  => $qry->paginate(30)->appends(request()->query()),
        );
        return view('admin.epin.epins', $data);

    }

    public function delete_epin($id)
    {
        DB::table('epins')->delete($id);
        echo success('E-Pin has been deleted');
    }

    ### Member Part

    public function save_request_epin(Request $e)
    {
        $e->validate([
                         'amount' => 'required',
                         'qty'    => 'required',
                     ]);
        $receipt = null;
        if ($e->receipt) {
            $receipt = uniqid('', true) . session('member_id') . '.' . $e->receipt->extension();
            $e->receipt->move(storage_path('receipts/'), $receipt);
        }
        DB::table('epin_requests')->insert([
                                               'userid'     => session('member_id'),
                                               'amount'     => $e->amount,
                                               'total_paid' => $e->paid_amt,
                                               'qty'        => $e->qty,
                                               'receipt'    => $receipt,
                                           ]);
        return redirect('/my-epin-requests')->with('msg', msg('Your E-PIN Request has been accepted and sent for approval.'));
    }

    public function my_epin_requests()
    {
        $qry = DB::table('epin_requests')->where('userid', session('member_id'))->paginate(10);

        $data = array(
            'title'   => 'My E-pin Requests',
            'heading' => 'My E-pin Requests',
            'data'    => $qry,
        );
        return view('member.epin.my_epin_requests', $data);
    }

    public function delete_epin_request($id)
    {
        $get_receipt = select('receipt', 'epin_requests', ['id' => $id]);
        DB::table('epin_requests')->where('userid', session('member_id'))->where('id', $id)->delete();
        unlink(storage_path('/receipts/' . $get_receipt));
        return redirect('/my-epin-requests')->with('msg', msg('Your E-PIN Request has been cancelled.'));
    }

    public function my_epins($type)
    {
        $qry = DB::table('epins')->where('owner', session('member_id'));
        if (!blank($type)) {
            if ($type === 'Used') {
                $qry = $qry->where('used_date', '!=', null);
            }
            if ($type === 'Un-Used') {
                $qry = $qry->where('used_date', null);
            }
        }

        $data = array(
            'title'   => 'My ' . $type . ' E-Pins',
            'heading' => 'My ' . $type . ' E-Pins',
            'data'    => $qry->paginate(30),
        );
        return view('member.epin.epins', $data);
    }

    public function transfer_epin(Request $e)
    {
        if (blank($e->amount)) {
            echo errorrecord('E-Pin amount is Required', url('/transfer-epin'));
            return;
        }
        if (blank($e->qty)) {
            echo errorrecord('E-Pin Quantity is Required', url('/transfer-epin'));
            return;
        }
        if (blank($e->user_id)) {
            echo errorrecord('Valid User ID is Required', url('/transfer-epin'));
            return;
        }
        $get = count_all('epins', array(
            'owner'     => session('member_id'), 'amount' => $e->amount,
            'used_date' => null,
        ));
        if ($get == 0 or $get < $e->qty) {
            echo errorrecord('You do not have enough un-used epin to transfer.', url('/transfer-epin'));
            return;
        }
        $array = array(
            'owner'            => id_filter($e->user_id),
            'transferred_by'   => session('member_id'),
            'transferred_date' => date('Y-m-d'),
        );
        DB::table('epins')
          ->where('owner', session('member_id'))
          ->where('amount', $e->amount)
          ->where('used_date', null)
          ->limit($e->qty)
          ->update($array);
        echo success('Epins has been transferred.');
        return;
    }

    public function epin_transfer_history()
    {
        $qry  = DB::table('epins');
        $qry  = $qry->where('transferred_by', session('member_id'));
        $data = array(
            'title'   => 'Transferred E-Pin Records',
            'heading' => 'Transferred E-Pin Records',
            'data'    => $qry->paginate(30),
        );
        return view('member.epin.transfer_record', $data);
    }

    public function epin_generate_history()
    {
        $qry  = DB::table('epins');
        $qry  = $qry->where('generated_by', session('member_id'));
        $data = array(
            'title'   => 'Generated E-Pin Records',
            'heading' => 'Generated E-Pin Records',
            'data'    => $qry->paginate(30),
        );
        return view('member.epin.generate_record', $data);
    }

    public function save_epin_member(Request $e)
    {
        if (blank($e->amount)) {
            echo errorrecord('E-Pin amount is Required', url('/generate-epin'));
            return;
        }
        if (blank($e->qty)) {
            echo errorrecord('E-Pin Quantity is Required', url('/generate-epin'));
            return;
        }

        if ($e->qty > 1000) {
            echo errorrecord('Maximum E-Pin Quantity is 1000', url('/generate-epin'));
            return;
        }

        $wallet_balance = select('balance', 'wallet', array('id' => session('member_id'), 'type' => 'Default'));
        if ($wallet_balance < ($e->amount * $e->qty)) {
            echo errorrecord('Your Wallet doesn\'t have sufficient fund to generate e-PINS', url('/generate-epin'));
            return;
        }
        DB::transaction(function () use ($e) {
            DB::table('wallet')->where(array(
                                           'id' => session('member_id'), 'type' => 'Default',
                                       ))->update(['balance' => DB::raw('balance - ' . ($e->amount * $e->qty))]);
            $data = array();
            for ($i = 0; $i < $e->qty; $i++) {
                $epin      = random_int(10000000, 99999999);
                $new_array = array(
                    'epin'         => $epin,
                    'owner'        => $e->user_id ? id_filter($e->user_id) : session('member_id'),
                    'amount'       => $e->amount,
                    'generated_by' => session('member_id'),
                    'type'         => $e->type,
                );
                array_push($data, $new_array);
            }
            DB::table('epins')->insert($data);
        });
        $e->session()->flash('msg', msg('E-Pins has been generated'));
        echo success('E-Pins has been generated');
        echo script_redirect(url('/epin-generate-history'));
        return;
    }

}
