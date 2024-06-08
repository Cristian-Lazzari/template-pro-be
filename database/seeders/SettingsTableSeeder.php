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
                'porperty' => ''
            ],
            [
                'name' => 'Prenotaione Tavoli',  
                'status' => true,
                'porperty' => ''
            ],
            [
                'name' => 'Periodo di Ferie',  
                'status' => false,
                'porperty' => ''
            ],
            [
                'name' => 'Possibilità di consegna a domicilio',  
                'status' => false,
                'porperty' => ''
            ],
            [
                'name' => 'Comuni per il domicilio ',  
                'status' => false,
                'porperty' => ''
            ],
               
    
            ];
    
            foreach ($settings as $setting) {
                Setting::create($setting);
    
    }
}
}
