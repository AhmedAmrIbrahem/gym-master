<?php

namespace Tests\Feature;

use App\Models\DayExercise;
use App\Models\Exercise;
use App\Models\User;
use App\Models\WorkoutDay;
use App\Models\WorkoutProgram;
use App\Models\WorkoutSession;
use App\Models\WorkoutLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProtectedDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_program_with_days_cannot_be_deleted(): void
    {
        $user = User::factory()->create();
        $program = WorkoutProgram::create(['user_id' => $user->id, 'name' => 'PPL']);
        $day = WorkoutDay::create(['workout_program_id' => $program->id, 'name' => 'Push']);

        $this->actingAs($user)->delete(route('programs.destroy', $program))
            ->assertRedirect()->assertSessionHas('error');

        $this->assertDatabaseHas('workout_programs', ['id' => $program->id]);
        $this->assertDatabaseHas('workout_days', ['id' => $day->id]);
    }

    public function test_day_with_exercises_cannot_be_deleted(): void
    {
        $user = User::factory()->create();
        $program = WorkoutProgram::create(['user_id' => $user->id, 'name' => 'PPL']);
        $day = WorkoutDay::create(['workout_program_id' => $program->id, 'name' => 'Push']);
        $exercise = Exercise::create(['user_id' => $user->id, 'name' => 'Bench', 'target_muscle' => 'صدر']);
        $entry = DayExercise::create(['workout_day_id' => $day->id, 'exercise_id' => $exercise->id]);

        $this->actingAs($user)->delete(route('days.destroy', $day))
            ->assertRedirect()->assertSessionHas('error');
        $this->assertDatabaseHas('day_exercises', ['id' => $entry->id]);
    }

    public function test_exercise_used_by_day_cannot_be_deleted(): void
    {
        $user = User::factory()->create();
        $program = WorkoutProgram::create(['user_id' => $user->id, 'name' => 'PPL']);
        $day = WorkoutDay::create(['workout_program_id' => $program->id, 'name' => 'Push']);
        $exercise = Exercise::create(['user_id' => $user->id, 'name' => 'Bench', 'target_muscle' => 'صدر']);
        DayExercise::create(['workout_day_id' => $day->id, 'exercise_id' => $exercise->id]);

        $this->actingAs($user)->delete(route('exercises.destroy', $exercise))
            ->assertRedirect()->assertSessionHas('error');
        $this->assertDatabaseHas('exercises', ['id' => $exercise->id]);
    }

    public function test_day_with_workout_session_cannot_be_deleted(): void
    {
        $user = User::factory()->create();
        $program = WorkoutProgram::create(['user_id' => $user->id, 'name' => 'PPL']);
        $day = WorkoutDay::create(['workout_program_id' => $program->id, 'name' => 'Push']);
        $session = WorkoutSession::create(['user_id' => $user->id, 'workout_day_id' => $day->id, 'date' => today()->toDateString()]);

        $this->actingAs($user)->delete(route('days.destroy', $day))
            ->assertRedirect()->assertSessionHas('error');
        $this->assertDatabaseHas('workout_sessions', ['id' => $session->id]);
    }

    public function test_account_with_training_data_cannot_be_deleted(): void
    {
        $user = User::factory()->create();
        WorkoutProgram::create(['user_id' => $user->id, 'name' => 'PPL']);

        $this->actingAs($user)->delete(route('profile.destroy'), ['password' => 'password'])
            ->assertRedirect()->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    public function test_exercise_with_workout_history_cannot_be_deleted_even_without_day_assignment(): void
    {
        $user = User::factory()->create();
        $program = WorkoutProgram::create(['user_id' => $user->id, 'name' => 'PPL']);
        $day = WorkoutDay::create(['workout_program_id' => $program->id, 'name' => 'Push']);
        $exercise = Exercise::create(['user_id' => $user->id, 'name' => 'Bench', 'target_muscle' => 'Chest']);
        $session = WorkoutSession::create([
            'user_id' => $user->id,
            'workout_day_id' => $day->id,
            'date' => today()->toDateString(),
        ]);
        $log = WorkoutLog::create([
            'workout_session_id' => $session->id,
            'exercise_id' => $exercise->id,
            'set_number' => 1, 'weight' => 40, 'reps' => 8,
        ]);

        $this->actingAs($user)->delete(route('exercises.destroy', $exercise))
            ->assertRedirect()->assertSessionHas('error');

        $this->assertDatabaseHas('workout_logs', ['id' => $log->id, 'exercise_id' => $exercise->id]);
        $this->assertDatabaseHas('exercises', ['id' => $exercise->id]);
    }

    public function test_admin_cannot_delete_member_with_linked_data(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $member = User::factory()->create(['role' => 'user']);
        $program = WorkoutProgram::create(['user_id' => $member->id, 'name' => 'Saved plan']);

        $this->actingAs($admin)->delete(route('admin.users.destroy', $member))
            ->assertRedirect()->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $member->id]);
        $this->assertDatabaseHas('workout_programs', ['id' => $program->id]);
    }

    public function test_unused_member_can_still_be_deleted_by_admin(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $member = User::factory()->create(['role' => 'user']);

        $this->actingAs($admin)->delete(route('admin.users.destroy', $member))
            ->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $member->id]);
    }

    public function test_empty_exercise_and_empty_program_can_still_be_deleted(): void
    {
        $user = User::factory()->create();
        $program = WorkoutProgram::create(['user_id' => $user->id, 'name' => 'Empty']);
        $exercise = Exercise::create(['user_id' => $user->id, 'name' => 'Unused', 'target_muscle' => 'صدر']);
        $this->actingAs($user)->delete(route('programs.destroy', $program))->assertSessionHas('success');
        $this->delete(route('exercises.destroy', $exercise))->assertSessionHas('success');
        $this->assertDatabaseMissing('workout_programs', ['id' => $program->id]);
        $this->assertDatabaseMissing('exercises', ['id' => $exercise->id]);
    }
}
