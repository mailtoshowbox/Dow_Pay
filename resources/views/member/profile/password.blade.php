@extends('layouts.app', ['activePage' => 'password', 'titlePage' => __('Change Password')])
@section('content')
<div class="content">
    <div class="container-fluid">
    <div class="row">
        <div class="col-sm-8 offset-sm-2">
            <div class="card shadow3 border-top3">
                <div class="card-body">
                    <form method="post" id="form" action="{{url('/update-password')}}">
                        @csrf
                        <div class="row form-group">
                            <div class="col-sm-6">
                                <label>New Password</label>
                                <input required type="text" id="new_password"
                                       class="form-control"
                                       name="new_password">
                            </div>
                            <div class="col-sm-6">
                                <label>Retype Password</label>
                                <input required type="text" name="retype_password"
                                       id="retype_password"
                                       class="form-control">
                            </div>
                        </div>
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
                                Change &rarr;
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
