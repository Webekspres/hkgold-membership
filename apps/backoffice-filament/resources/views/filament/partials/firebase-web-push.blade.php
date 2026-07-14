@php
    $firebaseConfigured = filled(config('notifications.firebase_client.api_key'))
        && filled(config('notifications.webpush.vapid_public_key'));
@endphp

@if ($firebaseConfigured && auth()->check())
    <script>
        window.__FIREBASE_CLIENT__ = @json(config('notifications.firebase_client'));
        window.__WEB_PUSH_REGISTER_URL__ = @json(route('web-push.register'));
        window.__FIREBASE_MESSAGING_SW_URL__ = @json(route('firebase.messaging-sw'));
        window.__CSRF_TOKEN__ = @json(csrf_token());
    </script>

    @vite('resources/js/firebase-messaging.js')
@endif

@if ($firebaseConfigured)
    <div
        id="web-push-prompt"
        role="status"
        aria-live="polite"
        style="display: none; position: fixed; bottom: 1rem; right: 1rem; z-index: 9999; width: min(24rem, calc(100vw - 2rem)); padding: 1rem; border-radius: 0.75rem; border: 1px solid #fcd34d; background: #fffbeb; color: #111827; font-size: 0.875rem; line-height: 1.4; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);"
    >
        <p id="web-push-prompt-title" style="margin: 0; font-weight: 600;"></p>
        <p id="web-push-prompt-body" style="margin: 0.5rem 0 0; color: #374151;"></p>
        <button
            id="web-push-prompt-action"
            type="button"
            style="display: none; margin-top: 0.75rem; padding: 0.5rem 0.75rem; border: none; border-radius: 0.5rem; font-size: 0.875rem; font-weight: 600; color: #111827; cursor: pointer; background: linear-gradient(135deg, #f5c842, #e8a020);"
        >
            Aktifkan notifikasi
        </button>
    </div>
@endif
