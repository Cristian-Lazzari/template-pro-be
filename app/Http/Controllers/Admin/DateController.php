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
    public function generate(Request $request){    
        $data = $request->all();
        $times_slot = $data['times_slot_'];
        $set = Setting::where('name', 'advanced')->first();
        $adv = json_decode($set->property, 1);

        for ($i=1; $i < 8; $i++) { 
            if(!array_key_exists($i, $times_slot)){
                $times_slot[$i] = [];
            }
        }
        ksort($times_slot);

        $adv['week_set'] = $times_slot;

        if(isset($data['max_table'])){
            $adv['max_table'] = $data['max_table'];
        }
        if(isset($data['max_table_1']) && isset($data['max_table_2'])){
            $adv['max_table_1'] = $data['max_table_1'];
            $adv['max_table_2'] = $data['max_table_2'];
        }
        if(isset($data['max_asporto'])){
            $adv['max_asporto'] = $data['max_asporto'];
        }
        if(isset($data['max_domicilio'])){
            $adv['max_domicilio'] = $data['max_domicilio'];
        }



        $set->property = json_encode($adv);
        $set->update();

        $m = 'Date per ordinazioni e prenotazioni configurate correttamente!';
        return back()->with('success', $m);
    }




}

 