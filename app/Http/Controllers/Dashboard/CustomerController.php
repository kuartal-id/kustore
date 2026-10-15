<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $store = $request->user()->store;
        $this->authorize('view', $store);
        $search = trim((string) $request->query('q', ''));

        $customers = $store->customers()
            ->withCount('orders')
            ->withSum(['orders as paid_total' => fn ($q) => $q->where('payment_status', 'paid')], 'total')
            ->withMax('orders as last_order_at', 'created_at')
            ->when($search !== '', function ($q) use ($search) {
                $like = '%'.str_replace(['%', '_'], ['\%', '\_'], mb_strtolower($search)).'%';
                $q->where(fn ($w) => $w->whereRaw('LOWER(name) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(email) LIKE ?', [$like])
                    ->orWhere('phone', 'like', $like));
            })
            ->orderByDesc('last_order_at')
            ->paginate(25)
            ->withQueryString();

        return view('dashboard.customers.index', [
            'store' => $store,
            'customers' => $customers,
            'search' => $search,
            'totalCustomers' => $store->customers()->count(),
            'repeatCustomers' => $store->customers()->has('orders', '>=', 2)->count(),
        ]);
    }

    public function show(Request $request, Customer $customer): View
    {
        $store = $request->user()->store;
        abort_unless($customer->store_id === $store->id, 404);

        $orders = $customer->orders()->latest()->with('items')->get();

        return view('dashboard.customers.show', [
            'store' => $store,
            'customer' => $customer,
            'orders' => $orders,
            'paidTotal' => (int) $orders->filter(fn ($o) => $o->payment_status === \App\Enums\PaymentStatus::Paid)->sum('total'),
        ]);
    }

    /** CSV of the store's customers (owner only). */
    public function export(Request $request): StreamedResponse
    {
        $store = $request->user()->store;
        $this->authorize('view', $store);

        $rows = $store->customers()
            ->withCount('orders')
            ->withSum(['orders as paid_total' => fn ($q) => $q->where('payment_status', 'paid')], 'total')
            ->orderBy('name')
            ->get();

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Name', 'Email', 'Phone', 'Orders', 'Paid total', 'First seen']);
            foreach ($rows as $c) {
                fputcsv($out, array_map([self::class, 'csvSafe'], [
                    $c->name, $c->email, $c->phone, $c->orders_count, (int) $c->paid_total, $c->created_at?->toDateString(),
                ]));
            }
            fclose($out);
        }, 'kustore-customers-'.$store->username.'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** Stop spreadsheet formula injection from buyer-entered text. */
    public static function csvSafe(mixed $value): mixed
    {
        return is_string($value) && preg_match('/^[=+\-@\t\r]/', $value) ? "'".$value : $value;
    }
}
