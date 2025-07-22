<?php

namespace App\Http\Controllers\Admin;

use App\Models\Date;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    private $aree = [
        'comune'       => 'required',
        'cap'          => 'required',
        'provincia'    => 'required',
    ];
    public function updateAree(Request $request){
        $setting = Setting::where('name', 'Comuni per il domicilio')->firstOrFail();
        $ar = $request->ar;
        if($ar == 'add'){ //se positivo aggiungie senno elimina
            $request->validate($this->aree);  
            $setting['property'] = json_decode($setting['property'], true);
            $newarea = [
                'id' => (count($setting['property']) + 1) . $request->cap ,
                'comune' => $request->comune,
                'provincia' => $request->provincia,
                'cap' => $request->cap,
                'price' => $request->price * 100,
            ];
            $isnew = true;
            foreach ($setting['property']  as $k) {
                if($k['cap'] == $request->cap){
                    $isnew = false;
                    $m = 'L\'area inserita era già presente! Non puoi avere due aree uguali o con lo stesso cap!';
                    return redirect()->back()->with('success', $m); 
                }
            }
            if ($isnew) {
                $newp = $setting['property'];

                array_push( $newp, $newarea);
                $setting['property'] = json_encode($newp);
                $setting->save();
            }
        }else{
            $comuni = $request->comuni;
            if($comuni !== null){
                $setting['property'] = json_decode($setting['property'], true);
                $upc = [];
                foreach ($setting['property'] as $co) {
                    if(!in_array($co['comune'], $comuni)){
                        array_push( $upc, $co);
                    }     
                }
                $setting['property'] = $upc;
                $setting['property'] = json_encode($upc);
                $setting->save();
            }
        }
        $m = 'Gli indirizzi sono stati aggiornati correttamente';

        return redirect()->back()->with('success', $m); 
    }
    
    public function advanced(Request $request){

        $adv = Setting::where('name', 'advanced')->first();
        if($adv){
  
            $property =json_decode($adv->property, 1);

            if($property['services'] !==  $request->services ||
                $property['too'] !==  $request->too ||
                $property['dt'] !==  $request->dt ){
                // Pulisco le tabelle
                DB::table('dates')->truncate(); 
            }

            $property['too'] = $request->services == 2 ? 0 : $request->too;
            $property['dt'] = $request->services == 3 ? 0 : $request->dt;
            $property['services'] = $request->services;
            $property['menu_fix_set'] = $request->menu_fix_set;
            
            $property['too_1'] = $request->too_1;
            $property['too_2'] = $request->too_2;
            $property['sala_1'] = $request->sala_1;
            $property['sala_2'] = $request->sala_2;
            $property['times_end'] = $request->times_end;
            $property['times_start'] = $request->times_start;
            $property['times_interval'] = $request->times_interval;
            $property['p_iva'] = $request->p_iva;
            $property['r_sociale'] = $request->r_sociale;
            $property['c_rea'] = $request->c_rea;
            $property['c_sociale'] = $request->c_sociale;
            $property['c_ateco'] = $request->c_ateco;
            $property['u_imprese'] = $request->u_imprese;
            $property['method'] = $request->method ? $request->method : [];
            $property['set_time'] = [];
            $property['max_day_res'] = $request->max_day_res;
            if($request->dt && in_array($request->services, [2,4])){
                $property['set_time'][] = $request->sala_1;
                $property['set_time'][] = $request->sala_2;
            }elseif(in_array($request->services, [2,4])){
                $property['set_time'][] = 'tavoli';
            }
            if($request->too && in_array($request->services, [3,4])){
                $property['set_time'][] = $request->too_1;
                $property['set_time'][] = $request->too_2;   
                $property['set_time'][] = 'domicilio';
            }elseif(in_array($request->services, [4,3])){
                $property['set_time'][] = 'asporto';
                $property['set_time'][] = 'domicilio';
            }
            $adv->property = json_encode($property);

            $adv->update();
            $m = 'Le impostazioni sono state ggiornate correttamente';
        }else{
            $m = 'ERRORE 404!';
        }
        return redirect()->back()->with('success', $m); 
    }
    public function numbers(Request $request){
        $numbers = $request->numbers;
        $setting = Setting::where('name', 'wa')->first();
        $old_p = json_decode($setting->property, true);
    
        $array_filtrato = [];
        foreach ($numbers as $n) {
           if($n !== null && $n !== ' '){
            array_push($array_filtrato, $n);
           }
        }
        $old_p['numbers'] = $array_filtrato;
        $old_p['last_response_wa_'] = $data = Carbon::today()->subDays(5)->toDateTimeString(); 
        $old_p['last_response_wa_2'] = $data = Carbon::today()->subDays(5)->toDateTimeString(); 
        $setting->property = json_encode($old_p);
        $setting->update();

        $m = 'I numeri sono stati aggiornati correttamente';
    
        return redirect()->back()->with('success', $m); 
    }
    public function updateAll(Request $request)
    {
        $setting = Setting::all();

        $tavoli = $request->tavoli_status;
        $asporto = $request->asporto_status;
        $ferie = $request->ferie_status;
        $ferie_from = $request->from;
        $ferie_to = $request->to;

        $delivery_cost = $request->delivery_cost;
        $min_price_a = $request->min_price_a;
        $min_price_d = $request->min_price_d;
        $pay_a = $request->asporto_pay;
        $pay_d = $request->domicilio_pay;

        $linK_fb = $request->facebook;
        $link_ig = $request->instagram;
        $link_yt = $request->youtube;
        $link_tt = $request->tiktok;
        $whatsapp = $request->whatsapp;

        $setting[0]->status = $tavoli;
        $setting[0]->save();

        $adv_s = Setting::where('name', 'advanced')->first();
        $property_adv = json_decode($adv_s->property, 1);  
        
        $setting[1]->status = $asporto;
        $prop_apsorto = [
            'pay' => intval($pay_a),
            'min_price' => $min_price_a * 100,
        ];
        $setting[1]->property = json_encode($prop_apsorto);
        $setting[1]->save();
        
        $setting[2]->status = $ferie;
        
        $propertyArray = [
            'from' => $ferie_from,
            'to' => $ferie_to,
        ];
        // Aggiorna il terzo setting
        $setting[2]->status = $ferie;
        $setting[2]->property = json_encode($propertyArray);
        $setting[2]->save();

        $giorni_attivita = [
            'lunedì'    =>  $request->lunedì,
            'martedì'   =>  $request->martedì,
            'mercoledì' =>  $request->mercoledì,
            'giovedì'   =>  $request->giovedì,
            'venerdì'   =>  $request->venerdì,
            'sabato'    =>  $request->sabato,
            'domenica'  =>  $request->domenica,
        ];
        $setting[3]->property = json_encode($giorni_attivita);
        $setting[3]->save();

        $oldPosition = json_decode($setting[4]['property'], 1);

        if(isset($oldPosition['foto_maps'])){
            $posizione = [
                'foto_maps' =>  $oldPosition['foto_maps'],
                'link_maps' =>  $request->link_maps,
                'indirizzo' =>  $request->indirizzo,
            ];
        
            if (isset($request->foto_maps)) {
                $imagePath = $request->file('foto_maps')->store('public/uploads');
                $posizione['foto_maps'] = $imagePath;
            }
        }else{
            $posizione = [
                'foto_maps' =>  "",
                'link_maps' =>  $request->link_maps,
                'indirizzo' =>  $request->indirizzo,
            ];
            if (isset($request->foto_maps)) {
                $imagePath = $request->file('foto_maps')->store('public/uploads');
                $posizione['foto_maps'] = $imagePath;
            }
        }
        $setting[4]->property = json_encode($posizione);
        $setting[4]->save();

        $contatti = [
            'telefono'  => $request->telefono,
            'email'     => $request->email,
            'instagram' => $link_ig,
            'facebook'  => $linK_fb,
            'youtube'   => $link_yt,
            'tiktok'    => $link_tt,
            'whatsapp'  => $whatsapp,
        ];
        $setting[5]->property = json_encode($contatti);
        $setting[5]->save();      
        

        if ($property_adv['services'] > 2) {
            $setting[7]->property = json_encode($setting[7]->property);
            $setting[6]->status = $request->domicilio_status;
            $prop_domicilio = [
                'pay' => intval($pay_d),
                'min_price' => $min_price_d * 100,
                'delivery_cost' => $delivery_cost * 100,
            ];
            $setting[6]->property = json_encode($prop_domicilio);
            $setting[6]->save();
        }
        
        $m = 'Le impostazioni sono state ggiornate correttamente';

        return redirect()->back()->with('success', $m);   
    }
}
