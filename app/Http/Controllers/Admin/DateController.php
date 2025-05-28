<?php

namespace App\Http\Controllers\Admin;

use DateTime;
use Exception;
use Carbon\Carbon;
use App\Models\Date;
use App\Models\Order;
use App\Models\Setting;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Database\Seeders\DatesTableSeeder;

class DateController extends Controller
{
    private $validations2 = [
        'max_table'      => 'required|integer',
        'days_on'               => 'required',
    ];
    private $validations2dt = [
        'max_table_1'    => 'required|integer',
        'max_table_2'    => 'required|integer',
        'days_on'               => 'required',
    ];
    private $validations3t = [
        'max_domicilio'         => 'required|integer',
        'max_cucina_1'          => 'required|integer',
        'max_cucina_2'          => 'required|integer',
        'days_on'               => 'required',
    ];
    private $validations3f = [
        'max_domicilio'         => 'required|integer',
        'max_asporto'           => 'required|integer',
        'days_on'               => 'required',
    ];
    private $validations4t = [
        'max_domicilio'         => 'required|integer',
        'max_table'      => 'required|integer',
        'max_cucina_1'          => 'required|integer',
        'max_cucina_2'          => 'required|integer',
        'days_on'               => 'required',
    ];
    private $validations4f = [
        'max_domicilio'         => 'required|integer',
        'max_table'      => 'required|integer',
        'max_asporto'           => 'required|integer',
        'days_on'               => 'required',
    ];
    private $validations4tdt = [
        'max_domicilio'         => 'required|integer',
        'max_table_1'    => 'required|integer',
        'max_table_2'    => 'required|integer',
        'max_cucina_1'          => 'required|integer',
        'max_cucina_2'          => 'required|integer',
        'days_on'               => 'required',
    ];
    private $validations4fdt = [
        'max_domicilio'         => 'required|integer',
        'max_table_1'    => 'required|integer',
        'max_table_2'    => 'required|integer',
        'max_asporto'           => 'required|integer',
        'days_on'               => 'required',
    ];
    public function index() {
        // Recupera le configurazioni
        $adv_s = Setting::where('name', 'advanced')->first();
        $property_adv = json_decode($adv_s->property, 1);  
            
        $double = $property_adv['dt'];
        $pack = $property_adv['services'];
        $type = $property_adv['too'];
        $times_end = $property_adv['times_end'];
        $times_start = $property_adv['times_start'];
        $times_interval = $property_adv['times_interval'];

        $dates = Date::select('*') // o specifica i campi che ti servono
            ->selectRaw("
                STR_TO_DATE(
                    CASE
                        WHEN date_slot LIKE '%null%' THEN REPLACE(date_slot, ' null', ' 00:00')
                        ELSE date_slot
                    END,
                    '%d/%m/%Y %H:%i'
                ) AS order_slot
            ")
            ->orderBy('order_slot')
            ->get();
        if(count($dates) == 0){
            return view('admin.Dates.index', compact('double', 'times_end', 'times_start', 'times_interval', 'double', 'pack', 'type', 'property_adv'));
        }
        $year = [
            1 => [
                'year' => $dates[0]['year'],
                'month'=> $dates[0]['month'],
                'days'=> [],
            ]
        ];
        //dd($dates[1]);
        $firstDay = [ 'year' => $dates[0]['year'], 'month' => $dates[0]['month'], 'day' => $dates[0]['day'],];       
        
        foreach ($dates as $d) {
            list($date, $time) = explode(" ", $d['date_slot']);
            $cy = count($year);

            $d1 = Carbon::create($firstDay['year'], $firstDay['month'], $firstDay['day']);
            $d2 = Carbon::create($d['year'], $d['month'], $d['day']);
            if ($d1->gt($d2)) {
                [$d1, $d2] = [$d2, $d1];
            }
            $diffInDays = $d1->diffInDays($d2);
            if ($diffInDays >= 1) {
                // Altrimenti crea array di giorni mancanti
                $current = $d1->copy()->addDay();

                for ($i = 1; $i < $diffInDays; $i++) {
                    array_push($year[$cy]['days'], [
                        'day' => $current->day,
                        'day_w' => $current->dayOfWeekIso,
                        'date' => $current->format('d/m/Y')]);
                        
                    if($current->isLastOfMonth()){
                        $current->addDay();
                        array_push($year, [
                            'year' =>  $current->year,
                            'month' => $current->month,
                            'days' => [],
                        ]);
                        //dump($diffInDays);
                        $cy++;
                    }else{
                        $current->addDay();
                    }
                    //dump($current->day);
                }
                $current->subDay(); // Rimuove il giorno corrente per evitare duplicati
                $firstDay = [
                    'year' =>  $current->year,
                    'month' =>  $current->month,
                    'day' =>  $current->day,
                ];
            }
            if($d['reserving'] !== '0'){
                $res = json_decode($d['reserving'], 1);
                // Prepara base comune per $day
                $day = [
                    'id' =>  $d['id'],
                    'day' => $d['day'],
                    'day_w' => $d['day_w'],
                    'date' => $date,
                    'time' => [],
                ];
                // Prepara base comune per $time
                $time = [
                    'time' => $d['time'],
                ];
                if ($pack == 2) {
                    $table = $double ? $res['table_1'] + $res['table_2'] : $res['table'];
                    $day['table'] = $table;
                    $time['table'] = $table;
                } elseif ($pack == 3) {
                    $asporto = $type 
                    ? ($res['cucina_1'] + $res['cucina_2']) 
                    : ($res['asporto'] ?? null);
                    $domicilio = $res['domicilio'] ?? null;
                    $day['asporto'] = $asporto;
                    $day['domicilio'] = $domicilio;
                    $time['asporto'] = $asporto;
                    $time['domicilio'] = $domicilio;
                } elseif ($pack == 4) {
                    $asporto = $type 
                    ? ($res['cucina_1'] + $res['cucina_2']) 
                    : ($res['asporto'] ?? null);
                    $domicilio = $res['domicilio'] ?? null;
                    $table = $double ? $res['table_1'] + $res['table_2'] : $res['table'];
                    $day['asporto'] = $asporto;
                    $day['domicilio'] = $domicilio;
                    $day['table'] = $table;
                }

            }
            $cd = count($year[$cy]['days']) - 1;
            if($d['year'] == $firstDay['year'] && $d['month'] == $firstDay['month']){
                
                
                if( $d['time'] == 0 ){
                    array_push($year[$cy]['days'], $dayoff = [
                        'id' =>  $d['id'],
                        'day' => $d['day'],
                        'day_w' => $d['day_w'],
                        'date' => $date]);
                }elseif($d['day'] !== $firstDay['day'] || count($year[1]['days']) == 0){
                    array_push($year[$cy]['days'], $day);
                    $cd ++;
                    array_push($year[$cy]['days'][$cd]['time'], $time);
                }elseif($d['day'] == $firstDay['day']){
                    if( $pack == 2 ){        
                        $year[$cy]['days'][$cd]['table'] += $day['table'];        
                    }elseif( $pack == 3){
                        $year[$cy]['days'][$cd]['asporto'] += $day['asporto'];
                        $year[$cy]['days'][$cd]['domicilio'] += $day['domicilio'];
                    }elseif( $pack == 4){
                        $year[$cy]['days'][$cd]['table'] += $day['table'];
                        $year[$cy]['days'][$cd]['domicilio'] += $day['domicilio'];
                        $year[$cy]['days'][$cd]['asporto'] += $day['asporto'];
                        
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
                        'id' =>  $d['id'],
                        'day' => $d['day'],
                        'day_w' => $d['day_w'],
                        'date' => $date]);
                }
                array_push($year, $month);
            }
        
            $firstDay = [
                'year' =>  $d['year'],
                'month' =>  $d['month'],
                'day' =>  $d['day'],
            ];
           
            
        };
        return view('admin.Dates.index', compact('year', 'times_end', 'times_start', 'times_interval', 'double', 'pack', 'type', 'property_adv'));
    }

