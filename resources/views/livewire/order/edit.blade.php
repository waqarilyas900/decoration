<div class="container-fluid py-4">
   
    <div class="row">
        <div class="col-md-1"></div>
        <div class="col-md-10">
            @if (session()->has('message'))
            <div class="alert alert-primary" role="alert">
                <strong class="text-white h5">{{ session('message') }}</strong> 
            </div>
            @endif
            <div class="card">
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
                                       

                                        <div  class="form-text text-danger mb-4">
                                            @error('current_location')
                                            <b> {{ $message }}</b>
                                            @enderror
                                        </div>
                                       
                                    </div>
                                    <div class="col-md-12 mt-3">
                                        <div class="mb-3">
                                            <label for="created_by" class="form-label h5">CREATED BY</label>
                                            <input wire:model.live="created_by" list="created_by" id="fruitInput" type="text"
                                                class="form-control" id="created_by" aria-describedby="emailHelp" autocomplete="off">
                                            <datalist id="created_by">
                                               @foreach($getCreatedBy as $item)
                                                <option value="{{ $item->created_by }}">
                                                @endforeach
                                                    <!-- Add more options as needed -->
                                            </datalist>
                                            @error('created_by')
                                            <div id="created_by" class="form-text text-danger">
                                                <b> {{ $message }}</b>
                                            </div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mt-4">
                                <div class="form-check mb-3">
                                    <input class="" type="checkbox"  id="fcustomCheck11" wire:model.live="need_sewing">
                                    <label class="custom-control-label h5 mt-2" for="fcustomCheck11">Needs
                                        Sewing</label>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="" type="checkbox"  id="fcustomCheck12" wire:model.live="need_embroidery">
                                    <label class="custom-control-label h5 mt-2" for="fcustomCheck12">Needs
                                        Embroidery</label>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="" type="checkbox"  id="fcustomCheck3" wire:model.live="need_imprinting">
                                    <label class="custom-control-label h5 mt-2" for="fcustomCheck3"> Needs
                                        Imprinting</label>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                        </div>
                        <div class="row">
                        </div>
                        <button type="submit" class="btn btn-primary" 
                      >Submit</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-1"></div>
    </div>
</div>