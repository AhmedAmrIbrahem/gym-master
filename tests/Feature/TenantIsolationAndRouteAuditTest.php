<?php

namespace Tests\Feature;

use App\Models\BodyMeasurement;
use App\Models\DailyMeal;
use App\Models\Exercise;
use App\Models\Food;
use App\Models\MealItem;
use App\Models\User;
use App\Models\WorkoutProgram;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantIsolationAndRouteAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_both_catalog_search_routes_are_available_and_isolated(): void
    {
        $member = User::factory()->create();
        $stranger = User::factory()->create();

        $publicFood = Food::create($this->foodFields(null, 'Shared protein'));
        $ownFood = Food::create($this->foodFields($member->id, 'My protein'));
        Food::create($this->foodFields($stranger->id, 'Hidden protein'));

        Exercise::create(['user_id' => null, 'name' => 'Shared press', 'target_muscle' => 'Chest']);
        Exercise::create(['user_id' => $member->id, 'name' => 'My press', 'target_muscle' => 'Chest']);
        Exercise::create(['user_id' => $stranger->id, 'name' => 'Hidden press', 'target_muscle' => 'Chest']);

        $this->getJson(route('nutrition.searchFoods', ['q' => 'protein']))->assertUnauthorized();
        $this->get(route('programs.exercises.search', ['q' => 'press']))->assertRedirect(route('login'));

        $foods = $this->actingAs($member)->getJson(route('nutrition.searchFoods', ['q' => 'protein']))
            ->assertOk()->assertJsonCount(2, 'foods');
        $foodIds = array_column($foods->json('foods'), 'id');
        $this->assertEqualsCanonicalizing([$publicFood->id, $ownFood->id], $foodIds);
        $this->assertStringContainsString('no-store', $foods->headers->get('Cache-Control', ''));

        $exercises = $this->getJson(route('programs.exercises.search', ['q' => 'press']))
            ->assertOk()->assertJsonCount(2, 'items')
            ->assertDontSee('Hidden press');
        $this->assertStringContainsString('no-store', $exercises->headers->get('Cache-Control', ''));
    }

    public function test_member_cannot_access_another_members_program_workout_or_measurement(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $program = WorkoutProgram::create(['user_id' => $owner->id, 'name' => 'PRIVATE_PLAN']);
        $day = $program->days()->create(['name' => 'Private day']);
        $exercise = Exercise::create(['user_id' => $owner->id, 'name' => 'Private press', 'target_muscle' => 'Chest']);
        $measurement = BodyMeasurement::create([
            'user_id' => $owner->id, 'date' => '2026-10-20', 'weight' => 77, 'height' => 175,
        ]);

        $this->actingAs($stranger)->get(route('programs.index'))->assertOk()->assertDontSee('PRIVATE_PLAN');
        $this->get(route('workout.start', $day))->assertForbidden();
        $this->post(route('days.exercises.attach', $day), [
            'exercise_id' => $exercise->id, 'default_sets' => 3, 'default_reps' => 8,
        ])->assertForbidden();
        $this->delete(route('measurements.destroy', $measurement))->assertForbidden();
        $this->assertDatabaseHas('body_measurements', ['id' => $measurement->id, 'user_id' => $owner->id]);
        $this->assertDatabaseHas('workout_programs', ['id' => $program->id, 'user_id' => $owner->id]);
    }

    public function test_member_cannot_access_another_members_food_and_meal_items(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $food = Food::create($this->foodFields($owner->id, 'PRIVATE_FOOD'));
        $meal = DailyMeal::create([
            'user_id' => $owner->id, 'date' => '2026-10-20', 'meal_type' => 'إفطار',
        ]);
        $item = MealItem::create(['daily_meal_id' => $meal->id, 'food_id' => $food->id, 'grams' => 170]);

        $this->actingAs($stranger)->get(route('nutrition.index', ['date' => '2026-10-20']))
            ->assertOk()->assertDontSee('PRIVATE_FOOD');
        $this->post(route('nutrition.storeMealItem'), [
            'daily_meal_id' => $meal->id, 'food_id' => $food->id, 'grams' => 100,
        ])->assertForbidden();
        $this->delete(route('nutrition.destroyMealItem', $item))->assertForbidden();
        $this->delete(route('nutrition.destroyFood', $food))->assertForbidden();

        $this->assertDatabaseHas('meal_items', ['id' => $item->id, 'grams' => 170]);
        $this->assertDatabaseHas('foods', ['id' => $food->id, 'user_id' => $owner->id]);
    }

    public function test_unsupported_program_detail_urls_return_expected_http_errors(): void
    {
        $member = User::factory()->create();
        $program = WorkoutProgram::create(['user_id' => $member->id, 'name' => 'Plan']);

        // A resource path that exists for PUT/DELETE returns 405 for GET, not 404.
        $this->actingAs($member)->get('/programs/'.$program->id)->assertStatus(405);
        $this->get('/programs/'.$program->id.'/edit')->assertNotFound();
        $this->get('/programs/create')->assertStatus(405);
    }

    public function test_program_and_day_cannot_be_deleted_while_linked_records_exist(): void
    {
        $member = User::factory()->create();
        $program = WorkoutProgram::create(['user_id' => $member->id, 'name' => 'PPL']);
        $day = $program->days()->create(['name' => 'Push']);
        $exercise = Exercise::create(['user_id' => $member->id, 'name' => 'Bench', 'target_muscle' => 'Chest']);
        $day->dayExercises()->create(['exercise_id' => $exercise->id, 'default_sets' => 3, 'default_reps' => 10]);

        $this->actingAs($member)->delete(route('programs.destroy', $program))
            ->assertRedirect()->assertSessionHas('error');
        $this->delete(route('days.destroy', $day))
            ->assertRedirect()->assertSessionHas('error');

        $this->assertDatabaseHas('workout_programs', ['id' => $program->id]);
        $this->assertDatabaseHas('workout_days', ['id' => $day->id]);
    }

    public function test_member_cannot_open_admin_user_management(): void
    {
        $member = User::factory()->create(['role' => 'user']);

        $this->actingAs($member)->get(route('admin.users.index'))->assertForbidden();
    }

    private function foodFields(?int $ownerId, string $name): array
    {
        return [
            'user_id' => $ownerId,
            'name' => $name,
            'calories_per_100g' => 100,
            'protein_per_100g' => 10,
            'carbs_per_100g' => 5,
            'fats_per_100g' => 2,
        ];
    }
}
