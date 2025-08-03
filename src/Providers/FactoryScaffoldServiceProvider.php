<?php

namespace UtkarshGayguwal\FactoryScaffold\Providers;

use Illuminate\Support\ServiceProvider;
use UtkarshGayguwal\FactoryScaffold\Commands\ScaffoldFactoryCommand;

class FactoryScaffoldServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ScaffoldFactoryCommand::class,
            ]);
        }
    }

    public function register()
    {
        // Register any package services here
    }
}