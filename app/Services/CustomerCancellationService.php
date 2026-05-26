<?php

namespace App\Services;

use App\Mail\confermaOrdineAdmin;
use App\Models\CustomerPromotion;
use App\Models\Ingredient;
use App\Models\Order;
use App\Models\Reservation;
use App\Models\Setting;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Stripe\Refund;
use Stripe\Stripe;

class CustomerCancellationService
{
    public const TYPE_ORDER = 'or';
    public const TYPE_RESERVATION = 'res';

    public function normalizeType(?string $type): ?string
    {
        return match ($type) {
            self::TYPE_ORDER, 'order', 'orders' => self::TYPE_ORDER,
            self::TYPE_RESERVATION, 'reservation', 'reservations' => self::TYPE_RESERVATION,
            default => null,
        };
    }

    public function findEntity(string $type, int $id): Order|Reservation|null
    {
        $type = $this->normalizeType($type);

        return match ($type) {
            self::TYPE_ORDER => Order::query()->find($id),
            self::TYPE_RESERVATION => Reservation::query()->find($id),
            default => null,
        };
    }

    public function eligibility(Order|Reservation $entity, string $type, ?Carbon $now = null): array
    {
        $type = $this->normalizeType($type);
        $now = $now ?: Carbon::now();
        $status = (int) $entity->status;

        if (! $type) {
            return $this->eligibilityResult(false, 'invalid_type');
        }

        if ($this->isAlreadyCancelled($status)) {
            return $this->eligibilityResult(false, 'already_cancelled', alreadyCancelled: true);
        }

        if (! $this->isStatusCancellable($status, $type)) {
            return $this->eligibilityResult(false, 'status_not_cancellable');
        }

        $dateSlot = $this->parseDateSlot($entity->date_slot);

        if (! $dateSlot) {
            return $this->eligibilityResult(false, 'invalid_date_slot');
        }

        $withinGracePeriod = $entity->created_at
            ? $entity->created_at->gt($now->copy()->subMinutes(5))
            : false;
        $sufficientNotice = $dateSlot->gt($now->copy()->addHours(24));
        $allowed = $withinGracePeriod || $sufficientNotice;

        return $this->eligibilityResult(
            $allowed,
            $allowed ? null : 'too_late',
            withinGracePeriod: $withinGracePeriod,
            sufficientNotice: $sufficientNotice,
            dateSlot: $dateSlot,
        );
    }

    public function cancel(Order|Reservation $entity, string $type): array
    {
        $type = $this->normalizeType($type);
        $eligibility = $this->eligibility($entity, (string) $type);

        if ($eligibility['already_cancelled']) {
            return [
                'success' => true,
                'already_cancelled' => true,
                'reason' => 'already_cancelled',
                'released_promotions' => 0,
            ];
        }

        if (! $eligibility['allowed']) {
            return [
                'success' => false,
                'already_cancelled' => false,
                'reason' => $eligibility['reason'],
                'released_promotions' => 0,
            ];
        }

        $result = $type === self::TYPE_ORDER
            ? $this->cancelOrder($entity)
            : $this->cancelReservation($entity);

        if (! ($result['success'] ?? false)) {
            return $result;
        }

        $this->sendCancellationEmailToAdmin($entity, $type);
        $this->sendWhatsappCancellationToRestaurant($entity, $type);

        return $result;
    }

    public function restaurantPhone(): string
    {
        $set = Setting::where('name', 'Contatti')->first();
        if (! $set) {
            return '';
        }

        $property = json_decode($set->property, true);

        return is_array($property) ? ($property['telefono'] ?? '') : '';
    }

