import { initializeApp } from 'firebase/app';
import { getMessaging, getToken, isSupported, onMessage } from 'firebase/messaging';

const STORAGE_KEY = 'hkgold_web_push_device_uuid';
const LOG_PREFIX = '[HK Gold Web Push]';

function readClientConfig() {
    return window.__FIREBASE_CLIENT__ ?? null;
}

function readRegisterUrl() {
    return window.__WEB_PUSH_REGISTER_URL__ ?? null;
}

function readServiceWorkerUrl() {
    return window.__FIREBASE_MESSAGING_SW_URL__ ?? '/firebase-messaging-sw.js';
}

function readCsrfToken() {
    return window.__CSRF_TOKEN__ ?? document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
}

function logInfo(message, context = undefined) {
    if (context === undefined) {
        console.info(LOG_PREFIX, message);

        return;
    }

    console.info(LOG_PREFIX, message, context);
}

function logWarn(message, context = undefined) {
    if (context === undefined) {
        console.warn(LOG_PREFIX, message);

        return;
    }

    console.warn(LOG_PREFIX, message, context);
}

function isLocalhostHostname(hostname) {
    return hostname === 'localhost' || hostname === '127.0.0.1' || hostname === '[::1]';
}

function hasSecurePushContext() {
    return window.isSecureContext || isLocalhostHostname(window.location.hostname);
}

function getPromptElements() {
    return {
        root: document.getElementById('web-push-prompt'),
        title: document.getElementById('web-push-prompt-title'),
        body: document.getElementById('web-push-prompt-body'),
        action: document.getElementById('web-push-prompt-action'),
    };
}

function showPrompt({ title, body, showAction = false, onAction = null, autoHideMs = null }) {
    const { root, title: titleEl, body: bodyEl, action } = getPromptElements();

    if (! root || ! titleEl || ! bodyEl || ! action) {
        logWarn('Elemen prompt web push tidak ditemukan di halaman.');

        return;
    }

    titleEl.textContent = title;
    bodyEl.textContent = body;
    action.style.display = showAction ? 'inline-block' : 'none';
    root.style.display = 'block';

    action.onclick = null;

    if (showAction && typeof onAction === 'function') {
        action.onclick = () => onAction();
    }

    if (typeof autoHideMs === 'number' && autoHideMs > 0) {
        window.setTimeout(() => hidePrompt(), autoHideMs);
    }
}

function hidePrompt() {
    const { root } = getPromptElements();

    if (root) {
        root.style.display = 'none';
    }
}

function getOrCreateDeviceUuid() {
    const existing = localStorage.getItem(STORAGE_KEY);

    if (existing) {
        return existing;
    }

    const deviceUuid = crypto.randomUUID();
    localStorage.setItem(STORAGE_KEY, deviceUuid);

    return deviceUuid;
}

async function registerTokenWithBackend(token) {
    const registerUrl = readRegisterUrl();

    if (! registerUrl) {
        throw new Error('URL registrasi token tidak tersedia.');
    }

    const response = await fetch(registerUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': readCsrfToken(),
        },
        credentials: 'same-origin',
        body: JSON.stringify({
            token,
            device_uuid: getOrCreateDeviceUuid(),
        }),
    });

    if (! response.ok) {
        throw new Error(`Registrasi token gagal (${response.status}).`);
    }
}

let messagingInstance = null;
let serviceWorkerRegistration = null;
let bootstrapPromise = null;

async function ensureMessaging() {
    const config = readClientConfig();

    if (! config?.api_key || ! config?.vapid_public_key) {
        throw new Error('Konfigurasi Firebase client belum lengkap.');
    }

    if (! ('Notification' in window) || ! ('serviceWorker' in navigator)) {
        throw new Error('Browser tidak mendukung notifikasi web.');
    }

    if (! hasSecurePushContext()) {
        throw new Error('Push hanya bisa diaktifkan lewat HTTPS atau localhost.');
    }

    const supported = await isSupported();

    if (! supported) {
        throw new Error('Firebase Messaging tidak didukung di browser ini.');
    }

    if (! messagingInstance) {
        const app = initializeApp({
            apiKey: config.api_key,
            authDomain: config.auth_domain,
            projectId: config.project_id,
            storageBucket: config.storage_bucket,
            messagingSenderId: config.messaging_sender_id,
            appId: config.app_id,
            measurementId: config.measurement_id || undefined,
        });

        messagingInstance = getMessaging(app);

        onMessage(messagingInstance, (payload) => {
            const title = payload.notification?.title ?? 'Notifikasi';
            const body = payload.notification?.body ?? '';

            if (Notification.permission === 'granted') {
                new Notification(title, {
                    body,
                    icon: '/favicon.ico',
                });
            }
        });
    }

    if (! serviceWorkerRegistration) {
        serviceWorkerRegistration = await navigator.serviceWorker.register(readServiceWorkerUrl(), {
            scope: '/',
        });
    }

    return {
        messaging: messagingInstance,
        registration: serviceWorkerRegistration,
        config,
    };
}

