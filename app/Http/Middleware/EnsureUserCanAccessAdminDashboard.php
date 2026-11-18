<?php

namespace App\Http\Middleware;

use Closure;
use App\Support\AdminModuleAccess;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserCanAccessAdminDashboard
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        if (! AdminModuleAccess::allows(
            $request->user(),
            $request->route()?->getName()
        )) {
            abort(403, 'You are not authorized to access the staff dashboard.');
        }

        return $next($request);
    }
}
