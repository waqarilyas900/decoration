<?php

namespace App\Livewire\Dashboard;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

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

            // Get assigned sections for this employee
            $assignedSections = \App\Models\OrderAssignment::where('employee_id', $employeeId)
                ->pluck('section')
                ->toArray();

            // Get all assignments for this employee to check completion of previous stages
            $myAssignments = \App\Models\OrderAssignment::where('employee_id', $employeeId)->get();

            // Count pending orders assigned to employee with previous stage completed
            $this->pendingOrder = \App\Models\Order::where('status', 0)
                ->whereHas('assignments', function ($query) use ($employeeId) {
                    $query->where('employee_id', $employeeId);
                })
                ->where(function ($query) use ($myAssignments) {
                    foreach ($myAssignments as $assign) {
                        $stage = $assign->section;
                        $garments = $assign->garments_assigned;
                        $orderId = $assign->order_id;

                        if ($stage === 'sewing') {
                            // Sewing assigned orders show directly
                            $query->orWhere('id', $orderId);

                        } elseif ($stage === 'embroidery') {
                            // Embroidery orders only if sewing complete
                            $query->orWhere(function ($q) use ($orderId, $garments) {
                                $q->where('id', $orderId)
                                ->whereHas('assignments', function ($qa) use ($garments) {
                                    $qa->where('section', 'sewing')
                                        ->where('garments_assigned', $garments)
                                        ->where('is_complete', 1);
                                });
                            });

                        } elseif ($stage === 'imprinting') {
                            // Imprinting orders only if embroidery complete
                            $query->orWhere(function ($q) use ($orderId, $garments) {
                                $q->where('id', $orderId)
                                ->whereHas('assignments', function ($qa) use ($garments) {
                                    $qa->where('section', 'embroidery')
                                        ->where('garments_assigned', $garments)
                                        ->where('is_complete', 1);
                                });
                            });
                        }
                    }
                })
                ->count();

            // Optional: hide other counts if not needed
            $this->readyOrder = 0;
            $this->removedOrder = 0;
        } else {
            // Admin or manager view
            $this->pendingOrder = \App\Models\Order::where('status', 0)->count();
            $this->readyOrder = \App\Models\Order::where('status', 1)->count();
            $this->removedOrder = \App\Models\Order::where('status', 3)->count();
        }
    }
// public function mount()
// {
//     $user = Auth::user();
//     $this->userType = $user->type;
//     $this->firstName = $user->name;

//     if ($user->type == 2) {
//         $employeeId = $user->employee_id;

//         // Fetch all assignments for this employee
//         $assignments = \App\Models\OrderAssignment::where('employee_id', $employeeId)->get();

//         $pendingOrderCount = 0;

//         foreach ($assignments as $assignment) {
//             $order = $assignment->order;
//             $section = strtolower($assignment->section);
//             $garments = $assignment->garments_assigned;

//             // Skip if order is not pending
//             if ($order->status !== 0) continue;

//             // Check if previous section is completed (if applicable)
//             $previousComplete = true;

//             if ($section === 'embroidery') {
//                 $previousComplete = \App\Models\OrderAssignment::where('order_id', $order->id)
//                     ->where('section', 'sewing')
//                     ->where('garments_assigned', $garments)
//                     ->where('is_complete', 1)
//                     ->exists();
//             } elseif ($section === 'imprinting') {
//                 $previousComplete = \App\Models\OrderAssignment::where('order_id', $order->id)
//                     ->where('section', 'embroidery')
//                     ->where('garments_assigned', $garments)
//                     ->where('is_complete', 1)
//                     ->exists();
//             }

//             if ($previousComplete) {
//                 $pendingOrderCount++;
//             }
//         }

//         $this->pendingOrder = $pendingOrderCount;
//         $this->readyOrder = 0;
//         $this->removedOrder = 0;
//     } else {
//         // For admin/manager
//         $this->pendingOrder = Order::where('status', 0)->count();
//         $this->readyOrder = Order::where('status', 1)->count();
//         $this->removedOrder = Order::where('status', 3)->count();
//     }
// }

    public function render()
    {
        return view('livewire.dashboard.dashboard-component');
    }
}
