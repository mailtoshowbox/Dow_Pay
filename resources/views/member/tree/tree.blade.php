@extends('layouts.app', ['activePage' => 'myfamily', 'titlePage' => __('My team')])
@section('content')
<div class="content">
    <div class="container-fluid">
      <div class="card">
        <div class="card-header card-header-primary">
            <div class="row">
                <div class="col-sm-7" style="font-size: 10px !important;">
                    <a  >
                        <img class="img-fluid" style="max-width: 40px; height: auto"
                             src="{{asset('material/img/green_user.png')}}" alt="Green User"> <span
                            class="text-success">Active</span> &nbsp;
                    </a>
                    <a  >
                        <img class="img-fluid" style="max-width: 40px; height: auto"
                             src="{{asset('material/img/red_user.png')}}" alt="Green User"> <span
                            class="text-danger">Inactive</span> &nbsp;
                    </a>
                    <a target="_blank"  >
                        <img class="img-fluid" style="max-width: 35px; height: auto"
                             src="{{asset('material/img/new_user.png')}}" alt="Green User"> <span
                            class="text-info">BLANK</span>
                    </a>
                    &nbsp;
                </div>
                <div class="col-sm-5">
                    <form class="search-box float-right" method="post" action="{{url('/my-tree')}}">
                        @csrf
                        
                    </form>
                </div>
        </div>
    </div>
        <div class="card-body">
            <div class="tree">
                @if(env('LEG_NUMBER')==1)
                    <div class="row">
                        <div class="col-sm-6 offset-sm-1">
                            <div class="card shadow2">
                                <div class="card-header bg-primary text-white">Click on any user to see it's
                                                                               downline
                                </div>
                                <div class="card-body">
                                    <div class="card-text">
                                        @php($id = generate_tree($topid, '/my-tree'))<br/>
                                        @if($id == false)
                                            <div class="alert alert-danger">Wrong User ID entered</div>
                                        @endif
                                        <div
                                            style="border-left: rgba(235,177,0,0.95) 3px solid; padding-left: 5px; margin-left: 50px">
                                            <?php
                                            $downlines = DB::table('members')->select('id')
                                                           ->where('sponsor', $topid)->get();
                                            ?>
                                            @foreach($downlines as $e)
                                                <div class="mt-4"><i
                                                        class="ti-more-alt"></i> @php($id = generate_tree($e->id, '/my-tree'))
                                                </div>
                                            @endforeach
                                            <div class="mt-4">
                                                <i class="ti-more-alt"></i>
                                                @php(blank_tree($topid,'A'))
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
         
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

   
@endsection
@section('header')
    <style type="text/css">
        .green_tree * {
            color: #008911 !important;
            font-size: 12px
        }

        .red_tree * {
            color: #ff363b !important;
            font-size: 12px
        }

        .popover .arrow {
            display: none !important
        }

        .popover-body {
            color: #0c4b85 !important;
        }

        .popover-body span {
            font-weight: 400;
            color: #0070d7
        }

        .popover-header {
            background-color: #1d72f3 !important;
            color: #fff !important;
            border-radius: 0px !important;
            font-weight: bold;
            text-align: center
        }

        .tree-table * {
            text-align: center !important;
        }

        .tree img {
            max-width: 60px !important;
            height: auto
        }

        .tree.table thead tr th, .table tbody tr td {
            border: 0
        }

        .tree .line {
            width: 100%;
            max-width: 50% !important;
        }

        .tree-table {
            width: 100%;
            min-width: 800px
        }

        .card i {
            color: rgba(235, 177, 0, 0.95);
            font-weight: bold;
            font-size: 16px
        }
    </style>
@endsection
@section('footer')
    @handheld
    <script>
        $('.green_tree').popover('disable')
        $('.red_tree').popover('disable')
    </script>
    @endhandheld
@endsection
