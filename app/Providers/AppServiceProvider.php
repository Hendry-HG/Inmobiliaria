<?php

namespace App\Providers;

use App\Models\Property;
use App\Models\User;
use App\Models\Appointment;
use App\Models\Lead;
use App\Models\SiteConfiguration;
use App\Observers\PropertyObserver;
use App\Observers\UserObserver;
use App\Observers\AppointmentObserver;
use App\Observers\LeadObserver;
use App\Observers\SiteConfigurationObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Registrar Observers para auditoría
        //Property::observe(PropertyObserver::class);
        //User::observe(UserObserver::class);
       // Appointment::observe(AppointmentObserver::class);
        //Lead::observe(LeadObserver::class);
        //SiteConfiguration::observe(SiteConfigurationObserver::class);
    }
}
