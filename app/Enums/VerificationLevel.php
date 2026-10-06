<?php

namespace App\Enums;

enum VerificationLevel: string
{
    case Unverified = 'unverified';
    case KuartalId = 'kuartal_id';
    case Business = 'business';
    case Official = 'official';

    public function isVerified(): bool
    {
        return $this !== self::Unverified;
    }

    public function label(): string
    {
        return match ($this) {
            self::Unverified => 'Unverified',
            self::KuartalId => 'Verified with Kuartal ID',
            self::Business => 'Verified business',
            self::Official => 'Official',
        };
    }
}
