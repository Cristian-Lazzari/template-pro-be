<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    
    public function updateSetting(Request $request)
    {
        $setting = Setting::all();
        foreach ($s as $setting) {
            if (isset($s->property)) {
                $s->property = json_decode($s->property, true);
            }
        }
        if( config('configurazione.pack') == 2 ){        
            $tavoli = $request->input('tavoli');
            if($setting[0]['status'] !== $tavoli ){
                $setting[0]['status'] = $tavoli;
                $setting[0]['status']->update();
            }
        }elseif( config('configurazione.pack') == 3){
            $asporto = $request->input('asporto');
            $domicilio = $request->input('domicilio');
            if($setting[1]['status'] !== $asporto ){
                $setting[1]['status'] = $asporto;
                $setting[1]['status']->update();
            }
            if($setting[3]['status'] !== $domicilio ){
                $setting[3]['status'] = $domicilio;
                $setting[3]['status']->update();
            }
        }elseif( config('configurazione.pack') == 4){
            $tavoli = $request->input('tavoli');
            $asporto = $request->input('asporto');
            $domicilio = $request->input('domicilio');
            if($setting[0]['status'] !== $tavoli ){
                $setting[0]['status'] = $tavoli;
                $setting[0]['status']->update();
            }
            if($setting[1]['status'] !== $asporto ){
                $setting[1]['status'] = $asporto;
                $setting[1]['status']->update();
            }
            if($setting[3]['status'] !== $domicilio ){
                $setting[3]['status'] = $domicilio;
                $setting[3]['status']->update();
            }
        }


    }

 

   

    
}
