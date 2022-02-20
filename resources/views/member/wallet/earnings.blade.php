@extends('layouts.app', ['activePage' => 'myearnings', 'titlePage' => __('My Earn')])
@section('content')
<div class="content">
    <div class="container-fluid">

        <div class="card">
            <div class="card-header card-header-primary">
              
                <form method="post" class="card" action="{{url('/earningsearch')}}">
                    @csrf
                    <div class="row card-body">
                        <div class="col-sm-3">
                            <strong>From Date</strong>
                            <input name="fromdt" value="{{isset($fromdt)?$fromdt:''}}" class="form-control"
                                   data-toggle="datepicker">
                            <div data-toggle="datepicker"></div>
                        </div>
                        <div class="col-sm-3">
                            <strong>To Date</strong>
                            <input name="todt" value="{{isset($todt)?$todt:''}}" class="form-control" data-toggle="datepicker">
                            <div data-toggle="datepicker"></div>
                        </div>
                     
                        <div class="col-sm-3"><br/>
                            <button type="submit" class="btn btn-block btn-success rounded-0 p-2">SEARCH</button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-body">    
                <div class="table-responsive">
                    @if(count($earnings)>0)
                        <table class="table table-hover mb-0">
                            <thead class="nexa-dark font-weight-bold">
                            <tr>
                                <th scope="col">SN</th>
                                <th scope="col">Income Type</th>
                                <th scope="col">Amount</th>
                                <th scope="col">Date</th>
                                <th scope="col">Information</th>
                            </tr>
                            </thead>
                            <?php
                            $sn = 1;
                            ?>
                            <tbody>
                            @foreach($earnings as $e)
                                <tr>
                                    <th scope="row">{{$sn++}}</th>
                                    <td>{{$e->type}}
                                        
                                    </td>
                                    <td>{{env('CURRENCY_SIGN').' '.$e->amount}}</td>
                                    <td>{{$e->date}}</td>
                                    <td>@if($e->type === 'Referral Income' || $e->type === 'Level Income') Referred
                                        By: {{$e->refid}} @else
                                            @if($e->data)
                                                @foreach(unserialize($e->data) as $a => $b)
                                                    {{$a}} : {{$b}}
                                                @endforeach
                                            @endif
                                        @endif</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        {!! $earnings->appends(request()->query())->links() !!}
                    @else
                        {!! norecord('No Income record found') !!}
                    @endif
                </div>
           </div>
      </div>
      
      </div>
      </div>
      </div>
      </div>
            </div>
          </div>
    
    
    </div>
</div>
@endsection
@section('header')
    <link type="text/css"
          href="//cdnjs.cloudflare.com/ajax/libs/datepicker/0.6.5/datepicker.min.css"
          rel="stylesheet">
    <link rel="stylesheet" href="//cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.css"/>
    <style type="text/css">
        @media only screen and (min-width: 768px) {
            .fancybox-content {min-width: 500px}
            }
    </style>
@endsection
@section('footer')
    <script type="text/javascript"
            src="//cdnjs.cloudflare.com/ajax/libs/datepicker/0.6.5/datepicker.min.js"></script>
    <script type="text/javascript">
        $('[data-toggle="datepicker"]').datepicker({
            format: 'yyyy-mm-dd',
        });
    </script>
    <script src="//cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.js"></script>
@endsection
