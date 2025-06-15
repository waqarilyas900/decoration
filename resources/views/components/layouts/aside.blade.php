<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-3 "
    id="sidenav-main">
    <div class="sidenav-header">
        <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none"
            aria-hidden="true" id="iconSidenav"></i>
        <a class="navbar-brand m-0 d-flex" href="{{ route('home') }}" style="padding: 0; justify-content:center;">
            <img src="/logo.jpg" class="navbar-brand-img" alt="main_logo" style="width:100px; max-height:100px;">
            {{-- <span class="ms-1 font-weight-bold h2">SWING</span> --}}
        </a>
    </div>
    <hr class="horizontal dark mt-0">
    <div class="w-auto " id="sidenav-collapse-main">
        @php
            $user = auth()->user();
        @endphp
        <ul class="navbar-nav">
            @if ($user->type != 2)
            <li class="nav-item">
                <a class="nav-link  {{ \Request::route()->getName() == 'order.create' ? 'active' : '' }}"
                    href="{{ route('order.create') }}">
                    <div
                        class="icon icon-shape icon-sm shadow border-radius-md  text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fa fa-plus-circle text-lg opacity-10" aria-hidden="true"></i>
                        
                    </div>
                    <span
                        class="nav-link-text ms-1 h5 mb-0  {{ \Request::route()->getName() == 'order.create' ? 'text-primary' : '' }}">CREATE
                        ORDER</span>
                </a>
            </li>
            <li class="nav-item mt-4">
                <a class="nav-link  {{ \Request::route()->getName() == 'pending.orders' ? 'active' : '' }}"
                    href="{{ route('pending.orders') }}">
                    <div
                        class="icon icon-shape icon-sm shadow border-radius-md  text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-cart text-lg opacity-10" aria-hidden="true"></i>
                    </div>
                    <span
                        class="nav-link-text ms-1 h5 mb-0 {{ \Request::route()->getName() == 'pending.orders' ? 'text-primary' : '' }}">PENDING
                        ORDERS</span>
                </a>
            </li>
            <li class="nav-item mt-4">
                <a class="nav-link  {{ \Request::route()->getName() == 'ready.orders' ? 'active' : '' }}"
                    href="{{ route('ready.orders') }}">
                    <div
                        class="icon icon-shape icon-sm shadow border-radius-md  text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-user-run text-lg opacity-10" aria-hidden="true"></i>
                    </div>
                    <span
                        class="nav-link-text ms-1 h5 mb-0 {{ \Request::route()->getName() == 'ready.orders' ? 'text-primary' : '' }}">READY
                        ORDERS</span>
                </a>
            </li>
            <li class="nav-item mt-2">
                <a class="nav-link  {{ \Request::route()->getName() == 'removed.orders' ? 'active' : '' }}"
                    href="{{ route('removed.orders') }}">
                    <div
                        class="icon icon-shape icon-sm shadow border-radius-md  text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fa fa-trash text-lg opacity-10" aria-hidden="true"></i>
                    </div>
                    <span
                        class="nav-link-text ms-1 h5 mb-0 {{ \Request::route()->getName() == 'removed.orders' ? 'text-primary' : '' }}">REMOVED
                        ORDERS</span>
                </a>
            </li>

            <li class="nav-item mt-2">
                <a class="nav-link  {{ \Request::route()->getName() == 'employee' ? 'active' : '' }}"
                    href="{{ route('employee') }}">
                    <div
                        class="icon icon-shape icon-sm shadow border-radius-md  text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fa fa-users text-lg opacity-10" aria-hidden="true"></i>
                    </div>
                    <span
                        class="nav-link-text ms-1 h5 mb-0 {{ \Request::route()->getName() == 'employee' ? 'text-primary' : '' }}">INTERNAL EMPLOYEE 
                        </span>
                </a>
            </li>
            <li class="nav-item mt-2">
                <a class="nav-link  {{ \Request::route()->getName() == 'external/employee' ? 'active' : '' }}"
                    href="{{ route('external-employee') }}">
                    <div
                        class="icon icon-shape icon-sm shadow border-radius-md  text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fa fa-users text-lg opacity-10" aria-hidden="true"></i>
                    </div>
                    <span
                        class="nav-link-text ms-1 h5 mb-0 {{ \Request::route()->getName() == 'external/employee' ? 'text-primary' : '' }}">EXTERNAL EMPLOYEE 
                        </span>
                </a>
            </li>
            @endif
            @if ($user->type == 2)
           
        <li class="nav-item mt-4">
             @php
                 $firstName = auth()->user()->name;
             @endphp
               <a class="nav-link  {{ \Request::route()->getName() == 'home' ? 'active' : '' }}"
                    href="{{ route('home') }}">
                    <div
                        class="icon icon-shape icon-sm shadow border-radius-md  text-center me-2 d-flex align-items-center justify-content-center">
                     <i class="fas fa-user text-black text-xl"></i>



                    </div>
                    <span
                        class="nav-link-text ms-1 h5 mb-0 {{ \Request::route()->getName() == 'home' ? 'text-primary' : '' }}">
                        {{ $firstName }}</span>
                </a>
            </li>
            <li class="nav-item mt-4">
                <a class="nav-link  {{ \Request::route()->getName() == 'pending.orders.external' ? 'active' : '' }}"
                    href="{{ route('pending.orders.external') }}">
                    <div
                        class="icon icon-shape icon-sm shadow border-radius-md  text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-cart text-lg opacity-10" aria-hidden="true"></i>
                    </div>
                    <span
                        class="nav-link-text ms-1 h5 mb-0 {{ \Request::route()->getName() == 'pending.orders.external' ? 'text-primary' : '' }}">PENDING
                        ORDERS</span>
                </a>
            </li>
            @endif
            <li class="nav-item mt-2">
                <a class="nav-link" href="{{ route('logout') }}">
                    <div
                        class="icon icon-shape icon-sm shadow border-radius-md  text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-button-power text-lg"></i>
                        

                    </div>
                    <span class="nav-link-text ms-1 h5 mb-0">LOGOUT</span>
                </a>
            </li>
        </ul>
    </div>

</aside>
