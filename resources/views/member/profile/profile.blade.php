@extends('layouts.app', ['activePage' => 'profile', 'titlePage' => __('Profile')])

@section('content')
<div class="content">
    <div class="container-fluid">
    <div class="row">
        <div class="col-sm-8 offset-sm-2">
            <div class="card shadow3 border-top3">
                <div class="card-body">
                    <form method="post" enctype="multipart/form-data" id="form" action="{{url('/update-profile')}}">
                        @csrf
                        <div class="row form-group">
                            <div class="col-12 text-center">
                                <img class="img-fluid rounded mb-2"
                                     style="max-width: 140px; height: auto"
                                     src="{{$mdetail->avatar?asset('storage/pics/'.$mdetail->avatar):asset('material/img/nouser.png')}}"><br/>
                                <a href="{{url('/change-pic')}}" data-type="ajax" data-fancybox class="btn btn-outline-primary">Change Pic</a>
                            </div>
                        </div>
                        <div class="row form-group">
                            <div class="col-sm-6">
                                <label>Name</label>
                                <input readonly type="text" id="name" value="{{old('name', $mdetail->name)}}"
                                       class="form-control"
                                       name="name">
                            </div>
                            <div class="col-sm-6">
                                <label>Phone</label>
                                <input type="text" name="phone" value="{{old('phone', $mdetail->phone)}}" id="phone"
                                       class="form-control">
                            </div>
                        </div>
                        <div class="row form-group">
                            <div class="col-sm-12">
                                <label>Email</label>
                                <input type="email" name="email" value="{{old('email', $mdetail->email)}}" id="email"
                                       class="form-control">
                            </div>
                        </div>
                        <div class="row form-group">
                            <div class="col-sm-12">
                                <label>Address</label>
                                <input type="text" id="address" value="{{old('address', $profile->address)}}"
                                       class="form-control"
                                       name="address">
                            </div>
                        </div>
                        <div class="row form-group">
                            <div class="col-sm-6">
                                <label>Postal Code / ZIP</label>
                                <input type="text" id="pin" value="{{old('pin', $profile->pin)}}"
                                       class="form-control"
                                       name="pin">
                            </div>
                            <div class="col-sm-6">
                                <label>City</label>
                                <input type="text" name="city" value="{{old('city', $profile->city)}}" id="city"
                                       class="form-control">
                            </div>
                        </div>
                        @if(env('NEED_NOMINEE')===TRUE)
                            <div class="row form-group">
                                <div class="col-sm-6">
                                    <label>Nominee Name</label>
                                    <input type="text" id="nominee_name"
                                           value="{{old('nominee_name', $profile->nominee_name)}}"
                                           class="form-control"
                                           name="nominee_name">
                                </div>
                                <div class="col-sm-6">
                                    <label>Nominee Address</label>
                                    <input type="text" id="nominee_address"
                                           value="{{old('nominee_address', $profile->nominee_address)}}"
                                           class="form-control"
                                           name="nominee_address">
                                </div>
                            </div>
                        @endif
                        @if(env('FUND_CREDIT')==='Bank')
                            <div class="form-group row">
                                <div class="col-sm-12 mb-3">
                                    <label>Account Holder Name</label>
                                    <input type="text" id="bank_ac_holder"
                                           value="{{old('bank_ac_holder', $profile->bank_ac_holder)}}"
                                           class="form-control"
                                           name="bank_ac_holder">
                                </div>
                                <div class="col-sm-6">
                                    <label>Bank Name</label>
                                    <input type="text" id="bank_name"
                                           value="{{old('bank_name', $profile->bank_name)}}"
                                           class="form-control"
                                           name="bank_name">
                                </div>
                                <div class="col-sm-6">
                                    <label>Bank AC No</label>
                                    <input type="text" id="bank_ac_no"
                                           value="{{old('bank_ac_no', $profile->bank_ac_no)}}"
                                           class="form-control"
                                           name="bank_ac_no">
                                </div>
                                <div class="col-sm-6 mt-3">
                                    <label>Bank IFSC</label>
                                    <input type="text" id="bank_ifsc"
                                           value="{{old('bank_ifsc', $profile->bank_ifsc)}}"
                                           class="form-control"
                                           name="bank_ifsc">
                                </div>
                                <div class="col-sm-6 mt-3">
                                    <label>Bank Branch</label>
                                    <input type="text" id="bank_branch"
                                           value="{{old('bank_branch', $profile->bank_branch)}}"
                                           class="form-control"
                                           name="bank_branch">
                                </div>
                            </div>
                        @endif
                        <div class="row form-group">
                            @if(env('FUND_CREDIT')==='BTC')
                                <div class="col">
                                    <label>Bitcoin Address</label>
                                    <input type="text" id="btc_address"
                                           value="{{old('btc_address', $profile->btc_address)}}"
                                           class="form-control"
                                           name="btc_address">
                                </div>
                            @elseif(env('FUND_CREDIT')==='UPI')
                                <div class="col">
                                    <label>UPI Address</label>
                                    <input type="text" id="upi_address"
                                           value="{{old('upi_address', $profile->upi_address)}}"
                                           class="form-control"
                                           name="upi_address">
                                </div>
                            @endif
                            <div class="col">
                                <label>PAN / Tax ID</label>
                                <input type="text" id="pan_card"
                                       value="{{old('pan_card', $profile->pan_card)}}"
                                       class="form-control"
                                       name="pan_card">
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col">
                                <label>Aadhar No</label>
                                <input type="text" id="aadhar"
                                       value="{{old('aadhar', $profile->aadhar)}}"
                                       class="form-control"
                                       name="aadhar">
                            </div>
                        </div>
                        @if(env('NEED_KYC')===TRUE)
                            @if(!blank($mdetail->id_proof))
                                <div class="row form-group">
                                    <div class="col-sm-6">
                                        <label>Select ID Proof</label>
                                        <a class="btn btn-xs btn-primary"
                                           href="{{asset('storage/pics/'.$mdetail->id_proof)}}"
                                           target="_blank">View Here</a>
                                    </div>
                                    <div class="col-sm-6">
                                        <label>Select Address Proof</label>
                                        <a class="btn btn-xs btn-primary"
                                           href="{{asset('storage/pics/'.$mdetail->address_proof)}}"
                                           target="_blank">View Here</a>
                                    </div>
                                </div>
                            @else
                                <div class="row form-group">
                                    <div class="col-sm-6">
                                        <label>Select ID Proof</label>
                                        <input id="id_proof" type="file"
                                               class="form-control"
                                               name="id_proof">
                                    </div>
                                    <div class="col-sm-6">
                                        <label>Select Address Proof</label>
                                        <input id="address_proof" type="file"
                                               class="form-control"
                                               name="address_proof">
                                    </div>
                                </div>
                            @endif
                        @endif
                        <div class="form-group row">
                            <div class="col">
                                <label>Current Password</label>
                                <input type="password" id="current_password" required="required"
                                       class="form-control"
                                       name="current_password">
                            </div>
                        </div>
                        <div class="form-group">
                            <button type="submit" id="btn" class="btn btn-primary btn-block load">
                                Proceed &rarr;
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    </div>
</div>
@endsection
@section('header')
    <link rel="stylesheet" href="//cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.css"/>
    <style type="text/css">
        @media only screen and (min-width: 768px) {
            .fancybox-content {min-width: 500px}
            }
    </style>
@endsection
@section('footer')
    <script src="//cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.js"></script>
@endsection
