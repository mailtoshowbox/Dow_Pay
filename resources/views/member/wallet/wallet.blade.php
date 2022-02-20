@extends('layouts.app', ['activePage' => 'the-wallet', 'titlePage' => __('My Wallet')])
@section('content')
<div class="content">
    <div class="container-fluid">
    <div class="row">
        <div class="col-sm-7">
            <div class="card border-top3">
                <div class="card-body">
                    <div class="table-responsive">
                        @if(count($latest_txns)>0)
                            <table class="table table-hover mb-0">
                                <thead class="nexa-dark font-weight-bold">
                                <tr>
                                    <th scope="col">SN</th>
                                    <th scope="col">Amount</th>
                                    <th scope="col">Type</th>
                                    <th scope="col">Note</th>
                                    <th scope="col">Date</th>
                                </tr>
                                </thead>
                                <?php
                                $sn = 1;
                                ?>
                                <tbody>
                                @foreach($latest_txns as $e)
                                    <tr>
                                        <th scope="row">{{$sn++}}</th>
                                        <td>{{env('CURRENCY_SIGN').' '.$e->amount}}</td>
                                        <td>{{$e->type}}</td>
                                        <td>{{$e->description}}</td>
                                        <td>{{$e->date}}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                            {!! $latest_txns->links() !!}
                        @else
                            {!! norecord('No Wallet Transactions found') !!}
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-5">
            <div class="card shadow2 border-top border-warning">
                <div class="card-body py-5">
                    <h4>Available Wallet Balances:</h4>
                    @foreach($wallets as $x)
                        <h1><span class="highlight-container"><span
                                    class="highlight">{{$x->type}} : {{env('CURRENCY_SIGN')}} {{$x->balance}}</span></span></h1>
                    @endforeach
                   
                    <a class="btn btn-block btn-success mt-4 oswald"  
                       href="{{url('/transfer-fund')}}">Transfer Fund &rarr;</a>
                </div>
            </div>
        </div>
    </div>
    </div>
</div>
@endsection
 
