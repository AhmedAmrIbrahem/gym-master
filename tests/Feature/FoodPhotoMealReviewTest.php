<?php

namespace Tests\Feature;

use App\Actions\Nutrition\AddMealItemAction;
use App\Models\DailyMeal;
use App\Models\Food;
use App\Models\FoodPhotoDraft;
use App\Models\MealItem;
use App\Models\User;
use App\Services\Nutrition\DailyNutritionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Mockery;
use Tests\TestCase;

class FoodPhotoMealReviewTest extends TestCase
{
    use RefreshDatabase;

    private function items(): array
    {
        return [['name' => 'فراخ مشوية', 'grams' => 200, 'calories' => 330, 'protein' => 62, 'carbs' => 0, 'fats' => 7.2]];
    }

    private function draft(User $user): FoodPhotoDraft
    {
        return FoodPhotoDraft::create(['id' => (string) Str::uuid(), 'user_id' => $user->id, 'items' => $this->items(), 'expires_at' => now('UTC')->addDay()]);
    }

    private function payload(): array
    {
        return ['date' => now('Africa/Cairo')->toDateString(), 'meal_type' => 'غداء', 'items' => $this->items()];
    }

    public function test_reviewed_values_save_to_existing_nutrition_totals_and_double_click_does_not_duplicate(): void
    {
        $user = User::factory()->create();
        $draft = $this->draft($user);
        $data = $this->payload();
        $data['items'][0] = ['name' => 'فراخ معدلة', 'grams' => 100, 'calories' => 165, 'protein' => 31, 'carbs' => 0, 'fats' => 3.6];
        $url = route('chatbot.food-drafts.save', $draft->id);
        $this->actingAs($user)->postJson($url, $data)->assertOk()->assertJsonPath('saved', true)->assertJsonPath('replayed', false);
        $this->postJson($url, $data)->assertOk()->assertJsonPath('replayed', true);
        $this->assertDatabaseCount('meal_items', 1);
        $this->assertDatabaseCount('foods', 1);
        $this->assertSame($user->id, Food::sole()->user_id);
        $this->assertEquals(100, MealItem::sole()->grams);
        $this->assertNotNull($draft->fresh()->consumed_at);
        $totals = app(DailyNutritionService::class)->forDate($user, $data['date']);
        $this->assertEquals(165, $totals['totalCalories']);
        $this->assertEquals(31, $totals['totalProtein']);
        $this->assertEquals(3.6, $totals['totalFats']);
    }

    public function test_retry_with_reordered_keys_is_idempotent_but_edited_payload_after_save_conflicts(): void
    {
        $user = User::factory()->create();
        $url = route('chatbot.food-drafts.save', $this->draft($user)->id);
        $data = $this->payload();
        $this->actingAs($user)->postJson($url, $data)->assertOk();
        $this->postJson($url, array_reverse($data, true))->assertOk()->assertJsonPath('replayed', true);
        $data['items'][0]['grams'] = 300;
        $this->postJson($url, $data)->assertConflict();
        $this->assertDatabaseCount('meal_items', 1);
    }

    public function test_drafts_are_scoped_and_admin_guest_inactive_cannot_save(): void
    {
        $owner = User::factory()->create();
        $url = route('chatbot.food-drafts.save', $this->draft($owner)->id);
        $this->postJson($url, $this->payload())->assertUnauthorized();
        $this->actingAs(User::factory()->create())->postJson($url, $this->payload())->assertNotFound();
        $this->actingAs(User::factory()->create(['role' => 'super_admin']))->postJson($url, $this->payload())->assertForbidden();
        $owner->update(['is_active' => false]);
        $this->actingAs($owner)->postJson($url, $this->payload())->assertForbidden();
        $this->assertDatabaseCount('meal_items', 0);
    }

    public function test_expired_draft_cannot_save_but_retry_of_previously_saved_expired_draft_is_safe(): void
    {
        $user = User::factory()->create();
        $draft = $this->draft($user);
        $draft->update(['expires_at' => now('UTC')->subMinute()]);
        $this->actingAs($user)->postJson(route('chatbot.food-drafts.save', $draft->id), $this->payload())->assertGone();
        $draft->update(['expires_at' => now('UTC')->addMinute()]);
        $this->postJson(route('chatbot.food-drafts.save', $draft->id), $this->payload())->assertOk();
        $draft->update(['expires_at' => now('UTC')->subMinute()]);
        $this->postJson(route('chatbot.food-drafts.save', $draft->id), $this->payload())->assertOk()->assertJsonPath('replayed', true);
        $this->assertDatabaseCount('meal_items', 1);
    }

