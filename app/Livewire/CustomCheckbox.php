<?php
namespace App\Livewire;

use Livewire\Component;

class CustomCheckbox extends Component
{
    public $isChecked;
    public $record; // assuming $record is the entity associated with each row

    public function mount($isChecked, $record)
    {
        $this->isChecked = $isChecked;
        $this->record = $record;
    }

    public function toggleCheckbox()
    {
        // Confirmation logic can be handled here or via Alpine.js
        // Update the checkbox state in the database if confirmed
    }

    public function render()
    {
        return view('livewire.custom-checkbox');
    }
}
