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
                            <select class="form-select form-select-lg mb-3 mb-md-0" wire:model.live="location">
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
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">
                                        Expected Delivery
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orders as $order)
                                <tr @if($order->is_priority) style="background-color: #f8d7da;" @endif>
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
                                            
                                            <option value="Sewing" {{ $order->current_location == "Sewing" ? 'selected' : '' }}>
                                                Sewing 
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
                                        <td class="align-middle text-center text-sm" style="border-right: 2px solid #e9ecef; border-left: 2px solid #e9ecef;">
                                        <div class="d-flex justify-content-center gap-2">
                                                <div>
                                                   <label class="d-block">In&#8209;Progress</label>
                                                        <input type="checkbox" id="inprogres{{ $order->id }}"
                                                        onchange="inProgress('inprogres{{ $order->id }}', {{ $order }})"
                                                        @disabled(!$order->need_sewing || $order->need_sewing == 1)
                                                    {{ $order->sewing_progress == 1 ? 'checked' : '' }}>
                                                </div>
                                                <div>
                                                    <label class="d-block">Complete</label>
                                                        <input type="checkbox" id="cbox{{ $order->id }}"
                                                        onchange="onCbChange('cbox{{ $order->id }}', {{ $order }})"
                                                        @disabled(!$order->need_sewing || !$order->sewing_progress)
                                                    {{ $order->need_sewing == 1 ? 'checked' : '' }}>

                                                </div>
                                        </div>
                                        </td>
                                    <td class="align-middle text-center text-sm" style="border-right: 2px solid #e9ecef;">

                                        <div class="d-flex justify-content-center gap-2">
                                            <div>
                                                <label class="d-block">In&#8209;Progress</label>
                                                    <input type="checkbox" id="inprogresEmb{{ $order->id }}"
                                                    onchange="inProgressEmb('inprogresEmb{{ $order->id }}', {{ $order }})"
                                                    @disabled(!$order->need_embroidery || $order->need_embroidery == 1)
                                                {{ $order->embroidery_progress == 1 ? 'checked' : '' }}>
                                            </div>
                                            <div>
                                                <label class="d-block">Complete</label>
                                                    <input type="checkbox" id="emb{{ $order->id }}"
                                                    onchange="onEmbChange('emb{{ $order->id }}', {{ $order }})"
                                                    @disabled(!$order->need_embroidery || !$order->embroidery_progress)
                                                {{ $order->need_embroidery == 1 ? 'checked' : '' }}>
                                            </div>
                                       </div>
                                        {{-- <input type="checkbox" id="emb{{ $order->id }}"
                                            onchange="onEmbChange('emb{{ $order->id }}', {{ $order }})"
                                            @disabled(!$order->need_embroidery)
                                        {{ $order->need_embroidery == 1 ?  'checked' : '' }}> --}}
                                    </td>
                                    <td class="align-middle text-center text-sm" style="border-right: 2px solid #e9ecef;">
                                        <div class="d-flex justify-content-center gap-2">
                                            <div>
                                                <label class="d-block">In&#8209;Progress</label>
                                                    <input type="checkbox" id="inprogresImp{{ $order->id }}"
                                                    onchange="inProgressImp('inprogresImp{{ $order->id }}', {{ $order }})"
                                                    @disabled(!$order->need_imprinting || $order->need_imprinting == 1)
                                                {{ $order->imprinting_progress == 1 ? 'checked' : '' }}>
                                            </div>
                                            <div>
                                                <label class="d-block">Complete</label>
                                                    <input type="checkbox" id="imp{{ $order->id }}"
                                                    onchange="onImpChange('imp{{ $order->id }}', {{ $order }})"
                                                    @disabled(!$order->need_imprinting || !$order->imprinting_progress)
                                                {{ $order->need_imprinting == 1 ? 'checked' : '' }}>
                                            </div>
                                       </div>
                                        {{-- <input type="checkbox" id="imp{{ $order->id }}"
                                            onchange="onImpChange('imp{{ $order->id }}', {{ $order }})"
                                            @disabled(!$order->need_imprinting)
                                        {{ $order->need_imprinting == 1 ?  'checked' : '' }}> --}}
                                    </td>
                                    <td class="align-middle text-center text-sm">
                                        @if($order->expected_delivery)
                                           {{ \Carbon\Carbon::parse($order->expected_delivery)->format('M d, Y h:i A') }}
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>


                                    <td class="align-middle text-center text-sm">
                                        <a href="{{ route('order.edit', ['orderId' => $order->id]) }}">
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
    <script>
        function onCbChange(cb, order) {
                var checkbox = document.getElementById(cb);
                if (checkbox.checked) {
                    var msg = 'complete'
                } else {
                    var msg = 'unchecked'
                }
                var b = document.getElementById(cb).checked;
                var getorder = order;
                Swal.fire({
                    title: 'Change?',
                    text: 'You are about to mark sewing as ' + msg + ' on order # ' + getorder.order_number + '.',
                    icon: 'warning',
                    input: 'select',
                    inputOptions: @json($employees),
                    inputPlaceholder: 'Select employee',
                    inputValue: null, // Set the default value to null
                    showCancelButton: true,
                    confirmButtonText: 'Yes',
                    cancelButtonText: 'No',
                    preConfirm: (inputValue) => {
                        if (!inputValue) {
                            Swal.showValidationMessage("Field is required!");
                            return false; // Prevent closing the modal
                        }
                        @this.set('by_user', inputValue);
                    }
                }).then((result) => {
                    if (result.isConfirmed && result.value) {
                        document.getElementById(cb).checked = b;
                        if (b) {
                            @this.updateSweing('1', cb, result.value);
                        } else {
                            @this.updateSweing('0', cb, result.value);
                        }
                    } else {
                        document.getElementById(cb).checked = !b;
                    }
            });
        }
        ////
        function inProgress(cb, order) {
                var checkbox = document.getElementById(cb);
                if (checkbox.checked) {
                    var msg = 'in process'
                } else {
                    var msg = 'Unfinished'
                }
                var b = document.getElementById(cb).checked;
                var getorder = order;

                Swal.fire({
                    title: 'Change?',
                    text: 'You are about to mark sewing as ' + msg + ' on order # ' + getorder.order_number + '.',
                    icon: 'warning',
                    input: 'select',
                    inputOptions: @json($employees),
                    inputPlaceholder: 'Select employee',
                    inputValue: null,
                    showCancelButton: true,
                    confirmButtonText: 'Yes',
                    cancelButtonText: 'No',
                    preConfirm: (inputValue) => {
                        if (!inputValue) {
                            Swal.showValidationMessage("Field is required!");
                            return false;
                        }
                        @this.set('by_user', inputValue);
                    }
                }).then((result) => {
                    if (result.isConfirmed && result.value) {
                        document.getElementById(cb).checked = b;
                        if (b) {
                            @this.updateInprogress('1', cb, result.value);
                        } else {
                            @this.updateInprogress('0', cb, result.value);
                        }
                    } else {
                        document.getElementById(cb).checked = !b;
                    }
            });
        }
        //////
        function inProgressEmb(cb, order) {
                var checkbox = document.getElementById(cb);
                if (checkbox.checked) {
                    var msg = 'in process'
                } else {
                    var msg = 'Unfinished'
                }
                var b = document.getElementById(cb).checked;
                var getorder = order;

                Swal.fire({
                    title: 'Change?',
                    text: 'You are about to mark embroidery as ' + msg + ' on order # ' + getorder.order_number + '.',
                    icon: 'warning',
                    input: 'select',
                    inputOptions: @json($employees),
                    inputPlaceholder: 'Select employee',
                    inputValue: null,
                    showCancelButton: true,
                    confirmButtonText: 'Yes',
                    cancelButtonText: 'No',
                    preConfirm: (inputValue) => {
                        if (!inputValue) {
                            Swal.showValidationMessage("Field is required!");
                            return false;
                        }
                        @this.set('by_user', inputValue);
                    }
                }).then((result) => {
                    if (result.isConfirmed && result.value) {
                        document.getElementById(cb).checked = b;
                        if (b) {
                            @this.updateInprogressEmb('1', cb, result.value);
                        } else {
                            @this.updateInprogressEmb('0', cb, result.value);
                        }
                    } else {
                        document.getElementById(cb).checked = !b;
                    }
            });
        }
        /////////
        ////////
        function onEmbChange(cb, order) 
        {
            var checkbox = document.getElementById(cb);
            if (checkbox.checked) {
                var msg = 'complete'
            } else {
                var msg = 'unchecked'
            }
            var b = document.getElementById(cb).checked;
            var getorder = order;
            Swal.fire({
                title: 'Change?',
                text: 'You are about to mark Embroidery as ' + msg + ' on order # ' + getorder.order_number + '.',
                icon: 'warning',
                input: 'select',
                inputOptions: @json($employees),
                inputPlaceholder: 'Select employee',
                showCancelButton: true,
                confirmButtonText: 'Yes',
                cancelButtonText: 'No',
                preConfirm: (inputValue) => {
                    if (!inputValue) {
                        Swal.showValidationMessage("Field is required!");
                    }
                    @this.set('by_user', inputValue);
                }
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    document.getElementById(cb).checked = b;
                    if (b) {
                        @this.updateEmb('1', cb, result
                            .value) // Assuming you want to pass the input value as well
                    } else {
                        @this.updateEmb('0', cb, result
                            .value) // Assuming you want to pass the input value as well
                    }
                } else {
                    document.getElementById(cb).checked = !b;
                }
            });
        }

        function inProgressImp(cb, order) {
                var checkbox = document.getElementById(cb);
                if (checkbox.checked) {
                    var msg = 'in process'
                } else {
                    var msg = 'Unfinished'
                }
                var b = document.getElementById(cb).checked;
                var getorder = order;
                Swal.fire({
                    title: 'Change?',
                    text: 'You are about to mark Imprinting as ' + msg + ' on order # ' + getorder.order_number + '.',
                    icon: 'warning',
                    input: 'select',
                    inputOptions: @json($employees),
                    inputPlaceholder: 'Select employee',
                    inputValue: null,
                    showCancelButton: true,
                    confirmButtonText: 'Yes',
                    cancelButtonText: 'No',
                    preConfirm: (inputValue) => {
                        if (!inputValue) {
                            Swal.showValidationMessage("Field is required!");
                            return false;
                        }
                        @this.set('by_user', inputValue);
                    }
                }).then((result) => {
                    if (result.isConfirmed && result.value) {
                        document.getElementById(cb).checked = b;
                        if (b) {
                            @this.updateInprogressImp('1', cb, result.value);
                        } else {
                            @this.updateInprogressImp('0', cb, result.value);
                        }
                    } else {
                        document.getElementById(cb).checked = !b;
                    }
            });
        }

        function onImpChange(cb, order) 
        {
            var checkbox = document.getElementById(cb);
            if (checkbox.checked) {
                var msg = 'complete'
            } else {
                var msg = 'unchecked'
            }
            var b = document.getElementById(cb).checked;
            var getorder = order;
            Swal.fire({
                title: 'Change?',
                text: 'You are about to mark Imprinting as ' + msg + ' on order # ' + getorder.order_number + '.',
                icon: 'warning',
                input: 'select',
                inputOptions: @json($employees),
                inputPlaceholder: 'Select employee',
                showCancelButton: true,
                confirmButtonText: 'Yes',
                cancelButtonText: 'No',
                preConfirm: (inputValue) => {
                    if (!inputValue) {
                        Swal.showValidationMessage("Field is required!");
                    }
                    @this.set('by_user', inputValue);
                }
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    document.getElementById(cb).checked = b;
                    if (b) {
                        @this.updateImp('1', cb, result
                            .value) // Assuming you want to pass the input value as well
                    } else {
                        @this.updateImp('0', cb, result
                            .value) // Assuming you want to pass the input value as well
                    }
                } else {
                    document.getElementById(cb).checked = !b;
                }
            });
        }


        // function onSelectChange(selectId, order) {
        //     var order = order
        //     // console.log(order.current_location)
        //     var selectedValue = document.getElementById(selectId).value;
        //     var selectElement = document.getElementById(selectId);
        //     var selectedOption = selectElement.options[selectElement.selectedIndex];
        //     var selectedText = selectedOption.text;

        //     Swal.fire({
        //         title: 'Change?',
        //         text: "You are about to move order #" + order.order_number + " from " + order.current_location +
        //             " to " + selectedText + '.',
        //         icon: 'warning',
        //         input: 'select',
        //         inputOptions: @json($employees),
        //         inputPlaceholder: 'Select employee',
        //         showCancelButton: true,
        //         confirmButtonText: 'Yes',
        //         cancelButtonText: 'No',
        //         preConfirm: (inputValue) => {
        //             if (!inputValue) {
        //                 Swal.showValidationMessage("Field is required!");
        //             }
        //             @this.set('ready_by', inputValue);
        //         }
        //     }).then((result) => {
        //         if (result.isConfirmed && result.value) {
        //             if (selectedValue === 'Yes') {
        //                 @this.updateLocation(selectId, result.value, selectedText)
        //             } else {
        //                 @this.updateLocation(selectId, result.value, selectedText)
        //             }
        //         } else {
        //             document.getElementById(selectId).value = order.current_location;
        //         }
        //     });
        // }
        window.addEventListener('livewire:initialized', () => {
            let pendingOrderData = null; // temporary store for order info while we wait for employees

            Livewire.on('assigned-employees-loaded', ({ assigned }) => {
                if (!pendingOrderData) return;

                const { order, selectedText, selectId } = pendingOrderData;

                const assignedHtml = assigned.length > 0
                    ? '<br><strong>' + assigned.join('<br>') + '</strong>'
                    : '<br><em>No employees assigned.</em>';

                Swal.fire({
                    title: 'Confirm Handover',
                    html: `You are about to move order <strong>#${order.order_number}</strong> from <strong>${order.current_location}</strong> to <strong>${selectedText}</strong>.<br><br>Assigned Employees:${assignedHtml}`,
                    icon: 'warning',
                    input: 'select',
                    inputOptions: @json($employees),
                    inputPlaceholder: 'Select employee',
                    showCancelButton: true,
                    confirmButtonText: 'Yes',
                    cancelButtonText: 'No',
                    preConfirm: (inputValue) => {
                        if (!inputValue) {
                            Swal.showValidationMessage("Field is required!");
                        }
                        @this.set('ready_by', inputValue);
                    }
                }).then((result) => {
                    if (result.isConfirmed && result.value) {
                        @this.updateLocation(order.id, result.value, selectedText);
                    } else {
                        document.getElementById(selectId).value = order.current_location;
                    }

                    pendingOrderData = null; // reset after done
                });
            });

            window.onSelectChange = function(selectId, order) {
                const selectedValue = document.getElementById(selectId).value;
                const selectElement = document.getElementById(selectId);
                const selectedOption = selectElement.options[selectElement.selectedIndex];
                const selectedText = selectedOption.text;

                // Save current context
                pendingOrderData = { order, selectedText, selectId };

                // Fetch assigned employees first
                Livewire.dispatch('fetchAssignedEmployees', {
                    orderId: order.id,
                    section: selectedText,
                    currentLocation: order.current_location
                });
            };
        });

    </script>
<style>
    .form-control.mb-3{
        height: 48px;
    }
</style>


</div>

