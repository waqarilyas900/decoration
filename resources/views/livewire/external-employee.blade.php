<div class="container-fluid p-0">

    <div class="row">
        <div class="col-md-10 m-auto col-12">
            @if (session()->has('message'))
            <div class="alert alert-primary" role="alert">
                <strong class="text-white h5">{{ session('message') }}</strong>
            </div>
            @endif
            <div class="row">
                <div class="col-md-3">
                    <input wire:model.live="search" type="text" class="form-control mb-3 mb-md-0"
                        placeholder="Search...">
                </div>
                <div class="col-md-9" style="text-align:right;">
                    @if($type == 'view')
                    <button wire:click="addReset()" type="submit" class="btn btn-primary">Add Employee</button>
                    @else
                    <button wire:click="set('type', 'view')" type="submit" class="btn btn-primary">Employee
                        List</button>
                    @endif
                </div>

            </div>
            @if($type == 'view')
            <div class="row">
                <div class="col-12">
                    <div class="card mb-4">
                        <div class="card-header pb-0">

                        </div>
                        <div class="card-body px-0 pt-0 pb-2">
                            <div class="table-responsive p-0">
                                <table class="table align-items-center mb-0" wire:poll.60s>
                                    <thead>
                                        <tr>
                                            <th
                                                class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                ID
                                            </th>
                                            <th
                                                class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                First Name
                                            </th>
                                            <th
                                                class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                Last Name
                                            </th>
                                           

                                            <th
                                                class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($employees as $item)
                                        <tr>
                                            <td>
                                                {{ $item->id }}
                                            </td>
                                            <td>
                                                {{ $item->first_name }}
                                            </td>
                                            <td>
                                                {{ $item->last_name }}
                                            </td>
                                           
                                            <td>
                                                <button wire:click="edit({{ $item->id }})" class="btn btn-info"><i
                                                        class="fa fa-pencil"></i></button>
                                                        <button  onclick="deleteRecord('{{ $item->id }}')" class="btn btn-info"><i
                                                            class="fa fa-trash"></i></button>
                                            </td>



                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                {{ $employees->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @else
            <div class="card">
                <div class="card body p-4">

                    <form wire:submit.prevent="save">
                        <hr class="hr hr-blurry" />

                        <div class="row">
                            <!-- First Name -->
                            <div class="col-md-6">
                                <div class="mb-0">
                                    <label for="first_name" class="form-label h5">FIRST NAME</label>
                                    <input type="text" class="form-control" id="first_name" aria-describedby="first_name"
                                        wire:model="record.first_name">
                                    <div class="form-text text-danger mb-4">
                                        @error('record.first_name')
                                            <b>{{ $message }}</b>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Last Name -->
                            <div class="col-md-6">
                                <div class="mb-0">
                                    <label for="last_name" class="form-label h5">LAST NAME</label>
                                    <input type="text" class="form-control" id="last_name" aria-describedby="last_name"
                                        wire:model="record.last_name">
                                    <div class="form-text text-danger mb-4">
                                        @error('record.last_name')
                                            <b>{{ $message }}</b>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- PIN -->
                           <div class="col-md-6">
                                <div class="mb-0">
                                    <label for="user_password" class="form-label h5">PIN</label>
                                    <input type="password" id="user_password" class="form-control" wire:model="user_password" placeholder="Enter password">
                                    <div class="form-text text-danger mb-4">
                                        @error('user_password') <b>{{ $message }}</b> @enderror
                                    </div>
                                </div>
                            </div>
                            <!-- Department -->
                            <div class="col-md-6">
                                <div class="mb-0">
                                    <label for="department" class="form-label h5">DEPARTMENT</label>
                                    <select class="form-control" id="department" wire:model="record.department">
                                        <option value="">Select Department</option>
                                        <option value="Sewing">Sewing</option>
                                        <option value="Embroidery">Embroidery</option>
                                        <option value="Imprinting">Imprinting</option>
                                    </select>
                                    <div class="form-text text-danger mb-4">
                                        @error('record.department') <b>{{ $message }}</b> @enderror
                                    </div>
                                </div>
                            </div>


                           
                            <!-- Working Hours Start -->
                            <div class="col-md-6">
                                <div class="mb-0">
                                    <label for="working_hours_start" class="form-label h5">WORKING HOURS START</label>
                                    <input type="time" class="form-control" id="working_hours_start" wire:model="record.working_hours_start">
                                    <div class="form-text text-danger mb-4">
                                        @error('record.working_hours_start')
                                            <b>{{ $message }}</b>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Working Hours End -->
                            <div class="col-md-6">
                                <div class="mb-0">
                                    <label for="working_hours_end" class="form-label h5">WORKING HOURS END</label>
                                    <input type="time" class="form-control" id="working_hours_end" wire:model="record.working_hours_end">
                                    <div class="form-text text-danger mb-4">
                                        @error('record.working_hours_end')
                                            <b>{{ $message }}</b>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Time per Garment -->
                            <div class="col-md-6">
                                <div class="mb-0">
                                    <label class="form-label h5">TIME PER GARMENT</label>
                                    <div class="d-flex gap-2 align-items-center">
                                        <input type="number"  class="form-control" style="width: 100px;"
                                            placeholder="Hours" wire:model="time_per_garment_hours">

                                        <span class="h5 m-0">:</span>

                                        <input type="number"  class="form-control" style="width: 100px;"
                                            placeholder="Minutes" wire:model="time_per_garment_minutes">
                                    </div>

                                    <div class="form-text text-danger mb-4">
                                        @error('time_per_garment_hours') <b>{{ $message }}</b> @enderror
                                        @error('time_per_garment_minutes') <b>{{ $message }}</b> @enderror
                                    </div>
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
            @endif
        </div>
    </div>
    <script>
        function deleteRecord(id) 
        {
            Swal.fire({
            title: "Are you sure?",
            text: "You won't be able to revert this!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, delete it!"
            }).then((result) => {
                if (result.isConfirmed) {
                    @this.deleteRecord(id)
                    Swal.fire({
                    title: "Deleted!",
                    text: "Your employee has been deleted.",
                    icon: "success"
                    });
                }
            });
        }
    </script>
</div>
