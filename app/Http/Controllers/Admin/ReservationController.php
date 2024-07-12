<?php

namespace App\Http\Controllers\Admin;

use App\Models\Date;
use App\Models\Reservation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ReservationController extends Controller
{
    

    public function status(Request $request){

        $type = $request->input('type');
        $c_a = $request->input('c_a');
        $id = $request->input('id');
        $res = Reservation::where('id', $rid)->firstOrFail();
        if($c_a){
            $res->status = 1;
            $m = 'La prenotazione e\' stata annullata correttamente';
        }else{
            $res->status = 0;
            $m = 'La prenotazione e\' stata confermata correttamente';
        }
        $res->update();
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
            $reservations = $query->orderBy('date_slot', 'asc')->get();
        }else{
            $reservations = $query->orderBy('updated_at', 'desc')->get();    
        }        
    

        return view('admin.Reservations.index', compact('reservations', 'filters'));
    }




    public function index()
    {
        $reservations = Reservation::where('status', '=', 2)->orderBy('date_slot', 'asc')->get();
        $dates = Date::all();
        return view('admin.Reservations.index', compact('reservations', 'dates'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
