<?php

namespace OnaOnbir\OOLaravelHelpers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use OnaOnbir\OOSubscription\Console\Commands\CheckSubscriptionLifecycle;
use OnaOnbir\OOSubscription\Console\Commands\ResetFeatureUsages;
use OnaOnbir\OOSubscription\Models\ModelPlan;
use OnaOnbir\OOSubscription\Observers\SubscriptionObserver;
use OnaOnbir\OOSubscription\Services\FeatureResetService;
use OnaOnbir\OOSubscription\Services\PaymentService;
use OnaOnbir\OOSubscription\Services\SubscriptionService;

class OOLaravelHelpersServiceProvider extends ServiceProvider
{

    private string $packageName = 'oo-laravel-helpers';

    public function register(): void
    {

    }

    public function boot(): void
    {

    }


}
