<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Owner;
use App\Models\Accountant;

class CheckCollectorSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        $collector = auth('collector')->user();

        if (!$collector) {
            return redirect()->route('collector.login');
        }

        // Collector -> Accountant
        $accountant = Accountant::find($collector->accountant_id);

        if (!$accountant) {
            auth('collector')->logout();

            return redirect()->route('collector.login')
                ->with('error', 'Accountant account not found.');
        }

        // Accountant -> Owner
        $owner = Owner::find($accountant->owner_id);

        if (!$owner) {
            auth('collector')->logout();

            return redirect()->route('collector.login')
                ->with('error', 'Owner account not found.');
        }

        // Owner subscription check
        if ($owner->is_subscribed !== 'true') {
            auth('collector')->logout();

            return redirect()->route('collector.login')
                ->with('error', 'Your account is inactive because your owner does not have an active subscription.');
        }

        return $next($request);
    }
}