<?php

namespace Database\Seeders;

use DateTime;
use App\Models\Date;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatesTableSeeder extends Seeder
{

    protected $variable;

    public function setVariables($variable)
    {
        $this->variable = $variable;
    }

    public function run()
    {
        $pack = 4;
        $typeOfOrdering = true;


        $currentDate = new DateTime();
        //   $currentDate->modify('+7 month');
        $times = $this->times;
        $abledDays = $this->days_off;

        // dump($times);

        // Si cicla contemporaneamente sui mesi, sui giorni e sulle fasce orarie
        // per ogni mese si contano i giorni, per ogni giorno si guarda il giorno(num) della settimana
        // per ogni giorno si verifica che il giorno non sia all'interno di quelli disabilitati dall'utente
        $currentMounth = $currentDate->format('n');

        for ($i = 1; $i <= 12; $i++) {
            $daysInMonth = $currentDate->format('t'); //n giorni nel mese
            Month::create([
                'month' => $currentDate->format('F'),
                'n'     => $currentDate->format('n'),
                'y'     => $currentDate->format('Y'),
            ]);
            for ($day = $currentDate->format('d'); $day <= $daysInMonth; $day++) {
                $currentDayOfWeek = $currentDate->format('N'); //giorno della settimana in numero 1-7

                foreach ($times[$currentDayOfWeek - 1] as $time) {
                    
                    if($pack== 2 ){  
                        $visible = [
                            'table' => 0,
                        ];
                        $availability = [
                            'table' =>  $this->variable['max_reservations'],
                        ];
                        $reserving = [
                            'table' => 0,
                        ];
                    }elseif($pack == 3)
                        if($typeOfOrdering){
                            $visible = [
                                'cucina_1' => 0,
                                'cuina_2' => 0,
                                'domicilio' => 0,
                            ];
                            $availability = [
                                'cucina_1' => $this->variable['max_cucina_1'],
                                'cuina_2' => $this->variable['max_cuina_2'],
                                'domicilio' => $this->variable['max_domicilio'],
                            ];
                            $reserving = [
                                'cucina_1' => 0,
                                'cuina_2' => 0,
                                'domicilio' => 0,
                            ];
                        }else{
                            $visible = [
                                'asporto' => 0,
                                'domicilio' => 0,
                            ];
                            $availability = [
                                'asporto' => $this->variable['max_asporto'],
                                'domicilio' => $this->variable['max_domicilio'],
                            ];
                            $reserving = [
                                'asporto' => 0,
                                'domicilio' => 0,
                            ];
                        } 
                    elseif($pack == 4){
                        if($typeOfOrdering){
                            $visible = [
                                'table' => 0,
                                'cucina_1' => 0,
                                'cuina_2' => 0,
                                'domicilio' => 0,
                            ];
                            $availability = [
                                'table' =>  $this->variable['max_table'],
                                'cucina_1' => $this->variable['max_cucina_1'],
                                'cuina_2' => $this->variable['max_cuina_2'],
                                'domicilio' => $this->variable['max_domicilio'],
                            ];
                            $reserving = [
                                'table' => 0,
                                'cucina_1' => 0,
                                'cuina_2' => 0,
                                'domicilio' => 0,
                            ];
                        }else{
                            $visible = [
                                'table' => 0,
                                'asporto' => 0,
                                'domicilio' => 0,
                            ];
                            $availability = [
                                'table' =>  $this->variable['max_reservations'],
                                'asporto' => $this->variable['max_asporto'],
                                'domicilio' => $this->variable['max_domicilio'],
                            ];
                            $reserving = [
                                'table' => 0,
                                'asporto' => 0,
                                'domicilio' => 0,
                            ];
                        }
                    }


                    
                    if ($time['set']) {
                        Date::create([

                            
                            'year' => $currentDate->format('Y'),
                            'month' => $currentDate->format('n'),
                            'day' => $currentDate->format('d'),
                            'day_w' => $currentDayOfWeek,
                            'time' => $time['time'],
                            'date_slot' => $currentDate->format('d') . '/' .  $currentDate->format('m') . '/' .  $currentDate->format('Y') . ' ' . $time['time'],
                            'status' => $time['set'],
                            
                            'max_res' => $this->max_reservations,
                            'max_pz_q' => $this->max_pz_q,
                            'max_pz_t' => $this->max_pz_t,
                            'max_domicilio' => $this->max_domicilio,
                            
                            'reserving' => json_encode($reserving),
                            'visible' => json_encode($visible),

                            'visible_d' => ($time['set'] >= 4 && $time['set'] <= 7 && in_array($currentDayOfWeek, $abledDays)) ? 1 : 0,
                            'visible_ft' => (($time['set'] == 1 || $time['set'] == 3 || $time['set'] == 4 || $time['set'] == 5 || $time['set'] == 6 || $time['set'] == 7) && in_array($currentDayOfWeek, $abledDays)) ? 1 : 0,
                            'visible_fq' => (($time['set'] == 1 || $time['set'] == 3 || $time['set'] == 4 || $time['set'] == 5 || $time['set'] == 6 || $time['set'] == 7) && in_array($currentDayOfWeek, $abledDays)) ? 1 : 0,
                            'visible_t' => ($time['set'] == 2 || $time['set'] == 3 || $time['set'] == 6 || $time['set'] == 7) && in_array($currentDayOfWeek, $abledDays) ? 1 : 0,

                        ]);
                    }
                }
                $currentDate->modify('+1 day');
            }
        }
        // dump($currentDate);
    }
}
