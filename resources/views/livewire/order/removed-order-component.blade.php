<div class="container-fluid p-0">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="row">
                        <div class="col-md-3">
                            <input wire:model.live="search" type="text" class="form-control mb-3 mb-md-0" placeholder="Search...">
                        </div>
                        <div class="col-md-3">
                            <select class="form-select form-select-lg" wire:model.live="location">
                                <option value="">Select Location</option>
                                <option value="Sewing">Sewing</option>
                                <option value="Embroidery">Embroidery</option>
                                <option value="Imprinting">Imprinting</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0 normal-table">
                        <table class="table align-items-center mb-0  normal table-show blue" wire:poll.60s>
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        <span class="sort-icon cursor"
                                            wire:click="sortData('created_at', 'asc')">⇅</span>
                                        Date
                                        <span class="sort-icon cursor"
                                            wire:click="sortData('created_at', 'desc')">⇅</span>
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        <span class="sort-icon cursor"
                                            wire:click="sortData('order_number', 'asc')">⇅</span>
                                        Order #
                                        <span class="sort-icon cursor"
                                            wire:click="sortData('order_number', 'desc')">⇅</span>
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        <span class="sort-icon cursor"
                                            wire:click="sortData('current_location', 'asc')">⇅</span>
                                        Location
                                        <span class="sort-icon cursor"
                                            wire:click="sortData('current_location', 'desc')">⇅</span>
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">
                                        Sewing
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">
                                        Embroidery
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">
                                        Imprinting
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orders as $order)
                                <tr>
                                    <td>
                                        <h6>{{ date('m-d-Y', strtotime($order->created_at)) }}</h6>
                                    </td>
                                    <td>
                                        <h6>{{ $order->order_number }}</h6>
                                    </td>
                                    <td>
                                        <select id="{{ $order->id }}"
                                            onchange="onSelectChange({{ $order->id }}, {{ $order }})"
                                            class="form-select form-select-lg">
                                            @if($order->need_sewing == 2 || $order->need_sewing == 1)
                                            <option value="Sewing"
                                                {{ $order->current_location == "Sewing" ? 'selected' : '' }}>Sewing
                                            </option>
                                            @endif
                                            @if($order->need_embroidery == 2 || $order->need_embroidery == 1)
                                            <option value="Embroidery"
                                                {{ $order->current_location == "Embroidery" ? 'selected' : '' }}>
                                                Embroidery</option>
                                            @endif
                                            @if($order->need_imprinting ==2 || $order->need_imprinting ==1)
                                            <option value="Imprinting"
                                                {{ $order->current_location == "Imprinting" ? 'selected' : '' }}>
                                                Imprinting</option>
                                            @endif
                                        </select>
                                    </td>
                                    <td class="align-middle text-center text-sm">
                                        <input type="checkbox" id="cbox{{ $order->id }}"
                                            
                                           disabled
                                        {{ $order->need_sewing == 1 ? 'checked' : '' }}>
                                    </td>
                                    <td class="align-middle text-center text-sm">
                                        <input type="checkbox" id="emb{{ $order->id }}"
                                           
                                        disabled
                                        {{ $order->need_embroidery == 1 ?  'checked' : '' }}>
                                    </td>
                                    <td class="align-middle text-center text-sm">
                                        <input type="checkbox" id="imp{{ $order->id }}"
                                           
                                        disabled
                                        {{ $order->need_imprinting == 1 ?  'checked' : '' }}>
                                    </td>
                                    <td class="align-middle text-center text-sm">
                                        <a class="btn btn-info" href="{{ route('order.edit', ['orderId' => $order->id]) }}">
                                            <i class="fa fa-eye text-lg opacity-10" aria-hidden="true"></i>
                                        </a>

                                       
                                        
                                    </td>


                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="mt-3 d-flex justify-content-center">
                            {{ $orders->links() }}
                           </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <style>
        .form-control.mb-3{
            height: 48px;
        }
    </style>
</div>
