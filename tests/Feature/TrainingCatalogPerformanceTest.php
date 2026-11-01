<?php

namespace Tests\Feature;

use App\Models\Exercise;
use App\Models\User;
use App\Models\WorkoutProgram;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TrainingCatalogPerformanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_programs_are_paginated_for_owner_with_days_and_attached_exercises_preserved(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        WorkoutProgram::create(['user_id' => $other->id, 'name' => 'OTHER USERS PROGRAM']);
        $attached = Exercise::create(['user_id' => $user->id, 'name' => 'Attached press', 'target_muscle' => 'Chest']);

        for ($index = 1; $index <= 8; $index++) {
            $program = WorkoutProgram::create(['user_id' => $user->id, 'name' => 'Plan '.$index]);
            if ($index === 1) {
                $day = $program->days()->create(['name' => 'Push Day']);
                $day->dayExercises()->create(['exercise_id' => $attached->id, 'default_sets' => 4, 'default_reps' => 8]);
            }
        }

        $this->actingAs($user)->get(route('programs.index'))
            ->assertOk()
            ->assertViewHas('programs', fn ($page) => $page->total() === 8
                && $page->count() === 5
                && $page->currentPage() === 1
                && $page->first()->name === 'Plan 1')
            ->assertSee('Push Day')
            ->assertSee('Attached press')
            ->assertDontSee('OTHER USERS PROGRAM');

        $this->get(route('programs.index', ['page' => 2]))
            ->assertOk()
            ->assertViewHas('programs', fn ($page) => $page->total() === 8
                && $page->count() === 3
                && $page->currentPage() === 2
                && $page->first()->name === 'Plan 6');
    }

    public function test_remote_exercise_search_is_authenticated_user_scoped_and_limited_to_forty(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        Exercise::create(['user_id' => null, 'name' => 'Chest global', 'target_muscle' => 'Chest']);
        $owned = Exercise::create(['user_id' => $user->id, 'name' => 'Chest mine', 'target_muscle' => 'Chest']);
        Exercise::create(['user_id' => $other->id, 'name' => 'Chest secret', 'target_muscle' => 'Chest']);
        Exercise::create(['user_id' => $user->id, 'name' => 'Different name', 'target_muscle' => 'Legs']);

        $this->get(route('programs.exercises.search', ['q' => 'Chest']))->assertRedirect(route('login'));

        $this->actingAs($user)->getJson(route('programs.exercises.search', ['q' => 'Chest']))
            ->assertOk()
            ->assertJsonPath('items.0.target_muscle', 'Chest')
            ->assertJsonFragment(['id' => $owned->id, 'name' => 'Chest mine'])
            ->assertDontSee('Chest secret')
            ->assertJsonCount(2, 'items');

        for ($index = 1; $index <= 48; $index++) {
            Exercise::create([
                'user_id' => $user->id,
                'name' => 'Chest catalog '.$index,
                'target_muscle' => 'Chest',
            ]);
        }

        $this->getJson(route('programs.exercises.search', ['q' => 'Chest']))
            ->assertOk()
            ->assertJsonCount(40, 'items');
    }

    public function test_program_page_does_not_load_entire_exercise_bank_and_attach_still_reuses_existing_id(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $program = WorkoutProgram::create(['user_id' => $user->id, 'name' => 'PPL']);
        $day = $program->days()->create(['name' => 'Push']);
        $exercise = Exercise::create(['user_id' => $user->id, 'name' => 'Bench available via search', 'target_muscle' => 'Chest']);
        $foreign = Exercise::create(['user_id' => $other->id, 'name' => 'Foreign', 'target_muscle' => 'Chest']);

        for ($index = 0; $index < 60; $index++) {
            Exercise::create(['user_id' => $user->id, 'name' => 'Unused gym exercise '.$index, 'target_muscle' => 'Arms']);
        }

        $exerciseSelects = 0;
        DB::listen(function ($query) use (&$exerciseSelects): void {
            if (str_contains(strtolower($query->sql), 'from "exercises"')) {
                $exerciseSelects++;
            }
        });

        $this->actingAs($user)->get(route('programs.index'))
            ->assertOk()
            ->assertDontSee('Unused gym exercise 0')
            ->assertSee(route('programs.exercises.search'));

        $this->assertLessThanOrEqual(2, $exerciseSelects, 'Program page should not preload the exercise catalog.');

        $before = Exercise::count();
        $this->post(route('days.exercises.attach', $day), [
            'exercise_id' => $exercise->id,
            'default_sets' => 3,
            'default_reps' => 10,
        ])->assertRedirect();

        $this->assertSame($before, Exercise::count());
        $this->assertDatabaseHas('day_exercises', [
            'workout_day_id' => $day->id,
            'exercise_id' => $exercise->id,
        ]);

        $this->post(route('days.exercises.attach', $day), [
            'exercise_id' => $foreign->id,
            'default_sets' => 3,
            'default_reps' => 10,
        ])->assertForbidden();

        $this->assertSame($before, Exercise::count());
    }
}
