<?php

namespace App\Livewire;

use App\Models\Employee as ModelsEmployee;
use Livewire\Component;
use Livewire\WithPagination;
class Employee extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $record;
    public $search;
    public $type = 'view';
    protected function rules()
    {
        return [
            'record.type' => 'required',
            'record.first_name' => 'required',
            'record.last_name' => 'required',
            'record.email' => $this->record['type'] == 2 ? 'nullable' :  'required|email|unique:employees,email,'. $this->record->id ?? null,
        ];
    }
    public function getEmployees()
    {
        $queyr = ModelsEmployee::where('is_delete', 0)->where('type', 1);
        if (strlen($this->search) > 2) {
            $search = $this->search;
            $columns = ['first_name', 'last_name', 'email'];
            $queyr->searchLike($columns, $search);
        }
        return $queyr;

    }
    public function mount()
    {
       $this->bindModel();
    }   
    public function bindModel()
    {
        $this->record = new ModelsEmployee([
            'type' => 1
        ]);
    }
    public function render()
    {
        return view('livewire.employee', [
            'employees' => $this->getEmployees()->paginate(10)
        ]);
    }
    public function save()
    {
        $this->record->type = 1; 
        $this->validate();
       

        $message = $this->record->id ? 'updated' : 'created';

        $this->record->save();

        session()->flash('message', "Employee has been $message.");

        $this->bindModel();
        $this->type = 'view';
    }

    public function edit($id)
    {
        $this->record = ModelsEmployee::where('type', 1)->find($id);
        $this->type = 'add';
    }
    public function addReset()
    {
        $this->bindModel();
        $this->type = 'add';
    }

    public function deleteRecord($id)
    {
        ModelsEmployee::where('type', 2)->find($id)->update([
            'is_delete' => 1
        ]);
    }

}
