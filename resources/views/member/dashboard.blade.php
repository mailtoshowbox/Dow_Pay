@extends('layouts.app', ['activePage' => 'dashboard', 'titlePage' => __('Dashboard')])

@section('content')
  <div class="content">
    <div class="container-fluid">
      
      <div class="row">
        <div class="col-lg-4 col-md-4 col-sm-6">
          <div class="card card-stats">
            <div class="card-header card-header-warning card-header-icon">
              <div class="card-icon">
                <i class="material-icons">account_balance_wallet
                </i>
              </div>
              <p class="card-category">{{env('BATCH_WALLET_LABEL')}} </p>
              <h3 class="card-title">{{$wallet}}
                <small>{{env('CURRENCY_SIGN')}} </small>
              </h3>
              <div class="dropdown dropleft card-menu-dropdown">
                <button class="btn p-0" type="button"
                        id="cardMenuButtonpurchasedetails"
                        data-toggle="dropdown" aria-haspopup="true"
                        aria-expanded="false">
                    <i class="fa fa-ellipsis-h card-menu-btn"></i>
                </button>
                <div class="dropdown-menu"
                     aria-labelledby="cardMenuButtonpurchasedetails"
                     x-placement="left-start">
                    <a class="dropdown-item" href="{{url('/the-wallet')}}">View Wallets</a>
                    <a class="dropdown-item" href="{{url('/withdraw-fund')}}">Withdraw Fund</a>
                </div>
            </div>
            </div>
            <div class="card-footer">
              <div class="stats">
                <i class="material-icons text-primary">warning</i>
                <a href="#pablo">Transfer Without charges</a>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-6">
          <div class="card card-stats">
            <div class="card-header card-header-success card-header-icon">
              <div class="card-icon">
                <i class="material-icons">card_giftcard</i>
              </div>
              <p class="card-category">{{env('BATCH_EARN_LABEL')}} </p>
              <h3 class="card-title">{{$total_earn . ' '.env('CURRENCY_SIGN')}} </h3>
              <div class="dropdown dropleft card-menu-dropdown">
                <button class="btn p-0" type="button"
                        id="cardMenuButtonpurchasedetails"
                        data-toggle="dropdown" aria-haspopup="true"
                        aria-expanded="false">
                    <i class="fa fa-ellipsis-h card-menu-btn"></i>
                </button>
                <div class="dropdown-menu"
                     aria-labelledby="cardMenuButtonpurchasedetails"
                     x-placement="left-start">
                    <a class="dropdown-item" href="{{url('/my-earnings')}}">View Earnings</a>
                </div>
            </div>
            </div>
            <div class="card-footer">
              <div class="stats">
                <i class="material-icons">date_range</i>Since you joined
              </div>
            </div>
          </div>
        </div>
        {{-- <div class="col-lg-3 col-md-4 col-sm-6">
          <div class="card card-stats">
            <div class="card-header card-header-danger card-header-icon">
              <div class="card-icon">
                <i class="material-icons">support_agent</i>
              </div>
              <p class="card-category">{{env('BATCH_SUPPORT_LABEL')}} </p>
              <h3 class="card-title">{{$msg_count}}</h3>
              <div class="dropdown dropleft card-menu-dropdown">
                <button class="btn p-0" type="button"
                        id="cardMenuButtonpurchasedetails"
                        data-toggle="dropdown" aria-haspopup="true"
                        aria-expanded="false">
                    <i class="fa fa-ellipsis-h card-menu-btn"></i>
                </button>
                <div class="dropdown-menu"
                     aria-labelledby="cardMenuButtonpurchasedetails"
                     x-placement="left-start">
                    <a class="dropdown-item" href="{{url('/compose')}}">Write</a>
                    <a class="dropdown-item" href="{{url('/my-inbox')}}">Inbox</a>
                    <a class="dropdown-item" href="{{url('/sent-inbox')}}">Outbox</a>
                </div>
            </div>
            </div>
            <div class="card-footer">
              <div class="stats">
                <i class="material-icons">local_offer</i> Connect with your Sponser
              </div>
            </div>
          </div>
        </div> --}}
        <div class="col-lg-4 col-md-4 col-sm-6">
          <div class="card card-stats">
            <div class="card-header card-header-info card-header-icon">
              <div class="card-icon">
                <i class="material-icons">group</i>
              </div>
              <p class="card-category">{{env('BATCH_DOWNLINE_LABEL')}} </p>
              <h3 class="card-title">@php($downline=($leg_info->total_a+$leg_info->total_b+$leg_info->total_c+$leg_info->total_d+$leg_info->total_e+$leg_info->total_f+$leg_info->total_g+$leg_info->total_h+$leg_info->total_i+$leg_info->total_j))
                {{$downline}}</h3>
              <div class="dropdown dropleft card-menu-dropdown">
                <button class="btn p-0" type="button"
                        id="cardMenuButtonpurchasedetails"
                        data-toggle="dropdown" aria-haspopup="true"
                        aria-expanded="false">
                    <i class="fa fa-ellipsis-h card-menu-btn"></i>
                </button>
                <div class="dropdown-menu"
                     aria-labelledby="cardMenuButtonpurchasedetails"
                     x-placement="left-start">
                    <a class="dropdown-item" href="{{url('/my-downline-list')}}">All Downlines</a>
                    <a class="dropdown-item" href="{{url('/my-downline-list/Active')}}">Active Downlines</a>
                    <a class="dropdown-item" href="{{url('/my-downline-list/Inactive')}}">Inactive
                                                                                          Downlines</a>
                </div>
            </div>
            </div>
            <div class="card-footer">
              <div class="stats">
                <i class="material-icons">update</i> Your Team
              </div>
            </div>
          </div>
        </div>
      </div> 
      <div class="row">
        
        <div class="col-lg-12  col-md-12">
          <div class="card">
            <div class="card-header card-header-warning">
              <h4 class="card-title"> <i class="material-icons">history</i> {{env('RECENT_EARANING_TITLE')}} </h4> 
            </div>
            <div class="card-body table-responsive">
              @if(count($earning) >0)
              <table class="table table-hover">
                  <thead  class="text-warning">
                 
                      <th>#</th>
                      <th>Income Type</th>
                      <th>Amount</th>
                      <th>Date</th>
                 
                  </thead>
                  <tbody>
                  @php($sn=1)
                  @foreach($earning as $e)
                      <tr>
                          <th scope="row">{{$sn++}}</th>
                          <td>{{$e->type}}</td>
                          <td>{{env('CURRENCY_SIGN')}} {{$e->amount}}</td>
                          <td>{{$e->date}}</td>
                      </tr>
                  @endforeach
                  </tbody>
              </table>
              {!! $earning->links() !!}
          @else
              {!! norecord() !!}
          @endif

             
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('js')
  <script>
    $(document).ready(function() {
      // Javascript method's body can be found in assets/js/demos.js
      md.initDashboardPageCharts();
    });
  </script>
@endpush