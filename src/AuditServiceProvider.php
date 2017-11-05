<?php

namespace Unisharp\AuditTrail2;

use Illuminate\Support\ServiceProvider;

class AuditServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'migrations/2017_10_12_113624_create_audits_table.php');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {

    }
}