    private function cancelOrder(Order $order): array
    {
        $status = (int) $order->status;
        $message = null;

        if (in_array($status, [3, 5], true)) {
            if ($order->checkout_session_id === null) {
                return [
                    'success' => false,
                    'reason' => 'refund_reference_missing',
                    'released_promotions' => 0,
                ];
            }

            try {
                Stripe::setApiKey(config('configurazione.STRIPE_SECRET'));
                Refund::create(['payment_intent' => $order->checkout_session_id]);
                $order->status = 6;
                $message = 'L\'ordine è stato annullato e RIMBORSATO correttamente';
            } catch (Exception $e) {
                Log::error('(CustomerCancellationService) Errore rimborso Stripe', [
                    'order_id' => $order->getKey(),
                    'message' => $e->getMessage(),
                ]);

                return [
                    'success' => false,
                    'reason' => 'refund_failed',
                    'released_promotions' => 0,
                ];
            }
        } elseif (in_array($status, [1, 2], true)) {
            $order->status = 0;
            $message = 'L\'ordine è stato annullato correttamente';
        } else {
            return [
                'success' => false,
                'reason' => 'status_not_cancellable',
                'released_promotions' => 0,
            ];
        }

        $releasedPromotions = DB::transaction(function () use ($order) {
            $order->save();

            return $this->releasePromotionsFor('order_id', (int) $order->getKey(), 'order_cancelled');
        });

        $this->sendOrderCancellationEmailToCustomer($order);

        return [
            'success' => true,
            'already_cancelled' => false,
            'reason' => null,
            'message' => $message,
            'released_promotions' => $releasedPromotions,
        ];
    }

    private function cancelReservation(Reservation $reservation): array
    {
        if ((int) $reservation->status === 0) {
            return [
                'success' => true,
                'already_cancelled' => true,
                'reason' => 'already_cancelled',
                'released_promotions' => 0,
            ];
        }

        $releasedPromotions = DB::transaction(function () use ($reservation) {
            $reservation->status = 0;
            $reservation->save();

            return $this->releasePromotionsFor('reservation_id', (int) $reservation->getKey(), 'reservation_cancelled');
        });

        $this->sendReservationCancellationEmailToCustomer($reservation);

        return [
            'success' => true,
            'already_cancelled' => false,
            'reason' => null,
            'released_promotions' => $releasedPromotions,
        ];
    }

    private function releasePromotionsFor(string $foreignKey, int $id, string $reason): int
    {
        $released = 0;

        CustomerPromotion::query()
            ->where($foreignKey, $id)
            ->lockForUpdate()
            ->get()
            ->each(function (CustomerPromotion $customerPromotion) use (&$released, $foreignKey, $id, $reason) {
                $wasUsed = $customerPromotion->promo_used !== null || $customerPromotion->status === 'used';
                $metadata = is_array($customerPromotion->metadata) ? $customerPromotion->metadata : [];

                $this->removeReusableRecreatedAssignment($customerPromotion, $metadata);

                $metadata['last_cancelled_usage'] = [
                    'reason' => $reason,
                    $foreignKey => $id,
                    'cancelled_at' => Carbon::now()->toISOString(),
                    'previous_promo_used' => $customerPromotion->promo_used?->toISOString(),
                    'previous_discount_amount' => $customerPromotion->discount_amount,
                ];

                foreach ($this->appliedMetadataKeys() as $key) {
                    unset($metadata[$key]);
                }

                $customerPromotion->promo_used = null;
                $customerPromotion->status = $this->statusBeforeUse($customerPromotion);
                $customerPromotion->discount_amount = null;
                $customerPromotion->order_id = null;
                $customerPromotion->reservation_id = null;
                $customerPromotion->metadata = $metadata;
                $customerPromotion->save();

                if ($wasUsed) {
                    DB::table('promotions')
                        ->where('id', $customerPromotion->promotion_id)
                        ->where('total_used', '>', 0)
                        ->decrement('total_used');
                }

                $released++;
            });

        return $released;
    }

    private function removeReusableRecreatedAssignment(CustomerPromotion $customerPromotion, array &$metadata): void
    {
        $recreatedId = $metadata['reusable_recreated_customer_promotion_id'] ?? null;

        if (! $recreatedId) {
            return;
        }

        $recreated = CustomerPromotion::query()
            ->whereKey($recreatedId)
            ->lockForUpdate()
            ->first();

        $recreatedMetadata = is_array($recreated?->metadata) ? $recreated->metadata : [];
        $isUntouchedRecreatedAssignment = $recreated
            && (int) ($recreatedMetadata['reusable_parent_id'] ?? 0) === (int) $customerPromotion->getKey()
            && $recreated->promo_used === null
            && $recreated->order_id === null
            && $recreated->reservation_id === null
            && $recreated->email_sent_at === null
            && $recreated->email_open_at === null
            && $recreated->email_click_at === null;

        if ($isUntouchedRecreatedAssignment) {
            $recreated->delete();
        }

        unset($metadata['reusable_recreated_customer_promotion_id']);
    }

