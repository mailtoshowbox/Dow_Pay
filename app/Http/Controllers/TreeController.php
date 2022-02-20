<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TreeController extends Controller
{
    public function admin_tree($id = '')
    {
        if ($id == '') {
            $id = id_filter(request()->post('user'));
        }
        $data = array(
            'title' => 'Member Tree',
            'topid' => $id ? $id : env('TOP_ID'),
        );
        return view('admin.tree.tree', $data);
    }

    public function shift_tree(Request $e)
    {
        $from = id_filter($e->userid);
        $to   = id_filter($e->userid2);
        $leg  = id_filter($e->leg);

        $position         = get_extreme_leg($to, $leg);
        $member           = \App\Member::find($from);
        $old_position     = $member->position;
        $old_leg          = $member->leg;
        $member->position = $position;
        $member->leg      = $leg;
        if ($e->mv_sponsor == 1) {
            $member->sponsor = $to;
        }
        $member->save();

        $member           = \App\Member::find($old_position);
        $member->$old_leg = 0;
        $member->save();

        $member       = \App\Member::find($position);
        $member->$leg = $from;
        $member->save();

        DB::table('leg_info')->where('id', $from)->update(['parent_id' => $position]);
        DB::table('user_relations')->where('id', $from)->update(['parent_id' => $position]);
        return redirect()->back()->with('msg', msg(env('ID_EXT') . $from . '\'s Tree has been shifted under ' . env('ID_EXT') . $to));
    }

    public function admin_downline_list($type = '', $id = '')
    {
        if ($id == '') {
            $id = id_filter(request()->post('user'));
        }
        $topid = $id ? $id : env('TOP_ID');
        if ($type === 'Active') {
            $list = green_downline_list($topid);
        } else if ($type === 'Inactive') {
            $list = red_downline_list($topid);
        } else {
            $list = get_downline_list($topid);
        }
        $data = array(
            'title' => 'User Downline List (ID: ' . $topid . ')',
            'red'   => total_red(session('member_id')),
            'green' => total_green(session('member_id')),
            'users' => $list,
        );
        return view('admin.tree.users_list', $data);
    }

    public function admin_level_counting($id = '')
    {
        if ($id == '') {
            $id = id_filter(request()->post('user'));
        }
        $topid = $id ? $id : env('TOP_ID');
        $list  = DB::table('levels')->where('id', $topid)->first();
        $data  = array(
            'title' => 'User Level Counting (ID: ' . $topid . ')',
            'list'  => $list,
        );
        return view('admin.tree.users_level_counting', $data);
    }


    public function src_users_downline(Request $e)
    {
        $id    = id_filter(request()->post('user'));
        $topid = $id ? $id : env('TOP_ID');
        $data  = array(
            'title'  => 'User Downline List (ID: ' . env('ID_EXT') . $topid . ')',
            'red'    => total_red(session('member_id')),
            'todt'   => $e->todt,
            'fromdt' => $e->fromdt,
            'green'  => total_green(session('member_id')),
            'users'  => get_downline_list_search($topid, $e->fromdt, $e->todt),
        );
        return view('admin.tree.users_list', $data);
    }

    public function team_report(Request $e)
    {
        $id    = id_filter(request()->post('user'));
        $topid = $id ? $id : env('TOP_ID');
        $data  = array(
            'title'         => 'Genealogy Report of ID: ' . env('ID_EXT') . $topid,
            'topid'         => $topid,
            'leg_info'      => DB::table('leg_info')->where('id', $topid)->first(),
            'user_relation' => DB::table('user_relations')->select('my_bv', 'my_investment_amount')->where('id', $topid)->first(),
        );
        return view('admin.tree.genealogy', $data);
    }


    public function admin_ref_list()
    {
        $id    = id_filter(request()->post('user'));
        $topid = $id ? $id : env('TOP_ID');
        $data  = array(
            'title'    => 'My Direct Referred List',
            'heading'  => 'My Direct Referred List',
            'subtitle' => 'List of members I have referred directly',
            'users'    => \App\Member::where('sponsor', $topid)->paginate(10),
        );
        return view('admin.tree.direct_users_list', $data);
    }

    ## member part
    public function user_tree($id = '')
    {
        if ($id == '') {
            $id = id_filter(request()->post('user'));
        }
        $id = $id ? $id : session('member_id');
        if (session('member_id') !== $id) {
            if (check_if_downline(session('member_id'), $id) === null && $id !== '')
                return redirect('/my-tree')->with('msg', '<div class="alert alert-danger">Invalid ID or ID is not in your downline</div>');
        }
        $data = array(
            'title'    => 'My Genealogy Tree',
            'heading'  => 'My Genealogy Tree',
            'subtitle' => 'View and Search any downline under you',
            'topid'    => $id ? $id : session('member_id'),
            
        ); 
        return view('member.tree.tree', $data);
    }


    public function member_downline_list($type = '')
    {
        if ($type === 'Active') {
            $list = green_downline_list(session('member_id'));
        } else if ($type === 'Inactive') {
            $list = red_downline_list(session('member_id'));
        } else {
            $list = get_downline_list(session('member_id'));
        }
        $data = array(
            'title'    => 'Complete Downline List',
            'heading'  => 'My Downline List',
            'subtitle' => 'See the Complete ' . $type . ' Downline List',
            'red'      => total_red(session('member_id')),
            'green'    => total_green(session('member_id')),
            'users'    => $list,
        );
        return view('member.tree.users_list', $data);
    }

    public function my_referred_list()
    {
        $data = array(
            'title'    => 'My Direct Referred List',
            'heading'  => 'My Direct Referred List',
            'subtitle' => 'List of members I have referred directly',
            'users'    => \App\Member::where('sponsor', session('member_id'))->orderBy('serial', 'DESC')->paginate(10),
        );
        return view('member.tree.direct_users_list', $data);
    }

    public function src_my_downline(Request $e)
    {
        $data = array(
            'title'    => session('name') . ' - ' . env('APP_NAME'),
            'heading'  => 'My Prospectives',
            'subtitle' => 'Your complete prospective\'s data',
            'red'      => total_red(session('member_id')),
            'todt'     => $e->todt,
            'fromdt'   => $e->fromdt,
            'green'    => total_green(session('member_id')),
            'users'    => get_downline_list_search(session('member_id'), $e->fromdt, $e->todt),
        );
        return view('member.tree.users_list', $data);
    }

    public function my_team_report($id = '')
    {
        if (blank($id)) {
            $id = id_filter(request()->post('user'));
        }
        if (check_if_downline(session('member_id'), $id) === null && $id !== '') {
            return redirect('/my-team-report')->with('msg', '<div class="alert alert-danger">Invalid ID or ID is not in your downline</div>');
        }
        $topid = $id ? $id : session('member_id');
        $data  = array(
            'title'         => 'Genealogy Report',
            'heading'       => 'Genealogy Report',
            'subtitle'      => 'View and Search any downline\'s genealogy report under you',
            'topid'         => $topid,
            'leg_info'      => DB::table('leg_info')->where('id', $topid)->first(),
            'user_relation' => DB::table('user_relations')->select('my_bv', 'my_investment_amount')->where('id', $topid)->first(),
        );
        return view('member.tree.genealogy', $data);
    }

    public function my_level_counting($id = '')
    {
        if (blank($id)) {
            $id = id_filter(request()->post('user'));
        }
        if (check_if_downline(session('member_id'), $id) === null && $id !== '') {
            return redirect('/my-level-counting')->with('msg', '<div class="alert alert-danger">Invalid ID or ID is not in your downline</div>');
        }
        $topid = $id ? $id : session('member_id');
        $list  = DB::table('levels')->where('id', $topid)->first();
        $data  = array(
            'title' => 'Level Counting (ID: ' . $topid . ')',
            'list'  => $list,
        );
        return view('member.tree.my_level_counting', $data);
    }
}
