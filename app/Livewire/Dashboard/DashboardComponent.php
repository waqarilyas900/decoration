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
   public function mount()
    {
        $user = Auth::user();
        $this->userType = $user->type;

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
    public function render()
    {
        return view('livewire.dashboard.dashboard-component');
    }
}
