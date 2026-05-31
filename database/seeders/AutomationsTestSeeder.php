<?php

namespace Database\Seeders;

use App\Models\Automation;
use App\Models\Customer;
use App\Models\Model as EmailModel;
use App\Models\Order;
use App\Models\Promotion;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * AutomationsTestSeeder
 *
 * Crea 15 clienti di test (Gmail plus addressing), 1 promozione e 13 automazioni
 * per coprire tutti i 12 trigger disponibili (customer_anniversary ha 2 varianti).
 *
 * ATTENZIONE: rimuovere questo seeder in produzione o disattivare le automazioni
 * create qui (prefisso [AUTO-TEST]) prima del deploy.
 *
 * Uso:
 *   php artisan db:seed --class=AutomationsTestSeeder
 *   php artisan customers:refresh-stats --limit=100
 *   php artisan marketing:process-automation-emails --dry-run --limit=100
 */
class AutomationsTestSeeder extends Seeder
{
    private const EMAIL_TAG       = 'cristian.lazzari.cl';
    private const EMAIL_DOMAIN    = 'gmail.com';
    private const PROMO_SLUG      = 'auto-test-promo-automazioni';
    private const MODEL_NAME      = 'MOD-AUTO-TEST';
    private const AUTO_PREFIX     = '[AUTO-TEST] ';

    // -------------------------------------------------------------------------
    // Entry point
    // -------------------------------------------------------------------------

    public function run(): void
    {
        $now = Carbon::now()->startOfMinute();

        $this->cleanup();

        $promotion  = $this->seedPromotion($now);
        $emailModel = $this->seedEmailModel();

        $this->seedAutomations($promotion, $emailModel);
        $this->seedCustomers($now);

        $this->command->info('AutomationsTestSeeder completato.');
        $this->command->info('Esegui: php artisan customers:refresh-stats --limit=100');
        $this->command->info('Poi:    php artisan marketing:process-automation-emails --dry-run --limit=100');
    }

    // -------------------------------------------------------------------------
    // Cleanup idempotente
    // -------------------------------------------------------------------------

    private function cleanup(): void
    {
        $emailLike     = self::EMAIL_TAG . '+auto-%@' . self::EMAIL_DOMAIN;
        $customerIds   = Customer::whereRaw('email LIKE ?', [$emailLike])->pluck('id');
        $automationIds = Automation::where('name', 'LIKE', self::AUTO_PREFIX . '%')->pluck('id');
        $promoId       = Promotion::where('slug', self::PROMO_SLUG)->value('id');

        // 1. customer_promotion: dipende da customer_id, automation_id, promotion_id
        if ($customerIds->isNotEmpty()) {
            DB::table('customer_promotion')->whereIn('customer_id', $customerIds)->delete();
        }
        if ($automationIds->isNotEmpty()) {
            DB::table('customer_promotion')->whereIn('automation_id', $automationIds)->delete();
        }

        // 2. Ordini e prenotazioni dei clienti test
        if ($customerIds->isNotEmpty()) {
            Order::whereIn('customer_id', $customerIds)->delete();
            Reservation::whereIn('customer_id', $customerIds)->delete();
            Customer::whereIn('id', $customerIds)->delete();
        }

        // 3. automation_promotion pivot
        if ($automationIds->isNotEmpty()) {
            DB::table('automation_promotion')->whereIn('automation_id', $automationIds)->delete();
        }
        if ($promoId) {
            DB::table('automation_promotion')->where('promotion_id', $promoId)->delete();
        }

        // 4. Automazioni test
        if ($automationIds->isNotEmpty()) {
            Automation::whereIn('id', $automationIds)->delete();
        }

        // 5. Promozione test
        if ($promoId) {
            Promotion::where('id', $promoId)->delete();
        }
    }

    // -------------------------------------------------------------------------
    // Promozione test
    // -------------------------------------------------------------------------

