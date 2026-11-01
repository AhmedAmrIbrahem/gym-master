<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InactiveAccountSessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_disabling_an_account_invalidates_a_session_that_was_already_signed_in(): void
    {
        $member = User::factory()->create(['is_active' => true]);

        $this->actingAs($member)->get(route('dashboard'))->assertOk();
        $member->update(['is_active' => false]);

        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_disabled_member_cannot_use_catalog_search_even_with_existing_session(): void
    {
        $member = User::factory()->create(['is_active' => true]);
        $this->actingAs($member);
        $member->update(['is_active' => false]);

        $this->getJson(route('nutrition.searchFoods'))->assertForbidden();
        $this->assertGuest();
    }
}
