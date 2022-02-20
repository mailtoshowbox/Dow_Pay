<?php //004fb
use App\Member;
use App\User_relation;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
if (!function_exists('member_autopool')) {
    function member_autopool($sponsor)
    {
        if (env('LEG_NUMBER') == '1') {
            return recursive_member_pool_find_a($sponsor);
        }
        if (env('LEG_NUMBER') == '2') {
            return recursive_member_pool_find_b($sponsor);
        }
        if (env('LEG_NUMBER') == '3') {
            return recursive_member_pool_find_c($sponsor);
        }
        if (env('LEG_NUMBER') == '4') {
            return recursive_member_pool_find_d($sponsor);
        }
        if (env('LEG_NUMBER') == '5') {
            return recursive_member_pool_find_e($sponsor);
        }
        if (env('LEG_NUMBER') == '6') {
            return recursive_member_pool_find_f($sponsor);
        }
        if (env('LEG_NUMBER') == '7') {
            return recursive_member_pool_find_g($sponsor);
        }
        if (env('LEG_NUMBER') == '8') {
            return recursive_member_pool_find_h($sponsor);
        }
        if (env('LEG_NUMBER') == '9') {
            return recursive_member_pool_find_i($sponsor);
        }
        if (env('LEG_NUMBER') == '10') {
            return recursive_member_pool_find_j($sponsor);
        }
    }
}
if (!function_exists('sms')) {
    function sms($phone, $msg)
    {
        $phone = str_ireplace('+91', '', $phone);
        $url   = str_ireplace(['{{phone}}', '{{msg}}',], [$phone, rawurlencode($msg)], env('SMS_API'));
       // return curl($url);
    }
}
if (!function_exists('find_autopool')) {
    function find_autopool($sponsor = null)
    {
        if ($sponsor !== null) {
            return member_autopool($sponsor);
        }
        if (env('LEG_NUMBER') == '1') {
            $result   = Member::select('id', 'A')->where('A', 0)->orderBy('serial', 'ASC')
                              ->limit(1)->first();
            $id       = $result->id;
            $position = 'A';
        } else if (env('LEG_NUMBER') == '2') {
            $result = Member::select('id', 'A', 'B')->where('A', 0)->orWhere('B', 0)->orderBy('serial', 'ASC')
                            ->limit(1)->first();
            $id     = $result->id;
            if (trim($result->A) == '0') {
                $position = 'A';
            } else {
                $position = 'B';
            }
        } else if (env('LEG_NUMBER') == '3') {
            $result = DB::table('members')->select('id', 'A', 'B', 'C')->where('A', 0)->orWhere('B', 0)->orWhere('C', 0)
                        ->orderBy('serial', 'ASC')
                        ->limit(1)->first();
            $id     = $result->id;
            if (trim($result->A) == '0') {
                $position = 'A';
            } else if (trim($result->B) == '0') {
                $position = 'B';
            } else {
                $position = 'C';
            }
        } else if (env('LEG_NUMBER') == '4') {
            $result = DB::table('members')->select('id', 'A', 'B', 'C', 'D')->where('A', 0)->orWhere('B', 0)
                        ->orWhere('C', 0)->orWhere('D', 0)->orderBy('serial', 'ASC')
                        ->limit(1)->first();
            $id     = $result->id;
            if (trim($result->A) == '0') {
                $position = 'A';
            } else if (trim($result->B) == '0') {
                $position = 'B';
            } else if (trim($result->C) == '0') {
                $position = 'C';
            } else {
                $position = 'D';
            }
        } else if (env('LEG_NUMBER') == '5') {
            $result = DB::table('members')->select('id', 'A', 'B', 'C', 'D', 'E')->where('A', 0)->orWhere('B', 0)
                        ->orWhere('C', 0)->orWhere('D', 0)->orWhere('E', 0)->orderBy('serial', 'ASC')
                        ->limit(1)->first();
            $id     = $result->id;
            if (trim($result->A) == '0') {
                $position = 'A';
            } else if (trim($result->B) == '0') {
                $position = 'B';
            } else if (trim($result->C) == '0') {
                $position = 'C';
            } else if (trim($result->D) == '0') {
                $position = 'D';
            } else {
                $position = 'E';
            }
        } else if (env('LEG_NUMBER') == '6') {
            $result = DB::table('members')->select('id', 'A', 'B', 'C', 'D', 'E', 'F')
                        ->where('A', 0)
                        ->orWhere('B', 0)
                        ->orWhere('C', 0)
                        ->orWhere('D', 0)
                        ->orWhere('E', 0)
                        ->orWhere('F', 0)
                        ->orderBy('serial', 'ASC')
                        ->limit(1)->first();
            $id     = $result->id;
            if (trim($result->A) == '0') {
                $position = 'A';
            } else if (trim($result->B) == '0') {
                $position = 'B';
            } else if (trim($result->C) == '0') {
                $position = 'C';
            } else if (trim($result->D) == '0') {
                $position = 'D';
            } else if (trim($result->E) == '0') {
                $position = 'E';
            } else {
                $position = 'F';
            }
        } else if (env('LEG_NUMBER') == '7') {
            $result = DB::table('members')->select('id', 'A', 'B', 'C', 'D', 'E', 'F', 'G')
                        ->where('A', 0)
                        ->orWhere('B', 0)
                        ->orWhere('C', 0)
                        ->orWhere('D', 0)
                        ->orWhere('E', 0)
                        ->orWhere('F', 0)
                        ->orWhere('G', 0)
                        ->orderBy('serial', 'ASC')
                        ->limit(1)->first();
            $id     = $result->id;
            if (trim($result->A) == '0') {
                $position = 'A';
            } else if (trim($result->B) == '0') {
                $position = 'B';
            } else if (trim($result->C) == '0') {
                $position = 'C';
            } else if (trim($result->D) == '0') {
                $position = 'D';
            } else if (trim($result->E) == '0') {
                $position = 'E';
            } else if (trim($result->F) == '0') {
                $position = 'F';
            } else {
                $position = 'G';
            }
        } else if (env('LEG_NUMBER') == '8') {
            $result = DB::table('members')->select('id', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H')
                        ->where('A', 0)
                        ->orWhere('B', 0)
                        ->orWhere('C', 0)
                        ->orWhere('D', 0)
                        ->orWhere('E', 0)
                        ->orWhere('F', 0)
                        ->orWhere('G', 0)
                        ->orWhere('H', 0)
                        ->orderBy('serial', 'ASC')
                        ->limit(1)->first();
            $id     = $result->id;
            if (trim($result->A) == '0') {
                $position = 'A';
            } else if (trim($result->B) == '0') {
                $position = 'B';
            } else if (trim($result->C) == '0') {
                $position = 'C';
            } else if (trim($result->D) == '0') {
                $position = 'D';
            } else if (trim($result->E) == '0') {
                $position = 'E';
            } else if (trim($result->F) == '0') {
                $position = 'F';
            } else if (trim($result->G) == '0') {
                $position = 'G';
            } else {
                $position = 'H';
            }
        } else if (env('LEG_NUMBER') == '9') {
            $result = DB::table('members')->select('id', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I')
                        ->where('A', 0)
                        ->orWhere('B', 0)
                        ->orWhere('C', 0)
                        ->orWhere('D', 0)
                        ->orWhere('E', 0)
                        ->orWhere('F', 0)
                        ->orWhere('G', 0)
                        ->orWhere('H', 0)
                        ->orWhere('I', 0)
                        ->orderBy('serial', 'ASC')
                        ->limit(1)->first();
            $id     = $result->id;
            if (trim($result->A) == '0') {
                $position = 'A';
            } else if (trim($result->B) == '0') {
                $position = 'B';
            } else if (trim($result->C) == '0') {
                $position = 'C';
            } else if (trim($result->D) == '0') {
                $position = 'D';
            } else if (trim($result->E) == '0') {
                $position = 'E';
            } else if (trim($result->F) == '0') {
                $position = 'F';
            } else if (trim($result->G) == '0') {
                $position = 'G';
            } else if (trim($result->H) == '0') {
                $position = 'H';
            } else {
                $position = 'I';
            }
        } else if (env('LEG_NUMBER') == '10') {
            $result = DB::table('members')->select('id', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J')
                        ->where('A', 0)
                        ->orWhere('B', 0)
                        ->orWhere('C', 0)
                        ->orWhere('D', 0)
                        ->orWhere('E', 0)
                        ->orWhere('F', 0)
                        ->orWhere('G', 0)
                        ->orWhere('H', 0)
                        ->orWhere('I', 0)
                        ->orWhere('J', 0)
                        ->orderBy('serial', 'ASC')
                        ->limit(1)->first();
            $id     = $result->id;
            if (trim($result->A) == '0') {
                $position = 'A';
            } else if (trim($result->B) == '0') {
                $position = 'B';
            } else if (trim($result->C) == '0') {
                $position = 'C';
            } else if (trim($result->D) == '0') {
                $position = 'D';
            } else if (trim($result->E) == '0') {
                $position = 'E';
            } else if (trim($result->F) == '0') {
                $position = 'F';
            } else if (trim($result->G) == '0') {
                $position = 'G';
            } else if (trim($result->H) == '0') {
                $position = 'H';
            } else if (trim($result->I) == '0') {
                $position = 'I';
            } else {
                $position = 'J';
            }
        }

        return [
            'position' => $id,
            'leg'      => $position,
        ];
    }
}
if (!function_exists('get_extreme_leg')) {
    function get_extreme_leg($sponsor, $leg)
    {
        $id = DB::table('members')
                ->select('id', $leg)
                ->where([
                            'position' => $sponsor,
                            'leg'      => $leg,
                        ])
                ->orderBy('serial', 'ASC')
                ->first();
        if (!empty($id)) {
            if ($id->$leg !== 0) {
                return get_extreme_leg($id->id, $leg);
            }
            return $id->id;
        }
        return $sponsor;
    }
}
if (!function_exists('count_all')) {
    function count_all($table, $where = 1)
    {
        $result = DB::table($table)->where($where)->count('id');
        if (!empty($result)) {
            return $result;
        }
        return 0;
    }
}
if (!function_exists('success')) {
    function success($msg = 'Your record has been successfully saved.')
    {
        $str = '<div class="text-center"><img class="img-fluid" style="max-height: 100px; width:auto" src="' . asset('material/img//success.png') . '"><h3 class="mt-1" style="font-weight: 500 !important; font-size: 22px !important;">' . $msg . '</h3></div>';
        return trim($str);
    }
}
// For add'active' class for activated route nav-item
function active_class($path, $active = 'active') {
    return call_user_func_array('Request::is', (array)$path) ? $active : '';
  }
  // For add'active' class for activated route nav-item
function active_submenu_class($path, $active = 'collapsed') {
    return call_user_func_array('Request::is', (array)$path) ? $active : '';
  }
  // For checking activated route
  function is_active_route($path) {
 ;
    return call_user_func_array('Request::is', (array)$path) ? 'true' : 'false';
  }
  
  // For add 'show' class for activated route collapse
  function show_class($path) {
    return call_user_func_array('Request::is', (array)$path) ? 'show' : '';
  }
if (!function_exists('msg')) {
    function msg($msg, $type = 'success', $close = true)
    {
        $icon = asset('material/img//tick.png');
        if ($type === 'info')
            $icon = asset('material/img//info.png');
        if ($type === 'warning')
            $icon = asset('material/img//warning.png');
        if ($type === 'danger')
            $icon = asset('material/img//danger.png');
        $str = '<div role="alert" class="alert mb-2 mt-1 alert-dismissible fade show alert-' . $type . '"><img src="' . $icon . '"></span> ' . $msg;
        $str .= '<button type="button" class="close" data-dismiss="alert" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button></div>';
        return $str;
    }
}
if (!function_exists('wallet_txns')) {
    function wallet_txns($user_id, $amount, $desc = 'Add fund', $type = 'Credit')
    {
        $array = [
            'user_id'     => $user_id,
            'type'        => $type,
            'description' => $desc,
            'amount'      => $amount,
            'date'        => date('Y-m-d'),
        ];
        return DB::table('wallet_txns')->insertGetId($array);
    }
}
if (!function_exists('check_user')) {
    function check_user($userid)
    {
        $result = DB::table('members')->where('id', id_filter($userid))->count('id');
        if (!empty($result)) {
            return $result;
        }
        return 0;
    }
}

if (!function_exists('script_redirect')) {
    function script_redirect($url, $msg = null)
    {
        if ($msg !== null)
            session()->flash('msg', $msg);
        $str = '<script type="text/javascript">document.location.href="' . $url . '"</script> ';
        return trim($str);
    }
}
if (!function_exists('errorrecord')) {
    function errorrecord($msg = 'Some Error occurred. Try again', $url = null)
    {
        if ($url !== null) {
            $url = '<p class="text-center mt-2"><a class="btn btn-warning" href="' . $url . '">Try Again</a></p>';
        }
        $str = '<div class="text-center"><img class="img-fluid" style="max-height: 100px; width:auto" src="' . asset('material/img/problem.png') . '"><h2 class="mt-1" style="font-weight: 500 !important; font-size: 22px !important;">' . $msg . '' . $url . '</h2></div>';
        return trim($str);
    }
}
if (!function_exists('select')) {
    function select($data, $table, $where = 1, $order = 'DESC')
    {
        $result = DB::table($table)->select($data)->where($where)->orderBy('id', $order)->first();
        if (!empty($result)) {
            return $result->$data;
        }
        return null;
    }
}
if (!function_exists('id_filter')) {
    function id_filter($id)
    {
        return trim(str_ireplace(env('ID_EXT'), '', $id));
    }
}
if (!function_exists('check_if_downline')) {
    function check_if_downline($parent_id, $child_id)
    {
        if ($parent_id == $child_id) {
            return $parent_id;
        }
        $total = User_relation::find($parent_id);
        if ($total) {
            $id = $total->descendantsAndSelf()->where('id', $child_id)->first();
            if (empty($id)) {
                return null;
            }
            return $id->id;
        }
        return null;
    }
}

if (!function_exists('blank_tree')) {
    function blank_tree($id, $leg)
    {
        echo '<a target="_blank" href="' . url('/register/' . $id . '/' . $leg) . '" data-toggle="tooltip" title="Click to register below: ' . env('ID_EXT') . $id . '">';
        echo '<img src="' . asset('material/img//new_user.png') . '" alt="New User">';
        echo '</a>';
        return $id;
    }
}
if (!function_exists('generate_tree')) {
    function generate_tree($id, $url, $leg = 'A')

 
    {
        echo "IDD".$id;
        $detail       = Member::select('id', 'name', 'sponsor', 'my_topup', 'topup_date', 'reg_product', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'rank', 'avatar', 'created_at')
                              ->where('id', $id)->first();

        $rank_name    = cache_select('rank_name', 'member_ranks', ['id' => $detail->rank]);
        $package_name = cache_select('name', 'products', ['id' => $detail->reg_product]);
        $more_detail  = DB::table('leg_info')->where('id', $id)->first();
        $status       = DB::table('user_relations')->select('status', 'my_bv', 'my_investment_amount')->where('id', $id)
                          ->first();

        $class = 'green_tree';
        $image = asset('material/img/green_user.png');
        if (!empty($status)) {
            if ($status->status == 0) {
                $class = 'red_tree';
                $image = asset('material/img//red_user.png');
            }
            if ($detail->avatar !== null)
                $image = asset('storage/pics/' . $detail->avatar);
            echo '<a class="text-center ' . $class . '" href="' . url($url) . '/' . $detail->id . '"';
            echo 'data-toggle="popover" title="' . env('ID_EXT') . $detail->id . '" data-content="';
            echo '<span>Name:</span> ' . $detail->name;
            echo '<br/><span>Sponsor:</span> ' . env('ID_EXT') . $detail->sponsor;
            echo '<br/><span>Registration Date:</span> ' . date('Y-m-d', strtotime($detail->created_at));
            if (!blank($package_name)):
                echo '<br/><span>Package:</span> ' . $package_name;
                echo '<br/><span>Activation Date:</span> ' . date('Y-m-d', strtotime($detail->topup_date));
                echo '<br/><span>Activation/Investment Amount:</span> ' . env('CURRENCY_SIGN') . ' ' . $detail->my_topup;
            endif;
            if (!blank($rank_name))
                echo '<br/><span>Rank:</span> ' . $rank_name;
            if (env('PV_BASED_PLAN') == true)
                echo '<br/><span>Own PV/BV:</span> ' . $status->my_bv;
            if (env('INVESTMENT_PLAN') == true)
                echo '<br/><span>Own Investment:</span> ' . env('CURRENCY_SIGN') . ' ' . $status->my_investment_amount;
            if (env('ENABLE_DONATION_PLAN') == true)
                echo '<br/><span>Last Help:</span> ' . env('CURRENCY_SIGN') . ' ' . $detail->my_topup;

            if (env('LEG_NUMBER') == 1) {
                echo '<br/><span>Total Active Downline:</span> ' . $more_detail->total_a;
                if (env('PV_BASED_PLAN') == true)
                    echo '<br/><span>Downline PV/BV:</span> ' . $more_detail->total_a_bv;
                if (env('INVESTMENT_PLAN') == true)
                    echo '<br/><span>Downline Investments:</span> ' . env('CURRENCY_SIGN') . ' ' . $more_detail->total_a_investments;
            }

            if (env('LEG_NUMBER') == 2) {
                echo '<br/><span>Total Active Left:</span> ' . $more_detail->total_a;
                echo '<br/><span>Total Active Right:</span> ' . $more_detail->total_b;
                if (env('PV_BASED_PLAN') == true) {
                    echo '<br/><span>Left PV/BV:</span> ' . $more_detail->total_a_bv;
                    echo '<br/><span>Right PV/BV:</span> ' . $more_detail->total_b_bv;
                }
                if (env('INVESTMENT_PLAN') == true) {
                    echo '<br/><span>Left Investments:</span> ' . env('CURRENCY_SIGN') . ' ' . $more_detail->total_a_investments;
                    echo '<br/><span>Right Investments:</span> ' . env('CURRENCY_SIGN') . ' ' . $more_detail->total_b_investments;
                }
            }

            if (env('LEG_NUMBER') == 3) {
                echo '<br/><span>Total Active A:</span> ' . $more_detail->total_a;
                echo '<br/><span>Total Active B:</span> ' . $more_detail->total_b;
                echo '<br/><span>Total Active C:</span> ' . $more_detail->total_c;
                if (env('PV_BASED_PLAN') == true) {
                    echo '<br/><span>A PV/BV:</span> ' . $more_detail->total_a_bv;
                    echo '<br/><span>B PV/BV:</span> ' . $more_detail->total_b_bv;
                    echo '<br/><span>C PV/BV:</span> ' . $more_detail->total_c_bv;
                }
                if (env('INVESTMENT_PLAN') == true) {
                    echo '<br/><span>A Investments:</span> ' . env('CURRENCY_SIGN') . ' ' . $more_detail->total_a_investments;
                    echo '<br/><span>B Investments:</span> ' . env('CURRENCY_SIGN') . ' ' . $more_detail->total_b_investments;
                    echo '<br/><span>C Investments:</span> ' . env('CURRENCY_SIGN') . ' ' . $more_detail->total_c_investments;
                }
            }
            if (env('LEG_NUMBER') == 4) {
                echo '<br/><span>Total Active A:</span> ' . $more_detail->total_a;
                echo '<br/><span>Total Active B:</span> ' . $more_detail->total_b;
                echo '<br/><span>Total Active C:</span> ' . $more_detail->total_c;
                echo '<br/><span>Total Active D:</span> ' . $more_detail->total_d;
                if (env('PV_BASED_PLAN') == true) {
                    echo '<br/><span>A PV/BV:</span> ' . $more_detail->total_a_bv;
                    echo '<br/><span>B PV/BV:</span> ' . $more_detail->total_b_bv;
                    echo '<br/><span>C PV/BV:</span> ' . $more_detail->total_c_bv;
                    echo '<br/><span>D PV/BV:</span> ' . $more_detail->total_d_bv;
                }
                if (env('INVESTMENT_PLAN') == true) {
                    echo '<br/><span>A Investments:</span> ' . env('CURRENCY_SIGN') . ' ' . $more_detail->total_a_investments;
                    echo '<br/><span>B Investments:</span> ' . env('CURRENCY_SIGN') . ' ' . $more_detail->total_b_investments;
                    echo '<br/><span>C Investments:</span> ' . env('CURRENCY_SIGN') . ' ' . $more_detail->total_c_investments;
                    echo '<br/><span>D Investments:</span> ' . env('CURRENCY_SIGN') . ' ' . $more_detail->total_d_investments;
                }
            }
            if (env('LEG_NUMBER') == 5) {
                echo '<br/><span>Total Active A:</span> ' . $more_detail->total_a;
                echo '<br/><span>Total Active B:</span> ' . $more_detail->total_b;
                echo '<br/><span>Total Active C:</span> ' . $more_detail->total_c;
                echo '<br/><span>Total Active D:</span> ' . $more_detail->total_d;
                echo '<br/><span>Total Active E:</span> ' . $more_detail->total_e;
                if (env('PV_BASED_PLAN') == true) {
                    echo '<br/><span>A PV/BV:</span> ' . $more_detail->total_a_bv;
                    echo '<br/><span>B PV/BV:</span> ' . $more_detail->total_b_bv;
                    echo '<br/><span>C PV/BV:</span> ' . $more_detail->total_c_bv;
                    echo '<br/><span>D PV/BV:</span> ' . $more_detail->total_d_bv;
                    echo '<br/><span>E PV/BV:</span> ' . $more_detail->total_e_bv;
                }
                if (env('INVESTMENT_PLAN') == true) {
                    echo '<br/><span>A Investments:</span> ' . env('CURRENCY_SIGN') . ' ' . $more_detail->total_a_investments;
                    echo '<br/><span>B Investments:</span> ' . env('CURRENCY_SIGN') . ' ' . $more_detail->total_b_investments;
                    echo '<br/><span>C Investments:</span> ' . env('CURRENCY_SIGN') . ' ' . $more_detail->total_c_investments;
                    echo '<br/><span>D Investments:</span> ' . env('CURRENCY_SIGN') . ' ' . $more_detail->total_d_investments;
                    echo '<br/><span>E Investments:</span> ' . env('CURRENCY_SIGN') . ' ' . $more_detail->total_e_investments;
                }
            }
            if (env('LEG_NUMBER') == 6) {
                echo '<br/><span>Total Active A:</span> ' . $more_detail->total_a;
                echo '<br/><span>Total Active B:</span> ' . $more_detail->total_b;
                echo '<br/><span>Total Active C:</span> ' . $more_detail->total_c;
                echo '<br/><span>Total Active D:</span> ' . $more_detail->total_d;
                echo '<br/><span>Total Active E:</span> ' . $more_detail->total_e;
                echo '<br/><span>Total Active F:</span> ' . $more_detail->total_f;
                if (env('PV_BASED_PLAN') == true) {
                    echo '<br/><span>A PV/BV:</span> ' . $more_detail->total_a_bv;
                    echo '<br/><span>B PV/BV:</span> ' . $more_detail->total_b_bv;
                    echo '<br/><span>C PV/BV:</span> ' . $more_detail->total_c_bv;
                    echo '<br/><span>D PV/BV:</span> ' . $more_detail->total_d_bv;
                    echo '<br/><span>E PV/BV:</span> ' . $more_detail->total_e_bv;
                    echo '<br/><span>F PV/BV:</span> ' . $more_detail->total_f_bv;
                }
                if (env('INVESTMENT_PLAN') == true) {
                    echo '<br/><span>A Investments:</span> ' . env('CURRENCY_SIGN') . ' ' . $more_detail->total_a_investments;
                    echo '<br/><span>B Investments:</span> ' . env('CURRENCY_SIGN') . ' ' . $more_detail->total_b_investments;
                    echo '<br/><span>C Investments:</span> ' . env('CURRENCY_SIGN') . ' ' . $more_detail->total_c_investments;
                    echo '<br/><span>D Investments:</span> ' . env('CURRENCY_SIGN') . ' ' . $more_detail->total_d_investments;
                    echo '<br/><span>E Investments:</span> ' . env('CURRENCY_SIGN') . ' ' . $more_detail->total_e_investments;
                    echo '<br/><span>F Investments:</span> ' . env('CURRENCY_SIGN') . ' ' . $more_detail->total_f_investments;
                }
            }
            if (env('LEG_NUMBER') == 7) {
                echo '<br/><span>Total Active A:</span> ' . $more_detail->total_a;
                echo '<br/><span>Total Active B:</span> ' . $more_detail->total_b;
                echo '<br/><span>Total Active C:</span> ' . $more_detail->total_c;
                echo '<br/><span>Total Active D:</span> ' . $more_detail->total_d;
                echo '<br/><span>Total Active E:</span> ' . $more_detail->total_e;
                echo '<br/><span>Total Active F:</span> ' . $more_detail->total_f;
                echo '<br/><span>Total Active G:</span> ' . $more_detail->total_g;
                if (env('PV_BASED_PLAN') == true) {
                    echo '<br/><span>A PV/BV:</span> ' . $more_detail->total_a_bv;
                    echo '<br/><span>B PV/BV:</span> ' . $more_detail->total_b_bv;
                    echo '<br/><span>C PV/BV:</span> ' . $more_detail->total_c_bv;
                    echo '<br/><span>D PV/BV:</span> ' . $more_detail->total_d_bv;
                    echo '<br/><span>E PV/BV:</span> ' . $more_detail->total_e_bv;
                    echo '<br/><span>F PV/BV:</span> ' . $more_detail->total_f_bv;
                    echo '<br/><span>G PV/BV:</span> ' . $more_detail->total_g_bv;
                }
                if (env('INVESTMENT_PLAN') == true) {
                    echo '<br/><span>A Investments:</span> ' . env('CURRENCY_SIGN') . ' ' . $more_detail->total_a_investments;
                    echo '<br/><span>B Investments:</span> ' . env('CURRENCY_SIGN') . ' ' . $more_detail->total_b_investments;
                    echo '<br/><span>C Investments:</span> ' . env('CURRENCY_SIGN') . ' ' . $more_detail->total_c_investments;
                    echo '<br/><span>D Investments:</span> ' . env('CURRENCY_SIGN') . ' ' . $more_detail->total_d_investments;
                    echo '<br/><span>E Investments:</span> ' . env('CURRENCY_SIGN') . ' ' . $more_detail->total_e_investments;
                    echo '<br/><span>F Investments:</span> ' . env('CURRENCY_SIGN') . ' ' . $more_detail->total_f_investments;
                    echo '<br/><span>G Investments:</span> ' . env('CURRENCY_SIGN') . ' ' . $more_detail->total_g_investments;
                }
            }
            if (env('LEG_NUMBER') == 8) {
                echo '<br/><span>Total Active A:</span> ' . $more_detail->total_a;
                echo '<br/><span>Total Active B:</span> ' . $more_detail->total_b;
                echo '<br/><span>Total Active C:</span> ' . $more_detail->total_c;
                echo '<br/><span>Total Active D:</span> ' . $more_detail->total_d;
                echo '<br/><span>Total Active E:</span> ' . $more_detail->total_e;
                echo '<br/><span>Total Active F:</span> ' . $more_detail->total_f;
                echo '<br/><span>Total Active G:</span> ' . $more_detail->total_g;
                echo '<br/><span>Total Active H:</span> ' . $more_detail->total_h;
                if (env('PV_BASED_PLAN') == true) {
                    echo '<br/><span>A PV/BV:</span> ' . $more_detail->total_a_bv;
                    echo '<br/><span>B PV/BV:</span> ' . $more_detail->total_b_bv;
                    echo '<br/><span>C PV/BV:</span> ' . $more_detail->total_c_bv;
                    echo '<br/><span>D PV/BV:</span> ' . $more_detail->total_d_bv;
                    echo '<br/><span>E PV/BV:</span> ' . $more_detail->total_e_bv;
                    echo '<br/><span>F PV/BV:</span> ' . $more_detail->total_f_bv;
                    echo '<br/><span>G PV/BV:</span> ' . $more_detail->total_g_bv;
                    echo '<br/><span>H PV/BV:</span> ' . $more_detail->total_h_bv;
                }
                if (env('INVESTMENT_PLAN') == true) {
                    echo '<br/><span>A Investments:</span> ' . env('CURRENCY_SIGN') . ' ' . $more_detail->total_a_investments;
                    echo '<br/><span>B Investments:</span> ' . env('CURRENCY_SIGN') . ' ' . $more_detail->total_b_investments;
                    echo '<br/><span>C Investments:</span> ' . env('CURRENCY_SIGN') . ' ' . $more_detail->total_c_investments;
                    echo '<br/><span>D Investments:</span> ' . env('CURRENCY_SIGN') . ' ' . $more_detail->total_d_investments;
                    echo '<br/><span>E Investments:</span> ' . env('CURRENCY_SIGN') . ' ' . $more_detail->total_e_investments;
                    echo '<br/><span>F Investments:</span> ' . env('CURRENCY_SIGN') . ' ' . $more_detail->total_f_investments;
                    echo '<br/><span>G Investments:</span> ' . env('CURRENCY_SIGN') . ' ' . $more_detail->total_g_investments;
                    echo '<br/><span>H Investments:</span> ' . env('CURRENCY_SIGN') . ' ' . $more_detail->total_h_investments;
                }
            }
            if (env('LEG_NUMBER') == 9) {
                echo '<br/><span>Total Active A:</span> ' . $more_detail->total_a;
                echo '<br/><span>Total Active B:</span> ' . $more_detail->total_b;
                echo '<br/><span>Total Active C:</span> ' . $more_detail->total_c;
                echo '<br/><span>Total Active D:</span> ' . $more_detail->total_d;
                echo '<br/><span>Total Active E:</span> ' . $more_detail->total_e;
                echo '<br/><span>Total Active F:</span> ' . $more_detail->total_f;
                echo '<br/><span>Total Active G:</span> ' . $more_detail->total_g;
                echo '<br/><span>Total Active H:</span> ' . $more_detail->total_h;
                echo '<br/><span>Total Active I:</span> ' . $more_detail->total_i;
                if (env('PV_BASED_PLAN') == true) {
                    echo '<br/><span>A PV/BV:</span> ' . $more_detail->total_a_bv;
                    echo '<br/><span>B PV/BV:</span> ' . $more_detail->total_b_bv;
                    echo '<br/><span>C PV/BV:</span> ' . $more_detail->total_c_bv;
                    echo '<br/><span>D PV/BV:</span> ' . $more_detail->total_d_bv;
                    echo '<br/><span>E PV/BV:</span> ' . $more_detail->total_e_bv;
                    echo '<br/><span>F PV/BV:</span> ' . $more_detail->total_f_bv;
                    echo '<br/><span>G PV/BV:</span> ' . $more_detail->total_g_bv;
                    echo '<br/><span>H PV/BV:</span> ' . $more_detail->total_h_bv;
                    echo '<br/><span>I PV/BV:</span> ' . $more_detail->total_i_bv;
                }
                if (env('INVESTMENT_PLAN') == true) {
                    echo '<br/><span>A Investments:</span> ' . env('CURRENCY_SIGN') . ' ' . $more_detail->total_a_investments;
                    echo '<br/><span>B Investments:</span> ' . env('CURRENCY_SIGN') . ' ' . $more_detail->total_b_investments;
                    echo '<br/><span>C Investments:</span> ' . env('CURRENCY_SIGN') . ' ' . $more_detail->total_c_investments;
                    echo '<br/><span>D Investments:</span> ' . env('CURRENCY_SIGN') . ' ' . $more_detail->total_d_investments;
                    echo '<br/><span>E Investments:</span> ' . env('CURRENCY_SIGN') . ' ' . $more_detail->total_e_investments;
                    echo '<br/><span>F Investments:</span> ' . env('CURRENCY_SIGN') . ' ' . $more_detail->total_f_investments;
                    echo '<br/><span>G Investments:</span> ' . env('CURRENCY_SIGN') . ' ' . $more_detail->total_g_investments;
                    echo '<br/><span>H Investments:</span> ' . env('CURRENCY_SIGN') . ' ' . $more_detail->total_h_investments;
                    echo '<br/><span>I Investments:</span> ' . env('CURRENCY_SIGN') . ' ' . $more_detail->total_i_investments;
                }
            }
            if (env('LEG_NUMBER') == 10) {
                echo '<br/><span>Total Active A:</span> ' . $more_detail->total_a;
                echo '<br/><span>Total Active B:</span> ' . $more_detail->total_b;
                echo '<br/><span>Total Active C:</span> ' . $more_detail->total_c;
                echo '<br/><span>Total Active D:</span> ' . $more_detail->total_d;
                echo '<br/><span>Total Active E:</span> ' . $more_detail->total_e;
                echo '<br/><span>Total Active F:</span> ' . $more_detail->total_f;
                echo '<br/><span>Total Active G:</span> ' . $more_detail->total_g;
                echo '<br/><span>Total Active H:</span> ' . $more_detail->total_h;
                echo '<br/><span>Total Active I:</span> ' . $more_detail->total_i;
                echo '<br/><span>Total Active J:</span> ' . $more_detail->total_j;
                if (env('PV_BASED_PLAN') == true) {
                    echo '<br/><span>A PV/BV:</span> ' . $more_detail->total_a_bv;
                    echo '<br/><span>B PV/BV:</span> ' . $more_detail->total_b_bv;
                    echo '<br/><span>C PV/BV:</span> ' . $more_detail->total_c_bv;
                    echo '<br/><span>D PV/BV:</span> ' . $more_detail->total_d_bv;
                    echo '<br/><span>E PV/BV:</span> ' . $more_detail->total_e_bv;
                    echo '<br/><span>F PV/BV:</span> ' . $more_detail->total_f_bv;
                    echo '<br/><span>G PV/BV:</span> ' . $more_detail->total_g_bv;
                    echo '<br/><span>H PV/BV:</span> ' . $more_detail->total_h_bv;
                    echo '<br/><span>I PV/BV:</span> ' . $more_detail->total_i_bv;
                    echo '<br/><span>J PV/BV:</span> ' . $more_detail->total_j_bv;
                }
                if (env('INVESTMENT_PLAN') == true) {
                    echo '<br/><span>A Investments:</span> ' . env('CURRENCY_SIGN') . ' ' . $more_detail->total_a_investments;
                    echo '<br/><span>B Investments:</span> ' . env('CURRENCY_SIGN') . ' ' . $more_detail->total_b_investments;
                    echo '<br/><span>C Investments:</span> ' . env('CURRENCY_SIGN') . ' ' . $more_detail->total_c_investments;
                    echo '<br/><span>D Investments:</span> ' . env('CURRENCY_SIGN') . ' ' . $more_detail->total_d_investments;
                    echo '<br/><span>E Investments:</span> ' . env('CURRENCY_SIGN') . ' ' . $more_detail->total_e_investments;
                    echo '<br/><span>F Investments:</span> ' . env('CURRENCY_SIGN') . ' ' . $more_detail->total_f_investments;
                    echo '<br/><span>G Investments:</span> ' . env('CURRENCY_SIGN') . ' ' . $more_detail->total_g_investments;
                    echo '<br/><span>H Investments:</span> ' . env('CURRENCY_SIGN') . ' ' . $more_detail->total_h_investments;
                    echo '<br/><span>I Investments:</span> ' . env('CURRENCY_SIGN') . ' ' . $more_detail->total_i_investments;
                    echo '<br/><span>J Investments:</span> ' . env('CURRENCY_SIGN') . ' ' . $more_detail->total_j_investments;
                }
            }

            echo '" data-trigger="hover" data-html="true" > <img class="rounded-circle" src="' . $image . '" alt="' . $detail->name . '">';
            echo '<br/><strong> ' . $detail->name . '</strong><br/><span class="text-sm-center"> ' . env('ID_EXT') . $detail->id . '</span ></a>';
            return $detail;
        }
        echo '<a target="_blank" href="' . url('/register/' . $id . '/' . $leg) . '" data-toggle="tooltip" title="Click to register below: ' . env('ID_EXT') . $id . '">';
        echo '<img src="' . asset('material/img/new_user.png') . '" alt="New User" > ';
        echo '</a>';
        return false;
    }
}

if (!function_exists('cache_select')) {
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
}
if (!function_exists('recursive_member_pool_find_a')) {
    function recursive_member_pool_find_a($id)
    {
        $result = Member::select('id', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J')->where('id', $id)
                        ->where(function ($query) {
                            $query->where('A', '0');
                        })
                        ->orderBy('serial', 'ASC')
                        ->first();
        if (empty($result)) {
            $result = Member::select('id', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J')->where('position', $id)
                            ->where(function ($query) {
                                $query->where('A', '0');
                            })
                            ->orderBy('serial', 'ASC')
                            ->first();
        }
        if ($result->A === 0) {
            return [
                'position' => $result->id,
                'leg'      => 'A',
            ];
        } else {
            $data = Member::select('id')->where('position', $id)->orderBy('serial', 'ASC')->get();
            foreach ($data as $result):
                if ($result->id !== $id):
                    return recursive_member_pool_find_a($result->id);
                endif;
            endforeach;
        }
    }

}
if (!function_exists('recursive_member_pool_find_b')) {
    function recursive_member_pool_find_b($id)
    {
        $result = Member::select('id', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J')->where('id', $id)
                        ->where(function ($query) {
                            $query->where('A', '0')
                                  ->orWhere('B', '0');
                        })
                        ->orderBy('serial', 'ASC')
                        ->first();
        if (empty($result)) {
            $result = Member::select('id', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J')->where('position', $id)
                            ->where(function ($query) {
                                $query->where('A', '0')
                                      ->orWhere('B', '0');
                            })
                            ->orderBy('serial', 'ASC')
                            ->first();
        }
        if ($result->A === 0) {
            return [
                'position' => $result->id,
                'leg'      => 'A',
            ];
        } else if ($result->B === 0) {
            return [
                'position' => $result->id,
                'leg'      => 'B',
            ];
        } else {
            $data = Member::select('id')->where('position', $id)->orderBy('serial', 'ASC')->get();
            foreach ($data as $result):
                if ($result->id !== $id):
                    return recursive_member_pool_find_b($result->id);
                endif;
            endforeach;
        }
    }

}
if (!function_exists('recursive_member_pool_find_c')) {
    function recursive_member_pool_find_c($id)
    {
        $result = Member::select('id', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J')->where('id', $id)
                        ->where(function ($query) {
                            $query->where('A', '0')
                                  ->orWhere('B', '0')
                                  ->orWhere('C', '0');
                        })
                        ->orderBy('serial', 'ASC')
                        ->first();
        if (empty($result)) {
            $result = Member::select('id', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J')->where('position', $id)
                            ->where(function ($query) {
                                $query->where('A', '0')
                                      ->orWhere('B', '0')
                                      ->orWhere('C', '0');
                            })
                            ->orderBy('serial', 'ASC')
                            ->first();
        }
        if ($result->A === 0) {
            return [
                'position' => $result->id,
                'leg'      => 'A',
            ];
        } else if ($result->B === 0) {
            return [
                'position' => $result->id,
                'leg'      => 'B',
            ];
        } else if ($result->C === 0) {
            return [
                'position' => $result->id,
                'leg'      => 'C',
            ];
        } else {
            $data = Member::select('id')->where('position', $id)->orderBy('serial', 'ASC')->get();
            foreach ($data as $result):
                if ($result->id !== $id):
                    return recursive_member_pool_find_c($result->id);
                endif;
            endforeach;
        }
    }

}
if (!function_exists('recursive_member_pool_find_d')) {
    function recursive_member_pool_find_d($id)
    {
        $result = Member::select('id', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J')->where('id', $id)
                        ->where(function ($query) {
                            $query->where('A', '0')
                                  ->orWhere('B', '0')
                                  ->orWhere('C', '0')
                                  ->orWhere('D', '0');
                        })
                        ->orderBy('serial', 'ASC')
                        ->first();
        if (empty($result)) {
            $result = Member::select('id', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J')->where('position', $id)
                            ->where(function ($query) {
                                $query->where('A', '0')
                                      ->orWhere('B', '0')
                                      ->orWhere('C', '0')
                                      ->orWhere('D', '0');
                            })
                            ->orderBy('serial', 'ASC')
                            ->first();
        }
        if ($result->A == 0) {
            return [
                'position' => $result->id,
                'leg'      => 'A',
            ];
        } else if ($result->B === 0) {
            return [
                'position' => $result->id,
                'leg'      => 'B',
            ];
        } else if ($result->C === 0) {
            return [
                'position' => $result->id,
                'leg'      => 'C',
            ];
        } else if ($result->D === 0) {
            return [
                'position' => $result->id,
                'leg'      => 'D',
            ];
        } else {
            $data = Member::select('id')->where('position', $id)->orderBy('serial', 'ASC')->get();
            foreach ($data as $result):
                if ($result->id !== $id):
                    return recursive_member_pool_find_d($result->id);
                endif;
            endforeach;
        }
    }

}
if (!function_exists('recursive_member_pool_find_e')) {
    function recursive_member_pool_find_e($id)
    {
        $result = Member::select('id', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J')->where('id', $id)
                        ->where(function ($query) {
                            $query->where('A', '0')
                                  ->orWhere('B', '0')
                                  ->orWhere('C', '0')
                                  ->orWhere('D', '0')
                                  ->orWhere('E', '0');
                        })
                        ->orderBy('serial', 'ASC')
                        ->first();
        if (empty($result)) {
            $result = Member::select('id', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J')->where('position', $id)
                            ->where(function ($query) {
                                $query->where('A', '0')
                                      ->orWhere('B', '0')
                                      ->orWhere('C', '0')
                                      ->orWhere('D', '0')
                                      ->orWhere('E', '0');
                            })
                            ->orderBy('serial', 'ASC')
                            ->first();
        }
        if ($result->A === 0) {
            return [
                'position' => $result->id,
                'leg'      => 'A',
            ];
        } else if ($result->B === 0) {
            return [
                'position' => $result->id,
                'leg'      => 'B',
            ];
        } else if ($result->C === 0) {
            return [
                'position' => $result->id,
                'leg'      => 'C',
            ];
        } else if ($result->D === 0) {
            return [
                'position' => $result->id,
                'leg'      => 'D',
            ];
        } else if ($result->E === 0) {
            return [
                'position' => $result->id,
                'leg'      => 'E',
            ];
        } else {
            $data = Member::select('id')->where('position', $id)->orderBy('serial', 'ASC')->get();
            foreach ($data as $result):
                if ($result->id !== $id):
                    return recursive_member_pool_find_e($result->id);
                endif;
            endforeach;
        }
    }

}
if (!function_exists('recursive_member_pool_find_f')) {
    function recursive_member_pool_find_f($id)
    {
        $result = Member::select('id', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J')->where('id', $id)
                        ->where(function ($query) {
                            $query->where('A', '0')
                                  ->orWhere('B', '0')
                                  ->orWhere('C', '0')
                                  ->orWhere('D', '0')
                                  ->orWhere('E', '0')
                                  ->orWhere('F', '0');
                        })
                        ->orderBy('serial', 'ASC')
                        ->first();
        if (empty($result)) {
            $result = Member::select('id', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J')->where('position', $id)
                            ->where(function ($query) {
                                $query->where('A', '0')
                                      ->orWhere('B', '0')
                                      ->orWhere('C', '0')
                                      ->orWhere('D', '0')
                                      ->orWhere('E', '0')
                                      ->orWhere('F', '0');
                            })
                            ->orderBy('serial', 'ASC')
                            ->first();
        }
        if ($result->A === 0) {
            return [
                'position' => $result->id,
                'leg'      => 'A',
            ];
        } else if ($result->B === 0) {
            return [
                'position' => $result->id,
                'leg'      => 'B',
            ];
        } else if ($result->C === 0) {
            return [
                'position' => $result->id,
                'leg'      => 'C',
            ];
        } else if ($result->D === 0) {
            return [
                'position' => $result->id,
                'leg'      => 'D',
            ];
        } else if ($result->E === 0) {
            return [
                'position' => $result->id,
                'leg'      => 'E',
            ];
        } else if ($result->F === 0) {
            return [
                'position' => $result->id,
                'leg'      => 'F',
            ];
        } else {
            $data = Member::select('id')->where('position', $id)->orderBy('serial', 'ASC')->get();
            foreach ($data as $result):
                if ($result->id !== $id):
                    return recursive_member_pool_find_f($result->id);
                endif;
            endforeach;
        }
    }

}
if (!function_exists('recursive_member_pool_find_g')) {
    function recursive_member_pool_find_g($id)
    {
        $result = Member::select('id', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J')->where('id', $id)
                        ->where(function ($query) {
                            $query->where('A', '0')
                                  ->orWhere('B', '0')
                                  ->orWhere('C', '0')
                                  ->orWhere('D', '0')
                                  ->orWhere('E', '0')
                                  ->orWhere('F', '0')
                                  ->orWhere('G', '0');
                        })
                        ->orderBy('serial', 'ASC')
                        ->first();
        if (empty($result)) {
            $result = Member::select('id', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J')->where('position', $id)
                            ->where(function ($query) {
                                $query->where('A', '0')
                                      ->orWhere('B', '0')
                                      ->orWhere('C', '0')
                                      ->orWhere('D', '0')
                                      ->orWhere('E', '0')
                                      ->orWhere('F', '0')
                                      ->orWhere('G', '0');
                            })
                            ->orderBy('serial', 'ASC')
                            ->first();
        }
        if ($result->A === 0) {
            return [
                'position' => $result->id,
                'leg'      => 'A',
            ];
        } else if ($result->B === 0) {
            return [
                'position' => $result->id,
                'leg'      => 'B',
            ];
        } else if ($result->C === 0) {
            return [
                'position' => $result->id,
                'leg'      => 'C',
            ];
        } else if ($result->D === 0) {
            return [
                'position' => $result->id,
                'leg'      => 'D',
            ];
        } else if ($result->E === 0) {
            return [
                'position' => $result->id,
                'leg'      => 'E',
            ];
        } else if ($result->F === 0) {
            return [
                'position' => $result->id,
                'leg'      => 'F',
            ];
        } else if ($result->G === 0) {
            return [
                'position' => $result->id,
                'leg'      => 'G',
            ];
        } else {
            $data = Member::select('id')->where('position', $id)->orderBy('serial', 'ASC')->get();
            foreach ($data as $result):
                if ($result->id !== $id):
                    return recursive_member_pool_find_g($result->id);
                endif;
            endforeach;
        }
    }

}
if (!function_exists('recursive_member_pool_find_h')) {
    function recursive_member_pool_find_h($id)
    {
        $result = Member::select('id', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J')->where('id', $id)
                        ->where(function ($query) {
                            $query->where('A', '0')
                                  ->orWhere('B', '0')
                                  ->orWhere('C', '0')
                                  ->orWhere('D', '0')
                                  ->orWhere('E', '0')
                                  ->orWhere('F', '0')
                                  ->orWhere('G', '0')
                                  ->orWhere('H', '0');
                        })
                        ->orderBy('serial', 'ASC')
                        ->first();
        if (empty($result)) {
            $result = Member::select('id', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J')->where('position', $id)
                            ->where(function ($query) {
                                $query->where('A', '0')
                                      ->orWhere('B', '0')
                                      ->orWhere('C', '0')
                                      ->orWhere('D', '0')
                                      ->orWhere('E', '0')
                                      ->orWhere('F', '0')
                                      ->orWhere('G', '0')
                                      ->orWhere('H', '0');
                            })
                            ->orderBy('serial', 'ASC')
                            ->first();
        }
        if ($result->A === 0) {
            return [
                'position' => $result->id,
                'leg'      => 'A',
            ];
        } else if ($result->B === 0) {
            return [
                'position' => $result->id,
                'leg'      => 'B',
            ];
        } else if ($result->C === 0) {
            return [
                'position' => $result->id,
                'leg'      => 'C',
            ];
        } else if ($result->D === 0) {
            return [
                'position' => $result->id,
                'leg'      => 'D',
            ];
        } else if ($result->E === 0) {
            return [
                'position' => $result->id,
                'leg'      => 'E',
            ];
        } else if ($result->F === 0) {
            return [
                'position' => $result->id,
                'leg'      => 'F',
            ];
        } else if ($result->G === 0) {
            return [
                'position' => $result->id,
                'leg'      => 'G',
            ];
        } else if ($result->H === 0) {
            return [
                'position' => $result->id,
                'leg'      => 'H',
            ];
        } else {
            $data = Member::select('id')->where('position', $id)->orderBy('serial', 'ASC')->get();
            foreach ($data as $result):
                if ($result->id !== $id):
                    return recursive_member_pool_find_h($result->id);
                endif;
            endforeach;
        }
    }

}
if (!function_exists('recursive_member_pool_find_i')) {
    function recursive_member_pool_find_i($id)
    {
        $result = Member::select('id', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J')->where('id', $id)
                        ->where(function ($query) {
                            $query->where('A', '0')
                                  ->orWhere('B', '0')
                                  ->orWhere('C', '0')
                                  ->orWhere('D', '0')
                                  ->orWhere('E', '0')
                                  ->orWhere('F', '0')
                                  ->orWhere('G', '0')
                                  ->orWhere('H', '0')
                                  ->orWhere('I', '0');
                        })
                        ->orderBy('serial', 'ASC')
                        ->first();
        if (empty($result)) {
            $result = Member::select('id', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J')->where('position', $id)
                            ->where(function ($query) {
                                $query->where('A', '0')
                                      ->orWhere('B', '0')
                                      ->orWhere('C', '0')
                                      ->orWhere('D', '0')
                                      ->orWhere('E', '0')
                                      ->orWhere('F', '0')
                                      ->orWhere('G', '0')
                                      ->orWhere('H', '0')
                                      ->orWhere('I', '0');
                            })
                            ->orderBy('serial', 'ASC')
                            ->first();
        }
        if ($result->A === 0) {
            return [
                'position' => $result->id,
                'leg'      => 'A',
            ];
        } else if ($result->B === 0) {
            return [
                'position' => $result->id,
                'leg'      => 'B',
            ];
        } else if ($result->C === 0) {
            return [
                'position' => $result->id,
                'leg'      => 'C',
            ];
        } else if ($result->D === 0) {
            return [
                'position' => $result->id,
                'leg'      => 'D',
            ];
        } else if ($result->E === 0) {
            return [
                'position' => $result->id,
                'leg'      => 'E',
            ];
        } else if ($result->F === 0) {
            return [
                'position' => $result->id,
                'leg'      => 'F',
            ];
        } else if ($result->G === 0) {
            return [
                'position' => $result->id,
                'leg'      => 'G',
            ];
        } else if ($result->H === 0) {
            return [
                'position' => $result->id,
                'leg'      => 'H',
            ];
        } else if ($result->I === 0) {
            return [
                'position' => $result->id,
                'leg'      => 'I',
            ];
        } else {
            $data = Member::select('id')->where('position', $id)->orderBy('serial', 'ASC')->get();
            foreach ($data as $result):
                if ($result->id !== $id):
                    return recursive_member_pool_find_i($result->id);
                endif;
            endforeach;
        }
    }

}if (!function_exists('recursive_member_pool_find_j')) {
    function recursive_member_pool_find_j($id)
    {
        $result = Member::select('id', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J')->where('id', $id)
                        ->where(function ($query) {
                            $query->where('A', '0')
                                  ->orWhere('B', '0')
                                  ->orWhere('C', '0')
                                  ->orWhere('D', '0')
                                  ->orWhere('E', '0')
                                  ->orWhere('F', '0')
                                  ->orWhere('G', '0')
                                  ->orWhere('H', '0')
                                  ->orWhere('I', '0')
                                  ->orWhere('J', '0');
                        })
                        ->orderBy('serial', 'ASC')
                        ->first();
        if (empty($result)) {
            $result = Member::select('id', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J')->where('position', $id)
                            ->where(function ($query) {
                                $query->where('A', '0')
                                      ->orWhere('B', '0')
                                      ->orWhere('C', '0')
                                      ->orWhere('D', '0')
                                      ->orWhere('E', '0')
                                      ->orWhere('F', '0')
                                      ->orWhere('G', '0')
                                      ->orWhere('H', '0')
                                      ->orWhere('I', '0')
                                      ->orWhere('J', '0');
                            })
                            ->orderBy('serial', 'ASC')
                            ->first();
        }
        if ($result->A == 0) {
            return [
                'position' => $result->id,
                'leg'      => 'A',
            ];
        } else if ($result->B == 0) {
            return [
                'position' => $result->id,
                'leg'      => 'B',
            ];
        } else if ($result->C == 0) {
            return [
                'position' => $result->id,
                'leg'      => 'C',
            ];
        } else if ($result->D == 0) {
            return [
                'position' => $result->id,
                'leg'      => 'D',
            ];
        } else if ($result->E == 0) {
            return [
                'position' => $result->id,
                'leg'      => 'E',
            ];
        } else if ($result->F == 0) {
            return [
                'position' => $result->id,
                'leg'      => 'F',
            ];
        } else if ($result->G == 0) {
            return [
                'position' => $result->id,
                'leg'      => 'G',
            ];
        } else if ($result->H == 0) {
            return [
                'position' => $result->id,
                'leg'      => 'H',
            ];
        } else if ($result->I == 0) {
            return [
                'position' => $result->id,
                'leg'      => 'I',
            ];
        } else if ($result->J == 0) {
            return [
                'position' => $result->id,
                'leg'      => 'J',
            ];
        } else {
            $data = Member::select('id')->where('position', $id)->orderBy('serial', 'ASC')->get();
            foreach ($data as $result):
                if ($result->id !== $id):
                    return recursive_member_pool_find_j($result->id);
                endif;
            endforeach;
        }
    }

    if (!function_exists('norecord')) {
        function norecord($msg = 'No Record available to display')
        {
            $str = '<div class="text-center"><img class="img-fluid" style="max-height: 100px; width:auto" src="' . asset('material/img/error.png') . '"><h3 class="mt-1" style="font-weight: 500 !important; font-size: 22px !important;">' . $msg . '</h4></div>';
            return trim($str);
        }
    }

}
?>