    private function appliedMetadataKeys(): array
    {
        return [
            'affected_items',
            'applied_from',
            'discount_amount',
            'reservation_date_slot',
            'reservation_n_adult',
            'reservation_n_child',
            'reservation_people',
            'reservation_sala',
            'subtotal_after_discount',
            'subtotal_before_discount',
            'total_after_discount',
        ];
    }

    private function statusBeforeUse(CustomerPromotion $customerPromotion): string
    {
        if ($customerPromotion->email_click_at !== null) {
            return 'clicked';
        }

        if ($customerPromotion->email_open_at !== null) {
            return 'opened';
        }

        if ($customerPromotion->email_sent_at !== null) {
            return 'sent';
        }

        return 'assigned';
    }

    private function sendCancellationEmailToAdmin(Order|Reservation $entity, string $type): void
    {
        $contact = $this->contactSettings();
        $propertyAdv = $this->advancedSettings();

        if ($type === self::TYPE_ORDER) {
            $bodymail = [
                'type' => 'or',
                'to' => 'admin',
                'title' => 'Ordine annullato dal cliente',
                'subtitle' => '',
                'order_id' => $entity->id,
                'name' => $entity->name,
                'surname' => $entity->surname,
                'email' => $entity->email,
                'date_slot' => $entity->date_slot,
                'message' => $entity->message,
                'phone' => $entity->phone,
                'admin_phone' => $contact['telefono'] ?? '',
                'comune' => $entity->comune,
                'address' => $entity->address,
                'address_n' => $entity->address_n,
                'status' => 0,
                'whatsapp_message_id' => null,
                'cart' => ['products' => [], 'menus' => []],
                'total_price' => $entity->tot_price,
                'property_adv' => $propertyAdv,
            ];
        } else {
            $bodymail = [
                'type' => 'res',
                'to' => 'admin',
                'title' => 'Prenotazione annullata dal cliente',
                'subtitle' => '',
                'res_id' => $entity->id,
                'name' => $entity->name,
                'surname' => $entity->surname,
                'email' => $entity->email,
                'date_slot' => $entity->date_slot,
                'message' => $entity->message,
                'sala' => $entity->sala,
                'phone' => $entity->phone,
                'admin_phone' => $contact['telefono'] ?? '',
                'whatsapp_message_id' => null,
                'n_person' => $entity->n_person,
                'status' => 0,
                'property_adv' => $propertyAdv,
            ];
        }

        Mail::to(config('configurazione.mf'))->send(new confermaOrdineAdmin($bodymail));
    }

    private function sendOrderCancellationEmailToCustomer(Order $order): void
    {
        $order->loadMissing('products', 'menus');
        $productRows = [];

        foreach ($order->products as $product) {
            $optionItems = [];
            $addItems = [];

            foreach ($this->decodeJsonArray($product->pivot->option ?? null) as $option) {
                $ingredient = Ingredient::findByName($option);
                if ($ingredient) {
                    $optionItems[] = $ingredient;
                }
            }

            foreach ($this->decodeJsonArray($product->pivot->add ?? null) as $add) {
                $ingredient = Ingredient::findByName($add);
                if ($ingredient) {
                    $addItems[] = $ingredient;
                }
            }

            $product->setAttribute('r_option', $optionItems);
            $product->setAttribute('r_add', $addItems);
            $productRows[] = $product;
        }

        $contact = $this->contactSettings();
        $bodymail = [
            'type' => 'or',
            'to' => 'user',
            'title' => 'Come richiesto il tuo ordine è stato annullato',
            'subtitle' => (int) $order->status === 6 ? 'Il tuo rimborso verrà elaborato in 5-10 giorni lavorativi' : '',
            'whatsapp_message_id' => $order->whatsapp_message_id,
            'order_id' => $order->id,
            'name' => $order->name,
            'surname' => $order->surname,
            'email' => $order->email,
            'date_slot' => $order->date_slot,
            'message' => $order->message,
            'phone' => $order->phone,
            'admin_phone' => $contact['telefono'] ?? '',
            'comune' => $order->comune,
            'address' => $order->address,
            'address_n' => $order->address_n,
            'status' => $order->status,
            'cart' => ['products' => $productRows, 'menus' => $order->menus],
            'total_price' => $order->tot_price,
            'property_adv' => $this->advancedSettings(),
        ];

        Mail::to($order->email)->send(new confermaOrdineAdmin($bodymail));
    }

