<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Employee as ModelsEmployee;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\WithPagination;

class ExternalEmployee extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $record;
    public $search;
    public $type = 'view';
    public $originalPin;
    public $time_per_garment_hours;
    public $time_per_garment_minutes;

    protected function rules()
    {
        $pinRule = $this->record->id
            ? 'nullable|digits:6|unique:employees,pin,' . $this->record->id
            : 'required|digits:6|unique:employees,pin';

        return [
            'record.first_name' => 'required',
            'record.last_name' => 'required',
            'record.pin' => $pinRule,
            'record.working_hours_start' => 'nullable|date_format:H:i',
            'record.working_hours_end' => 'nullable|date_format:H:i',
            'time_per_garment_hours' => 'nullable|integer|min:0',
            'time_per_garment_minutes' => 'nullable|integer|min:0|max:59',
        ];
    }


    public function mount()
    {
        $this->bindModel();
    }

    public function bindModel()
    {
        $this->record = new ModelsEmployee([
            'type' => 2,
            'time_per_garment' => '00:00'
        ]);

        $this->time_per_garment_hours = 0;
        $this->time_per_garment_minutes = 0;
    }

    public function getEmployees()
    {
        $query = ModelsEmployee::where('is_delete', 0)->where('type', 2);

        if (strlen($this->search) > 3) {
            $search = $this->search;
            $columns = ['first_name', 'last_name', 'pin'];
            $query->searchLike($columns, $search); // assumes a `searchLike` scope exists
        }

        return $query;
    }

    public function render()
    {
        return view('livewire.external-employee', [
            'employees' => $this->getEmployees()->paginate(10)
        ]);
    }

    public function save()
    {
        $this->validate();

        $hours = str_pad((int)$this->time_per_garment_hours, 2, '0', STR_PAD_LEFT);
        $minutes = str_pad((int)$this->time_per_garment_minutes, 2, '0', STR_PAD_LEFT);
        $this->record->time_per_garment = "$hours:$minutes";

        $this->record->type = 2;

        $plainPin = $this->record->pin;
        $hashedPin = null;

        if ($plainPin && strlen($plainPin) === 6 && is_numeric($plainPin)) {
            $hashedPin = Hash::make($plainPin);
            $this->record->pin = $hashedPin;
        } else {
            $this->record->pin = $this->originalPin;
        }

        $this->record->save();

        User::updateOrCreate(
            ['employee_id' => $this->record->id],
            [
                'name' => $this->record->first_name . ' ' . $this->record->last_name,
                'email' => null,
                'password' => $hashedPin ?? User::where('employee_id', $this->record->id)->value('password'),
                'type' => 2,
            ]
        );

        session()->flash('message', 'External employee has been ' . ($this->record->wasRecentlyCreated ? 'created' : 'updated') . '.');

        $this->bindModel();
        $this->type = 'view';
    }



    public function edit($id)
    {
        $this->record = ModelsEmployee::where('type', 2)->findOrFail($id);
        $this->originalPin = $this->record->pin;
        $this->record->pin = '';
        if (!empty($this->record->working_hours_start)) {
            $this->record->working_hours_start = \Carbon\Carbon::parse($this->record->working_hours_start)->format('H:i');
        }

        if (!empty($this->record->working_hours_end)) {
            $this->record->working_hours_end = \Carbon\Carbon::parse($this->record->working_hours_end)->format('H:i');
        }

        if (!empty($this->record->time_per_garment)) {
            [$h, $m] = explode(':', $this->record->time_per_garment);
            $this->time_per_garment_hours = (int)$h;
            $this->time_per_garment_minutes = (int)$m;
        } else {
            $this->time_per_garment_hours = 0;
            $this->time_per_garment_minutes = 0;
        }

        $this->type = 'add';
    }



    public function addReset()
    {
        $this->bindModel();
        $this->type = 'add';
    }

    public function deleteRecord($id)
    {
        ModelsEmployee::where('type', 2)->findOrFail($id)->update([
            'is_delete' => 1
        ]);
    }
}
