@extends('layouts.app', ['activePage' => 'the-received', 'titlePage' => __('My Earn')])
@section('content')
<div class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header card-header-primary">
                <form method="post" class="card" action="{{url('/transfersearch')}}">
                    @csrf
                    <div class="row p-2">
                        <div class="col-sm-3">
                            <strong>From Date</strong>
                            <input name="fromdt" value="{{isset($fromdt)?$fromdt:''}}" class="form-control p-3"
                                   data-toggle="datepicker">
                            <div data-toggle="datepicker"></div>
                        </div>
                        <div class="col-sm-3">
                            <strong>To Date</strong>
                            <input name="todt" value="{{isset($todt)?$todt:''}}" class="form-control p-3" data-toggle="datepicker">
                            <div data-toggle="datepicker"></div>
                        </div>
                        <div class="col-sm-3">
                            <strong>Sender/Receiver ID</strong>
                            <input name="userid" class="form-control p-3">
                        </div>
                        <div class="col-sm-3"><br/>
                            <button type="submit" class="btn btn-block btn-success rounded-0 p-2">SEARCH</button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-body">    
                <div class="table-responsive">
                    @if(count($data)>0)
                        <table class="table table-hover mb-0">
                            <thead class="nexa-dark font-weight-bold">
                            <tr>
                                <th scope="col">SN</th>
                                <th scope="col">Sender ID</th>
                                <th scope="col">Receiver ID</th>
                                <th scope="col">Amount</th>
                                <th scope="col">Date</th>
                            </tr>
                            </thead>
                            <?php
                            $sn = 1;
                            ?>
                            <tbody>
                            @foreach($data as $e)
                                <tr>
                                    <th scope="row">{{$sn++}}</th>
                                    <td><a data-type="ajax" data-fancybox
                                           href="{{url('/member-detail-little/'.$e->sender_id)}}">{{env('ID_EXT').$e->sender_id}}</a>
                                    </td>
                                    <td><a data-type="ajax" data-fancybox
                                           href="{{url('/member-detail-little/'.$e->receiver_id)}}">{{env('ID_EXT').$e->receiver_id}}</a>
                                    </td>
                                    <td>{{env('CURRENCY_ISO')}} {{$e->amount}}</td>
                                    <td>{{$e->date}}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        {!! $data->links() !!}
                    @else
                        {!! norecord('No Transfer record found') !!}
                    @endif
                </div>
           </div>
      </div>
    
   
    </div>
</div>
@endsection
 
