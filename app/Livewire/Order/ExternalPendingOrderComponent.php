<?php

namespace App\Livewire\Order;

use App\Mail\ReadyEmail;
use App\Models\Employee;
use App\Models\Order;
use App\Models\OrderAssignment;
use App\Models\OrderLog;
use App\Models\OrderTrack;
use Livewire\Component;

use Illuminate\Support\Facades\Mail;
use Livewire\WithPagination;
use Livewire\Attributes\Session;
use Livewire\Attributes\Url;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Carbon\CarbonInterval;

class ExternalPendingOrderComponent extends Component
{  
    use WithPagination;
    #[Url] 
    public $search; 
    public $orderLocations = [];
    protected $queryString = ['search'];
    protected $paginationTheme = 'bootstrap';
    public $need_sewing;
    public $by_user;
    public $sort;
    public $orderBy;
    public $employees = [];
    public $confirmingStageUpdate = false;
    public $confirmingOrderId;
    public $confirmingStage;
    public $confirmingType;
    public $eta_data = [];
    public $currentDepartment;
    public $locationFilter;
    public $pendingLocationChange = null;
    public $showHandoverModal = false;
    public $handoverToEmployee = null;
    public $handoverOrderNumber = null;
    

    public function mount()
    {
        $user = auth()->user();
        $this->currentDepartment = $user->employee?->department;
        $this->employees = Employee::where('type', 2)
        ->where('active', 1)
        ->where('is_delete', 0)
        ->orderBy('first_name', 'asc') 
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
    public function updatedOrderLocations($value, $key)
    {
        $orderId = $key;
        $employeeId = auth()->user()->employee_id;

        $assignment = OrderAssignment::where('order_id', $orderId)
            ->where('employee_id', $employeeId)
            ->first();

        if ($assignment && $assignment->location !== $value) {
            // Save updated location
            $assignment->location = $value;
            $assignment->where('order_id', $orderId)->where('section', $value)->where('garments_assigned', $assignment->garments_assigned)->update(['location' => null]);
            $assignment->save();

           //Yakki
           $order = Order::where('id', $orderId)->first();

        //    dd($order->number_of_garments);
            if ( $assignment
            ->where('order_id', $orderId)
            ->where('location', 'Sewing')
            ->sum('garments_assigned') == $order->number_of_garments
                ) {
                    $order->current_location = 'Sewing';
                    $order->save();
                }


                
 if ( $assignment
            ->where('order_id', $orderId)
            ->where('location', 'Embroidery')
            ->sum('garments_assigned') == $order->number_of_garments
                ) {
                    $order->current_location = 'Embroidery';
                    $order->save();
                }

                if ( $assignment
            ->where('order_id', $orderId)
            ->where('location', 'Imprinting')
            ->sum('garments_assigned') == $order->number_of_garments
                ) {
                    $order->current_location = 'Imprinting';
                    $order->save();
                }

            // ✅ Check if all employees in the same section completed and selected the same location
            $sectionAssignments = OrderAssignment::where('order_id', $orderId)
                ->where('section', $assignment->section)
                ->get();

            $allCompleted = $sectionAssignments->every(fn ($a) => $a->is_complete == 1);
            $uniqueLocations = $sectionAssignments->pluck('location')->filter()->unique();

            if ($allCompleted && $uniqueLocations->count() === 1) {
                // All done + all same location → update order's main current_location
                Order::where('id', $orderId)->update([
                    'current_location' => $uniqueLocations->first(),
                ]);
            }

            // ✅ Handover logic remains
            $nextEmployeeAssignment = OrderAssignment::where('order_id', $assignment->order_id)
                ->where('section', $value)
                ->where('garments_assigned', $assignment->garments_assigned)
                ->first();

            if ($nextEmployeeAssignment && $nextEmployeeAssignment->employee) {
                $order = Order::find($orderId);
                $nextEmpName = $nextEmployeeAssignment->employee->first_name ?? 'Another Employee';
                $orderNumber = $order?->order_number ?? 'Unknown';

                session()->flash('message', "Order #{$orderNumber} is handed over to {$nextEmpName}.");
            } else {
                session()->flash('message', 'Location updated.');
            }
             $allAssignments = OrderAssignment::where('order_id', $orderId)->get();
                $allDone = $allAssignments->every(fn ($a) => $a->is_complete == 1);

                if ($allDone) {
                    Order::where('id', $orderId)->update([
                        'status' => 1, // ✅ Mark as ready
                    ]);
                }
        }
    }
   
    public function orders()
    {
        $user = auth()->user();
        $employeeId = $user->employee_id ?? null;

        $query = Order::query()
            ->where('status', 0)
            ->with('assignments');

      
        if ($user->type == 2 && $employeeId) {
            $myAssignments = OrderAssignment::where('employee_id', $employeeId)->get();

            $query->whereHas('assignments', fn($q) => $q->where('employee_id', $employeeId));

            $query->where(function ($q) use ($myAssignments, $employeeId) {
                    foreach ($myAssignments as $assign) {
                        $order = Order::find($assign->order_id);
                        if (!$order) continue;

                        // Get related assignments for this order
                        $related = $order->assignments;

                        // Check if user changed location
                        $userChangedLocation = !is_null($assign->location) && $assign->location !== $assign->section;

                        // CASE: Both employees swapped locations and both are incomplete
                        $swapped = $related->first(function ($a) use ($assign, $employeeId) {
                            return $a->employee_id !== $employeeId &&
                                $a->section === $assign->location && // his section = my location
                                $a->location === $assign->section && // his location = my section
                                $a->garments_assigned === $assign->garments_assigned &&
                                !$a->is_complete;
                        });

                        if ($userChangedLocation && !$assign->is_complete && $swapped) {
                            $q->orWhere('id', $order->id); // ✅ show to both sides
                            continue;
                        }

                        // CASE: Employee's section matches current location and location not changed
                        if (
                            is_null($assign->location) &&
                            $order->current_location === $assign->section &&
                            !$assign->is_complete
                        ) {
                            $q->orWhere('id', $order->id);
                            continue;
                        }

                        // CASE: Someone handed off the order to me (one-way)
                        $handoverToMe = $related->first(function ($a) use ($assign, $employeeId) {
                            return $a->employee_id !== $employeeId &&
                                $a->location === $assign->section &&
                                $a->garments_assigned === $assign->garments_assigned &&
                                !$a->is_complete;
                        });

                        if ($handoverToMe && !$assign->is_complete) {
                            $q->orWhere('id', $order->id);
                            continue;
                        }

                        // Optionally: allow viewing completed if still in same section
                        if ($assign->is_complete && is_null($assign->location) && $order->current_location === $assign->section) {
                            $q->orWhere('id', $order->id);
                            continue;
                        }

                        // ❌ Default: skip everything else
                    }
                });
        }
        // 📊 Filters
        if ($this->sort) {
            $query->orderBy($this->sort, $this->orderBy ?? 'asc');
        } else {
            $query->orderByDesc('is_priority')->orderBy('created_at', 'asc');
        }

       if ($user->type == 2 && $this->locationFilter) {
            $query->whereHas('assignments', function ($q) use ($employeeId) {
                $q->where('employee_id', $employeeId)
                ->where('location', $this->locationFilter);
            });
        }

        if (strlen($this->search) > 3) {
            $query->searchLike(['order_number', 'current_location'], $this->search);
        }

        $orders = $query->get();

        // ✅ ETA Calculation
        if ($user->type == 2 && $employeeId) {
            $employee = $user->employee;
            $startHour = Carbon::createFromFormat('H:i:s', $employee->working_hours_start);
            $endHour = Carbon::createFromFormat('H:i:s', $employee->working_hours_end);
            $timePerGarment = CarbonInterval::createFromFormat('H:i:s', $employee->time_per_garment);
            $cursorTime = $this->normalizeStartTime(Carbon::now(), $startHour, $endHour);

            foreach ($orders as $order) {
                $assignments = $order->assignments
                    ->where('employee_id', $employeeId)
                    ->sortBy(fn($a) => $this->getSectionPriority($a->section));

                foreach ($assignments as $assignment) {
                    if ($assignment->is_complete) continue;

                    // if (!$this->hasCorrectSequentialDependency($order, $assignment)) {
                    //     continue;
                    // }

                    $eta = $cursorTime->copy();
                    $totalSeconds = $timePerGarment->totalSeconds * $assignment->garments_assigned;
                    $eta = $this->normalizeStartTime($eta, $startHour, $endHour);

                    $secondsLeft = $totalSeconds;
                    while ($secondsLeft > 0) {
                        if ($this->isWeekend($eta)) {
                            $eta->addDay()->setTimeFrom($startHour);
                            continue;
                        }

                        $endOfDay = $eta->copy()->setTimeFrom($endHour);
                        $available = $eta->diffInSeconds($endOfDay);
                        $consume = min($available, $secondsLeft);

                        $eta->addSeconds($consume);
                        $secondsLeft -= $consume;

                        if ($secondsLeft > 0) {
                            $eta->addDay()->setTimeFrom($startHour);
                            while ($this->isWeekend($eta)) {
                                $eta->addDay();
                            }
                        }
                    }

                    $order->eta_data = [
                        'section' => $assignment->section,
                        'garments' => $assignment->garments_assigned,
                        'total_time' => gmdate('H:i:s', $totalSeconds),
                        'expected_delivery' => $eta->toDateTimeString(),
                    ];

                    $cursorTime = $eta->copy();
                    break;
                }
            }
        }

        // 📦 Manual Pagination
        $page = request()->get('page', 1);
        $perPage = 20;

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $orders->forPage($page, $perPage),
            $orders->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }
    private function getPreviousSection($section, Order $order)
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

    private function getSectionAssignments(Order $order, string $section)
    {
        return OrderAssignment::where('order_id', $order->id)
            ->where('section', $section)
            ->orderBy('id') // Ensure consistent order
            ->get();
    }
    private function hasCorrectSequentialDependency(Order $order, $assignment)
    {
        $prevSection = $this->getPreviousSection($assignment->section, $order);
        if (!$prevSection) return true;

        $currentAssignments = $this->getSectionAssignments($order, $assignment->section);
        $prevAssignments = $this->getSectionAssignments($order, $prevSection);

        $index = $currentAssignments->search(fn($a) => $a->id === $assignment->id);

        if ($index === false || !isset($prevAssignments[$index])) {
            return false;
        }

        $prev = $prevAssignments[$index];

        return $prev->is_complete && $prev->garments_assigned === $assignment->garments_assigned;
    }
    private function normalizeStartTime(Carbon $time, Carbon $start, Carbon $end): Carbon
    {
        if ($this->isWeekend($time)) {
            $time = $time->next(Carbon::MONDAY)->setTimeFrom($start);
        } elseif ($time->lt($time->copy()->setTimeFrom($start))) {
            $time->setTimeFrom($start);
        } elseif ($time->gte($time->copy()->setTimeFrom($end))) {
            $time = $time->addDay();
            while ($this->isWeekend($time)) $time->addDay();
            $time->setTimeFrom($start);
        }
        return $time;
    }

    public function confirmStageUpdate($orderId, $stage, $type)
    {
        $this->confirmingOrderId = $orderId;
        $this->confirmingStage = $stage;
        $this->confirmingType = $type;
        $this->confirmingStageUpdate = true;
    }
  
  
    public function performStageUpdate()
    {
        $orderId = $this->confirmingOrderId;
        $stage = $this->confirmingStage;
        $type = $this->confirmingType;

        $user = auth()->user();
        $employeeId = $user->employee_id;

        $assignments = OrderAssignment::where('order_id', $orderId)
            ->where('section', $stage)
            ->where('employee_id', $employeeId)
            ->get();

        if ($assignments->isEmpty()) {
            return;
        }

        $progressChanged = false;
        $completionChanged = false;

        foreach ($assignments as $assignment) {
            if ($type === 'progress') {
                $assignment->is_progress = !$assignment->is_progress;

                if ($assignment->is_progress) {
                    $progressChanged = true;
                } else {
                    $assignment->is_complete = 0; // reset complete if progress turned off
                }
            } elseif ($type === 'complete' && $assignment->is_progress) {
                $assignment->is_complete = !$assignment->is_complete;
                $completionChanged = true;
            }

            $assignment->save();
        }

        $order = Order::find($orderId);

        // ✅ If marked progress, update progress field
       if ($order) {
            $progressField = strtolower($stage) . '_progress';
            if (in_array($progressField, ['sewing_progress', 'embroidery_progress', 'imprinting_progress'])) {
                $hasAnyProgress = OrderAssignment::where('order_id', $orderId)
                    ->where('section', $stage)
                    ->where('is_progress', 1)
                    ->exists();

                $order->$progressField = $hasAnyProgress ? 1 : 2;
            }
        }

        // ✅ If marked complete, check if all in section are complete
       if ($completionChanged && $order) {
            $allCompleteInSection = OrderAssignment::where('order_id', $orderId)
                ->where('section', $stage)
                ->where('is_complete', 0)
                ->doesntExist();

            if ($allCompleteInSection) {
                $needField = 'need_' . strtolower($stage);
                if (in_array($needField, ['need_sewing', 'need_embroidery', 'need_imprinting'])) {
                    $order->$needField = 1;
                }
            }
        }

        // Always ensure progress field is updated
        if ($order) {
            // Determine applicable sections based on assignments
            $sectionsWithAssignments = OrderAssignment::where('order_id', $order->id)
                ->select('section')
                ->distinct()
                ->pluck('section')
                ->map(fn ($section) => strtolower($section))
                ->toArray();

            $sectionNeedFields = [
                'sewing' => 'need_sewing',
                'embroidery' => 'need_embroidery',
                'imprinting' => 'need_imprinting',
            ];

            // Only include sections that actually have assignments
            $requiredSections = collect($sectionNeedFields)
                ->only($sectionsWithAssignments)
                ->all();

            // Check if all assignments are complete
            $allAssignmentsComplete = OrderAssignment::where('order_id', $order->id)
                ->where('is_complete', 0)
                ->doesntExist();

            // Check if all required `need_*` fields are set to 1
            $allRequiredSectionsDone = collect($requiredSections)->every(function ($field) use ($order) {
                return $order->$field == 1;
            });

            // \Log::info("Checking Order {$order->order_number} → Required Sections: " . json_encode($requiredSections));
            // \Log::info("AllComplete: {$allAssignmentsComplete} | AllSectionsDone: {$allRequiredSectionsDone}");

            if ($allAssignmentsComplete && $allRequiredSectionsDone) {
                $order->status = 1;

                $order->load('assignments');
                $order->assignments->each(function ($assignment) {
                    $assignment->is_complete = 1;
                    $assignment->is_progress = 1;
                    $assignment->save();
                });

                session()->flash('message', "Order #{$order->order_number} is now ready.");
            }

            $order->save();
        }

        $this->confirmingStageUpdate = false;

        // ✅ Update stage if needed
        // $this->checkAndAdvanceOrderStage($orderId);
    }

    public function checkAndAdvanceOrderStage($orderId)
    {
        $stages = ['Sewing', 'Embroidery', 'Imprinting'];

        $order = Order::find($orderId);
        if (!$order || !in_array($order->current_location, $stages)) {
            return;
        }

        $currentStage = $order->current_location;

        // Get all assignments for current stage
        $assignments = OrderAssignment::where('order_id', $order->id)
            ->where('section', $currentStage)
            ->get();

        // Only proceed if all assignments are completed
        if ($assignments->isEmpty() || $assignments->contains(fn($a) => $a->is_complete != 1)) {
            return;
        }

        $currentIndex = array_search($currentStage, $stages);
        $nextStage = $stages[$currentIndex + 1] ?? null;

        if ($nextStage && $order->{"need_" . strtolower($nextStage)}) {
            // Check if any assignment in current stage selected this next stage as location
            $locationSelected = $assignments->contains(function ($a) use ($nextStage) {
                return $a->location === $nextStage;
            });

            if ($locationSelected) {
                $order->current_location = $nextStage;
            }
        }
        else {
                }

        $order->save();
    }

    public function sortData($sort, $orderBy)
    {
        $this->orderBy = $orderBy;
        $this->sort  = $sort;
    }
    public function render()
    {

        $this->dispatch('sticky-header');
        $orders = $this->orders(); 
        
        $employeeId = auth()->user()->employee_id;
        foreach ($orders as $order) {
            $assignment = $order->assignments->where('employee_id', $employeeId)->first();

            if ($assignment) {
                $this->orderLocations[$order->id] = $assignment->location ?? $assignment->section;
            }
        }

        return view('livewire.order.external-pending-order-component', [
            'orders' => $orders,
        ]);
    }

    private function getSectionPriority($section)
    {
        return match ($section) {
            'Sewing' => 1,
            'Embroidery' => 2,
            'Imprinting' => 3,
            default => 99,
        };
    }

   

    private function getPreviousEmployeeSection(Order $order, $employeeId, $currentSection, $garments)
    {
        $sections = ['Sewing', 'Embroidery', 'Imprinting'];

        // Get only sections this employee has for this order & garments
        $employeeSections = $order->assignments
            ->where('employee_id', $employeeId)
            ->where('garments_assigned', $garments)
            ->pluck('section')
            ->unique()
            ->values()
            ->toArray();

        // Keep only those in correct priority order
        $sortedEmployeeSections = array_values(array_filter(
            $sections,
            fn($s) => in_array($s, $employeeSections)
        ));

        $currentIndex = array_search($currentSection, $sortedEmployeeSections);

        // Only return previous section if current section exists and is not first
        if ($currentIndex !== false && $currentIndex > 0) {
            return $sortedEmployeeSections[$currentIndex - 1];
        }

        return null;
    }
    private function isWeekend(Carbon $date)
    {
        return in_array($date->dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY]);
    }
    private function logPoint($label)
    {
        Log::info("[$label] Time: " . now() . ' | Memory: ' . memory_get_usage(true));
    }



}
