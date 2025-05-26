<!DOCTYPE html>
<html lang="en">

<head>
    <title>
        Login Page
    </title>


    <link rel="canonical" href="https://www.creative-tim.com/product/soft-ui-dashboard" />




    <link id="pagestyle" href="../assets/css/soft-ui-dashboard.min.css?v=1.0.7" rel="stylesheet" />
    @filamentStyles
</head>

<body class>
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NKDMSK6" height="0" width="0"
            style="display:none;visibility:hidden"></iframe></noscript>


    <main class="main-content  mt-0">
       {{ $slot }}
    </main>
    <footer class="footer py-5">
        <div class="container">

            <div class="row">
                <div class="col-8 mx-auto text-center mt-1">
                    <p class="mb-0 text-secondary">
                        Copyright © <script>
                            document.write(new Date().getFullYear())

                        </script> 
                        Emergency Responder Products, LLC <p class="text-secondary">Developed With <span style="color:red;"> ❤ </span> By <a href="https://confinito.com/" target="__blank">CONFINITO</a> </p>
                    </p>
                </div>
            </div>
        </div>
    </footer>
    @filamentScripts
</body>

</html>
