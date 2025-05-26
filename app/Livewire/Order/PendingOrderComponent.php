<?php

namespace App\Livewire\Order;

use App\Mail\ReadyEmail;
use App\Models\Employee;
use App\Models\Order;
use App\Models\OrderLog;
use App\Models\OrderTrack;
use Livewire\Component;

use Illuminate\Support\Facades\Mail;
use Livewire\WithPagination;
use Livewire\Attributes\Session;
use Livewire\Attributes\Url;

class PendingOrderComponent extends Component 
{
    #[Url] 
    public $search;
    #[Url] 
    public $location;
    protected $queryString = ['search', 'location'];
    

    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $need_sewing;
    
    public $by_user;
    public $sort;
    public $orderBy;
   
    public $employees = [];
    public function mount()
    {
        $this->employees = Employee::where('type', 2)
        ->where('active', 1)
        ->where('is_delete', 0)
        ->orderBy('first_name', 'asc') // Move orderBy before get()
        ->get()
        ->map(function ($employee) {
            $employee['full_name'] = $employee->first_name . ' ' . $employee->last_name;
            $employee['empId'] = 'emp'.$employee->id;
            return $employee;
        })
        ->pluck('full_name', 'empId');
    }
    public function updatingSearch()
    {
        $this->resetPage();
    }
    public function updatedLocation()
    {

    }
    public function orders()
    {
       
        $order =  Order::where('status', 0);
        if($this->sort) {
           $order->orderBy($this->sort, $this->orderBy);
        }else{
            $order->oldest();
        }
        if($this->location) {
            $order->where('current_location', $this->location);
        }
        if (strlen($this->search) > 3) {
            $search = $this->search;
            $columns = ['order_number', 'current_location'];
            $order->searchLike($columns, $search);
        }
        return $order;
    }
    public function sortData($sort, $orderBy)
    {
        $this->orderBy = $orderBy;
        $this->sort  = $sort;
    }
    public function render()
    {
        $this->dispatch('sticky-header'); 
        return view('livewire.order.pending-order-component', [
            'orders' => $this->orders()->paginate(20)
        ]);
    }
    public function updateSweing($status, $orderId, $updated_by)
    {

        
        $numbersOnly = preg_replace("/[^0-9]/", "", $orderId);
        $order = Order::find($numbersOnly);  
        ////
        $ready = 0;
        $allCount = 0;
        if($order->need_sewing != 0) {
            $allCount += 1;
        }
        if($order->need_embroidery != 0) {
            $allCount += 1;
        }
        if($order->need_imprinting != 0) {
            $allCount += 1;
        }

        ////////
        $order->need_sewing = $status;
        $order->update();
        if($status)
        {
            $msg = "completed";
            OrderTrack::forceCreate([
                'order_id' => $order->id,
                'type' => 1,
                'status' => 1,
            ]);
        }
        else
        {
            OrderTrack::where('type', 1)->where('order_id', $order->id)->where('status', 1)->delete();
            $msg = "unchecked";
            $order->need_sewing = 2;
            $order->update();
        }
        OrderLog::forceCreate([
            'title' => "Sewing mark as $msg",
            'updated_by' => $updated_by,
            'order_id' => $order->id
        ]);

        if($order->need_sewing == 1) {
            $ready += 1;
        }
        if($order->need_embroidery == 1) {
            $ready += 1;
        }
        if($order->need_imprinting == 1) {
            $ready += 1;
        }
        

        if($allCount == $ready && isset($order->employee->email)) {
           
            Mail::to($order->employee->email)->send(new ReadyEmail($order));
            $order->status = 1;
            $order->update();
        }
        else
        {
            $order->status = 0;
            $order->update();
        }  
    }
    public function updateInprogress($status, $orderId, $updated_by)
    {
        $numbersOnly = preg_replace("/[^0-9]/", "", $orderId);
        $order = Order::find($numbersOnly);
        if($status)
        {
            $order->sewing_progress =  1;
            $order->update();
            $msg = "in process";
        }
        else
        {
            $msg = "Unfinished";
        }
        OrderLog::forceCreate([
            'title' => "Sewing mark as $msg",
            'updated_by' => $updated_by,
            'order_id' => $order->id
        ]); 
        /////
        if($status) 
        {
            OrderTrack::forceCreate([
                'order_id' => $order->id,
                'type' => 1,
                'status' => 0,
            ]);
        }
        else
        {
            OrderTrack::where('order_id', $order->id)->where('type', 1)->delete();
            $order->need_sewing = 2;
            $order->sewing_progress = 0;
            $order->update();
        }  
    }



