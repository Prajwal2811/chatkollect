<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class CollectorAuthenticate extends Middleware
{
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            return route('collector.login');
        }
    }


    protected function authenticate($request, array $guards)
    {
        if ($this->auth->guard('collector')->check()) {
            return $this->auth->shouldUse('collector');
        }

        $this->unauthenticated($request, ['collector']);
    }

}
