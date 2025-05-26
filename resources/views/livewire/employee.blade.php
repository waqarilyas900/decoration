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
                                                Email
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
                                                {{ $item->email }}
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
                            <div class="col-md-6">
                                <div class="mb-0">
                                    <label for="first_name" class="form-label h5">FIRST NAME</label>
                                    <input type="text" class="form-control" id="first_name"
                                        aria-describedby="first_name" wire:model="record.first_name">
                                    <div id="first_name" class="form-text text-danger mb-4">
                                        @error('record.first_name')
                                        <b> {{ $message }}</b>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-0">
                                    <label for="last_name" class="form-label h5">LAST NAME</label>
                                    <input type="text" class="form-control" id="last_name" aria-describedby="last_name"
                                        wire:model="record.last_name">
                                    <div id="last_name" class="form-text text-danger mb-4">
                                        @error('record.last_name')
                                        <b> {{ $message }}</b>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-0">
                                    <label for="email" class="form-label h5">EMAIL</label>
                                    <input type="text" class="form-control" id="email" aria-describedby="email"
                                        wire:model="record.email">
                                    <div id="email" class="form-text text-danger mb-4">
                                        @error('record.email')
                                        <b> {{ $message }}</b>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-0">
                                    <label for="type" class="form-label h5">Type</label>
                                    <select wire:model="record.type" class="form-control">
                                        <option value="">Select</option>
                                        <option value="1">Internal</option>
                                        <option value="2">External</option>
                                    </select>
                                    <div id="type" class="form-text text-danger mb-4">
                                        @error('record.type')
                                        <b> {{ $message }}</b>
                                        @enderror
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
