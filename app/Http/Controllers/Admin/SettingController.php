<?php

namespace App\Http\Controllers\Admin;

use App\Models\Date;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SettingController extends Controller
{
    public function updateAree(Request $request){
        $setting = Setting::all();
        $ar = $request->ar;
        if($ar == 'add'){ //se positivo aggiungie senno elimina
            $comune = $request->comune;
            $provincia = $request->provincia;
            $newarea = [
                'comune' => $comune,
                'provincia' => $provincia,
            ];
            $setting[4]['property'] = json_decode($setting[4]['property'], true);
            $isnew = true;
            foreach ($setting[4]['property']  as $k) {
                if($k['comune'] == $comune){
                    $isnew = false;
                }
            }
            if ($isnew && $comune !== " ") {
                $newp = $setting[4]['property'];

                array_push( $newp, $newarea);
                $setting[4]['property'] = json_encode($newp);
                $setting[4]->save();
            }
        }else{
            $comuni = $request->comuni;
            if($comuni !== null){
                $setting[4]['property'] = json_decode($setting[4]['property'], true);
                $upc = [];
                foreach ($setting[4]['property'] as $co) {
                        if(!in_array($co['comune'], $comuni)){
                            array_push( $upc, $co);
                        }
                    
                }
                $setting[4]['property'] = $upc;
                $setting[4]['property'] = json_encode($upc);
                $setting[4]->save();
            }

        }


        //date
        $dates = Date::all();
        if(count($dates) == 0){
            return view('admin.dashboard', compact('setting'));
        }
        $year = [
            1 => [
                'year' => $dates[0]['year'],
                'month'=> $dates[0]['month'],
                'days'=> [],
            ]
        ];
        //dd($dates[1]);
        $firstDay = [
            'year' => $dates[0]['year'],
            'month' => $dates[0]['month'],
            'day' => $dates[0]['day'],
        ];
               
        foreach ($dates as $d) {
            list($date, $time) = explode(" ", $d['date_slot']);
            
            if($d['reserving'] !== '0'){
                //dump($d['day']);
                $res = json_decode($d['reserving'], 1);
                if( config('configurazione.pack') == 2 ){        
                    $day = [
                        'day' => $d['day'],
                        'day_w' => $d['day_w'],
                        'date' => $date,
                        'time' => [],
                        'table' => 0,
                    ];
                    $time = [
                        'time' => $d['time'],
                        'table' => $res['table'],
                    ];
                }elseif( config('configurazione.pack') == 3){
                    if(config('configurazione.typeOfOrdering')){
                        $day = [
                            'day' => $d['day'],
                            'day_w' => $d['day_w'],
                            'date' => $date,
                            'time' => [],
                            'asporto' => 0,
                            'domicilio' => 0,
                        ];
                        $time = [
                            'time' => $d['time'],
                            'asporto' => $res['cucina_1'] + $res['cucina_2'],
                            'domicilio' => $res['domicilio'],
                        ];
                    }else{
                        $day = [
                            'day' => $d['day'],
                            'day_w' => $d['day_w'],
                            'date' => $date,
                            'time' => [],
                            'asporto' => 0,
                            'domicilio' => 0,
                        ];
                        $time = [
                            'time' => $d['time'],
                            'asporto' => $res['asporto'],
                            'domicilio' => $res['domicilio'],
                        ];
                    }
                }elseif( config('configurazione.pack') == 4){
                    if(config('configurazione.typeOfOrdering')){ 
                        $day = [
                            'day' => $d['day'],
                            'day_w' => $d['day_w'],
                            'date' => $date,
                            'time' => [],
                            'asporto' => 0,
                            'table' => 0,
                            'domicilio' => 0,
                        ];
                        $time = [
                            'time' => $d['time'],
                            'asporto' => $res['cucina_1'] + $res['cucina_2'],
                            'table' => $res['table'],
                            'domicilio' => $res['domicilio'],
                        ];
                    }else{
                        $day = [
                            'day' => $d['day'],
                            'day_w' => $d['day_w'],
                            'date' => $date,
                            'time' => [],
                            'asporto' => 0,
                            'table' => 0,
                            'domicilio' => 0,
                        ];
                        $time = [
                            'time' => $d['time'],
                            'asporto' => $res['asporto'],
                            'table' => $res['table'],
                            'domicilio' => $res['domicilio'],
                        ];
                    }
                }
            }
            // || || count($year[1]['days']) !== 0
            $cy = count($year);
            if($d['year'] == $firstDay['year'] && $d['month'] == $firstDay['month']){
                if( $d['time'] == 0 ){
                    array_push($year[$cy]['days'], $dayoff = [
                        'day' => $d['day'],
                        'day_w' => $d['day_w'],
                        'date' => $date]);
                }elseif($d['day'] !== $firstDay['day'] || count($year[1]['days']) == 0){
                    array_push($year[$cy]['days'], $day);
                    array_push($year[$cy]['days'][count($year[$cy]['days']) - 1]['time'], $day);
                    
                }elseif($d['day'] == $firstDay['day']){
                    if( config('configurazione.pack') == 2 ){        
                        $year[$cy]['days'][count($year[$cy]['days']) - 1]['table'] += $day['table'];        
                    }elseif( config('configurazione.pack') == 3){
                        $year[$cy]['days'][count($year[$cy]['days']) - 1]['asporto'] += $day['asporto'];
                        $year[$cy]['days'][count($year[$cy]['days']) - 1]['domicilio'] += $day['domicilio'];
                        
                    }elseif( config('configurazione.pack') == 4){
                        $year[$cy]['days'][count($year[$cy]['days']) - 1]['table'] += $day['table'];
                        $year[$cy]['days'][count($year[$cy]['days']) - 1]['domicilio'] += $day['domicilio'];
                        $year[$cy]['days'][count($year[$cy]['days']) - 1]['asporto'] += $day['asporto'];
                        
                    }
                    array_push($year[$cy]['days'][count($year[$cy]['days']) - 1]['time'], $time);
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
        
        return view ('admin.dashboard', compact('year', 'setting'));
    }
    
    public function updateAll(Request $request)
    {
        $setting = Setting::all();

        foreach ($setting as $s) {
            // Decodificare 'property' se non è null
            if (!is_null($s->property)) {
                $s->property = json_decode($s->property, true);
            } else {
                $s->property = [];
            }
        }
    
        $tavoli = $request->tavoli_status;
        $asporto = $request->asporto_status;
        $ferie = $request->ferie_status;
        $ferie_from = $request->from;
        $ferie_to = $request->to;
    
        $setting[0]->status = $tavoli;
        $setting[0]->save();
    
        $setting[1]->status = $asporto;
        $setting[1]->save();
        
            // Aggiornare il terzo setting
        $setting[2]->status = $ferie;
        $propertyArray = [
            'from' => $ferie_from,
            'to' => $ferie_to,
            // Aggiungi altri campi se necessario
        ];
        // Aggiorna il terzo setting
        $setting[2]->status = $ferie;
        $setting[2]->property = json_encode($propertyArray);
        $setting[2]->save();
        
        
        if (config('configurazione.pack') == 3 || config('configurazione.pack') == 4) {
            $setting[4]->property = json_encode($setting[4]->property);
            $domicilio = $request->domicilio_status;
            $setting[3]->status = $domicilio;
            $setting[3]->save();
        }
    

//date
        $dates = Date::all();
        if(count($dates) == 0){
            return view('admin.dashboard', compact('setting'));
        }
        $year = [
            1 => [
                'year' => $dates[0]['year'],
                'month'=> $dates[0]['month'],
                'days'=> [],
            ]
        ];
        //dd($dates[1]);
        $firstDay = [
            'year' => $dates[0]['year'],
            'month' => $dates[0]['month'],
            'day' => $dates[0]['day'],
        ];
               
        foreach ($dates as $d) {
            list($date, $time) = explode(" ", $d['date_slot']);
            
            if($d['reserving'] !== '0'){
                //dump($d['day']);
                $res = json_decode($d['reserving'], 1);
                if( config('configurazione.pack') == 2 ){        
                    $day = [
                        'day' => $d['day'],
                        'day_w' => $d['day_w'],
                        'date' => $date,
                        'time' => [],
                        'table' => 0,
                    ];
                    $time = [
                        'time' => $d['time'],
                        'table' => $res['table'],
                    ];
                }elseif( config('configurazione.pack') == 3){
                    if(config('configurazione.typeOfOrdering')){
                        $day = [
                            'day' => $d['day'],
                            'day_w' => $d['day_w'],
                            'date' => $date,
                            'time' => [],
                            'asporto' => 0,
                            'domicilio' => 0,
                        ];
                        $time = [
                            'time' => $d['time'],
                            'asporto' => $res['cucina_1'] + $res['cucina_2'],
                            'domicilio' => $res['domicilio'],
                        ];
                    }else{
                        $day = [
                            'day' => $d['day'],
                            'day_w' => $d['day_w'],
                            'date' => $date,
                            'time' => [],
                            'asporto' => 0,
                            'domicilio' => 0,
                        ];
                        $time = [
                            'time' => $d['time'],
                            'asporto' => $res['asporto'],
                            'domicilio' => $res['domicilio'],
                        ];
                    }
                }elseif( config('configurazione.pack') == 4){
                    if(config('configurazione.typeOfOrdering')){ 
                        $day = [
                            'day' => $d['day'],
                            'day_w' => $d['day_w'],
                            'date' => $date,
                            'time' => [],
                            'asporto' => 0,
                            'table' => 0,
                            'domicilio' => 0,
                        ];
                        $time = [
                            'time' => $d['time'],
                            'asporto' => $res['cucina_1'] + $res['cucina_2'],
                            'table' => $res['table'],
                            'domicilio' => $res['domicilio'],
                        ];
                    }else{
                        $day = [
                            'day' => $d['day'],
                            'day_w' => $d['day_w'],
                            'date' => $date,
                            'time' => [],
                            'asporto' => 0,
                            'table' => 0,
                            'domicilio' => 0,
                        ];
                        $time = [
                            'time' => $d['time'],
                            'asporto' => $res['asporto'],
                            'table' => $res['table'],
                            'domicilio' => $res['domicilio'],
                        ];
                    }
                }
            }
            // || || count($year[1]['days']) !== 0
            $cy = count($year);
            if($d['year'] == $firstDay['year'] && $d['month'] == $firstDay['month']){
                if( $d['time'] == 0 ){
                    array_push($year[$cy]['days'], $dayoff = [
                        'day' => $d['day'],
                        'day_w' => $d['day_w'],
                        'date' => $date]);
                }elseif($d['day'] !== $firstDay['day'] || count($year[1]['days']) == 0){
                    array_push($year[$cy]['days'], $day);
                    array_push($year[$cy]['days'][count($year[$cy]['days']) - 1]['time'], $day);
                    
                }elseif($d['day'] == $firstDay['day']){
                    if( config('configurazione.pack') == 2 ){        
                        $year[$cy]['days'][count($year[$cy]['days']) - 1]['table'] += $day['table'];        
                    }elseif( config('configurazione.pack') == 3){
                        $year[$cy]['days'][count($year[$cy]['days']) - 1]['asporto'] += $day['asporto'];
                        $year[$cy]['days'][count($year[$cy]['days']) - 1]['domicilio'] += $day['domicilio'];
                        
                    }elseif( config('configurazione.pack') == 4){
                        $year[$cy]['days'][count($year[$cy]['days']) - 1]['table'] += $day['table'];
                        $year[$cy]['days'][count($year[$cy]['days']) - 1]['domicilio'] += $day['domicilio'];
                        $year[$cy]['days'][count($year[$cy]['days']) - 1]['asporto'] += $day['asporto'];
                        
                    }
                    array_push($year[$cy]['days'][count($year[$cy]['days']) - 1]['time'], $time);
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
        
        return view ('admin.dashboard', compact('year', 'setting'));
 

   

    
    }
}
