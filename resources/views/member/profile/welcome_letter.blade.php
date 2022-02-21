@extends('layouts.app', ['activePage' => 'typography', 'titlePage' => __('Typography')])

@section('content')
<div class="content">
  <div class="container-fluid">
    <div class="card">
      <div class="card-header card-header-primary">
        
        <div class="row">
          <div class="col-6 text-left">
            <h4 class="card-title">{{$subtitle}}</h4>
          </div>
          <div class="col-6 text-right">
            <button onclick="printDiv()" type="button" class="btn btn-sm btn-rose"
            class="btn btn-info btn-icon mr-2" title="print"> <i class="material-icons">print</i></button>           
            
          </div>
        </div>
        <p class="card-category"></p>
      </div>
      <div class="card-body">    

        <div id="print" style="margin: 15px">
          <div class="row">
<div class="offset-sm-1 col-sm-10 card shadow2" style="border-left: rgba(235,177,0,0.95) 4px solid;">
<div class="card-body letter">
<p style="color: #000;font-size: 25px; font-weight: bold">
  Dear <strong>{{$mdetail->name}}</strong>,<br/>
                Date: {{date('Y-m-d', strtotime($mdetail->created_at))}}<br/>
</p>
Welcome to our Nilaa Pay Store family. I'd like to personally welcome you to our organization. Its
an exciting time for Nilaa Pay Store
as we continue to grow; we strive to remain adaptable, motivated and responsive to our new members
always.
<br>
<br>We'll be excited to help you in every step of your career journey with us. And we promise you to
help
you to acieve your dream goal within short period of time.
<br>
<br>Before I finish, I'd just like you to know that you, as part of our team, are our most important
and
greatest asset. We could not accomplish what we do every day without our members. I'm very pleased
to welcome you to Nilaa Pay Store and look forward to working with you !
<p></p>
<div style="color: #000;font-weight: bold">
<strong>Business Coordinator</strong>
<div>for, Nilaa Pay Store</div>
<div></div>
</div>

<div style="max-width: 200px; margin-top: 20px">
<img src="//bwipjs-api.metafloor.com/?bcid=code128&amp;text=NP137968&amp;parsefnc&amp;alttext=NP137968&amp;height=6">
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