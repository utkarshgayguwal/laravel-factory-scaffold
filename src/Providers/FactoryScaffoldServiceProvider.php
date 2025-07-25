<?php

namespace utkarshgayguwal\FactoryScaffold\Providers;

use Illuminate\Support\ServiceProvider;
use utkarshgayguwal\FactoryScaffold\Commands\ScaffoldFactoryCommand;

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