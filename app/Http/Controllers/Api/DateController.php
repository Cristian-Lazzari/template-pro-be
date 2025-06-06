<?php

namespace App\Http\Controllers\Api;


use App\Models\Date;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;

class DateController extends Controller
{
    public function getDays(Request $request) {
        
        $filter = $request->query('filter'); // 1 tavol1 // 2 asporto // 3 domicillio

        $adv_s = Setting::where('name', 'advanced')->first();
        $property_adv = json_decode($adv_s->property, 1);  
        $max_time_res = 55;
        // Ottieni la data e l'ora attuale 
        $currentDateTime = Carbon::now()->format('Y-m-d H:i:s');

        // Ottieni la data di inizio (ad esempio, l'inizio della giornata attuale)
        $startDateTime = Carbon::now()->addMinutes($max_time_res)->format('Y-m-d H:i:s');

        // Ottieni la data di fine (ad esempio, la fine della giornata attuale)
        $endDateTime = Carbon::now()->addDays($property_adv['max_day_res'])->format('Y-m-d H:i:s');


        $vis_a = '"asporto":1';
        if ($property_adv['dt']) {
            $double_t = $request->query('double_t');
            $vis_t_1 = '"table_1":1';
            $vis_t_2 = '"table_2":1';
        }else{
            $vis_t = '"table":1';
        }

        $vis_c1 = '"cuina_1":1';
        $vis_c2 = '"cucina_2":1';
        $vis_d = '"domicilio":1';

        $query = Date::select('*')
            ->selectRaw("
                STR_TO_DATE(
                    CASE
                        WHEN date_slot LIKE '%null%' THEN REPLACE(date_slot, ' null', ' 00:00')
                        ELSE date_slot
                    END,
                    '%d/%m/%Y %H:%i'
                ) AS order_slot
            ")
            ->whereRaw("STR_TO_DATE(
                CASE
                    WHEN date_slot LIKE '%null%' THEN REPLACE(date_slot, ' null', ' 00:00')
                    ELSE date_slot
                END, '%d/%m/%Y %H:%i') BETWEEN ? AND ?", [$startDateTime, $endDateTime])
            ->orderBy('order_slot');


        //$query = Date::whereRaw("STR_TO_DATE(date_slot, '%d/%m/%Y %H:%i') BETWEEN ? AND ?", [$startDateTime, $endDateTime]);

        if ($filter == 1) {
            if ($property_adv['dt']) {
                if($double_t == 1){
                    $query->where('visible', 'like',  '%' . $vis_t_1 . '%');
                }else{
                    $query->where('visible', 'like',  '%' . $vis_t_2 . '%');
                }
                $dates = $query->get();
            }else{
                $query->where('visible', 'like',  '%' . $vis_t . '%');
                $dates = $query->get();
            }
            //$query->where('visible', 'like',  '%' . $vis_t . '%');
            $dates = $query->get();
        }elseif($filter == 2){
            if ( $property_adv['too'] == false) {
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
            if ( $property_adv['too'] == false) {
                $query->where('visible', 'like',  '%' . $vis_d . '%');
                $dates = $query->get();          //1 3 5 7 
            }else{
                $query->where('visible', 'like',  '%' . $vis_d . '%');
                $query->where('visible', 'like',  '%' . $vis_c2 . '%' . $vis_d . '%')
                    ->orWhere('visible', 'like', '%' . $vis_c1 . '%' . $vis_d . '%');
                    
                $dates = $query->get();
               
            }
        }

        if(count($dates) == 0){
            return response()->json([
                'startDateTime' => $startDateTime,
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

        $adv_s = Setting::where('name', 'advanced')->first();
        $property_adv = json_decode($adv_s->property, 1);  
               
        foreach ($dates as $d) {
            list($date, $time) = explode(" ", $d['date_slot']);
            
            if($d['reserving'] !== '0'){
                $res = json_decode($d['reserving'], 1);
                $vis = json_decode($d['visible'], 1);
                $max = json_decode($d['availability'], 1);
                if( $property_adv['services'] == 2 ){  
                    if ($property_adv['dt']) {
                        $av = [
                            'table_1' => $max['table_1'] - $res['table_1'],
                            'table_2' => $max['table_2'] - $res['table_2'],
                        ];
                    }else{
                        $av = [
                            'table' => $max['table'] - $res['table'],
                        ]; 
                    }   
                    
                }elseif( $property_adv['services'] == 3){
                    if ( $property_adv['too'] == false) {
                        $av = [
                            'asporto' => $max['asporto'] - $res['asporto'],
                            'domicilio' => $max['domicilio'] - $res['domicilio'],
                        ];
                    }else{
                        $av = [
                            'cucina_1' => $max['cucina_1'] - $res['cucina_1'],
                            'cucina_2' => $max['cucina_2'] - $res['cucina_2'],
                            'domicilio' => $max['domicilio'] - $res['domicilio'],
                        ];
                    }
                    
                }elseif( $property_adv['services'] == 4){
                    if ($property_adv['dt']) {
                        if ( $property_adv['too'] == false) {
                            $av = [
                                'table_1' => $max['table_1'] - $res['table_1'],
                                'table_2' => $max['table_2'] - $res['table_2'],
                                'asporto' => $max['asporto'] - $res['asporto'],
                                'domicilio' => $max['domicilio'] - $res['domicilio'],
                            ];   
                        }else{
                            $av = [
                                'cucina_1' => $max['cucina_1'] - $res['cucina_1'],
                                'cucina_2' => $max['cucina_2'] - $res['cucina_2'],
                                'table_1' => $max['table_1'] - $res['table_1'],
                                'table_2' => $max['table_2'] - $res['table_2'],
                                'domicilio' => $max['domicilio'] - $res['domicilio'],
                            ];
                        }
                    }else{
                        if ( $property_adv['too'] == false) {
                            $av = [
                                'asporto' => $max['asporto'] - $res['asporto'],
                                'table' => $max['table'] - $res['table'],
                                'domicilio' => $max['domicilio'] - $res['domicilio'],
                            ];   
                        }else{
                            $av = [
                                'cucina_1' => $max['cucina_1'] - $res['cucina_1'],
                                'cucina_2' => $max['cucina_2'] - $res['cucina_2'],
                                'table' => $max['table'] - $res['table'],
                                'domicilio' => $max['domicilio'] - $res['domicilio'],
                            ];
                        }
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
                    $d_y = count($year[$cy]['days']) - 1;
                    // prima correggo i dati del giorno in cui poi pusho l orario
                    if( $property_adv['services'] == 2 ){
                        if($property_adv['dt']){
                            $year[$cy]['days'][$d_y]['vis']['table_1'] += $day['vis']['table_1'];
                            if($year[$cy]['days'][$d_y]['vis']['table_1'] >= 1){
                                $year[$cy]['days'][$d_y]['vis']['table_1'] = 1;
                            }
                            $year[$cy]['days'][$d_y]['vis']['table_2'] += $day['vis']['table_2'];
                            if($year[$cy]['days'][$d_y]['vis']['table_2'] >= 1){
                                $year[$cy]['days'][$d_y]['vis']['table_2'] = 1;
                            }
                            //av       
                            $year[$cy]['days'][$d_y]['av']['table_1'] += $day['av']['table_1'];   
                            $year[$cy]['days'][$d_y]['av']['table_2'] += $day['av']['table_2'];   
                            

                        }else{
                            $year[$cy]['days'][$d_y]['vis']['table'] += $day['vis']['table'];
                            if($year[$cy]['days'][$d_y]['vis']['table'] >= 1){
                                $year[$cy]['days'][$d_y]['vis']['table'] = 1;
                            }
                            //av       
                            $year[$cy]['days'][$d_y]['av']['table'] += $day['av']['table'];       
                        }

                    }elseif( $property_adv['services'] == 3){
                        if ( $property_adv['too'] == false) {
                            $year[$cy]['days'][$d_y]['vis']['asporto'] += $day['vis']['asporto'];
                            if($year[$cy]['days'][$d_y]['vis']['asporto'] >= 1){
                                $year[$cy]['days'][$d_y]['vis']['asporto'] = 1;
                            }
                            $year[$cy]['days'][$d_y]['vis']['domicilio'] += $day['vis']['domicilio'];
                            if($year[$cy]['days'][$d_y]['vis']['domicilio'] >= 1){
                                $year[$cy]['days'][$d_y]['vis']['domicilio'] = 1;
                            }
                            //av
                            $year[$cy]['days'][$d_y]['av']['asporto'] += $day['av']['asporto'];
                            $year[$cy]['days'][$d_y]['av']['domicilio'] += $day['av']['domicilio'];   
                        }else{
                            $year[$cy]['days'][$d_y]['vis']['domicilio'] += $day['vis']['domicilio'];
                            if($year[$cy]['days'][$d_y]['vis']['domicilio'] >= 1){
                                $year[$cy]['days'][$d_y]['vis']['domicilio'] = 1;
                            }
                            $year[$cy]['days'][$d_y]['vis']['cucina_1'] += $day['vis']['cucina_1'];
                            if($year[$cy]['days'][$d_y]['vis']['cucina_1'] >= 1){
                                $year[$cy]['days'][$d_y]['vis']['cucina_1'] = 1;
                            }
                            $year[$cy]['days'][$d_y]['vis']['cucina_2'] += $day['vis']['cucina_2'];
                            if($year[$cy]['days'][$d_y]['vis']['cucina_2'] >= 1){
                                $year[$cy]['days'][$d_y]['vis']['cucina_2'] = 1;
                            }
                            //av
                            $year[$cy]['days'][$d_y]['av']['cucina_1'] += $day['av']['cucina_1'];
                            $year[$cy]['days'][$d_y]['av']['cucina_2'] += $day['av']['cucina_2'];
                            $year[$cy]['days'][$d_y]['av']['domicilio'] += $day['av']['domicilio'];
                        }
                        
                    }elseif( $property_adv['services'] == 4){
                        if ( $property_adv['too'] == false) {
                            if($property_adv['dt']){
                                $year[$cy]['days'][$d_y]['vis']['table_1'] += $day['vis']['table_1'];
                                if($year[$cy]['days'][$d_y]['vis']['table_1'] >= 1){
                                    $year[$cy]['days'][$d_y]['vis']['table_1'] = 1;
                                }
                                $year[$cy]['days'][$d_y]['vis']['table_2'] += $day['vis']['table_2'];
                                if($year[$cy]['days'][$d_y]['vis']['table_2'] >= 1){
                                    $year[$cy]['days'][$d_y]['vis']['table_2'] = 1;
                                }
                                //av       
                                $year[$cy]['days'][$d_y]['av']['table_1'] += $day['av']['table_1'];   
                                $year[$cy]['days'][$d_y]['av']['table_2'] += $day['av']['table_2'];   
                            }else{
                                $year[$cy]['days'][$d_y]['vis']['table'] += $day['vis']['table'];
                                if($year[$cy]['days'][$d_y]['vis']['table'] >= 1){
                                    $year[$cy]['days'][$d_y]['vis']['table'] = 1;
                                }
                                //av       
                                $year[$cy]['days'][$d_y]['av']['table'] += $day['av']['table'];       
                            }
                            $year[$cy]['days'][$d_y]['vis']['asporto'] += $day['vis']['asporto'];
                            if($year[$cy]['days'][$d_y]['vis']['asporto'] >= 1){
                                $year[$cy]['days'][$d_y]['vis']['asporto'] = 1;
                            }
                            $year[$cy]['days'][$d_y]['vis']['domicilio'] += $day['vis']['domicilio'];
                            if($year[$cy]['days'][$d_y]['vis']['domicilio'] >= 1){
                                $year[$cy]['days'][$d_y]['vis']['domicilio'] = 1;
                            }
                            //av
                            
                            $year[$cy]['days'][$d_y]['av']['asporto'] += $day['av']['asporto'];
                            $year[$cy]['days'][$d_y]['av']['domicilio'] += $day['av']['domicilio'];   
                        }else{
                            if($property_adv['dt']){
                                $year[$cy]['days'][$d_y]['vis']['table_1'] += $day['vis']['table_1'];
                                if($year[$cy]['days'][$d_y]['vis']['table_1'] >= 1){
                                    $year[$cy]['days'][$d_y]['vis']['table_1'] = 1;
                                }
                                $year[$cy]['days'][$d_y]['vis']['table_2'] += $day['vis']['table_2'];
                                if($year[$cy]['days'][$d_y]['vis']['table_2'] >= 1){
                                    $year[$cy]['days'][$d_y]['vis']['table_2'] = 1;
                                }
                                //av       
                                $year[$cy]['days'][$d_y]['av']['table_1'] += $day['av']['table_1'];   
                                $year[$cy]['days'][$d_y]['av']['table_2'] += $day['av']['table_2'];   
                            }else{
                                $year[$cy]['days'][$d_y]['vis']['table'] += $day['vis']['table'];
                                if($year[$cy]['days'][$d_y]['vis']['table'] >= 1){
                                    $year[$cy]['days'][$d_y]['vis']['table'] = 1;
                                }
                                //av       
                                $year[$cy]['days'][$d_y]['av']['table'] += $day['av']['table'];       
                            }
                            $year[$cy]['days'][$d_y]['vis']['domicilio'] += $day['vis']['domicilio'];
                            if($year[$cy]['days'][$d_y]['vis']['domicilio'] >= 1){
                                $year[$cy]['days'][$d_y]['vis']['domicilio'] = 1;
                            }
                            $year[$cy]['days'][$d_y]['vis']['cucina_1'] += $day['vis']['cucina_1'];
                            if($year[$cy]['days'][$d_y]['vis']['cucina_1'] >= 1){
                                $year[$cy]['days'][$d_y]['vis']['cucina_1'] = 1;
                            }
                            $year[$cy]['days'][$d_y]['vis']['cucina_2'] += $day['vis']['cucina_2'];
                            if($year[$cy]['days'][$d_y]['vis']['cucina_2'] >= 1){
                                $year[$cy]['days'][$d_y]['vis']['cucina_2'] = 1;
                            }
                            //av
                           
                            $year[$cy]['days'][$d_y]['av']['cucina_1'] += $day['av']['cucina_1'];
                            $year[$cy]['days'][$d_y]['av']['cucina_2'] += $day['av']['cucina_2'];
                            $year[$cy]['days'][$d_y]['av']['domicilio'] += $day['av']['domicilio'];
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
            'startDateTime' =>$startDateTime,
            'success'   => true,
            'results'   => $year,    
            'filter'   => $filter,    
            'typeOfOrdering'   => $property_adv['too'],    
            'count'   => count($year),    
        ]);
    }
   
}
