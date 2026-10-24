<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\ChatbotMemberDataService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatbotProfileScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_questions_use_only_the_supplied_member(): void
    {
        $first = User::factory()->create(['name' => 'أحمد الأول', 'email' => 'first@example.com']);
        $second = User::factory()->create(['name' => 'محمد الثاني', 'email' => 'second@example.com']);
        $service = app(ChatbotMemberDataService::class);
        $this->assertStringContainsString('first@example.com', $service->answer($first, 'ايميلي ايه'));
        $this->assertStringNotContainsString('second@example.com', $service->answer($first, 'ايميلي ايه'));
        $this->assertStringContainsString('محمد الثاني', $service->answer($second, 'اسمي ايه'));
    }
}