    public function showDay(Request $request){
        $date = $request->input('date');
        // Recupera i record corrispondenti
        $dayr = Date::where('date_slot', 'like', '%' . $date . '%')->get();
        // Controlla se c'è solo un record e se il campo `time` è uguale a 0
        if ($dayr->count() == 1 && $dayr[0]['time'] == 0) {
            return to_route('admin.dates.index')->with('not_found', $dayr);
        }
        // Aggiungi le chiavi `or` e `res` ai record
        $day = $dayr->map(function ($item) {
            $item->or = [];  // Inizializza ordini
            $item->res = []; // Inizializza prenotazioni
            return $item;
        });
        // Itera sui record per popolare le chiavi
        foreach ($day as $time) {
            // Recupera gli ordini e le prenotazioni per lo specifico date_slot
            $order = Order::where('date_slot', $time->date_slot)->where('status', '!=', 4)->get();
            $reservation = Reservation::where('date_slot', $time->date_slot)->where('status', '!=', 4)->get();
        
            // Aggiungi i dati ai rispettivi campi
            $time->or = $order;
            $time->res = $reservation;
        }
        $adv_s = Setting::where('name', 'advanced')->first();
        $property_adv = json_decode($adv_s->property, 1);  
        $set_time = $property_adv['set_time'];
        //dd($property_adv);
        return view('admin.Dates.showDay', compact('day', 'set_time', 'property_adv'));   
    }