    private function seedPromotion(Carbon $now): Promotion
    {
        return Promotion::create([
            'name'             => 'AUTO TEST - Promo Automazioni',
            'slug'             => self::PROMO_SLUG,
            'description'      => 'Promozione usata esclusivamente dal seeder di test automazioni. Rimuovere in produzione.',
            'status'           => 'active',
            'case_use'         => 'take_away',
            'type_discount'    => 'percentage',
            'discount'         => 10.00,
            'minimum_pretest'  => null,
            'cta'              => 'Ordina ora',
            'permanent'        => true,
            'total_activation' => 0,
            'total_sent'       => 0,
            'total_used'       => 0,
            'metadata'         => [
                'seed_key' => 'AUTO_TEST_PROMO',
                'note'     => 'Test seeder — rimuovere in produzione.',
            ],
        ]);
    }

    // -------------------------------------------------------------------------
    // Modello email test
    // -------------------------------------------------------------------------

    private function seedEmailModel(): EmailModel
    {
        return EmailModel::firstOrCreate(
            ['name' => self::MODEL_NAME],
            [
                'object'        => '[AUTO-TEST] Ciao @customer_first_name, una promo per te',
                'heading'       => 'Promo riservata a te',
                'body'          => "Ciao @customer_first_name,\n\nquesto è un messaggio di test automazioni. Puoi ignorarlo.\n\nUna promo speciale per te.",
                'ending'        => 'Il team tecnico',
                'sender'        => 'Test Automazioni',
                'type'          => 'marketing',
                'channel'       => 'email',
                'status'        => 'active',
                'has_promotion' => true,
                'cta_label'     => 'Scopri la promo',
                'variables'     => ['customer_first_name'],
                'preview_data'  => ['customer_first_name' => 'AutoTest'],
            ]
        );
    }

    // -------------------------------------------------------------------------
    // 13 Automazioni (tutti i 12 trigger + variante anniversary per prenotazione)
    // -------------------------------------------------------------------------

    private function seedAutomations(Promotion $promotion, EmailModel $emailModel): void
    {
        foreach ($this->automationDefinitions() as $def) {
            $automation = Automation::create([
                'name'             => self::AUTO_PREFIX . $def['label'],
                'trigger'          => $def['trigger'],
                'status'           => 'active',
                'model_id'         => $emailModel->id,
                'total_activation' => 0,
                'total_sent'       => 0,
                'metadata'         => $def['metadata'],
            ]);

            $automation->promotions()->syncWithoutDetaching([
                $promotion->id => ['total_activation' => 0, 'total_sent' => 0],
            ]);
        }
    }

    private function automationDefinitions(): array
    {
        return [
            // 1
            [
                'label'    => 'No interazione 60gg',
                'trigger'  => 'no_interaction_since',
                'metadata' => ['days' => 60],
            ],
            // 2
            [
                'label'    => 'Nessun ordine 30gg',
                'trigger'  => 'no_order_since',
                'metadata' => ['days' => 30],
            ],
            // 3
            [
                'label'    => 'Nessuna prenotazione 30gg',
                'trigger'  => 'no_booking_since',
                'metadata' => ['days' => 30],
            ],
            // 4 — richiede tracking_consent_at (ProfilingConsentTriggerContract)
            [
                'label'    => 'Compleanno entro 7gg',
                'trigger'  => 'birthday_before',
                'metadata' => ['days_before' => 7],
            ],
            // 5
            [
                'label'    => 'Dopo primo ordine 30gg',
                'trigger'  => 'first_order_completed',
                'metadata' => ['delay_days' => 30],
            ],
            // 6
            [
                'label'    => 'Dopo prima prenotazione 30gg',
                'trigger'  => 'first_booking_completed',
                'metadata' => ['delay_days' => 30],
            ],
            // 7
            [
                'label'    => 'Ordini senza prenotazioni',
                'trigger'  => 'orders_without_bookings',
                'metadata' => ['min_orders' => 1],
            ],
            // 8
            [
                'label'    => 'Prenotazioni senza ordini',
                'trigger'  => 'bookings_without_orders',
                'metadata' => ['min_bookings' => 1],
            ],
            // 9
            [
                'label'    => 'Raggiunge 50€ di spesa',
                'trigger'  => 'customer_reaches_value',
                'metadata' => [
                    'threshold_type'  => 'total_spent',
                    'threshold_value' => 50,
                ],
            ],
            // 10
            [
                'label'    => 'Cliente valore a rischio 60gg',
                'trigger'  => 'valuable_customer_at_risk',
                'metadata' => [
                    'value_type'      => 'total_spent',
                    'value_threshold' => 50,
                    'inactive_days'   => 60,
                ],
            ],
            // 11 — anniversary su primo ordine
            [
                'label'    => 'Anniversario primo ordine 7gg',
                'trigger'  => 'customer_anniversary',
                'metadata' => [
                    'anniversary_source' => 'first_order',
                    'days_before'        => 7,
                ],
            ],
            // 12 — anniversary su prima prenotazione
            [
                'label'    => 'Anniversario prima prenotazione 7gg',
                'trigger'  => 'customer_anniversary',
                'metadata' => [
                    'anniversary_source' => 'first_booking',
                    'days_before'        => 7,
                ],
            ],
            // 13
            [
                'label'    => 'Carrello medio alto 30€ min2',
                'trigger'  => 'high_average_order_value',
                'metadata' => [
                    'average_order_value' => 30,
                    'min_orders'          => 2,
                ],
            ],
        ];
    }

