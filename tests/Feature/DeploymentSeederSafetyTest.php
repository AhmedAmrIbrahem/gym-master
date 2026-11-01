<?php

namespace Tests\Feature;

use App\Models\Exercise;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeploymentSeederSafetyTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_seeder_does_not_create_administrator_and_does_not_duplicate_exercises(): void
    {
        $this->seed(DatabaseSeeder::class);
        $count = Exercise::query()->whereNull('user_id')->count();

        $this->assertGreaterThan(0, $count);
        $this->assertDatabaseCount('users', 0);

        $this->seed(DatabaseSeeder::class);
        $this->assertSame($count, Exercise::query()->whereNull('user_id')->count());
        $this->assertDatabaseCount('users', 0);
    }
}
