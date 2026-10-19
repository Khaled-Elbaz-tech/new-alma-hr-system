<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->with('error', 'Invalid credentials');
        }

        if (!$user->is_active) {
            return back()->with('error', 'Your account has been deactivated. Please contact the administrator.');
        }

        if (!Hash::check($request->password, $user->password)) {
            return back()->with('error', 'Invalid credentials');
        }

        Auth::login($user);

        $request->session()->regenerate();

        // Update last login
        $user->update(['last_login' => now()]);

        // Log activity
        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'login',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        // Log activity before logout
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'logout',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function showChangePasswordForm()
    {
        return view('auth.change-password');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/|confirmed',
        ], [
            'new_password.regex' => 'Password must contain at least: 1 uppercase, 1 lowercase, 1 number, and 1 special character (@$!%*?&)',
        ]);

        $user = auth()->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->with('error', 'Current password is incorrect');
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'password_change',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Password changed successfully. Please login with your new password.');
    }
}