    public function status(Request $request){
        $id = $request->input('id');
        $date = Date::where('id', $id)->firstOrFail();

        // Recupera le configurazioni
        $adv_s = Setting::where('name', 'advanced')->first();
        $property_adv = json_decode($adv_s->property, 1);  
            
        $double = $property_adv['dt'];
        $pack = $property_adv['services'];
        $type = $property_adv['too'];


        switch ($pack) {
            case 2:
                $av = [
                    'table' => $double ? intval($request->avtable_1) : intval($request->avtable),
                ];
                $vis = [
                    'table' => $double ? intval($request->vistable_1) : intval($request->vistable),
                ];
                if ($double) {
                    $av['table_2'] = intval($request->avtable_2);
                    $vis['table_2'] = intval($request->vistable_2);
                }
                break;

            case 3:
                if ($type) {
                    $av = [
                        'cucina_1' => intval($request->avcucina_1),
                        'cucina_2' => intval($request->avcucina_2),
                        'domicilio' => intval($request->avdomicilio),
                    ];
                    $vis = [
                        'cucina_1' => intval($request->viscucina_1),
                        'cucina_2' => intval($request->viscucina_2),
                        'domicilio' => intval($request->visdomicilio),
                    ];
                } else {
                    $av = [
                        'asporto' => intval($request->avasporto),
                        'domicilio' => intval($request->avdomicilio),
                    ];
                    $vis = [
                        'asporto' => intval($request->visasporto),
                        'domicilio' => intval($request->visdomicilio),
                    ];
                }
                break;

            case 4:
                $av = $vis = [];
                if ($double) {
                    $av['table_1'] = intval($request->avtable_1);
                    $av['table_2'] = intval($request->avtable_2);
                    $vis['table_1'] = intval($request->vistable_1);
                    $vis['table_2'] = intval($request->vistable_2);
                } else {
                    $av['table'] = intval($request->avtable);
                    $vis['table'] = intval($request->vistable);
                }
                if ($type) {
                    $av['cucina_1'] = intval($request->avcucina_1);
                    $av['cucina_2'] = intval($request->avcucina_2);
                    $av['domicilio'] = intval($request->avdomicilio);

                    $vis['cucina_1'] = intval($request->viscucina_1);
                    $vis['cucina_2'] = intval($request->viscucina_2);
                    $vis['domicilio'] = intval($request->visdomicilio);
                } else {
                    $av['asporto'] = intval($request->avasporto);
                    $av['domicilio'] = intval($request->avdomicilio);

                    $vis['asporto'] = intval($request->visasporto);
                    $vis['domicilio'] = intval($request->visdomicilio);
                }
                break;
        }

        $date->availability = json_encode($av);
        $date->visible = json_encode($vis);
        $date->update();

        $m = 'Questo orario: ' . $date->time . ' e\' stato ggiornato correttamente';


        return redirect()->back()->with('success', $m);
    }
    public function editDays(Request $request){
        $data = $request->all();
        //dd($data);
        $adv_s = Setting::where('name', 'advanced')->first();
            $property_adv = json_decode($adv_s->property, 1);  
                
            $double = $property_adv['dt'];
            $pack = $property_adv['services'];
            $type = $property_adv['too'];
        foreach ($data['dates'] as $d) {
            $date = Date::where('id' , $d)->first();
            foreach ($data['times'] as $key => $value) {
                $status = $this->getStatus($value);
                $visible = [
                    'table' => in_array($status,[2,3,6,7]) ? 1 : 0,
                    'table_1' => in_array($status,[2,3,6,7]) ? 1 : 0,
                    'table_2' => in_array($status,[2,3,6,7]) ? 1 : 0,
                    'asporto' => in_array($status,[1,3,4,5]) ? 1 : 0,
                    'cucina_1' => in_array($status,[1,3,4,5]) ? 1 : 0,
                    'cucina_2' => in_array($status,[1,3,4,5]) ? 1 : 0,
                    'domicilio' => $status >= 4 ? 1 : 0,
                ];
                $availability = [
                    'table' => $data['max_table'],
                    'table_1' => $data['max_table_1'],
                    'table_2' => $data['max_table_2'],
                    'asporto' => $data['max_asporto'],
                    'cucina_1' => $data['max_cucina_1'],
                    'cucina_2' => $data['max_cucina_2'],
                    'domicilio' => $data['max_domicilio'],
                ];
                $reserving = [
                    'table' => 0,
                    'table_1' => 0,
                    'table_2' => 0,
                    'asporto' => 0,
                    'cucina_1' => 0,
                    'cucina_2' => 0,
                    'domicilio' => 0,
                ];
                
                if( $pack == 2 ){ 
                    if($double){
                        unset($visible['table']);
                        unset($reserving['table']);
                        unset($availability['table']);
                    }else{
                        unset($visible['table_1'], $visible['table_2']);
                        unset($reserving['table_1'], $reserving['table_2']);
                        unset($availability['table_1'], $availability['table_2']);
                    }
                    unset($visible['asporto'], $visible['cucina_1'], $visible['cucina_2'], $visible['domicilio']);
                    unset($reserving['asporto'], $reserving['cucina_1'], $reserving['cucina_2'], $reserving['domicilio']);
                    unset($availability['asporto'], $availability['cucina_1'], $availability['cucina_2'], $availability['domicilio']);
                }elseif( $pack == 3){
                    if($type){
                        unset($visible['asporto']);
                        unset($reserving['asporto']);
                        unset($availability['asporto']);
                    }else{
                        unset($visible['cucina_1'], $visible['cucina_2']);
                        unset($reserving['cucina_1'], $reserving['cucina_2']);
                        unset($availability['cucina_1'], $availability['cucina_2']);
                    }
                    unset($visible['table'], $visible['table_1'], $visible['table_2']);
                    unset($reserving['table'], $reserving['table_1'], $reserving['table_2']);
                    unset($availability['table'], $availability['table_1'], $availability['table_2']);
                }elseif( $pack == 4){    
                    if($double){
                        unset($visible['table']);
                        unset($reserving['table']);
                        unset($availability['table']);
                    }else{
                        unset($visible['table_1'], $visible['table_2']);
                        unset($reserving['table_1'], $reserving['table_2']);
                        unset($availability['table_1'], $availability['table_2']);
                    }
                    if($type){
                        unset($visible['asporto']);
                        unset($reserving['asporto']);
                        unset($availability['asporto']);
                    }else{
                        unset($visible['cucina_1'], $visible['cucina_2']);
                        unset($reserving['cucina_1'], $reserving['cucina_2']);
                        unset($availability['cucina_1'], $availability['cucina_2']);
                    }
                }
                $date->delete();
                
                Date::create([  
                    'year' => $date->year,
                    'month' => $date->month,
                    'day' => $date->day,
                    'day_w' => $date->day_w,
                    'time' => $key,
                    'date_slot' => str_replace("null.", $key, $date->date_slot),
                    'status' => $status,
                    'reserving' => json_encode($reserving),
                    'visible' => json_encode($visible),
                    'availability' => json_encode($availability),
                ]);
            }
        }
        $m = 'Giorni aggiunti correttamente!';
        return back()->with('success', $m);


    }

