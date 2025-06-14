<div class="container-fluid p-0">
   

    <div class="row">
        <div class="col-md-10 m-auto col-12">
            @if (session()->has('message'))
            <div class="alert alert-primary" role="alert">
                <strong class="text-white h5">{{ session('message') }}</strong>
            </div>
            @endif
            <div class="card">
                 @if ($errors->any())
                    <div class="mb-4 p-4 rounded-lg bg-red-100 border border-red-400 text-red-700">
                        <strong>Whoops! Something went wrong.</strong>
                        <ul class="mt-2 list-disc list-inside text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div class="card body p-4">
                    <form wire:submit.prevent="save">

                        <hr class="hr hr-blurry" />
                        <div class="row">

                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mb-0">
                                            <label for="exampleInputEmail1" class="form-label h5">ORDER NUMBER</label>
                                            <input type="text" class="form-control" id="exampleInputEmail1"
                                                aria-describedby="emailHelp" wire:model.blur="order_number">

                                            <div id="emailHelp" class="form-text text-danger mb-4">
                                                @error('order_number')
                                                <b> {{ $message }}</b>
                                                @enderror
                                            </div>

                                        </div>
                                    </div>
                                    <!-- Number of Garments -->
                                    <div class="col-md-12 mt-3">
                                        <div class="mb-0">
                                            <label for="number_of_garments" class="form-label h5">NUMBER OF GARMENTS</label>
                                            <input type="number" class="form-control" id="number_of_garments"
                                                wire:model.live="number_of_garments">
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
                                            <select wire:model.live="created_by" class="form-select form-select-lg"
                                                aria-label=".form-select-lg example" id="created_by">
                                                <option selected>SELECT EMPLOYEE</option>
                                                @foreach($employees as $employee)
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
                                </div>
                            </div>
                            <div class="col-md-6 mt-4">
                                <div class="form-check mb-3 d-flex align-items-center gap-2">
                                    <input class="" type="checkbox" id="fcustomCheck11" wire:model.live="need_sewing">
                                    <label class="custom-control-label h5 mt-2" for="fcustomCheck11">Needs Sewing</label>
                                    @if( $need_sewing)
                                        <button type="button" class="btn btn-sm btn-outline-primary ms-2"
                                                wire:click="openSplitModal('Sewing')">Assign</button>
                                    @endif
                                </div>
                                <div class="form-check mb-3 d-flex align-items-center gap-2">
                                    <input class="" type="checkbox" id="fcustomCheck12" wire:model.live="need_embroidery">
                                    <label class="custom-control-label h5 mt-2" for="fcustomCheck12">Needs Embroidery</label>
                                    @if( $need_embroidery)
                                        <button type="button" class="btn btn-sm btn-outline-primary ms-2"
                                                wire:click="openSplitModal('Embroidery')">Assign</button>
                                    @endif
                                </div>
                                <div class="form-check mb-3 d-flex align-items-center gap-2">
                                    <input class="" type="checkbox" id="fcustomCheck3" wire:model.live="need_imprinting">
                                    <label class="custom-control-label h5 mt-2" for="fcustomCheck3">Needs Imprinting</label>
                                    @if( $need_imprinting)
                                        <button type="button" class="btn btn-sm btn-outline-primary ms-2"
                                                wire:click="openSplitModal('Imprinting')">Assign</button>
                                    @endif
                                </div>
                            </div>

                        </div>
                        <hr>
                        <div class="row">
                        </div>
                        <div class="row">
                        </div>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </form>
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
                                    {{-- <select class="form-select" wire:model="splitEntries.{{ $index }}.employee_id">
                                        <option value="">Select Employee</option>
                                        @foreach($external_employees as $employee)
                                            @php
                                                $pending = $pendingOrdersPerEmployee[$employee->id] ?? 0;
                                            @endphp
                                            <option value="{{ $employee->id }}">
                                                {{ $employee->first_name }} {{ $employee->last_name }}
                                                @if($pending > 0)
                                                    (Pending Orders: {{ $pending }}) 
                                                @endif
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
                                        wire:model="splitEntries.{{ $index }}.quantity"
                                        @if($isQuantityFromPattern) disabled @endif>

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
