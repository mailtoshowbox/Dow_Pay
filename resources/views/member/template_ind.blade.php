<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>{{$title}}</title>
    <link rel="shortcut icon" href="{{asset('images/favicon.png')}}">
    <link rel="stylesheet" href="//fonts.googleapis.com/css?family=Poppins">
    <link href="{{asset('css/member.css')}}" rel="stylesheet" type="text/css">
    <link rel="stylesheet"
          href="//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/jquery-nice-select/1.1.0/css/nice-select.min.css">
    <!--[if lt IE 9]>
    <script src="//oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="//oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.css">
    <script type="text/javascript"
            src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
    <script type="text/javascript">
        function googleTranslateElementInit() {
            new google.translate.TranslateElement({pageLanguage: 'en'}, 'google_translate_element');
        }
    </script>
    @section('header')
    @show
</head>
<body data-gr-c-s-loaded="true" cz-shortcut-listen="true" class="sidebar-dark">
<div class="container-scroller">
    <!-- partial:partials/_navbar.html -->
    <nav class="navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
        <div class="text-left navbar-brand-wrapper d-flex align-items-center justify-content-between">
            <a class="navbar-brand brand-logo" href="{{url('/member-dash')}}"><img src="{{asset('images/logo.png')}}"
                                                                                   alt="logo"></a>
            <button class="navbar-toggler align-self-center" type="button" data-toggle="minimize">
                <span class="mdi mdi-menu"></span>
            </button>
        </div>
        <div class="navbar-menu-wrapper d-flex align-items-center justify-content-end">
            <a href="{{url('/member-dash')}}" class="d-lg-none d-block"><img class="img-fluid"
                                                                             style="max-width: 100px"
                                                                             src="{{asset('images/logo.png')}}"
                                                                             alt="logo"></a>
            <ul class="navbar-nav navbar-nav-right">
                <li class="nav-item nav-search d-none d-lg-flex">
                    <div class="input-group">
                        <div class="input-group-prepend">
                  <span class="input-group-text" id="search">
                  <i class="fa fa-search"></i>
                  </span>
                        </div>
                        <form class="search-box float-right" method="post" action="{{url('/my-tree')}}">
                            @csrf
                            <input type="text" name="user" class="form-control" placeholder="Type to search user..."
                                   aria-label="search"
                                   aria-describedby="search">
                        </form>
                    </div>
                </li>
                <li class="nav-item nav-user-icon">
                     
                </li>
            </ul>
            <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button"
                    data-toggle="offcanvas">
                <i class="mdi fa fa-bars"></i>
            </button>
        </div>
    </nav>
    <div class="container-fluid page-body-wrapper">
        <nav class="sidebar sidebar-offcanvas" id="sidebar">
            <ul class="nav">
                <li class="nav-item nav-profile">
                    <div class="nav-link d-flex">
                        <div class="profile-image">
                            
                        </div>
                        <div class="profile-name">
                            <p class="name">
                                {{session('name')}}
                            </p>
                            <p class="designation">
                                {{env('ID_EXT').session('member_id')}}
                            </p>
                        </div>
                    </div>
                </li>
                <li class="nav-item active">
                    <a class="nav-link" href="{{url('/member-dash')}}">
                        <i class="fa fa-home menu-icon"></i>
                        <span class="menu-title">Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{url('/my-welcome-letter')}}">
                        <i class="fa fa-file menu-icon"></i>
                        <span class="menu-title">Welcome Letter</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" target="_blank" href="{{url('/my-id-card/'.session('member_id'))}}">
                        <i class="fa fa-id-card menu-icon"></i>
                        <span class="menu-title">ID Card</span>
                    </a>
                </li>
                @if(env('ENABLE_PRODUCT')==true)
                    <li class="nav-item">
                        <a class="nav-link" href="{{url('/old-purchases')}}">
                            <i class="fa fa-file-o menu-icon"></i>
                            <span class="menu-title">Invoices / Orders</span>
                        </a>
                    </li>
                @endif
                @if(env('ENABLE_COUPON')==true)
                    <li class="nav-item">
                        <a class="nav-link" href="{{url('/my-coupons')}}">
                            <i class="fa fa-id-card menu-icon"></i>
                            <span class="menu-title">My Coupons</span>
                        </a>
                    </li>
                @endif
                <li class="nav-item">
                    <a class="nav-link" data-toggle="collapse" href="#ui-basic" aria-expanded="false"
                       aria-controls="ui-basic">
                        <i class="fa fa-envelope menu-icon"></i>
                        <span class="menu-title">Messages</span>
                        <i class="fa fa-angle-right menu-arrow"></i>
                    </a>
                    <div class="collapse" id="ui-basic">
                        <ul class="nav flex-column sub-menu">
                            <li class="nav-item"><a class="nav-link"
                                                    href="{{url('/compose')}}">New Message</a></li>
                            <li class="nav-item"><a class="nav-link"
                                                    href="{{url('/my-inbox')}}">Inbox</a></li>
                            <li class="nav-item"><a class="nav-link"
                                                    href="{{url('/sent-inbox')}}">Sent Items</a></li>
                            <li class="nav-item"><a class="nav-link"
                                                    href="{{url('/src-msg')}}">Search</a></li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="collapse" href="#ui-basic1" aria-expanded="false"
                       aria-controls="ui-basic1">
                        <i class="fa fa-sitemap menu-icon"></i>
                        <span class="menu-title">Tree & Downline</span>
                        <i class="fa fa-angle-right menu-arrow"></i>
                    </a>
                    <div class="collapse" id="ui-basic1">
                        <ul class="nav flex-column sub-menu">
                            @if(env('ENABLE_BOARD_PLAN')==false)
                                <li class="nav-item"><a class="nav-link"
                                                        href="{{url('/my-tree')}}">My Tree</a></li>
                                <li class="nav-item"><a class="nav-link"
                                                        href="{{url('/my-team-report')}}">My Genealogy Report</a></li>
                                @if(env('LEG_NUMBER') == 1 && env('AUTOPOOL')==false)
                                    <li class="nav-item"><a class="nav-link"
                                                            href="{{url('/my-level-counting')}}">Level Counting</a>
                                    </li>
                                @endif
                                <li class="nav-item"><a class="nav-link"
                                                        href="{{url('/my-downline-list')}}">All Downline List</a></li>
                                <li class="nav-item"><a class="nav-link"
                                                        href="{{url('/my-downline-list/Active')}}">Active Downlines</a>
                                </li>
                                <li class="nav-item"><a class="nav-link"
                                                        href="{{url('/my-downline-list/Inactive')}}">Inactive
                                                                                                     Downlines</a></li>
                                <li class="nav-item"><a class="nav-link"
                                                        href="{{url('/register/'.session('member_id').'/A')}}">New
                                                                                                               Registration</a>
                                </li>
                            @else
                                <li class="nav-item"><a class="nav-link"
                                                        href="{{url('/my-board-tree')}}">Board Tree</a></li>
                            @endif
                            <li class="nav-item"><a class="nav-link"
                                                    href="{{url('/my-referred-list')}}">Direct Referrals</a></li>
                        </ul>
                    </div>
                </li>
                @if(env('INVESTMENT_PLAN')==true)
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="collapse" href="#ui-basic2" aria-expanded="false"
                           aria-controls="ui-basic2">
                            <i class="fa fa-money menu-icon"></i>
                            <span class="menu-title">Investments</span>
                            <i class="fa fa-angle-right menu-arrow"></i>
                        </a>
                        <div class="collapse" id="ui-basic2">
                            <ul class="nav flex-column sub-menu">
                                <li class="nav-item"><a class="nav-link"
                                                        href="{{url('/new-investments')}}">New Investment</a></li>
                                <li class="nav-item"><a class="nav-link"
                                                        href="{{url('/my-investments')}}">All Investments</a></li>
                                <li class="nav-item"><a class="nav-link"
                                                        href="{{url('/investment-graph')}}">Graphical Report</a></li>
                            </ul>
                        </div>
                    </li>
                @endif
                @if(env('ENABLE_DONATION_PLAN')==true)
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="collapse" href="#ui-basic3" aria-expanded="false"
                           aria-controls="ui-basic3">
                            <i class="fa fa-gift menu-icon"></i>
                            <span class="menu-title">Donations</span>
                            <i class="fa fa-angle-right menu-arrow"></i>
                        </a>
                        <div class="collapse" id="ui-basic3">
                            <ul class="nav flex-column sub-menu">
                                @if(env('DONATION_PLAN_TYPE')==='PH')
                                    <li class="nav-item"><a class="nav-link"
                                                            href="{{url('/my-commitments')}}">My Commitments</a></li>
                                    <li class="nav-item"><a class="nav-link"
                                                            href="{{url('/my-gh-requests')}}">My GH Requests</a></li>
                                @endif
                                <li class="nav-item"><a class="nav-link"
                                                        href="{{url('/my-send-history')}}">My Sent History</a></li>
                                <li class="nav-item"><a class="nav-link"
                                                        href="{{url('/my-received-history')}}">My Received History</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                @endif
                @if(env('ENABLE_EPIN')==true)
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="collapse" href="#ui-basic4" aria-expanded="false"
                           aria-controls="ui-basic4">
                            <i class="fa fa-code menu-icon"></i>
                            <span class="menu-title">E-PINs</span>
                            <i class="fa fa-angle-right menu-arrow"></i>
                        </a>
                        <div class="collapse" id="ui-basic4">
                            <ul class="nav flex-column sub-menu">
                                <li class="nav-item"><a class="nav-link"
                                                        href="{{url('/my-epins/Un-Used')}}">Un-used Epins</a></li>
                                <li class="nav-item"><a class="nav-link"
                                                        href="{{url('/my-epins/Used')}}">Used E-Pins</a>
                                </li>
                                <li class="nav-item"><a class="nav-link"
                                                        href="{{url('/transfer-epin')}}">Transfer E-Pins</a>
                                </li>
                                <li class="nav-item"><a class="nav-link"
                                                        href="{{url('/epin-transfer-history')}}">Epin Transfer
                                                                                                 History</a>
                                </li>
                                @if(env('ALLOW_EPIN_GENERATE')===true)
                                    <li class="nav-item"><a class="nav-link"
                                                            href="{{url('/generate-epin')}}">Generate E-Pin</a>
                                    </li>
                                    <li class="nav-item"><a class="nav-link"
                                                            href="{{url('/epin-generate-history')}}">Generate
                                                                                                     History</a>
                                    </li>
                                @endif
                                <li class="nav-item"><a class="nav-link"
                                                        href="{{url('/request-epin')}}">Request Epin</a>
                                <li class="nav-item"><a class="nav-link"
                                                        href="{{url('/my-epin-requests')}}">Epin Requests</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                @endif
                @if(env('ENABLE_LMS')==true)
                    <li class="nav-item">
                        <a class="nav-link" href="{{url('/my-courses')}}">
                            <i class="fa fa-book menu-icon"></i>
                            <span class="menu-title">My Courses</span>
                        </a>
                    </li>
                @endif
                @if(env('ENABLE_PRODUCT')==true && env('ENABLE_MEMBER_REPURCHASE')==true && env('ENABLE_REPURCHASE')==true)
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="collapse" href="#ui-basic5" aria-expanded="false"
                           aria-controls="ui-basic5">
                            <i class="fa fa-shopping-cart menu-icon"></i>
                            <span class="menu-title">Products</span>
                            <i class="fa fa-angle-right menu-arrow"></i>
                        </a>
                        <div class="collapse" id="ui-basic5">
                            <ul class="nav flex-column sub-menu">
                                <li class="nav-item"><a class="nav-link"
                                                        href="{{url('/show-products')}}">Buy Product</a></li>
                                <li class="nav-item"><a class="nav-link"
                                                        href="{{url('/cart')}}">My Cart</a></li>
                                <li class="nav-item"><a class="nav-link"
                                                        href="{{url('/old-purchases')}}">Past Orders</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                @endif
                @if(env('ENABLE_RECHARGE')==true)
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="collapse" href="#ui-basic6" aria-expanded="false"
                           aria-controls="ui-basic6">
                            <i class="fa fa-mobile-phone menu-icon"></i>
                            <span class="menu-title">Recharge & Bills</span>
                            <i class="fa fa-angle-right menu-arrow"></i>
                        </a>
                        <div class="collapse" id="ui-basic6">
                            <ul class="nav flex-column sub-menu">
                                <li class="nav-item"><a class="nav-link"
                                                        href="{{url('/new-recharge')}}">New Recharge</a></li>
                                <li class="nav-item"><a class="nav-link"
                                                        href="{{url('/new-recharge')}}">New Bill Payment</a></li>
                                <li class="nav-item"><a class="nav-link"
                                                        href="{{url('/my-recharge-history')}}">Recharge History</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                @endif
                @if(env('ENABLE_ADVT_PLAN')==true)
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="collapse" href="#ui-basic6" aria-expanded="false"
                           aria-controls="ui-basic6">
                            <i class="fa fa-bullhorn menu-icon"></i>
                            <span class="menu-title">Advertisements</span>
                            <i class="fa fa-angle-right menu-arrow"></i>
                        </a>
                        <div class="collapse" id="ui-basic6">
                            <ul class="nav flex-column sub-menu">
                                <li class="nav-item"><a class="nav-link"
                                                        href="{{url('/my-ads')}}">New Advertisements</a></li>
                                <li class="nav-item"><a class="nav-link"
                                                        href="{{url('/my-old-ads')}}">Old Advertisements</a></li>
                            </ul>
                        </div>
                    </li>
                @endif
                @if(env('ENABLE_UPGRADE_PLAN')==true)
                    <li class="nav-item">
                        <a class="nav-link" href="{{url('/my-plans')}}">
                            <i class="fa fa-users menu-icon"></i>
                            <span class="menu-title">My Non-working Plans</span>
                        </a>
                    </li>
                @endif
                @if(env('ENABLE_EARNING')==true)
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="collapse" href="#ui-basic7" aria-expanded="false"
                           aria-controls="ui-basic7">
                            <i class="fa fa-google-wallet menu-icon"></i>
                            <span class="menu-title">Earnings & Wallet</span>
                            <i class="fa fa-angle-right menu-arrow"></i>
                        </a>
                        <div class="collapse" id="ui-basic7">
                            <ul class="nav flex-column sub-menu">
                                <li class="nav-item"><a class="nav-link"
                                                        href="{{url('/my-earnings')}}">All Earnings</a></li>
                                <li class="nav-item"><a class="nav-link"
                                                        href="{{url('/my-wallet')}}">My Wallets</a></li>
                                @foreach(config('config.income_names') as $key => $val)
                                    <li class="nav-item"><a class="nav-link"
                                                            href="{{url('/list-earnings/'.$key)}}">{{$val}}</a></li>
                                @endforeach
                                @if(env('ENABLE_DONATION_PLAN')==false)
                                    @if(env('ALLOW_FUND_TRANSFER')===true)
                                        <li class="nav-item"><a class="nav-link"
                                                                href="{{url('/transfer-fund-form')}}">Transfer Funds</a>
                                        </li>
                                        <li class="nav-item"><a class="nav-link"
                                                                href="{{url('/transfer-fund-history')}}">Fund Transfer
                                                                                                         History</a>
                                        <li class="nav-item"><a class="nav-link"
                                                                href="{{url('/fund-received-history')}}">Fund Received
                                                                                                         History</a>
                                        </li>
                                    @endif
                                    @if (env('ALLOW_WITHDRAW') == true)
                                        <li class="nav-item"><a class="nav-link"
                                                                href="{{url('/withdraw-fund')}}">Withdraw Fund</a></li>
                                    @endif
                                    <li class="nav-item"><a class="nav-link"
                                                            href="{{url('/withdraw-history')}}">Withdrawal History</a>
                                    <li class="nav-item"><a class="nav-link"
                                                            href="{{url('/deduction-report')}}">TAX/Deduction
                                                                                                History</a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </li>
                @endif
                @if(env('ENABLE_REWARDS')==true)
                    <li class="nav-item">
                        <a class="nav-link" href="{{url('/my-rewards')}}">
                            <i class="fa fa-heart-o menu-icon"></i>
                            <span class="menu-title">My Rewards</span>
                        </a>
                    </li>
                @endif
                <li class="nav-item">
                    <a class="nav-link" data-toggle="collapse" href="#ui-basic8" aria-expanded="false"
                       aria-controls="ui-basic8">
                        <i class="fa fa-lock menu-icon"></i>
                        <span class="menu-title">Security</span>
                        <i class="fa fa-angle-right menu-arrow"></i>
                    </a>
                    <div class="collapse" id="ui-basic8">
                        <ul class="nav flex-column sub-menu">
                            <li class="nav-item"><a class="nav-link"
                                                    href="{{url('/profile')}}">Profile</a></li>
                            <li class="nav-item"><a class="nav-link"
                                                    href="{{url('/password')}}">Change Password</a></li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{url('/member-logout')}}">
                        <i class="fa fa-power-off menu-icon"></i>
                        <span class="menu-title">Logout</span>
                    </a>
                </li>
            </ul>
        </nav>
        <div class="main-panel">
            <div class="content-wrapper">
                <div class="row">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-sm-6 mb-4 mb-xl-0">
                                <h3>{{$heading}}</h3>
                                <h6 class="font-weight-normal mb-0 text-muted">{{$subtitle}}</h6>
                            </div>
                            <div class="col-sm-6">
                                <div class="d-flex align-items-center justify-content-md-end">
                                    <div class="border-right-dark pr-4 mb-3 mb-xl-0 d-xl-block d-none">
                                        <p class="text-muted">Today</p>
                                        <h6 class="font-weight-medium text-dark mb-0">{{date('d M Y')}}</h6>
                                    </div>
                                    <div class="pr-4 pl-4 mb-3 mb-xl-0 d-xl-block d-none">
                                        <p class="text-muted">Rank</p>
                                        <h6 class="font-weight-medium text-dark mb-0">
                                            <?php
                                            $rank = "RICK";;
                                            echo $rank ?: 'Member'
                                            ?>
                                        </h6>
                                    </div>
                                    <div class="pr-1 mb-3 mb-xl-0">
                                        <button onclick="printDiv()" type="button"
                                                class="btn btn-info btn-icon mr-2"><i
                                                class="fa fa-print" style="font-size:15px !important;"></i></button>
                                    </div>
                                    @if(env('ENABLE_REPURCHASE')==true && env('ENABLE_MEMBER_REPURCHASE')==true)
                                        <div class="pr-1 mb-3 mb-xl-0">
                                            <button onclick="document.location.href='{{url('/cart')}}'" type="button"
                                                    class="btn btn-danger btn-icon mr-2"><i
                                                    class="fa fa-shopping-cart text-white"
                                                    style="font-size:15px !important;"></i></button>
                                        </div>
                                    @endif
                                    <div class="pr-1 mb-3 mb-xl-0">
                                        <button onclick="document.location.href='{{url()->current()}}'" type="button"
                                                class="btn btn-success btn-icon mr-2"><i
                                                class="fa fa-refresh" style="font-size:15px !important;"></i></button>
                                    </div>
                                    <div class="mb-3 mb-xl-0 d-md-none d-block">
                                        <button type="button" class="btn btn-success">Rank: <?php
                                             $rank = "RICK";;
                                            echo $rank ?: 'Member'
                                            ?></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="page-header-tab mt-xl-4">
                            <div class="col-12 pl-0 pr-0">
                                <div class="row ">
                                    <div class="col-12 col-sm-6 mb-xs-4  pt-2 pb-2 mb-xl-0">
                                        <ul class="nav nav-tabs tab-transparent" role="tablist">
                                            <li class="nav-item d-md-none d-block">
                                                <a class="nav-link text-decoration-none" id="overview-tab"
                                                   href="{{url()->previous()}}"
                                                   role="tab" aria-controls="overview" aria-selected="true">&larr; Go
                                                                                                            Back</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link active" id="overview-tab" data-toggle="tab" href="#"
                                                   role="tab" aria-controls="overview" aria-selected="true">Overview</a>
                                            </li>
                                            <li class="nav-item">
                                                <div id="google_translate_element" class="ml-3"></div>
                                            </li>
                                        </ul>
                                    </div>
                                    <div
                                        class="col-12 col-sm-6 mb-xs-4 mb-xl-0 pt-2 pb-2 text-md-right d-none d-md-block">
                                        <div class="d-inline-flex">
                                            <button class="btn d-flex align-items-center">
                                                <i class="fa fa-arrow-left mr-1" style="font-size:15px !important;"></i>
                                                <span onclick="document.location.href='{{url()->previous()}}'"
                                                      class="cursor text-left font-weight-medium">
                                                    Go Back
                                                </span>
                                            </button>
                                        </div>
                                        <div class="d-inline-flex">
                                            <button class="btn d-flex align-items-center" style="cursor: auto !important;">
                                                <i class="fa fa-download mr-1" style="font-size:15px !important;"></i>
                                                <span class="text-left font-weight-medium">
													<a target="_blank" style="text-decoration: none !important; color: #000 !important;" href="{{url('/download-profile')}}">Download Profile</a>
													</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-content tab-transparent-content pb-0">
                            <div class="tab-pane fade show active" id="overview" role="tabpanel"
                                 aria-labelledby="overview-tab">
                                <div id="print" style="margin: 15px">
                                    @if(session('msg'))
                                        {!! session('msg') !!}
                                    @endif
                                    @if($errors->any())
                                        @foreach ($errors->all() as $error)
                                            <div class="alert alert-danger" style="color:#000"><i
                                                    class="icon-exclamation"></i> {{$error}}
                                            </div>
                                        @endforeach
                                    @endif
                                    @section('content')
                                    @show
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="footer-wrapper">
                <footer class="footer">
                    <div class="d-sm-flex justify-content-center justify-content-sm-between">
                        <span class="text-center text-sm-left d-block d-sm-inline-block">Copyright &copy; {{env('LEGAL_NAME')}}.</span>
                        <span class="float-none float-sm-right d-block mt-1 mt-sm-0 text-center">Secured Site &nbsp;<img
                                src="{{asset('images/greenlock.png')}}" alt="" style="width: 15px; height: 15px"/>&nbsp; Version 7.2.0</span>
                    </div>
                </footer>
            </div>
        </div>
    </div>
</div>
<script src="{{asset('js/jquery.js')}}"></script>
<script src="{{asset('js/bootstrap.js')}}"></script>
<script src="{{asset('material/js/member.js')}}"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery-nice-select/1.1.0/js/jquery.nice-select.min.js"></script>
@section('footer')
@show
</body>
</html>
