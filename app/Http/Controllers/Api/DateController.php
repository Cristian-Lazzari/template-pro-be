<?php

namespace App\Http\Controllers\Api;


use App\Models\Date;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;

class DateController extends Controller
{
    public function getDays(Request $request) {

        $filter = $request->query('filter'); // 1 tavol1 // asporto 2 // 3 domicillio
        
        // Ottieni la data e l'ora attuale 
        $currentDateTime = Carbon::now()->format('Y-m-d H:i:s');

        // Ottieni la data di inizio (ad esempio, l'inizio della giornata attuale)
        $startDateTime = Carbon::now()->startOfDay()->format('Y-m-d H:i:s');

        // Ottieni la data di fine (ad esempio, la fine della giornata attuale)
        $endDateTime = Carbon::now()->addDays(config('configurazione.maxdayres'))->format('Y-m-d H:i:s');
        //$dates = Date::all();


        $vis_a = '"asporto":1';

        $vis_t = '"table":1';
        $vis_c1 = '"cuina_1":1';
        $vis_c2 = '"cucina_2":1';
        $vis_d = '"domicilio":1';

        $query = Date::whereRaw("STR_TO_DATE(date_slot, '%d/%m/%Y %H:%i') BETWEEN ? AND ?", [$startDateTime, $endDateTime]);

        if ($filter == 1) {
            $query->where('visible', 'like',  '%' . $vis_t . '%');
            $dates = $query->get();
            
        }elseif($filter == 2){
            if ( config('configurazione.typeOfOrdering') == false) {
                $query->where('visible', 'like',  '%' . $vis_a . '%');
                $dates = $query->get();          
            }else{
                $query->whereIn('status', [1, 3, 5, 7]);
                $query->where('visible', 'like',  '%' . $vis_c2 . '%')
                    ->orWhere('visible', 'like', '%' . $vis_c1 . '%');
                $dates = $query->get();
                //dd($query);
            }
        }else{
            if ( config('configurazione.typeOfOrdering') == false) {
                $query->where('visible', 'like',  '%' . $vis_d . '%');
                $dates = $query->get();          //1 3 5 7 
            }else{
                $query->where('visible', 'like',  '%' . $vis_c2 . '%' . $vis_d . '%')
                    ->orWhere('visible', 'like', '%' . $vis_c1 . '%' . $vis_d . '%');
                    
                $dates = $query->get();
               
            }
        }

        if(count($dates) == 0){
            return response()->json([
                'success'   => false,
                'results'   => [],    
                'filter'   => $filter,    
            ]);
        }


        $year = [
            1 => [
                'year' => $dates[0]['year'],
                'month'=> $dates[0]['month'],
                'days'=> [],
            ]
        ];
        $firstDay = [
            'year' => $dates[0]['year'],
            'month' => $dates[0]['month'],
            'day' => $dates[0]['day'],
        ];
               
        foreach ($dates as $d) {
            list($date, $time) = explode(" ", $d['date_slot']);
            
            if($d['reserving'] !== '0'){
                $res = json_decode($d['reserving'], 1);
                $vis = json_decode($d['visible'], 1);
                $max = json_decode($d['availability'], 1);
                if( config('configurazione.pack') == 2 ){        
                    $av = [
                        'table' => $max['table'] - $res['table'],
                    ];
                    
                }elseif( config('configurazione.pack') == 3){
                    if ( config('configurazione.typeOfOrdering') == false) {
                        $av = [
                            'asporto' => $max['asporto'] - $res['asporto'],
                            'domicilio' => $max['domicilio'] - $res['domicilio'],
                        ];
                    }else{
                        $av = [
                            'cucina_1' => $max['cucina_1'] - $res['cucina_1'],
                            'cucina_2' => $max['cucina_1'] - $res['cucina_1'],
                            'domicilio' => $max['domicilio'] - $res['domicilio'],
                        ];
                    }
                    
                }elseif( config('configurazione.pack') == 4){
                    if ( config('configurazione.typeOfOrdering') == false) {
                        $av = [
                            'asporto' => $max['asporto'] - $res['asporto'],
                            'table' => $max['table'] - $res['table'],
                            'domicilio' => $max['domicilio'] - $res['domicilio'],
                        ];   
                    }else{
                        $av = [
                            'cucina_1' => $max['cucina_1'] - $res['cucina_1'],
                            'cucina_2' => $max['cucina_1'] - $res['cucina_1'],
                            'table' => $max['table'] - $res['table'],
                            'domicilio' => $max['domicilio'] - $res['domicilio'],
                        ];
                    }
                }

                // dump($date . $time);
                // dump($res);
                //dump($av);

                $day = [
                    'date' => $date,
                    'times' => [],
                    'day_w' => $d['day_w'],
                    'day' => $d['day'],
                    'av' => $av,
                    'vis' => $vis,
                ];
                $time = [
                    'time' => $d['time'],
                    'av' => $av,
                    'vis' => $vis,
                ];
            }
            
            $cy = count($year);
            if($d['year'] == $firstDay['year'] && $d['month'] == $firstDay['month']){
                if( $d['time'] == 0 ){
                    array_push($year[$cy]['days'], $dayoff = [
                        'day' => $d['day'],
                        'day_w' => $d['day_w'],
                        'date' => $date]);
                }elseif($d['day'] !== $firstDay['day'] || count($year[1]['days']) == 0){
                    array_push($year[$cy]['days'], $day);
                    array_push($year[$cy]['days'][count($year[$cy]['days']) - 1]['times'], $time);
                }elseif($d['day'] == $firstDay['day']){
                    // prima correggo i dati del giorno in cui poi pusho l orario
                    if( config('configurazione.pack') == 2 ){ 
                        if($year[$cy]['days'][count($year[$cy]['days']) - 1]['vis']['table'] >= 1){
                            $year[$cy]['days'][count($year[$cy]['days']) - 1]['vis']['table'] = 1;
                        }
                        //av       
                        $year[$cy]['days'][count($year[$cy]['days']) - 1]['av']['table'] += $day['av']['table'];       

                    }elseif( config('configurazione.pack') == 3){
                        if ( config('configurazione.typeOfOrdering') == false) {
                            $year[$cy]['days'][count($year[$cy]['days']) - 1]['vis']['asporto'] += $day['vis']['asporto'];
                            if($year[$cy]['days'][count($year[$cy]['days']) - 1]['vis']['asporto'] >= 1){
                                $year[$cy]['days'][count($year[$cy]['days']) - 1]['vis']['asporto'] = 1;
                            }
                            $year[$cy]['days'][count($year[$cy]['days']) - 1]['vis']['domicilio'] += $day['vis']['domicilio'];
                            if($year[$cy]['days'][count($year[$cy]['days']) - 1]['vis']['domicilio'] >= 1){
                                $year[$cy]['days'][count($year[$cy]['days']) - 1]['vis']['domicilio'] = 1;
                            }
                            //av
                            $year[$cy]['days'][count($year[$cy]['days']) - 1]['av']['asporto'] += $day['av']['asporto'];
                            $year[$cy]['days'][count($year[$cy]['days']) - 1]['av']['domicilio'] += $day['av']['domicilio'];   
                        }else{
                            $year[$cy]['days'][count($year[$cy]['days']) - 1]['vis']['domicilio'] += $day['vis']['domicilio'];
                            if($year[$cy]['days'][count($year[$cy]['days']) - 1]['vis']['domicilio'] >= 1){
                                $year[$cy]['days'][count($year[$cy]['days']) - 1]['vis']['domicilio'] = 1;
                            }
                            $year[$cy]['days'][count($year[$cy]['days']) - 1]['vis']['cucina_1'] += $day['vis']['cucina_1'];
                            if($year[$cy]['days'][count($year[$cy]['days']) - 1]['vis']['cucina_1'] >= 1){
                                $year[$cy]['days'][count($year[$cy]['days']) - 1]['vis']['cucina_1'] = 1;
                            }
                            $year[$cy]['days'][count($year[$cy]['days']) - 1]['vis']['cucina_2'] += $day['vis']['cucina_2'];
                            if($year[$cy]['days'][count($year[$cy]['days']) - 1]['vis']['cucina_2'] >= 1){
                                $year[$cy]['days'][count($year[$cy]['days']) - 1]['vis']['cucina_2'] = 1;
                            }
                            //av
                            $year[$cy]['days'][count($year[$cy]['days']) - 1]['av']['cucina_1'] += $day['av']['cucina_1'];
                            $year[$cy]['days'][count($year[$cy]['days']) - 1]['av']['cucina_2'] += $day['av']['cucina_2'];
                            $year[$cy]['days'][count($year[$cy]['days']) - 1]['av']['domicilio'] += $day['av']['domicilio'];
                        }
                        
                    }elseif( config('configurazione.pack') == 4){
                        if ( config('configurazione.typeOfOrdering') == false) {
                            if($year[$cy]['days'][count($year[$cy]['days']) - 1]['vis']['table'] >= 1){
                                $year[$cy]['days'][count($year[$cy]['days']) - 1]['vis']['table'] = 1;
                            }
                            $year[$cy]['days'][count($year[$cy]['days']) - 1]['vis']['asporto'] += $day['vis']['asporto'];
                            if($year[$cy]['days'][count($year[$cy]['days']) - 1]['vis']['asporto'] >= 1){
                                $year[$cy]['days'][count($year[$cy]['days']) - 1]['vis']['asporto'] = 1;
                            }
                            $year[$cy]['days'][count($year[$cy]['days']) - 1]['vis']['domicilio'] += $day['vis']['domicilio'];
                            if($year[$cy]['days'][count($year[$cy]['days']) - 1]['vis']['domicilio'] >= 1){
                                $year[$cy]['days'][count($year[$cy]['days']) - 1]['vis']['domicilio'] = 1;
                            }
                            //av
                            $year[$cy]['days'][count($year[$cy]['days']) - 1]['av']['table'] += $day['av']['table'];
                            $year[$cy]['days'][count($year[$cy]['days']) - 1]['av']['asporto'] += $day['av']['asporto'];
                            $year[$cy]['days'][count($year[$cy]['days']) - 1]['av']['domicilio'] += $day['av']['domicilio'];   
                        }else{

                            $year[$cy]['days'][count($year[$cy]['days']) - 1]['vis']['table'] += $day['vis']['table'];
                            if($year[$cy]['days'][count($year[$cy]['days']) - 1]['vis']['table'] >= 1){
                                $year[$cy]['days'][count($year[$cy]['days']) - 1]['vis']['table'] = 1;
                            }
                            $year[$cy]['days'][count($year[$cy]['days']) - 1]['vis']['domicilio'] += $day['vis']['domicilio'];
                            if($year[$cy]['days'][count($year[$cy]['days']) - 1]['vis']['domicilio'] >= 1){
                                $year[$cy]['days'][count($year[$cy]['days']) - 1]['vis']['domicilio'] = 1;
                            }
                            $year[$cy]['days'][count($year[$cy]['days']) - 1]['vis']['cucina_1'] += $day['vis']['cucina_1'];
                            if($year[$cy]['days'][count($year[$cy]['days']) - 1]['vis']['cucina_1'] >= 1){
                                $year[$cy]['days'][count($year[$cy]['days']) - 1]['vis']['cucina_1'] = 1;
                            }
                            $year[$cy]['days'][count($year[$cy]['days']) - 1]['vis']['cucina_2'] += $day['vis']['cucina_2'];
                            if($year[$cy]['days'][count($year[$cy]['days']) - 1]['vis']['cucina_2'] >= 1){
                                $year[$cy]['days'][count($year[$cy]['days']) - 1]['vis']['cucina_2'] = 1;
                            }
                            //av
                            $year[$cy]['days'][count($year[$cy]['days']) - 1]['av']['table'] += $day['av']['table'];
                            $year[$cy]['days'][count($year[$cy]['days']) - 1]['av']['cucina_1'] += $day['av']['cucina_1'];
                            $year[$cy]['days'][count($year[$cy]['days']) - 1]['av']['cucina_2'] += $day['av']['cucina_2'];
                            $year[$cy]['days'][count($year[$cy]['days']) - 1]['av']['domicilio'] += $day['av']['domicilio'];
                        }
                    }
                    array_push($year[$cy]['days'][count($year[$cy]['days']) - 1]['times'], $time);
                }
            }else{
                
                $month = [
                    'year' =>  $d['year'],
                    'month' => $d['month'],
                    'days' => [],
                ];
                if($d['reserving'] !== '0'){
                    array_push($month['days'], $day);
                }else{
                    array_push($month['days'], $dayoff = [
                        'day' => $d['day'],
                        'day_w' => $d['day_w'],
                        'date' => $date]);
                }
                array_push($year, $month);
            }
            $firstDay = [
                'year' => $d['year'],
                'month' => $d['month'],
                'day' => $d['day'],
            ];
            
        };
        
        // dd($year[1]);
        //dd($year);
        
        return response()->json([
            'success'   => true,
            'results'   => $year,    
            'filter'   => $filter,    
            'count'   => count($year),    
        ]);
    }
   
}
