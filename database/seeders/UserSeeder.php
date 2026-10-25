<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Backward-compatible name for the explicit, environment-configured
 * administrator seeder. Never embed known administrator credentials.
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(SuperAdminSeeder::class);
    }
}
