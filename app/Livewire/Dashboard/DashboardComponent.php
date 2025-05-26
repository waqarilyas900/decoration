<?php

namespace App\Livewire\Dashboard;

use App\Models\Order;
use Livewire\Component;

class DashboardComponent extends Component
{
    public $pendingOrder;
    public $readyOrder;
    public $removedOrder;
    public function mount()
    {
        $this->pendingOrder = Order::where('status', 0)->count();
        $this->readyOrder = Order::where('status', 1)->count();
        $this->removedOrder = Order::where('status', 3)->count();
    }
    public function render()
    {
        return view('livewire.dashboard.dashboard-component');
    }
}