async function enableWebPush({ requestPermission = true } = {}) {
    const { messaging, registration, config } = await ensureMessaging();

    if (requestPermission && Notification.permission === 'default') {
        const permission = await Notification.requestPermission();

        if (permission !== 'granted') {
            throw new Error('Izin notifikasi ditolak.');
        }
    }

    if (Notification.permission !== 'granted') {
        throw new Error('Izin notifikasi belum diberikan.');
    }

    const token = await getToken(messaging, {
        vapidKey: config.vapid_public_key,
        serviceWorkerRegistration: registration,
    });

    if (! token) {
        throw new Error('Gagal mendapatkan token FCM.');
    }

    await registerTokenWithBackend(token);
    logInfo('Token web push terdaftar.');

    showPrompt({
        title: 'Notifikasi push aktif',
        body: 'Browser ini sudah terdaftar untuk menerima notifikasi admin.',
        showAction: false,
        autoHideMs: 5000,
    });

    return token;
}

function showEnvironmentPrompt() {
    if (! readClientConfig()) {
        logWarn('Konfigurasi Firebase client kosong, web push dilewati.');

        return;
    }

    if (! hasSecurePushContext()) {
        showPrompt({
            title: 'Push belum bisa diaktifkan',
            body: `Browser memblokir notifikasi di ${window.location.origin}. Buka lewat http://localhost:8800/app (bukan IP LAN) atau gunakan HTTPS.`,
            showAction: false,
        });

        logWarn('Konteks tidak aman untuk web push.', { origin: window.location.origin });

        return;
    }

    if (Notification.permission === 'denied') {
        showPrompt({
            title: 'Notifikasi diblokir',
            body: 'Buka ikon gembok di address bar → Site settings → Notifications → Allow, lalu muat ulang halaman.',
            showAction: false,
        });

        logWarn('Izin notifikasi sudah ditolak sebelumnya.');

        return;
    }

    if (Notification.permission === 'granted') {
        return;
    }

    showPrompt({
        title: 'Aktifkan notifikasi push',
        body: 'Klik tombol di bawah, lalu pilih Allow pada dialog browser.',
        showAction: true,
        onAction: async () => {
            try {
                await enableWebPush({ requestPermission: true });
            } catch (error) {
                const message = error instanceof Error ? error.message : 'Gagal mengaktifkan notifikasi.';
                logWarn(message, error);
                showPrompt({
                    title: 'Gagal mengaktifkan notifikasi',
                    body: message,
                    showAction: false,
                });
            }
        },
    });
}

async function bootstrapWebPush() {
    if (! readClientConfig()) {
        return;
    }

    if (! ('Notification' in window)) {
        logWarn('Notification API tidak tersedia di browser ini.');

        return;
    }

    if (Notification.permission === 'granted') {
        try {
            await enableWebPush({ requestPermission: false });
        } catch (error) {
            logWarn('Gagal menyinkronkan token web push yang sudah diizinkan.', error);
            showEnvironmentPrompt();
        }

        return;
    }

    showEnvironmentPrompt();
}

function scheduleBootstrap() {
    if (bootstrapPromise) {
        return bootstrapPromise;
    }

    bootstrapPromise = bootstrapWebPush()
        .catch((error) => {
            logWarn('Inisialisasi web push gagal.', error);
            showEnvironmentPrompt();
        })
        .finally(() => {
            bootstrapPromise = null;
        });

    return bootstrapPromise;
}

window.HkGoldWebPush = {
    enable: enableWebPush,
    bootstrap: scheduleBootstrap,
};

scheduleBootstrap();

document.addEventListener('livewire:navigated', () => {
    scheduleBootstrap();
});
