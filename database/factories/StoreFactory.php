<?php

namespace Database\Factories;

use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Store> */
class StoreFactory extends Factory
{
    protected $model = Store::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'username' => strtolower(fake()->unique()->bothify('store-####??')),
            'display_name' => fake()->company(),
            'bio' => fake()->sentence(12),
            'account_type' => 'individual',
            'layout' => 'minimal',
            'color_mode' => 'light',
            'currency' => 'IDR',
            'payment_instructions' => 'Transfer to BCA 1234567890.',
        ];
    }

    public function published(): static
    {
        return $this->afterMaking(function (Store $store) {
            $store->is_published = true;
            $store->published_at = now();
        });
    }

    public function suspended(): static
    {
        return $this->afterMaking(fn (Store $store) => $store->is_suspended = true);
    }
}
