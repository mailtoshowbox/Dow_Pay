<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-transparent navbar-absolute fixed-top ">
  
  <div class="container-fluid row">
    <div class="col-sm-6 col-6 mb-4 mb-xl-0">
        <h3>My Profile</h3>
        <h6 class="font-weight-normal mb-0 text-muted">Update your profile</h6>
    </div>
    <div class="col-sm-6 col-6 mb-xl-0">
        <div class="d-flex align-items-center justify-content-md-end">
             
            <div class="pr-1 mb-3 mb-xl-0">
                <button onclick="printDiv()" type="button" class="btn btn-info btn-icon mr-2">
                  <i class="material-icons">touch_app</i>
                  <span>Register New</span> </button>
            </div>
                                                <div class="pr-1 mb-3 mb-xl-0">
                <button onclick="document.location.href='https://nilaapaystore.com/profile'" type="button" 
                class="btn btn-success btn-icon mr-2">
                <i class="material-icons">verified_user</i>
              <span><b>{{session('member_id')}}</b></span>
              </button>
            </div>
            
            
        </div>
        
    </div>
    
  </div>
</nav>
