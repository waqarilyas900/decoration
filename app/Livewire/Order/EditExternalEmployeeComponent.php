<?php

namespace App\Livewire\Order;

use App\Models\Employee;
use App\Models\Order;
use App\Models\OrderAssignmentLog;
use App\Models\OrderLog;
use App\Models\OrderTrack;
use Livewire\Component;
use Livewire\Attributes\Validate;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class EditExternalEmployeeComponent extends Component
{
    #[Validate] 
    public $order_number = '';
    public $need_sewing;
    public $need_embroidery;
    public $need_imprinting;
    public $current_location;
    public $sewing_progress;
    public $imprinting_progress;
    public $embroidery_progress;
    public $created_by;
    public $getCreatedBy = [];
    public $orderId;
    protected $queryString = ['orderId'];
    public $order;
    public $confrmView;
    public $updated_by;
    public $removed_by;
    public $status;
    public $getReadyBy = [];
    public $getRemovedBy = [];
    public $employees = [];
    public $employeesCreated = [];
     public $external_employees = [];
    public $updateView = true;
    public $number_of_garments;
    public $is_priority;
    public $showSplitModal = false;
    public $splitSection = '';
    public $splitEntries = [];
    public $splitQuantitiesPattern = null;
    public $allSplitEntries = []; 
    public $manuallyOverriddenSections = [];
    public $assignments = [];
    public $isExternal = false;
    public $splitEntriesOthers = [];

   public function mount()
{
    $this->isExternal = auth()->user()->type == 2;

    $this->employees = Employee::where('type', 1)
        ->where('is_delete', 0)
        ->where('active', 1)
        ->orderBy('first_name', 'asc')
        ->get();

    $this->external_employees = Employee::where('type', 2)
        ->where('active', 1)
        ->where('is_delete', 0)
        ->orderBy('first_name', 'asc')
        ->get();

    $this->employeesCreated = Employee::where('type', 1)
        ->where('active', 1)
        ->orderBy('first_name', 'asc')
        ->get();

    $this->order = Order::with('assignments')->find($this->orderId);

    $this->allSplitEntries = [];

    foreach (['sewing', 'embroidery', 'imprinting'] as $section) {
        $assignments = $this->order->assignments->where('section', $section);

        if ($assignments->isNotEmpty()) {
            $this->allSplitEntries[$section] = $assignments->map(function ($assignment) {
                return [
                    'employee_id' => $assignment->employee_id,
                    'quantity' => $assignment->garments_assigned,
                ];
            })->toArray();
        }
    }

    $this->need_sewing = (bool) $this->order->need_sewing;
    $this->need_embroidery = (bool) $this->order->need_embroidery;
    $this->need_imprinting = (bool) $this->order->need_imprinting;
    $this->current_location = $this->order->current_location;
    $this->order_number = $this->order->order_number;
    $this->created_by = $this->order->created_by;
    $this->number_of_garments = $this->order->number_of_garments;
    $this->is_priority = $this->order->is_priority;
}

public function openSplitModal($section)
{
    $this->splitSection = $section;

    // Get logged-in user's employee_id, not the user id
    $employeeId = auth()->user()->employee_id;

    $this->splitEntries = [];

    if (isset($this->allSplitEntries[$section])) {
        // Filter only entries assigned to logged-in user's employee_id
        $this->splitEntries = collect($this->allSplitEntries[$section])
            ->where('employee_id', $employeeId)
            ->values()
            ->toArray();

        if (empty($this->splitEntries)) {
            $this->splitEntries = [['employee_id' => $employeeId, 'quantity' => '']];
        }
    } else {
        $this->splitEntries = [['employee_id' => $employeeId, 'quantity' => '']];
    }

    $this->showSplitModal = true;
}




     public function addSplitEntry()
    {
        $this->splitEntries[] = ['employee_id' => '', 'quantity' => ''];
    }

    public function removeSplitEntry($index)
    {
        unset($this->splitEntries[$index]);
        $this->splitEntries = array_values($this->splitEntries);
    }

    public function updatedSplitEntries($value, $name)
    {
        $this->validateSplitEntriesTotal();

        // Validate split matches initial pattern if set
        if ($this->splitQuantitiesPattern) {
            $this->validateSplitMatch();
        }
    }

    public function validateSplitEntriesTotal()
    {
        $totalAssigned = collect($this->splitEntries)->sum(fn($entry) => (int) $entry['quantity']);

        if ($totalAssigned > $this->number_of_garments) {
            $this->addError('splitEntries', 'Total assigned garments exceed the number of garments specified.');
        } else {
            $this->resetErrorBag('splitEntries');
        }
    }

    public function validateSplitMatch()
    {
        $currentQuantities = collect($this->splitEntries)->pluck('quantity')->map(fn($q) => (int) $q)->toArray();

        if ($currentQuantities !== $this->splitQuantitiesPattern) {
            $this->addError('splitEntries', 'Garment split quantities must match the first section\'s pattern.');
        } else {
            $this->resetErrorBag('splitEntries');
        }
    }

    public function saveSplitAssignments()
{
    $totalAssigned = collect($this->splitEntries)->sum(fn($entry) => (int) $entry['quantity']);

    if ($totalAssigned > $this->number_of_garments) {
        $this->addError('splitEntries', 'Total assigned garments exceed the number of garments specified.');
        return;
    }

    foreach ($this->splitEntries as $entry) {
        if (empty($entry['employee_id']) || empty($entry['quantity']) || $entry['quantity'] <= 0) {
            $this->addError('splitEntries', 'Each split must have a valid employee and a positive garment quantity.');
            return;
        }
    }

    $quantities = collect($this->splitEntries)
        ->pluck('quantity')
        ->map(fn($q) => (int) $q)
        ->toArray();

    if (count($quantities) > 1 && count(array_unique($quantities)) === 1) {
        $this->addError('splitEntries', 'Split quantities must not be equally divided.');
        return;
    }

    // Save the first-used pattern
    if (!$this->splitQuantitiesPattern) {
        $this->splitQuantitiesPattern = $quantities;
    }

    // Save entries for this section
    $this->allSplitEntries[$this->splitSection] = $this->splitEntries;

    $this->resetErrorBag('splitEntries');
    $this->showSplitModal = false;
}



    public function updatedNeedSewing($value)
    {
        if (!$value) {
            unset($this->allSplitEntries['Sewing']);

            if ($this->splitSection === 'Sewing') {
                $this->resetSplitModal();
            }

            $this->maybeResetSplitQuantitiesPattern();
        }
    }
    public function updatedNeedEmbroidery($value)
    {
        if (!$value) {
            unset($this->allSplitEntries['Embroidery']);

            if ($this->splitSection === 'Embroidery') {
                $this->resetSplitModal();
            }

            $this->maybeResetSplitQuantitiesPattern();
        }
    }

    public function updatedNeedImprinting($value)
    {
        if (!$value) {
            unset($this->allSplitEntries['Imprinting']);

            if ($this->splitSection === 'Imprinting') {
                $this->resetSplitModal();
            }

            $this->maybeResetSplitQuantitiesPattern();
        }
    }
    protected function maybeResetSplitQuantitiesPattern()
    {
        if (empty($this->allSplitEntries)) {
            $this->splitQuantitiesPattern = null;
        }
    }
    protected function resetSplitModal()
    {
        $this->showSplitModal = false;
        $this->splitSection = '';
        $this->splitEntries = [];
        $this->resetErrorBag('splitEntries');
    }
    public function render()
    {
        // $this->dispatch('sticky-header'); 
        return view('livewire.order.edit-external-employee-component');
    }
}
