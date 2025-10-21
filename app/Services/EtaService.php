<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\OrderAssignment;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Support\Collection;

class EtaService
{
    public function calculateEtas(?string $calculationTime = null): array
    {
        $calculationTime = $calculationTime ?? now()->toDateTimeString();

        // Only assignments for employees where is_delete = 0
        $employeeAssignments = OrderAssignment::with(['employee', 'order'])
            ->whereHas('employee', fn($q) => $q->where('is_delete', 0))
            ->whereHas('order', fn($q) => $q->where('status', 0))
            ->where('is_complete', false)
            ->get()
            ->groupBy('employee_id');

        $cursorTimes = [];
        $orderEtaTracker = [];

        // Calculate ETA per employee
        foreach ($employeeAssignments as $employeeId => $assignments) {
            $employee = $assignments->first()->employee;
            if (!$employee) continue;

            $startHour = Carbon::createFromFormat('H:i:s', $employee->working_hours_start);
            $endHour   = Carbon::createFromFormat('H:i:s', $employee->working_hours_end);
            $timePerGarment = CarbonInterval::createFromFormat('H:i:s', $employee->time_per_garment);

            $cursorTimes[$employeeId] = $this->normalizeStartTime(
                Carbon::parse($calculationTime),
                $startHour,
                $endHour
            );

            // Group assignments by order
            $groupedAssignments = $assignments->groupBy('order_id');

            foreach ($groupedAssignments as $orderId => $orderAssignments) {
                $maxEtaForOrder = null;

                foreach ($orderAssignments as $assignment) {
                    $eta = $cursorTimes[$employeeId]->copy();
                    $totalSeconds = $timePerGarment->totalSeconds * $assignment->garments_assigned;

                    $eta = $this->normalizeStartTime($eta, $startHour, $endHour);
                    $secondsLeft = $totalSeconds;

                    while ($secondsLeft > 0) {
                        if ($this->isWeekend($eta)) {
                            $eta->addDay()->setTimeFrom($startHour);
                            continue;
                        }

                        $endOfDay  = $eta->copy()->setTimeFrom($endHour);
                        $available = $eta->diffInSeconds($endOfDay);
                        $consume   = min($available, $secondsLeft);

                        $eta->addSeconds($consume);
                        $secondsLeft -= $consume;

                        if ($secondsLeft > 0) {
                            $eta->addDay()->setTimeFrom($startHour);
                            while ($this->isWeekend($eta)) {
                                $eta->addDay();
                            }
                        }
                    }

                    // Track the longest ETA within this order
                    if (!$maxEtaForOrder || $eta->greaterThan($maxEtaForOrder)) {
                        $maxEtaForOrder = $eta->copy();
                    }
                }

                // Set order final ETA
                if (!isset($orderEtaTracker[$orderId]) || $maxEtaForOrder->greaterThan($orderEtaTracker[$orderId])) {
                    $orderEtaTracker[$orderId] = $maxEtaForOrder->copy();
                }

                // Force employee cursor to wait until order is done
                $cursorTimes[$employeeId] = $orderEtaTracker[$orderId]->copy();
            }
        }

        // Compute overall breakdown
        $overallEtaBreakdown = $this->computeOverallBreakdown($orderEtaTracker);

        // Build employee free times
        $employeeFreeTimes = [];

        foreach ($employeeAssignments as $employeeId => $assignments) {
            $employee = $assignments->first()->employee;
            if (!$employee) continue;

            $latestEta = null;
            foreach ($assignments as $assignment) {
                $orderFinalEta = $orderEtaTracker[$assignment->order_id] ?? null;
                if ($orderFinalEta && (!$latestEta || $orderFinalEta->gt($latestEta))) {
                    $latestEta = $orderFinalEta;
                }
            }

            if ($latestEta) {
                $employeeFreeTimes[$employeeId] = [
                    'employee'     => $employee,
                    'free_at'      => $latestEta,
                    'personal_eta' => $cursorTimes[$employeeId] ?? null,
                ];
            }
        }

        // Add employees with no assignments (they are free immediately)
        $allEmployees = Employee::whereNotNull('department')
            ->where('active', 1)      // optional: only active employees
            ->where('is_delete', 0)   // exclude deleted employees
            ->get();

        foreach ($allEmployees as $emp) {
            if (!isset($employeeFreeTimes[$emp->id])) {
                $employeeFreeTimes[$emp->id] = [
                    'employee'     => $emp,
                    'free_at'      => now(),
                    'personal_eta' => null,
                ];
            }
        }

        return [
            'orderEtas'           => $orderEtaTracker,
            'overallEtaBreakdown' => $overallEtaBreakdown,
            'employeeFreeTimes'   => $employeeFreeTimes,
        ];
    }

    protected function computeOverallBreakdown(array $orderEtaTracker): array
    {
        $maxEta = collect($orderEtaTracker)->max();

        if (!$maxEta) {
            return ['readable' => null, 'date' => null];
        }

        $now = Carbon::now();
        $diffInDays = $now->diffInDays($maxEta);
        $weeks = floor($diffInDays / 7);
        $days = $diffInDays % 7;

        $parts = [];
        if ($weeks > 0) $parts[] = $weeks . ' ' . \Illuminate\Support\Str::plural('Week', $weeks);
        if ($days > 0 || empty($parts)) $parts[] = $days . ' ' . \Illuminate\Support\Str::plural('Day', $days);

        $last = array_pop($parts);
        $readable = count($parts) ? implode(', ', $parts) . ' and ' . $last : $last;

        return [
            'readable' => $readable,
            'date' => $maxEta->format('M d, Y'),
        ];
    }

    protected function getSectionPriority($section)
    {
        return match ($section) {
            'Sewing' => 1,
            'Embroidery' => 2,
            'Imprinting' => 3,
            default => 99,
        };
    }

    private function normalizeStartTime($start, $startHour, $endHour)
    {
        $startTime = $start->copy()->setTimeFrom($startHour);
        $endTime = $start->copy()->setTimeFrom($endHour);

        if ($start->lt($startTime)) {
            $start = $startTime;
        } elseif ($start->gte($endTime)) {
            $start = $startTime->addDay();
        }

        while ($this->isWeekend($start)) {
            $start->addDay()->setTimeFrom($startHour);
        }

        return $start;
    }

    private function isWeekend(Carbon $date)
    {
        return $date->isSaturday() || $date->isSunday();
    }

}