    public function updateEmb($status, $orderId, $updated_by)
    {   
        $numbersOnly = preg_replace("/[^0-9]/", "", $orderId);
        $order = Order::find($numbersOnly);  
        ///
        $ready = 0;
        $allCount = 0;
        if($order->need_sewing != 0) {
            $allCount += 1;
        }
        if($order->need_embroidery != 0) {
            $allCount += 1;
        }
        if($order->need_imprinting != 0) {
            $allCount += 1;
        }
        ///
        $order->need_embroidery = $status;
        $order->update();
        if($status){

            // dd('asd');
            $msg = "completed";
            OrderTrack::forceCreate([
                'order_id' => $order->id,
                'type' => 2,
                'status' => 1,
            ]);
           
        }
        else
        {
            OrderTrack::where('type', 2)->where('order_id', $order->id)->where('status', 1)->delete();
            $msg = "unchecked";
            $order->need_embroidery = 2;
            $order->update();
        }
        OrderLog::forceCreate([
            'title' => "Embroidery mark as $msg",
            'updated_by' => $updated_by,
            'order_id' => $order->id
        ]);
        ///
        if($order->need_sewing == 1) {
            $ready += 1;
        }
        if($order->need_embroidery == 1) {
            $ready += 1;
        }
        if($order->need_imprinting == 1) {
            $ready += 1;
        }
        

        if($allCount == $ready) {
            Mail::to($order->employee->email)->send(new ReadyEmail($order));
            $order->status = 1;
            $order->update();
        }else{
            $order->status = 0;
            $order->update();
        }
    }

    public function updateInprogressEmb($status, $orderId, $updated_by)
    {
        $numbersOnly = preg_replace("/[^0-9]/", "", $orderId);
        $order = Order::find($numbersOnly);
        if($status)
        {
            
            $order->embroidery_progress =  1;
            $order->update();
            $msg = "in process";
        }
        else
        {
            $msg = "Unfinished";
        }
        OrderLog::forceCreate([
            'title' => "Embroidery mark as $msg",
            'updated_by' => $updated_by,
            'order_id' => $order->id
        ]); 
        /////
        if($status) 
        {
            OrderTrack::forceCreate([
                'order_id' => $order->id,
                'type' => 2,
                'status' => 0,
            ]);
        }
        else
        {
            OrderTrack::where('order_id', $order->id)->where('type', 2)->delete();
            $order->need_embroidery = 2;
            $order->embroidery_progress = 0;
            $order->update();
        }  
    }
    public function updateImp($status, $orderId, $updated_by)
    {
        $numbersOnly = preg_replace("/[^0-9]/", "", $orderId);
        $order = Order::find($numbersOnly);  
         ///
         $ready = 0;
         $allCount = 0;
         if($order->need_sewing != 0) {
             $allCount += 1;
         }
         if($order->need_embroidery != 0) {
             $allCount += 1;
         }
         if($order->need_imprinting != 0) {
             $allCount += 1;
         }
         ///
        $order->need_imprinting = $status;
        $order->update();
        if($status)
        {
            $msg = "completed";
            OrderTrack::forceCreate([
                'order_id' => $order->id,
                'type' => 3,
                'status' => 1,
            ]);
        }
        else
        {
            OrderTrack::where('type', 3)->where('status', 1)->where('order_id', $order->id)->delete();
            $msg = "unchecked";
            $order->need_imprinting = 2;
            $order->update();
        }
        OrderLog::forceCreate([
            'title' => "Imprinting mark as $msg",
            'updated_by' => $updated_by,
            'order_id' => $order->id
        ]);
        ///
        if($order->need_sewing == 1) {
            $ready += 1;
        }
        if($order->need_embroidery == 1) {
            $ready += 1;
        }
        if($order->need_imprinting == 1) {
            $ready += 1;
        }
        

        if($allCount == $ready) {
            Mail::to($order->employee->email)->send(new ReadyEmail($order));
            $order->status = 1;
            $order->update();
        }else{
            $order->status = 0;
            $order->update();
        }
    }
    public function updateInprogressImp($status, $orderId, $updated_by)
    {
        $numbersOnly = preg_replace("/[^0-9]/", "", $orderId);
        $order = Order::find($numbersOnly);
        if($status)
        {
            $order->imprinting_progress =  1;
            $order->update();
            $msg = "in process";
        }
        else
        {
            $msg = "Unfinished";
        }
        OrderLog::forceCreate([
            'title' => "Imprinting mark as $msg",
            'updated_by' => $updated_by,
            'order_id' => $order->id
        ]); 
        /////
        if($status) 
        {
            OrderTrack::forceCreate([
                'order_id' => $order->id,
                'type' => 3,
                'status' => 0,
            ]);
        }
        else
        {
            OrderTrack::where('order_id', $order->id)->where('type', 3)->delete();
            $order->need_imprinting = 2;
            $order->imprinting_progress = 0;
            $order->update();
        }  
    }
    public function updateLocation($orderId, $updated_by, $selectedText)
    {
        $order = Order::find($orderId);
        $location = $order->current_location;
        $order->current_location=$selectedText;
        $order->update();
        OrderLog::forceCreate([
            'title' => "Location changed from ".$location. " to $selectedText",
            'updated_by' => $updated_by,
            'order_id' => $order->id
        ]);
    }
    
    
}
