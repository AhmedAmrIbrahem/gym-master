<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ChatbotFoodPhotoTest extends TestCase
{
    use RefreshDatabase;

    private function photo(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('meal.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Wl6+S8AAAAASUVORK5CYII='));
    }

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.gemini.key' => 'test-only-key', 'services.gemini.model' => 'vision-primary', 'services.gemini.fallback_model' => 'vision-fallback']);
        Http::preventStrayRequests();
    }

    public function test_image_without_caption_is_sent_inline_without_account_or_history_and_without_saving_meal(): void
    {
        Http::fake(['*' => Http::response(['candidates' => [['content' => ['parts' => [['text' => json_encode(['items' => [['name' => 'وجبة', 'grams' => 200, 'calories' => 290, 'protein' => 20, 'carbs' => 30, 'fats' => 10]], 'notes' => 'وزن مفترض'])]]], 'finishReason' => 'STOP']]])]);
        $user = User::factory()->create(['name' => 'Private account name']);
        $this->actingAs($user)->postJson(route('chatbot.send'), [
            'photo' => $this->photo(),
            'history' => [['sender' => 'user', 'text' => 'private history']],
        ])->assertOk()->assertJsonPath('reply', fn ($reply) => str_contains($reply, 'بروتين 20') && str_contains($reply, 'تقدير غذائي'));

        Http::assertSent(fn ($r) => $r->hasHeader('x-goog-api-key', 'test-only-key')
            && $r['contents'][0]['parts'][0]['inlineData']['mimeType'] === 'image/png'
            && base64_decode($r['contents'][0]['parts'][0]['inlineData']['data']) === $this->photo()->getContent()
            && ! str_contains($r->body(), 'private history') && ! str_contains($r->body(), 'Private account name'));
        $this->assertDatabaseCount('daily_meals', 0);
        $this->assertDatabaseCount('meal_items', 0);
        $this->assertDatabaseCount('food_photo_drafts', 1);
    }

    public function test_caption_is_forwarded_with_image(): void
    {
        Http::fake(['*' => Http::response(['candidates' => [['content' => ['parts' => [['text' => 'تقدير الوجبة']]]]]])]);
        $this->actingAs(User::factory()->create())->postJson(route('chatbot.send'), ['photo' => $this->photo(), 'message' => 'رز مطبوخ 150 جرام'])
            ->assertOk();
        Http::assertSent(fn ($r) => $r['contents'][0]['parts'][1]['text'] === 'رز مطبوخ 150 جرام');
    }

    public function test_fake_image_and_svg_and_oversized_upload_are_rejected_before_provider_call(): void
    {
        $this->actingAs(User::factory()->create());
        foreach ([
            UploadedFile::fake()->createWithContent('meal.jpg', '<?php echo "not an image";'),
            UploadedFile::fake()->createWithContent('meal.svg', '<svg xmlns="http://www.w3.org/2000/svg"></svg>'),
            $this->photo()->size(5121),
        ] as $file) {
            $this->postJson(route('chatbot.send'), ['photo' => $file])->assertUnprocessable()->assertJsonValidationErrors('photo');
        }
        Http::assertNothingSent();
    }

    public function test_empty_request_is_rejected_and_guest_cannot_upload(): void
    {
        $this->postJson(route('chatbot.send'), ['photo' => $this->photo()])->assertUnauthorized();
        $this->actingAs(User::factory()->create())->postJson(route('chatbot.send'), [])->assertUnprocessable()->assertJsonValidationErrors('message');
        Http::assertNothingSent();
    }

    public function test_missing_key_does_not_send_image(): void
    {
        config(['services.gemini.key' => '']);
        $this->actingAs(User::factory()->create())->postJson(route('chatbot.send'), ['photo' => $this->photo()])
            ->assertOk()->assertJsonPath('reply', fn ($reply) => str_contains($reply, 'مفتاح Gemini'));
        Http::assertNothingSent();
    }

    public function test_temporary_provider_failure_uses_fallback_and_empty_response_is_explained(): void
    {
        Http::fake(['*' => Http::sequence()->push([], 503)->push(['candidates' => []])]);
        $this->actingAs(User::factory()->create())->postJson(route('chatbot.send'), ['photo' => $this->photo()])
            ->assertOk()->assertJsonPath('reply', fn ($reply) => str_contains($reply, 'صورة أوضح'));
        Http::assertSentCount(2);
        Http::assertSent(fn ($r) => str_contains($r->url(), 'vision-fallback:generateContent'));
    }

    public function test_provider_quota_error_is_not_reported_as_food_analysis(): void
    {
        Http::fake(['*' => Http::response([], 429)]);
        $this->actingAs(User::factory()->create())->postJson(route('chatbot.send'), ['photo' => $this->photo()])
            ->assertOk()->assertJsonPath('reply', fn ($reply) => str_contains($reply, 'حد استخدام Gemini'));
        Http::assertSentCount(2);
    }

    public function test_provider_connection_error_is_handled_without_exposing_secrets(): void
    {
        Http::fake(['*' => Http::failedConnection()]);
        $this->actingAs(User::factory()->create())->postJson(route('chatbot.send'), ['photo' => $this->photo()])
            ->assertOk()->assertJsonPath('reply', fn ($reply) => str_contains($reply, 'تعذر الاتصال') && ! str_contains($reply, 'test-only-key'));
    }
}
