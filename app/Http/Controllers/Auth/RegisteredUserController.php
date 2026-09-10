<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Employee;
use App\Models\Shift;
use App\Models\OfficeLocation;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            // email boleh duplikat hanya jika yang lama status_account=0, jika masih 1 tidak boleh daftar
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'password' => ['required', 'confirmed', 'string'],
        ]);

        // Jika email sudah pernah ada dan masih aktif (status_account=1) → tolak daftar
        if (User::where('email', $request->email)->where('status_account', 1)->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'email' => 'Email sudah terdaftar dan masih aktif (status_account=1). Tidak bisa daftar lagi. Hanya jika akun lama sudah nonaktif/pending (0) baru bisa daftar dengan email yang sama (NIK baru akan dibuat).',
            ]);
        }

        $nik = $this->generateUniqueNik();

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'nik' => $nik,
            'password' => Hash::make($request->password),
            'role' => 'staff',
            'status_account' => 0, // pending menunggu ACC HRD
        ]);

        // assign spatie role agar middleware role konsisten
        try {
            if (method_exists($user, 'assignRole')) {
                $user->assignRole('staff');
            }
        } catch (\Throwable $e) {}

        // auto-create employee agar $employee tidak null (hindari "Attempt to read property shift on null" di employee/home)
        try {
            $shiftId = Shift::first()?->id;
            $officeId = OfficeLocation::where('is_active', true)->first()?->id ?? OfficeLocation::first()?->id;
            Employee::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'shift_id' => $shiftId,
                    'office_location_id' => $officeId,
                    'join_date' => now()->toDateString(),
                    'employment_status' => 'kontrak',
                    'is_active' => true,
                ]
            );
        } catch (\Throwable $e) {}

        event(new Registered($user));

        // Jangan auto-login: akun pending harus di-ACC HRD (status_account=1) baru bisa login
        return redirect()->route('login')->with('status', "Akun berhasil dibuat! NIK Anda: {$nik}. Akun menunggu persetujuan HRD (status pending). Anda akan bisa login setelah HRD mengaktifkan akun.");
    }

    /**
     * Generate NIK unik: 9 digit angka semua, maks 9, uniq (contoh: 150019941)
     * Sesuai request: email boleh duplikat, NIK yang uniq 9 angka.
     */
    private function generateUniqueNik(): string
    {
        for ($attempt = 0; $attempt < 30; $attempt++) {
            // 9 digit angka semua, tidak diawali 0 agar tetap 9 digit
            $candidate = (string) random_int(100000000, 999999999);
            if (!User::where('nik', $candidate)->exists()) {
                return $candidate;
            }
        }
        // fallback deterministik berdasar id + microtime
        $base = (int) (microtime(true) * 1000) % 900000000 + 100000000;
        $candidate = (string) $base;
        $suffix = 0;
        while (User::where('nik', $candidate)->exists()) {
            $candidate = (string) (100000000 + (($base + ++$suffix) % 900000000));
        }
        return $candidate;
    }
}
