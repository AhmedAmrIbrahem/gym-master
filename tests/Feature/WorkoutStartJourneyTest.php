<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WorkoutProgram;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkoutStartJourneyTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_without_active_program_offers_program_setup(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('أنشئ أو فعّل نظامًا تدريبيًا');
    }

    public function test_dashboard_exposes_day_selector_and_valid_day_opens_workout_form(): void
    {
        $user = User::factory()->create();
        $program = WorkoutProgram::create([
            'user_id' => $user->id,
            'name' => 'My workout program',
            'is_active' => true,
        ]);
        $day = $program->days()->create(['name' => 'Push day']);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('data-open-workout-selector', false)
            ->assertSee('today-workout-selector', false)
            ->assertSee(route('workout.start', $day), false);

        $this->actingAs($user)
            ->get(route('workout.start', $day))
            ->assertOk()
            ->assertSee('Push day');
    }

    public function test_member_cannot_open_another_members_workout_day(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $program = WorkoutProgram::create([
            'user_id' => $owner->id,
            'name' => 'Private workout',
            'is_active' => true,
        ]);
        $day = $program->days()->create(['name' => 'Private day']);

        $this->actingAs($other)
            ->get(route('workout.start', $day))
            ->assertForbidden();
    }
}
