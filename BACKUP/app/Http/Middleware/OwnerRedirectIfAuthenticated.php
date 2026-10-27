<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OwnerRedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, ...$guards)
    {

        if (Auth::guard('owner')->check()) {
            return redirect()->route('owner.tally.dashboard');
        }

        return $next($request);
    }
}
