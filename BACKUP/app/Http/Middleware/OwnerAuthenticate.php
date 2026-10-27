<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class OwnerAuthenticate extends Middleware
{
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            return route('owner.login');
        }
    }


    protected function authenticate($request, array $guards)
    {
        if ($this->auth->guard('owner')->check()) {
            return $this->auth->shouldUse('owner');
        }

        $this->unauthenticated($request, ['owner']);
    }

}
