<?php

namespace App\Providers;

use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class SettingsServiceProvider extends ServiceProvider
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

    public function boot()
    {
        try {
            if (!Schema::hasTable('settings')) {
                return;
            }

            $setting = Setting::where('name', 'Lingua')->first();
        } catch (\Throwable $exception) {
            return;
        }
        
        if(!$setting){
            return;
        }
        
        $data = json_decode($setting->property, true);
        App::setLocale($data['default']);
        Carbon::setLocale($data['default']);

        config([
            'configurazione.default_lang' => $data['default'],
            // 'languages.available' => $data['available']
        ]);
    }
}
