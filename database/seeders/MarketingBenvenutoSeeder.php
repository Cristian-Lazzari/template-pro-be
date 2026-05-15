<?php

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\Customer;
use App\Models\Model as EmailModel;
use App\Models\Order;
use App\Models\Promotion;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class MarketingBenvenutoSeeder extends Seeder
{
    private const MODEL_NAME    = 'MOD-OFFERTA-BENVENUTO';
    private const CAMPAIGN_NAME = 'CAMP-OFFERTA-BENVENUTO';
    private const SEED_TAG      = 'benvenuto-seed';

    public function run(): void
    {
        [$promoAsporto, $promoTavolo] = $this->seedPromotions();
        $model    = $this->seedModel();
        $campaign = $this->seedCampaign($model->id);

        $campaign->promotions()->syncWithoutDetaching([
            $promoAsporto->id => ['total_activation' => 0, 'total_sent' => 0],
            $promoTavolo->id  => ['total_activation' => 0, 'total_sent' => 0],
        ]);

        $this->seedCustomers();
    }

    // -------------------------------------------------------------------------
    // Promotions
    // -------------------------------------------------------------------------

    private function seedPromotions(): array
    {
        $definitions = [
            [
                'slug'            => 'promo-asporto-5e',
                'name'            => 'PROMO ASPORTO -5€',
                'case_use'        => 'take_away',
                'type_discount'   => 'fixed',
                'discount'        => 5.00,
                'minimum_pretest' => 15.00,
                'cta'             => '/asporto',
                'permanent'       => false,
                'metadata'        => [
                    'reusable'     => false,
                    'description'  => '5€ di sconto generico sul carrello asporto. Minima spesa 15€.',
                    'minimum_type' => 'cart_total',
                    'seed_key'     => 'PROMO_ASPORTO_5E',
                ],
            ],
            [
                'slug'            => 'promo-tavolo-benvenuto',
                'name'            => 'PROMO TAVOLO BENVENUTO',
                'case_use'        => 'table',
                'type_discount'   => 'percentage',
                'discount'        => 10.00,
                'minimum_pretest' => 2.00,
                'cta'             => '/prenota',
                'permanent'       => false,
                'metadata'        => [
                    'reusable'     => false,
                    'description'  => '10% di sconto prenotazione tavolo. Minimo 2 persone.',
                    'minimum_type' => 'people',
                    'seed_key'     => 'PROMO_TAVOLO_BENVENUTO',
                ],
            ],
        ];

        $promotions = [];

        foreach ($definitions as $def) {
            $existing = Promotion::where('slug', $def['slug'])->first();

            $attrs = [
                'name'            => $def['name'],
                'status'          => $existing?->status ?: 'draft',
                'case_use'        => $def['case_use'],
                'type_discount'   => $def['type_discount'],
                'discount'        => $def['discount'],
                'minimum_pretest' => $def['minimum_pretest'],
                'cta'             => $def['cta'],
                'permanent'       => $def['permanent'],
                'schedule_at'     => null,
                'expiring_at'     => null,
                'metadata'        => array_replace(
                    is_array($existing?->metadata) ? $existing->metadata : [],
                    $def['metadata']
                ),
            ];

            if (! $existing || $existing->total_activation === null) {
                $attrs['total_activation'] = 0;
            }
            if (! $existing || $existing->total_sent === null) {
                $attrs['total_sent'] = 0;
            }
            if (! $existing || $existing->total_used === null) {
                $attrs['total_used'] = 0;
            }

            $promotions[] = Promotion::updateOrCreate(['slug' => $def['slug']], $attrs);
        }

        return $promotions;
    }

    // -------------------------------------------------------------------------
    // Model & Campaign
    // -------------------------------------------------------------------------

    private function seedModel(): EmailModel
    {
        return EmailModel::firstOrCreate(
            ['name' => self::MODEL_NAME],
            [
                'object'  => "Un'offerta esclusiva solo per te 🎁",
                'heading' => 'La tua offerta personale',
                'body'    => implode("\n\n", [
                    'Ciao {{nome}},',
                    'abbiamo riservato per te una doppia offerta speciale:',
                    '🛍️  Asporto: -5€ sul carrello con una spesa minima di 15€.',
                    '🍽️  Prenotazione tavolo: -10% con almeno 2 persone.',
                    'Usa la tua offerta entro la scadenza {{scadenza}}.',
                    'Clicca qui per approfittarne subito: {{link_offerta}}',
                ]),
                'ending'       => "Non vediamo l'ora di rivederti!\n{{nome_ristorante}}",
                'sender'       => '{{nome_ristorante}}',
                'type'         => 'marketing',
                'channel'      => Campaign::CHANNEL_EMAIL,
                'status'       => 'draft',
                'variables'    => ['nome', 'nome_ristorante', 'link_offerta', 'scadenza'],
                'preview_data' => [
                    'nome'            => 'Mario',
                    'nome_ristorante' => 'Il Ristorante',
                    'link_offerta'    => '#',
                    'scadenza'        => '31/12/2026',
                ],
            ]
        );
    }

    private function seedCampaign(int $modelId): Campaign
    {
        return Campaign::firstOrCreate(
            ['name' => self::CAMPAIGN_NAME],
            [
                'status'           => 'draft',
                'campaign_type'    => Campaign::CAMPAIGN_TYPE_EXPLICIT_EMAIL_MARKETING,
                'channel'          => Campaign::CHANNEL_EMAIL,
                'consent_basis'    => Campaign::CONSENT_BASIS_EXPLICIT_EMAIL_MARKETING,
                'segment'          => null,
                'model_id'         => $modelId,
                'total_activation' => 0,
                'total_sent'       => 0,
                'metadata'         => [
                    'seed_key'    => self::CAMPAIGN_NAME,
                    'description' => 'Campagna benvenuto: -5€ asporto + -10% prenotazione tavolo.',
                ],
            ]
        );
    }

    // -------------------------------------------------------------------------
    // Customers + ordini + prenotazioni
    // -------------------------------------------------------------------------

    private function seedCustomers(): void
    {
        $now = Carbon::now()->startOfMinute();

        // 4 email reali + 6 casuali
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

        $customer = Customer::where('email', Customer::normalizeEmail($profile['email']))->first();

        if ($customer) {
            return $customer;
        }

        $customer = new Customer();
        $customer->forceFill([
            'name'                 => $profile['name'],
            'surname'              => $profile['surname'],
            'email'                => Customer::normalizeEmail($profile['email']),
            'phone'                => $profile['phone'],
            'registered_at'        => $registeredAt,
            'email_verified_at'    => $registeredAt,
            'marketing_consent_at' => $registeredAt,
            'created_at'           => $registeredAt,
            'updated_at'           => $now,
        ]);
        $customer->timestamps = false;
        $customer->save();

        return $customer;
    }

    private function upsertOrder(Customer $customer, Carbon $slot, float $price, int $status): void
    {
        $slotFormatted = $slot->format('d/m/Y H:i');

        $exists = Order::where('customer_id', $customer->id)
            ->where('date_slot', $slotFormatted)
            ->exists();

        if ($exists) {
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

        $exists = Reservation::where('customer_id', $customer->id)
            ->where('date_slot', $slotFormatted)
            ->exists();

        if ($exists) {
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
