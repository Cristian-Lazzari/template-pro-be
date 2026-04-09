<?php

namespace Database\Seeders;

use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class GuestReservationsSeeder extends Seeder
{
    public function run(): void
    {
        $targetReservations = 50;

        $existingGuestReservations = Reservation::query()
            ->whereNull('customer_id')
            ->count();

        $missingReservations = max(0, $targetReservations - $existingGuestReservations);

        if ($missingReservations === 0) {
            return;
        }

        $timeSlots = ['12:00', '12:30', '13:00', '13:30', '20:00', '20:30', '21:00', '21:30'];
        $names = ['Luca', 'Marco', 'Giulia'];
        $surnames = ['Rossi', 'Bianchi'];
        $messages = [
            null,
            'Tavolo vicino alla finestra se disponibile.',
            'Arriviamo con un passeggino.',
            'Preferenza per tavolo tranquillo.',
            'Festeggiamo un compleanno.',
            'Serve un seggiolone.',
        ];
        $statuses = [0, 1, 2 ];

        for ($i = 0; $i < $missingReservations; $i++) {
            $name = fake()->randomElement($names);
            $surname = fake()->randomElement($surnames);
            $adults = fake()->numberBetween(2, 6);
            $children = fake()->boolean(35) ? fake()->numberBetween(0, 3) : 0;
            $slot = Carbon::now()
                ->addDays($i + 1)
                ->setTimeFromTimeString(fake()->randomElement($timeSlots))
                ->format('d/m/Y H:i');

            $reservation = new Reservation();
            $reservation->customer_id = null;
            $reservation->date_slot = $slot;
            $reservation->status = fake()->randomElement($statuses);
            $reservation->name = $name;
            $reservation->surname = $surname;
            $reservation->email = fake()->unique()->safeEmail();
            $reservation->phone = fake()->numerify('3#########');
            $reservation->n_person = json_encode([
                'adult' => $adults,
                'child' => $children,
            ]);
            $reservation->sala = null;
            $reservation->message = fake()->randomElement($messages);
            $reservation->whatsapp_message_id = null;
            $reservation->news_letter = fake()->boolean();
            $reservation->notificated = fake()->boolean(20);
            $reservation->created_at = now();
            $reservation->updated_at = now();
            $reservation->save();
        }
    }
}
