<?php

namespace Tests\Feature;

use App\Models\Exercise;
use App\Models\User;
use App\Models\WorkoutLog;
use App\Models\WorkoutProgram;
use App\Models\WorkoutSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class WorkoutStartQueryPerformanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_last_performance_is_scoped_to_user_and_saved_sets_are_batched(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $program = WorkoutProgram::create(['user_id' => $user->id, 'name' => 'PPL', 'is_active' => true]);
        $day = $program->days()->create(['name' => 'Push']);
        $first = Exercise::create(['user_id' => null, 'name' => 'Bench', 'target_muscle' => 'Chest']);
        $second = Exercise::create(['user_id' => null, 'name' => 'Press', 'target_muscle' => 'Chest']);

        foreach ([$first, $second] as $exercise) {
            $day->dayExercises()->create([
                'exercise_id' => $exercise->id, 'default_sets' => 3, 'default_reps' => 10,
            ]);
        }

        $oldSession = WorkoutSession::create([
            'user_id' => $user->id, 'workout_day_id' => $day->id,
            'date' => now()->subDays(2)->toDateString(),
        ]);
        $oldLog = WorkoutLog::create([
            'workout_session_id' => $oldSession->id, 'exercise_id' => $first->id,
            'set_number' => 1, 'weight' => 40, 'reps' => 10,
        ]);
        $oldLog->timestamps = false;
        $oldLog->created_at = now()->subDays(2);
        $oldLog->save();

        $previousSession = WorkoutSession::create([
            'user_id' => $user->id, 'workout_day_id' => $day->id,
            'date' => now()->subDay()->toDateString(),
        ]);
        $expected = WorkoutLog::create([
            'workout_session_id' => $previousSession->id, 'exercise_id' => $first->id,
            'set_number' => 1, 'weight' => 50, 'reps' => 8,
        ]);

        $otherSession = WorkoutSession::create([
            'user_id' => $other->id, 'workout_day_id' => $day->id,
            'date' => now()->toDateString(),
        ]);
        WorkoutLog::create([
            'workout_session_id' => $otherSession->id, 'exercise_id' => $first->id,
            'set_number' => 1, 'weight' => 100, 'reps' => 5,
        ]);

        $today = WorkoutSession::create([
            'user_id' => $user->id, 'workout_day_id' => $day->id,
            'date' => now()->toDateString(),
        ]);
        WorkoutLog::create([
            'workout_session_id' => $today->id, 'exercise_id' => $first->id,
            'set_number' => 1, 'weight' => 55, 'reps' => 8,
        ]);
        WorkoutLog::create([
            'workout_session_id' => $today->id, 'exercise_id' => $first->id,
            'set_number' => 2, 'weight' => 55, 'reps' => 7,
        ]);

        $logQueries = 0;
        DB::listen(function ($query) use (&$logQueries): void {
            if (str_contains(strtolower($query->sql), 'workout_logs')) {
                $logQueries++;
            }
        });

        $this->actingAs($user)->get(route('workout.start', $day))
            ->assertOk()
            ->assertViewHas('previousLogs', function ($logs) use ($first, $second, $expected) {
                return isset($logs[$first->id])
                    && $logs[$first->id]->id === $expected->id
                    && ! isset($logs[$second->id]);
            })
            ->assertViewHas('savedTodayLogs', function ($logs) use ($first) {
                return isset($logs[$first->id]) && $logs[$first->id]->count() === 2;
            });

        $this->assertLessThanOrEqual(3, $logQueries, 'Opening a day must not query logs once per exercise.');
    }
}
