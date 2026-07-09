<?php

namespace App\Observers;

use App\Models\SiteConfiguration;

class SiteConfigurationObserver
{
    /**
     * Handle the SiteConfiguration "created" event.
     */
    public function created(SiteConfiguration $siteConfiguration): void
    {
        //
    }

    /**
     * Handle the SiteConfiguration "updated" event.
     */
    public function updated(SiteConfiguration $siteConfiguration): void
    {
        //
    }

    /**
     * Handle the SiteConfiguration "deleted" event.
     */
    public function deleted(SiteConfiguration $siteConfiguration): void
    {
        //
    }

    /**
     * Handle the SiteConfiguration "restored" event.
     */
    public function restored(SiteConfiguration $siteConfiguration): void
    {
        //
    }

    /**
     * Handle the SiteConfiguration "force deleted" event.
     */
    public function forceDeleted(SiteConfiguration $siteConfiguration): void
    {
        //
    }
}
