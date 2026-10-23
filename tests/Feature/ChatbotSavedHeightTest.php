<?php

namespace Tests\Feature;

use App\Models\BodyMeasurement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatbotSavedHeightTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_ask_for_latest_saved_height(): void
    {
        $user = User::factory()->create();
        BodyMeasurement::create(['user_id' => $user->id, 'date' => '2026-10-01', 'height' => 172, 'weight' => 70]);
        BodyMeasurement::create(['user_id' => $user->id, 'date' => '2026-10-20', 'height' => 175, 'weight' => 71]);
        $other = User::factory()->create();
        BodyMeasurement::create(['user_id' => $other->id, 'date' => '2026-10-23', 'height' => 190, 'weight' => 85]);

        $this->actingAs($user)->postJson(route('chatbot.send'), ['message' => 'طولي كام'])
            ->assertOk()->assertJsonPath('reply', 'آخر طول مسجل لك: 175 سم (بتاريخ 2026-10-20).');
    }

    public function test_missing_height_is_reported_without_guessing(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->postJson(route('chatbot.send'), ['message' => 'طولي كام'])
            ->assertOk()->assertJsonPath('reply', 'لا يوجد طول مسجل في قياسات حسابك حتى الآن. يمكنك تسجيله من صفحة وزن الجسم والقياسات.');
    }
}
