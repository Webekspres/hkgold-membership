<?php

declare(strict_types=1);

return [

    'queue' => env('NOTIFICATIONS_QUEUE', 'notifications'),

    'fcm' => [
        'project_id' => env('FCM_PROJECT_ID'),
        'credentials_path' => env('FCM_CREDENTIALS_PATH'),
    ],

    'webpush' => [
        'vapid_public_key' => env('WEBPUSH_VAPID_PUBLIC_KEY'),
        'vapid_private_key' => env('WEBPUSH_VAPID_PRIVATE_KEY'),
        'vapid_subject' => env('WEBPUSH_VAPID_SUBJECT'),
    ],

    'firebase_client' => [
        'api_key' => env('FIREBASE_API_KEY'),
        'auth_domain' => env('FIREBASE_AUTH_DOMAIN'),
        'project_id' => env('FIREBASE_PROJECT_ID'),
        'storage_bucket' => env('FIREBASE_STORAGE_BUCKET'),
        'messaging_sender_id' => env('FIREBASE_MESSAGING_SENDER_ID'),
        'app_id' => env('FIREBASE_APP_ID'),
        'measurement_id' => env('FIREBASE_MEASUREMENT_ID'),
        'vapid_public_key' => env('WEBPUSH_VAPID_PUBLIC_KEY'),
    ],

];
