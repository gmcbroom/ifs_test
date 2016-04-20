<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class APIResponseProvider extends ServiceProvider {

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot() {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register() {
        $this->app->bind('APIResponse', function() {
            return new \App\CarrierAPI\APIResponse;
        });
    }

}