    // -------------------------------------------------------------------------
    // 15 Clienti di test
    // -------------------------------------------------------------------------

    private function seedCustomers(Carbon $now): void
    {
        foreach ($this->customerProfiles($now) as $profile) {
            $this->createCustomer($profile, $now);
        }
    }

    /**
     * Definisce i 15 profili test con le relative aspettative sul trigger.
     *
     * Stats force-filled per coerenza con gli ordini/prenotazioni creati.
     * Dopo il seeder, eseguire `customers:refresh-stats` per sincronizzare.
     *
     * Convenzioni:
     *   - consents: ['email'] = email_marketing_consent_at
     *               ['tracking'] = tracking_consent_at + profiling_consent_at
     *               ['privacy']  = privacy_accepted_at
     *   - last_activity_at = MAX(last_order_at, last_booking_at)
     */
    private function customerProfiles(Carbon $now): array
    {
        // Oggi: 2026-05-28 (data hardcoded nei commenti per chiarezza)
        // Finestra birthday_before=7: 28/05 → 04/06
        // Finestra first_order/booking delay_days=30: last 30gg
        // Finestra no_*_since days=30: last_*_at < 28/04
        // Finestra no_interaction days=60: last_activity_at < 29/03
        // Finestra at_risk inactive_days=60: last_activity_at < 29/03

        return [

            // ── C1: no_interaction_since ─────────────────────────────────────
            // last_activity_at = 90gg fa → supera soglia 60gg
            // Matcherà anche: no_order_since(30gg)
            [
                'alias'        => 'auto-no-interaction',
                'name'         => 'AutoTest',
                'surname'      => 'NoInteraction',
                'expects'      => 'no_interaction_since',
                'consents'     => ['email', 'privacy'],
                'birthday'     => null,
                'stats'        => [
                    'orders_count'        => 1,
                    'reservations_count'  => 0,
                    'total_spent'         => 20.00,
                    'average_order_value' => 20.00,
                    'first_order_at'      => $now->copy()->subDays(90),
                    'last_order_at'       => $now->copy()->subDays(90),
                    'first_booking_at'    => null,
                    'last_booking_at'     => null,
                    'last_activity_at'    => $now->copy()->subDays(90),
                ],
                'orders' => [
                    ['date' => $now->copy()->subDays(90), 'price' => 20.00, 'status' => 1],
                ],
                'reservations' => [],
            ],

            // ── C2: no_order_since ───────────────────────────────────────────
            // last_order_at = 45gg fa → supera soglia 30gg
            // Prenotazione recente (5gg) → last_activity_at=5gg, NO no_interaction(60gg)
            [
                'alias'    => 'auto-no-order',
                'name'     => 'AutoTest',
                'surname'  => 'NoOrder',
                'expects'  => 'no_order_since',
                'consents' => ['email', 'privacy'],
                'birthday' => null,
                'stats'    => [
                    'orders_count'        => 1,
                    'reservations_count'  => 1,
                    'total_spent'         => 25.00,
                    'average_order_value' => 25.00,
                    'first_order_at'      => $now->copy()->subDays(45),
                    'last_order_at'       => $now->copy()->subDays(45),
                    'first_booking_at'    => $now->copy()->subDays(5),
                    'last_booking_at'     => $now->copy()->subDays(5),
                    'last_activity_at'    => $now->copy()->subDays(5),
                ],
                'orders' => [
                    ['date' => $now->copy()->subDays(45), 'price' => 25.00, 'status' => 1],
                ],
                'reservations' => [
                    ['date' => $now->copy()->subDays(5), 'adults' => 2, 'status' => 1],
                ],
            ],

            // ── C3: no_booking_since ─────────────────────────────────────────
            // last_booking_at = 45gg fa → supera soglia 30gg
            // Ordine recente (5gg) → last_activity_at=5gg, NO no_interaction(60gg)
            [
                'alias'    => 'auto-no-booking',
                'name'     => 'AutoTest',
                'surname'  => 'NoBooking',
                'expects'  => 'no_booking_since',
                'consents' => ['email', 'privacy'],
                'birthday' => null,
                'stats'    => [
                    'orders_count'        => 1,
                    'reservations_count'  => 1,
                    'total_spent'         => 25.00,
                    'average_order_value' => 25.00,
                    'first_order_at'      => $now->copy()->subDays(5),
                    'last_order_at'       => $now->copy()->subDays(5),
                    'first_booking_at'    => $now->copy()->subDays(45),
                    'last_booking_at'     => $now->copy()->subDays(45),
                    'last_activity_at'    => $now->copy()->subDays(5),
                ],
                'orders' => [
                    ['date' => $now->copy()->subDays(5), 'price' => 25.00, 'status' => 1],
                ],
                'reservations' => [
                    ['date' => $now->copy()->subDays(45), 'adults' => 2, 'status' => 1],
                ],
            ],

            // ── C4: birthday_before — CON tracking consent ───────────────────
            // birthday = 31/05 (3 giorni da oggi 28/05) → nella finestra days_before=7
            // tracking_consent_at SET → deve matchare
            [
                'alias'    => 'auto-birthday-consent',
                'name'     => 'AutoTest',
                'surname'  => 'Birthday',
                'expects'  => 'birthday_before (MATCH atteso)',
                'consents' => ['email', 'privacy', 'tracking'],
                'birthday' => '2000-05-31',
                'stats'    => [
                    'orders_count'        => 1,
                    'reservations_count'  => 0,
                    'total_spent'         => 30.00,
                    'average_order_value' => 30.00,
                    'first_order_at'      => $now->copy()->subDays(10),
                    'last_order_at'       => $now->copy()->subDays(10),
                    'first_booking_at'    => null,
                    'last_booking_at'     => null,
                    'last_activity_at'    => $now->copy()->subDays(10),
                ],
                'orders' => [
                    ['date' => $now->copy()->subDays(10), 'price' => 30.00, 'status' => 1],
                ],
                'reservations' => [],
            ],

            // ── C5: birthday_before — SENZA tracking consent ─────────────────
            // Stesso compleanno di C4 ma tracking_consent_at=NULL
            // → NON deve essere nell'audience (ProfilingConsentTriggerContract)
            [
                'alias'    => 'auto-birthday-no-consent',
                'name'     => 'AutoTest',
                'surname'  => 'BirthdayNoConsent',
                'expects'  => 'birthday_before (NO MATCH — no tracking consent)',
                'consents' => ['email', 'privacy'],
                'birthday' => '2000-05-31',
                'stats'    => [
                    'orders_count'        => 1,
                    'reservations_count'  => 0,
                    'total_spent'         => 30.00,
                    'average_order_value' => 30.00,
                    'first_order_at'      => $now->copy()->subDays(10),
                    'last_order_at'       => $now->copy()->subDays(10),
                    'first_booking_at'    => null,
                    'last_booking_at'     => null,
                    'last_activity_at'    => $now->copy()->subDays(10),
                ],
                'orders' => [
                    ['date' => $now->copy()->subDays(10), 'price' => 30.00, 'status' => 1],
                ],
                'reservations' => [],
            ],

            // ── C6: first_order_completed ────────────────────────────────────
            // first_order_at = 5gg fa → dentro finestra delay_days=30
            [
                'alias'    => 'auto-first-order',
                'name'     => 'AutoTest',
                'surname'  => 'FirstOrder',
                'expects'  => 'first_order_completed',
                'consents' => ['email', 'privacy'],
                'birthday' => null,
                'stats'    => [
                    'orders_count'        => 1,
                    'reservations_count'  => 0,
                    'total_spent'         => 30.00,
                    'average_order_value' => 30.00,
                    'first_order_at'      => $now->copy()->subDays(5),
                    'last_order_at'       => $now->copy()->subDays(5),
                    'first_booking_at'    => null,
                    'last_booking_at'     => null,
                    'last_activity_at'    => $now->copy()->subDays(5),
                ],
                'orders' => [
                    ['date' => $now->copy()->subDays(5), 'price' => 30.00, 'status' => 1],
                ],
                'reservations' => [],
            ],

            // ── C7: first_booking_completed ──────────────────────────────────
            // first_booking_at = 5gg fa → dentro finestra delay_days=30
            [
                'alias'    => 'auto-first-booking',
                'name'     => 'AutoTest',
                'surname'  => 'FirstBooking',
                'expects'  => 'first_booking_completed',
                'consents' => ['email', 'privacy'],
                'birthday' => null,
                'stats'    => [
                    'orders_count'        => 0,
                    'reservations_count'  => 1,
                    'total_spent'         => null,
                    'average_order_value' => null,
                    'first_order_at'      => null,
                    'last_order_at'       => null,
                    'first_booking_at'    => $now->copy()->subDays(5),
                    'last_booking_at'     => $now->copy()->subDays(5),
                    'last_activity_at'    => $now->copy()->subDays(5),
                ],
                'orders'       => [],
                'reservations' => [
                    ['date' => $now->copy()->subDays(5), 'adults' => 2, 'status' => 1],
                ],
            ],

            // ── C8: orders_without_bookings ──────────────────────────────────
            // 2 ordini, 0 prenotazioni
            [
                'alias'    => 'auto-orders-no-booking',
                'name'     => 'AutoTest',
                'surname'  => 'OrdersNoBooking',
                'expects'  => 'orders_without_bookings',
                'consents' => ['email', 'privacy'],
                'birthday' => null,
                'stats'    => [
                    'orders_count'        => 2,
                    'reservations_count'  => 0,
                    'total_spent'         => 50.00,
                    'average_order_value' => 25.00,
                    'first_order_at'      => $now->copy()->subDays(60),
                    'last_order_at'       => $now->copy()->subDays(31),
                    'first_booking_at'    => null,
                    'last_booking_at'     => null,
                    'last_activity_at'    => $now->copy()->subDays(31),
                ],
                'orders' => [
                    ['date' => $now->copy()->subDays(60), 'price' => 25.00, 'status' => 1],
                    ['date' => $now->copy()->subDays(31), 'price' => 25.00, 'status' => 1],
                ],
                'reservations' => [],
            ],

            // ── C9: bookings_without_orders ──────────────────────────────────
            // 2 prenotazioni, 0 ordini
            [
                'alias'    => 'auto-bookings-no-order',
                'name'     => 'AutoTest',
                'surname'  => 'BookingsNoOrder',
                'expects'  => 'bookings_without_orders',
                'consents' => ['email', 'privacy'],
                'birthday' => null,
                'stats'    => [
                    'orders_count'        => 0,
                    'reservations_count'  => 2,
                    'total_spent'         => null,
                    'average_order_value' => null,
                    'first_order_at'      => null,
                    'last_order_at'       => null,
                    'first_booking_at'    => $now->copy()->subDays(60),
                    'last_booking_at'     => $now->copy()->subDays(31),
                    'last_activity_at'    => $now->copy()->subDays(31),
                ],
                'orders'       => [],
                'reservations' => [
                    ['date' => $now->copy()->subDays(60), 'adults' => 2, 'status' => 1],
                    ['date' => $now->copy()->subDays(31), 'adults' => 3, 'status' => 1],
                ],
            ],

            // ── C10: customer_reaches_value ──────────────────────────────────
            // total_spent = 60€ ≥ soglia 50€, cliente attivo (5gg) → no at-risk
            [
                'alias'    => 'auto-reaches-value',
                'name'     => 'AutoTest',
                'surname'  => 'ReachesValue',
                'expects'  => 'customer_reaches_value',
                'consents' => ['email', 'privacy'],
                'birthday' => null,
                'stats'    => [
                    'orders_count'        => 3,
                    'reservations_count'  => 0,
                    'total_spent'         => 60.00,
                    'average_order_value' => 20.00,
                    'first_order_at'      => $now->copy()->subDays(20),
                    'last_order_at'       => $now->copy()->subDays(5),
                    'first_booking_at'    => null,
                    'last_booking_at'     => null,
                    'last_activity_at'    => $now->copy()->subDays(5),
                ],
                'orders' => [
                    ['date' => $now->copy()->subDays(20), 'price' => 20.00, 'status' => 1],
                    ['date' => $now->copy()->subDays(12), 'price' => 20.00, 'status' => 1],
                    ['date' => $now->copy()->subDays(5),  'price' => 20.00, 'status' => 1],
                ],
                'reservations' => [],
            ],

            // ── C11: valuable_customer_at_risk ───────────────────────────────
            // total_spent = 60€ ≥ 50€, last_activity_at = 90gg fa → supera inactive_days=60
            [
                'alias'    => 'auto-at-risk',
                'name'     => 'AutoTest',
                'surname'  => 'AtRisk',
                'expects'  => 'valuable_customer_at_risk',
                'consents' => ['email', 'privacy'],
                'birthday' => null,
                'stats'    => [
                    'orders_count'        => 3,
                    'reservations_count'  => 0,
                    'total_spent'         => 60.00,
                    'average_order_value' => 20.00,
                    'first_order_at'      => $now->copy()->subDays(120),
                    'last_order_at'       => $now->copy()->subDays(90),
                    'first_booking_at'    => null,
                    'last_booking_at'     => null,
                    'last_activity_at'    => $now->copy()->subDays(90),
                ],
                'orders' => [
                    ['date' => $now->copy()->subDays(120), 'price' => 20.00, 'status' => 1],
                    ['date' => $now->copy()->subDays(105), 'price' => 20.00, 'status' => 1],
                    ['date' => $now->copy()->subDays(90),  'price' => 20.00, 'status' => 1],
                ],
                'reservations' => [],
            ],

            // ── C12: customer_anniversary (primo ordine) ──────────────────────
            // first_order_at = 28/05/2023 → oggi 28/05 è l'anniversario (days_before=7)
            [
                'alias'    => 'auto-anniversary-order',
                'name'     => 'AutoTest',
                'surname'  => 'AnniversaryOrder',
                'expects'  => 'customer_anniversary (anniversary_source=first_order)',
                'consents' => ['email', 'privacy'],
                'birthday' => null,
                'stats'    => [
                    'orders_count'        => 3,
                    'reservations_count'  => 0,
                    'total_spent'         => 75.00,
                    'average_order_value' => 25.00,
                    'first_order_at'      => Carbon::parse('2023-05-28 12:00:00'),
                    'last_order_at'       => $now->copy()->subDays(30),
                    'first_booking_at'    => null,
                    'last_booking_at'     => null,
                    'last_activity_at'    => $now->copy()->subDays(30),
                ],
                'orders' => [
                    ['date' => Carbon::parse('2023-05-28 12:00:00'), 'price' => 25.00, 'status' => 1],
                    ['date' => Carbon::parse('2024-06-10 12:00:00'), 'price' => 25.00, 'status' => 1],
                    ['date' => $now->copy()->subDays(30),             'price' => 25.00, 'status' => 1],
                ],
                'reservations' => [],
            ],

            // ── C13: customer_anniversary (prima prenotazione) ────────────────
            // first_booking_at = 28/05/2023 → oggi 28/05 è l'anniversario (days_before=7)
            [
                'alias'    => 'auto-anniversary-booking',
                'name'     => 'AutoTest',
                'surname'  => 'AnniversaryBooking',
                'expects'  => 'customer_anniversary (anniversary_source=first_booking)',
                'consents' => ['email', 'privacy'],
                'birthday' => null,
                'stats'    => [
                    'orders_count'        => 0,
                    'reservations_count'  => 3,
                    'total_spent'         => null,
                    'average_order_value' => null,
                    'first_order_at'      => null,
                    'last_order_at'       => null,
                    'first_booking_at'    => Carbon::parse('2023-05-28 12:00:00'),
                    'last_booking_at'     => $now->copy()->subDays(30),
                    'last_activity_at'    => $now->copy()->subDays(30),
                ],
                'orders'       => [],
                'reservations' => [
                    ['date' => Carbon::parse('2023-05-28 20:00:00'), 'adults' => 2, 'status' => 1],
                    ['date' => Carbon::parse('2024-06-15 20:00:00'), 'adults' => 3, 'status' => 1],
                    ['date' => $now->copy()->subDays(30),             'adults' => 2, 'status' => 1],
                ],
            ],

            // ── C14: high_average_order_value ────────────────────────────────
            // average_order_value = 35€ ≥ 30€, 3 ordini ≥ min_orders=2
            [
                'alias'    => 'auto-high-aov',
                'name'     => 'AutoTest',
                'surname'  => 'HighAov',
                'expects'  => 'high_average_order_value',
                'consents' => ['email', 'privacy'],
                'birthday' => null,
                'stats'    => [
                    'orders_count'        => 3,
                    'reservations_count'  => 0,
                    'total_spent'         => 105.00,
                    'average_order_value' => 35.00,
                    'first_order_at'      => $now->copy()->subDays(90),
                    'last_order_at'       => $now->copy()->subDays(20),
                    'first_booking_at'    => null,
                    'last_booking_at'     => null,
                    'last_activity_at'    => $now->copy()->subDays(20),
                ],
                'orders' => [
                    ['date' => $now->copy()->subDays(90), 'price' => 35.00, 'status' => 1],
                    ['date' => $now->copy()->subDays(55), 'price' => 35.00, 'status' => 1],
                    ['date' => $now->copy()->subDays(20), 'price' => 35.00, 'status' => 1],
                ],
                'reservations' => [],
            ],

            // ── C15: nessun email marketing consent ───────────────────────────
            // last_order_at = 45gg fa → nell'audience no_order_since(30gg)
            // Ma email_marketing_consent_at=NULL → CustomerPromotion assegnata,
            // invio fallisce silenziosamente a canSend() (separazione audience/dispatch)
            [
                'alias'    => 'auto-no-email-consent',
                'name'     => 'AutoTest',
                'surname'  => 'NoEmailConsent',
                'expects'  => 'no_order_since (audience=SI, dispatch=NO — no email consent)',
                'consents' => ['privacy'],
                'birthday' => null,
                'stats'    => [
                    'orders_count'        => 1,
                    'reservations_count'  => 0,
                    'total_spent'         => 25.00,
                    'average_order_value' => 25.00,
                    'first_order_at'      => $now->copy()->subDays(45),
                    'last_order_at'       => $now->copy()->subDays(45),
                    'first_booking_at'    => null,
                    'last_booking_at'     => null,
                    'last_activity_at'    => $now->copy()->subDays(45),
                ],
                'orders' => [
                    ['date' => $now->copy()->subDays(45), 'price' => 25.00, 'status' => 1],
                ],
                'reservations' => [],
            ],

        ];
    }

