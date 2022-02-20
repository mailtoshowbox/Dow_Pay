@extends('layouts.app', ['activePage' => 'transfer-fund', 'titlePage' => __('Transfer Fund')])

@section('content')
<div class="content">
    <div class="container-fluid">
    <div class="row">
        <div class="col-sm-12 offset-sm-0">
            <div class="card border-top3 border-primary">
          
                <div class="card-header card-header-primary">
        
                    <div class="row">
                      <div class="col-6 text-left">
                        <h4 class="card-title">Transafer Funds</h4>
                      </div>
                      <div class="col-6 text-right">
                        <div class="form-group" id="error"><strong> Wallet Balance
                             : <span
                                id="">{{env('CURRENCY_SIGN')}} {{$balance}} {{env('CURRENCY_ISO')}}</span>
                        </strong></div>       
                        
                      </div>
                    </div>
                    <p class="card-category"></p>
                  </div>
                <div class="card-body">
                <div class="row">
                    <div class="col-sm-8 offset-sm-2">
                    <form method="post" id="form" action="{{url('/transfer-fund')}}">
                        @csrf
                        <div class="row form-group">
                            <div class="col-sm-6">
                                <label>User ID</label>
                                <input type="text" onkeyup="work()"  onkeypress="return isNumber(event)"  class="form-control" id="user_id" name="user_id">
                                <strong id="username" style="color: #0060ca"></strong>
                            </div>
                            <div class="col-sm-6">
                                <label>Enter Amount</label>
                                <input id="amount" class="form-control" name="amount" onkeypress="return isNumber(event)" >
                            </div>
                        </div>
                        <div class="row form-group">
                            <div class="col-12">
                                <label>Select Wallet</label>
                                <select class="wide" name="wallet">
                                    @foreach(config('config.wallet_types') as $e)
                                        <option value="{{$e}}">{{$e}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        @if(env('CROSS_WALLET_TRANSFER')==TRUE)
                            <div class="row form-group">
                                <div class="col-12">
                                    <label>To Wallet</label>
                                    <select class="wide" name="to_wallet">
                                        @foreach(config('config.wallet_types') as $e)
                                            <option value="{{$e}}">{{$e}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @endif
                        <div class="form-group" id="error"><strong>{{config('config.wallet_types')[0]}} Wallet Balance
                                is: <span
                                    id="">{{env('CURRENCY_SIGN')}} {{$balance}} {{env('CURRENCY_ISO')}}</span>
                            </strong></div>
                        @if(env('CROSS_WALLET_TRANSFER')==TRUE)
                            <div class="form-group text-info" id="error">When you transfer from One Wallet to Different
                                Wallet, {{env('CROSS_WALLET_TRANSFER_CHARGE')}}% Transaction charge will be
                                applicable
                            </div>
                        @endif
                        <div class="form-group">
                            <button type="button" id="btn" onclick="submit_form()" class="btn btn-info btn-block load">
                                Proceed
                            </button>
                        </div>
                    </form>
                </div>
            </div>
                </div>
            </div>
        </div>
    </div>
    </div>
</div>
<script type="text/javascript">

 
function isNumber(evt) {
    evt = (evt) ? evt : window.event;
    var charCode = (evt.which) ? evt.which : evt.keyCode;
    if (charCode > 31 && (charCode < 48 || charCode > 57)) {
        return false;
    }
    return true;
}
        function work() { 
          
            var v = $('#user_id').val().replace(/\D/g,'') ;
     
            if (v.length > 4) {
                $('#username').html('Checking......');
                $.get("{{url('/getuser')}}/" + v, function (data) {
                    $('#username').html(data);
                });
            }
        }

        function submit_form() {
            $('#error').html('&nbsp;');
            var id = $('#user_id').val();
            var amount = $('#amount').val();
            if (id === '') {
                $('#error').html('<div class="alert alert-danger">User ID is required</div>');
            } else if ((amount === '')) {
                $('#error').html('<br/><div class="alert alert-danger mt-1">Please enter amount</div>');
            } else {
                $('#error').html('<br/><div class="alert alert-info mt-1">Please wait...</div>');
                var form = $('#form')[0];
                var data = new FormData(form);
                $("#btn").prop("disabled", true);
                $.ajax({
                    type: "POST",
                    url: "{{url('/transfer')}}", 
                    data: data,
                    processData: false,
                    contentType: false,
                    cache: false,
                    timeout: 600000,
                    success: function (data) {
                        $("#form").html(data);
                    },
                    error: function (e) {
                        $('#result').show('slow');
                        $("#result").html('<div class="alert alert-danger">' + e.responseText + '</div>');
                        console.log("ERROR : ", e);
                        $("#btn").prop("disabled", false);

                    }
                });
            }
        }
    </script>
@endsection
@section('footer')
   
@endsection
