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
    public function index()
    {
        $dates = Date::paginate(100);

        return view('admin.dates.index', compact('dates'));
    }


    private $validations2 = [
        'max_reservations'      => 'required|integer',
    ];
    private $validations3t = [
        'max_domicilio'         => 'required|integer',
        'max_pz_q'              => 'required|integer',
        'max_pz_t'              => 'required|integer',
    ];
    private $validations3f = [
        'max_domicilio'         => 'required|integer',
        'max_asporto'           => 'required|integer',
    ];
    private $validations4t = [
        'max_domicilio'         => 'required|integer',
        'max_reservations'      => 'required|integer',
        'max_pz_q'              => 'required|integer',
        'max_pz_t'              => 'required|integer',
    ];
    private $validations4f = [
        'max_domicilio'         => 'required|integer',
        'max_reservations'      => 'required|integer',
        'max_asporto'           => 'required|integer',
    ];

    public function generate(Request $request)
    {
        $typeOfOrdering = true; 
        try {
            $request->validate($this->validations);
            if($typeOfOrdering){
                $max_pz_t = $request->input("max_pz_t");
                $max_pz_q = $request->input("max_pz_q");
            }else{
                $max_asporto = $request->input("max_asporto");
            }
            if($pack== 2 || )
            $max_reservations = $request->input("max_reservations");


            $max_domicilio = $request->input("max_domicilio");

            $days_off = $request->input("days_off");
            $times_slot1 = $request->input("times_slot_1");
            $times_slot2 = $request->input("times_slot_2");
            $times_slot3 = $request->input("times_slot_3");
            $times_slot4 = $request->input("times_slot_4");
            $times_slot5 = $request->input("times_slot_5");
            $times_slot6 = $request->input("times_slot_6");
            $times_slot7 = $request->input("times_slot_7");

            $timesDay = [];
            $day =[
                ['time' => '19:00', 'set' => ''] ,
                ['time' => '19:15', 'set' => ''] ,
                ['time' => '19:30', 'set' => ''] ,
                ['time' => '19:45', 'set' => ''] ,
                ['time' => '20:00', 'set' => ''] ,
                ['time' => '20:15', 'set' => ''] ,
                ['time' => '20:30', 'set' => ''] ,
            ];
            for ($i=0; $i < 6; $i++) { 
                for ($s = 0; $s < count($timesDay[$i]); $i++) {
                    $timesDay[$i][$s]['set'] = $times_slot1[$s];
                }
                array_push($timesDay, $day);
            }

            // Pulisco le tabelle
            DB::table('dates')->truncate();

            // Eseguo il seeder
            $seeder = new DatesTableSeeder();
            $seeder->setVariables($max_reservations, $max_pz_q, $max_pz_t, $timesDay, $days_off, $max_domicilio);
            $seeder->run();

            // Ripristino le prenotazioni
            $this->restoreReservationsAndOrders();

            return back()->with('success', 'Seeder avvenuto con successo')->with('response', [
                'success' => true,
                'message'  => 'Seeder avvenuto con successo',
            ]);
        } catch (Exception $e) {
            $trace = $e->getTrace();
            $errorInfo = [
                'success' => false,
                'error' => 'Si è verificato un errore durante l\'elaborazione della richiesta: ' . $e->getMessage(),
                'file' => $trace[0]['file'],
                'line' => $trace[0]['line'],
            ];

            return response()->json($errorInfo, 500);
        }
    }

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
