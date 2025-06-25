<?php

namespace App\Livewire\Order;

use App\Mail\ReadyEmail;
use App\Models\Employee;
use App\Models\Order;
use App\Models\OrderAssignment;
use App\Models\OrderLog;
use App\Models\OrderTrack;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

use Illuminate\Support\Facades\Mail;
use Livewire\WithPagination;
use Livewire\Attributes\Session;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;

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
    public $internal_employee;
    public $external_employee;
    public $employees = [];
    public $internalEmployees = [];
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

         $this->internalEmployees = Employee::where('type', 1)
        ->where('active', 1)
        ->where('is_delete', 0)
        ->orderBy('first_name', 'asc')
        ->get()
        ->mapWithKeys(function ($employee) {
            return ['emp' . $employee->id => $employee->first_name . ' ' . $employee->last_name];
        });
    }
    public function updatingSearch()
    {
        $this->resetPage();
    }
    public function updatedLocation()
    {

    }
    //  public function orders()
    // {
    //     $order = Order::where('status', 0);
    //     $user = auth()->user();

    //     if ($user->type == 2) {
    //         $employeeId = $user->employee_id;

    //         // Get all sections the employee is assigned to
    //         $assignedSections = \App\Models\OrderAssignment::where('employee_id', $employeeId)
    //             ->pluck('section')
    //             ->toArray();

    //         $order->where(function ($q) use ($employeeId, $assignedSections) {
    //             // Case 1: Show if current_location matches employee's assigned section
    //             $q->whereHas('assignments', function ($q2) use ($employeeId) {
    //                 $q2->where('employee_id', $employeeId);
    //             })->whereIn('current_location', $assignedSections);
    //         })->orWhere(function ($q) use ($employeeId) {
    //             // Case 2: Previous stage completed, employee assigned for next stage
    //             $q->whereHas('assignments', function ($q2) use ($employeeId) {
    //                 $q2->where('employee_id', $employeeId);
    //             })->where(function ($inner) {
    //                 $inner->where('need_sewing', 1)
    //                     ->orWhere('need_embroidery', 1)
    //                     ->orWhere('need_imprinting', 1);
    //             });
    //         });
    //     }

    //     if ($this->sort) {
    //         $order->orderBy($this->sort, $this->orderBy);
    //     } else {
    //         // $order->oldest();
    //         $order->orderByDesc('is_priority')  // Show priority orders on top
    //             ->orderBy('created_at', 'asc');
    //     }

    //     if ($this->location) {
    //         $order->where('current_location', $this->location);
    //     }

    //     if (strlen($this->search) > 3) {
    //         $search = $this->search;
    //         $columns = ['order_number', 'current_location'];
    //         $order->searchLike($columns, $search);
    //     }

    //     return $order;
    // }
    // public function orders()
    // {
    //     $query = Order::with('assignments.employee')->where('status', 0);
    //     $user = auth()->user();

    //     if ($user->type == 2) {
    //         $employeeId = $user->employee_id;

    //         $assignedSections = \App\Models\OrderAssignment::where('employee_id', $employeeId)
    //             ->pluck('section')
    //             ->toArray();

    //         $query->where(function ($q) use ($employeeId, $assignedSections) {
    //             $q->whereHas('assignments', fn($q2) => $q2->where('employee_id', $employeeId))
    //             ->whereIn('current_location', $assignedSections);
    //         })->orWhere(function ($q) use ($employeeId) {
    //             $q->whereHas('assignments', fn($q2) => $q2->where('employee_id', $employeeId))
    //             ->where(function ($inner) {
    //                 $inner->where('need_sewing', 1)
    //                         ->orWhere('need_embroidery', 1)
    //                         ->orWhere('need_imprinting', 1);
    //             });
    //         });
    //     }

    //     // Sorting
    //     if ($this->sort) {
    //         $query->orderBy($this->sort, $this->orderBy);
    //     } else {
    //         $query->orderByDesc('is_priority')->orderBy('created_at', 'asc');
    //     }

    //     if ($this->location) {
    //         $query->where('current_location', $this->location);
    //     }

    //     if (strlen($this->search) > 3) {
    //         $query->searchLike(['order_number', 'current_location'], $this->search);
    //     }

    //     $orders = $query->paginate(20);

      

    //     $employeeCursors = [];

    //     $orders->getCollection()
    //     ->sortBy([
    //         fn($o) => $o->is_priority ? 0 : 1,       // priority first
    //         fn($o) => $o->created_at->timestamp      // earlier created first
    //     ])
    //     ->values() // important to reindex after sortBy
    //     ->transform(function ($order) use (&$employeeCursors) {
    //         $latestEnd = null;

    //         $incompleteAssignments = $order->assignments->filter(fn($a) => !$a->is_complete);

    //         if ($incompleteAssignments->isEmpty()) {
    //             $order->expected_delivery = null;
    //             return $order;
    //         }

    //         foreach ($incompleteAssignments as $assignment) {
    //             $employee = $assignment->employee;
    //             if (!$employee) continue;

    //             $employeeId = $employee->id;

    //             $startHour = Carbon::createFromFormat('H:i:s', $employee->working_hours_start);
    //             $endHour = Carbon::createFromFormat('H:i:s', $employee->working_hours_end);
    //             $timePerGarment = CarbonInterval::createFromFormat('H:i:s', $employee->time_per_garment);

    //             if ($timePerGarment->totalSeconds <= 0) continue;

    //             $garments = $assignment->garments_assigned;
    //             $seconds = $timePerGarment->totalSeconds * $garments;

    //             // Determine start time (respecting last task of this employee)
    //             $start = $employeeCursors[$employeeId] ?? now();
    //             $start = $this->normalizeStartTime($start, $startHour, $endHour);

    //             // Calculate end time with working hour logic
    //             $end = $this->addWorkingTime($start->copy(), $seconds, $startHour, $endHour);

    //             // Update cursor
    //             $employeeCursors[$employeeId] = $end;

    //             // Track latest end time among all assignments
    //             if (!$latestEnd || $end->gt($latestEnd)) {
    //                 $latestEnd = $end;
    //             }
    //         }

    //         $order->expected_delivery = $latestEnd;
    //         return $order;
    //     });

    //     return $orders;
    // }
    public function orders()
    {
        $query = Order::with('assignments.employee')->where('status', 0);
        $user = auth()->user();

        if ($user->type == 2) {
            $employeeId = $user->employee_id;

            $assignedSections = \App\Models\OrderAssignment::where('employee_id', $employeeId)
                ->pluck('section')
                ->toArray();

            $query->where(function ($q) use ($employeeId, $assignedSections) {
                $q->whereHas('assignments', fn($q2) => $q2->where('employee_id', $employeeId))
                ->whereIn('current_location', $assignedSections);
            })->orWhere(function ($q) use ($employeeId) {
                $q->whereHas('assignments', fn($q2) => $q2->where('employee_id', $employeeId))
                ->where(function ($inner) {
                    $inner->where('need_sewing', 1)
                            ->orWhere('need_embroidery', 1)
                            ->orWhere('need_imprinting', 1);
                });
            });
        }
            // Internal employee filtering (created_by column)
            if ($this->internal_employee) {
                $internalId = ltrim($this->internal_employee, 'emp');

                $query->where(function ($q) use ($internalId) {
                    $q->where('created_by', $internalId)
                    ->orWhereHas('logs', function ($logQuery) use ($internalId) {
                        $logQuery->where('updated_by', $internalId);
                    });
                });
            }

            // External employee filtering via order_assignments
            if ($this->external_employee) {
                $externalId = ltrim($this->external_employee, 'emp');
                $query->whereHas('assignments', function ($q) use ($externalId) {
                    $q->where('employee_id', $externalId);
                });
            }
        // Sorting
        if ($this->sort) {
            $query->orderBy($this->sort, $this->orderBy);
        } else {
            $query->orderByDesc('is_priority')->orderBy('created_at', 'asc');
        }

        if ($this->location) {
            $query->where('current_location', $this->location);
        }

        if (strlen($this->search) > 3) {
            $query->searchLike(['order_number', 'current_location'], $this->search);
        }


        $startTime = microtime(true);

        $orders = $query->paginate(20);

        $employeeCursors = [];

        $paginatedOrderIds = $orders->pluck('id');

        $fetchStart = microtime(true);

        $allRelevantOrders = Order::with('assignments.employee')
            ->whereIn('id', $paginatedOrderIds)
            ->get()
            ->sortBy([
                fn($o) => $o->is_priority ? 0 : 1,
                fn($o) => $o->created_at->timestamp
            ])
            ->values();


        $calculationStart = microtime(true);

        foreach ($allRelevantOrders as $order) {
            $incompleteAssignments = $order->assignments->filter(fn($a) => !$a->is_complete);
            if ($incompleteAssignments->isEmpty()) continue;

            $latestEnd = null;

            foreach ($incompleteAssignments as $assignment) {
                $employee = $assignment->employee;
                if (!$employee) continue;

                $employeeId = $employee->id;

                $startHour = Carbon::createFromFormat('H:i:s', $employee->working_hours_start);
                $endHour = Carbon::createFromFormat('H:i:s', $employee->working_hours_end);
                $timePerGarment = CarbonInterval::createFromFormat('H:i:s', $employee->time_per_garment);

                if ($timePerGarment->totalSeconds <= 0) continue;

                $garments = $assignment->garments_assigned;
                $seconds = $timePerGarment->totalSeconds * $garments;

                $start = $employeeCursors[$employeeId] ?? now();
                $start = $this->normalizeStartTime($start, $startHour, $endHour);

                $end = $this->addWorkingTime($start->copy(), $seconds, $startHour, $endHour);

                $employeeCursors[$employeeId] = $end;

                if (!$latestEnd || $end->gt($latestEnd)) {
                    $latestEnd = $end;
                }
            }

            $order->expected_delivery = $latestEnd;
        }


        $transformStart = microtime(true);

        $orders->getCollection()->transform(function ($order) use ($allRelevantOrders) {
            $matchingOrder = $allRelevantOrders->firstWhere('id', $order->id);
            if ($matchingOrder) {
                $order->expected_delivery = $matchingOrder->expected_delivery;
            }
            return $order;
        });


        $endTime = microtime(true);

        return $orders;


    }

    private function normalizeStartTime($start, $startHour, $endHour)
    {
        $startTime = $start->copy()->setTimeFrom($startHour);
        $endTime = $start->copy()->setTimeFrom($endHour);
        
        // If start is before working hours, move to start of working hours
        if ($start->lt($startTime)) {
            return $startTime;
        }
        
        // If start is after working hours, move to next day's start
        if ($start->gt($endTime)) {
            return $startTime->addDay();
        }
        
        return $start;
    }

    // public function addWorkingTime(Carbon $start, int $secondsToAdd, Carbon $startHour, Carbon $endHour): Carbon
    // {
    //     $current = $start->copy();

    //     while ($secondsToAdd > 0) {
    //         if ($current->isSunday()) {
    //             $current->addDay()->setTimeFrom($startHour);
    //             continue;
    //         }

    //         $workDayStart = $current->copy()->setTimeFrom($startHour);
    //         $workDayEnd = $current->copy()->setTimeFrom($endHour);

    //         // If before work hours, start at work start
    //         if ($current->lt($workDayStart)) {
    //             $current = $workDayStart;
    //         }

    //         // If after work hours, skip to next day
    //         if ($current->gte($workDayEnd)) {
    //             $current->addDay()->setTimeFrom($startHour);
    //             continue;
    //         }

    //         // Calculate available time today
    //         $availableToday = $workDayEnd->diffInSeconds($current);

    //         $secondsToWork = min($availableToday, $secondsToAdd);
    //         $current->addSeconds($secondsToWork);
    //         $secondsToAdd -= $secondsToWork;
    //     }

    //     return $current;
    // }
    public function addWorkingTime(Carbon $start, int $secondsToAdd, Carbon $startHour, Carbon $endHour): Carbon
    {
        $workDaySeconds = $endHour->diffInSeconds($startHour);
        $current = $start->copy();

        while ($secondsToAdd > 0) {
            // Skip Sundays
            if ($current->isSunday()) {
                $current->addDay()->setTimeFrom($startHour);
                continue;
            }

            $workDayStart = $current->copy()->setTimeFrom($startHour);
            $workDayEnd = $current->copy()->setTimeFrom($endHour);

            // If before working hours, move to workDayStart
            if ($current->lt($workDayStart)) {
                $current = $workDayStart;
            }

            // If after working hours, go to next workday
            if ($current->gte($workDayEnd)) {
                $current->addDay()->setTimeFrom($startHour);
                continue;
            }

            // Seconds left today
            $availableToday = $workDayEnd->diffInSeconds($current);

            if ($secondsToAdd <= $availableToday) {
                return $current->addSeconds($secondsToAdd); // done
            }

            // Use up the day, go to next day
            $secondsToAdd -= $availableToday;
            $current = $current->copy()->addDay()->setTimeFrom($startHour);
        }

        return $current;
    }


    protected function isWeekend(Carbon $date)
    {
        return $date->isSaturday() || $date->isSunday();
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
            'orders' => $this->orders()
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

            OrderAssignment::where('order_id', $order->id)
            ->where('section', 'Sewing')
            ->update([
                'is_progress' => 1,
                'is_complete' => 1
            ]);

            OrderTrack::forceCreate([
                'order_id' => $order->id,
                'type' => 1,
                'status' => 1,
            ]);
        }
        else
        {
            OrderAssignment::where('order_id', $order->id)
            ->where('section', 'Sewing')
            ->update([
                // 'is_progress' => 0,
                'is_complete' => 0
            ]);

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
            OrderAssignment::where('order_id', $order->id)
            ->where('section', 'Sewing')
            ->update([
                'is_progress' => 1,
            ]);
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
             OrderAssignment::where('order_id', $order->id)
            ->where('section', 'Sewing')
            ->update([
                'is_progress' => 0,
            ]);
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
            OrderAssignment::where('order_id', $order->id)
            ->where('section', 'Embroidery')
            ->update([
                'is_progress' => 1,
                'is_complete' => 1
            ]);
            OrderTrack::forceCreate([
                'order_id' => $order->id,
                'type' => 2,
                'status' => 1,
            ]);
           
        }
        else
        {
            OrderAssignment::where('order_id', $order->id)
            ->where('section', 'Embroidery')
            ->update([
                // 'is_progress' => 0,
                'is_complete' => 0
            ]);

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
             OrderAssignment::where('order_id', $order->id)
            ->where('section', 'Embroidery')
            ->update([
                'is_progress' => 1,
            ]);
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
            OrderAssignment::where('order_id', $order->id)
            ->where('section', 'Embroidery')
            ->update([
                'is_progress' => 0,
            ]);
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
            OrderAssignment::where('order_id', $order->id)
            ->where('section', 'Imprinting')
            ->update([
                'is_progress' => 1,
                'is_complete' => 1
            ]);
            OrderTrack::forceCreate([
                'order_id' => $order->id,
                'type' => 3,
                'status' => 1,
            ]);
        }
        else
        {
            OrderAssignment::where('order_id', $order->id)
            ->where('section', 'Imprinting')
            ->update([
                // 'is_progress' => 0,
                'is_complete' => 0
            ]);
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
            OrderAssignment::where('order_id', $order->id)
            ->where('section', 'Imprinting')
            ->update([
                'is_progress' => 1,
            ]);
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
             OrderAssignment::where('order_id', $order->id)
            ->where('section', 'Imprinting')
            ->update([
                'is_progress' => 0,
            ]);
            OrderTrack::where('order_id', $order->id)->where('type', 3)->delete();
            $order->need_imprinting = 2;
            $order->imprinting_progress = 0;
            $order->update();
        }  
    }
    public function updateLocation($orderId, $updated_by, $selectedText)
    {
        $order = Order::find($orderId);
        if($order){
            $order->assignments()->update([
            'location' => null,
            ]);
        }
        $location = $order->current_location;
        $order->current_location=$selectedText;
        $order->update();
        OrderLog::forceCreate([
            'title' => "Location changed from ".$location. " to $selectedText",
            'updated_by' => $updated_by,
            'order_id' => $order->id
        ]);
    }
    #[On('fetchAssignedEmployees')]
    public function fetchAssignedEmployees($orderId, $section, $currentLocation)
    {

        $assigned = Employee::whereHas('assignments', function ($q) use ($orderId, $section, $currentLocation) {
                $q->where('order_id', $orderId)
                ->where('section', $section)
                ->where(function ($sub) use ($currentLocation) {
                    $sub->whereNull('location')
                        ->orWhere('location', $currentLocation);
                });
            })
            ->where('type', 2)
            ->where('active', 1)
            ->where('is_delete', 0)
            ->get();

        $assignedNames = $assigned->map(function ($e) {
            return trim("{$e->first_name} {$e->last_name}");
        })->toArray();

        $this->dispatch('assigned-employees-loaded', assigned: $assignedNames);
    } 
}
