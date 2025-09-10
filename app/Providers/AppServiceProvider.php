<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Doctrine\DBAL\Types\Type;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    public function boot()
    {
        // Register char type if not registered
        if (!Type::hasType('char')) {
            Type::addType('char', \Doctrine\DBAL\Types\StringType::class);
        }
    }
}
