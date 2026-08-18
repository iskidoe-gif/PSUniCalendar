<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role)
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route($role === 'admin' ? 'admin.login' : 'superadmin.login');
        }

        if ($user->role !== $role) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route($role === 'admin' ? 'admin.login' : 'superadmin.login');
        }

        return $next($request);
    }
}
