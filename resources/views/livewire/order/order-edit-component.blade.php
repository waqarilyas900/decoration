<div class="container-fluid py-4 px-0">
    <div class="row">
        <div class="col-12 col-md-10 m-auto">
            @if (session()->has('message'))
            <div class="alert alert-primary" role="alert">
                <strong class="text-white h5">{{ session('message') }}</strong>
            </div>
            @endif
            <div class="card">
                
                <div class="card body p-4">
                    <div>
                        <hr class="hr hr-blurry" />
                        <div class="row">
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mb-0">
                                            <label for="exampleInputEmail1" class="form-label h5">ORDER NUMBER</label>
                                            <input disabled type="text" class="form-control" id="exampleInputEmail1"
                                                aria-describedby="emailHelp" wire:model.blur="order_number">
                                            <div id="emailHelp" class="form-text text-danger mb-4">
                                                @error('order_number')
                                                <b> {{ $message }}</b>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12 mt-3">
                                        <div class="mb-0">
                                            <label for="number_of_garments" class="form-label h5">NUMBER OF GARMENTS</label>
                                            <input type="number" class="form-control" id="number_of_garments"
                                                wire:model.blur="number_of_garments">
                                            @error('number_of_garments')
                                            <div class="form-text text-danger">
                                                <b>{{ $message }}</b>
                                            </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Priority Checkbox -->
                                    <div class="col-md-12 mt-3">
                                        <div class="form-check d-flex align-items-center gap-2">
                                            <input class="form-check-input" type="checkbox" id="priority"
                                                wire:model.live="is_priority">
                                            <label class="form-check-label h5 mt-2" for="priority">PRIORITY</label>
                                        </div>
                                    </div>
                                    <div class="col-md-12 mt-3">
                                        <label for="current_location" class="form-label h5">CURRENT LOCATION</label>
                                        <select wire:model.live="current_location" class="form-select form-select-lg"
                                            aria-label=".form-select-lg example" id="current_location">
                                            <option selected>SELECT CURRENT LOCATION</option>
                                            @if($need_sewing)
                                            <option value="Sewing">Sewing</option>
                                            @endif
                                            @if($need_embroidery)
                                            <option value="Embroidery">Embroidery</option>
                                            @endif
                                            @if($need_imprinting)
                                            <option value="Imprinting">Imprinting</option>
                                            @endif
                                        </select>
                                        <div class="form-text text-danger mb-4">
                                            @error('current_location')
                                            <b> {{ $message }}</b>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-12 mt-3">
                                        <div class="mb-3">
                                            <label for="created_by" class="form-label h5">CREATED BY</label>
                                            <select disabled wire:model.live="created_by"
                                                class="form-select form-select-lg" aria-label=".form-select-lg example"
                                                id="created_by">
                                                <option selected>SELECT EMPLOYEE</option>
                                                @foreach($employeesCreated as $employee)
                                                <option value="{{ $employee->id }}">
                                                    {{ $employee->first_name . ' ' . $employee->last_name }}</option>
                                                @endforeach
                                            </select>
                                            @error('created_by')
                                            <div id="created_by" class="form-text text-danger">
                                                <b> {{ $message }}</b>
                                            </div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-12 mt-3">
                                        <div class="mb-3">
                                            <label for="updated_by" class="form-label h5">UPDATED BY</label>
                                            <select wire:model.live="updated_by" class="form-select form-select-lg"
                                                aria-label=".form-select-lg example" id="updated_by">
                                                <option selected>SELECT EMPLOYEE</option>
                                                @foreach($employees as $employee)
                                                <option value="{{ $employee->id }}">
                                                    {{ $employee->first_name . ' ' . $employee->last_name }}</option>
                                                @endforeach
                                            </select>
                                            @error('updated_by')
                                            <div id="updated_by" class="form-text text-danger">
                                                <b> {{ $message }}</b>
                                            </div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mt-4">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-check mb-3 d-flex align-item-center gap-3">
                                            <input class="" type="checkbox" id="fcustomCheck11"
                                                wire:model.live="need_sewing">
                                            <label class="custom-control-label h4 m-0" for="fcustomCheck11">Needs
                                                Sewing</label>
                                                 @if( $need_sewing)
                                        <button type="button" class="btn btn-sm btn-outline-primary ms-2"
                                                wire:click="openSplitModal('Sewing')">Assign</button>
                                    @endif
                                        </div>
                                        <div class="form-check mb-3 d-flex align-item-center gap-3">
                                            <input class="" type="checkbox" id="fcustomCheck12"
                                                wire:model.live="need_embroidery">
                                            <label class="custom-control-label h4 m-0" for="fcustomCheck12">Needs
                                                Embroidery</label>
                                                  @if( $need_embroidery)
                                        <button type="button" class="btn btn-sm btn-outline-primary ms-2"
                                                wire:click="openSplitModal('Embroidery')">Assign</button>
                                    @endif
                                        </div>
                                        <div class="form-check mb-3 d-flex align-item-center gap-3">
                                            <input class="" type="checkbox" id="fcustomCheck3"
                                                wire:model.live="need_imprinting">
                                            <label class="custom-control-label h4 m-0" for="fcustomCheck3"> Needs
                                                Imprinting</label>
                                                 @if( $need_imprinting)
                                        <button type="button" class="btn btn-sm btn-outline-primary ms-2"
                                                wire:click="openSplitModal('Imprinting')">Assign</button>
                                    @endif
                                        </div>
                                    </div>
                                     @if ($errors->any())
                                        <div class="mb-4 p-4 rounded-lg bg-red-100 border border-red-400 text-red-700">
                                            {{-- <strong>Whoops! Something went wrong.</strong> --}}
                                            <ul class="mt-2 list-disc list-inside text-sm">
                                                @foreach ($errors->all() as $error)
                                                    <li style="color: red">{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                    <div class="col-md-12 mt-3">
                                        <div class="table-responsive p-0">
                                            <table class="table align-items-center mb-0">
                                                <thead>
                                                    <tr>
                                                        <th colspan="3">Time Spent</th>
                                                    </tr>
                                                    <tr>
                                                        <th
                                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                            Sewing
                                                        </th>
                                                        <th
                                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                                            Embroidery</th>
                                                        <th
                                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                                            Imprinting</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                 
                                                    <tr>
                                                        <td class="text-center">
                                                            @if(!$need_sewing) 
                                                                <i class="fa fa-close text-danger"></i>
                                                            @endif
                                                            @php 
                                                            $sewingStart = $order->track()->where('type', 1)->where('status', 0)->first();
                                                            $sewingEnd = $order->track()->where('type', 1)->where('status', 1)->first();
                                                            @endphp
                                                            @php
                                                                if($sewingStart && $sewingStart->created_at && $sewingEnd && $sewingEnd->created_at) { 
                                                                    $diff = \Carbon\Carbon::parse($sewingStart->created_at)->diff($sewingEnd->created_at);
                                                                    $hours = $diff->h > 0 ? $diff->h . ' hours' : '';
                                                                    $minutes = $diff->i > 0 ? $diff->i . ' minutes' : '';
                                                                    $seconds = $diff->s > 0 ? $diff->s . ' seconds' : '';     
                                                                }
                                                               
                                                            @endphp
                                                         
                                                            @if($sewingStart && $sewingEnd && $need_sewing)
                                                           
                                                                {{ trim($hours . ' ' . $minutes . ' ' . $seconds) }}

                                                            @endif
                                                        </td>
                                                        <td class="text-center">
                                                            @if(!$need_embroidery) 
                                                                <i class="fa fa-close text-danger"></i>
                                                            @endif
                                                            @php 
                                                                $embStart = $order->track()->where('type', 2)->where('status', 0)->first();
                                                                $embEnd = $order->track()->where('type', 2)->where('status', 1)->first();
                                                                @endphp
                                                                @php
                                                                    if($embStart && $embStart->created_at && $embEnd && $embEnd->created_at) {
                                                                        $diff = \Carbon\Carbon::parse($embStart->created_at)->diff($embEnd->created_at);
                                                                        $hours = $diff->h > 0 ? $diff->h . ' hours' : '';
                                                                        $minutes = $diff->i > 0 ? $diff->i . ' minutes' : '';
                                                                        $seconds = $diff->s > 0 ? $diff->s . ' seconds' : '';
                                                                    }
                                                                       
                                                                @endphp
                                                                @if($embStart && $embEnd && $need_embroidery)
                                                                {{ \Carbon\Carbon::parse($embStart->created_at)->diff($embEnd->created_at)->format('%h hours %i minutes %s seconds') }}

                                                                @endif
                                                        </td>
                                                        <td class="text-center">
                                                            @if(!$need_imprinting) 
                                                                <i class="fa fa-close text-danger"></i>
                                                                @endif
                                                                @php 
                                                                $impStart = $order->track()->where('type', 3)->where('status', 0)->first();
                                                                $impEnd = $order->track()->where('type', 3)->where('status', 1)->first();
                                                                @endphp
                                                                    @php
                                                                    if($impStart  && $impStart->created_at && $impEnd  && $impEnd->created_at) {
                                                                        $diff = \Carbon\Carbon::parse($impStart->created_at)->diff($impEnd->created_at);
                                                                        $hours = $diff->h > 0 ? $diff->h . ' hours' : '';
                                                                        $minutes = $diff->i > 0 ? $diff->i . ' minutes' : '';
                                                                        $seconds = $diff->s > 0 ? $diff->s . ' seconds' : '';
                                                                    }
                                                                   
                                                                @endphp
                                                                @if($impStart && $impEnd && $need_imprinting)
                                                                {{ trim($hours . ' ' . $minutes . ' ' . $seconds) }}
                                                                @endif
                                                           
                                                           
                                                        </td>
                                                    </tr>
                                                   
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                        </div>
                        <div class="row">
                        </div>
                        @if(!$confrmView)
                        <button type="submit" class="btn btn-primary btn-cutom"
                            wire:click="confirmation('update')">Update</button>
                        @endif
                        @if($confrmView)
                        <div class="alert alert-success" role="alert">
                            <b class="text-white"> Are you sure want to continue</b>
                            <button type="submit" class="btn btn-primary" wire:click="save">Yes</button>
                            <button type="submit" class="btn btn-danger" wire:click="confirmation('no')">No</button>
                        </div>
                        @endif
                    </div>
                </div>
                <div class="table-responsive p-0">
                    {{-- <table class="table align-items-center mb-0">
                        <thead>
                            <tr>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Title
                                </th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                    Updated By</th>
                                <th
                                    class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                    Updated Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->logs as $item)
                            <tr>
                                <td>
                                    {{ $item->title }}
                                </td>
                                <td>
                                    @if($item->user)
                                    {{ $item->user->first_name. ' '. $item->user->last_name }}
                                    @endif
                                </td>
                                <td class="align-middle text-center text-sm">
                                    {{ date('m-d-Y h:i:A', strtotime($item->created_at)) }}
                                </td>

                            </tr>
                            @endforeach
                        </tbody>
                    </table> --}}
                  <h5>📋 Order & Assignment Logs</h5>
