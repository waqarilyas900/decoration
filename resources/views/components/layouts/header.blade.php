  <!-- Navbar -->
    <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl" id="navbarBlur"
            navbar-scroll="true">
            <div class="container-fluid py-1 px-3">
                <nav aria-label="breadcrumb">
               
                    <h6 class="font-weight-bolder mb-0">{{ strtoupper(str_replace(".", " ", \Request::route()->getName())) }}</h6>
                </nav>
                <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4" id="navbar">
                  
                    <ul class="navbar-nav  justify-content-end">
                        
                        <li class="nav-item d-xl-none ps-3 d-flex align-items-center">
                            <a href="javascript:;" class="nav-link text-body p-0" id="iconNavbarSidenav">
                                <div class="sidenav-toggler-inner">
                                    <i class="sidenav-toggler-line"></i>
                                    <i class="sidenav-toggler-line"></i>
                                    <i class="sidenav-toggler-line"></i>
                                </div>
                            </a>
                        </li>
                        
                       
                    </ul>
                </div>
            </div>
        </nav>
  <!-- End Navbar -->
  <script>
    // g-sidenav-pinned


    document.getElementById('iconNavbarSidenav').addEventListener('click', function() {
  document.body.classList.toggle('g-sidenav-pinned');
  
  // If `aside` is a tag name and you want to target the first <aside> element
  var asideElement = document.querySelector('aside');
  if (asideElement) {
    asideElement.classList.toggle('bg-white');
  }
});


  </script>
