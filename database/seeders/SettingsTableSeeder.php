<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SettingsTableSeeder extends Seeder
{
   
    public function run()
    {
        
        
        if(config('configurazione.pack') == 3 || config('configurazione.pack') == 4){
            $settings = [
                [
                    'name' => 'Prenotaione Tavoli',  
                    'status' => 1,
                    'property' => [
                        'empty' => 0
                    ],
                ],
                [
                    'name' => 'Prenotazione Asporti',  
                    'status' => 1,
                    'property' => [
                        'empty' => 0
                    ],
                ],
                [
                    'name' => 'Periodo di Ferie',  
                    'status' => 0,
                    'property' => [
                        'from' => '',
                        'to' => '',
                    ]
                ],
                [
                    'name' => 'Possibilità di consegna a domicilio',  
                    'status' => 1,
                    'property' => [
                        'empty' => 0
                    ],
                ],
                [
                    'name' => 'Comuni per il domicilio ',
                    'property' => [
                        [
                            'comune' => 'Monte San Vito',
                            'provincia' => 'AN',
                        ]
                    ]
                ],
            ];
        }else{
            $settings = [
                [
                    'name' => 'Prenotaione Tavoli',  
                    'status' => 1,
                    'property' => [
                        'empty' => 0
                    ],
                ],
                [
                    'name' => 'Prenotazione Asporti',  
                    'status' => 1,
                    'property' => [
                        'empty' => 0
                    ],
                ],
                [
                    'name' => 'Periodo di Ferie',  
                    'status' => 0,
                    'property' => [
                        'from' => '',
                        'to' => '',
                        'messagge' => '',
                        'style' => '',
                    ]
                ],
            ];
        }

        foreach ($settings as $s) {
            $string = json_encode($s['property'], true);  
            $s['property'] = $string;
            dump( $s['name']);
        

            // Creazione della voce di impostazione
            Setting::create($s);
        }
    }
}
