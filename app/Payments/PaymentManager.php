<?php

namespace App\Payments;

use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;

class PaymentManager
{
    public function __construct(private Container $container) {}

    public function provider(?string $key = null): PaymentProviderInterface
    {
        $key ??= (string) config('kustore.payment_provider', 'manual');
        $class = config("kustore.payment_providers.$key");

        if (! $class || ! is_subclass_of($class, PaymentProviderInterface::class)) {
            throw new InvalidArgumentException("Unknown payment provider [$key].");
        }

        return $this->container->make($class);
    }
}
