<?php

namespace Tests\Unit;

use App\Support\Money;
use Tests\TestCase;

class MoneyTest extends TestCase
{
    public function test_idr_is_formatted_without_decimals(): void
    {
        $this->assertSame('Rp 150.000', Money::format(150000, 'IDR'));
    }

    public function test_idr_input_parsing_ignores_separators(): void
    {
        $this->assertSame(150000, Money::parse('150.000', 'IDR'));
        $this->assertSame(150000, Money::parse('Rp 150,000', 'IDR'));
        $this->assertNull(Money::parse('', 'IDR'));
    }
}
