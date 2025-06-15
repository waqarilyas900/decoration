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
//    public function mount()
//     {
//         $user = Auth::user();
//         $this->userType = $user->type;
//         $this->firstName = $user->name;
//         if ($user->type == 2) {
//             $employeeId = $user->employee_id;

//             // Get assigned sections for this employee
//             $assignedSections = \App\Models\OrderAssignment::where('employee_id', $employeeId)
//                 ->pluck('section')
//                 ->toArray();

//             // Get all assignments for this employee to check completion of previous stages
//             $myAssignments = \App\Models\OrderAssignment::where('employee_id', $employeeId)->get();

//             // Count pending orders assigned to employee with previous stage completed
//             $this->pendingOrder = \App\Models\Order::where('status', 0)
//                 ->whereHas('assignments', function ($query) use ($employeeId) {
//                     $query->where('employee_id', $employeeId);
//                 })
//                 ->where(function ($query) use ($myAssignments) {
//                     foreach ($myAssignments as $assign) {
//                         $stage = $assign->section;
//                         $garments = $assign->garments_assigned;
//                         $orderId = $assign->order_id;

//                         if ($stage === 'sewing') {
//                             // Sewing assigned orders show directly
//                             $query->orWhere('id', $orderId);

//                         } elseif ($stage === 'embroidery') {
//                             // Embroidery orders only if sewing complete
//                             $query->orWhere(function ($q) use ($orderId, $garments) {
//                                 $q->where('id', $orderId)
//                                 ->whereHas('assignments', function ($qa) use ($garments) {
//                                     $qa->where('section', 'sewing')
//                                         ->where('garments_assigned', $garments)
//                                         ->where('is_complete', 1);
//                                 });
//                             });

//                         } elseif ($stage === 'imprinting') {
//                             // Imprinting orders only if embroidery complete
//                             $query->orWhere(function ($q) use ($orderId, $garments) {
//                                 $q->where('id', $orderId)
//                                 ->whereHas('assignments', function ($qa) use ($garments) {
//                                     $qa->where('section', 'embroidery')
//                                         ->where('garments_assigned', $garments)
//                                         ->where('is_complete', 1);
//                                 });
//                             });
//                         }
//                     }
//                 })
//                 ->count();

//             // Optional: hide other counts if not needed
//             $this->readyOrder = 0;
//             $this->removedOrder = 0;
//         } else {
//             // Admin or manager view
//             $this->pendingOrder = \App\Models\Order::where('status', 0)->count();
//             $this->readyOrder = \App\Models\Order::where('status', 1)->count();
//             $this->removedOrder = \App\Models\Order::where('status', 3)->count();
//         }
//     }

    public function mount()
    {
        $user = Auth::user();
        $this->userType = $user->type;
        $this->firstName = $user->name;

        if ($user->type == 2) {
            $employeeId = $user->employee_id;

            // Mirror the full query logic from externalpendingorder
            $query = \App\Models\Order::query()
                ->where('status', 0)
                ->with(['assignments' => fn($q) => $q->where('employee_id', $employeeId)]);

            $myAssignments = \App\Models\OrderAssignment::where('employee_id', $employeeId)->get();

            $query->whereHas('assignments', fn($q) => $q->where('employee_id', $employeeId))
                ->where(function ($q) use ($myAssignments) {
                    foreach ($myAssignments as $assign) {
                        $order = \App\Models\Order::find($assign->order_id);
                        if (!$order) continue;

                        $q->orWhere(function ($sub) use ($assign, $order) {
                            $prevSection = $this->getPreviousSection($assign->section, $order);

                            if (!$prevSection) {
                                $sub->where('id', $assign->order_id);
                            } else {
                                $currentAssignments = $this->getSectionAssignments($order, $assign->section);
                                $prevAssignments = $this->getSectionAssignments($order, $prevSection);

                                $index = $currentAssignments->search(fn($a) => $a->id === $assign->id);

                                if ($index !== false && isset($prevAssignments[$index])) {
                                    $prev = $prevAssignments[$index];
                                    if (
                                        $prev->is_complete &&
                                        $prev->garments_assigned === $assign->garments_assigned
                                    ) {
                                        $sub->where('id', $assign->order_id);
                                    }
                                }
                            }
                        });
                    }
                });

            // Final count
            $this->pendingOrder = $query->count();
            $this->readyOrder = 0;
            $this->removedOrder = 0;
        } else {
            $this->pendingOrder = \App\Models\Order::where('status', 0)->count();
            $this->readyOrder = \App\Models\Order::where('status', 1)->count();
            $this->removedOrder = \App\Models\Order::where('status', 3)->count();
        }
    }



    private function getPreviousSection($section, \App\Models\Order $order)
    {
        $sections = ['Sewing', 'Embroidery', 'Imprinting'];
        $currentIndex = array_search($section, $sections);

        if ($currentIndex === false || $currentIndex === 0) {
            return null;
        }

        for ($i = $currentIndex - 1; $i >= 0; $i--) {
            $prev = $sections[$i];
            if ($order->{'need_' . strtolower($prev)}) {
                return $prev;
            }
        }

        return null;
    }

    private function getSectionAssignments(\App\Models\Order $order, string $section)
    {
        return \App\Models\OrderAssignment::where('order_id', $order->id)
            ->where('section', $section)
            ->orderBy('id')
            ->get();
    }

    public function render()
    {
        return view('livewire.dashboard.dashboard-component');
    }
}
