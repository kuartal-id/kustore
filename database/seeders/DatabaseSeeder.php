<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Demo data is for local development and screenshots only. Never seed production.
        if (app()->environment('production')) {
            $this->command?->warn('Skipping demo seed in production.');

            return;
        }

        $this->call(DemoSeeder::class);
    }
}
