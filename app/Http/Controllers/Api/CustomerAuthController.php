<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Menu;
use App\Models\Order;
use App\Models\Product;
use App\Models\Reservation;
use App\Services\CustomerAuth\CustomerAccessService;
use App\Services\CustomerAuth\EmailOtpService;
use App\Services\CustomerAuth\CustomerProfileSettingsService;
use App\Services\CustomerAuth\VerifiedCheckoutSessionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Illuminate\Validation\ValidationException;

class CustomerAuthController extends Controller
{
    private const SUPPORTED_LANGS = ['it', 'en', 'es', 'de', 'fr', 'ja', 'ro'];

    public function __construct(
        private EmailOtpService $emailOtpService,
        private CustomerAccessService $customerAccessService,
        private CustomerProfileSettingsService $customerProfileSettingsService,
        private VerifiedCheckoutSessionService $verifiedCheckoutSessionService,
    ) {
    }

    public function sendOtp(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:100'],
            'intent' => ['nullable', 'in:account,checkout'],
        ]);

        $lang = $this->resolveLang($request);
        $email = Customer::normalizeEmail($data['email']);
        $intent = $data['intent'] ?? 'checkout';
        $customerExists = $this->customerAccessService->customerExists($email);
        $hasHistoricalEvidence = $this->customerAccessService->hasHistoricalEmailEvidence($email);

        if ($intent === 'checkout' && $hasHistoricalEvidence) {
            return response()->json([
                'success' => true,
                'message' => $this->message($lang, 'messages.otp_verified'),
                'otp_required' => false,
                'customer_exists' => $customerExists,
                'verified_session' => $this->verifiedCheckoutSessionService->issue($email),
            ]);
        }

        $otpPayload = $this->emailOtpService->send($email, $lang);

        return response()->json([
            'success' => true,
            'message' => $otpPayload['message'],
            'expires_in_minutes' => $otpPayload['expires_in_minutes'],
            'otp_required' => true,
            'customer_exists' => $customerExists,
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:100'],
            'code' => ['required', 'digits:6'],
            'name' => ['nullable', 'string', 'max:50'],
            'surname' => ['nullable', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:20'],
            'remember_details' => ['nullable', 'boolean'],
            'intent' => ['nullable', 'in:account,checkout'],
        ]);

        $lang = $this->resolveLang($request);
        $email = Customer::normalizeEmail($data['email']);

        $this->emailOtpService->verify($email, (string) $data['code'], $lang);

        $rememberDetails = $request->boolean('remember_details');
        $forceCustomerAccount = ($data['intent'] ?? 'checkout') === 'account';

        $access = $this->customerAccessService->completeVerifiedAccess(
            $email,
            [
                'name' => $data['name'] ?? null,
                'surname' => $data['surname'] ?? null,
                'phone' => $data['phone'] ?? null,
            ],
            $rememberDetails,
            $forceCustomerAccount,
        );

        return response()->json([
            'success' => true,
            'message' => $this->message($lang, 'messages.otp_verified'),
            'token' => $access['token'],
            'customer' => $access['customer'] ? $this->customerPayload($access['customer']) : null,
            'profile_settings' => $this->customerProfileSettingsService->get(),
            'customer_exists' => $access['customer_exists'],
            'created_customer' => $access['created_customer'],
            'verified_session' => $this->verifiedCheckoutSessionService->issue($email),
        ]);
    }

    public function me(Request $request)
    {
        $customer = $request->user();
        if (!$customer instanceof Customer) {
            abort(403);
        }

        return response()->json([
            'success' => true,
            'customer' => $this->customerPayload($customer),
            'profile_settings' => $this->customerProfileSettingsService->get(),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $customer = $request->user();
        if (!$customer instanceof Customer) {
            abort(403);
        }

        $settings = $this->customerProfileSettingsService->get();
        $questions = $settings['questions'] ?? [];

        $data = $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'surname' => ['required', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:20'],
            'gender' => ['required', 'string', 'max:20'],
            'birthday' => ['required', 'date_format:Y-m-d', 'after_or_equal:1900-01-01', 'before_or_equal:today'],
            'profile_answers' => ['nullable', 'array'],
            'marketing_enabled' => ['nullable', 'boolean'],
            'email_marketing_enabled' => ['nullable', 'boolean'],
            'whatsapp_marketing_enabled' => ['nullable', 'boolean'],
            'profiling_enabled' => ['nullable', 'boolean'],
            'tracking_enabled' => ['nullable', 'boolean'],
            'privacy_accepted' => ['nullable', 'boolean'],
            'privacy_version' => ['nullable', 'string', 'max:50'],
        ]);

        $customer->name = trim((string) $data['name']);
        $customer->surname = trim((string) $data['surname']);
        $customer->phone = $this->nullableTrimmed($data['phone'] ?? null);
        $customer->gender = trim((string) $data['gender']);
        $customer->birthday = $data['birthday'];
        $customer->profile_answers = $this->customerProfileSettingsService->normalizeAnswers(
            $data['profile_answers'] ?? [],
            $questions
        );

        if (!$this->customerProfileSettingsService->isRegistrationComplete($customer, $questions)) {
            throw ValidationException::withMessages([
                'profile' => [$this->message($this->resolveLang($request), 'messages.registration_incomplete')],
            ]);
        }

        $this->applyAccountConsentPreferences($customer, $data);

        $customer->registered_at = $customer->registered_at ?: now();
        $customer->save();

        return response()->json([
            'success' => true,
            'message' => $this->message($this->resolveLang($request), 'messages.registration_completed'),
            'customer' => $this->customerPayload($customer->fresh()),
        ]);
    }

    public function updateConsents(Request $request)
    {
        $customer = $request->user();
        if (!$customer instanceof Customer) {
            abort(403);
        }

        $data = $request->validate([
            'marketing_enabled' => ['nullable', 'boolean'],
            'email_marketing_enabled' => ['nullable', 'boolean'],
            'whatsapp_marketing_enabled' => ['nullable', 'boolean'],
            'profiling_enabled' => ['nullable', 'boolean'],
            'tracking_enabled' => ['nullable', 'boolean'],
            'privacy_accepted' => ['nullable', 'boolean'],
            'privacy_version' => ['nullable', 'string', 'max:50'],
        ]);

        $this->applyAccountConsentPreferences($customer, $data);

        $customer->save();

        return response()->json([
            'success' => true,
            'message' => $this->message($this->resolveLang($request), 'messages.consents_updated'),
            'customer' => $this->customerPayload($customer->fresh()),
        ]);
    }

    public function history(Request $request)
    {
        $customer = $request->user();
        if (!$customer instanceof Customer) {
            abort(403);
        }

        $lang = $this->resolveLang($request);
        app()->setLocale($lang);

        $orders = $customer->orders()
            ->with(['products', 'menus.products'])
            ->orderByRaw("COALESCE(STR_TO_DATE(date_slot, '%d/%m/%Y %H:%i'), created_at) DESC")
            ->get()
            ->map(fn (Order $order) => $this->transformOrderHistory($order))
            ->values();

        $reservations = $customer->reservations()
            ->orderByRaw("COALESCE(STR_TO_DATE(date_slot, '%d/%m/%Y %H:%i'), created_at) DESC")
            ->get()
            ->map(fn (Reservation $reservation) => $this->transformReservationHistory($reservation))
            ->values();

        return response()->json([
            'success' => true,
            'summary' => [
                'orders_count' => $orders->count(),
                'reservations_count' => $reservations->count(),
                'total_spent_cents' => (float) $customer->orders()
                    ->whereNotIn('status', [0, 6])
                    ->sum('tot_price'),
            ],
            'orders' => $orders,
            'reservations' => $reservations,
        ]);
    }

    public function logout(Request $request)
    {
        $customer = $request->user();
        if (!$customer instanceof Customer) {
            abort(403);
        }

        $token = $customer->currentAccessToken();

        if ($token) {
            $token->delete();
        }

        return response()->json([
            'success' => true,
            'message' => $this->message($this->resolveLang($request), 'messages.logout_completed'),
        ]);
    }

    private function resolveLang(Request $request): string
    {
        $lang = $request->input('lang') ?? $request->input('lango') ?? config('app.locale');
        $lang = is_string($lang) ? mb_strtolower(trim($lang)) : config('app.locale');

        return in_array($lang, self::SUPPORTED_LANGS, true) ? $lang : config('app.locale');
    }

    private function message(string $lang, string $key, array $replace = []): string
    {
        return Lang::get('customer.' . $key, $replace, $lang);
    }

    private function customerPayload(Customer $customer): array
    {
        return $customer->toApiPayload();
    }

    private function applyAccountConsentPreferences(Customer $customer, array $data): void
    {
        $payload = $this->normalizeAccountConsentPayload($data);
        $now = now();

        if (array_key_exists('email_marketing_enabled', $payload)) {
            if ($payload['email_marketing_enabled']) {
                $customer->email_marketing_consent_at = $now;
            } else {
                $customer->email_marketing_consent_at = null;
            }
        }

        if (array_key_exists('whatsapp_marketing_enabled', $payload)) {
            $customer->whatsapp_marketing_consent_at = $payload['whatsapp_marketing_enabled']
                ? $now
                : null;
        }

        if (array_key_exists('profiling_enabled', $payload)) {
            $customer->profiling_consent_at = $payload['profiling_enabled']
                ? $now
                : null;
        }

        if (array_key_exists('tracking_enabled', $payload)) {
            $customer->tracking_consent_at = $payload['tracking_enabled']
                ? $now
                : null;
        }

        if (($payload['privacy_accepted'] ?? null) === true) {
            $customer->privacy_accepted_at = $now;
            $customer->privacy_accepted_version = $this->privacyVersionFromPayload($payload);
        }

        if ($customer->isDirty([
            'email_marketing_consent_at',
            'whatsapp_marketing_consent_at',
            'profiling_consent_at',
            'tracking_consent_at',
            'privacy_accepted_at',
            'privacy_accepted_version',
        ])) {
            $customer->consents_updated_at = $now;
        }
    }

    private function normalizeAccountConsentPayload(array $data): array
    {
        if (
            (!array_key_exists('email_marketing_enabled', $data) || $data['email_marketing_enabled'] === null)
            && array_key_exists('marketing_enabled', $data)
            && $data['marketing_enabled'] !== null
        ) {
            $data['email_marketing_enabled'] = $data['marketing_enabled'];
        }

        $payload = [];
        foreach ([
            'email_marketing_enabled',
            'whatsapp_marketing_enabled',
            'profiling_enabled',
            'tracking_enabled',
            'privacy_accepted',
        ] as $key) {
            $value = $this->nullableBoolean($data, $key);
            if ($value !== null) {
                $payload[$key] = $value;
            }
        }

        if (array_key_exists('privacy_version', $data)) {
            $payload['privacy_version'] = $data['privacy_version'];
        }

        return $payload;
    }

    private function nullableBoolean(array $data, string $key): ?bool
    {
        if (!array_key_exists($key, $data) || $data[$key] === null) {
            return null;
        }

        return filter_var($data[$key], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }

    private function privacyVersionFromPayload(array $payload): string
    {
        $version = $payload['privacy_version'] ?? null;

        if (is_string($version)) {
            $version = trim($version);
        }

        return $version !== null && $version !== '' ? (string) $version : 'default';
    }

    private function nullableTrimmed($value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed !== '' ? $trimmed : null;
    }

    private function transformOrderHistory(Order $order): array
    {
        return [
            'id' => $order->id,
            'type' => 'order',
            'reference' => 'ORD-' . str_pad((string) $order->id, 6, '0', STR_PAD_LEFT),
            'date_slot' => $order->date_slot,
            'activity_at' => $this->toHistoryTimestamp($order->date_slot, $order->created_at),
            'status' => $this->orderStatusPayload((int) $order->status),
            'total_price_cents' => (float) $order->tot_price,
            'is_delivery' => filled($order->comune),
            'delivery_address' => $this->formatDeliveryAddress($order),
            'message' => $order->message,
            'items' => $this->transformOrderItems($order),
            'created_at' => $order->created_at?->toISOString(),
        ];
    }

    private function transformReservationHistory(Reservation $reservation): array
    {
        $guests = $this->parseGuests($reservation->n_person);

        return [
            'id' => $reservation->id,
            'type' => 'reservation',
            'reference' => 'RES-' . str_pad((string) $reservation->id, 6, '0', STR_PAD_LEFT),
            'date_slot' => $reservation->date_slot,
            'activity_at' => $this->toHistoryTimestamp($reservation->date_slot, $reservation->created_at),
            'status' => $this->reservationStatusPayload((int) $reservation->status),
            'message' => $reservation->message,
            'guests' => $guests,
            'sala' => $reservation->sala,
            'created_at' => $reservation->created_at?->toISOString(),
        ];
    }

    private function transformOrderItems(Order $order): array
    {
        $items = [];

        foreach ($order->menus as $menu) {
            $items[] = [
                'kind' => 'menu',
                'name' => $menu->name,
                'quantity' => (int) $menu->pivot->quantity,
                'choices' => $this->extractMenuChoices($menu),
            ];
        }

        foreach ($order->products as $product) {
            $items[] = [
                'kind' => 'product',
                'name' => $product->name,
                'quantity' => (int) $product->pivot->quantity,
                'options' => $this->decodeJsonArray($product->pivot->option),
                'add' => $this->decodeJsonArray($product->pivot->add),
                'remove' => $this->decodeJsonArray($product->pivot->remove),
            ];
        }

        return $items;
    }

    private function extractMenuChoices(Menu $menu): array
    {
        if ($menu->pivot->choices === '1' || blank($menu->pivot->choices)) {
            return [];
        }

        $choiceIds = json_decode((string) $menu->pivot->choices, true);
        if (!is_array($choiceIds) || $choiceIds === []) {
            return [];
        }

        return $menu->products
            ->whereIn('id', $choiceIds)
            ->map(fn (Product $product) => $product->name)
            ->values()
            ->all();
    }

    private function decodeJsonArray(?string $value): array
    {
        if (blank($value)) {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? array_values($decoded) : [];
    }

    private function parseGuests(?string $value): array
    {
        $decoded = json_decode((string) $value, true);
        $adult = isset($decoded['adult']) ? (int) $decoded['adult'] : 0;
        $child = isset($decoded['child']) ? (int) $decoded['child'] : 0;

        return [
            'adult' => $adult,
            'child' => $child,
            'total' => $adult + $child,
        ];
    }

    private function toHistoryTimestamp(?string $dateSlot, $createdAt): ?string
    {
        if (is_string($dateSlot) && $dateSlot !== '') {
            try {
                return Carbon::createFromFormat('d/m/Y H:i', $dateSlot)->toISOString();
            } catch (\Throwable $exception) {
            }
        }

        return $createdAt?->toISOString();
    }

    private function formatDeliveryAddress(Order $order): ?string
    {
        if (!filled($order->comune)) {
            return null;
        }

        return trim(implode(', ', array_filter([
            trim(implode(' ', array_filter([$order->address, $order->address_n]))),
            $order->comune,
        ])));
    }

    private function orderStatusPayload(int $status): array
    {
        return match ($status) {
            0 => ['code' => 0, 'key' => 'cancelled', 'group' => 'cancelled'],
            1 => ['code' => 1, 'key' => 'confirmed', 'group' => 'confirmed'],
            2 => ['code' => 2, 'key' => 'pending', 'group' => 'pending'],
            3 => ['code' => 3, 'key' => 'paid_pending_confirmation', 'group' => 'pending'],
            4 => ['code' => 4, 'key' => 'awaiting_payment', 'group' => 'pending'],
            5 => ['code' => 5, 'key' => 'confirmed_paid', 'group' => 'confirmed'],
            6 => ['code' => 6, 'key' => 'refunded', 'group' => 'cancelled'],
            default => ['code' => $status, 'key' => 'unknown', 'group' => 'pending'],
        };
    }

    private function reservationStatusPayload(int $status): array
    {
        return match ($status) {
            0, 6 => ['code' => $status, 'key' => 'cancelled', 'group' => 'cancelled'],
            1, 5 => ['code' => $status, 'key' => 'confirmed', 'group' => 'confirmed'],
            2, 3, 4 => ['code' => $status, 'key' => 'pending', 'group' => 'pending'],
            default => ['code' => $status, 'key' => 'unknown', 'group' => 'pending'],
        };
    }
}
