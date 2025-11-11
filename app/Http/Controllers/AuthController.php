<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        // If already authenticated, redirect to correct dashboard
        if (Auth::check()) {
            $user = Auth::user();
            return $user->is_admin
                ? redirect()->route('admin.dashboard')
                : redirect()->route('users.dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'password' => 'required|string',
        ],
        
    );

        if (Auth::attempt($request->only('name', 'password'), $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            if ($user->is_admin) {
                return redirect()->route('admin.dashboard');
            }

            return redirect()->route('users.dashboard');
        }

        throw ValidationException::withMessages([
            'name' => ['خطأ في كلمة السر أو الٳسم'],
        ]);
    }


    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
