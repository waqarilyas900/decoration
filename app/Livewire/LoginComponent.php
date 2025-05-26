<?php

namespace App\Livewire;

use Livewire\Component;

class LoginComponent extends Component
{
    public  $pin;

    

    public function render()
    {
        return view('livewire.login-component')->layout('components.layouts.full-width');
    }

    public function auth()
    {
        
        $this->validate([
            'pin' => 'required'
        ]);
        if(auth()->attempt(['email' => 'jeff@info.com','password' => $this->pin])) 
        {
            return redirect()->route('home');
        }
        else
        {
            $this->addError('pin', 'Wrong Pin');
        }
    }
}
