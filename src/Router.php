<?php

namespace Goodwong\LaravelShop;

use Illuminate\Support\Facades\Route;

class Router
{
    /**
     * routes
     * 
     * @return void
     */
    public static function route()
    {
        // require __DIR__.'/routes.php';
        Route::namespace('\\Goodwong\\LaravelShop\\Http\\Controllers')
        ->group(__DIR__.'/routes.php');
    }
}