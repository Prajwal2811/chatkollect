<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckOwnerSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        $owner = auth('owner')->user();

        if (!$owner) {
            return redirect()->route('owner.login');
        }

        if ($owner->is_subscribed !== 'true') {
            return redirect()->route('owner.subscription')
                ->with('error', 'Please buy a subscription plan.');
        }

        return $next($request);
    }
}