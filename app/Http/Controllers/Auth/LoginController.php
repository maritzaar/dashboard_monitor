<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        // (not requiring @email format)
        $credentials = $request->validate([
            'email' => ['required', 'string'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user();
            // Send welcome notification
            $user->notify(new \App\Notifications\WelcomeNotification());

            return redirect()->intended(route('monitoring.index'))
                ->with('success', __('Selamat datang kembali, :name!', ['name' => $user->name]));
        }

        return back()->withErrors([
            'email' => __('Email atau kata sandi yang Anda masukkan salah.'),
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', __('Anda telah berhasil keluar dari sistem.'));
    }
}
