<?php

namespace App\Payments;

use App\Enums\PaymentStatus;
use App\Models\Order;

/**
 * Manual payments: the buyer pays the seller directly (bank transfer, e-wallet,
 * QRIS screenshot, cash...) following the seller's own instructions. The seller
 * then marks the order as paid in the dashboard.
 */
class ManualPaymentProvider implements PaymentProviderInterface
{
    public function key(): string
    {
        return 'manual';
    }

    public function label(): string
    {
        return 'Manual payment';
    }

    public function initiate(Order $order): PaymentResult
    {
        $instructions = trim((string) $order->store->payment_instructions)
            ?: 'The seller will contact you with payment details. Please keep your order number.';

        $order->payments()->create([
            'provider' => $this->key(),
            'provider_reference' => $order->number,
            'amount' => $order->total,
            'currency' => $order->currency,
            'status' => PaymentStatus::Pending->value,
            'payload' => ['instructions' => $instructions],
        ]);

        return new PaymentResult(PaymentStatus::Pending->value, $instructions, null, $order->number);
    }
}
