<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\ChatbotMemberDataService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatbotColloquialIntentTest extends TestCase
{
    use RefreshDatabase;

    public function test_account_overview_handles_short_colloquial_questions_without_gemini(): void
    {
        $member = User::factory()->create(['name' => 'Member Alpha']);
        $other = User::factory()->create(['name' => 'Member Beta']);
        $service = app(ChatbotMemberDataService::class);

        foreach (['تعرف عني اي', 'تعرف عني ايه', 'انت تعرف عني أي؟', 'اعرف عني اي'] as $question) {
            $response = $service->answer($member, $question);
            $this->assertNotNull($response, $question);
            $this->assertStringContainsString('Member Alpha', $response);
            $this->assertStringNotContainsString('Member Beta', $response);
        }
    }

    public function test_current_program_questions_without_final_ha_are_handled_locally(): void
    {
        $member = User::factory()->create();
        $service = app(ChatbotMemberDataService::class);

        foreach (['اي جدول تماريني', 'أي جدول تماريني', 'ايه جدول تماريني', 'اي جدول تمريني الحالي'] as $question) {
            $response = $service->answer($member, $question);
            $this->assertNotNull($response, $question);
            $this->assertStringContainsString('برنامج', $response);
        }
    }
}
