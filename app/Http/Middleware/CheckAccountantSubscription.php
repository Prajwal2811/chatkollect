<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Owner;

class CheckAccountantSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        $accountant = auth('accountant')->user();

        if (!$accountant) {
            return redirect()->route('accountant.login');
        }

        // Owner fetch karo
        $owner = Owner::find($accountant->owner_id);

        if (!$owner) {
            auth('accountant')->logout();

            return redirect()->route('accountant.login')
                ->with('error', 'Owner account not found.');
        }

        // Owner ki subscription check karo
        if ($owner->is_subscribed !== 'true') {
            auth('accountant')->logout();

            return redirect()->route('accountant.login')
                ->with('error', 'Your account is inactive because your owner does not have an active subscription.');
        }

        return $next($request);
    }
}