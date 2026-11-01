<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\ChatbotMemberDataService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatbotTodayMealsQuestionTest extends TestCase
{
    use RefreshDatabase;

    public function test_colloquial_today_meals_question_uses_saved_nutrition_data(): void
    {
        $user = User::factory()->create();
        $service = app(ChatbotMemberDataService::class);

        foreach (['انا كلت اي النهارده', 'أنا أكلت إيه النهارده', 'كلت ايه اليوم'] as $question) {
            $reply = $service->answer($user, $question);
            $this->assertNotNull($reply);
            $this->assertStringContainsString('لا توجد أطعمة مسجلة اليوم', $reply);
        }
    }
}
