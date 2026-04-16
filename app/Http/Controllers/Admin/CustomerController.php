<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Reservation;
use App\Services\CustomerAuth\CustomerProfileSettingsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    public function __construct(
        private CustomerProfileSettingsService $customerProfileSettingsService,
    ) {
    }

    public function index()
    {
        $linkedStats = $this->linkedStatsByCustomerId();

        $registeredCustomers = Customer::query()
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($customer) use ($linkedStats) {
                $stats = $linkedStats->get($customer->id);

                $customer->orders_count = (int) ($stats->orders_count ?? 0);
                $customer->reservations_count = (int) ($stats->reservations_count ?? 0);
                $customer->interactions_count = (int) ($stats->interactions_count ?? 0);
                $customer->last_source = $stats->last_source ?? null;
                $customer->last_source_id = isset($stats->last_source_id) ? (int) $stats->last_source_id : null;
                $customer->last_activity_at = isset($stats->last_activity_at)
                    ? Carbon::parse($stats->last_activity_at)
                    : ($customer->created_at ? Carbon::parse($customer->created_at) : null);
                $customer->is_registered = $customer->isRegistered();
                $customer->account_state = $customer->isRegistered() ? 'registered' : 'guest';
                $customer->marketing_state = $customer->marketingState();
                $customer->detail_url = route('admin.customers.show', $customer);

                $customer->search_text = mb_strtolower(trim(implode(' ', array_filter([
                    $customer->name,
                    $customer->surname,
                    $customer->email,
                    $customer->phone,
                ]))));

                return $customer;
            });

        $orderEvents = Order::query()
            ->selectRaw("
                LOWER(TRIM(email)) as email_key,
                LOWER(TRIM(name)) as name_key,
                LOWER(TRIM(surname)) as surname_key,
                TRIM(email) as email,
                TRIM(name) as name,
                TRIM(surname) as surname,
                NULLIF(TRIM(phone), '') as phone,
                news_letter,
                COALESCE(STR_TO_DATE(date_slot, '%d/%m/%Y %H:%i'), created_at) as activity_at,
                'order' as source,
                id as source_id
            ")
            ->whereNotNull('email')
            ->whereRaw("TRIM(email) <> ''")
            ->whereNull('customer_id');

        $reservationEvents = Reservation::query()
            ->selectRaw("
                LOWER(TRIM(email)) as email_key,
                LOWER(TRIM(name)) as name_key,
                LOWER(TRIM(surname)) as surname_key,
                TRIM(email) as email,
                TRIM(name) as name,
                TRIM(surname) as surname,
                NULLIF(TRIM(phone), '') as phone,
                news_letter,
                COALESCE(STR_TO_DATE(date_slot, '%d/%m/%Y %H:%i'), created_at) as activity_at,
                'reservation' as source,
                id as source_id
            ")
            ->whereNotNull('email')
            ->whereRaw("TRIM(email) <> ''")
            ->whereNull('customer_id');

        $guestCustomers = DB::query()
            ->fromSub($orderEvents->unionAll($reservationEvents), 'customer_events')
            ->selectRaw("
                SUBSTRING_INDEX(GROUP_CONCAT(email ORDER BY activity_at DESC SEPARATOR '||'), '||', 1) as email,
                SUBSTRING_INDEX(GROUP_CONCAT(name ORDER BY activity_at DESC SEPARATOR '||'), '||', 1) as name,
                SUBSTRING_INDEX(GROUP_CONCAT(surname ORDER BY activity_at DESC SEPARATOR '||'), '||', 1) as surname,
                SUBSTRING_INDEX(GROUP_CONCAT(phone ORDER BY activity_at DESC SEPARATOR '||'), '||', 1) as phone,
                SUM(CASE WHEN source = 'order' THEN 1 ELSE 0 END) as orders_count,
                SUM(CASE WHEN source = 'reservation' THEN 1 ELSE 0 END) as reservations_count,
                MAX(CASE WHEN news_letter = 1 THEN 1 ELSE 0 END) as marketing_opt_in,
                COUNT(*) as interactions_count,
                MAX(activity_at) as last_activity_at,
                SUBSTRING_INDEX(GROUP_CONCAT(source ORDER BY activity_at DESC SEPARATOR ','), ',', 1) as last_source,
                SUBSTRING_INDEX(GROUP_CONCAT(source_id ORDER BY activity_at DESC SEPARATOR ','), ',', 1) as last_source_id
            ")
            ->groupBy('email_key', 'name_key', 'surname_key')
            ->orderByDesc('last_activity_at')
            ->get()
            ->map(function ($customer) {
                $customer->orders_count = (int) $customer->orders_count;
                $customer->reservations_count = (int) $customer->reservations_count;
                $customer->interactions_count = (int) $customer->interactions_count;
                $customer->last_source_id = $customer->last_source_id ? (int) $customer->last_source_id : null;
                $customer->last_activity_at = $customer->last_activity_at
                    ? Carbon::parse($customer->last_activity_at)
                    : null;
                $customer->is_registered = false;
                $customer->account_state = 'guest';
                $customer->marketing_state = ((int) ($customer->marketing_opt_in ?? 0)) === 1
                    ? 'soft_marketing'
                    : 'no_marketing';
                $customer->detail_url = route('admin.customers.show_guest', ['email' => $customer->email]);

                $customer->search_text = mb_strtolower(trim(implode(' ', array_filter([
                    $customer->name,
                    $customer->surname,
                    $customer->email,
                    $customer->phone,
                ]))));

                return $customer;
            })
            ->values();

        $customers = $registeredCustomers
            ->concat($guestCustomers)
            ->sortByDesc(function ($customer) {
                return $customer->last_activity_at?->timestamp ?? 0;
            })
            ->values();

        $stats = [
            'total' => $customers->count(),
            'with_orders' => $customers->where('orders_count', '>', 0)->count(),
            'with_reservations' => $customers->where('reservations_count', '>', 0)->count(),
            'with_both' => $customers->filter(function ($customer) {
                return $customer->orders_count > 0 && $customer->reservations_count > 0;
            })->count(),
        ];

        $profileSettings = $this->customerProfileSettingsService->get();

        return view('admin.Customers.index', compact('customers', 'stats', 'profileSettings'));
    }

    public function show(Customer $customer)
    {
        $payload = $this->buildCustomerShowPayload($customer);

        return view('admin.Customers.show', $payload);
    }

    public function showGuest(string $email)
    {
        $normalizedEmail = Customer::normalizeEmail($email);
        $customer = Customer::query()
            ->whereRaw('LOWER(email) = ?', [$normalizedEmail])
            ->first();

        if ($customer) {
            return redirect()->route('admin.customers.show', $customer);
        }

        $payload = $this->buildCustomerShowPayload(null, $normalizedEmail);

        abort_if(
            $payload['orders']->isEmpty() && $payload['reservations']->isEmpty(),
            404
        );

        return view('admin.Customers.show', $payload);
    }

    public function updateProfileSettings(Request $request)
    {
        $questions = $request->input('questions', []);
        if (!is_array($questions)) {
            $questions = [];
        }

        $this->customerProfileSettingsService->update([
            'marketing_consent_text' => $request->input('marketing_consent_text'),
            'profiling_consent_text' => $request->input('profiling_consent_text'),
            'questions' => $questions,
        ]);

        return redirect()
            ->route('admin.customers.index')
            ->with('success', 'Profilo cliente aggiornato correttamente.');
    }

    private function linkedStatsByCustomerId()
    {
        $linkedOrderEvents = Order::query()
            ->selectRaw("
                customer_id,
                COALESCE(STR_TO_DATE(date_slot, '%d/%m/%Y %H:%i'), created_at) as activity_at,
                'order' as source,
                id as source_id
            ")
            ->whereNotNull('customer_id');

        $linkedReservationEvents = Reservation::query()
            ->selectRaw("
                customer_id,
                COALESCE(STR_TO_DATE(date_slot, '%d/%m/%Y %H:%i'), created_at) as activity_at,
                'reservation' as source,
                id as source_id
            ")
            ->whereNotNull('customer_id');

        return DB::query()
            ->fromSub($linkedOrderEvents->unionAll($linkedReservationEvents), 'customer_events')
            ->selectRaw("
                customer_id,
                SUM(CASE WHEN source = 'order' THEN 1 ELSE 0 END) as orders_count,
                SUM(CASE WHEN source = 'reservation' THEN 1 ELSE 0 END) as reservations_count,
                COUNT(*) as interactions_count,
                MAX(activity_at) as last_activity_at,
                SUBSTRING_INDEX(GROUP_CONCAT(source ORDER BY activity_at DESC SEPARATOR ','), ',', 1) as last_source,
                SUBSTRING_INDEX(GROUP_CONCAT(source_id ORDER BY activity_at DESC SEPARATOR ','), ',', 1) as last_source_id
            ")
            ->groupBy('customer_id')
            ->get()
            ->keyBy('customer_id');
    }

    private function buildCustomerShowPayload(?Customer $customer, ?string $email = null): array
    {
        $profileSettings = $this->customerProfileSettingsService->get();
        $normalizedEmail = $customer
            ? Customer::normalizeEmail($customer->email)
            : Customer::normalizeEmail((string) $email);

        $orders = Order::query()
            ->where(function ($query) use ($customer, $normalizedEmail) {
                if ($customer) {
                    $query->where('customer_id', $customer->id)
                        ->orWhereRaw('LOWER(TRIM(email)) = ?', [$normalizedEmail]);
                    return;
                }

                $query->whereRaw('LOWER(TRIM(email)) = ?', [$normalizedEmail]);
            })
            ->orderByRaw("COALESCE(STR_TO_DATE(date_slot, '%d/%m/%Y %H:%i'), created_at) DESC")
            ->get()
            ->each(function ($order) {
                $order->activity_at = $this->activityTimestamp($order->date_slot, $order->created_at);
            })
            ->values();

        $reservations = Reservation::query()
            ->where(function ($query) use ($customer, $normalizedEmail) {
                if ($customer) {
                    $query->where('customer_id', $customer->id)
                        ->orWhereRaw('LOWER(TRIM(email)) = ?', [$normalizedEmail]);
                    return;
                }

                $query->whereRaw('LOWER(TRIM(email)) = ?', [$normalizedEmail]);
            })
            ->orderByRaw("COALESCE(STR_TO_DATE(date_slot, '%d/%m/%Y %H:%i'), created_at) DESC")
            ->get()
            ->each(function ($reservation) {
                $reservation->activity_at = $this->activityTimestamp($reservation->date_slot, $reservation->created_at);
            })
            ->values();

        $latestActivity = collect([$orders->first()?->activity_at, $reservations->first()?->activity_at])
            ->filter()
            ->sortByDesc(fn ($value) => $value->timestamp)
            ->first();

        $lastKnownProfile = $this->lastKnownGuestProfile($normalizedEmail);
        $questionAnswers = [];

        if ($customer) {
            foreach (($profileSettings['questions'] ?? []) as $question) {
                $key = $question['key'] ?? null;
                if (!$key) {
                    continue;
                }

                $value = $customer->profile_answers[$key] ?? null;
                if ($value === null || $value === '') {
                    continue;
                }

                $questionAnswers[] = [
                    'label' => $question['label'] ?? $key,
                    'value' => $value,
                ];
            }
        }

        $customerView = $customer ?: (object) [
            'id' => null,
            'name' => $lastKnownProfile['name'] ?? '',
            'surname' => $lastKnownProfile['surname'] ?? '',
            'email' => $lastKnownProfile['email'] ?? $normalizedEmail,
            'phone' => $lastKnownProfile['phone'] ?? null,
            'gender' => null,
            'age' => null,
            'registered_at' => null,
            'email_verified_at' => null,
            'created_at' => $latestActivity,
            'marketing_consent_at' => ($lastKnownProfile['marketing_opt_in'] ?? false) ? $latestActivity : null,
            'profiling_consent_at' => null,
            'profile_answers' => [],
        ];

        $accountState = $customer ? ($customer->isRegistered() ? 'registered' : 'guest') : 'guest';
        $marketingState = $customer
            ? $customer->marketingState()
            : (($lastKnownProfile['marketing_opt_in'] ?? false) ? 'soft_marketing' : 'no_marketing');

        $stats = [
            'orders_count' => $orders->count(),
            'reservations_count' => $reservations->count(),
            'interactions_count' => $orders->count() + $reservations->count(),
            'total_spent_cents' => (float) $orders
                ->whereNotIn('status', [0, 6])
                ->sum('tot_price'),
            'latest_activity_at' => $latestActivity,
        ];

        return [
            'customer' => $customerView,
            'orders' => $orders,
            'reservations' => $reservations,
            'stats' => $stats,
            'questionAnswers' => $questionAnswers,
            'profileSettings' => $profileSettings,
            'accountState' => $accountState,
            'marketingState' => $marketingState,
            'hasCustomerRecord' => $customer !== null,
        ];
    }

    private function lastKnownGuestProfile(string $normalizedEmail): array
    {
        $orders = Order::query()
            ->select(['name', 'surname', 'email', 'phone', 'news_letter', 'created_at'])
            ->whereRaw('LOWER(TRIM(email)) = ?', [$normalizedEmail])
            ->orderByDesc('created_at')
            ->get();

        $reservations = Reservation::query()
            ->select(['name', 'surname', 'email', 'phone', 'news_letter', 'created_at'])
            ->whereRaw('LOWER(TRIM(email)) = ?', [$normalizedEmail])
            ->orderByDesc('created_at')
            ->get();

        $latest = collect([$orders->first(), $reservations->first()])
            ->filter()
            ->sortByDesc(fn ($item) => Carbon::parse($item->created_at)->timestamp)
            ->first();

        return [
            'name' => trim((string) ($latest?->name ?? '')),
            'surname' => trim((string) ($latest?->surname ?? '')),
            'email' => trim((string) ($latest?->email ?? $normalizedEmail)),
            'phone' => $latest?->phone,
            'marketing_opt_in' => $orders->contains(fn ($item) => (bool) $item->news_letter)
                || $reservations->contains(fn ($item) => (bool) $item->news_letter),
        ];
    }

    private function activityTimestamp(?string $dateSlot, $createdAt): ?Carbon
    {
        if (is_string($dateSlot) && trim($dateSlot) !== '') {
            try {
                return Carbon::createFromFormat('d/m/Y H:i', trim($dateSlot));
            } catch (\Throwable $exception) {
            }
        }

        return $createdAt ? Carbon::parse($createdAt) : null;
    }
}
