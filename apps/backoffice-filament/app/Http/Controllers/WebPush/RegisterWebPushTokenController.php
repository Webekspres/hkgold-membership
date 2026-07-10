<?php

declare(strict_types=1);

namespace App\Http\Controllers\WebPush;

use App\Enums\DevicePushTokenPlatform;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterWebPushTokenRequest;
use App\Services\Notification\DevicePushTokenRegistry;
use Illuminate\Http\JsonResponse;

class RegisterWebPushTokenController extends Controller
{
    public function __invoke(
        RegisterWebPushTokenRequest $request,
        DevicePushTokenRegistry $tokenRegistry,
    ): JsonResponse {
        $user = $request->user();

        if ($user === null) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $tokenRegistry->register(
            user: $user,
            token: (string) $request->validated('token'),
            platform: DevicePushTokenPlatform::Web,
            deviceUuid: $request->validated('device_uuid'),
        );

        return response()->json(['message' => 'Token web push terdaftar.']);
    }
}