    public function generate(Request $request)
    {    
        $data = $request->all();
        //dd($data);
        
        // Configurazione delle validazioni e disponibilità
        $configs = [
            // double_t = false
            false => [
                2 => [
                    'validation' => 'validations2',
                    'availability' => fn($data) => ['table' => $data['max_table']],
                ],
                3 => [
                    true => [
                        'validation' => 'validations3t',
                        'availability' => fn($data) => [
                            'cucina_1' => $data['max_cucina_1'],
                            'cucina_2' => $data['max_cucina_2'],
                            'domicilio' => $data['max_domicilio'],
                        ],
                    ],
                    false => [
                        'validation' => 'validations3f',
                        'availability' => fn($data) => [
                            'asporto' => $data['max_asporto'],
                            'domicilio' => $data['max_domicilio'],
                        ],
                    ],
                ],
                4 => [
                    true => [
                        'validation' => 'validations4t',
                        'availability' => fn($data) => [
                            'table' => $data['max_table'],
                            'cucina_1' => $data['max_cucina_1'],
                            'cucina_2' => $data['max_cucina_2'],
                            'domicilio' => $data['max_domicilio'],
                        ],
                    ],
                    false => [
                        'validation' => 'validations4f',
                        'availability' => fn($data) => [
                            'table' => $data['max_table'],
                            'asporto' => $data['max_asporto'],
                            'domicilio' => $data['max_domicilio'],
                        ],
                    ],
                ],
            ],
            // double_t = true
            true => [
                2 => [
                    'validation' => 'validations2dt',
                    'availability' => fn($data) => [
                        'table_1' => $data['max_table_1'],
                        'table_2' => $data['max_table_2'],
                    ],
                ],
                3 => [
                    true => [
                        'validation' => 'validations3t',
                        'availability' => fn($data) => [
                            'cucina_1' => $data['max_cucina_1'],
                            'cucina_2' => $data['max_cucina_2'],
                            'domicilio' => $data['max_domicilio'],
                        ],
                    ],
                    false => [
                        'validation' => 'validations3f',
                        'availability' => fn($data) => [
                            'asporto' => $data['max_asporto'],
                            'domicilio' => $data['max_domicilio'],
                        ],
                    ],
                ],
                4 => [
                    true => [
                        'validation' => 'validations4tdt',
                        'availability' => fn($data) => [
                            'table_1' => $data['max_table_1'],
                            'table_2' => $data['max_table_2'],
                            'cucina_1' => $data['max_cucina_1'],
                            'cucina_2' => $data['max_cucina_2'],
                            'domicilio' => $data['max_domicilio'],
                        ],
                    ],
                    false => [
                        'validation' => 'validations4fdt',
                        'availability' => fn($data) => [
                            'table_1' => $data['max_table_1'],
                            'table_2' => $data['max_table_2'],
                            'asporto' => $data['max_asporto'],
                            'domicilio' => $data['max_domicilio'],
                        ],
                    ],
                ],
            ],
        ];
        // Recupera le configurazioni
        $adv_s = Setting::where('name', 'advanced')->first();
        $property_adv = json_decode($adv_s->property, 1);  
           
        $double = $property_adv['dt'];
        $pack = $property_adv['services'];
        $type = $property_adv['too'];

        // Estraggo config corretta
        $config = $configs[$double][$pack] ?? null;
        if (is_array($config) && array_key_exists($type, $config)) {
            $config = $config[$type];
        }

        if ($config) {
            //$request->validate($this->{$config['validation']});
            $availability = $config['availability']($data);
            // Inizializza reserving a 0
            $reserving = array_map(fn() => 0, $availability);
        }

        $days_on     = $request->input("days_on");

        $timesDay = [];

        $day = [];
        $start = new DateTime($property_adv['times_start']);
        $end = new DateTime($property_adv['times_end']);
        $index = 1;
        $interval = $property_adv['times_interval'];

        // Loop finché l'orario di inizio è inferiore all'orario di fine
        // while ($start <= $end) {
        //     $day[$index] = [ $start->format('H:i') => '' ];
        //     // Incrementa l'orario di inizio con l'intervallo specificato
        //     $start->modify("+$interval minutes");
        //     $index++;
        // }


        for ($i = 0; $i < 7; $i++) { 
            array_push($timesDay, $day);
        }


        if(isset($data["times_slot_1"])){
            foreach ($data["times_slot_1"] as $key => $value) {
                $timesDay[0][$key] = $this->getStatus($value);
            }
        }
        if(isset($data["times_slot_2"])){
            foreach ($data["times_slot_2"] as $key => $value) {
                $timesDay[1][$key] = $this->getStatus($value);
            }
        }
        if(isset($data["times_slot_3"])){
            foreach ($data["times_slot_3"] as $key => $value) {
                $timesDay[2][$key] = $this->getStatus($value);
            }
        }
        if(isset($data["times_slot_4"])){
            foreach ($data["times_slot_4"] as $key => $value) {
                $timesDay[3][$key] = $this->getStatus($value);
            }
        }
        if(isset($data["times_slot_5"])){
            foreach ($data["times_slot_5"] as $key => $value) {
                $timesDay[4][$key] = $this->getStatus($value);
            }
        }
        if(isset($data["times_slot_6"])){
            foreach ($data["times_slot_6"] as $key => $value) {
                $timesDay[5][$key] = $this->getStatus($value);
            }
        }
        if(isset($data["times_slot_7"])){
            foreach ($data["times_slot_7"] as $key => $value) {
                $timesDay[6][$key] = $this->getStatus($value);
            }
        }
       


      
        // Pulisco le tabelle
        DB::table('dates')->truncate();
        // Eseguo il seeder
        $seeder = new DatesTableSeeder();
        $seeder->setVariables($reserving, $availability, $timesDay, $days_on);
        $seeder->run();
        // Ripristino le prenotazioni
        $this->restoreReservationsAndOrders();
        $m = 'Date per ordinazioni e prenotazioni configurate correttamente!';
        return back()->with('success', $m);
 
    }

