<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Store;
use App\Payments\PaymentManager;
use App\Services\ImageUploader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * How the seller gets paid. Today: manual payments (their own instructions, QRIS image and
 * WhatsApp number). Gateways plug in later through PaymentManager / config('kustore.payment_providers').
 */
class PaymentController extends Controller
{
    public function edit(Request $request, PaymentManager $payments): View
    {
        $store = $request->user()->store;
        $this->authorize('view', $store);

        $monthStart = now()->startOfMonth();

        return view('dashboard.payments', [
            'store' => $store,
            'provider' => $payments->provider(),
            'recent' => Payment::query()
                ->whereHas('order', fn ($q) => $q->where('store_id', $store->id))
                ->with('order:id,store_id,number,customer_name,payment_status')
                ->latest()->limit(20)->get(),
            'paidThisMonth' => (int) $store->orders()->paid()->where('paid_at', '>=', $monthStart)->sum('total'),
            'awaiting' => (int) $store->orders()->where('payment_status', 'pending')->sum('total'),
            'awaitingCount' => $store->orders()->where('payment_status', 'pending')->count(),
        ]);
    }

    public function update(Request $request, ImageUploader $uploader): RedirectResponse
    {
        $store = $request->user()->store;
        $this->authorize('update', $store);

        $data = $request->validate([
            'payment_instructions' => ['nullable', 'string', 'max:2000'],
            'whatsapp_number' => ['nullable', 'string', 'max:25', 'regex:/^[0-9+()\s-]{8,25}$/'],
            'notify_new_orders' => ['nullable', 'boolean'],
            'qris' => ['nullable', 'file', ...ImageUploader::rules()],
            'remove_qris' => ['nullable', 'boolean'],
        ], ['whatsapp_number.regex' => 'Enter a phone number like 0812 3456 7890 or +62 812 3456 7890.']);

        if ($request->hasFile('qris')) {
            $uploader->delete($store->payment_qris_path);
            $store->payment_qris_path = $uploader->store($request->file('qris'), 'qris');
        } elseif ($request->boolean('remove_qris')) {
            $uploader->delete($store->payment_qris_path);
            $store->payment_qris_path = null;
        }

        $store->forceFill([
            'payment_instructions' => $data['payment_instructions'] ?? null,
            'whatsapp_number' => Store::normaliseWhatsapp($data['whatsapp_number'] ?? null),
            'notify_new_orders' => $request->boolean('notify_new_orders'),
        ])->save();

        return back()->with('status', 'Payment settings saved.');
    }
}
