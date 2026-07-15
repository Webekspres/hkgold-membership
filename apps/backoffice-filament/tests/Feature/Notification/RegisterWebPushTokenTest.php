<?php

declare(strict_types=1);

use App\Enums\DevicePushTokenPlatform;
use App\Models\DevicePushToken;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    // Laravel 13 web stack uses PreventRequestForgery; feature tests don't carry a CSRF cookie.
    $this->withoutMiddleware(PreventRequestForgery::class);
});

test('staff dapat mendaftarkan token web push', function (): void {
    $user = User::factory()->administrator()->create();

    $response = $this->actingAs($user)->postJson(route('web-push.register'), [
        'token' => 'fcm_web_token_'.str_repeat('x', 40),
        'device_uuid' => 'browser-device-001',
    ]);

    $response->assertOk()
        ->assertJson(['message' => 'Token web push terdaftar.']);

    $this->assertDatabaseHas('device_push_tokens', [
        'user_id' => $user->id,
        'platform' => DevicePushTokenPlatform::Web->value,
        'token' => 'fcm_web_token_'.str_repeat('x', 40),
        'device_uuid' => 'browser-device-001',
        'revoked_at' => null,
    ]);
});

test('token web push yang sama diperbarui bukan diduplikasi', function (): void {
    $user = User::factory()->administrator()->create();
    $token = 'fcm_web_token_'.str_repeat('y', 40);

    DevicePushToken::factory()->create([
        'user_id' => $user->id,
        'platform' => DevicePushTokenPlatform::Web,
        'token' => $token,
        'revoked_at' => now(),
    ]);

    $response = $this->actingAs($user)->postJson(route('web-push.register'), [
        'token' => $token,
        'device_uuid' => 'browser-device-002',
    ]);

    $response->assertOk();

    expect(DevicePushToken::query()->where('user_id', $user->id)->where('token', $token)->count())->toBe(1);

    $this->assertDatabaseHas('device_push_tokens', [
        'user_id' => $user->id,
        'token' => $token,
        'revoked_at' => null,
        'device_uuid' => 'browser-device-002',
    ]);
});

test('guest tidak bisa mendaftarkan token web push', function (): void {
    $this->post(route('web-push.register'), [
        'token' => 'fcm_web_token_'.str_repeat('z', 40),
    ])->assertRedirect('/app/login');
});
