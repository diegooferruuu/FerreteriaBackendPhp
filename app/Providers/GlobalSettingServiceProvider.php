<?php

namespace App\Providers;

use App\Models\AutorizacionSistema;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class GlobalSettingServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
            // $sistema = AutorizacionSistema::where('estado', 'ACTIVO')->first();
            // Config::get('sistema', $sistema);
    }
}
