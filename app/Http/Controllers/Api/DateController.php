<?php

namespace App\Http\Controllers\Api;


use App\Models\Date;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class DateController extends Controller
{
    public function getDays(Request $request) {
        $filter = $request->query('filter'); // 1 tavol1 // 2 asporto // 3 domicillio

        $adv_s = Setting::where('name', 'advanced')->first();
        $property_adv = json_decode($adv_s->property, 1);  


        $timeString = $filter == 1 ? $property_adv['delay_res'] : $property_adv['delay_or'];
        list($hours, $minutes) = explode(":", $timeString);
        $totalMinutes = ($hours * 60) + $minutes;

        // Ottieni la data di inizio 
        $startDateTime = Carbon::now()->addMinutes($totalMinutes);



        $year = $this->get_date($startDateTime, $filter);



        return response()->json([
            'startDateTime' => $startDateTime->format('d/m/Y'),
            'success'   => true,
            'results'   => $year,    
            'filter'   => $filter,    
            'typeOfOrdering'   => $property_adv['too'],    
            'count'   => count($year),    
        ]);
        
    }
    private function get_res($now, $source){
        $reservations = [];
        $orders = [];
        if($source == 1){
            $reservations = DB::table('reservations')
                ->select(
                    'name',
                    'surname',
                    'status',
                    'n_person',
                    'id',
                    'status',
                    'sala',
                    DB::raw("DATE(STR_TO_DATE(date_slot, '%d/%m/%Y %H:%i'))  AS day"),
                    DB::raw("DATE_FORMAT(STR_TO_DATE(date_slot, '%d/%m/%Y %H:%i'), '%H:%i') AS time")
                )
                ->whereRaw("STR_TO_DATE(date_slot, '%d/%m/%Y %H:%i') >= ?", [$now])
                ->where('status', '!=', 4) // 👈 controllo aggiunto
                ->orderByRaw("DATE(STR_TO_DATE(date_slot, '%d/%m/%Y %H:%i')) ASC")
                ->orderByRaw("TIME(STR_TO_DATE(date_slot, '%d/%m/%Y %H:%i')) ASC")
            ->get();
        }else{
            $orders = Order::select(
                    'name',
                    'surname',
                    'status',
                    'tot_price',
                    'id',
                    'status',
                    DB::raw("DATE(STR_TO_DATE(date_slot, '%d/%m/%Y %H:%i'))  AS day"),
                    DB::raw("DATE_FORMAT(STR_TO_DATE(date_slot, '%d/%m/%Y %H:%i'), '%H:%i') AS time")
                )
                ->whereRaw("STR_TO_DATE(date_slot, '%d/%m/%Y %H:%i') >= ?", [$now])
                ->where('status', '!=', 4) // 👈 controllo aggiunto
                ->orderByRaw("DATE(STR_TO_DATE(date_slot, '%d/%m/%Y %H:%i')) ASC")
                ->orderByRaw("TIME(STR_TO_DATE(date_slot, '%d/%m/%Y %H:%i')) ASC")
                ->with(['products', 'menus']) // 👈 carico anche i prodotti e i menu
            ->get();
        }
   
        $reserved = [];
        foreach ($reservations as $r) {
            $day = $r;
            $reserved[$r->day]['res'][] = $day;
        }
        foreach ($orders as $r) {
            $day = $r;
            if(array_key_exists($r->day, $reserved)){
                $reserved[$r->day]['or'][] = $day;
            }else{
                $reserved[$r->day]['or'][] = $day;
            }
        }


        return $reserved;
    }
    private function get_date($startDateTime, $source){
        $reserved = $this->get_res(Carbon::now(), $source);
        //return $reserved;
        $first_day = $startDateTime;

        $adv = json_decode(Setting::where('name', 'advanced')->first()->property, 1);
        $week = $adv['week_set'];

        $day_in_calendar = $adv['max_day_res']; // giorni da mostrare
        $days = [];
        for ($i = 0 ; $i < $day_in_calendar; $i++) { 
            $day = [
                'year' => $first_day->year,
                'month' => $first_day->month, // 1 - 12
                'date' => $first_day->copy()->format('Y-m-d'),
                'day' => $first_day->copy()->format('j'), // 1 - 31
                'day_w' => $first_day->copy()->format('N'), // 1 = lunedì, 7 = domenica
                'times' => [],
                'status' => 1, // 0 non disponibile,1 disponobile,2 oggi,  3 bloccato
            ];



            $av_t = ['table' => $adv['max_table']];
            if($adv['dt']){
                $av_t = [
                    'table_1' => $adv['max_table_1'],
                    'table_2' => $adv['max_table_2']
                ];
            }
            $max_or = $source == 2 ? $adv['max_asporto'] : $adv['max_domicilio'];
            if(!in_array($first_day->copy()->format('Y-m-d'), $adv['day_off'])){
                foreach ($week[$first_day->format('N')] as $time => $property) {
                    if(in_array($source, $property)){
                        if($source == 1){
                            $day['times'][$time] = [
                                'av' => $av_t,
                                'time' => $time,
                            ];   
                        }else{
                            $day['times'][$time] = [
                                'av' => [],
                                'time' => $time,
                                'or' => $max_or,
                            ];   
                        }
                    }
                }
                if(isset($reserved[$day['date']])){
                    if($source == 1){
                        foreach ($reserved[$day['date']]['res'] ?? [] as $r) {
                            $_p = json_decode($r->n_person);
        
                            if(isset($day['times'][$r->time])){
                                if(!$adv['dt']){
                                    $day['times'][$r->time]['av']['table'] -= ($_p->child + $_p->adult);
                                    if($day['times'][$r->time]['av']['table'] == 0){
                                        unset($day['times'][$r->time]);
                                    }
                                }elseif($r->sala == 1){
                                    $day['times'][$r->time]['av']['table_1'] -= ($_p->child + $_p->adult);
                                    if($day['times'][$r->time]['av']['table_1'] == 0){
                                        unset($day['times'][$r->time]);
                                    }
                                }else{
                                    $day['times'][$r->time]['av']['table_2'] -= ($_p->child + $_p->adult);
                                    if($day['times'][$r->time]['av']['table_2'] == 0){
                                        unset($day['times'][$r->time]);
                                    }
                                }
                            }
        
                            
                        }
                    }else{
                        foreach ($reserved[$day['date']]['or'] ?? [] as $r) { 
                            $day['times'][$r->time]['or'] --;
                            if($day['times'][$r->time]['or'] == 0){
                                unset($day['times'][$r->time]);
                            }
                        }
                    }
                }
                uksort($day['times'], function($a, $b) {
                    // confronto come orari
                    return strtotime($a) <=> strtotime($b);
                });
                $day['times'] = array_values($day['times']);
            }


                 
            $days[] = $day;
      
            $first_day->addDay();
        }
        $result = [];
        foreach ($days as $day) {
            $monthNumber = $day['month'];
            $year = $day['year'];

            // se il mese non esiste ancora, inizializzalo
            if (!isset($result[$monthNumber])) {
                $result[$monthNumber] = [
                    'year' => $year,
                    'month' => $monthNumber,
                    'days' => [],
                ];
            }
            // aggiungi il giorno dentro il mese corrispondente
            $result[$monthNumber]['days'][] = $day;
        }
       // dd($result);


        return $result;
    }
   
}
