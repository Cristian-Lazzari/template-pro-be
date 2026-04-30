<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Model as MailModel;
use App\Models\Order;
use App\Models\Reservation;
use App\Services\Crm\CustomerSegmentService;
use App\Services\CustomerAuth\CustomerProfileSettingsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CustomerController extends Controller
{
    public function __construct(
        private CustomerProfileSettingsService $customerProfileSettingsService,
    ) {}

    public function index(Request $request, CustomerSegmentService $customerSegmentService)
    {
        $filters = [
            'search' => trim((string) $request->query('search', '')),
            'type' => trim((string) $request->query('type', 'all')),
            'segment' => trim((string) $request->query('segment', '')),
        ];

        if (! in_array($filters['type'], ['all', 'orders', 'reservations', 'both'], true)) {
            $filters['type'] = 'all';
        }

        $allCustomers = $customerSegmentService->buildBaseCustomerQuery();
        $filteredCustomers = $filters['segment'] !== ''
            ? $customerSegmentService->getCustomersForSegment($filters['segment'], $filters)
            : $customerSegmentService->buildBaseCustomerQuery($filters);
        $customers = $this->paginateCustomers($filteredCustomers, $request);

        $stats = [
            'total' => $allCustomers->count(),
            'with_orders' => $allCustomers->where('orders_count', '>', 0)->count(),
            'with_reservations' => $allCustomers->where('reservations_count', '>', 0)->count(),
            'with_both' => $allCustomers->filter(function ($customer) {
                return $customer->orders_count > 0 && $customer->reservations_count > 0;
            })->count(),
        ];

        $profileSettings = $this->customerProfileSettingsService->get();
        $mailModels = MailModel::query()
            ->orderByDesc('id')
            ->get();
        $segmentOptions = $customerSegmentService->getSegments();

        if ($request->ajax()) {
            return response()->view('admin.Customers.partials.results', compact('customers'));
        }

        return view('admin.Customers.index', compact(
            'customers',
            'stats',
            'profileSettings',
            'mailModels',
            'segmentOptions',
            'filters',
        ));
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
        if (! is_array($questions)) {
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

    private function paginateCustomers(Collection $customers, Request $request): LengthAwarePaginator
    {
        $perPage = max(1, (int) config('crm.customer_list_per_page', 15));
        $total = $customers->count();
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page = min(max(1, (int) $request->query('page', 1)), $lastPage);
        $items = $customers->forPage($page, $perPage)->values();

        return (new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            ['path' => route('admin.customers.index')]
        ))->appends($request->query());
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
                if (! $key) {
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
