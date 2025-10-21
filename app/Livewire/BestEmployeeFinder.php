<?php

namespace App\Livewire;

use App\Models\Employee;
use App\Services\EtaService;
use Livewire\Component;

class BestEmployeeFinder extends Component
{
    public $quantity;
    public $selectedSections = [];
    public $bestEmployees = [];
    public string $title = 'ETA Generator';
    public $errorMessage = '';
    public $results = [];
    public $overallCompletion = '';

    protected EtaService $etaService;

    public function boot()
    {
        // ✅ Resolve EtaService from the service container
        $this->etaService = app(EtaService::class);
    }

    public function render()
    {
        return view('livewire.best-employee-finder');
    }

    public function generate()
    {
        $this->errorMessage = '';
        $this->bestEmployees = [];
        $this->results = [];

        if (empty($this->quantity) || $this->quantity <= 0) {
            $this->errorMessage = 'Please enter a valid quantity to generate ETA.';
            return;
        }

        if (empty($this->selectedSections)) {
            $this->errorMessage = 'Please select at least one section (Sewing, Embroidery, or Imprinting).';
            return;
        }

        // ✅ Call EtaService
        $this->results = $this->etaService->calculateEtas();

        $employeeFreeTimes = $this->results['employeeFreeTimes'] ?? [];

        if (empty($employeeFreeTimes)) {
            $this->errorMessage = 'No employees found with active assignments.';
            return;
        }

        foreach ($this->selectedSections as $section) {
            $best = collect($employeeFreeTimes)
                ->filter(fn ($data) => $data['employee']->department === $section)
                ->sortBy('free_at')
                ->first();

            if ($best) {
                // Calculate new ETA after adding new quantity
                $previousEta = $best['personal_eta'] ? $best['personal_eta']->copy() : null;
                $newEta = $best['free_at']->copy();
                $employee = $best['employee'];

                // Get time per garment for this employee
                $timePerGarment = $employee->time_per_garment ?? '00:00:00';
                $timeInterval = \Carbon\CarbonInterval::createFromFormat('H:i:s', $timePerGarment);
                $totalSeconds = $timeInterval->totalSeconds * $this->quantity;

                // Add time for new quantity, skipping weekends and outside working hours
                $startHour = \Carbon\Carbon::createFromFormat('H:i:s', $employee->working_hours_start);
                $endHour = \Carbon\Carbon::createFromFormat('H:i:s', $employee->working_hours_end);
                $eta = $newEta->copy();
                $secondsLeft = $totalSeconds;
                while ($secondsLeft > 0) {
                    // If weekend, skip to next working day
                    if ($eta->isSaturday() || $eta->isSunday()) {
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
                        while ($eta->isSaturday() || $eta->isSunday()) {
                            $eta->addDay();
                        }
                    }
                }

                $this->bestEmployees[$section] = [
                    'employee'      => $employee->first_name . ' ' . $employee->last_name,
                    'previous_eta'  => $previousEta ? $previousEta->format('M d, Y H:i') : 'N/A',
                    'new_eta'       => $eta->format('M d, Y H:i'),
                ];
            }
        }

        // After the foreach ($this->selectedSections as $section)
        if (!empty($this->bestEmployees)) {
            $latestEta = collect($this->bestEmployees)
                ->map(fn($info) => \Carbon\Carbon::parse($info['new_eta']))
                ->sortDesc()
                ->first();

            if ($latestEta) {
                $this->overallCompletion = $latestEta->format('M d, Y H:i');
            }
        }


    }

}
