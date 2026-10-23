<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WorkoutProgram;
use App\Models\WorkoutSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkoutHistoryPerformanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_history_counts_distinct_session_dates_in_current_month_for_signed_in_user_only(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $program = WorkoutProgram::create([
            'user_id' => $user->id, 'name' => 'Training', 'is_active' => true,
        ]);
        $day = $program->days()->create(['name' => 'Day 1']);

        foreach ([
            [$user->id, now()->startOfMonth()->addDays(1), now()->startOfMonth()->addDay()->toDateString()],
            [$user->id, now()->startOfMonth()->addDays(1)->addHours(1), now()->startOfMonth()->addDay()->toDateString()],
            [$user->id, now()->startOfMonth()->subSecond(), now()->startOfMonth()->subDay()->toDateString()],
            [$other->id, now()->startOfMonth()->addDays(2), now()->startOfMonth()->addDays(2)->toDateString()],
        ] as [$userId, $created, $date]) {
            $session = WorkoutSession::create([
                'user_id' => $userId,
                'workout_day_id' => $day->id,
                'date' => $date,
            ]);
            $session->timestamps = false;
            $session->created_at = $created;
            $session->updated_at = $created;
            $session->save();
        }

        $this->actingAs($user)->get(route('history.index'))
            ->assertOk()
            ->assertViewHas('monthlyDaysCount', 1);
    }
}
