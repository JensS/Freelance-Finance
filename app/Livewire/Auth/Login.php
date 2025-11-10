<?php

namespace App\Livewire\Auth;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.guest')]
#[Title('Login')]
class Login extends Component
{
    public string $password = '';

    public string $error = '';

    public function login()
    {
        $this->validate([
            'password' => 'required',
        ]);

        $correctPassword = config('app.password');

        if ($this->password === $correctPassword) {
            session()->put('authenticated', true);

            return redirect()->route('dashboard');
        }

        $this->error = 'Invalid password';
        $this->password = '';
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
