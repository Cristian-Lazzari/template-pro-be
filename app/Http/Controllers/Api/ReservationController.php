<?php

namespace App\Http\Controllers\Api;

use App\Models\Date;
use App\Models\Reservation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;

class ReservationController extends Controller
{
    private $validations = [
        'name'      => 'required|string|max:50',
        'phone'     => 'required|string|max:20',
        'email'     => 'required|email|max:100',
        'n_person'  => 'required|string|max:10',
        'message'   => 'nullable|string|max:1000',
    ];

    public function store(Request $request)
    {
        
        $request->validate($this->validations);
        
        $data = $request->all();
        
        $date = Date::where('date_slot', $data['date_slot'])->firstOrFail();
        $vis = json_decode($date->visible, true);
        $av = json_decode($date->availability, true);
        $res = json_decode($date->reserving, true);
        
        $n_person = intval($data['n_person']);
        if(($res['table'] + $n_person) < $av['table']){
            $res['table'] = $res['table'] + $n_person;
            $date->reserving = json_encode($res);
        }elseif(($res['table'] + $n_person) == $av['table']){
            $res['table'] = $res['table'] + $n_person;
            $date->reserving = json_encode($res);
            $vis['table'] = 0;
            $date->visible = json_encode($vis);
        }else{
            return response()->json([
                'success' => false,
                'message' => 'Sembra che pochi attimi fa la disponibilita sia cambiata,  ci dispiace per l\'inconveniente... provate di nuovo',
                'data' => $date
            ]);

        }


        $newRes = new Reservation();
        $newRes->name = $data['name'];
        $newRes->surname = $data['surname'];
        $newRes->phone = $data['phone'];
        $newRes->email = $data['email'];
        $newRes->date_slot = $data['date_slot'];
        $newRes->n_person = $n_person;
        $newRes->message = $data['message'];
        $newRes->status = 2;
        $newRes->news_letter = $data['news_letter'];
        $date->update();
        $newRes->save();

        // $mail = new confermaPrenotazione($newRes);
        // Mail::to($data['email'])->send($mail);

        // $mailAdmin = new confermaPrenotazioneAdmin($newRes);
        // Mail::to('info@dashboadristorante.it')->send($mailAdmin);


        
        return response()->json([
            'success' => true,
            'prenotazione' => $newRes,
            'data' => $date
        ]);

    }
}