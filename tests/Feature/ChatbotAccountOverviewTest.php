<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\ChatbotMemberDataService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatbotAccountOverviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_account_overview_is_scoped_to_the_requested_member(): void
    {
        $member = User::factory()->create(['name' => 'Member Alpha']);
        $other = User::factory()->create(['name' => 'Member Beta']);

        $service = app(ChatbotMemberDataService::class);
        $reply = $service->answer($member, 'انت تعرف عني ايه؟');

        $this->assertNotNull($reply);
        $this->assertStringContainsString('Member Alpha', $reply);
        $this->assertStringNotContainsString('Member Beta', $reply);
    }

    public function test_colloquial_weight_and_set_questions_are_handled_locally(): void
    {
        $member = User::factory()->create();
        $service = app(ChatbotMemberDataService::class);

        foreach (['انا لعبت كام مجموعة النهارده؟', 'شلت كام النهارده؟', 'ايه تفاصيل جدولي؟'] as $question) {
            $this->assertNotNull($service->answer($member, $question));
        }
    }
}
