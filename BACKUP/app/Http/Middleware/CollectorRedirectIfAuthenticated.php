<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Accountant;
use App\Models\Owner;

class CollectorRedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, ...$guards)
    {
        if (Auth::guard('collector')->check()) {

            $collector = Auth::guard('collector')->user();

            // Collector -> Accountant
            $accountant = Accountant::find($collector->accountant_id);

            if (!$accountant) {
                Auth::guard('collector')->logout();

                return redirect()->route('collector.login')->with('error', 'Accountant account not found.');
            }

            // Accountant -> Owner
            $owner = Owner::find($accountant->owner_id);

            if (!$owner || $owner->is_subscribed !== 'true') {

                Auth::guard('collector')->logout();

                return redirect()->route('collector.login')
                    ->with('error', 'Your owner subscription has expired. Please contact your owner.');
            }

            return redirect()->route('collector.tally.dashboard');
        }

        return $next($request);
    }
}