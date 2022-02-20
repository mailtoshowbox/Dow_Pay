<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-transparent navbar-absolute fixed-top ">
  
  <div class="container-fluid">
    <nav class="navbar navbar-expand-lg navbar-transparent navbar-absolute fixed-top ">
      <div class="container-fluid">
        <div class="navbar-wrapper">
      <a class="navbar-brand" href="#"><h2>{{ $heading }}</h2></a> 
    </div><br />
        <button class="navbar-toggler" type="button" data-toggle="collapse" aria-controls="navigation-index" aria-expanded="false" aria-label="Toggle navigation">
          <span class="sr-only">Toggle navigation</span>
          <span class="navbar-toggler-icon icon-bar"></span>
          <span class="navbar-toggler-icon icon-bar"></span>
          <span class="navbar-toggler-icon icon-bar"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end">
          <a class="btn btn-danger mr-4" target="_blank" href="https://www.creative-tim.com/product/material-dashboard-pro-laravel">
            <i class="material-icons">touch_app</i>
              <span>Register New</span>
          </a>
          <a id="docs" class="btn btn-success mr-4" target="_blank" href="https://material-dashboard-pro-laravel.creative-tim.com/docs/getting-started/laravel-setup.html">
            <i class="material-icons">verified_user</i>
              <span><b>{{session('member_id')}}</b></span>
          </a> 
          <ul class="navbar-nav" id="logout">
            <li class="nav-item">
              <a class="nav-link" href="https://material-dashboard-pro-laravel.creative-tim.com/dashboard">
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
                  <a class="dropdown-item" href="https://material-dashboard-pro-laravel.creative-tim.com/profile">Profile</a>
                  <div class="dropdown-divider"></div>
                  <a class="dropdown-item" id="logout-btn" href="https://material-dashboard-pro-laravel.creative-tim.com/logout" onclick="event.preventDefault();document.getElementById('logout-form').submit();">Log out</a>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </nav>
    
  </div>
</nav>
