<?php

namespace App\Livewire\Order;

use App\Models\Employee;
use App\Models\Order;
use App\Models\OrderAssignmentLog;
use App\Models\OrderLog;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Validate;

class Create extends Component
{
    #[Validate] 
    public $order_number = '';
    public $need_sewing;
    public $need_embroidery;
    public $need_imprinting;
    public $current_location;
    public $created_by;
    public $getCreatedBy = [];
    public $employees = [];
    public $external_employees = [];
    public $number_of_garments;
    public $is_priority = false;
    public $showSplitModal = false;
    public $splitSection = '';
    public $splitEntries = [];
    public $splitQuantitiesPattern = null;
    public $allSplitEntries = [];
    public $isQuantityFromPattern = false;
    public $pendingOrdersPerEmployee = []; 
    public function mount()
    {
        $this->employees = Employee::where('type', 1)
        ->orderBy('first_name', 'asc')
        ->where('active', 1)
        ->where('is_delete', 0)
        ->get();

        $this->external_employees = Employee::where('type', 2) 
        ->where('active', 1)
        ->where('is_delete', 0)
        ->orderBy('first_name', 'asc')
        ->get();
    }

    public function getPendingOrdersCountPerEmployee()
    {
        return \App\Models\OrderAssignment::query()
            ->select('employee_id')
            ->selectRaw('COUNT(DISTINCT order_id) as pending_count')
            ->where('is_complete', 0)  // pending assignments only
            ->groupBy('employee_id')
            ->pluck('pending_count', 'employee_id')
            ->toArray();
    }



    public function rules()
    {
        return [
            'order_number' => 'required',
            'current_location' => 'required',
            'created_by' => 'required',
            'need_sewing' => 'nullable',
            'need_imprinting' => 'nullable',
            'need_embroidery' => 'nullable',
            'number_of_garments' => 'nullable|integer|min:1',
            'is_priority' => 'nullable|boolean', 
        ];
    }
    public function render()
    {
        
        return view('livewire.order.create');
    }
    public function save()
    {
        if (!$this->need_sewing && !$this->need_embroidery && !$this->need_imprinting) {
            $this->current_location = null;
        }

        $validated = $this->validate();
        $requiredSections = collect([
                'Sewing' => $this->need_sewing,
                'Embroidery' => $this->need_embroidery,
                'Imprinting' => $this->need_imprinting,
            ])->filter();

            foreach ($requiredSections as $section => $required) {
                $entries = $this->allSplitEntries[$section] ?? [];

                if (empty($entries)) {
                    $this->addError('splitEntries', "Please assign employees for {$section} section.");
                    return;
                }

                foreach ($entries as $entry) {
                    if (empty($entry['employee_id']) || empty($entry['quantity']) || $entry['quantity'] <= 0) {
                        $this->addError('splitEntries', "Each {$section} assignment must have a valid employee and quantity.");
                        return;
                    }
                }
            }

        $order = Order::forceCreate($validated);

        OrderLog::create([
            'title' => "Order has been created",
            'updated_by' => $this->created_by,
            'order_id' => $order->id,
        ]);

        if ($this->current_location) {
            OrderLog::create([
                'title' => "Current Location of order is " . $this->current_location,
                'updated_by' => $this->created_by,
                'order_id' => $order->id,
            ]);
        }

        if ($this->need_sewing) $order->need_sewing = 2;
        if ($this->need_embroidery) $order->need_embroidery = 2;
        if ($this->need_imprinting) $order->need_imprinting = 2;
        $order->save();

        // ✅ Save all split entries per section
        foreach ($this->allSplitEntries as $section => $entries) {
            foreach ($entries as $entry) {
                if (!empty($entry['employee_id']) && !empty($entry['quantity'])) {
                    $assignment = $order->assignments()->create([
                        'section' => $section,
                        'employee_id' => $entry['employee_id'],
                        'garments_assigned' => (int) $entry['quantity'],
                    ]);

                    // Log the assignment creation
                    OrderAssignmentLog::create([
                        'order_id' => $order->id,
                        'assignment_id' => $assignment->id,
                        'employee_id' => $entry['employee_id'],
                        'title' => "Assigned to $section",
                        'updated_by' => $this->created_by,
                        'section' => $section,
                        'garments_assigned' => (int) $entry['quantity'],
                        'status' => 'assigned',
                    ]);
                }
            }
        }   


        session()->flash('message', "Order number {$this->order_number} has been created.");

        // Reset but keep employees list loaded
        $this->resetExcept('employees', 'external_employees');
    }

    public function updatedCreatedBy()
    {
        $keyword = $this->created_by;
        $this->getCreatedBy = Order::where('created_by', 'like', '%' . $keyword . '%')->groupBy('created_by')->limit(5)->get();
    }

    public function openSplitModal($section)
    {
         $this->resetErrorBag();
        $this->splitSection = $section;
        $this->external_employees = Employee::where('type', 2)
        ->where('active', 1)
        ->where('is_delete', 0)
        ->where('department', $section)
        ->orderBy('first_name', 'asc')
        ->get();

        // Load pending order counts per employee for the current section
        $this->pendingOrdersPerEmployee = $this->getPendingOrdersCountPerEmployee($section);

        if (isset($this->allSplitEntries[$section])) {
            $this->splitEntries = $this->allSplitEntries[$section];
            $this->isQuantityFromPattern = false;
        } elseif ($this->splitQuantitiesPattern) {
            $this->splitEntries = collect($this->splitQuantitiesPattern)
                ->map(fn($qty) => ['employee_id' => '', 'quantity' => $qty])
                ->toArray();
            $this->isQuantityFromPattern = true;
        } else {
            $this->splitEntries = [['employee_id' => '', 'quantity' => '']];
            $this->isQuantityFromPattern = false;
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
        if ($totalAssigned !== (int) $this->number_of_garments) {
            $this->addError('splitEntries', 'Total assigned garments must exactly match the number of garments specified ('.$this->number_of_garments.').');
            return;
        }
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
        $quantities = collect($this->splitEntries)->pluck('quantity')->map(fn($q) => (int) $q)->toArray();
        // if (count($quantities) > 1 && count(array_unique($quantities)) === 1) {
        //     $this->addError('splitEntries', 'Split quantities must not be equally divided.');
        //     return;
        // }
        // Set pattern only once (first section)
        if (!$this->splitQuantitiesPattern) {
            $this->splitQuantitiesPattern = collect($this->splitEntries)
                ->pluck('quantity')
                ->map(fn($q) => (int) $q)
                ->toArray();
        }

        // Save this section’s entries
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

    
}
