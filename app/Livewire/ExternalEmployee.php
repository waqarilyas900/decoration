<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Employee as ModelsEmployee;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\WithPagination;

class ExternalEmployee extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $record;
    public $search;
    public $type = 'view';
    public $user_password;
    public $time_per_garment_hours;
    public $time_per_garment_minutes;

    protected function rules()
    {
        $mustRequirePassword = !$this->record->id || !$this->userHasPassword();

        return [
            'record.first_name' => 'required',
            'record.last_name' => 'required',
            'record.department' => 'required',
            'record.working_hours_start' => 'required|date_format:H:i',
            'record.working_hours_end' => 'required|date_format:H:i',
            'time_per_garment_hours' => 'required|integer|min:0',
            'time_per_garment_minutes' => 'required|integer|min:0|max:59',
            'user_password' => [
                $mustRequirePassword ? 'required' : 'nullable',
                'min:6',
            ],
        ];
    }

    protected function userHasPassword(): bool
    {
        return User::where('employee_id', $this->record->id)
            ->whereNotNull('password')
            ->exists();
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
        $this->user_password = null;
    }

    public function getEmployees()
    {
        $query = ModelsEmployee::where('is_delete', 0)->where('type', 2);

        if (strlen($this->search) > 2) {
            $search = $this->search;
            $columns = ['first_name', 'last_name'];
            $query->searchLike($columns, $search);
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

        // Validation: End time must be after start time
        if (strtotime($this->record->working_hours_start) >= strtotime($this->record->working_hours_end)) {
            $this->addError('record.working_hours_end', 'End time must be after start time.');
            return;
        }

        // Validation: Time per garment must be > 0
        if (
            (int) $this->time_per_garment_hours === 0 &&
            (int) $this->time_per_garment_minutes === 0
        ) {
            $this->addError('time_per_garment_minutes', 'Time per garment must be greater than 0.');
            return;
        }

        // Format and store time per garment
        $hours = str_pad((int) $this->time_per_garment_hours, 2, '0', STR_PAD_LEFT);
        $minutes = str_pad((int) $this->time_per_garment_minutes, 2, '0', STR_PAD_LEFT);
        $this->record->time_per_garment = "$hours:$minutes";
        $this->record->type = 2;
        $this->record->save();

        // Prepare user data
        $userData = [
            'name' => $this->record->first_name . ' ' . $this->record->last_name,
            'email' => null,
            'type' => 2,
        ];

        // Check for password uniqueness across all users
        if (!empty($this->user_password)) {
            $existingUser = User::where('employee_id', $this->record->id)->first();

            // Block if same as current user password
            if ($existingUser && $existingUser->password && Hash::check($this->user_password, $existingUser->password)) {
                $this->addError('user_password', 'The new password must be different from your current password.');
                return;
            }

            // Block if password matches any other user's password
            $matchingOtherUser = User::whereNotNull('password')->get()
                ->filter(fn($user) =>
                    $user->employee_id !== $this->record->id &&
                    Hash::check($this->user_password, $user->password)
                )->first();

            if ($matchingOtherUser) {
                $this->addError('user_password', 'Please choose a different password.');
                return;
            }

            // Hash and store the new password
            $userData['password'] = Hash::make($this->user_password);
        }

        // Save or update user
        User::updateOrCreate(
            ['employee_id' => $this->record->id],
            $userData
        );

        session()->flash('message', 'External employee has been ' . ($this->record->wasRecentlyCreated ? 'created' : 'updated') . '.');

        $this->bindModel();
        $this->type = 'view';
    }


    public function edit($id)
    {
        $this->record = ModelsEmployee::where('type', 2)->findOrFail($id);

        if (!empty($this->record->working_hours_start)) {
            $this->record->working_hours_start = \Carbon\Carbon::parse($this->record->working_hours_start)->format('H:i');
        }

        if (!empty($this->record->working_hours_end)) {
            $this->record->working_hours_end = \Carbon\Carbon::parse($this->record->working_hours_end)->format('H:i');
        }

        if (!empty($this->record->time_per_garment)) {
            [$h, $m] = explode(':', $this->record->time_per_garment);
            $this->time_per_garment_hours = (int) $h;
            $this->time_per_garment_minutes = (int) $m;
        } else {
            $this->time_per_garment_hours = 0;
            $this->time_per_garment_minutes = 0;
        }

        $this->user_password = null;
        $this->type = 'add';
    }

    public function addReset()
    {
        $this->bindModel();
        $this->type = 'add';
    }

    public function deleteRecord($id)
    {
        $employee = ModelsEmployee::where('id', $id)->where('type', 2)->first();

        if (!$employee) {
            abort(404, 'Employee not found or not of type 2');
        }

        $employee->update([
            'is_delete' => 1
        ]);
    }
}