    private function sendReservationCancellationEmailToCustomer(Reservation $reservation): void
    {
        $contact = $this->contactSettings();
        $bodymail = [
            'type' => 'res',
            'to' => 'user',
            'title' => 'Come richiesto la tua prenotazione è stata annullata',
            'subtitle' => '',
            'whatsapp_message_id' => $reservation->whatsapp_message_id,
            'res_id' => $reservation->id,
            'name' => $reservation->name,
            'surname' => $reservation->surname,
            'email' => $reservation->email,
            'date_slot' => $reservation->date_slot,
            'message' => $reservation->message,
            'sala' => $reservation->sala,
            'phone' => $reservation->phone,
            'admin_phone' => $contact['telefono'] ?? '',
            'n_person' => $reservation->n_person,
            'status' => $reservation->status,
            'property_adv' => $this->advancedSettings(),
        ];

        Mail::to($reservation->email)->send(new confermaOrdineAdmin($bodymail));
    }

    private function sendWhatsappCancellationToRestaurant(Order|Reservation $entity, string $type): void
    {
        $setting = Setting::where('name', 'wa')->first();
        $property = $setting ? json_decode($setting->property, true) : null;
        $numbers = is_array($property['numbers'] ?? null) ? $property['numbers'] : [];

        if ($numbers === []) {
            return;
        }

        $adminPath = $type === self::TYPE_ORDER ? 'orders' : 'reservations';
        $linkId = config('configurazione.APP_URL') . '/admin/' . $adminPath . '/' . $entity->id;

        foreach ($numbers as $index => $number) {
            $this->messageDefault($type, (int) $index, $entity, $number, $linkId);
        }
    }

