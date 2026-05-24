<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class MarketingBenvenutoSeeder extends Seeder
{
    private const SEED_TAG = 'benvenuto-seed';

    public function run(): void
    {
        $this->seedCustomers();
    }

    // -------------------------------------------------------------------------
    // Clienti demo: 4 email reali + 6 casuali con ordini e prenotazioni
    // -------------------------------------------------------------------------

    private function seedCustomers(): void
    {
        $now = Carbon::now()->startOfMinute();

        $profiles = [
            // --- email reali ---
            [
                'name'    => 'Cristian',
                'surname' => 'Lazzari',
                'email'   => 'cristian.lazzari.cl@gmail.com',
                'phone'   => '3491234567',
            ],
            [
                'name'    => 'Marco',
                'surname' => 'Ferretti',
                'email'   => 'futureplus.commerciale@gmial.com',
                'phone'   => '3357654321',
            ],
            [
                'name'    => 'Sara',
                'surname' => 'Conti',
                'email'   => 'info@future-plus.it',
                'phone'   => '3481122334',
            ],
            [
                'name'    => 'Luca',
                'surname' => 'Negri',
                'email'   => 'test@dashboardristorante.it',
                'phone'   => '3669988776',
            ],
            // --- email casuali ---
            [
                'name'    => 'Giulia',
                'surname' => 'Marini',
                'email'   => 'giulia.marini@' . self::SEED_TAG . '.test',
                'phone'   => '3401111222',
            ],
            [
                'name'    => 'Alessandro',
                'surname' => 'Bruno',
                'email'   => 'ale.bruno@' . self::SEED_TAG . '.test',
                'phone'   => '3452233445',
            ],
            [
                'name'    => 'Chiara',
                'surname' => 'Moretti',
                'email'   => 'chiara.moretti@' . self::SEED_TAG . '.test',
                'phone'   => '3503344556',
            ],
            [
                'name'    => 'Davide',
                'surname' => 'Gallo',
                'email'   => 'davide.gallo@' . self::SEED_TAG . '.test',
                'phone'   => '3334455667',
            ],
            [
                'name'    => 'Elisa',
                'surname' => 'Ferrara',
                'email'   => 'elisa.ferrara@' . self::SEED_TAG . '.test',
                'phone'   => '3665566778',
            ],
            [
                'name'    => 'Roberto',
                'surname' => 'Mancini',
                'email'   => 'roberto.mancini@' . self::SEED_TAG . '.test',
                'phone'   => '3476677889',
            ],
        ];

        foreach ($profiles as $profile) {
            $customer = $this->upsertCustomer($profile, $now);

            // ordine passato (30 gg fa)
            $this->upsertOrder($customer, $now->copy()->subDays(30)->setTime(12, 30), 28.50, 1);
            // ordine recente (ieri)
            $this->upsertOrder($customer, $now->copy()->subDay()->setTime(19, 0), 36.00, 1);
            // ordine futuro (tra 7 gg)
            $this->upsertOrder($customer, $now->copy()->addDays(7)->setTime(12, 30), 22.00, 2);

            // prenotazione passata (15 gg fa)
            $this->upsertReservation($customer, $now->copy()->subDays(15)->setTime(20, 0), 1);
            // prenotazione recente (oggi sera)
            $this->upsertReservation($customer, $now->copy()->setTime(20, 30), 2);
            // prenotazione futura (tra 14 gg)
            $this->upsertReservation($customer, $now->copy()->addDays(14)->setTime(20, 0), 2);
        }
    }

    private function upsertCustomer(array $profile, Carbon $now): Customer
    {
        $registeredAt = $now->copy()->subMonths(3);
        $customer     = Customer::where('email', Customer::normalizeEmail($profile['email']))->first();

        if ($customer) {
            return $customer;
        }

        $customer = new Customer();
        $customer->forceFill([
            'name'                        => $profile['name'],
            'surname'                     => $profile['surname'],
            'email'                       => Customer::normalizeEmail($profile['email']),
            'phone'                       => $profile['phone'],
            'registered_at'              => $registeredAt,
            'email_verified_at'          => $registeredAt,
            'email_marketing_consent_at' => $registeredAt,
            'created_at'                  => $registeredAt,
            'updated_at'                  => $now,
        ]);
        $customer->timestamps = false;
        $customer->save();

        return $customer;
    }

    private function upsertOrder(Customer $customer, Carbon $slot, float $price, int $status): void
    {
        $slotFormatted = $slot->format('d/m/Y H:i');

        if (Order::where('customer_id', $customer->id)->where('date_slot', $slotFormatted)->exists()) {
            return;
        }

        $order = new Order();
        $order->forceFill([
            'customer_id'         => $customer->id,
            'name'                => $customer->name,
            'surname'             => $customer->surname,
            'email'               => $customer->email,
            'phone'               => $customer->phone,
            'date_slot'           => $slotFormatted,
            'status'              => $status,
            'tot_price'           => $price,
            'message'             => null,
            'comune'              => null,
            'address'             => null,
            'address_n'           => null,
            'whatsapp_message_id' => null,
            'news_letter'         => true,
            'notificated'         => false,
            'created_at'          => $slot,
            'updated_at'          => $slot,
        ]);
        $order->timestamps = false;
        $order->save();
    }

    private function upsertReservation(Customer $customer, Carbon $slot, int $adults): void
    {
        $slotFormatted = $slot->format('d/m/Y H:i');

        if (Reservation::where('customer_id', $customer->id)->where('date_slot', $slotFormatted)->exists()) {
            return;
        }

        $reservation = new Reservation();
        $reservation->forceFill([
            'customer_id'         => $customer->id,
            'name'                => $customer->name,
            'surname'             => $customer->surname,
            'email'               => $customer->email,
            'phone'               => $customer->phone,
            'date_slot'           => $slotFormatted,
            'status'              => 1,
            'n_person'            => json_encode(['adult' => $adults, 'child' => 0]),
            'sala'                => null,
            'message'             => null,
            'whatsapp_message_id' => null,
            'news_letter'         => true,
            'notificated'         => false,
            'created_at'          => $slot,
            'updated_at'          => $slot,
        ]);
        $reservation->timestamps = false;
        $reservation->save();
    }
}
