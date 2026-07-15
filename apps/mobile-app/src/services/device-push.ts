import * as Device from 'expo-device';
import { isRunningInExpoGo } from 'expo';
import { Platform } from 'react-native';
import * as SecureStore from 'expo-secure-store';

import { apiClient } from '@/lib/api-client';
import type { ApiEnvelope } from '@/types/auth';

const PUSH_TOKEN_KEY = 'hkgold.pushToken';

/** Remote push (FCM) needs a dev/prod native build — Expo Go Android throws on import side-effects. */
function pushSupported(): boolean {
  return !isRunningInExpoGo();
}

async function loadNotifications() {
  // Dynamic import keeps Expo Go from evaluating DevicePushTokenAutoRegistration.fx
  return import('expo-notifications');
}

async function ensurePermission(
  Notifications: typeof import('expo-notifications'),
): Promise<boolean> {
  if (!Device.isDevice) {
    return false;
  }

  const current = await Notifications.getPermissionsAsync();
  if (current.granted || current.ios?.status === Notifications.IosAuthorizationStatus.PROVISIONAL) {
    return true;
  }

  const requested = await Notifications.requestPermissionsAsync();
  return (
    requested.granted ||
    requested.ios?.status === Notifications.IosAuthorizationStatus.PROVISIONAL
  );
}

/** Register native FCM/APNs device token with API (same token FcmPushDriver expects). */
export async function registerPushToken(): Promise<string | null> {
  if (!pushSupported()) {
    return null;
  }

  const Notifications = await loadNotifications();

  Notifications.setNotificationHandler({
    handleNotification: async () => ({
      shouldShowBanner: true,
      shouldShowList: true,
      shouldPlaySound: true,
      shouldSetBadge: false,
    }),
  });

  const allowed = await ensurePermission(Notifications);
  if (!allowed) {
    return null;
  }

  if (Platform.OS === 'android') {
    await Notifications.setNotificationChannelAsync('default', {
      name: 'default',
      importance: Notifications.AndroidImportance.DEFAULT,
    });
  }

  const deviceToken = await Notifications.getDevicePushTokenAsync();
  const token = typeof deviceToken.data === 'string' ? deviceToken.data : null;
  if (!token) {
    return null;
  }

  await apiClient.post<ApiEnvelope<{ id: string }>>('/api/device/push-token', {
    token,
    deviceUuid: Device.modelId ?? Device.modelName ?? undefined,
  });

  await SecureStore.setItemAsync(PUSH_TOKEN_KEY, token);
  return token;
}

export async function unregisterPushToken(): Promise<void> {
  const token = await SecureStore.getItemAsync(PUSH_TOKEN_KEY);
  if (!token) {
    return;
  }

  try {
    await apiClient.delete('/api/device/push-token', { data: { token } });
  } catch {
    // best-effort revoke
  }

  await SecureStore.deleteItemAsync(PUSH_TOKEN_KEY).catch(() => undefined);
}

export async function getStoredPushToken(): Promise<string | null> {
  return SecureStore.getItemAsync(PUSH_TOKEN_KEY);
}
