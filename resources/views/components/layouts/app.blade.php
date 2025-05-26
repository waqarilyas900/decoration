
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="apple-touch-icon" sizes="76x76" href="{{ url('/') }}/assets/img/apple-icon.png">
    <link rel="icon" type="image/png" href="{{ url('/') }}/assets/img/favicon.png">
    <title>
        Sewing
    </title>


    <link rel="canonical" href="https://www.creative-tim.com/product/soft-ui-dashboard" />

    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />

    <link href="{{ url('/') }}/assets/css/nucleo-icons.css" rel="stylesheet" />
    <link href="{{ url('/') }}/assets/css/nucleo-svg.css" rel="stylesheet" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="{{ url('/') }}/assets/css/nucleo-svg.css" rel="stylesheet" />

    <link id="pagestyle" href="{{ url('/') }}/assets/css/soft-ui-dashboard.min.css?v=1.0.7" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @livewireStyles
    {{-- @vite(['resources/js/app.js', 'resources/css/app.css']) --}}
    <style>
        input[type="checkbox"] {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            width: 32px;
            height: 32px;
            outline: none;
            background-color: #f3f3f3;
            cursor: pointer;
            position: relative;
            box-shadow: 0px 0px 0px 2px rgba(0,0,0,0.5), 0px 2px 4px rgba(0,0,0,0.1), 0px 4px 8px rgba(0,0,0,0.1), 0px 8px 16px rgba(0,0,0,0.1);
        }

        [type=checkbox]:checked,
        [type=radio]:checked {
            /* border-color: transparent; */
            background-color: #fff;
            border-color: green;
            background-size: 100% 100%;
            background-position: center;
            background-repeat: no-repeat;
        }

        /* Creating a green tick mark */
        input[type="checkbox"]:checked::before {
            content: '';
            position: absolute;
            left: 10px;
            /* Center the tick mark */
            top: 5px;
            width: 10px;
            height: 20px;
            border: solid green;

            /* Set the color of the tick */
            border-width: 0 4px 4px 0;
            /* Adjust the thickness of the tick */
            transform: rotate(45deg);
        }

        [type=checkbox]:checked:hover,
        [type=checkbox]:checked:focus,
        [type=radio]:checked:hover,
        [type=radio]:checked:focus {
            border-color: green;
            background-color: #fff !important;
        }

        input[type="checkbox"] {
            border-color: green;
        }

        input[type="checkbox"]:disabled {
            background-color: #efefef !important;
            border-color: #efefef;
            cursor: not-allowed;
            opacity: 0.2;
        }

        .fi-modal-content {
            padding: 33px !important;
        }
        .fi-modal-header {
            padding: 33px !important;
        }
        .fi-modal-footer {
            padding: 33px !important;
        }
        .cursor {
            cursor: pointer;
        }
        .btn-info {
            background-image: linear-gradient(45deg, #6b2e38, #053530);
        }
        .btn-primary {
            background-image: linear-gradient(45deg, #6b2e38, #053530);
        }
        .text-primary {
            color: #6b2e38 !important;
        }
        .alert-primary {
            background-image: linear-gradient(45deg, #6b2e38, #053530);
        }

        .normal-table .fixed{
            top:0px;
            position:fixed;
            width:auto;
            display:none;
            height: 48px;
            background: linear-gradient(45deg, #6b2e38, #053530);
            z-index: 111;

            }
            .normal-table .fixed th {
                color: #fff !important;
                opacity: 1 !important;
            }

        /* .text-primary {
            background-image: linear-gradient(45deg, #6b2e38, #053530);
        } */
        .table-show .text-uppercase{
                background-color:#fff !important;
                font-size: 12px !important;
                opacity: 1 !important;
                
            }
            .table-show h6{
                margin: 0;
            }
            .table-show td{
                white-space:unset !important;
            }
            .z-index-sticky {
    z-index: 1020;
    position: relative !important;
}
    </style>
  
</head>

<body class="g-sidenav-show  bg-gray-100">
    @include('components.layouts.aside')
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">

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

        <div class="container-fluid py-4">
            {{ $slot }}
            
        </div>
    </main>
    
    
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="{{ url('/') }}/assets/js/core/popper.min.js"></script>
    <script src="{{ url('/') }}/assets/js/core/bootstrap.min.js"></script>
    {{-- <script src="{{ url('/') }}/assets/js/plugins/perfect-scrollbar.min.js"></script> --}}
    <script src="{{ url('/') }}/assets/js/plugins/smooth-scrollbar.min.js"></script>
    <script src="{{ url('/') }}/assets/js/plugins/chartjs.min.js"></script>
    {{-- <script src="{{ url('/') }}/jquery.fixedTableHeader.js"></script> --}}

    <script src="https://cdn.jsdelivr.net/npm/jquery-freeze-table-items@1.3.0/index.min.js"></script>
   
    <script>
        var win = navigator.platform.indexOf('Win') > -1;
        if (win && document.querySelector('#sidenav-scrollbar')) {
            var options = {
                damping: '0.5'
            }
            Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
        }

    </script>

<script>
    document.addEventListener('livewire:init', () => {

        Livewire.on('sticky-header', (event) => {
            console.log('waqar')
            $('.table-show').freezeTableItems({freezeHeader: true, freezeFirstColumn: false});
        });   
    });
setInterval(() => {
    $('.table-show').freezeTableItems({freezeHeader: true, freezeFirstColumn: false});
}, 1000);
            
    
</script>
    

    <script async defer src="https://buttons.github.io/buttons.js"></script>

    <script src="{{ url('/') }}/assets/js/soft-ui-dashboard.min.js?v=1.0.7"></script>
    {{-- <script defer
        src="https://static.cloudflareinsights.com/beacon.min.js/v84a3a4012de94ce1a686ba8c167c359c1696973893317"
        integrity="sha512-euoFGowhlaLqXsPWQ48qSkBSCFs3DPRyiwVu3FjR96cMPx+Fr+gpWRhIafcHwqwCqWS42RZhIudOvEI+Ckf6MA=="
        data-cf-beacon='{"rayId":"84cbb3260fa25fd5","version":"2024.1.0","token":"1b7cbb72744b40c580f8633c6b62637e"}'
        crossorigin="anonymous"></script> --}}
        @livewireScripts
</body>

</html>
