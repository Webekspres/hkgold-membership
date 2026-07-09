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

];
