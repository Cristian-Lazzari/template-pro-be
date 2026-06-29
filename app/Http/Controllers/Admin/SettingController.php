<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Date;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Support\Currency;
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
                'price' => Currency::parseInput($request->price),
            ];
            $isnew = true;
            foreach ($setting['property']  as $k) {
                if($k['cap'] == $request->cap){
                    $isnew = false;
                    $m = __('admin.controllers.settings.duplicate_area_full');
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
        $m = __('admin.controllers.settings.addresses_updated');

        return redirect()->back()->with('success', $m); 
    }
    
    public function advanced(Request $request){

        $adv = Setting::where('name', 'advanced')->first();
        if($adv){
  
            $property = json_decode($adv->property, true) ?: [];
            $services = (int) $request->input('services', $property['services'] ?? 4);
            $too = (int) $request->input('too', $property['too'] ?? 0);
            $dt = (int) $request->input('dt', $property['dt'] ?? 0);

            if((int) ($property['services'] ?? 4) !== $services ||
                (int) ($property['too'] ?? 0) !== $too ||
                (int) ($property['dt'] ?? 0) !== $dt ){
                // Pulisco le tabelle
                DB::table('dates')->truncate(); 
            }

            $property['too'] = $services === 2 ? 0 : $too;
            $property['dt'] = $services === 3 ? 0 : $dt;
            $property['services'] = $services;
            $property['menu_fix_set'] = (int) $request->input('menu_fix_set', $property['menu_fix_set'] ?? 1);
            
            $property['too_1'] = $request->input('too_1', $property['too_1'] ?? '');
            $property['too_2'] = $request->input('too_2', $property['too_2'] ?? '');
            $property['sala_1'] = $request->input('sala_1', $property['sala_1'] ?? '');
            $property['sala_2'] = $request->input('sala_2', $property['sala_2'] ?? '');
            
            $property['p_iva'] = $request->p_iva;
            $property['r_sociale'] = $request->r_sociale;
            $property['c_rea'] = $request->c_rea;
            $property['c_sociale'] = $request->c_sociale;
            $property['c_ateco'] = $request->c_ateco;
            $property['u_imprese'] = $request->u_imprese;
            $property['method'] = $request->method ? $request->method : [];
            $property['set_time'] = [];
            if($property['dt'] && in_array($services, [2,4], true)){
                $property['set_time'][] = $property['sala_1'];
                $property['set_time'][] = $property['sala_2'];
            }elseif(in_array($services, [2,4], true)){
                $property['set_time'][] = 'tavoli';
            }
            if($property['too'] && in_array($services, [3,4], true)){
                $property['set_time'][] = $property['too_1'];
                $property['set_time'][] = $property['too_2'];
                $property['set_time'][] = 'domicilio';
            }elseif(in_array($services, [4,3], true)){
                $property['set_time'][] = 'asporto';
                $property['set_time'][] = 'domicilio';
            }
            $adv->property = json_encode($property);

            $adv->update();
            $m = __('admin.controllers.settings.settings_updated');
        }else{
            $m = __('admin.controllers.settings.not_found');
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
        $old_p['last_response_wa_1'] = $data = Carbon::today()->subDays(2)->toDateTimeString(); 
        $old_p['last_response_wa_2'] = $data = Carbon::today()->subDays(2)->toDateTimeString(); 
        $setting->property = json_encode($old_p);
        $setting->update();

        $m = __('admin.controllers.settings.numbers_updated');
    
        return redirect()->back()->with('success', $m); 
    }
    public function updateAll(Request $request)
    {
        $setting = Setting::all()->keyBy('name');
        $inputOr = fn (string $key, $default = null) => $request->exists($key) ? $request->input($key) : $default;

        $setting['Prenotazione Tavoli']->status = (int) $inputOr('tavoli_status', $setting['Prenotazione Tavoli']->status);
        $setting['Prenotazione Tavoli']->save();

        $promoProp = json_decode($setting['Promozione Tavoli']->property, true) ?: [];
        $setting['Promozione Tavoli']->status = (int) $inputOr('table_promo', $setting['Promozione Tavoli']->status);
        $allowedCtas = ['prenota', 'ordina', 'offerte', 'registrati'];
        $ctaInput = $inputOr('promo_table_cta', $promoProp['cta'] ?? 'prenota');
        $prop_promo = [
            'title' => $inputOr('promo_table_title', $promoProp['title'] ?? ''),
            'body' => $inputOr('promo_table_body', $promoProp['body'] ?? ''),
            'cta' => in_array($ctaInput, $allowedCtas, true) ? $ctaInput : 'prenota',
        ];
        $setting['Promozione Tavoli']->property = json_encode($prop_promo);
        $setting['Promozione Tavoli']->save();

        $adv_s = Setting::where('name', 'advanced')->first();
        $property_adv = $adv_s ? (json_decode($adv_s->property, 1) ?: []) : [];

        $asportoProp = json_decode($setting['Prenotazione Asporti']->property, true) ?: [];
        $setting['Prenotazione Asporti']->status = (int) $inputOr('asporto_status', $setting['Prenotazione Asporti']->status);
        $prop_apsorto = [
            'pay' => (int) $inputOr('asporto_pay', $asportoProp['pay'] ?? 0),
            'min_price' => $request->exists('min_price_a')
                ? Currency::parseInput($request->min_price_a)
                : ($asportoProp['min_price'] ?? 0),
        ];
        if ((int) ($property_adv['services'] ?? 4) > 2) {
            $domicilioProp = json_decode($setting['Possibilità di consegna a domicilio']->property, true) ?: [];
            $setting['Possibilità di consegna a domicilio']->status = (int) $inputOr('domicilio_status', $setting['Possibilità di consegna a domicilio']->status);
            $prop_domicilio = [
                'pay' => (int) $inputOr('domicilio_pay', $domicilioProp['pay'] ?? 0),
                'min_price' => $request->exists('min_price_d')
                    ? Currency::parseInput($request->min_price_d)
                    : ($domicilioProp['min_price'] ?? 0),
                'delivery_cost' => $request->exists('delivery_cost')
                    ? Currency::parseInput($request->delivery_cost)
                    : ($domicilioProp['delivery_cost'] ?? 0),
            ];
            $setting['Possibilità di consegna a domicilio']->property = json_encode($prop_domicilio);
            $setting['Possibilità di consegna a domicilio']->save();
        }
        $setting['Prenotazione Asporti']->property = json_encode($prop_apsorto);
        $setting['Prenotazione Asporti']->save();

        $ferieProp = json_decode($setting['Periodo di Ferie']->property, true) ?: [];
        $setting['Periodo di Ferie']->status = (int) $inputOr('ferie_status', $setting['Periodo di Ferie']->status);
        $propertyArray = [
            'from' => $inputOr('from', $ferieProp['from'] ?? null),
            'to' => $inputOr('to', $ferieProp['to'] ?? null),
        ];
        $setting['Periodo di Ferie']->property = json_encode($propertyArray);
        $setting['Periodo di Ferie']->save();

        $old_prop = json_decode($setting['Lingua']->property, 1) ?: [];
        $languages = $request->exists('languages')
            ? (array) $request->input('languages')
            : ($old_prop['languages'] ?? ['it']);

        $languages = is_array($languages)
            ? array_values(array_filter($languages, fn ($language) => is_string($language) && trim($language) !== ''))
            : [];

        if (empty($languages)) {
            $languages = ['it'];
        }

        $defaultLang = $inputOr('defaultLang', $old_prop['default'] ?? null);

        if (!is_string($defaultLang) || trim($defaultLang) === '') {
            $defaultLang = $languages[0] ?? config('configurazione.default_lang') ?? config('app.locale') ?? 'it';
        }

        $defaultLang = trim((string) $defaultLang) ?: 'it';

        $propertyArray = [
            'default' => $defaultLang,
            'languages' => $languages,
        ];
        $setting['Lingua']->property = json_encode($propertyArray);
        $setting['Lingua']->save();

        if ($request->exists('currency_code')) {
            $currency = Currency::normalize($request->input('currency_code'));
            $currencySetting = $setting['Valuta'] ?? Setting::query()->firstOrCreate(
                ['name' => 'Valuta'],
                [
                    'status' => 1,
                    'property' => json_encode(Currency::defaultDefinition()),
                ]
            );
            $currencySetting->status = 1;
            $currencySetting->property = json_encode($currency);
            $currencySetting->save();
        }

        $oldOrari = json_decode($setting['Orari di attività']->property, true) ?: [];

        $giorni_attivita = [
            'lunedì'    =>  $inputOr('lunedì', $oldOrari['lunedì'] ?? ''),
            'martedì'   =>  $inputOr('martedì', $oldOrari['martedì'] ?? ''),
            'mercoledì' =>  $inputOr('mercoledì', $oldOrari['mercoledì'] ?? ''),
            'giovedì'   =>  $inputOr('giovedì', $oldOrari['giovedì'] ?? ''),
            'venerdì'   =>  $inputOr('venerdì', $oldOrari['venerdì'] ?? ''),
            'sabato'    =>  $inputOr('sabato', $oldOrari['sabato'] ?? ''),
            'domenica'  =>  $inputOr('domenica', $oldOrari['domenica'] ?? ''),
        ];
        $setting['Orari di attività']->property = json_encode($giorni_attivita);
        $setting['Orari di attività']->save();

        $oldPosition = json_decode($setting['Posizione']['property'], 1) ?: [];
        $posizione = [
            'foto_maps' =>  $oldPosition['foto_maps'] ?? '',
            'link_maps' =>  $inputOr('link_maps', $oldPosition['link_maps'] ?? ''),
            'indirizzo' =>  $inputOr('indirizzo', $oldPosition['indirizzo'] ?? ''),
        ];

        if ($request->hasFile('foto_maps')) {
            $imagePath = $request->file('foto_maps')->store('public/uploads');
            $posizione['foto_maps'] = $imagePath;
        }

        $setting['Posizione']->property = json_encode($posizione);
        $setting['Posizione']->save();

        $oldContatti = json_decode($setting['Contatti']->property, true) ?: [];
        $contatti = [
            'telefono'  => $inputOr('telefono', $oldContatti['telefono'] ?? ''),
            'email'     => $inputOr('email', $oldContatti['email'] ?? ''),
            'instagram' => $inputOr('instagram', $oldContatti['instagram'] ?? ''),
            'facebook'  => $inputOr('facebook', $oldContatti['facebook'] ?? ''),
            'youtube'   => $inputOr('youtube', $oldContatti['youtube'] ?? ''),
            'tiktok'    => $inputOr('tiktok', $oldContatti['tiktok'] ?? ''),
            'whatsapp'  => $inputOr('whatsapp', $oldContatti['whatsapp'] ?? ''),
        ];
        $setting['Contatti']->property = json_encode($contatti);
        $setting['Contatti']->save();      
        

       
        
        $m = __('admin.controllers.settings.settings_updated');

        return redirect()->back()->with('success', $m);   
    }

    public function quickUpdate(Request $request)
    {
        $field = $request->input('field');
        $value = $request->input('value');

        $setting = Setting::all()->keyBy('name');

        switch ($field) {
            case 'tavoli':
                $setting['Prenotazione Tavoli']->status = (int) $value;
                $setting['Prenotazione Tavoli']->save();
                break;

            case 'asporto':
                $setting['Prenotazione Asporti']->status = (int) $value;
                $setting['Prenotazione Asporti']->save();
                break;

            case 'domicilio':
                $setting['Possibilità di consegna a domicilio']->status = (int) $value;
                $setting['Possibilità di consegna a domicilio']->save();
                break;

            case 'ferie':
                $setting['Periodo di Ferie']->status = (int) $value;
                $setting['Periodo di Ferie']->save();
                break;

            case 'promo':
                $setting['Promozione Tavoli']->status = (int) $value;
                $setting['Promozione Tavoli']->save();
                break;

            case 'lang':
                $prop = json_decode($setting['Lingua']->property, true);
                $prop['default'] = $value;
                $setting['Lingua']->property = json_encode($prop);
                $setting['Lingua']->save();
                break;

            case 'currency':
                $currency = Currency::normalize($value);
                $currencySetting = $setting['Valuta'] ?? Setting::firstOrCreate(
                    ['name' => 'Valuta'],
                    ['status' => 1, 'property' => json_encode(Currency::defaultDefinition())]
                );
                $currencySetting->property = json_encode($currency);
                $currencySetting->save();
                break;

            case 'asporto_pay':
                $prop = json_decode($setting['Prenotazione Asporti']->property, true);
                $prop['pay'] = (int) $value;
                $setting['Prenotazione Asporti']->property = json_encode($prop);
                $setting['Prenotazione Asporti']->save();
                break;

            case 'min_price_a':
                $prop = json_decode($setting['Prenotazione Asporti']->property, true);
                $prop['min_price'] = Currency::parseInput($value);
                $setting['Prenotazione Asporti']->property = json_encode($prop);
                $setting['Prenotazione Asporti']->save();
                break;

            case 'domicilio_pay':
                $prop = json_decode($setting['Possibilità di consegna a domicilio']->property, true);
                $prop['pay'] = (int) $value;
                $setting['Possibilità di consegna a domicilio']->property = json_encode($prop);
                $setting['Possibilità di consegna a domicilio']->save();
                break;

            case 'min_price_d':
                $prop = json_decode($setting['Possibilità di consegna a domicilio']->property, true);
                $prop['min_price'] = Currency::parseInput($value);
                $setting['Possibilità di consegna a domicilio']->property = json_encode($prop);
                $setting['Possibilità di consegna a domicilio']->save();
                break;

            case 'delivery_cost':
                $prop = json_decode($setting['Possibilità di consegna a domicilio']->property, true);
                $prop['delivery_cost'] = Currency::parseInput($value);
                $setting['Possibilità di consegna a domicilio']->property = json_encode($prop);
                $setting['Possibilità di consegna a domicilio']->save();
                break;

            case 'from':
            case 'to':
                $prop = json_decode($setting['Periodo di Ferie']->property, true);
                $prop[$field] = $value;
                $setting['Periodo di Ferie']->property = json_encode($prop);
                $setting['Periodo di Ferie']->save();
                break;

            case 'promo_table_title':
                $prop = json_decode($setting['Promozione Tavoli']->property, true);
                $prop['title'] = $value;
                $setting['Promozione Tavoli']->property = json_encode($prop);
                $setting['Promozione Tavoli']->save();
                break;

            case 'promo_table_body':
                $prop = json_decode($setting['Promozione Tavoli']->property, true);
                $prop['body'] = $value;
                $setting['Promozione Tavoli']->property = json_encode($prop);
                $setting['Promozione Tavoli']->save();
                break;

            case 'promo_table_cta':
                $allowed = ['prenota', 'ordina', 'offerte', 'registrati'];
                $prop = json_decode($setting['Promozione Tavoli']->property, true);
                $prop['cta'] = in_array($value, $allowed, true) ? $value : 'prenota';
                $setting['Promozione Tavoli']->property = json_encode($prop);
                $setting['Promozione Tavoli']->save();
                break;

            case 'telefono':
                $prop = json_decode($setting['Contatti']->property, true);
                $prop['telefono'] = $value;
                $setting['Contatti']->property = json_encode($prop);
                $setting['Contatti']->save();
                break;

            default:
                return response()->json(['error' => __('admin.controllers.settings.unknown_field', ['field' => $field])], 422);
        }

        return response()->json(['success' => true]);
    }

    public function cancelDates(Request $request){
        $data = $request->all();
        $s = Setting::where('name', 'advanced')->first();
        // /dd($data['day_off']);
        $adv = json_decode($s->property, 1);
        $adv['day_off'] = $data['day_off'] ?? [];

        $s->property = json_encode($adv);
        $s->update();
        
        return redirect()->route('admin.dashboard')->with('message', __('admin.controllers.settings.dates_updated'));

    }
}
