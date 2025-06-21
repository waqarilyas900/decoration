<?php

namespace App\Livewire\Dashboard;

use App\Models\Order;
use App\Models\OrderAssignment;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
  use Illuminate\Support\Facades\Log;

class DashboardComponent extends Component
{
    public $pendingOrder;
    public $readyOrder;
    public $removedOrder;
    public $userType;
    public $firstName;
    public function mount()
    {
        $user = Auth::user();
        $this->userType = $user->type;
        $this->firstName = $user->name;


        if ($user->type == 2) {
            $employeeId = $user->employee_id;

            $myAssignments = \App\Models\OrderAssignment::with('order.assignments')
                ->where('employee_id', $employeeId)
                ->get();

            $visibleOrderIds = collect();

            foreach ($myAssignments as $assign) {
                $order = $assign->order;

                if (is_null($order) || (int)$order->status !== 0) {
                    continue;
                }

                if ($order->current_location === $assign->section && !$assign->is_complete) {
                    $visibleOrderIds->push($order->id);
                    continue;
                }

                if ($assign->is_complete) {
                    $visibleOrderIds->push($order->id);
                    continue;
                }

                $handoverAssignments = $order->assignments
                    ->where('location', $assign->section)
                    ->where('is_complete', 1)
                    ->where('garments_assigned', $assign->garments_assigned);

                if ($handoverAssignments->count()) {
                    $visibleOrderIds->push($order->id);
                }
            }


            $this->pendingOrder = $visibleOrderIds->unique()->count();
            $this->readyOrder = 0;
            $this->removedOrder = 0;
        } else {
            $this->pendingOrder = \App\Models\Order::where('status', 0)->count();
            $this->readyOrder = \App\Models\Order::where('status', 1)->count();
            $this->removedOrder = \App\Models\Order::where('status', 3)->count();
        }
    }
    public function render()
    {
        return view('livewire.dashboard.dashboard-component');
    }
}
