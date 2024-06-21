<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\Date;
use App\Models\Order;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Database\Seeders\DatesTableSeeder;

class DateController extends Controller
{
    private $validations2 = [
        'max_reservations'      => 'required|integer',
    ];
    private $validations3t = [
        'max_domicilio'         => 'required|integer',
        'max_cucina_1'              => 'required|integer',
        'max_cucina_2'              => 'required|integer',
    ];
    private $validations3f = [
        'max_domicilio'         => 'required|integer',
        'max_asporto'           => 'required|integer',
    ];
    private $validations4t = [
        'max_domicilio'         => 'required|integer',
        'max_reservations'      => 'required|integer',
        'max_cucina_1'              => 'required|integer',
        'max_cucina_2'              => 'required|integer',
    ];
    private $validations4f = [
        'max_domicilio'         => 'required|integer',
        'max_reservations'      => 'required|integer',
        'max_asporto'           => 'required|integer',
    ];
    public function index()
    {
        
        $dates = Date::all();
        if(count($dates) == 0){
            return view('admin.dates.index');
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
        return view('admin.dates.index', compact('year'));
    }

    public function showDay(Request $request){
        $date = $request->input('date');
        $day = Date::where('date_slot','like','%' . $date . '%')->get();
        if(count($day) == 1){
            if($day[0]['time'] == 0){   
                return to_route('admin.dates.index')->with('not_found', $day);   
            }
        }
        ///dd($day);
        return view('admin.dates.showDay', compact('day'));   
    }



    public function generate(Request $request)
    {
          
        $data = $request->all();
        if( config('configurazione.pack') == 2 ){ 
            $request->validate($this->validations2);
            $availability = [
                'table' =>  $data['max_reservations'],
            ];
            $reserving = [
                'table' => 0,
            ];
        }elseif( config('configurazione.pack') == 3){
            if(config('configurazione.typeOfOrdering')){
                $request->validate($this->validations3t);
                $availability = [
                    'cucina_1' => $data['max_cucina_1'],
                    'cucina_2' => $data['max_cucina_2'],
                    'domicilio' => $data['max_domicilio'],
                ];
                $reserving = [
                    'cucina_1' => 0,
                    'cucina_2' => 0,
                    'domicilio' => 0,
                ];
            }else{
                $request->validate($this->validations3f);
                $availability = [
                    'asporto' => $data['max_asporto'],
                    'domicilio' => $data['max_domicilio'],
                ];
                $reserving = [
                    'asporto' => 0,
                    'domicilio' => 0,
                ];
            } 
        }elseif( config('configurazione.pack') == 4){
            if(config('configurazione.typeOfOrdering')){
            $request->validate($this->validations4t);
            $availability = [
                'table' =>  $data['max_reservations'],
                'cucina_1' => $data['max_cucina_1'],
                'cucina_2' => $data['max_cucina_2'],
                'domicilio' => $data['max_domicilio'],
            ];
            $reserving = [
                'table' => 0,
                'cucina_1' => 0,
                'cucina_2' => 0,
                'domicilio' => 0,
            ];
            }else{
                $request->validate($this->validations4f);
                $availability = [
                    'table' =>  $data['max_reservations'],
                    'asporto' => $data['max_asporto'],
                    'domicilio' => $data['max_domicilio'],
                ];
                $reserving = [
                    'table' => 0,
                    'asporto' => 0,
                    'domicilio' => 0,
                ];
            }
        }
        $days_on = $request->input("days_on");
        $times_slot1 = $request->input("times_slot_1");
        $times_slot2 = $request->input("times_slot_2");
        $times_slot3 = $request->input("times_slot_3");
        $times_slot4 = $request->input("times_slot_4");
        $times_slot5 = $request->input("times_slot_5");
        $times_slot6 = $request->input("times_slot_6");
        $times_slot7 = $request->input("times_slot_7");
        $timesDay = [];
        $day = config('configurazione.times');
        
        for ($i = 0; $i < 7; $i++) { 
            array_push($timesDay, $day);
        }
        //dd(count($timesDay[0]));
        for ($i = 0; $i < count($timesDay[0]); $i++) {
            // dump($timesDay[0][$i + 1]['set']);
            // dump($times_slot1[$i]);
            $timesDay[0][$i+1]['set'] = $times_slot1[$i];
        }
        for ($i = 0; $i < count($timesDay[1]); $i++) {
            $timesDay[1][$i+1]['set'] = $times_slot2[$i];
        }
        for ($i = 0; $i < count($timesDay[2]); $i++) {
            $timesDay[2][$i+1]['set'] = $times_slot3[$i];
        }
        for ($i = 0; $i < count($timesDay[3]); $i++) {
            $timesDay[3][$i+1]['set'] = $times_slot4[$i];
        }
        for ($i = 0; $i < count($timesDay[4]); $i++) {
            $timesDay[4][$i+1]['set'] = $times_slot5[$i];
        }
        for ($i = 0; $i < count($timesDay[5]); $i++) {
            $timesDay[5][$i+1]['set'] = $times_slot6[$i];
        }
        for ($i = 0; $i < count($timesDay[6]); $i++) {
            $timesDay[6][$i+1]['set'] = $times_slot7[$i];
        }
        // Pulisco le tabelle
        DB::table('dates')->truncate();
        // Eseguo il seeder
        $seeder = new DatesTableSeeder();
        $seeder->setVariables($reserving, $availability, $timesDay, $days_on);
        $seeder->run();
        // Ripristino le prenotazioni
        //$this->restoreReservationsAndOrders();
        $m = 'Seeder avvenuto con successso!';
        return back()->with('success', $m);
 
    }
    //da fixare
    public function restoreReservationsAndOrders()
    {
        $reservations = Reservation::all();

        if ($reservations) {
            foreach ($reservations as $reservation) {
                $date = Date::where('date_slot', $reservation->date_slot)->first();
                if ($date) {
                    $date->reserved = $date->reserved + $reservation->n_person;
                    $date->save();
                }
            }
        }

        $orders = Order::all();

        if ($orders) {
            foreach ($orders as $order) {
                $date = Date::where('date_slot', $order->date_slot)->first();
                if ($date) {
                    $date->reserved_pz_q = $date->reserved_pz_q + $order->total_pz_q;
                    if($date->reserved_pz_q > $date->max_px_q){
                        $date->visble_fq = 0;
                    }
                    if($date->reserved_pz_t > $date->max_px_t){
                        $date->visble_ft = 0;
                    }
                    $date->save();
                }
            }
        }
    }
}
