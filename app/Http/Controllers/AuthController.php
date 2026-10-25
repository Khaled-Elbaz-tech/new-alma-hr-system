<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
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

        // M12: one uniform message for every failure case — unknown user, wrong
        // password, and deactivated account are indistinguishable, so login
        // responses cannot be used to enumerate accounts or account states.
        // (Short-circuiting keeps Hash::check from running on a null user.)
        if (!$user || !$user->is_active || !Hash::check($request->password, $user->password)) {
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

        // H8: imported accounts must set a new password before continuing.
        // (Route name corrected from the patch note: the form route is
        // 'password.change', not 'change-password'.)
        if (!empty($user->must_change_password)) {
            return redirect()->route('password.change')
                ->with('info', 'Your account was created by import. Please set a new password before continuing.');
        }

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
            'must_change_password' => false,   // H8: clear the import flag
        ]);

        // M7: a password change must invalidate every OTHER session for this user.
        // logoutOtherDevices() handles drivers that support it (it needs the
        // AuthenticateSession middleware to take effect); the database-driver
        // purge below works regardless of that middleware, because this app's
        // default session driver is 'database' (config/session.php). Both are
        // attempted, and a failure of either must not roll back the password
        // change itself. The current session is logged out just below, as before.
        try {
            Auth::logoutOtherDevices($request->new_password);
        } catch (\Throwable $e) {
            // Fall through to the explicit session-row purge.
        }
        if (config('session.driver') === 'database') {
            try {
                DB::table(config('session.table', 'sessions'))
                    ->where('user_id', $user->id)
                    ->where('id', '!=', $request->session()->getId())
                    ->delete();
            } catch (\Throwable $e) {
                // Sessions table without a user_id column (non-default schema):
                // logoutOtherDevices() above remains the invalidation path.
            }
        }

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