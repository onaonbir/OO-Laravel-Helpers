<?php

namespace OnaOnbir\OOLaravelHelpers;

use Illuminate\Support\ServiceProvider;

class OOLaravelHelpersServiceProvider extends ServiceProvider
{
    private string $packageName = 'oo-laravel-helpers';

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/'.$this->packageName.'.php',
            $this->packageName
        );
    }

    public function boot(): void {}
}