    public function test_invalid_items_future_date_and_unrecognized_type_are_rejected(): void
    {
        $user = User::factory()->create();
        $url = route('chatbot.food-drafts.save', $this->draft($user)->id);
        $this->actingAs($user);
        foreach ([['items' => []], ['items' => array_fill(0, 13, $this->items()[0])], ['date' => now('Africa/Cairo')->addDay()->toDateString()], ['meal_type' => 'fake']] as $invalid) {
            $this->postJson($url, array_replace($this->payload(), $invalid))->assertUnprocessable();
        }
        foreach (['grams' => 0, 'calories' => -1, 'protein' => 9000, 'name' => ''] as $field => $value) {
            $data = $this->payload();
            $data['items'][0][$field] = $value;
            $this->postJson($url, $data)->assertUnprocessable();
        }
        $data = $this->payload();
        $data['items'][0]['grams'] = 1;
        $this->postJson($url, $data)->assertUnprocessable();
        $this->assertDatabaseCount('meal_items', 0);
        $this->assertDatabaseCount('foods', 0);
    }

    public function test_existing_foods_and_other_members_meals_are_never_modified(): void
    {
        $other = User::factory()->create();
        $public = Food::create(['name' => 'فراخ مشوية', 'calories_per_100g' => 100, 'protein_per_100g' => 10, 'carbs_per_100g' => 5, 'fats_per_100g' => 1]);
        $meal = DailyMeal::create(['user_id' => $other->id, 'date' => now()->toDateString(), 'meal_type' => 'غداء']);
        MealItem::create(['daily_meal_id' => $meal->id, 'food_id' => $public->id, 'grams' => 100]);
        $user = User::factory()->create();
        $this->actingAs($user)->postJson(route('chatbot.food-drafts.save', $this->draft($user)->id), $this->payload() + ['user_id' => $other->id, 'daily_meal_id' => $meal->id])->assertOk();
        $this->assertEquals(100, $public->fresh()->calories_per_100g);
        $this->assertEquals(100, $meal->fresh()->total_calories);
        $this->assertDatabaseCount('foods', 2);
        $this->getJson(route('nutrition.searchFoods', ['q' => 'فراخ']))->assertOk();
    }

    public function test_failure_rolls_back_all_items_foods_and_consumption_marker(): void
    {
        $user = User::factory()->create();
        $draft = $this->draft($user);
        $action = Mockery::mock(AddMealItemAction::class);
        $action->shouldReceive('execute')->once()->andThrow(new \RuntimeException('simulated save failure'));
        $this->app->instance(AddMealItemAction::class, $action);
        $this->actingAs($user)->postJson(route('chatbot.food-drafts.save', $draft->id), $this->payload())->assertServerError();
        $this->assertDatabaseCount('meal_items', 0);
        $this->assertDatabaseCount('foods', 0);
        $this->assertNull($draft->fresh()->consumed_at);
    }

    public function test_structured_analysis_creates_only_draft_and_invalid_model_data_never_creates_one(): void
    {
        config(['services.gemini.key' => 'test-key']);
        $user = User::factory()->create();
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Wl6+S8AAAAASUVORK5CYII=');
        Http::fake(['*' => Http::sequence()
            ->push(['candidates' => [['content' => ['parts' => [['text' => json_encode(['items' => $this->items(), 'notes' => 'وزن تقديري'])]]], 'finishReason' => 'STOP']]])
            ->push(['candidates' => [['content' => ['parts' => [['text' => '{"items":[{"name":"broken"}],"notes":""}']]]]]])
            ->push(['candidates' => [['content' => ['parts' => [['text' => 'not JSON']]]]]])]);
        $response = $this->actingAs($user)->postJson(route('chatbot.send'), ['photo' => UploadedFile::fake()->createWithContent('meal.png', $png)])->assertOk()->assertJsonPath('meal_draft.items.0.grams', 200);
        $this->assertDatabaseHas('food_photo_drafts', ['id' => $response->json('meal_draft.id'), 'user_id' => $user->id, 'consumed_at' => null]);
        foreach (range(1, 2) as $attempt) {
            $this->postJson(route('chatbot.send'), ['photo' => UploadedFile::fake()->createWithContent('meal.png', $png)])->assertOk()->assertJsonMissingPath('meal_draft');
        }
        $this->assertDatabaseCount('food_photo_drafts', 1);
        $this->assertDatabaseCount('meal_items', 0);
        Http::assertSent(fn ($r) => $r['generationConfig']['responseMimeType'] === 'application/json');
    }

    public function test_expired_draft_cleanup_keeps_saved_meals_and_pruned_draft_cannot_be_used_again(): void
    {
        $user = User::factory()->create();
        $draft = $this->draft($user);
        $url = route('chatbot.food-drafts.save', $draft->id);
        $this->actingAs($user)->postJson($url, $this->payload())->assertOk();
        $draft->update(['expires_at' => now('UTC')->subDays(8)]);
        $this->artisan('model:prune', ['--model' => [FoodPhotoDraft::class]])->assertSuccessful();
        $this->assertDatabaseCount('food_photo_drafts', 0);
        $this->assertDatabaseCount('daily_meals', 1);
        $this->assertDatabaseCount('meal_items', 1);
        $this->postJson($url, $this->payload())->assertNotFound();
        $this->assertDatabaseCount('meal_items', 1);
    }
}
