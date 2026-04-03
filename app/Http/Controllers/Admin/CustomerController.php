<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    public function index()
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

        $linkedStats = DB::query()
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
                $customer->is_registered = true;
                $customer->detail_url = null;

                if ($customer->last_source_id) {
                    $customer->detail_url = $customer->last_source === 'reservation'
                        ? route('admin.reservations.show', $customer->last_source_id)
                        : route('admin.orders.show', $customer->last_source_id);
                }

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

                $customer->detail_url = null;
                if ($customer->last_source_id) {
                    $customer->detail_url = $customer->last_source === 'reservation'
                        ? route('admin.reservations.show', $customer->last_source_id)
                        : route('admin.orders.show', $customer->last_source_id);
                }

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

        return view('admin.Customers.index', compact('customers', 'stats'));
    }
}
