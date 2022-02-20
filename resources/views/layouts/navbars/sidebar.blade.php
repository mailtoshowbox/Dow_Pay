<div class="sidebar" data-color="azure" data-background-color="azure" data-image="{{ asset('material') }}/img/sidebar-1.jpg">
  <!--
      Tip 1: You can change the color of the sidebar using: data-color="purple | azure | green | orange | danger"

      Tip 2: you can also add an image using data-image tag
  -->
  <div class="logo">
    <a href="" class="simple-text logo-normal">
      <img style="width:66px" src="{{asset('material/img//MM_logo.png')}}">
        {{env('MY_APP_NAME')}} 
    </a>
   
   
  </div>
  <div class="sidebar-wrapper">
    <ul class="nav">
      <li class="nav-item{{ $activePage == 'dashboard' ? ' active' : '' }}">
        <a class="nav-link" href="{{ url('/member-dash') }}">
          <i class="material-icons">dashboard</i>
            <p>{{ __('Dashboard') }}</p>
        </a>
      </li>
      <li class="nav-item{{ $activePage == 'typography' ? ' active' : '' }}">
        <a class="nav-link" href="{{url('/welcome-letter')}}" >
          <i class="material-icons">mail_outline</i>
            <p>{{ __('Welcome Letter') }}</p>
        </a>
      </li>
      <li class="nav-item {{ ($activePage == 'myfamily' || $activePage == 'user-management') ? ' active' : '' }}">
        <a class="nav-link" data-toggle="collapse" href="#laravelExample1" >
          <i class="material-icons">account_tree</i> 
          <p>{{ __('Tree') }}
            <b class="caret"></b>
          </p>
        </a>
        <div class="collapse {{ ($activePage == 'myfamily' || $activePage == 'user-management') ? ' show' : '' }}" id="laravelExample1">
          <ul class="nav">
            <li class="nav-item{{ $activePage == 'myfamily' ? ' active' : '' }}">
              <a class="nav-link" href="{{ url('my-family') }}">
                <span class="sidebar-mini"> <i class="material-icons">groups</i> </span>
                <span class="sidebar-normal">{{ __('My Tree') }} </span>
              </a>
            </li>
            
          </ul>
        </div>
        
      </li>
      <li class="nav-item {{ ($activePage == 'myearnings' || $activePage == 'the-wallet'  || $activePage == 'the-transfered' || $activePage == 'the-received' || $activePage == 'transfer-fund') ? ' active' : '' }}">
        <a class="nav-link" data-toggle="collapse" href="#laravelExample" >
          <i class="material-icons">account_balance</i> 
          <p>{{ __('Earning & Wallet') }}
            <b class="caret"></b>
          </p>
        </a>
        <div class="collapse {{ ($activePage == 'dashboard' || $activePage == 'myearnings' || $activePage == 'the-wallet'  || $activePage == 'the-transfered' || $activePage == 'the-received' || $activePage == 'transfer-fund') ? ' show' : '' }}" id="laravelExample">
          <ul class="nav">
            <li class="nav-item{{ $activePage == 'myearnings' ? ' active' : '' }}">
              <a class="nav-link" href="{{ url('my-earnings') }}">
                <span class="sidebar-mini"> <i class="material-icons">history</i> </span>
                <span class="sidebar-normal">{{ __('All Earnings') }} </span>
              </a>
            </li>
            <li class="nav-item{{ $activePage == 'the-wallet' ? ' active' : '' }}">
              <a class="nav-link" href="{{ url('the-wallet') }}">
                <span class="sidebar-mini"> <i class="material-icons">account_balance_wallet</i>  </span>
                <span class="sidebar-normal">{{ __('My Wallet') }} </span>
              </a>
            </li>
            <li class="nav-item{{ $activePage == 'the-transfered' ? ' active' : '' }}">
              <a class="nav-link" href="{{ url('the-transfered') }}">
                <span class="sidebar-mini"> <i class="material-icons">call_made</i>  </span>
                <span class="sidebar-normal"> {{ __('Fund Transfered') }} </span>
              </a>
            </li>
            <li class="nav-item{{ $activePage == 'the-received' ? ' active' : '' }}">
              <a class="nav-link" href="{{ url('the-received') }}">
                <span class="sidebar-mini"> <i class="material-icons">call_received</i>  </span>
                <span class="sidebar-normal"> {{ __('Fund Received') }} </span>
              </a>
            </li>
            <li class="nav-item{{ $activePage == 'transfer-fund' ? ' active' : '' }}">
              <a class="nav-link" href="{{ url('transfer-fund') }}">
                <span class="sidebar-mini"> <i class="material-icons">payment</i>   </span>
                <span class="sidebar-normal"> {{ __('Transfer Fund') }} </span>
              </a>
            </li>
          </ul>
        </div>        
      </li>    

      <li class="nav-item {{ ($activePage == 'profile' || $activePage == 'password') ? ' active' : '' }}">
        <a class="nav-link" data-toggle="collapse" href="#profile" >
          <i class="material-icons">phonelink_lock</i> 
          <p>{{ __('Security') }}
            <b class="caret"></b>
          </p>
        </a>
        <div class="collapse {{ ($activePage == 'profile' || $activePage == 'password') ? ' show' : '' }}" id="profile">
          <ul class="nav">
            <li class="nav-item{{ $activePage == 'profile' ? ' active' : '' }}">
              <a class="nav-link" href="{{ url('profile') }}">
                <span class="sidebar-mini"> <i class="material-icons">settings_accessibility</i> </span>
                <span class="sidebar-normal">{{ __('Profile') }} </span>
              </a>
            </li>
            <li class="nav-item{{ $activePage == 'password' ? ' active' : '' }}">
              <a class="nav-link" href="{{ url('password') }}">
                <span class="sidebar-mini"> <i class="material-icons">password</i> </span>
                <span class="sidebar-normal">{{ __('Password') }} </span>
              </a>
            </li>            
          </ul>
        </div>        
      </li> 
      <li class="nav-item{{ $activePage == 'language' ? ' active' : '' }}">
        <a class="nav-link"  href="{{url('/member-logout')}}">
          <i class="material-icons">directions_run</i>
          <p>{{ __('Logout') }}</p>
        </a>
      </li>
       
    </ul>
  </div>
</div>