    private function messageDefault(string $type, int $index, Order|Reservation $entity, string $number, string $linkId): mixed
    {
        try {
            $isOrder = $type === self::TYPE_ORDER;
            $message = $isOrder ? 'L\'ordine è stato ' : 'La prenotazione è stata ';
            $subject = $isOrder ? 'L\'ordine è stato' : 'La prenotazione è stata';

            $message .= '*annullat' . ($isOrder ? 'o* ❌' : 'a* ❌');
            $word = 'annullat' . ($isOrder ? 'o ❌' : 'a ❌');
            $message .= ' dal *cliente*';

            $messages = json_decode((string) $entity->whatsapp_message_id, true);
            $oldId = is_array($messages) ? ($messages[$index] ?? null) : null;

            $textPayload = [
                'messaging_product' => 'whatsapp',
                'recipient_type' => 'individual',
                'to' => $number,
                'type' => 'text',
                'text' => [
                    'body' => $message,
                ],
            ];

            $templatePayload = [
                'messaging_product' => 'whatsapp',
                'recipient_type' => 'individual',
                'to' => $number,
                'type' => 'template',
                'template' => [
                    'name' => 'response_link',
                    'language' => [
                        'code' => 'it',
                    ],
                    'components' => [
                        [
                            'type' => 'body',
                            'parameters' => [
                                ['type' => 'text', 'text' => $subject],
                                ['type' => 'text', 'text' => $word],
                                ['type' => 'text', 'text' => 'cliente'],
                                ['type' => 'text', 'text' => $entity->name . ' ' . $entity->surname],
                                ['type' => 'text', 'text' => $entity->date_slot],
                                ['type' => 'text', 'text' => $linkId],
                            ],
                        ],
                    ],
                ],
            ];

            if ($oldId) {
                $textPayload['context'] = ['message_id' => $oldId];
                $templatePayload['context'] = ['message_id' => $oldId];
            } else {
                Log::warning('(CustomerCancellationService) Invio WhatsApp senza message_id di contesto', [
                    'index' => $index,
                    'type' => $type,
                    'entity_id' => $entity->getKey(),
                    'whatsapp_message_id' => $entity->whatsapp_message_id,
                ]);
            }

            $url = 'https://graph.facebook.com/v24.0/' . config('configurazione.WA_ID') . '/messages';

            return $this->sendWhatsappContextMessageWithFallback(
                $url,
                (string) $number,
                $textPayload,
                $templatePayload,
                $this->isLastResponseWaWithin24Hours($index),
            );
        } catch (Exception $e) {
            Log::error('(CustomerCancellationService) Errore invio WhatsApp annullamento', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }

    private function sendWhatsappContextMessageWithFallback(
        string $url,
        string $number,
        array $textPayload,
        array $templatePayload,
        bool $preferText,
    ): mixed {
        $attempts = $preferText
            ? [
                ['type' => 'text', 'payload' => $textPayload],
                ['type' => 'template', 'payload' => $templatePayload],
            ]
            : [
                ['type' => 'template', 'payload' => $templatePayload],
                ['type' => 'text', 'payload' => $textPayload],
            ];

        foreach ($attempts as $attempt) {
            $response = Http::withHeaders([
                'Authorization' => config('configurazione.WA_TO'),
                'Content-Type' => 'application/json',
            ])->post($url, $attempt['payload']);

            if ($response->successful()) {
                Log::info('(CustomerCancellationService) Risposta WhatsApp inviata', [
                    'number' => $number,
                    'type' => $attempt['type'],
                    'response' => $response->json(),
                ]);

                return $response->json();
            }

            Log::error('(CustomerCancellationService) Invio WhatsApp fallito', [
                'number' => $number,
                'type' => $attempt['type'],
                'status' => $response->status(),
                'response' => $response->json(),
            ]);
        }

        return null;
    }

    private function isLastResponseWaWithin24Hours(int $index): bool
    {
        $setting = Setting::where('name', 'wa')->first();
        $property = $setting ? json_decode($setting->property, true) : null;

        if (! is_array($property)) {
            return false;
        }

        $lastResponseKey = $index === 0 ? 'last_response_wa_1' : 'last_response_wa_2';

        if (empty($property[$lastResponseKey])) {
            return false;
        }

        try {
            return Carbon::parse($property[$lastResponseKey])->greaterThanOrEqualTo(Carbon::now()->subHours(24));
        } catch (\Throwable) {
            return false;
        }
    }

    private function contactSettings(): array
    {
        $setting = Setting::where('name', 'Contatti')->firstOrFail();
        $property = json_decode($setting->property, true);

        return is_array($property) ? $property : [];
    }

    private function advancedSettings(): array
    {
        $setting = Setting::where('name', 'advanced')->first();
        $property = $setting ? json_decode($setting->property, true) : null;

        return is_array($property) ? $property : [];
    }

    private function decodeJsonArray(?string $value): array
    {
        if ($value === null || $value === '' || $value === '[]') {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? array_values($decoded) : [];
    }

    private function parseDateSlot(?string $dateSlot): ?Carbon
    {
        if (! is_string($dateSlot) || trim($dateSlot) === '') {
            return null;
        }

        try {
            return Carbon::createFromFormat('d/m/Y H:i', $dateSlot);
        } catch (\Throwable) {
            return null;
        }
    }

    private function isAlreadyCancelled(int $status): bool
    {
        return in_array($status, [0, 6], true);
    }

    private function isStatusCancellable(int $status, string $type): bool
    {
        if ($type === self::TYPE_ORDER) {
            return in_array($status, [1, 2, 3, 5], true);
        }

        return in_array($status, [1, 2, 3, 4, 5], true);
    }

    private function eligibilityResult(
        bool $allowed,
        ?string $reason,
        bool $alreadyCancelled = false,
        bool $withinGracePeriod = false,
        bool $sufficientNotice = false,
        ?Carbon $dateSlot = null,
    ): array {
        return [
            'allowed' => $allowed,
            'reason' => $reason,
            'already_cancelled' => $alreadyCancelled,
            'within_grace_period' => $withinGracePeriod,
            'sufficient_notice' => $sufficientNotice,
            'date_slot' => $dateSlot?->toISOString(),
        ];
    }
}
