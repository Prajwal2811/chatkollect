<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Owner;

class AccountantRedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, ...$guards)
    {
        if (Auth::guard('accountant')->check()) {

            $accountant = Auth::guard('accountant')->user();

            $owner = Owner::find($accountant->owner_id);

            // Owner nahi mila ya subscription inactive hai
            if (!$owner || $owner->is_subscribed !== 'true') {

                Auth::guard('accountant')->logout();

                return redirect()->route('accountant.login')
                    ->with('error', 'Your owner subscription has expired. Please contact your owner.');
            }

            return redirect()->route('accountant.tally.dashboard');
        }

        return $next($request);
    }
}