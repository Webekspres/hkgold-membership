importScripts('https://www.gstatic.com/firebasejs/12.16.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/12.16.0/firebase-messaging-compat.js');

firebase.initializeApp(@json($config));

const messaging = firebase.messaging();

messaging.onBackgroundMessage((payload) => {
    const title = payload.notification?.title ?? 'Notifikasi';
    const options = {
        body: payload.notification?.body ?? '',
        icon: '/favicon.ico',
        data: payload.data ?? {},
    };

    self.registration.showNotification(title, options);
});
