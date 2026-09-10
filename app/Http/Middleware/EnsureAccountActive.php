<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountActive
{
    /**
     * Jika user sudah login tapi status_account != 1, logout paksa + redirect ke login.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        if ($user && (int) ($user->status_account ?? 1) !== 1) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')->withErrors(['email' => 'Akun Anda dinonaktifkan HRD (status pending). NIK: '.($user->nik ?? '-').'. Hubungi HRD.']);
        }
        return $next($request);
    }
}
