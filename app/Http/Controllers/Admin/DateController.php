<?php

namespace App\Http\Controllers\Admin;


use App\Models\Setting;
use App\Support\AvailabilityWeekSet;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DateController extends Controller
{
    public function generate(Request $request){    
        $data = $request->validate([
            'delay_or' => 'nullable|date_format:H:i',
            'delay_res' => 'nullable|date_format:H:i',
            'max_day_res' => 'nullable|integer|min:1|max:365',
            'times_start' => 'required|date_format:H:i',
            'times_end' => 'required|date_format:H:i',
            'times_interval' => 'required|integer|min:1|max:1440',
            'max_table' => 'nullable|integer|min:0',
            'max_table_1' => 'nullable|integer|min:0',
            'max_table_2' => 'nullable|integer|min:0',
            'max_asporto' => 'nullable|integer|min:0',
            'max_domicilio' => 'nullable|integer|min:0',
            'times_slot_' => 'nullable|array',
        ]);

        $times_slot = $data['times_slot_'] ?? [];
        $set = Setting::where('name', 'advanced')->first();
        $adv = json_decode($set->property, 1);

        $adv['delay_or'] = $data['delay_or'] ?? ($adv['delay_or'] ?? null);
        $adv['delay_res'] = $data['delay_res'] ?? ($adv['delay_res'] ?? null);
        $adv['max_day_res'] = $data['max_day_res'] ?? ($adv['max_day_res'] ?? null);
        $adv['times_start'] = $data['times_start'];
        $adv['times_end'] = $data['times_end'];
        $adv['times_interval'] = (int) $data['times_interval'];

        for ($i=1; $i < 8; $i++) { 
            if(!array_key_exists($i, $times_slot)){
                $times_slot[$i] = [];
            }
        }
        ksort($times_slot);

        $adv['week_set'] = AvailabilityWeekSet::normalize($times_slot);

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

    public function blockTime(Request $request)
    {
        try {
            $data = $request->validate([
                'date' => 'required|date_format:Y-m-d',
                'time' => 'required|date_format:H:i',
                'action' => 'required|in:block,unblock',
            ]);

            $date = $data['date'];
            $time = $data['time'];
            $action = $data['action'];

            $setting = Setting::where('name', 'advanced')->first();
            if (!$setting) {
                return response()->json(['success' => false, 'message' => 'Impostazioni non trovate']);
            }

            $adv = json_decode($setting->property, true);
            if (!is_array($adv)) {
                $adv = [];
            }

            if (!isset($adv['time_blocked'])) {
                $adv['time_blocked'] = [];
            }

            if (!isset($adv['time_blocked'][$date])) {
                $adv['time_blocked'][$date] = [];
            }

            if ($action === 'block') {
                if (!in_array($time, $adv['time_blocked'][$date])) {
                    $adv['time_blocked'][$date][] = $time;
                }
            } elseif ($action === 'unblock') {
                $adv['time_blocked'][$date] = array_values(array_filter($adv['time_blocked'][$date], fn($t) => $t !== $time));
                if (empty($adv['time_blocked'][$date])) {
                    unset($adv['time_blocked'][$date]);
                }
            }

            // pulizia orari bloccati nel passato
            foreach ($adv['time_blocked'] as $key => $times) {
                if ($key < now()->format('Y-m-d')) {
                    unset($adv['time_blocked'][$key]);
                }
            }

            $setting->property = json_encode($adv);
            $setting->save();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Log::error('Error in blockTime: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Errore interno del server']);
        }
    }
}

 
