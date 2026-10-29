<?php

namespace App\Http\Controllers\Auth;

use App\Models\ActivityLog;
use App\Models\LoginHistory;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Show the login page.
     */
    public function show()
    {
        if (Auth::check()) {
            return redirect()->route(
                $this->dashboardRoute(Auth::user())
            );
        }

        return view('auth.login');
    }

    /**
     * Authenticate the user and redirect according to role.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt(
            $credentials,
            $request->boolean('remember')
        )) {
            throw ValidationException::withMessages([
                'email' => 'Invalid email or password.',
            ]);
        }

        $request->session()->regenerate();

        $user = Auth::user();
        $loginHistory = LoginHistory::create([
            'user_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'logged_in_at' => now(),
        ]);
        $request->session()->put('login_history_id', $loginHistory->id);
        ActivityLog::record($request, 'auth.login');

        // Admins, staff, and riders always go to their own dashboards.
        if ($user->isAdmin() || $user->isStaff() || $user->isRider()) {
            return redirect()->route(
                $this->dashboardRoute($user)
            );
        }

        // Customers may continue to their intended destination.
        $intendedUrl = $request->session()->pull('url.intended');

        if ($intendedUrl) {
            $intendedHost = parse_url($intendedUrl, PHP_URL_HOST);
            $currentHost = $request->getHost();

            // Only redirect to a URL on the current host.
            if (
                !$intendedHost ||
                strcasecmp($intendedHost, $currentHost) === 0
            ) {
                return redirect()->to($intendedUrl);
            }
        }

        return redirect()->route('home');
    }

    /**
     * Determine the dashboard for the authenticated role.
     */
    private function dashboardRoute($user): string
    {
        if ($user->isAdmin()) {
            return 'admin.dashboard';
        }

        if ($user->isRider()) {
            return 'rider.dashboard';
        }

        if ($user->isStaff()) {
            return 'admin.dashboard';
        }

        return 'home';
    }

    /**
     * Log the user out.
     */
    public function logout(Request $request)
    {
        ActivityLog::record($request, 'auth.logout');

        $loginHistoryId = $request->session()->pull('login_history_id');
        if ($loginHistoryId) {
            LoginHistory::query()
                ->where('id', $loginHistoryId)
                ->where('user_id', $request->user()->id)
                ->whereNull('logged_out_at')
                ->update(['logged_out_at' => now()]);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
