<?php

namespace App\Http\Controllers\Dashboard;

use App\Enums\FulfillmentStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $store = $request->user()->store;
        $status = $request->query('status');

        $orders = $store->orders()
            ->when(in_array($status, PaymentStatus::values(), true), fn ($q) => $q->where('payment_status', $status))
            ->with('items')->latest()->paginate(20)->withQueryString();

        return view('dashboard.orders.index', ['store' => $store, 'orders' => $orders, 'status' => $status]);
    }

    public function show(Request $request, Order $order): View
    {
        $this->authorize('view', $order);

        return view('dashboard.orders.show', ['store' => $request->user()->store, 'order' => $order->load('items', 'payments')]);
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        $this->authorize('update', $order);

        $data = $request->validate([
            'payment_status' => ['required', Rule::enum(PaymentStatus::class)],
            'fulfillment_status' => ['required', Rule::enum(FulfillmentStatus::class)],
        ]);

        $order->fill($data);
        if ($data['payment_status'] === PaymentStatus::Paid->value && ! $order->paid_at) {
            $order->paid_at = now();
        }
        $order->save();

        $order->payments()->latest('id')->first()?->update(['status' => $data['payment_status']]);

        return back()->with('status', 'Order updated.');
    }
}
