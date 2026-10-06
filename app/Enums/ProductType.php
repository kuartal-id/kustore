<?php

namespace App\Enums;

enum ProductType: string
{
    case Physical = 'physical';
    case Digital = 'digital';
    case Service = 'service';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function requiresShippingByDefault(): bool
    {
        return $this === self::Physical;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