    protected function restoreReservationsAndOrders() {
        // Ripristino le prenotazioni
        $reservations = Reservation::all();
        $orders = Order::all();
        $adv_s = Setting::where('name', 'advanced')->first();
        $property_adv = json_decode($adv_s->property, 1);
        foreach ($reservations as $reservation) {
            if (in_array($reservation->status, [2, 1, 3, 5])) {
                
                $date = Date::where('date_slot', $reservation->date_slot)->first();
                if ($date) {
                    $reserving = json_decode($date->reserving, true);
                    $visible = json_decode($date->visible, true);
                    $availability = json_decode($date->availability, true);
                    if($property_adv['dt']){
                        if($reservation->sala == 1){
                            $reserving['table_1'] += json_decode($reservation->n_person, true)['adult'] + json_decode($reservation->n_person, true)['child'];
                            $visible['table_1'] = $availability['table_1'] > $reserving['table_1'] ? 1 : 0;
                        }else{
                            $reserving['table_2'] += json_decode($reservation->n_person, true)['adult'] + json_decode($reservation->n_person, true)['child'];
                            $visible['table_2'] = $availability['table_2'] > $reserving['table_2'] ? 1 : 0;
                        }
                    }else{
                        $reserving['table'] += json_decode($reservation->n_person, true)['adult'] + json_decode($reservation->n_person, true)['child'];
                        $visible['table'] = $availability['table'] > $reserving['table'] ? 1 : 0;
                    }
                    $date->reserving = json_encode($reserving);
                    $date->visible = json_encode($visible);
                    $date->availability = json_encode($availability);
                    $date->save();
                }else{
                    $datetime = Carbon::createFromFormat('d/m/Y H:i', $reservation->date_slot);
                    if($property_adv['dt']){
                        $reserving = [
                            'table_1' => 0,
                            'table_2' => 0,
                        ];
                        $visible = [
                            'table_1' => 0,
                            'table_2' => 0,
                        ];
                        if($reservation->sala == 1){
                            $reserving['table_1'] = json_decode($reservation->n_person, true)['adult'] + json_decode($reservation->n_person, true)['child'];
                        }else{
                            $reserving['table_2'] = json_decode($reservation->n_person, true)['adult'] + json_decode($reservation->n_person, true)['child'];
                        }
                    }else{
                        $reserving = [
                            'table' => json_decode($reservation->n_person, true)['adult'] + json_decode($reservation->n_person, true)['child'],
                        ];
                        $visible = [
                            'table' => 0,
                        ];
                    }
                    $soloData = explode(' ', $reservation->date_slot)[0] . ' null.';
                    $data_delete = Date::where('date_slot', $soloData )->first();
                    if($data_delete){
                        $data_delete->delete();
                    }
                    
                    Date::create([  
                        'year' => $datetime->year,
                        'month' => $datetime->month,
                        'day' => $datetime->day,
                        'day_w' => $datetime->isoWeekday(),
                        'time' =>  $datetime->format('H:i'),
                        'date_slot' => $reservation->date_slot,
                        'status' => 7,
                        'reserving' => json_encode($reserving),
                        'visible' => json_encode($visible),
                        'availability' => json_encode($visible),
                    ]);
                }
            }
        }
        // $reserving['asporto'] += $reservation->asporto;
        // $reserving['cucina_1'] += $reservation->cucina_1;
        // $reserving['cucina_2'] += $reservation->cucina_2;
        // $reserving['domicilio'] += $reservation->domicilio;
        // Date::create([  
        //     'year' => ,
        //     'month' => ,
        //     'day' => ,
        //     'day_w' => ,
        //     'time' => ,
        //     'date_slot' =>,
        //     'status' => ,
        //     'reserving' => ,
        //     'visible' => ,
        //     'availability' => ,
        // ]);
    }
    protected function getStatus($value){
        $status = 7;
        if(in_array(1,$value) && count($value) == 1){
            $status = 1; //asporto
        }elseif (in_array(2,$value) && count($value) == 1) {
            $status = 2; //tavoli
        }elseif (in_array(3,$value) && count($value) == 1) {
            $status = 4; //domicilio
        }elseif (in_array(1,$value) && in_array(2,$value) && count($value) == 2) {
            $status = 3; //asporto tavoli
        }elseif(in_array(1,$value) && in_array(3,$value) && count($value) == 2) {
            $status = 5; //asporto domicilio
        }elseif(in_array(2,$value) && in_array(3,$value) && count($value) == 2) {
            $status = 6; //tavoli domicilio
        }
        return $status;
    }



}
