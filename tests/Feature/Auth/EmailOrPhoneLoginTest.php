<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Services\Auth\LoginIdentity;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class EmailOrPhoneLoginTest extends TestCase
{
    use RefreshDatabase;

    private function clearLogin(string $identity): void
    {
        RateLimiter::clear(hash('sha256', LoginIdentity::canonical($identity)).'|127.0.0.1');
    }

    public function test_page_accepts_both_identifiers_and_keeps_password_autofill_and_mobile_font(): void
    {
        $this->get(route('login'))->assertOk()->assertSee('البريد الإلكتروني أو رقم الهاتف')
            ->assertSee('type="text" name="email"', false)->assertSee('autocomplete="username"', false)
            ->assertSee('autocomplete="current-password"', false)->assertSee('font-size:16px', false);
    }

    public function test_email_login_is_case_insensitive_and_trimmed(): void
    {
        $user = User::factory()->create(['email' => 'Member@Example.test']);
        $this->post(route('login'), ['email' => '  MEMBER@example.TEST  ', 'password' => 'password'])->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_supported_egyptian_formats_match_existing_local_phone(): void
    {
        $user = User::factory()->create(['phone' => '01012345678']);
        foreach (['01012345678', '+201012345678', '00201012345678', '201012345678', '010 1234 5678', '+20 (10) 1234-5678', '٠١٠١٢٣٤٥٦٧٨', '۰۱۰۱۲۳۴۵۶۷۸'] as $identity) {
            $this->clearLogin($identity);
            $this->post(route('login'), ['email' => $identity, 'password' => 'password'])->assertRedirect(route('dashboard'));
            $this->assertAuthenticatedAs($user);
            $this->post(route('logout'));
        }
    }

    public function test_legacy_formatted_international_and_arabic_stored_phones_work_without_changing_account(): void
    {
        foreach (['+20 (10) 1234-5678', '00201012345678', '٠١٠ ١٢٣٤ ٥٦٧٨'] as $stored) {
            $user = User::factory()->create(['phone' => $stored]);
            $this->post(route('login'), ['email' => '01012345678', 'password' => 'password'])->assertRedirect(route('dashboard'));
            $this->assertAuthenticatedAs($user);
            $this->assertSame($stored, $user->fresh()->phone);
            $this->post(route('logout'));
            $user->delete();
        }
    }

    public function test_international_phone_and_super_admin_redirect_and_remember_me_are_preserved(): void
    {
        $user = User::factory()->create(['phone' => '+966501234567', 'role' => 'super_admin']);
        $response = $this->post(route('login'), ['email' => '00966 50 123 4567', 'password' => 'password', 'remember' => 'on']);
        $response->assertRedirect(route('admin.dashboard'));
        $response->assertCookie(auth()->guard('web')->getRecallerName());
        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->remember_token);
    }

    public function test_wrong_password_unknown_phone_and_invalid_identifiers_fail_without_account_disclosure(): void
    {
        User::factory()->create(['phone' => '01012345678']);
        foreach (['01012345678', '01112345678', 'not-a-number', "+201012345678' OR 1=1 --", 'a@b.test'] as $identity) {
            $response = $this->from(route('login'))->post(route('login'), ['email' => $identity, 'password' => 'wrong-password']);
            $response->assertRedirect(route('login'))->assertSessionHasErrors('email');
            $response->assertSessionHasInput('email', $identity)->assertSessionMissing('_old_input.password');
            $this->assertGuest();
            $this->assertSame('البريد الإلكتروني أو رقم الهاتف أو كلمة المرور غير صحيحة.', session('errors')->first('email'));
        }
    }

    public function test_equivalent_phone_collisions_fail_closed_even_with_correct_password_but_email_still_works(): void
    {
        $user = User::factory()->create(['phone' => '01012345678']);
        User::factory()->create(['phone' => '+201012345678']);
        $this->post(route('login'), ['email' => '01012345678', 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->post(route('login'), ['email' => $user->email, 'password' => 'password'])->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_inactive_member_cannot_login_by_phone(): void
    {
        User::factory()->create(['phone' => '01012345678', 'is_active' => false]);
        $this->post(route('login'), ['email' => '+201012345678', 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->assertStringContainsString('معطل', session('errors')->first('email'));
    }

    public function test_phone_format_changes_do_not_bypass_five_attempt_throttle_and_emit_lockout(): void
    {
        Event::fake([Lockout::class]);
        User::factory()->create(['phone' => '01012345678']);
        $this->clearLogin('01012345678');
        foreach (['01012345678', '+201012345678', '00201012345678', '٠١٠١٢٣٤٥٦٧٨', '010 1234 5678'] as $identity) {
            $this->post(route('login'), ['email' => $identity, 'password' => 'wrong'])->assertSessionHasErrors('email');
        }
        $this->post(route('login'), ['email' => '201012345678', 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->assertStringContainsString('محاولات دخول كثيرة', session('errors')->first('email'));
        Event::assertDispatched(Lockout::class);
        $this->clearLogin('01012345678');
    }

    public function test_missing_fields_and_oversized_identifier_are_rejected(): void
    {
        $this->post(route('login'), [])->assertSessionHasErrors(['email', 'password']);
        $this->post(route('login'), ['email' => str_repeat('1', 255), 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