<table class="table table-sm table-bordered">
    <thead>
        <tr>
            <th>Type</th>
            <th>Title</th>
            <th>User</th>
            <th>Garments</th>
            <th>Time</th>
        </tr>
    </thead>
    <tbody>
        {{-- 📝 Order Logs --}}
        @foreach($order->logs as $item)
            <tr>
                <td>📝 Order</td>
                <td>{{ $item->title }}</td>
                <td>{{ $item->user?->first_name }} {{ $item->user?->last_name }}</td>
                <td>-</td>
                <td class="text-center">{{ $item->created_at->format('d-m-Y h:i A') }}</td>
            </tr>
        @endforeach

        {{-- 👷 Assignment Logs --}}
        @foreach($order->assignmentLogs as $log)
            <tr>
                <td>👷 Assignment</td>
                <td>{{ $log->title }}</td>
                <td>{{ $log->employee?->first_name }} {{ $log->employee?->last_name }}</td>
                <td>
                   {{ $log->garments_assigned }}
                </td>
                <td class="text-center">{{ $log->created_at->format('d-m-Y h:i A') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>


                </div>
            </div>
        </div>
    </div>
    @if($showSplitModal)
        <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5);" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Assignee {{ ucfirst($splitSection) }} Work</h5>
                        <button type="button" class="btn-close" wire:click="$set('showSplitModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        @foreach($splitEntries as $index => $entry)
                            <div class="row mb-2 align-items-center">
                                <div class="col-md-6">
                                    {{-- <select class="form-select" wire:model="splitEntries.{{ $index }}.employee_id">
                                        <option value="">Select Employee</option>
                                        @foreach($external_employees as $employee)
                                            <option value="{{ $employee->id }}">
                                                {{ $employee->first_name }} {{ $employee->last_name }}
                                            </option>
                                        @endforeach
                                    </select> --}}
                                     <select class="form-select" wire:model="splitEntries.{{ $index }}.employee_id">
                                        <option value="">Select Employee</option>
                                        @foreach($external_employees as $employee)
                                            @php
                                                $pending = $pendingOrdersPerEmployee[$employee->id] ?? 0;

                                                // Check if employee is already selected in another split entry
                                                $isAlreadySelected = collect($splitEntries)
                                                    ->where('employee_id', $employee->id)
                                                    ->keys()
                                                    ->filter(fn($i) => $i !== $index) // exclude current index
                                                    ->isNotEmpty();
                                            @endphp

                                            @if(!$isAlreadySelected)
                                                <option value="{{ $employee->id }}">
                                                    {{ $employee->first_name }} {{ $employee->last_name }}
                                                    @if($pending > 0)
                                                        (Pending Orders: {{ $pending }})
                                                    @endif
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <input type="number" class="form-control"
                                        placeholder="Garments"
                                        wire:model="splitEntries.{{ $index }}.quantity">
                                </div>
                                <div class="col-md-2">
                                    <button class="btn btn-danger" wire:click="removeSplitEntry({{ $index }})">Remove</button>
                                </div>
                            </div>
                        @endforeach

                        <button class="btn btn-sm btn-outline-secondary mt-3" wire:click="addSplitEntry">
                            + Add Assignee
                        </button>

                        @error('splitEntries')
                        <div class="text-danger mt-2"><strong>{{ $message }}</strong></div>
                        @enderror
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" wire:click="$set('showSplitModal', false)">Cancel</button>
                        <button class="btn btn-primary" wire:click="saveSplitAssignments">Assign</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>