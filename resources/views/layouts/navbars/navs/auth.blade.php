<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-transparent navbar-absolute fixed-top ">
  <div class="container-fluid">
      <div class="navbar-wrapper">
        <a class="navbar-brand" href="#">
           <h2>{{ $heading }}</h2>
          
        </a>
     </div>
  
     
     <button class="navbar-toggler" type="button" data-toggle="collapse" 
     aria-controls="navigation-index" aria-expanded="false" aria-label="Toggle navigation">
     <span class="sr-only">Toggle navigation</span>
     <span class="navbar-toggler-icon icon-bar"></span>
     <span class="navbar-toggler-icon icon-bar"></span>
     <span class="navbar-toggler-icon icon-bar"></span>
     </button>
     <div class="collapse navbar-collapse justify-content-end">
      <div class="d-flex align-items-center justify-content-md-end">
        <div class="border-right-dark pr-4 mb-3 mb-xl-0 d-xl-block d-none">
            <p class="text-muted">Today</p>
            <h6 class="font-weight-medium text-dark mb-0">20 Feb 2022</h6>
        </div>
        <div class="pr-4 pl-4 mb-3 mb-xl-0 d-xl-block d-none">
            <p class="text-muted">Rank</p>
            <h6 class="font-weight-medium text-dark mb-0">
                Member                                        </h6>
        </div>
        <div class="pr-1 mb-3 mb-xl-0">
            <button onclick="printDiv()" type="button" class="btn btn-info btn-icon mr-2"><i class="fa fa-print" style="font-size:15px !important;"></i></button>
        </div>
                                            <div class="pr-1 mb-3 mb-xl-0">
            <button onclick="document.location.href='https://nilaapaystore.com/profile'" type="button" class="btn btn-success btn-icon mr-2"><i class="fa fa-refresh" style="font-size:15px !important;"></i></button>
        </div>
        <div class="mb-3 mb-xl-0 d-md-none d-block">
            <button type="button" class="btn btn-success">Rank: Member</button>
        </div>
    </div>
        <ul class="navbar-nav" id="logout">
           <li class="nav-item">
              <a class="nav-link" href="{{ route('member') }}">
                 <i class="material-icons">dashboard</i>
                 <p class="d-lg-none d-md-block">
                    Stats
                 </p>
              </a>
           </li>
           <li class="nav-item dropdown">
              <a class="nav-link" href="#pablo" id="navbarDropdownProfile" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                 <i class="material-icons">person</i>
                 <p class="d-lg-none d-md-block">
                    Account
                 </p>
              </a>
              <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownProfile">
                 <a class="dropdown-item" href="{{ url('profile') }}" >Profile</a>
                 <div class="dropdown-divider"></div>
                 <a class="dropdown-item" id="logout-btn" href="{{ url('member-logout') }}" onclick="event.preventDefault();document.getElementById('logout-form').submit();">Log out</a>
              </div>
           </li>
        </ul>
     </div> 
  </div>
</nav>