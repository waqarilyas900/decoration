<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
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
            'pin' => 'required',
        ]);

        if (auth()->attempt(['email' => 'jeff@info.com', 'password' => $this->pin])) {
            return redirect()->route('home');
        }

        $usersType2 = User::where('type', 2)->get();

        foreach ($usersType2 as $user) {
            if (Hash::check($this->pin, $user->password)) {
                auth()->login($user);
                return redirect()->route('home');
            }
        }

        $this->addError('pin', 'Wrong Pin');
    }

   

}
