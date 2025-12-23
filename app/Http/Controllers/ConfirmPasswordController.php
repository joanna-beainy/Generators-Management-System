<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ConfirmPasswordController extends Controller
{
    /**
     * Show the password confirmation page.
     */
    public function show(Request $request)
    {
        return view('auth.confirm-password');
    }

    /**
     * Handle the incoming password confirmation.
     */
    public function store(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ],[
            'password.required' => 'يرجى إدخال كلمة السر.',
        ]);


        // Check password matches the logged-in user's password
        if (! Hash::check($request->password, $request->user()->password)) {
            return back()->withErrors([
                'password' => 'كلمة المرور غير صحيحة.',
            ]);
        }

        // Password confirmed — store timestamp in session
        $request->session()->put('auth.password_confirmed_at', time());

        // Redirect to intended page
        return redirect()->intended();
    }
}
