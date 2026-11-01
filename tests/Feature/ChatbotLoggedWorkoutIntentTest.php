<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\ChatbotMemberDataService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatbotLoggedWorkoutIntentTest extends TestCase
{
    use RefreshDatabase;

    public function test_workout_questions_use_the_authenticated_members_records(): void
    {
        $member = User::factory()->create();
        $answer = app(ChatbotMemberDataService::class)->answer($member, 'أنا اتمرنت اي النهارده؟');
        $this->assertNotNull($answer);
        $this->assertStringContainsString('لا توجد جلسات', $answer);
    }

    public function test_training_plan_question_uses_saved_programs(): void
    {
        $member = User::factory()->create();
        $answer = app(ChatbotMemberDataService::class)->answer($member, 'ايه جدول تماريني الحالي؟');
        $this->assertNotNull($answer);
        $this->assertStringContainsString('برنامج تمارين', $answer);
    }
}
