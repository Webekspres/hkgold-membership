<?php

use App\Http\Controllers\WebPush\RegisterWebPushTokenController;
use Illuminate\Support\Facades\Route;

Route::redirect('/login', '/app/login');

Route::get('/', function () {
    return view('welcome');
});

Route::get('/firebase-messaging-sw.js', function () {
    $client = config('notifications.firebase_client');

    if (! filled($client['api_key'] ?? null)) {
        abort(404);
    }

    $config = [
        'apiKey' => $client['api_key'],
        'authDomain' => $client['auth_domain'],
        'projectId' => $client['project_id'],
        'storageBucket' => $client['storage_bucket'],
        'messagingSenderId' => $client['messaging_sender_id'],
        'appId' => $client['app_id'],
        'measurementId' => $client['measurement_id'] ?? null,
    ];

    return response()
        ->view('firebase.firebase-messaging-sw', ['config' => $config])
        ->header('Content-Type', 'application/javascript; charset=UTF-8')
        ->header('Service-Worker-Allowed', '/')
        ->header('Cache-Control', 'no-cache, no-store, must-revalidate');
})->name('firebase.messaging-sw');

Route::middleware(['web', 'auth'])->prefix('app')->group(function (): void {
    Route::post('/web-push/register', RegisterWebPushTokenController::class)
        ->name('web-push.register');
});
