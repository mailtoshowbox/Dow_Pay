<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EarningController extends Controller
{
    public function manage_funds()
    {
        $data = array(
            'title'   => 'Manage Fund',
            'wallets' => DB::table('wallet')->select('type', 'balance')->where('id', env('TOP_ID'))->get(),
        );
        return view('admin.earning.fund', $data);
    }

    public function delete_user_earning($id)
    : void
    {
        $user = DB::table('earnings')->select('userid', 'amount')->where(['id' => $id])->first();
        wallet_txns($user->userid, $user->amount, 'Remove Fund', 'Debit');
        $wallet = config('config.wallet_types')[0];
        DB::table('wallet')->where(array(
                                       'id'   => $user->userid,
                                       'type' => $wallet,
                                   ))->update(array('balance' => DB::raw('balance-' . $user->amount)));

        DB::table('earnings')->where('id', $id)->delete();
        echo success('Earning has been deleted');
    }

    public function delete_reward($id)
    : void
    {
        DB::table('rewards')->where('id', $id)->delete();
        echo success('Reward has been deleted');
    }

    public function pay_reward($id)
    : void
    {
        DB::table('rewards')->where('id', $id)->update(['status' => 'Delivered', 'pay_date' => date('Y-m-d')]);
        echo success('Reward has been marked as dispatched.');
    }

    public function search_earning()
    {
        $data = array(
            'title' => 'Search Member Earnings',

        );
        return view('admin.earning.search', $data);
    }

    public function search_earning_result(Request $e)
    {
        $result = DB::table('earnings')->leftJoin('members', 'earnings.userid', '=', 'members.id');
        if (!blank(id_filter($e->userid))) {
            $result->where('userid', id_filter($e->userid));
        }
        if (!blank($e->fromdt)) {
            $result->where('date', '>=', $e->fromdt);
        }
        if (!blank($e->todt)) {
            $result->where('date', '<=', $e->todt);
        }
        if (!blank($e->type)) {
            $result->where('type', $e->type);
        }
        $result = $result->where('usertype', 'Member')->select('members.name', 'earnings.*')->orderBy('id', 'DESC')->paginate(15)->appends(request()->query());
        $data   = array(
            'title'    => 'View and Search User Earnings',
            'earnings' => $result,
        );
        return view('admin.earning.earnings', $data);
    }

    public function admin_rewards($type = 'Pending')
    {
        $data = array(
            'title'   => $type . ' User Rewards',
            'rewards' => DB::table('rewards')->join('reward_setting', 'rewards.reward_id', 'reward_setting.id')
                           ->select('reward_setting.reward_name', 'reward_setting.reward_image', 'rewards.id', 'rewards.user_id', 'rewards.status', 'rewards.achieve_date', 'rewards.pay_date')
                           ->where(array(
                                       'rewards.status' => $type,
                                   ))->paginate(10)->appends(request()->query()),

        );
        return view('admin.earning.rewards', $data);
    }

    ########## Member part starts here

    public function my_rewards()
    {
        $data = array(
            'title'    => 'My Rewards',
            'heading'  => 'My Rewards',
            'subtitle' => 'Rewards I have achieved till now',
            'rewards'  => DB::table('rewards')->join('reward_setting', 'rewards.reward_id', 'reward_setting.id')
                            ->select('reward_setting.reward_name', 'reward_setting.reward_image', 'rewards.status', 'rewards.achieve_date', 'rewards.pay_date')
                            ->where(array(
                                        'rewards.user_id' => session('member_id'),
                                    ))->orderBy('id', 'DESC')->paginate(10),
        );
        return view('member.wallet.rewards', $data);
    }

    public function member_earning()
    {
        $data = array(
            'title'    => 'My Earnings',
            'heading'  => 'My Earnings',
            'subtitle' => 'View and search all your earnings',
            'earnings' => DB::table('earnings')->where(array(
                                                           'userid' => session('member_id'), 'usertype' => 'Member',
                                                       ))->orderBy('id', 'DESC')->paginate(10),
        );

        
        return view('member.wallet.earnings', $data);
    }

    public function member_earning_type_wise($type)
    {
        $type = html_entity_decode($type);
        $data = array(
            'title'    => 'My Earnings',
            'heading'  => 'My Earnings',
            'subtitle' => 'View and search all your earnings',
            'earnings' => DB::table('earnings')->where(array(
                                                           'userid' => session('member_id'), 'usertype' => 'Member',
                                                           'type'   => $type,
                                                       ))->orderBy('id', 'DESC')->paginate(10),
        );
        return view('member.wallet.earnings', $data);
    }

    public function earningsearch(Request $e)
    {
        $result = DB::table('earnings')->where('userid', session('member_id'));

        if (!blank($e->fromdt)) {
            $result->where('date', '>=', $e->fromdt);
        }
        if (!blank($e->todt)) {
            $result->where('date', '<=', $e->todt);
        }
        if (!blank($e->type)) {
            $result->where('type', $e->type);
        }
        $result = $result->where('usertype', 'Member')->orderBy('id', 'DESC')->paginate(15)->appends(request()->query());
        $data   = array(
            'title'    => 'My Earnings',
            'heading'  => 'My Earnings',
            'subtitle' => 'View and search all your earnings',
            'earnings' => $result,
        );
        return view('member.wallet.earnings', $data);
    }
}
