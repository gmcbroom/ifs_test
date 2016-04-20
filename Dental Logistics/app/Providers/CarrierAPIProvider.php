<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class CarrierAPIProvider extends ServiceProvider {

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
        $this->app->bind('CarrierAPI', function() {
            return new \App\CarrierAPI\CarrierAPI;
        });
    }

}
