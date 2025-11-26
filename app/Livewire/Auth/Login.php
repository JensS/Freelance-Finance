<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.guest')]
#[Title('Login')]
class Login extends Component
{
    #[Validate('required|email')]
    public string $email = '';

    #[Validate('required')]
    public string $password = '';

    public bool $remember = false;

    public string $error = '';

    public function login(): mixed
    {
        $this->validate();

        // Ensure login is not rate limited
        $this->ensureIsNotRateLimited();

        if (Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            // Clear the rate limiter on successful login
            RateLimiter::clear($this->throttleKey());

            session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }

        // Increment rate limiter attempts
        RateLimiter::hit($this->throttleKey());

        $this->error = 'Die eingegebenen Anmeldedaten sind ungültig.';
        $this->password = '';

        return null;
    }

    /**
     * Ensure the login request is not rate limited.
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email).'|'.request()->ip());
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
