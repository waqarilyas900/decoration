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
use Livewire\Component;

use Illuminate\Support\Facades\Mail;
use Livewire\WithPagination;
use Livewire\Attributes\Session;
use Livewire\Attributes\Url;

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
   
    public $employees = [];
    public function mount()
    {
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

        $orders = $query->paginate(20);

        $orders->getCollection()->transform(function ($order) {
            $totalSeconds = 0;
            $cursorTime = now()->copy(); // shared tracker for all assignments in this order
            $latestEnd = null;

            foreach ($order->assignments as $assignment) {
                $employee = $assignment->employee;

                if (
                    !$employee ||
                    !$employee->working_hours_start || !$employee->working_hours_end ||
                    !$employee->time_per_garment
                ) continue;

                $startHour = Carbon::createFromFormat('H:i:s', $employee->working_hours_start);
                $endHour = Carbon::createFromFormat('H:i:s', $employee->working_hours_end);
                $timePerGarment = CarbonInterval::createFromFormat('H:i:s', $employee->time_per_garment);

                if ($timePerGarment->totalSeconds <= 0 || $timePerGarment->totalSeconds > 8 * 3600) {
                    continue; // skip if invalid
                }

                $garments = $assignment->garments_assigned;
                $secondsRequired = $timePerGarment->totalSeconds * $garments;

                // Normalize cursor time per assignment
                $cursorTime = $this->normalizeStartTime($cursorTime, $startHour, $endHour);
                $endTime = $this->addWorkingTime($cursorTime->copy(), $secondsRequired, $startHour, $endHour);

                // update shared timer and stats
                $cursorTime = $endTime->copy();
                $totalSeconds += $secondsRequired;

                if (!$latestEnd || $endTime->gt($latestEnd)) {
                    $latestEnd = $endTime;
                }
            }

            $order->overall_eta = gmdate('H:i:s', $totalSeconds);
            $order->expected_delivery = $latestEnd ? $latestEnd : null;

            return $order;
        });

        return $orders;
    }

    protected function isWeekend(Carbon $date)
    {
        return $date->isSaturday() || $date->isSunday();
    }

    protected function normalizeStartTime(Carbon $dateTime, Carbon $startHour, Carbon $endHour)
    {
        if ($this->isWeekend($dateTime)) {
            $dateTime->addDay();
            while ($this->isWeekend($dateTime)) {
                $dateTime->addDay();
            }
            return $dateTime->setTimeFrom($startHour);
        }

        $workStart = $dateTime->copy()->setTimeFrom($startHour);
        $workEnd = $dateTime->copy()->setTimeFrom($endHour);

        if ($dateTime->lt($workStart)) return $workStart;
        if ($dateTime->gte($workEnd)) {
            $dateTime->addDay();
            while ($this->isWeekend($dateTime)) {
                $dateTime->addDay();
            }
            return $dateTime->setTimeFrom($startHour);
        }

        return $dateTime;
    }

    protected function addWorkingTime(Carbon $start, $seconds, Carbon $startHour, Carbon $endHour)
    {
        $current = $start->copy();
        $remaining = $seconds;

        while ($remaining > 0) {
            if ($this->isWeekend($current)) {
                $current->addDay()->setTimeFrom($startHour);
                continue;
            }

            $endOfDay = $current->copy()->setTimeFrom($endHour);
            $available = $current->diffInSeconds($endOfDay);
            $consume = min($available, $remaining);

            $current->addSeconds($consume);
            $remaining -= $consume;

            if ($remaining > 0) {
                $current->addDay()->setTimeFrom($startHour);
                while ($this->isWeekend($current)) {
                    $current->addDay();
                }
            }
        }

        return $current;
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
                'is_progress' => 0,
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
                'is_progress' => 0,
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
                'is_progress' => 0,
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
            OrderTrack::where('order_id', $order->id)->where('type', 3)->delete();
            $order->need_imprinting = 2;
            $order->imprinting_progress = 0;
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
    
    
}
