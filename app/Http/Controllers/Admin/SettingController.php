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
        $m = 'Gli indirizzi sono stati aggiornati correttamente';

        return redirect()->back()->with('success', $m); 
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

        // dd($setting[4]['property']);
        // $oldPosition = json_decode( $setting[4]['property'], true);
        if(isset($oldPosition['foto_maps'])){
            $posizione = [
                'foto_maps' =>  "",
                'link_maps' =>  $request->link_maps,
                'indirizzo' =>  $request->indirizzo,
            ];     
            if (isset($request->foto_maps)) {
                $imagePath = Storage::put('public/uploads', $request->foto_maps);
                $posizione['foto_maps'] = $imagePath;
            }else{
                $posizione['foto_maps'] = $oldPosition['foto_maps'];
            }
        }else{
            $posizione = [
                'foto_maps' =>  "",
                'link_maps' =>  $request->link_maps,
                'indirizzo' =>  $request->indirizzo,
            ];
            if (isset($request->foto_maps)) {
                $imagePath = Storage::put('public/uploads', $request->foto_maps);
                $posizione['foto_maps'] = $imagePath;
            }
        }
        $setting[4]->property = json_encode($posizione);
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
        $m = 'Le impostazioni sono state ggiornate correttamente';

        return redirect()->back()->with('success', $m);   
    }
}
