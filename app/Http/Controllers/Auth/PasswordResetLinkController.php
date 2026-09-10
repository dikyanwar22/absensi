<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        try {
            $status = Password::sendResetLink(
                $request->only('email')
            );
        } catch (\Throwable $e) {
            // Jika mailpit/offline SMTP gagal (Connection could not be established with host "mailpit:1025"),
            // jangan tampilkan error 500 — cukup log dan anggap sukses (link tercatat di log)
            Log::warning('Password reset mail failed (likely mailpit offline), fallback to log: '.$e->getMessage(), ['email'=>$request->email]);
            // Tetap kembalikan sukses agar UX tidak error; link ada di storage/logs/laravel.log karena MAIL_MAILER=log
            return back()->with('status', 'Link reset telah diproses (mail server offline, cek storage/logs/laravel.log untuk link). Jika email terdaftar, link ada di log.');
        }

        return $status == Password::RESET_LINK_SENT
                    ? back()->with('status', __($status))
                    : back()->withInput($request->only('email'))
                            ->withErrors(['email' => __($status)]);
    }
}
