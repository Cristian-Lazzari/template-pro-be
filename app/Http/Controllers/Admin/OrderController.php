<?php

namespace App\Http\Controllers\Admin;

use App\Models\Date;
use App\Models\Order;
use App\Models\Setting;
use App\Models\OrderProduct;
use Illuminate\Http\Request;
use App\Mail\confermaOrdineAdmin;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;

class OrderController extends Controller
{
    

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
        
        $query = Order::query();
       
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
            $orders = $query->orderBy('date_slot', 'asc')->get();
        }else{
            $orders = $query->orderBy('updated_at', 'desc')->get();    
        }        
    

        $data = [];
        array_push($data, $filters);
        array_push($data, $orders);

        return redirect()->back()->with('filter', $data);
    }

    public function status(Request $request){
        $wa = $request->input('wa');
        $c_a = $request->input('c_a');
        $id = $request->input('id');
        $order = Order::where('id', $id)->firstOrFail();
        if($c_a){
            $order->status = 1;
            $m = 'La prenotazione e\' stata confermata correttamente';
            $message = 'Grazie ' . $order->name . ' per aver ordinato da noi, ti confermiamo che il tuo ordine sarà pronto per il ' . $order->date_slot;
        }else{
            $date = Date::where('date_slot', $order->date_slot)->firstOrFail();
            $vis = json_decode($date->visible, 1); 
            $reserving = json_decode($date->reserving, 1);
            if(config('configurazione.typeOfOrdering')){
                $np_cucina_1 = 0;
                $np_cucina_2 = 0;
                foreach ($order->products as $p) {
                    $qt = 0;
                    $op = OrderProduct::where('product_id', $p->id)->where('order_id', $order->id)->firstOrFail();
                    if($op !== null){
                        $qt = $op->quantity;
                        if($p->type_plate == 1 && $qt !== 0){
                            $np_cucina_1 += ($p->slot_plate * $qt);
                            if($vis['cucina_1'] == 0){
                                $vis['cucina_1'] = 1;
                            }
                        }
                        if($p->type_plate == 2){
                            $np_cucina_2 += ($p->slot_plate * $qt);
                            if($vis['cucina_2'] == 0){
                                $vis['cucina_2'] = 1;
                            }
                        }
                    }
                }
                $reserving['cucina_1'] = $reserving['cucina_1'] - $np_cucina_1;
                $reserving['cucina_2'] = $reserving['cucina_2'] - $np_cucina_2;
                if($order->address !== null){
                    if($vis['domicilio'] == 0){
                        $vis['domicilio'] = 1;
                    }
                    $reserving['domicilio'] --;
                }
            }else{
                if($order->address !== null){
                    if($vis['domicilio'] == 0){
                        $vis['domicilio'] = 1;
                    }
                    $reserving['domicilio'] --;
                }else{
                    $reserving['asporto'] --;

                }
            }

            $date->reserving = json_encode($reserving);
            $date->visible = json_encode($vis);
            $date->update();

            $order->status = 0;
            $m = 'La prenotazione e\' stata annullata correttamente';
            $message = 'Ci dispiace informarti che purtroppo il tuo ordine è stato annullato';
        }
        $order->update();
        

        $set = Setting::where('name', 'Contatti')->firstOrFail();
        $p_set = json_decode($set->property, true);
        $orderProduct = OrderProduct::all();
        $bodymail = [
            'type' => 'or',
            'to' => 'user',
            'order_id' => $order->id,
            'name' => $order->name,
            'surname' => $order->surname,
            'email' => $order->email,
            'date_slot' => $order->date_slot,
            'message' => $order->message,
            'phone' => $order->phone,
            'admin_phone' => $p_set['telefono'],
            
            'comune' => $order->comune,
            'address' => $order->address,
            'address_n' => $order->address_n,
            'orderProduct' => $orderProduct,
            
            'status' => $order->status,
            'cart' => $order->products,
            'total_price' => $order->tot_price,
        ];

       
        $mail = new confermaOrdineAdmin($bodymail);
        Mail::to($order['email'])->send($mail);

       
        if($wa){
            return redirect("https://wa.me/39" . $order->phone . "?text=" . $message);
        }
        
        return redirect()->back()->with('success', $m);   
    }
    



    public function index()
    {
        $orders = Order::where('status', '=', 2)->orderBy('created_at', 'desc')->get();
        $dates = Date::all();
        return view('admin.Orders.index', compact('orders', 'dates'));
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
        $order = Order::where('id', $id)->firstOrFail();
        $orderProduct = OrderProduct::all();

        return view('admin.Orders.show', compact('order', 'orderProduct'));
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
