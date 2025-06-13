<div class="row">
     @php
        $userType = $this->userType;
    @endphp
    @if($userType != 2)
    <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
        <a href="{{ route('order.create') }}">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Create Order</p>
                                <h5 class="font-weight-bolder mb-0">
                                    {{-- $53,000 --}}
                                    {{-- <span class="text-success text-sm font-weight-bolder">+55%</span> --}}
                                </h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
                                {{-- <i class="ni ni-money-coins text-lg opacity-10" aria-hidden="true"></i> --}}
                                <i class="fa fa-plus-circle text-lg opacity-10" aria-hidden="true"></i>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    
    <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
        <a href="{{ route('pending.orders') }}">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Pending Order</p>
                                <h5 class="font-weight-bolder mb-0">
                                    {{ number_format($pendingOrder) }}
                                    {{-- <span class="text-success text-sm font-weight-bolder">+3%</span> --}}
                                </h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
                                <i class="ni ni-cart text-lg opacity-10" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
        <a href="{{ route('ready.orders') }}">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Ready Order</p>
                                <h5 class="font-weight-bolder mb-0">
                                    {{ number_format($readyOrder) }}
                                    {{-- <span class="text-danger text-sm font-weight-bolder">-2%</span> --}}
                                </h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
                                <i class="ni ni-user-run text-lg opacity-10" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-xl-3 col-sm-6">
        <a href="{{ route('removed.orders') }}">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Removed Order</p>
                                <h5 class="font-weight-bolder mb-0">
                                    {{ number_format($removedOrder) }}
                                    {{-- <span class="text-success text-sm font-weight-bolder">+5%</span> --}}
                                </h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
                                {{-- <i class="ni ni-cart text-lg opacity-10" aria-hidden="true"></i> --}}
                                <i class="fa fa-trash text-lg opacity-10" aria-hidden="true"></i>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    @endif
    @if($userType == 2)
    <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
        <a href="{{ route('pending.orders.external') }}">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Pending Order</p>
                                <h5 class="font-weight-bolder mb-0">
                                    {{ number_format($pendingOrder) }}
                                    {{-- <span class="text-success text-sm font-weight-bolder">+3%</span> --}}
                                </h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
                                <i class="ni ni-cart text-lg opacity-10" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    @endif
</div>
