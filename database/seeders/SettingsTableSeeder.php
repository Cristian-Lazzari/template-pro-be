<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SettingsTableSeeder extends Seeder
{
   
    public function run()
    {
        $settings = [
            [
                'name' => 'Prenotazione Tavoli',  
                'status' => 1,
                'property' => [],
            ],
            [
                'name' => 'Prenotazione Asporti',  
                'status' => 1,
                'property' => [
                    'pay' => 1, // 0 dis - 1 ab - 2 ob
                    'min_price' => 0,
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
                'name' => 'Orari di attività',
                'property' => [
                    'lunedì'    =>  '',
                    'martedì'   =>  '',
                    'mercoledì' =>  '',
                    'giovedì'   =>  '',
                    'venerdì'   =>  '',
                    'sabato'    =>  '',
                    'domenica'  =>  '',
                ]
            ],
            [
                'name' => 'Posizione',
                'property' => []
            ],
            [
                'name' => 'Contatti',
                'property' => []
            ],
            [
                'name' => 'Possibilità di consegna a domicilio',  
                'status' => 1,
                'property' => [
                    'pay' => 1, // 0 dis - 1 ab - 2 ob
                    'min_price' => 0,
                    'delivery_cost' => 0,
                ],
            ],
            [
                'name' => 'Comuni per il domicilio',
                'property' => []
            ],
            [
                'name' => 'wa',
                'property' => [
                    'last_response_wa_1' => '',
                    'last_response_wa_2' => '',
                    'numbers' => ['393271622244'],
                ]
            ],
            [
                'name' => 'Promozione Tavoli',
                'status' => 0,
                'property' => [
                    'title' => '',
                    'body' => '',
                ]
            ],
            [
                'name' => 'advanced',
                'property' => [
                    'too' => false,
                    'dt' => false,
                    'services' => 4, //1 niente // 2 tavoli // 3 asporto // 4 tutti
                    
                    'menu_fix_set' => 1,
                    'too_1' => 'tipo 1',
                    'too_2' => 'tipo 2',
                    'sala_1' => 'Interno',
                    'sala_2' => 'Esterno',
                    'p_iva' => '',
                    'r_sociale' => '',
                    'times_start' => '12:00',
                    'times_end' => '23:30',
                    'max_day_res' => '30',
                    'times_interval' => 20,
                    'c_rea' => '',
                    'c_sociale' => '',
                    'c_ateco' => '',
                    'u_imprese' => '',
                    'method' => [],
                    'set_time'=> [
                        'tavoli',
                        'asporto',
                        'domicilio',
                    ]
                ]
            ],
        ];
   

        foreach ($settings as $s) {
            $string = json_encode($s['property'], true);  
            $s['property'] = $string;
            dump( $s['name']);
            // Creazione della voce di impostazione
            Setting::create($s);
        }
    }
}
