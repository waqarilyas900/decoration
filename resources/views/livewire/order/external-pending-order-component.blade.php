<div class="container-fluid p-0">
    <div class="row">
        <style>
            .checkbox-highlight {
            border: 2px solid #28a745 !important;
            border-radius: 3px;
            box-shadow: 0 0 5px rgba(40, 167, 69, 0.3);
        }

        /* Optional: Add a subtle glow effect on hover */
        .checkbox-highlight:hover {
            box-shadow: 0 0 8px rgba(40, 167, 69, 0.5);
        }
        </style>
        @if (session()->has('message'))
            <div class="alert alert-primary" role="alert">
                <strong class="text-white h5">{{ session('message') }}</strong>
            </div>
            @endif
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="row">
                        <div class="col-md-3">
                            <input wire:model.live="search" type="text" class="form-control mb-3 mb-md-0" placeholder="Search...">
                        </div>
                        <div class="col-md-3">
                            <select class="form-select form-select-lg mb-3 mb-md-0" wire:model.live="locationFilter">
                                <option value="">Select Location</option>
                                <option value="Sewing">Sewing</option>
                                <option value="Embroidery">Embroidery</option>
                                <option value="Imprinting">Imprinting</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body px-0 pt-0 pb-2 mt-3">
                    <div class="table-responsive p-0 normal-table">
                        <table class="table table-striped align-items-center mb-0 border pending-order normal table-show blue" wire:poll.60s  id="table" data-show-columns="true" >
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7" >
                                        <span class="sort-icon cursor"
                                            wire:click="sortData('created_at', 'asc')">⇅</span>
                                        Date
                                        <span class="sort-icon cursor"
                                            wire:click="sortData('created_at', 'desc')">⇅</span>
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7" width="170">
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
                                    {{-- <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">
                                        ETA
                                    </th> --}}
                                     <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">
                                        Expected Delivery Date
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orders as $order)
                                    @php
                                        $employeeId = auth()->user()->employee_id;
                                        $userDept = auth()->user()->employee?->department;

                                        // Current user's assignments, section-keyed
                                        $assignments = $order->assignments->where('employee_id', $employeeId)->keyBy('section');

                                       $sewingProgress = isset($assignments['Sewing']) 
                                            ? $assignments['Sewing']->is_progress 
                                            : ($order->sewing_progress == 1);

                                        $sewingComplete = isset($assignments['Sewing']) 
                                            ? $assignments['Sewing']->is_complete 
                                            : ($order->need_sewing == 1);

                                        $embroideryProgress = isset($assignments['Embroidery']) 
                                            ? $assignments['Embroidery']->is_progress 
                                            : ($order->embroidery_progress == 1);

                                        $embroideryComplete = isset($assignments['Embroidery']) 
                                            ? $assignments['Embroidery']->is_complete 
                                            : ($order->need_embroidery == 1);

                                        $imprintingProgress = isset($assignments['Imprinting']) 
                                            ? $assignments['Imprinting']->is_progress 
                                            : ($order->imprinting_progress == 1);

                                        $imprintingComplete = isset($assignments['Imprinting']) 
                                            ? $assignments['Imprinting']->is_complete 
                                            : ($order->need_imprinting == 1);


                                        $highlightIfOtherNeed = function ($section) use ($order, $userDept) {
                                            $needField = 'need_' . strtolower($section);
                                            return $order->$needField && $section !== $userDept ? 'background-color: #d4edda;' : '';
                                        };
                                    @endphp


                                    <tr @if($order->is_priority) style="background-color: #f8d7da;" @endif>
                                        <!-- Date -->
                                        <td><h6>{{ date('m-d-Y', strtotime($order->created_at)) }}</h6></td>

                                        <!-- Order Number -->
                                        <td><h6>{{ $order->order_number }}</h6></td>

                                        <!-- Current Location -->
                                        <td>
                                            <select class="form-select form-select-lg"
                                                    wire:model.live="orderLocations.{{ $order->id }}">
                                                @foreach($order->assignments->pluck('section')->unique() as $section)
                                                    <option value="{{ $section }}">
                                                        {{ $section }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>




                                        <!-- Sewing -->
                                        <td class="align-middle text-center text-sm" style="border-right: 2px solid #e9ecef; border-left: 2px solid #e9ecef; {{ $highlightIfOtherNeed('Sewing') }}">
                                            <div class="d-flex justify-content-center gap-2">
                                                <div>
                                                    <label class="d-block">In&#8209;Progress</label>
                                                    <input type="checkbox"
                                                        wire:click="confirmStageUpdate({{ $order->id }}, 'Sewing', 'progress')"
                                                        @disabled(!isset($assignments['Sewing']))
                                                        {{ $sewingProgress ? 'checked' : '' }}>
                                                </div>
                                                <div>
                                                    <label class="d-block">Complete</label>
                                                    <input type="checkbox"
                                                        wire:click="confirmStageUpdate({{ $order->id }}, 'Sewing', 'complete')"
                                                        @disabled(!isset($assignments['Sewing']) || !$sewingProgress)
                                                        {{ $sewingComplete ? 'checked' : '' }}>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Embroidery -->
                                        <td class="align-middle text-center text-sm" style="border-right: 2px solid #e9ecef; {{ $highlightIfOtherNeed('Embroidery') }}">
                                            <div class="d-flex justify-content-center gap-2">
                                                <div>
                                                    <label class="d-block">In&#8209;Progress</label>
                                                    <input type="checkbox"
                                                        wire:click="confirmStageUpdate({{ $order->id }}, 'Embroidery', 'progress')"
                                                        @disabled(!isset($assignments['Embroidery']))
                                                        {{ $embroideryProgress ? 'checked' : '' }}>
                                                </div>
                                                <div>
                                                    <label class="d-block">Complete</label>
                                                    <input type="checkbox"
                                                        wire:click="confirmStageUpdate({{ $order->id }}, 'Embroidery', 'complete')"
                                                        @disabled(!isset($assignments['Embroidery']) || !$embroideryProgress)
                                                        {{ $embroideryComplete ? 'checked' : '' }}>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Imprinting -->
                                        <td class="align-middle text-center text-sm" style="border-right: 2px solid #e9ecef; {{ $highlightIfOtherNeed('Imprinting') }}">
                                            <div class="d-flex justify-content-center gap-2">
                                                <div>
                                                    <label class="d-block">In&#8209;Progress</label>
                                                    <input type="checkbox"
                                                        wire:click="confirmStageUpdate({{ $order->id }}, 'Imprinting', 'progress')"
                                                        @disabled(!isset($assignments['Imprinting']))
                                                        {{ $imprintingProgress ? 'checked' : '' }}>
                                                </div>
                                                <div>
                                                    <label class="d-block">Complete</label>
                                                    <input type="checkbox"
                                                        wire:click="confirmStageUpdate({{ $order->id }}, 'Imprinting', 'complete')"
                                                        @disabled(!isset($assignments['Imprinting']) || !$imprintingProgress)
                                                        {{ $imprintingComplete ? 'checked' : '' }}>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Delivery Date/Time -->
                                        <td>
                                            @if(!empty($order->eta_data))
                                                {{ \Carbon\Carbon::parse($order->eta_data['expected_delivery'])->format('M d, Y h:i A') }}
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>

                                        <!-- View Button -->
                                        <td class="align-middle text-center text-sm">
                                            <a href="{{ route('edit.orders.external', ['orderId' => $order->id]) }}">
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
    @if($confirmingStageUpdate)
        <div class="modal fade show d-block" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content p-4">
                    <h5 class="modal-title mb-3">Confirm {{ ucfirst($confirmingType) }}</h5>
                    <p>Are you sure you want to mark <strong>{{ $confirmingStage }}</strong> as <strong>{{ ucfirst($confirmingType) }}</strong>?</p>
                    
                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <button class="btn btn-secondary" wire:click="$set('confirmingStageUpdate', false)">Cancel</button>
                        <button class="btn btn-primary" wire:click="performStageUpdate">Yes, Confirm</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

