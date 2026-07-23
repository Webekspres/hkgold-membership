import { useQueryClient } from '@tanstack/react-query';
import { isRunningInExpoGo } from 'expo';
import { useRouter } from 'expo-router';
import { useEffect, useRef } from 'react';

import { handleRedeemPushPayload } from '@/lib/notifications/handle-redeem-push';
import { registerPushToken } from '@/services/device-push';

function navigateFromNotificationData(
  router: ReturnType<typeof useRouter>,
  data: Record<string, unknown> | undefined,
  queryClient: ReturnType<typeof useQueryClient>,
): void {
  const route = handleRedeemPushPayload(data);
  if (!route) {
    return;
  }

  void queryClient.invalidateQueries({ queryKey: ['redeem', 'active'] });
  void queryClient.invalidateQueries({ queryKey: ['redeem', 'history'] });
  router.replace(route);
}

/** Register FCM token when logged in + wire tap handlers for redeem invoice deep link. */
export function useRegisterPushToken(enabled: boolean): void {
  const router = useRouter();
  const queryClient = useQueryClient();
  const registered = useRef(false);

  useEffect(() => {
    // Expo Go Android: importing expo-notifications throws (SDK 53+). Skip remote push.
    if (!enabled || isRunningInExpoGo()) {
      registered.current = false;
      return;
    }

    if (registered.current) {
      return;
    }
    registered.current = true;

    void registerPushToken().catch(() => {
      registered.current = false;
    });
  }, [enabled]);

  useEffect(() => {
    if (!enabled || isRunningInExpoGo()) {
      return;
    }

    let remove: (() => void) | undefined;
    let cancelled = false;

    void import('expo-notifications').then((Notifications) => {
      if (cancelled) {
        return;
      }

      void Notifications.getLastNotificationResponseAsync().then((response) => {
        const data = response?.notification.request.content.data as
          | Record<string, unknown>
          | undefined;
        navigateFromNotificationData(router, data, queryClient);
      });

      const sub = Notifications.addNotificationResponseReceivedListener((response) => {
        const data = response.notification.request.content.data as
          | Record<string, unknown>
          | undefined;
        navigateFromNotificationData(router, data, queryClient);
      });
      remove = () => sub.remove();
    });

    return () => {
      cancelled = true;
      remove?.();
    };
  }, [enabled, router, queryClient]);
}
