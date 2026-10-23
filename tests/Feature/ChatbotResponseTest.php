<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\CalorieCalculatorService;
use App\Services\ChatbotKnowledgeService;
use App\Services\ChatbotMemberDataService;
use App\Services\GeminiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class ChatbotResponseTest extends TestCase
{
    use RefreshDatabase;

    public function test_explicit_calorie_request_keeps_calorie_card_payload_and_skips_gemini(): void
    {
        $user = User::factory()->create();
        $calculator = Mockery::mock(CalorieCalculatorService::class);
        $calculator->shouldReceive('isExplicitCalculationRequest')->once()->andReturn(true);
        $calculator->shouldReceive('answer')->once()->andReturn('حساب السعرات');
        $calculator->shouldReceive('resultData')->once()->andReturn(['calories' => 2100]);
        $this->app->instance(CalorieCalculatorService::class, $calculator);
        $this->app->instance(GeminiService::class, Mockery::mock(GeminiService::class));
        $this->app->instance(ChatbotMemberDataService::class, Mockery::mock(ChatbotMemberDataService::class));

        $this->actingAs($user)->postJson(route('chatbot.send'), ['message' => 'احسب السعرات'])
            ->assertOk()->assertExactJson(['reply' => 'حساب السعرات', 'calorie_result' => ['calories' => 2100]]);
    }

    public function test_member_data_reply_takes_priority_over_faq_and_gemini(): void
    {
        $user = User::factory()->create();
        $calculator = Mockery::mock(CalorieCalculatorService::class);
        $calculator->shouldReceive('isExplicitCalculationRequest')->once()->andReturn(false);
        $this->app->instance(CalorieCalculatorService::class, $calculator);
        $members = Mockery::mock(ChatbotMemberDataService::class);
        $members->shouldReceive('answer')->once()->andReturn('بيانات المشترك');
        $this->app->instance(ChatbotMemberDataService::class, $members);
        $this->app->instance(ChatbotKnowledgeService::class, Mockery::mock(ChatbotKnowledgeService::class));
        $this->app->instance(GeminiService::class, Mockery::mock(GeminiService::class));

        $this->actingAs($user)->postJson(route('chatbot.send'), ['message' => 'عرض بياناتي'])
            ->assertOk()->assertExactJson(['reply' => 'بيانات المشترك']);
    }

    public function test_unhandled_question_falls_back_to_gemini_with_history(): void
    {
        $user = User::factory()->create();
        $history = [['sender' => 'user', 'text' => 'أهلا']];
        $calculator = Mockery::mock(CalorieCalculatorService::class);
        $calculator->shouldReceive('isExplicitCalculationRequest')->once()->andReturn(false);
        $calculator->shouldReceive('answer')->once()->andReturn(null);
        $this->app->instance(CalorieCalculatorService::class, $calculator);
        $members = Mockery::mock(ChatbotMemberDataService::class);
        $members->shouldReceive('answer')->once()->andReturn(null);
        $this->app->instance(ChatbotMemberDataService::class, $members);
        $faq = Mockery::mock(ChatbotKnowledgeService::class);
        $faq->shouldReceive('findAnswer')->once()->andReturn(null);
        $this->app->instance(ChatbotKnowledgeService::class, $faq);
        $gemini = Mockery::mock(GeminiService::class);
        $gemini->shouldReceive('askCoach')->once()->with(Mockery::on(fn ($u) => $u->is($user)), 'ممكن نصيحة؟', $history)->andReturn('رد Gemini');
        $this->app->instance(GeminiService::class, $gemini);

        $this->actingAs($user)->postJson(route('chatbot.send'), ['message' => 'ممكن نصيحة؟', 'history' => $history])
            ->assertOk()->assertExactJson(['reply' => 'رد Gemini']);
    }
}
