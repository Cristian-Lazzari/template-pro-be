<?php

namespace App\Http\Controllers\Admin;

use DateTime;
use Exception;
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
        'max_reservations'      => 'required|integer',
        'days_on'               => 'required',
    ];
    private $validations2dt = [
        'max_reservations_1'    => 'required|integer',
        'max_reservations_2'    => 'required|integer',
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
        'max_reservations'      => 'required|integer',
        'max_cucina_1'          => 'required|integer',
        'max_cucina_2'          => 'required|integer',
        'days_on'               => 'required',
    ];
    private $validations4f = [
        'max_domicilio'         => 'required|integer',
        'max_reservations'      => 'required|integer',
        'max_asporto'           => 'required|integer',
        'days_on'               => 'required',
    ];
    private $validations4tdt = [
        'max_domicilio'         => 'required|integer',
        'max_reservations_1'    => 'required|integer',
        'max_reservations_2'    => 'required|integer',
        'max_cucina_1'          => 'required|integer',
        'max_cucina_2'          => 'required|integer',
        'days_on'               => 'required',
    ];
    private $validations4fdt = [
        'max_domicilio'         => 'required|integer',
        'max_reservations_1'    => 'required|integer',
        'max_reservations_2'    => 'required|integer',
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

            $dates = Date::all();
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
            $firstDay = [
                'year' => $dates[0]['year'],
                'month' => $dates[0]['month'],
                'day' => $dates[0]['day'],
            ];
                
            foreach ($dates as $d) {
                list($date, $time) = explode(" ", $d['date_slot']);
                
                if($d['reserving'] !== '0'){
                    $res = json_decode($d['reserving'], 1);

                    // Prepara base comune per $day
                    $day = [
                        'day' => $d['day'],
                        'day_w' => $d['day_w'],
                        'date' => $date,
                        'time' => [],
                    ];

                    // Prepara base comune per $time
                    $time = [
                        'time' => $d['time'],
                    ];

    
                    // Helpers per assegnare valori in base alla configurazione
                    
                    
                    // Assegnazione in base al pack
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
                        $time['asporto'] = $asporto;
                        $time['domicilio'] = $domicilio;
                        $time['table'] = $table;
                    }

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
                        array_push($year[$cy]['days'][count($year[$cy]['days']) - 1]['time'], $time);
                    }elseif($d['day'] == $firstDay['day']){
                        if($pack == 2 ){        
                            $year[$cy]['days'][count($year[$cy]['days']) - 1]['table'] += $day['table'];        
                        }elseif($pack == 3){
                            $year[$cy]['days'][count($year[$cy]['days']) - 1]['asporto'] += $day['asporto'];
                            $year[$cy]['days'][count($year[$cy]['days']) - 1]['domicilio'] += $day['domicilio'];
                            
                        }elseif($pack == 4){
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


    public function generate(Request $request)
    {    
        $data = $request->all();
        
        // Configurazione delle validazioni e disponibilità
        $configs = [
            // double_t = false
            false => [
                2 => [
                    'validation' => 'validations2',
                    'availability' => fn($data) => ['table' => $data['max_reservations']],
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
                            'table' => $data['max_reservations'],
                            'cucina_1' => $data['max_cucina_1'],
                            'cucina_2' => $data['max_cucina_2'],
                            'domicilio' => $data['max_domicilio'],
                        ],
                    ],
                    false => [
                        'validation' => 'validations4f',
                        'availability' => fn($data) => [
                            'table' => $data['max_reservations'],
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
                        'table_1' => $data['max_reservations_1'],
                        'table_2' => $data['max_reservations_2'],
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
                            'table_1' => $data['max_reservations_1'],
                            'table_2' => $data['max_reservations_2'],
                            'cucina_1' => $data['max_cucina_1'],
                            'cucina_2' => $data['max_cucina_2'],
                            'domicilio' => $data['max_domicilio'],
                        ],
                    ],
                    false => [
                        'validation' => 'validations4fdt',
                        'availability' => fn($data) => [
                            'table_1' => $data['max_reservations_1'],
                            'table_2' => $data['max_reservations_2'],
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
            $request->validate($this->{$config['validation']});
            $availability = $config['availability']($data);

            // Inizializza reserving a 0
            $reserving = array_map(fn() => 0, $availability);
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

        $day = [];
        $start = new DateTime($property_adv['times_start']);
        $end = new DateTime($property_adv['times_end']);
        $index = 1;
        $interval = $property_adv['times_interval'];

        // Loop finché l'orario di inizio è inferiore all'orario di fine
        while ($start <= $end) {
            $day[$index] = [
                'time' => $start->format('H:i'),
                'set' => ''
            ];
            // Incrementa l'orario di inizio con l'intervallo specificato
            $start->modify("+$interval minutes");
            $index++;
        }



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
        $m = 'Date per ordinazioni e prenotazioni configurate correttamente!';
        return back()->with('success', $m);
 
    }
    //da fixare
    public function restoreReservationsAndOrders()
    {
        $reservations = Reservation::where('status' , 2)->orWhere('status', 1)->all();
        if ($reservations) {
            foreach ($reservations as $reservation) {
                $date = Date::where('date_slot', $reservation->date_slot)->firstOrFail();
                if ($date) {
                    $av = json_decode($date->availability);
                    $res = json_decode($date->reserved);
                    $res['table'] += $reservation->n_person;
                    if($res['table'] >= $av['table']){
                        $vis = json_decode($date->visible);
                        $vis = 0;
                    }
                    $date->save();
                }
            }
        }

        $orders = Order::where('status' , 2)->orWhere('status', 1)->all();
        if ($orders) {
            foreach ($orders as $order) {
                $date = Date::where('date_slot', $order->date_slot)->firstOrFail();
                if ($date) {
                    $av = json_decode($date->availability);
                    $res = json_decode($date->reserved);
                    $products = $order->products;
                    foreach ($products as $key => $value) {
                        # code...
                    }
                    $res['cucina_1'] += $npezzi;
                    $res['cucina_2'] += $order->n_person;
                    if($res['cucina_1'] >= $av['cucina_1']){
                        $vis = json_decode($date->visible);
                        $vis = 0;
                    }
                    $date->save();
                }
            }
        }
    }


}
