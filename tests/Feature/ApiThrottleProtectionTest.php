<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiThrottleProtectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_chatbot_request_rate_limit_rejects_abuse_per_account(): void
    {
        $member = User::factory()->create();
        $this->actingAs($member);

        // Invalid payload stays inside Laravel validation, so the test never
        // calls Gemini and cannot spend any API credits.
        for ($attempt = 0; $attempt < 30; $attempt++) {
            $this->postJson(route('chatbot.send'), [])->assertUnprocessable();
        }

        $this->postJson(route('chatbot.send'), [])->assertStatus(429);
    }

    public function test_anonymous_user_cannot_call_chatbot(): void
    {
        $this->postJson(route('chatbot.send'), ['message' => 'hello'])->assertUnauthorized();
    }
}
