<?php

namespace App\Livewire\Order;

use App\Models\Employee;
use App\Models\Order;
use App\Models\OrderLog;
use App\Models\OrderTrack;
use Livewire\Component;
use App\Models\Shop\Product;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Filament\Tables\Columns\CheckboxColumn;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
class ReadyOrderComponent extends Component 
{
    #[Url] 
    public $search;
    #[Url] 
    public $location;
    protected $queryString = ['search', 'location', 'employee_id'];
    use WithPagination;
    
    protected $paginationTheme = 'bootstrap';
    public $need_sewing;
    
    public $by_user;
    public $sort;
    public $orderBy;
   
    public $employees = [];
    public $removeEmployees = [];
    #[Url] 
    public $employee_id;

    public function mount()
    {
        // $this->employees = Employee::where('type', 2)->where('active', 1)
        // ->orderBy('first_name', 'asc')
        // ->get()
        // ->map(function ($employee) {
        //     $employee['full_name'] = $employee->first_name . ' ' . $employee->last_name;
        //     return $employee;
        // })
        // ->pluck('full_name', 'id');
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

        ////
        // $this->removeEmployees = Employee::where('type', 1)->where('active', 1)
        // ->orderBy('first_name', 'asc')
        // ->get()
        // ->map(function ($employee) {
        //     $employee['full_name'] = $employee->first_name . ' ' . $employee->last_name;
        //     return $employee;
        // })
        // ->pluck('full_name', 'id');
        $this->removeEmployees = Employee::where('type', 1)
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
       
        // dd( $this->employees );
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
       
        $order =  Order::where('status', 1);
        if($this->employee_id) {
            $numbersOnly = preg_replace("/[^0-9]/", "", $this->employee_id);
            $order->where('created_by', $numbersOnly);
        }
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
        return view('livewire.order.ready-order-component', [
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
        if($status){
            $msg = "completed";
        }
        else
        {
            OrderTrack::where('type', 1)->where('status', 1)->where('order_id', $order->id)->delete();
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
        

        if($allCount == $ready) 
        {
            OrderLog::forceCreate([
                'title' => "Order marked as ready",
                'updated_by' => $updated_by,
                'order_id' => $order->id
            ]);
            $order->status = 1;
            $order->update();
        }
        else
        {
            OrderLog::forceCreate([
                'title' => "Order marked as pending",
                'updated_by' => $updated_by,
                'order_id' => $order->id
            ]);
            $order->status = 0;
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
            $msg = "completed";
           
        }else{
            OrderTrack::where('type', 2)->where('status', 1)->where('order_id', $order->id)->delete();
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
            $order->status = 1;
            $order->update();
        }else{
            $order->status = 0;
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
        if($order->need_sewing == 1) 
        {
            $ready += 1;
        }
        if($order->need_embroidery == 1) 
        {
            $ready += 1;
        }
        if($order->need_imprinting == 1) 
        {
            $ready += 1;
        }
        

        if($allCount == $ready) 
        {
            $order->status = 1;
            $order->update();
        }
        else
        {
            $order->status = 0;
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

    public function removeOrder($orderId, $updated_by, $selectedText)
    {
        // dd($orderId);
       $order = Order::find($orderId);
       $order->status = 3;
       OrderLog::forceCreate([
        'title' => "Order has been removed",
        'updated_by' => $updated_by,
        'order_id' => $order->id
        ]);
       $order->update();
    }
    
}
