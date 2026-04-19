<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\ReservationController as ApiReservationController;
use App\Models\Reservation;
use App\Models\Setting;
use App\Services\CustomerAuth\VerifiedCheckoutSessionService;
use Carbon\Carbon;
use Database\Seeders\SettingsTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ReservationFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SettingsTableSeeder::class);

        config([
            'configurazione.APP_URL' => 'http://localhost',
            'configurazione.WA_ID' => '123456789',
            'configurazione.WA_TO' => 'Bearer test-token',
        ]);

        $controller = \Mockery::mock(ApiReservationController::class, [
            app(VerifiedCheckoutSessionService::class),
            app(\App\Services\CustomerAuth\CustomerAccessService::class),
        ])->makePartial();

        $controller->shouldAllowMockingProtectedMethods();
        $controller->shouldReceive('save_message')->andReturn([]);
        $controller->shouldReceive('send_mail')->andReturnNull();

        $this->app->instance(ApiReservationController::class, $controller);
    }

    public function test_reservation_store_rejects_a_day_marked_as_day_off(): void
    {
        Http::fake();

        $slot = Carbon::now()->addDays(7)->setTime(19, 0)->startOfMinute();
        $this->configureReservationSlot($slot, [
            'day_off' => [$slot->format('Y-m-d')],
            'max_table' => 6,
        ]);

        $response = $this->postJson('/api/reservations', $this->reservationPayload($slot));

        $response
            ->assertOk()
            ->assertJson([
                'success' => false,
                'r' => '56',
            ]);

        $this->assertDatabaseCount('reservations', 0);
    }

    public function test_reservation_store_counts_existing_reservations_for_the_same_slot(): void
    {
        Http::fake();

        $slot = Carbon::now()->addDays(8)->setTime(20, 0)->startOfMinute();
        $this->configureReservationSlot($slot, [
            'max_table' => 4,
        ]);

        DB::table('reservations')->insert([
            'date_slot' => $slot->format('d/m/Y H:i'),
            'status' => 2,
            'name' => 'Mario',
            'surname' => 'Rossi',
            'email' => 'mario@example.com',
            'phone' => '3331111111',
            'n_person' => json_encode(['adult' => 4, 'child' => 0]),
            'sala' => null,
            'message' => null,
            'whatsapp_message_id' => null,
            'news_letter' => false,
            'notificated' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->postJson('/api/reservations', $this->reservationPayload($slot, [
            'email' => 'anna@example.com',
        ]));

        $response
            ->assertOk()
            ->assertJson([
                'success' => false,
                'r' => '86',
            ]);

        $this->assertDatabaseCount('reservations', 1);
    }

    public function test_reservation_store_accepts_slots_saved_as_string_service_ids(): void
    {
        Http::fake([
            'https://graph.facebook.com/*' => Http::response([
                'messages' => [
                    ['id' => 'wamid.string-slot.1'],
                ],
            ], 200),
        ]);

        $slot = Carbon::now()->addDays(9)->setTime(19, 0)->startOfMinute();
        $this->configureReservationSlot($slot, [
            'max_table' => 6,
        ]);

        $setting = Setting::query()->where('name', 'advanced')->firstOrFail();
        $property = json_decode($setting->property, true);
        $dayOfWeek = (int) $slot->format('N');
        $property['week_set'][$dayOfWeek][$slot->format('H:i')] = ['1'];

        $setting->update([
            'property' => json_encode($property),
        ]);

        $response = $this->postJson('/api/reservations', $this->reservationPayload($slot, [
            'email' => 'string-slot@example.com',
        ]));

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseCount('reservations', 1);
    }

    public function test_reservation_store_uses_room_specific_capacity_when_double_room_is_enabled(): void
    {
        Http::fake([
            'https://graph.facebook.com/*' => Http::response([
                'messages' => [
                    ['id' => 'wamid.room.1'],
                ],
            ], 200),
        ]);

        $slot = Carbon::now()->addDays(9)->setTime(21, 0)->startOfMinute();
        $this->configureReservationSlot($slot, [
            'dt' => true,
            'max_table' => 0,
            'max_table_1' => 2,
            'max_table_2' => 6,
            'sala_1' => 'Interno',
            'sala_2' => 'Esterno',
        ]);

        $response = $this->postJson('/api/reservations', $this->reservationPayload($slot, [
            'sala' => 1,
        ]));

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $reservation = Reservation::query()->sole();

        $this->assertSame('1', (string) $reservation->sala);
    }

    public function test_reservation_store_retries_with_template_when_interactive_message_fails(): void
    {
        Http::fake([
            'https://graph.facebook.com/*' => Http::sequence()
                ->push([
                    'messages' => [
                        ['id' => 'wamid.first'],
                    ],
                ], 200)
                ->push([
                    'error' => [
                        'message' => 'Second number failed',
                    ],
                ], 500)
                ->push([
                    'messages' => [
                        ['id' => 'wamid.second.template'],
                    ],
                ], 200),
        ]);

        $slot = Carbon::now()->addDays(10)->setTime(19, 30)->startOfMinute();
        $this->configureReservationSlot($slot, [
            'max_table' => 6,
        ]);
        $this->updateWhatsappNumbers(['39000111000', '39000111001'], true);

        $response = $this->postJson('/api/reservations', $this->reservationPayload($slot));

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $reservation = Reservation::query()->sole();

        $this->assertSame(
            ['wamid.first', 'wamid.second.template'],
            json_decode($reservation->whatsapp_message_id, true)
        );
        Http::assertSentCount(3);
    }

    private function reservationPayload(Carbon $slot, array $overrides = []): array
    {
        $email = $overrides['email'] ?? 'cliente@example.com';
        $verifiedSession = app(VerifiedCheckoutSessionService::class)->issue($email);

        return array_merge([
            'name' => 'Anna',
            'surname' => 'Bianchi',
            'phone' => '3331234567',
            'email' => $email,
            'n_adult' => '2',
            'n_child' => '0',
            'message' => 'Vicino alla finestra',
            'news_letter' => false,
            'save_details' => false,
            'lang' => 'it',
            'date_slot' => $slot->format('Y-m-d H:i'),
            'verified_session_token' => $verifiedSession['token'],
        ], $overrides);
    }

    private function configureReservationSlot(Carbon $slot, array $overrides = []): void
    {
        $setting = Setting::query()->where('name', 'advanced')->firstOrFail();
        $property = json_decode($setting->property, true);
        $dayOfWeek = (int) $slot->format('N');

        $property['week_set'][$dayOfWeek] = [
            $slot->format('H:i') => [1],
        ];
        $property['day_off'] = [];
        $property['dt'] = false;
        $property['max_table'] = 6;
        $property['max_table_1'] = 0;
        $property['max_table_2'] = 0;
        $property['max_day_res'] = max((int) ($property['max_day_res'] ?? 30), Carbon::now()->diffInDays($slot) + 2);

        $setting->update([
            'property' => json_encode(array_replace_recursive($property, $overrides)),
        ]);
    }

    private function updateWhatsappNumbers(array $numbers, bool $within24Hours = false): void
    {
        $setting = Setting::query()->where('name', 'wa')->firstOrFail();
        $property = json_decode($setting->property, true);

        $property['numbers'] = $numbers;
        $reference = $within24Hours ? Carbon::now()->subHour() : Carbon::now()->subDays(2);
        $property['last_response_wa_1'] = $reference->toDateTimeString();
        $property['last_response_wa_2'] = $reference->toDateTimeString();

        $setting->update([
            'property' => json_encode($property),
        ]);
    }
}
