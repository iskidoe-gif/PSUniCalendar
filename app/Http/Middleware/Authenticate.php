<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Authenticate
{
    public function handle(Request $request, Closure $next, ...$guards)
    {
        if (! Auth::check()) {
            $host = $request->getHost();

            if (str_contains($host, 'admin.unicalendar.test')) {
                return redirect()->route('admin.login');
            }

            if (str_contains($host, 'superadmin.unicalendar.test')) {
                return redirect()->route('superadmin.login');
            }

            return redirect()->route('superadmin.login');
        }

        return $next($request);
    }
}