    // -------------------------------------------------------------------------
    // Helpers: Customer / Order / Reservation
    // -------------------------------------------------------------------------

    private function createCustomer(array $profile, Carbon $now): void
    {
        $email        = self::EMAIL_TAG . '+' . $profile['alias'] . '@' . self::EMAIL_DOMAIN;
        $registeredAt = $now->copy()->subYear();
        $consents     = $profile['consents'] ?? [];
        $stats        = $profile['stats'];

        $customer             = new Customer();
        $customer->timestamps = false;
        $customer->forceFill([
            'name'                               => $profile['name'],
            'surname'                             => $profile['surname'],
            'email'                               => Customer::normalizeEmail($email),
            'phone'                               => '0000000000',
            'gender'                              => null,
            'birthday'                            => $profile['birthday'] ?? null,
            'registered_at'                       => $registeredAt,
            'email_verified_at'                   => $registeredAt,
            // Consensi
            'privacy_accepted_at'                 => in_array('privacy', $consents) ? $registeredAt : null,
            'email_marketing_consent_at'          => in_array('email', $consents) ? $registeredAt : null,
            'tracking_consent_at'                 => in_array('tracking', $consents) ? $registeredAt : null,
            'profiling_consent_at'                => in_array('tracking', $consents) ? $registeredAt : null,
            'whatsapp_marketing_consent_at'       => null,
            'soft_email_marketing_unsubscribed_at' => null,
            'consents_updated_at'                 => null,
            // Stats denormalizzate (sincronizzare con customers:refresh-stats)
            'orders_count'                        => $stats['orders_count'] ?? 0,
            'reservations_count'                  => $stats['reservations_count'] ?? 0,
            'interactions_count'                  => 0,
            'total_spent'                         => $stats['total_spent'] ?? 0.00,
            'average_order_value'                 => $stats['average_order_value'] ?? null,
            'first_order_at'                      => $stats['first_order_at'] ?? null,
            'last_order_at'                       => $stats['last_order_at'] ?? null,
            'first_booking_at'                    => $stats['first_booking_at'] ?? null,
            'last_booking_at'                     => $stats['last_booking_at'] ?? null,
            'last_activity_at'                    => $stats['last_activity_at'] ?? null,
            'last_marketing_contact_at'           => null,
            'customer_score'                      => null,
            'lifecycle_segment'                   => null,
            'created_at'                          => $registeredAt,
            'updated_at'                          => $now,
        ]);
        $customer->save();

        foreach ($profile['orders'] ?? [] as $orderData) {
            $this->createOrder($customer, $orderData['date'], $orderData['price'], $orderData['status']);
        }

        foreach ($profile['reservations'] ?? [] as $resData) {
            $this->createReservation($customer, $resData['date'], $resData['adults'], $resData['status']);
        }
    }

