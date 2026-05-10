<?php

namespace App\Http\Controllers\Admin;

use DateTime;
use App\Models\Date;
use App\Models\Order;
use App\Models\Setting;
use App\Models\Reservation;
use Illuminate\Http\Request;
use App\Mail\confermaOrdineAdmin;
use App\Http\Controllers\Controller;
use App\Models\CustomerPromotion;
use App\Services\Marketing\PromotionNotificationFormatter;
use App\Support\Currency;
use Illuminate\Support\Facades\Mail;

class ReservationController extends Controller
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
        
        $query = Reservation::query();
       
        if ($name) {
            $query->where('name', 'like', '%' . $name . '%')
            ->orWhere('surname', 'like', '%' . $name . '%');
        } 
        if ($status == 4) {
            $query->where('status', 0)
            ->orWhere('status', 6);
        } else if ($status == 1) {
            $query->where('status', 1)
            ->orWhere('status', 5);
        } else if ($status == 2) {
            $query->where('status', 2)
            ->orWhere('status', 3);
        } else if ($status == 5) {
            $query->where('status', 3)
            ->orWhere('status', 5);
        }else{ 
            $query->where('status', '!=', 4);
        }
        if($date){
            $formattedDate = DateTime::createFromFormat('Y-m-d', $date)->format('d/m/Y');
            $query->where('date_slot', 'like', '%' . $formattedDate . '%');
        }
        if($order){
            $reservations = $query->orderBy('date_slot', 'asc')->get();    
        }else{
            $reservations = $query->orderBy('created_at', 'asc')->get();
        }        
    
        $data = [];
        array_push($data, $filters);
        array_push($data, $reservations);
      
        
        return redirect()->back()->with('filter', $data);
    }
    
    public function status(Request $request){
        $wa = $request->input('wa');
        $c_a = $request->input('c_a');
        $id = $request->input('id');
        $res = Reservation::where('id', $id)->firstOrFail();
        $message = '';
        $adv_s = Setting::where('name', 'advanced')->first();
        $property_adv = json_decode($adv_s->property, 1);
        if($c_a){
            $res->status = 1;
            $m = 'La prenotazione e\' stata confermata correttamente';
            $message = 'Siamo felici di informarti che la tua prenotazione e\' stata confermata, ti ricordo la data e l\'orario che hai scelto: ' . $res->date_slot ;
        }else{
            if($res->status == 0){
                $m = 'La prenotazione e\' stata gia annullata correttamente';
                return redirect()->back()->with('success', $m);
            }


            $res->status = 0;
            $m = 'La prenotazione e\' stata annullata correttamente';
            $message = 'Ci spiace informarti che la tua prenotazione e\' stata annullata per la data e l\'orario che hai scelto... ' . $res->date_slot ;
        }
        $res->update();
        
        $adv_s = Setting::where('name', 'advanced')->first();
        $property_adv = json_decode($adv_s->property, 1);
        $set = Setting::where('name', 'Contatti')->firstOrFail();
        $p_set = json_decode($set->property, true);
        $bodymail = [
            'type' => 'res',
            'to' => 'user',

            'res_id' => $res->id,
            'name' => $res->name,
            'surname' => $res->surname,
            'date_slot' => $res->date_slot,
            'message' => $res->message,
            'sala' => $res->sala,
            'phone' => $res->phone,
            'admin_phone' => $p_set['telefono'],

            'title' =>  $c_a ? 'Ti confermiamo che la tua prenotazione è stata accettata' : 'Ci dispiace informarti che la tua prenotazione è stata annullata',
            'subtitle' => '',
            'whatsapp_message_id' => $res->whatsapp_message_id,
               
            'n_person' => $res->n_person,
            'status' => $res->status,
            'property_adv' => $property_adv,
        ];

       
        $mail = new confermaOrdineAdmin($bodymail);

        Mail::to($res['email'])->send($mail);
        
        if($wa){
            return redirect("https://wa.me/39" . $res->phone . "?text=" . $message);
        }
        return redirect()->back()->with('success', $m);   
    }
    


    public function index()
    {
        $order_delete = Order::where('status', 4)->get();
        foreach($order_delete as $o){
            $o->delete();
        }
        $promotionFormatter = app(PromotionNotificationFormatter::class);
        $res = Reservation::with('customerPromotions.promotion')->get();
        $orders = Order::with('customerPromotions.promotion')->get();
        $res->each(function (Reservation $reservation) use ($promotionFormatter) {
            $reservation->setAttribute(
                'promotion_summary',
                $this->listPromotionSummary($promotionFormatter->forReservation($reservation), false)
            );
        });
        $orders->each(function (Order $order) use ($promotionFormatter) {
            $order->setAttribute(
                'promotion_summary',
                $this->listPromotionSummary($promotionFormatter->forOrder($order), true)
            );
        });
        // dump($res);
        // dump($orders);

        $reservations = $res
            ->concat($orders)   // unisce senza usare le chiavi ID
            ->sortBy('date_slot')  // ordina
            ->values();

        $adv_s = Setting::where('name', 'advanced')->first();
        $property_adv = json_decode($adv_s->property, 1);

      //  dd($reservations);
        return view('admin.Reservations.index', compact('reservations', 'property_adv'));
    }
    
    public function show($id)
    {
        $adv_s = Setting::where('name', 'advanced')->first();
        $property_adv = json_decode($adv_s->property, 1);
        $reservation = Reservation::where('id', $id)
            ->with('customerPromotions.promotion')
            ->firstOrFail();
        $promotionDetails = $this->reservationPromotionDetails($reservation, app(PromotionNotificationFormatter::class));

        return view('admin.Reservations.show', compact('reservation', 'property_adv', 'promotionDetails'));
    }

    public function destroy($id)
    {
        //
    }

    private function listPromotionSummary(array $promotions, bool $order): array
    {
        if ($promotions === []) {
            return [
                'has_promotion' => false,
            ];
        }

        $promotion = $promotions[0];
        $discountAmount = (float) ($promotion['discount_amount'] ?? 0);

        return [
            'has_promotion' => true,
            'name' => $promotion['promotion_name'] ?? 'Promozione',
            'badge_label' => $order && $discountAmount > 0
                ? 'Sconto ' . Currency::formatCents($discountAmount)
                : 'Promo prenotazione',
        ];
    }

    private function reservationPromotionDetails(Reservation $reservation, PromotionNotificationFormatter $formatter): array
    {
        $formattedPromotions = collect($formatter->forReservation($reservation))->keyBy('customer_promotion_id');

        return $reservation->customerPromotions
            ->filter(fn (CustomerPromotion $customerPromotion) => (int) $customerPromotion->reservation_id === (int) $reservation->getKey())
            ->map(function (CustomerPromotion $customerPromotion) use ($formattedPromotions) {
                $formatted = $formattedPromotions->get($customerPromotion->getKey(), []);
                $metadata = is_array($customerPromotion->metadata) ? $customerPromotion->metadata : [];
                $affectedItems = $formatted['affected_items'] ?? ($metadata['affected_items'] ?? []);

                return [
                    'customer_promotion_id' => $customerPromotion->getKey(),
                    'status' => $this->customerPromotionStatusLabel($customerPromotion->status),
                    'name' => $formatted['promotion_name']
                        ?? $customerPromotion->promotion?->name
                        ?? 'Promozione #' . $customerPromotion->promotion_id,
                    'type_label' => $formatted['type_label']
                        ?? $this->promotionTypeLabel($customerPromotion->promotion?->type_discount),
                    'affected_items' => $this->formatAffectedItems(is_array($affectedItems) ? $affectedItems : []),
                ];
            })
            ->values()
            ->all();
    }

    private function formatAffectedItems(array $items): array
    {
        return collect($items)
            ->map(function (array $item) {
                $type = (string) ($item['type'] ?? 'reservation');
                $label = match ($type) {
                    'reservation' => 'Prenotazione tavolo',
                    'product' => 'Prodotto',
                    'menu' => 'Menu',
                    'category' => 'Categoria',
                    default => ucfirst($type ?: 'Elemento'),
                };

                $details = [];

                if (! empty($item['people'])) {
                    $details[] = $item['people'] . ' ospiti';
                }

                if (! empty($item['minimum_required'])) {
                    $details[] = 'minimo ' . $item['minimum_required'];
                }

                if (! empty($item['benefit_type'])) {
                    $details[] = $this->promotionTypeLabel($item['benefit_type']);
                }

                if (! empty($item['gift_benefit'])) {
                    $details[] = 'omaggio';
                }

                return [
                    'label' => $label,
                    'details' => $details,
                ];
            })
            ->values()
            ->all();
    }

    private function customerPromotionStatusLabel(?string $status): string
    {
        return match ($status) {
            'assigned' => 'Assegnata',
            'sent' => 'Inviata',
            'opened' => 'Aperta',
            'clicked' => 'Cliccata',
            'used' => 'Usata',
            default => $status ?: 'n/d',
        };
    }

    private function promotionTypeLabel(?string $type): string
    {
        return match ($type) {
            'fixed' => 'Sconto fisso',
            'percentage' => 'Sconto percentuale',
            'gift' => 'Omaggio',
            default => $type ?: 'Promozione',
        };
    }
}
