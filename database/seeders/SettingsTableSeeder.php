<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $settings = [
            [
                'name' => 'Prenotazione Asporti',  
                'status' => true,
            ],
            [
                'name' => 'Prenotaione Tavoli',  
                'status' => true,
            ],
            [
                'name' => 'Possibilità di consegna a domicilio',  
                'status' => false,
            ],
            [
                'name' => 'Periodo di Ferie',  
                'status' => false,
                'property' => [
                    'from' => '',
                    'to' => '',
                    'messagge' => '',
                    'style' => '',
                    
                ]
            ],
            [
                'name' => 'Comuni per il domicilio ',  
                'status' => false,
                'property' => [
                    [
                        'comune' => 'Mmonte San Vito',
                        'provincia' => 'AN',
                    ]
                ]
            ],
        ];
        foreach ($settings as $s) {
            if (isset($s->property)) {
                $s->property = json_encode($s->property, true);
            }
        }
    
        foreach ($settings as $setting) {
            Setting::create($setting);
        }
    }
}
