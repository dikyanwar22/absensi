<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $login = $this->input('email');
        $password = $this->input('password');
        $isEmail = filter_var($login, FILTER_VALIDATE_EMAIL) !== false;

        // Jika login pakai email: email TIDAK uniq, bisa ada >1 user dengan email sama.
        // Hanya user dengan status_account=1 yang boleh login (sesuai request).
        if ($isEmail) {
            $candidates = \App\Models\User::where('email', $login)->get();
            if ($candidates->isEmpty()) {
                RateLimiter::hit($this->throttleKey());
                throw ValidationException::withMessages(['email' => trans('auth.failed')]);
            }
            // Jika semua akun dengan email ini pending/nonaktif
            $active = $candidates->where('status_account', 1);
            if ($active->isEmpty()) {
                $firstNik = $candidates->first()->nik ?? '-';
                RateLimiter::hit($this->throttleKey());
                throw ValidationException::withMessages([
                    'email' => 'Akun dengan email ini belum diaktifkan HRD (status pending). NIK: ' . $firstNik . '. Hubungi HRD untuk aktivasi.',
                ]);
            }
            // Coba cocokkan password di antara akun aktif dengan email sama
            $matchedUser = null;
            foreach ($active as $candidate) {
                if (Hash::check($password, $candidate->password)) {
                    $matchedUser = $candidate;
                    break;
                }
            }
            if (!$matchedUser) {
                // Password tidak cocok untuk akun aktif manapun -> cek apakah ada akun pending dengan password cocok untuk pesan lebih jelas
                foreach ($candidates->where('status_account', 0) as $pending) {
                    if (Hash::check($password, $pending->password)) {
                        RateLimiter::hit($this->throttleKey());
                        throw ValidationException::withMessages([
                            'email' => 'Password cocok tapi akun belum diaktifkan HRD (NIK: ' . $pending->nik . ' status pending). Gunakan akun aktif atau hubungi HRD.',
                        ]);
                    }
                }
                RateLimiter::hit($this->throttleKey());
                throw ValidationException::withMessages(['email' => trans('auth.failed')]);
            }
            // Login manual ke user yang cocok (aktif)
            Auth::login($matchedUser, $this->boolean('remember'));
            RateLimiter::clear($this->throttleKey());
            return;
        }

        // Login pakai NIK (9 digit angka, uniq)
        $userCheck = \App\Models\User::where('nik', $login)->first();
        if ($userCheck && (int) $userCheck->status_account !== 1) {
            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages([
                'email' => 'Akun Anda belum diaktifkan HRD (status pending). NIK: ' . $userCheck->nik . '. Hubungi HRD untuk aktivasi.',
            ]);
        }

        if (! Auth::attempt(['nik' => $login, 'password' => $password], $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages(['email' => trans('auth.failed')]);
        }

        $user = Auth::user();
        if ($user && (int) $user->status_account !== 1) {
            Auth::logout();
            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages([
                'email' => 'Akun Anda belum diaktifkan HRD (status pending). NIK: ' . ($user->nik ?? '-') . '. Hubungi HRD untuk aktivasi.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

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
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
