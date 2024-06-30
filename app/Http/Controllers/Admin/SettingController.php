<?php

namespace App\Http\Controllers\Admin;

use App\Models\Date;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

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
            $setting[7]['property'] = json_decode($setting[7]['property'], true);
            $isnew = true;
            foreach ($setting[7]['property']  as $k) {
                if($k['comune'] == $comune){
                    $isnew = false;
                }
            }
            if ($isnew && $comune !== " ") {
                $newp = $setting[7]['property'];

                array_push( $newp, $newarea);
                $setting[7]['property'] = json_encode($newp);
                $setting[7]->save();
            }
        }else{
            $comuni = $request->comuni;
            if($comuni !== null){
                $setting[7]['property'] = json_decode($setting[7]['property'], true);
                $upc = [];
                foreach ($setting[7]['property'] as $co) {
                    if(!in_array($co['comune'], $comuni)){
                        array_push( $upc, $co);
                    }     
                }
                $setting[7]['property'] = $upc;
                $setting[7]['property'] = json_encode($upc);
                $setting[7]->save();
            }
        }
        return redirect()->back();
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

        dump('STAMPA DELLA REQUEST PRIMA DI TUTTO: ', $request->foto_maps);
        $posizione = [
            'foto_maps' =>  "",
            'link_maps' =>  $request->link_maps,
            'indirizzo' =>  $request->indirizzo,
        ];

        if (isset($request->foto_maps)) {
            $imagePath = Storage::put('public/uploads', $request->foto_maps);
            $posizione['foto_maps'] = $imagePath;
        }
        dump('DOPO IL CARICAMENTO DEL FILE: ', $posizione['foto_maps']); 

        $setting[4]->property = json_encode($posizione);
        dump('DOPO IL SALVATAGGIO: ', $setting[4]->property);
        $setting[4]->save();

        $contatti = [
            'telefono'  => $request->telefono,
            'email'     => $request->email,
        ];
        $setting[5]->property = json_encode($contatti);
        $setting[5]->save();      
        
        if (config('configurazione.pack') == 3 || config('configurazione.pack') == 4) {
            $setting[7]->property = json_encode($setting[7]->property);
            $domicilio = $request->domicilio_status;
            $setting[6]->status = $domicilio;
            $setting[6]->save();
        }
    

        return redirect()->back();   
    }
}
