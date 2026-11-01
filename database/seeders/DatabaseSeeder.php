<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Populate the shared exercise catalog only. Administrator creation
        // must be an explicit, one-time operator action (see deployment notes).
        $this->call([ExerciseSeeder::class]);
    }
}
