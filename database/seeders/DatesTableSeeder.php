<?php

namespace Database\Seeders;

use DateTime;
use App\Models\Day;
use App\Models\Date;
use App\Models\Month;
use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatesTableSeeder extends Seeder
{

    protected $reserving;
    protected $availability;
    protected $time;
    protected $variable;
    protected $days_on;

    public function setVariables($reserving, $availability, $time, $days_on)
    {
        $this->reserving = $reserving;
        $this->availability = $availability;
        $this->time = $time;
        $this->days_on = $days_on;
    }

    public function run()
    {
        
        $currentDate = new DateTime();
        $reserving = $this->reserving;
        $availability = $this->availability;
        $times = $this->time;
        $abledDays = $this->days_on;

        $currentMounth = $currentDate->format('n');

        $adv_s = Setting::where('name', 'advanced')->first();
        $property_adv = json_decode($adv_s->property, 1);  
        $double = $property_adv['dt'];
        $pack = $property_adv['services'];
        $type = $property_adv['too'];

        for ($i = 1; $i <= 12; $i++) {
            $daysInMonth = $currentDate->format('t'); //n giorni nel mese

            for ($day = $currentDate->format('d'); $day <= $daysInMonth; $day++) {
                $currentDayOfWeek = $currentDate->format('N'); //giorno della settimana in numero 1-7

                $add = false;
                foreach ($times[$currentDayOfWeek - 1] as $key => $value) {
                    if ($value) {
                        $add = true;
                        $visible = [
                            'table' => in_array($value,[2,3,6,7]) && in_array($currentDayOfWeek, $abledDays) ? 1 : 0,
                            'table_1' => in_array($value,[2,3,6,7]) && in_array($currentDayOfWeek, $abledDays) ? 1 : 0,
                            'table_2' => in_array($value,[2,3,6,7]) && in_array($currentDayOfWeek, $abledDays) ? 1 : 0,
                            'asporto' => (in_array($value,[1,3,4,5]) && in_array($currentDayOfWeek, $abledDays)) ? 1 : 0,
                            'cucina_1' => (in_array($value,[1,3,4,5]) && in_array($currentDayOfWeek, $abledDays)) ? 1 : 0,
                            'cucina_2' => (in_array($value,[1,3,4,5]) && in_array($currentDayOfWeek, $abledDays)) ? 1 : 0,
                            'domicilio' => ($value >= 4 && in_array($currentDayOfWeek, $abledDays)) ? 1 : 0,
                        ];
                        
                        if( $pack == 2 ){ 
                            if($double){
                                unset($visible['table']);
                            }else{
                                unset($visible['table_1'], $visible['table_2']);
                            }
                            unset($visible['asporto'], $visible['cucina_1'], $visible['cucina_2'], $visible['domicilio']);
                        }elseif( $pack == 3){
                            if($type){
                                unset($visible['asporto']);
                            }else{
                                unset($visible['cucina_1'], $visible['cucina_2']);
                            }
                            unset($visible['table'], $visible['table_1'], $visible['table_2']);
                        }elseif( $pack == 4){    
                            if($double){
                                unset($visible['table']);
                            }else{
                                unset($visible['table_1'], $visible['table_2']);
                            }
                            if($type){
                                unset($visible['asporto']);
                            }else{
                                unset($visible['cucina_1'], $visible['cucina_2']);
                            }
                        }
                        
                        Date::create([  
                            'year' => $currentDate->format('Y'),
                            'month' => $currentDate->format('n'),
                            'day' => $currentDate->format('d'),
                            'day_w' => $currentDayOfWeek,
                            'time' => $key,
                            'date_slot' => $currentDate->format('d') . '/' .  $currentDate->format('m') . '/' .  $currentDate->format('Y') . ' ' . $key,
                            'status' => $value,
                            'reserving' => json_encode($reserving),
                            'visible' => json_encode($visible),
                            'availability' => json_encode($availability),

                        ]);
                    }
                }
                if(!$add){
                    Date::create([  
                        'year' => $currentDate->format('Y'),
                        'month' => $currentDate->format('n'),
                        'day' => $currentDate->format('d'),
                        'day_w' => $currentDayOfWeek,
                        'time' => 0,
                        'date_slot' => $currentDate->format('d') . '/' .  $currentDate->format('m') . '/' .  $currentDate->format('Y') . ' ' . 'null.',
                        'status' => 0,
                        'reserving' => 0,
                        'visible' => 0,
                        'availability' => 0,
                    ]);
                }
                $currentDate->modify('+1 day');
            }
        }
        // dump($currentDate);
    }
}

