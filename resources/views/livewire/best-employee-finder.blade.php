<div class="container-fluid">
    <div class="row">
        <div class="col-md-10 m-auto col-12">
            {{-- Session Message --}}
            @if (session()->has('message'))
                <div class="alert alert-primary" role="alert">
                    <strong class="text-white h5">{{ session('message') }}</strong>
                </div>
            @endif

            {{-- Error Message --}}
            @if($errorMessage)
                <div class="alert alert-danger mt-3">
                    <strong>{{ $errorMessage }}</strong>
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

                <div class="card-body p-4">
                    {{-- FIRST DIV: Form (Left) + Final ETA (Right) --}}
                    <div class="row align-items-start">
                        {{-- Form Section --}}
                        <div class="col-md-8">
                            <form wire:submit.prevent="generate">
                                <div class="row">
                                    {{-- Quantity --}}
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label h5">QUANTITY</label>
                                            <input type="number" class="form-control" wire:model="quantity">
                                            @error('quantity')
                                                <div class="form-text text-danger"><b>{{ $message }}</b></div>
                                            @enderror
                                        </div>
                                    </div>

                                    {{-- Select Operations --}}
                                    <div class="col-md-6">
                                        <label class="form-label h5 d-block">SELECT TYPES</label>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" value="Sewing" id="need_sewing" wire:model="selectedSections">
                                            <label class="form-check-label h5" for="need_sewing">Sewing</label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" value="Embroidery" id="need_embroidery" wire:model="selectedSections">
                                            <label class="form-check-label h5" for="need_embroidery">Embroidery</label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" value="Imprinting" id="need_imprinting" wire:model="selectedSections">
                                            <label class="form-check-label h5" for="need_imprinting">Imprinting</label>
                                        </div>
                                    </div>
                                </div>

                                <hr class="hr hr-blurry" />
                                <button type="submit" class="btn btn-primary">Find Employees</button>
                            </form>
                        </div>

                        {{-- Final ETA Section --}}
                        <div class="col-md-4">
                            @if($overallCompletion)
                                <div class="alert alert-info text-center">
                                    <strong>Order Completion:</strong><br>
                                    <b>{{ \Carbon\Carbon::parse($overallCompletion)->format('M d, Y g:i A') }}<b>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- SECOND DIV: Best Employees (No design change) --}}
                    @if($bestEmployees)
                        <div class="mt-4">
                            @php
                                $orderedSections = ['Sewing', 'Embroidery', 'Imprinting'];
                            @endphp

                            @foreach($orderedSections as $section)
                                @if(isset($bestEmployees[$section]))
                                    @php
                                        $info = $bestEmployees[$section];
                                    @endphp
                                    <div class="alert alert-success">
                                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center">
                                            <div class="mb-3 mb-sm-0">
                                                <div class="h6 mb-1 mb-sm-0 d-block d-sm-inline">
                                                    <strong>{{ strtoupper($section) }}</strong>
                                                </div>
                                                <div class="d-block d-sm-inline">
                                                    <span class="d-none d-sm-inline"> — </span>
                                                    <span class="text-break">{{ $info['employee'] }}</span>
                                                </div>
                                            </div>
                                            <div class="d-flex flex-column w-100 w-sm-auto" style="min-width: fit-content;">
                                                <div class="badge bg-secondary px-2 py-1 mb-2 mb-sm-1">
                                                    <small class="opacity-75">Previous ETA:</small><br class="d-sm-none">
                                                    {{ !empty($info['previous_eta']) && $info['previous_eta'] !== 'N/A' 
                                                        ? \Carbon\Carbon::parse($info['previous_eta'])->format('M d, Y g:i A') 
                                                        : 'N/A' }}
                                                </div>

                                                <div class="badge bg-dark px-2 py-1">
                                                    <small class="opacity-75">New ETA:</small><br class="d-sm-none">
                                                    {{ !empty($info['new_eta']) && $info['new_eta'] !== 'N/A' 
                                                        ? \Carbon\Carbon::parse($info['new_eta'])->format('M d, Y g:i A') 
                                                        : 'N/A' }}
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif

                </div>

                {{-- 🔹 Debug All Results --}}
                    {{-- @if($results)
                        <div class="card mt-4">
                            <div class="card-body">
                                <h5>All Employee Free Times</h5>
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Employee</th>
                                            <th>Section</th>
                                            <th>Personal ETA</th>
                                            <th>Free At (Order ETA)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($results['employeeFreeTimes'] ?? [] as $empId => $data)
                                            <tr>
                                                <td>
                                                    {{ ($data['employee']->first_name ?? '') . ' ' . ($data['employee']->last_name ?? '') }}
                                                </td>

                                                <td>{{ $data['employee']->department ?? 'N/A' }}</td>

                                                <td>{{ $data['personal_eta']?->format('M d, Y H:i') ?? 'N/A' }}</td>

                                            
                                                <td>{{ $data['free_at']?->format('M d, Y H:i') ?? 'N/A' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>


                                </table>
                            </div>
                        </div>
                    @endif --}}
            </div>
        </div>
    </div>
</div>
