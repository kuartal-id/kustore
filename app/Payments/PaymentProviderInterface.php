<?php

namespace App\Payments;

use App\Models\Order;

/**
 * Contract every payment provider implements. The active provider is chosen by
 * KUSTORE_PAYMENT_PROVIDER (config/kustore.php) and resolved by PaymentManager;
 * application code only ever depends on this interface.
 */
interface PaymentProviderInterface
{
    /** Short machine key stored on orders/payments, e.g. "manual". */
    public function key(): string;

    /** Human label shown in the dashboard. */
    public function label(): string;

    /**
     * Start payment for a freshly created (pending) order. Implementations
     * record a Payment row and return what the buyer should see next:
     * either instructions (manual transfer) or a redirect URL (gateways).
     */
    public function initiate(Order $order): PaymentResult;
}