    private function createOrder(Customer $customer, Carbon $date, float $price, int $status): void
    {
        $slotFormatted = $date->format('d/m/Y H:i');

        if (Order::where('customer_id', $customer->id)->where('date_slot', $slotFormatted)->exists()) {
            return;
        }

        $order             = new Order();
        $order->timestamps = false;
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
            'created_at'          => $date,
            'updated_at'          => $date,
        ]);
        $order->save();
    }

    private function createReservation(Customer $customer, Carbon $date, int $adults, int $status): void
    {
        $slotFormatted = $date->format('d/m/Y H:i');

        if (Reservation::where('customer_id', $customer->id)->where('date_slot', $slotFormatted)->exists()) {
            return;
        }

        $reservation             = new Reservation();
        $reservation->timestamps = false;
        $reservation->forceFill([
            'customer_id'         => $customer->id,
            'name'                => $customer->name,
            'surname'             => $customer->surname,
            'email'               => $customer->email,
            'phone'               => $customer->phone,
            'date_slot'           => $slotFormatted,
            'status'              => $status,
            'n_person'            => json_encode(['adult' => $adults, 'child' => 0]),
            'sala'                => null,
            'message'             => null,
            'whatsapp_message_id' => null,
            'news_letter'         => true,
            'notificated'         => false,
            'created_at'          => $date,
            'updated_at'          => $date,
        ]);
        $reservation->save();
    }
}
