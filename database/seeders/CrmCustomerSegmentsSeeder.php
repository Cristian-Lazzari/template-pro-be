<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CrmCustomerSegmentsSeeder extends Seeder
{
    private const DEMO_DOMAIN = 'crm-segment.test';

    private array $customerColumns = [];

    public function run(): void
    {
        $this->customerColumns = Schema::hasTable('customers')
            ? Schema::getColumnListing('customers')
            : [];

        DB::transaction(function (): void {
            $this->cleanupDemoFixtures();
            $this->seedProfiles();
        });

        $this->command?->info('Seeder CRM clienti completato.');
        $this->command?->line('Fixture incluse:');
        $this->command?->line('- Nina Nuova: new_customers + order_only');
        $this->command?->line('- Paolo Freddo: new_customers + reservation_only + low_engagement');
        $this->command?->line('- Alberto Attivo: active_customers con ordini e prenotazioni');
        $this->command?->line('- Lucia Fedele: loyal_customers');
        $this->command?->line('- Roberto Rischio: at_risk_customers');
        $this->command?->line('- Laura Persa: lost_customers');
        $this->command?->line('- Valerio Premium: high_value_customers');
        $this->command?->line('- Chiara Rituale: habit_customers');
        $this->command?->line('- Anna Ricostruita: deduplica registrato + guest ricostruito');
        $this->command?->line('- Giulio Fallback: fallback da customer_score = 0 se la colonna esiste');
        $this->command?->line('- Marta Cache: uso di metriche CRM cached se le colonne esistono');
        $this->command?->line('- Irene Dormiente: cliente senza interazioni confermate');
    }

    private function seedProfiles(): void
    {
        $now = now()->startOfMinute();

        $this->createOrder(null, [
            'name' => 'Nina',
            'surname' => 'Nuova',
            'email' => $this->email('nina.nuova'),
            'phone' => '3330000001',
            'tot_price' => 52.00,
            'activity_at' => $now->copy()->subDays(5)->setTime(12, 30),
            'news_letter' => true,
        ]);
        $this->createOrder(null, [
            'name' => 'Nina',
            'surname' => 'Nuova',
            'email' => $this->email('nina.nuova'),
            'phone' => '3330000001',
            'tot_price' => 28.00,
            'activity_at' => $now->copy()->subDay()->setTime(12, 30),
            'status' => 0,
        ]);

        $this->createReservation(null, [
            'name' => 'Paolo',
            'surname' => 'Freddo',
            'email' => $this->email('paolo.freddo'),
            'phone' => '3330000002',
            'activity_at' => $now->copy()->subDays(7)->setTime(20, 0),
            'news_letter' => false,
        ]);

        $alberto = $this->createCustomer([
            'name' => 'Alberto',
            'surname' => 'Attivo',
            'email' => $this->email('alberto.attivo'),
            'phone' => '3330000003',
            'registered_at' => $now->copy()->subMonths(4),
            'email_verified_at' => $now->copy()->subMonths(4),
            'marketing_consent_at' => $now->copy()->subMonths(4),
            'created_at' => $now->copy()->subMonths(4),
            'updated_at' => $now->copy()->subDays(2),
        ]);
        $this->createOrder($alberto, [
            'tot_price' => 34.00,
            'activity_at' => $now->copy()->subDays(7)->setTime(12, 45),
            'news_letter' => true,
        ]);
        $this->createReservation($alberto, [
            'activity_at' => $now->copy()->subDays(2)->setTime(20, 30),
            'news_letter' => true,
        ]);

        $lucia = $this->createCustomer([
            'name' => 'Lucia',
            'surname' => 'Fedele',
            'email' => $this->email('lucia.fedele'),
            'phone' => '3330000004',
            'registered_at' => $now->copy()->subMonths(10),
            'email_verified_at' => $now->copy()->subMonths(10),
            'marketing_consent_at' => $now->copy()->subMonths(10),
            'profiling_consent_at' => $now->copy()->subMonths(8),
            'created_at' => $now->copy()->subMonths(10),
            'updated_at' => $now->copy()->subDays(20),
        ], [
            'customer_score' => 82,
            'lifecycle_segment' => 'loyal_customers',
        ]);
        foreach ([48, 41, 34, 27, 20] as $daysAgo) {
            $this->createOrder($lucia, [
                'tot_price' => 18.00,
                'activity_at' => $now->copy()->subDays($daysAgo)->setTime(20, 15),
                'news_letter' => true,
            ]);
        }

        $this->createOrder(null, [
            'name' => 'Roberto',
            'surname' => 'Rischio',
            'email' => $this->email('roberto.rischio'),
            'phone' => '3330000005',
            'tot_price' => 26.00,
            'activity_at' => $now->copy()->subDays(52)->setTime(12, 15),
        ]);
        $this->createOrder(null, [
            'name' => 'Roberto',
            'surname' => 'Rischio',
            'email' => $this->email('roberto.rischio'),
            'phone' => '3330000005',
            'tot_price' => 29.00,
            'activity_at' => $now->copy()->subDays(45)->setTime(20, 0),
        ]);

        $this->createReservation(null, [
            'name' => 'Laura',
            'surname' => 'Persa',
            'email' => $this->email('laura.persa'),
            'phone' => '3330000006',
            'activity_at' => $now->copy()->subDays(90)->setTime(20, 0),
        ]);
        $this->createReservation(null, [
            'name' => 'Laura',
            'surname' => 'Persa',
            'email' => $this->email('laura.persa'),
            'phone' => '3330000006',
            'activity_at' => $now->copy()->subDays(75)->setTime(20, 0),
        ]);

        $valerio = $this->createCustomer([
            'name' => 'Valerio',
            'surname' => 'Premium',
            'email' => $this->email('valerio.premium'),
            'phone' => '3330000007',
            'registered_at' => $now->copy()->subMonths(3),
            'email_verified_at' => $now->copy()->subMonths(3),
            'marketing_consent_at' => $now->copy()->subMonths(3),
            'created_at' => $now->copy()->subMonths(3),
            'updated_at' => $now->copy()->subDays(3),
        ]);
        $this->createOrder($valerio, [
            'tot_price' => 80.00,
            'activity_at' => $now->copy()->subDays(8)->setTime(20, 0),
            'news_letter' => true,
        ]);
        $this->createOrder($valerio, [
            'tot_price' => 60.00,
            'activity_at' => $now->copy()->subDays(3)->setTime(20, 30),
            'news_letter' => true,
        ]);
        $this->createOrder($valerio, [
            'tot_price' => 45.00,
            'activity_at' => $now->copy()->subDay()->setTime(21, 0),
            'status' => 6,
        ]);

        foreach ([3, 2, 1] as $weeksAgo) {
            $this->createReservation(null, [
                'name' => 'Chiara',
                'surname' => 'Rituale',
                'email' => $this->email('chiara.rituale'),
                'phone' => '3330000008',
                'activity_at' => $now->copy()->subWeeks($weeksAgo)->setTime(20, 0),
                'news_letter' => true,
            ]);
        }

        $anna = $this->createCustomer([
            'name' => 'Anna',
            'surname' => 'Ricostruita',
            'email' => $this->email('anna.ricostruita'),
            'phone' => '333 111 2222',
            'registered_at' => $now->copy()->subMonths(5),
            'email_verified_at' => $now->copy()->subMonths(5),
            'marketing_consent_at' => $now->copy()->subMonths(5),
            'created_at' => $now->copy()->subMonths(5),
            'updated_at' => $now->copy()->subDay(),
        ]);
        $this->createOrder(null, [
            'name' => 'Anna',
            'surname' => 'Ricostruita',
            'email' => strtoupper($anna->email),
            'phone' => '3331112222',
            'tot_price' => 35.00,
            'activity_at' => $now->copy()->subDays(3)->setTime(12, 30),
            'news_letter' => true,
        ]);
        $this->createReservation(null, [
            'name' => 'Anna',
            'surname' => 'Ricostruita',
            'email' => $this->email('anna.secondaria'),
            'phone' => '+39 333 111 2222',
            'activity_at' => $now->copy()->subDay()->setTime(20, 15),
            'news_letter' => true,
        ]);

        $giulio = $this->createCustomer([
            'name' => 'Giulio',
            'surname' => 'Fallback',
            'email' => $this->email('giulio.fallback'),
            'phone' => '3330000009',
            'registered_at' => $now->copy()->subMonths(2),
            'email_verified_at' => $now->copy()->subMonths(2),
            'created_at' => $now->copy()->subMonths(2),
            'updated_at' => $now->copy()->subDays(2),
        ], [
            'customer_score' => 0,
        ]);
        foreach ([15, 8, 2] as $daysAgo) {
            $this->createOrder($giulio, [
                'tot_price' => 50.00,
                'activity_at' => $now->copy()->subDays($daysAgo)->setTime(12, 30),
            ]);
        }

        $marta = $this->createCustomer([
            'name' => 'Marta',
            'surname' => 'Cache',
            'email' => $this->email('marta.cache'),
            'phone' => '3330000010',
            'registered_at' => $now->copy()->subMonths(6),
            'email_verified_at' => $now->copy()->subMonths(6),
            'marketing_consent_at' => $now->copy()->subMonths(6),
            'profiling_consent_at' => $now->copy()->subMonths(5),
            'created_at' => $now->copy()->subMonths(6),
            'updated_at' => $now->copy()->subDays(4),
        ], [
            'customer_score' => 88,
            'lifecycle_segment' => 'loyal_customers',
            'orders_count' => 2,
            'reservations_count' => 1,
            'interactions_count' => 3,
            'total_spent' => 68.00,
            'last_activity_at' => $now->copy()->subDays(4),
        ]);
        $this->createOrder($marta, [
            'tot_price' => 32.00,
            'activity_at' => $now->copy()->subDays(12)->setTime(20, 0),
            'news_letter' => true,
        ]);
        $this->createReservation($marta, [
            'activity_at' => $now->copy()->subDays(4)->setTime(20, 30),
            'news_letter' => true,
        ]);

        $this->createCustomer([
            'name' => 'Irene',
            'surname' => 'Dormiente',
            'email' => $this->email('irene.dormiente'),
            'phone' => '3330000011',
            'registered_at' => $now->copy()->subMonths(7),
            'email_verified_at' => $now->copy()->subMonths(7),
            'created_at' => $now->copy()->subMonths(7),
            'updated_at' => $now->copy()->subMonths(7),
        ]);
    }

    private function cleanupDemoFixtures(): void
    {
        $pattern = $this->domainPattern();
        $customerIds = Customer::query()
            ->where('email', 'like', $pattern)
            ->pluck('id');

        Order::query()
            ->where('email', 'like', $pattern)
            ->when($customerIds->isNotEmpty(), function ($query) use ($customerIds) {
                $query->orWhereIn('customer_id', $customerIds->all());
            })
            ->delete();

        Reservation::query()
            ->where('email', 'like', $pattern)
            ->when($customerIds->isNotEmpty(), function ($query) use ($customerIds) {
                $query->orWhereIn('customer_id', $customerIds->all());
            })
            ->delete();

        Customer::query()
            ->where('email', 'like', $pattern)
            ->delete();
    }

    private function createCustomer(array $attributes, array $optionalMetrics = []): Customer
    {
        $customer = new Customer;
        $customer->forceFill([
            'name' => $attributes['name'],
            'surname' => $attributes['surname'],
            'email' => $attributes['email'],
            'phone' => $attributes['phone'] ?? null,
            'gender' => $attributes['gender'] ?? null,
            'age' => $attributes['age'] ?? null,
            'profile_answers' => $attributes['profile_answers'] ?? [],
            'registered_at' => $attributes['registered_at'] ?? null,
            'marketing_consent_at' => $attributes['marketing_consent_at'] ?? null,
            'profiling_consent_at' => $attributes['profiling_consent_at'] ?? null,
            'email_verified_at' => $attributes['email_verified_at'] ?? null,
            'created_at' => $attributes['created_at'] ?? now(),
            'updated_at' => $attributes['updated_at'] ?? now(),
        ]);

        foreach ($optionalMetrics as $column => $value) {
            if (in_array($column, $this->customerColumns, true)) {
                $customer->{$column} = $value;
            }
        }

        $customer->timestamps = false;
        $customer->save();

        return $customer;
    }

    private function createOrder(?Customer $customer, array $attributes): Order
    {
        /** @var Carbon $activityAt */
        $activityAt = $attributes['activity_at'] ?? now();
        $email = $attributes['email'] ?? $customer?->email;
        $phone = $attributes['phone'] ?? $customer?->phone;
        $name = $attributes['name'] ?? $customer?->name ?? 'Cliente';
        $surname = $attributes['surname'] ?? $customer?->surname ?? 'CRM';

        $order = new Order;
        $order->forceFill([
            'customer_id' => $attributes['customer_id'] ?? $customer?->id,
            'date_slot' => $activityAt->format('d/m/Y H:i'),
            'status' => $attributes['status'] ?? 1,
            'name' => $name,
            'surname' => $surname,
            'email' => $email,
            'phone' => $phone,
            'checkout_session_id' => null,
            'address' => $attributes['address'] ?? null,
            'address_n' => $attributes['address_n'] ?? null,
            'comune' => $attributes['comune'] ?? null,
            'whatsapp_message_id' => null,
            'tot_price' => $attributes['tot_price'] ?? 25.00,
            'message' => $attributes['message'] ?? null,
            'news_letter' => $attributes['news_letter'] ?? false,
            'notificated' => false,
            'created_at' => $attributes['created_at'] ?? $activityAt,
            'updated_at' => $attributes['updated_at'] ?? $activityAt,
        ]);
        $order->timestamps = false;
        $order->save();

        return $order;
    }

    private function createReservation(?Customer $customer, array $attributes): Reservation
    {
        /** @var Carbon $activityAt */
        $activityAt = $attributes['activity_at'] ?? now();
        $email = $attributes['email'] ?? $customer?->email;
        $phone = $attributes['phone'] ?? $customer?->phone;
        $name = $attributes['name'] ?? $customer?->name ?? 'Cliente';
        $surname = $attributes['surname'] ?? $customer?->surname ?? 'CRM';

        $reservation = new Reservation;
        $reservation->forceFill([
            'customer_id' => $attributes['customer_id'] ?? $customer?->id,
            'date_slot' => $activityAt->format('d/m/Y H:i'),
            'status' => $attributes['status'] ?? 1,
            'name' => $name,
            'surname' => $surname,
            'email' => $email,
            'phone' => $phone,
            'n_person' => json_encode($attributes['n_person'] ?? ['adult' => 2, 'child' => 0]),
            'sala' => $attributes['sala'] ?? null,
            'message' => $attributes['message'] ?? null,
            'whatsapp_message_id' => null,
            'news_letter' => $attributes['news_letter'] ?? false,
            'notificated' => false,
            'created_at' => $attributes['created_at'] ?? $activityAt,
            'updated_at' => $attributes['updated_at'] ?? $activityAt,
        ]);
        $reservation->timestamps = false;
        $reservation->save();

        return $reservation;
    }

    private function email(string $localPart): string
    {
        return $localPart.'@'.self::DEMO_DOMAIN;
    }

    private function domainPattern(): string
    {
        return '%@'.self::DEMO_DOMAIN;
    }
}
