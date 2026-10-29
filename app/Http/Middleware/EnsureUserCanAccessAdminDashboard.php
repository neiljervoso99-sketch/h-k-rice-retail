<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserCanAccessAdminDashboard
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        if (!$request->user()->isAdmin() && !$request->user()->isStaff()) {
            abort(403, 'You are not authorized to access the staff dashboard.');
        }

        return $next($request);
    }
}
