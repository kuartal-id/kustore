<?php

namespace App\Payments;

final class PaymentResult
{
    public function __construct(
        public readonly string $status,
        public readonly ?string $instructions = null,
        public readonly ?string $redirectUrl = null,
        public readonly ?string $reference = null,
    ) {}
}
