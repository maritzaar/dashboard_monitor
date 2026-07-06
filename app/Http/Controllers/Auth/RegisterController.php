<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Role selection: first user in system or company email domain gets 'admin' role, others default to 'viewer'
        $isFirstUser = User::count() === 0;
        $isCompanyDomain = str_ends_with(strtolower($request->email), '@xyz.com');
        $role = ($isFirstUser || $isCompanyDomain) ? 'admin' : 'viewer';

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $role,
        ]);

        Auth::login($user);

        $welcomeMessage = $user->role === 'admin' 
            ? 'Akun berhasil dibuat. Selamat datang di sistem, ' . $user->name . '!'
            : 'Akun berhasil dibuat. Selamat datang di sistem, ' . $user->name . '!';

        return redirect()->route('monitoring.index')
            ->with('success', $welcomeMessage);
    }
}
