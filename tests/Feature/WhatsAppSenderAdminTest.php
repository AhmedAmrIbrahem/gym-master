<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhatsAppSenderAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'gym_reminders.provider_url' => 'http://provider.test',
            'gym_reminders.provider_token' => str_repeat('s', 32),
            'gym_reminders.admin_token' => str_repeat('m', 32),
        ]);
        Http::preventStrayRequests();
    }

    public function test_guest_cannot_view_or_manage_sender(): void
    {
        Http::fake();
        foreach (['admin.whatsapp.index', 'admin.whatsapp.status'] as $route) {
            $this->get(route($route))->assertRedirect(route('login'));
        }
        foreach (['admin.whatsapp.connect', 'admin.whatsapp.disconnect'] as $route) {
            $this->post(route($route))->assertRedirect(route('login'));
        }
        Http::assertNothingSent();
    }

    public function test_subscriber_cannot_access_sender_even_by_direct_request_or_forged_role(): void
    {
        Http::fake();
        $this->actingAs(User::factory()->create(['role' => 'user']));
        foreach (['admin.whatsapp.index', 'admin.whatsapp.status'] as $route) {
            $this->getJson(route($route))->assertForbidden();
        }
        foreach (['admin.whatsapp.connect', 'admin.whatsapp.disconnect'] as $route) {
            $this->postJson(route($route), ['role' => 'super_admin', 'sender_phone' => '+201112345678'])->assertForbidden();
        }
        $this->get(route('profile.edit'))->assertOk()->assertDontSee(route('admin.whatsapp.index'));
        Http::assertNothingSent();
    }

    public function test_inactive_super_admin_cannot_manage_sender(): void
    {
        Http::fake();
        $user = User::factory()->create(['role' => 'super_admin', 'is_active' => false]);
        $this->actingAs($user)->getJson(route('admin.whatsapp.status'))->assertForbidden();
        $this->actingAs($user)->postJson(route('admin.whatsapp.disconnect'))->assertForbidden();
        Http::assertNothingSent();
    }

    public function test_super_admin_sees_actual_linked_number_and_only_sanitized_private_status(): void
    {
        Http::fake(['provider.test/session' => Http::response([
            'status' => 'ready', 'sender_phone' => '+201012345678', 'qr_data_url' => 'data:image/png;base64,c2VjcmV0',
            'token' => 'must-not-be-returned',
        ])]);
        $this->actingAs(User::factory()->create(['role' => 'super_admin']));
        $this->get(route('admin.whatsapp.index'))->assertOk()->assertSee('إدارة رقم إرسال واتساب')
            ->assertSee(route('admin.whatsapp.connect'))->assertDontSee('must-not-be-returned');
        $response = $this->getJson(route('admin.whatsapp.status'))->assertOk()->assertExactJson([
            'status' => 'ready', 'sender_phone' => '+201012345678', 'qr_data_url' => null,
        ]);
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        Http::assertSent(fn ($request) => $request->url() === 'http://provider.test/session'
            && $request->hasHeader('Authorization', 'Bearer '.str_repeat('m', 32)));
    }

    public function test_qr_is_available_to_super_admin_only_and_malicious_image_schemes_are_dropped(): void
    {
        Http::fake(['provider.test/session' => Http::sequence()
            ->push(['status' => 'qr', 'qr_data_url' => 'data:image/png;base64,cXI=', 'sender_phone' => null])
            ->push(['status' => 'qr', 'qr_data_url' => 'javascript:alert(1)', 'sender_phone' => '+201012345678'])]);
        $this->actingAs(User::factory()->create(['role' => 'super_admin']));
        $this->getJson(route('admin.whatsapp.status'))->assertOk()->assertJsonPath('qr_data_url', 'data:image/png;base64,cXI=');
        $this->getJson(route('admin.whatsapp.status'))->assertOk()->assertJsonPath('qr_data_url', null)->assertJsonPath('sender_phone', null);
    }

    public function test_super_admin_can_connect_and_disconnect_without_forwarding_submitted_number(): void
    {
        Http::fake(['provider.test/*' => Http::response(['status' => 'starting'], 202)]);
        $this->actingAs(User::factory()->create(['role' => 'super_admin']));
        $this->post(route('admin.whatsapp.connect'), ['phone' => '+19999999999'])->assertRedirect(route('admin.whatsapp.index'))->assertSessionHas('success');
        $this->post(route('admin.whatsapp.disconnect'))->assertRedirect(route('admin.whatsapp.index'))->assertSessionHas('success');
        Http::assertSentCount(2);
        Http::assertSent(fn ($request) => $request->url() === 'http://provider.test/session/connect' && $request->data() === []);
        Http::assertSent(fn ($request) => $request->url() === 'http://provider.test/session/disconnect' && $request->method() === 'POST');
    }

    public function test_provider_failure_does_not_claim_disconnect_or_expose_errors(): void
    {
        Http::fake(['provider.test/*' => Http::sequence()->push(['status' => 'busy', 'error' => 'sensitive'], 409)->pushFailedConnection()]);
        $this->actingAs(User::factory()->create(['role' => 'super_admin']));
        $this->post(route('admin.whatsapp.disconnect'))->assertSessionHas('error')->assertSessionMissing('success');
        $this->getJson(route('admin.whatsapp.status'))->assertOk()->assertExactJson([
            'status' => 'unavailable', 'qr_data_url' => null, 'sender_phone' => null,
        ]);
    }

    public function test_management_credentials_must_be_separate_from_sender_credential(): void
    {
        Http::fake();
        config(['gym_reminders.admin_token' => config('gym_reminders.provider_token')]);
        $this->actingAs(User::factory()->create(['role' => 'super_admin']));
        $this->getJson(route('admin.whatsapp.status'))->assertJsonPath('status', 'unavailable');
        $this->post(route('admin.whatsapp.connect'))->assertSessionHas('error');
        Http::assertNothingSent();
    }

    public function test_status_polling_cannot_exhaust_management_limits_and_admin_cannot_save_member_reminder(): void
    {
        Http::fake(['provider.test/*' => Http::response(['status' => 'idle'])]);
        $this->actingAs(User::factory()->create(['role' => 'super_admin', 'phone' => '+201012345678']));
        for ($i = 0; $i < 30; $i++) {
            $this->getJson(route('admin.whatsapp.status'))->assertOk();
        }
        $this->getJson(route('admin.whatsapp.status'))->assertStatus(429);
        $this->post(route('admin.whatsapp.connect'))->assertRedirect(route('admin.whatsapp.index'));
        $this->post(route('admin.whatsapp.disconnect'))->assertRedirect(route('admin.whatsapp.index'));
        $this->put(route('profile.gym-reminder.update'), [
            'enabled' => 0, 'days' => array_fill(0, 7, ['enabled' => 1, 'time' => '18:00']),
        ])->assertForbidden();
    }

    public function test_connect_limit_is_enforced_independently_of_status_and_disconnect(): void
    {
        Http::fake(['provider.test/*' => Http::response(['status' => 'idle'])]);
        $this->actingAs(User::factory()->create(['role' => 'super_admin']));
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('admin.whatsapp.connect'))->assertRedirect(route('admin.whatsapp.index'));
        }
        $this->post(route('admin.whatsapp.connect'))->assertStatus(429);
        $this->getJson(route('admin.whatsapp.status'))->assertOk();
        $this->post(route('admin.whatsapp.disconnect'))->assertRedirect(route('admin.whatsapp.index'));
    }
}
