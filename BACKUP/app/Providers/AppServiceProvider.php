<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        View::composer('*', function ($view) {
            if (!$view->offsetExists('company')) {
                $view->with('company', request()->route('company') ?? '');
            }
        });
    }
}