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
    #[Url] 
    public $location;
    protected $queryString = ['search', 'location'];
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
    

    public function mount()
    {
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
    public function updatedLocation()
    {

    }

    public function orders()
    {

        $user = auth()->user();
        $employeeId = $user->employee_id ?? null;

        $query = Order::query()
            ->where('status', 0)
            ->with(['assignments' => fn($q) => $q->where('employee_id', $employeeId)]);

        // 🔒 Restrict to relevant orders
        if ($user->type == 2 && $employeeId) {

            $myAssignments = OrderAssignment::where('employee_id', $employeeId)->get();

            $query->whereHas('assignments', fn($q) => $q->where('employee_id', $employeeId))
                ->where(function ($q) use ($myAssignments) {
                    foreach ($myAssignments as $assign) {
                        $q->orWhere(function ($sub) use ($assign) {
                            $dependsOn = $this->getPreviousSection($assign->section);
                            if (!$dependsOn) {
                                $sub->where('id', $assign->order_id);
                            } else {
                                $sub->where('id', $assign->order_id)
                                    ->whereHas('assignments', fn($qa) =>
                                        $qa->where('section', $dependsOn)
                                        ->where('garments_assigned', $assign->garments_assigned)
                                        ->where('is_complete', 1)
                                    );
                            }
                        });
                    }
                });
        }

        // 📊 Filters
        if ($this->sort) {
            $query->orderBy($this->sort, $this->orderBy ?? 'asc');
        } else {
            $query->orderByDesc('is_priority')->orderBy('created_at', 'asc');
        }

        if ($this->location) {
            $query->where('current_location', $this->location);
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
            $dailySeconds = $endHour->diffInSeconds($startHour);
            $timePerGarment = CarbonInterval::createFromFormat('H:i:s', $employee->time_per_garment);

            $cursorTime = $this->normalizeStartTime(Carbon::now(), $startHour, $endHour);

            $completedAssignments = OrderAssignment::where('is_complete', 1)->get()
                ->groupBy(fn($a) => "{$a->order_id}|{$a->section}|{$a->garments_assigned}");

            foreach ($orders as $order) {
                $assignments = $order->assignments
                    ->where('employee_id', $employeeId)
                    ->sortBy(fn($a) => $this->getSectionPriority($a->section));

                foreach ($assignments as $assignment) {
                    if ($assignment->is_complete) continue;

                    $dependsOn = $this->getPreviousEmployeeSection($order, $employeeId, $assignment->section, $assignment->garments_assigned);
                    if ($dependsOn) {
                        $key = "{$order->id}|{$dependsOn}|{$assignment->garments_assigned}";
                        if (!$completedAssignments->has($key)) continue;
                    }

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

                    // Set ETA for the order
                    $order->eta_data = [
                        'section' => $assignment->section,
                        'garments' => $assignment->garments_assigned,
                        'total_time' => gmdate('H:i:s', $totalSeconds),
                        'expected_delivery' => $eta->toDateTimeString(),
                    ];

                    // Move cursor forward
                    $cursorTime = $eta->copy();

                    break; // only first valid assignment per order
                }
            }

        }

        // 📦 Paginate manually after enrichment
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

        foreach ($assignments as $assignment) {
            if ($type === 'progress') {
                $assignment->is_progress = !$assignment->is_progress;
                if (!$assignment->is_progress) {
                    $assignment->is_complete = 0;
                }
            } elseif ($type === 'complete' && $assignment->is_progress) {
                $assignment->is_complete = !$assignment->is_complete;
            }
            $assignment->save();
        }

        $this->confirmingStageUpdate = false;
        $this->checkAndAdvanceOrderStage($orderId);
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

        if ($nextStage && $order->{"need_".strtolower($nextStage)}) {
            $order->current_location = $nextStage;
        } else {
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

    private function getPreviousSection($section)
    {
        return match ($section) {
            'Embroidery' => 'Sewing',
            'Imprinting' => 'Embroidery',
            default => null,
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
