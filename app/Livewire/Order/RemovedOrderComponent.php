<?php

namespace App\Livewire\Order;

use App\Models\Order;
use App\Models\OrderLog;
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
class RemovedOrderComponent extends Component 
{
    #[Url] 
    public $search;
    #[Url] 
    public $location;
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $need_sewing;
  
    public $by_user;
    public $sort;
    public $orderBy;
   

    
    public function updatingSearch()
    {
        $this->resetPage();
    }
    public function updatedLocation()
    {
        
    }
    public function orders()
    {
       
        $order =  Order::where('status', 3);
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
        return view('livewire.order.removed-order-component', [
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
        }else{
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
        

        if($allCount == $ready) {
            $order->status = 2;
            $order->update();
        }else{
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
            $order->status = 2;
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
            $order->status = 1;
            $order->update();
        }else{
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

    public function removeOrder($orderId)
    {
       $order = Order::find($orderId);
       $order->status = 3;
       $order->update();
    }
    
}
