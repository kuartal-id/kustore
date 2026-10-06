<?php

namespace Database\Factories;

use App\Models\Store;
use App\Models\StoreLink;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<StoreLink> */
class StoreLinkFactory extends Factory
{
    protected $model = StoreLink::class;

    public function definition(): array
    {
        return [
            'store_id' => Store::factory(),
            'title' => 'Instagram',
            'url' => 'https://instagram.com/example',
            'icon' => 'instagram',
            'is_visible' => true,
            'position' => 1,
        ];
    }
}
