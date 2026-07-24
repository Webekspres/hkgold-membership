import "@/global.css";

import { PortalHost } from "@rn-primitives/portal";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import { Stack, ThemeProvider, useRouter, useSegments } from "expo-router";
import * as SplashScreen from "expo-splash-screen";
import { useEffect, useState, type ReactNode } from "react";
import { StatusBar } from "react-native";
import { useColorScheme } from "nativewind";
import { GestureHandlerRootView } from "react-native-gesture-handler";

import { AnimatedSplashOverlay } from "@/components/shared/animated-icon";
import { AppToaster } from "@/components/shared/app-toaster";
import { useRegisterPushToken } from "@/hooks/use-register-push-token";
import { useAppFonts } from "@/lib/fonts";
import { NAV_THEME } from "@/lib/theme";
import { getAccessToken } from "@/services/auth";

SplashScreen.preventAutoHideAsync().catch(() => undefined);

const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      retry: 1,
    },
  },
});

function AuthGate({ children }: { children: ReactNode }) {
  const segments = useSegments();
  const router = useRouter();
  const [ready, setReady] = useState(false);
  const [hasToken, setHasToken] = useState(false);

  useRegisterPushToken(ready && hasToken);

  useEffect(() => {
    let active = true;

    void (async () => {
      const token = await getAccessToken();
      if (!active) return;

      const inAuthGroup = segments[0] === "(auth)";

      if (!token && !inAuthGroup) {
        router.replace("/login");
      } else if (token && inAuthGroup) {
        router.replace("/");
      }

      setHasToken(Boolean(token));
      setReady(true);
      void SplashScreen.hideAsync();
    })();

    return () => {
      active = false;
    };
  }, [segments, router]);

  if (!ready) return null;
  return <>{children}</>;
}

export default function RootLayout() {
  const { setColorScheme } = useColorScheme();
  const fontsLoaded = useAppFonts();

  useEffect(() => {
    setColorScheme("light");
  }, [setColorScheme]);

  if (!fontsLoaded) return null;

  return (
    <QueryClientProvider client={queryClient}>
      <GestureHandlerRootView className="flex-1">
        <ThemeProvider value={NAV_THEME.light}>
          <StatusBar
            barStyle="dark-content"
            backgroundColor="transparent"
            translucent
          />
          <AnimatedSplashOverlay />
          <AuthGate>
            <Stack screenOptions={{ headerShown: false }}>
              <Stack.Screen name="(tabs)" />
              <Stack.Screen name="(auth)" />
              <Stack.Screen name="cms" />
              <Stack.Screen name="events" />
              <Stack.Screen name="berita" />
              <Stack.Screen name="cabang" />
              <Stack.Screen name="faq" />
              <Stack.Screen name="tier-benefit" />
              <Stack.Screen name="redeem" />
              <Stack.Screen name="point-ledger" />
              <Stack.Screen name="reward/[sku]" />
              <Stack.Screen name="change-password" />
              <Stack.Screen name="change-phone" />
              <Stack.Screen name="profile/detail" />
              <Stack.Screen name="profile/edit" />
            </Stack>
          </AuthGate>
          <PortalHost />
          <AppToaster />
        </ThemeProvider>
      </GestureHandlerRootView>
    </QueryClientProvider>
  );
}
