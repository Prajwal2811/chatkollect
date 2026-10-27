<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class AccountantAuthenticate extends Middleware
{
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            return route('accountant.login');
        }
    }


    protected function authenticate($request, array $guards)
    {
        if ($this->auth->guard('accountant')->check()) {
            return $this->auth->shouldUse('accountant');
        }

        $this->unauthenticated($request, ['accountant']);
    }

}
