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
        
        $data = json_decode($setting->property, true) ?: [];
        $languages = [];

        if (isset($data['languages']) && is_array($data['languages'])) {
            foreach ($data['languages'] as $language) {
                if (is_string($language) && trim($language) !== '') {
                    $languages[] = trim($language);
                }
            }
        }

        $default = $data['default'] ?? null;

        if (!is_string($default) || trim($default) === '') {
            $default = $languages[0] ?? config('configurazione.default_lang') ?? config('app.locale') ?? 'it';
        }

        $default = trim((string) $default) ?: 'it';

        App::setLocale($default);
        Carbon::setLocale($default);

        config([
            'configurazione.default_lang' => $default,
            // 'languages.available' => $data['available']
        ]);
    }
}
