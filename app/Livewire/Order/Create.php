<?php

namespace App\Livewire\Order;

use App\Models\Employee;
use App\Models\Order;
use App\Models\OrderLog;
use Livewire\Component;
use Livewire\Attributes\Validate;

class Create extends Component
{
    #[Validate] 
    public $order_number = '';
    public $need_sewing;
    public $need_embroidery;
    public $need_imprinting;
    public $current_location;
    public $created_by;
    public $getCreatedBy = [];
    public $employees = [];
    public function mount()
    {
        $this->employees = Employee::where('type', 1)
        ->orderBy('first_name', 'asc')
        ->where('active', 1)
        ->where('is_delete', 0)
        ->get();
    }
    public function rules()
    {
        return [
            'order_number' => 'required',
            'current_location' => 'required',
            'created_by' => 'required',
            'need_sewing' => 'nullable',
            'need_imprinting' => 'nullable',
            'need_embroidery' => 'nullable',
        ];
    }
    public function render()
    {
        
        return view('livewire.order.create');
    }
    public function save()
    {
       
       if(!$this->need_sewing && !$this->need_embroidery && !$this->need_imprinting) {
            $this->current_location = null;
       }
        $validated = $this->validate();
        $order = Order::forceCreate($validated);

        OrderLog::forceCreate([
            'title' => "Order has been created",
            'updated_by' => $this->created_by,
            'order_id' => $order->id
        ]);
        
        OrderLog::forceCreate([
            'title' => "Current Location of order is ". $this->current_location,
            'updated_by' => $this->created_by,
            'order_id' => $order->id
        ]);
        if($this->need_sewing) 
        $order->need_sewing = 2;
        if($this->need_embroidery) 
        $order->need_embroidery = 2;
        
        if($this->need_imprinting)
        $order->need_imprinting = 2;
        
        $order->update();

        session()->flash('message', "Order number $this->order_number has been created.");
        
        $this->resetExcept('employees');

        
    }

    public function updatedCreatedBy()
    {
        $keyword = $this->created_by;
        $this->getCreatedBy = Order::where('created_by', 'like', '%' . $keyword . '%')->groupBy('created_by')->limit(5)->get();
    }
    
}
