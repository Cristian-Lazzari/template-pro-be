<?php

namespace App\Http\Controllers\Admin;

use App\Models\Date;
use App\Models\Setting;
use App\Models\Reservation;
use Illuminate\Http\Request;
use App\Mail\confermaOrdineAdmin;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;

class ReservationController extends Controller
{
    
    public function status(Request $request){
        $c_a = $request->input('c_a');
        $id = $request->input('id');
        $res = Reservation::where('id', $id)->firstOrFail();
        if($c_a){
            $res->status = 1;
            $m = 'La prenotazione e\' stata confermata correttamente';
        }else{
            $res->status = 0;
            $m = 'La prenotazione e\' stata annullata correttamente';
        }
        $res->update();
        

        $set = Setting::where('name', 'Contatti')->firstOrFail();
        $p_set = json_decode($set->property, true);
        $bodymail = [
            'type' => 'res',
            'to' => 'user',
            
            'name' => $res->name,
            'surname' => $res->surname,
            'email' => $res->email,
            'date_slot' => $res->date_slot,
            'message' => $res->message,
            'phone' => $res->phone,
            'admin_phone' => $p_set['telefono'],
               
            'n_person' => $res->n_person,
            'status' => $res->status,
        ];

       
        $mail = new confermaOrdineAdmin($bodymail);
        Mail::to($res['email'])->send($mail);
        
        return redirect()->back()->with('success', $m);   
    }
    

    public function filter(Request $request){
        
        // FUNZIONE DI FILTRAGGIO INDEX
        $status = $request->input('status');
        $name = $request->input('name');
        $order = $request->input('order');
        $date = $request->input('date');

        $filters = [
            'name'          => $name ,
            'status'        => $status ,
            'date'          => $date ,
            'order'         => $order,     
        ];
        
        $query = Reservation::query();
       
        if ($name) {
            $query->where('name', 'like', '%' . $name . '%');
        } 
        if ($status == 0) {
            $query->where('status', '=', 0);
        } else if ($status == 2) {
            $query->where('status', '=', 2);
        } else if ($status == 1) {
            $query->where('status', '=', 1);
        }
        if($date){
            $query->where('date_slot', 'like', '%' . $date . '%');
        }
        if($order){
            $reservations = $query->orderBy('updated_at', 'desc')->get();    
        }else{
            $reservations = $query->orderBy('date_slot', 'desc')->get();
        }        
    
        $data = [];
        array_push($data, $filters);
        array_push($data, $reservations);

        return redirect()->back()->with('filter', $data);
    }




    public function index()
    {
        $reservations = Reservation::where('status', '=', 2)->orderBy('date_slot', 'asc')->get();
        $dates = Date::all();
        return view('admin.Reservations.index', compact('reservations', 'dates'));
    }

    public function show($id)
    {
        $reservation = Reservation::where('id', $id)->firstOrFail();
       

        return view('admin.reservations.show', compact('reservation'));
    }

    public function destroy($id)
    {
        //
    }
}
