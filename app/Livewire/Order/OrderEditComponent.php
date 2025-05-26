<?php

namespace App\Livewire\Order;

use App\Models\Employee;
use App\Models\Order;
use App\Models\OrderLog;
use App\Models\OrderTrack;
use Livewire\Component;
use Livewire\Attributes\Validate;
use Illuminate\Support\Arr;
class OrderEditComponent extends Component
{
    #[Validate] 
    public $order_number = '';
    public $need_sewing;
    public $need_embroidery;
    public $need_imprinting;
    public $current_location;
    public $sewing_progress;
    public $imprinting_progress;
    public $embroidery_progress;
    public $created_by;
    public $getCreatedBy = [];
    public $orderId;
    protected $queryString = ['orderId'];
    public $order;
    public $confrmView;
    public $updated_by;
    public $removed_by;
    public $status;
    public $getReadyBy = [];
    public $getRemovedBy = [];
    public $employees = [];
    public $employeesCreated = [];
    public $updateView = true;
    public function mount()
    {
        $this->employees  = Employee::where('type', 1)
        ->where('is_delete', 0)
        ->orderBy('first_name', 'asc')
        ->where('active', 1)->get();

        $this->employeesCreated  = Employee::where('type', 1)
       
        ->orderBy('first_name', 'asc')
        ->where('active', 1)->get();

        $this->order = Order::find($this->orderId); 
        $this->need_sewing = $this->order->need_sewing ? true : false;
        $this->need_embroidery = (int)$this->order->need_embroidery ? true : false;
        $this->need_imprinting = (int)$this->order->need_imprinting ? true : false;
        $this->current_location = $this->order->current_location;
        $this->order_number = $this->order->order_number;
        $this->created_by = $this->order->created_by;
        // dd($this->current_location);
    }
    public function render()
    {  
        return view('livewire.order.order-edit-component');
    }
    public function save()
    {
        if(!$this->need_sewing && !$this->need_embroidery && !$this->need_imprinting) {
            $this->current_location = null;
        }
        $order = Order::where('id', $this->order->id)->first();
        $validated = $this->validate( [
            'updated_by' => 'required',
            'current_location' => 'required',
            'need_sewing' => 'nullable',
            'need_imprinting' => 'nullable',
            'need_embroidery' => 'nullable',
            'sewing_progress' => 'nullable',
            'imprinting_progress' => 'nullable',
            'embroidery_progress' => 'nullable',
        ]);
        unset($validated['updated_by']);
        $ready = null;
        if($this->confrmView) {
            if((int) $order->need_sewing != (int)$this->need_sewing || (int) $order->need_imprinting != (int)$this->need_embroidery || (int) $order->need_embroidery != (int)$this->need_imprinting) 
            {
            $order->status = 0;
            $order->update();
            }
            if( $order->need_sewing!=1 && $validated['need_sewing'] == 1) {
                $validated['need_sewing'] = 2;
                $validated['sewing_progress'] = 0;
                OrderTrack::where('type', 1)->where('status', 0)->where('order_id', $order->id)->delete();
            }
            if($order->need_imprinting!=1 && $validated['need_imprinting'] == 1) {
                $validated['need_imprinting'] = 2;
                $validated['imprinting_progress'] = 0;
                OrderTrack::where('type', 3)->where('status', 0)->where('order_id', $order->id)->delete();
            }
            if($order->need_embroidery!=1 && $validated['need_embroidery'] == 1) {

                $validated['embroidery_progress'] = 0;
                OrderTrack::where('type', 2)->where('status', 0)->where('order_id', $order->id)->delete();
                $validated['need_embroidery'] = 2;
            }
            Order::where('id', $this->order->id)->update($validated);
            $afterUpdateorder = Order::where('id', $this->order->id)->first();
            // ///
            $ready = 0;
            $allCount = 0;
            if($afterUpdateorder->need_sewing != 0) {
                $allCount += 1;
            }
            if($afterUpdateorder->need_embroidery != 0) {
                $allCount += 1;
            }
            if($afterUpdateorder->need_imprinting != 0) {
                $allCount += 1;
            }
            if($afterUpdateorder->need_sewing == 1) {
                $ready += 1;
            }
            if($afterUpdateorder->need_embroidery == 1) {
                $ready += 1;
            }
            if($afterUpdateorder->need_imprinting == 1) {
                $ready += 1;
            }
           
    
            if($allCount == $ready) 
            {
                OrderLog::forceCreate([
                    'title' => "Order marked as ready",
                    'updated_by' => $this->updated_by,
                    'order_id' => $afterUpdateorder->id
                ]);
                $afterUpdateorder->status = 1;
                $afterUpdateorder->update();
            }
            else
            {
                OrderLog::forceCreate([
                    'title' => "Order marked as pending",
                    'updated_by' => $this->updated_by,
                    'order_id' => $afterUpdateorder->id
                ]);
                $afterUpdateorder->status = 0;
                $afterUpdateorder->update();
            }
            ///////
            OrderLog::forceCreate([
                'order_id' => $afterUpdateorder->id,
                'title' => 'order updated',
                'updated_by' => $this->updated_by
            ]);  

            session()->flash('message', 'Order has been updated');
            $this->confrmView = false;  
        }
    }
    public function confirmation($status)
    {
        if($status == "no") {
            $this->confrmView = false;
        }
        elseif($status == "update") {
            $this->updateView = false;
            $this->confrmView = true;
        }
        else
        {
            $this->status = $status;
            $this->confrmView = true;
        }
    }
}
