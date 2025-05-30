<section>
    <div class="page-header min-vh-75">
        <div class="container">
            <div class="row">
                <div class="col-xl-4 col-lg-5 col-md-6 d-flex flex-column mx-auto">
                    <div class="card card-plain mt-8">
                        <div class="text-center">
                            <img src="/logo.jpg" class="navbar-brand-img" alt="main_logo"
                                style="width:150px; max-height:150px;">
                        </div>
                        <div class="card-header pb-0 text-left bg-transparent">
                            <h3 class="font-weight-bolder text-info text-gradient">Welcome back</h3>
                            <p class="mb-0">Enter your Pin to sign in</p>

                        </div>

                        <div class="card-body">
                            <form wire:submit.prevent="auth">

                                <label>Pin</label>
                                <div class="mb-3">
                                    <input type="password" class="form-control" placeholder="Pin" wire:model="pin"
                                        aria-label="Password" aria-describedby="password-addon">
                                    @error('pin')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                {{-- <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="rememberMe" checked>
                                    <label class="form-check-label" for="rememberMe">Remember me</label>
                                </div> --}}
                                <div class="text-center">
                                    <button type="submit" class="btn bg-gradient-info w-100 mt-4 mb-0">Sign
                                        in</button>
                                </div>
                            </form>
                        </div>

                    </div>
                </div>
                <div class="col-md-6">
                    <div class="oblique position-absolute top-0 h-100 d-md-block d-none me-n8">
                        <div class="oblique-image bg-cover position-absolute fixed-top ms-auto h-100 z-index-0 ms-n6"
                            style="background-image:url('../assets/img/curved-images/curved6.jpg')"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
