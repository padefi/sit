<?php

namespace App\Http\Requests\Auth;

use App\Models\Users\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array {
        return [
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    public function messages(): array {
        return [
            'username.required' => 'El usuario es obligatorio.',
            'password.required' => 'La contraseña es obligatoria.',
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void {
        $this->ensureIsNotRateLimited();

        $user =  User::where('username', $this->only('username'))
            ->where('is_active', false)->first();

        if ($user) {
            throw ValidationException::withMessages([
                'message' => trans('Usuario bloqueado.')
            ]);
        }

        $credentials = $this->only('username', 'password');

        if (Auth::attempt($credentials, $this->boolean('remember'))) {
            if ($credentials['username'] === $credentials['password']) {
                Auth::logout();
                
                throw ValidationException::withMessages([
                    'message' => trans('Debe cambiar la contraseña.'),
                    'changePassword' => true,
                ]);
            }

            RateLimiter::hit($this->throttleKey());
            return;
        }

        RateLimiter::clear($this->throttleKey());

        throw ValidationException::withMessages([
            'message' => trans('Usuario y/o contraseña incorrectos.')
        ]);
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 3)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        $user =  User::where('username', $this->only('username'))->first();

        if ($user) {
            $user->update([
                'is_active' => false,
            ]);
        }

        throw ValidationException::withMessages([
            'message' => trans('Demasiados intentos de inicio de sesión. Usuario bloqueado.', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string {
        return Str::transliterate(Str::lower($this->input('username')) . '|' . $this->ip());
    }
}
