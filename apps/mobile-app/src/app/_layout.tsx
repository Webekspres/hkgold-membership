import '@/global.css';

import { PortalHost } from '@rn-primitives/portal';
import { Stack, ThemeProvider } from 'expo-router';
import * as SplashScreen from 'expo-splash-screen';
import { useEffect } from 'react';
import { useColorScheme } from 'nativewind';
import { GestureHandlerRootView } from 'react-native-gesture-handler';

import { AnimatedSplashOverlay } from '@/components/shared/animated-icon';
import { AppToaster } from '@/components/shared/app-toaster';
import { NAV_THEME } from '@/lib/theme';

export default function RootLayout() {
  const { setColorScheme } = useColorScheme();

  useEffect(() => {
    setColorScheme('light');
    void SplashScreen.hideAsync();
  }, [setColorScheme]);

  return (
    <GestureHandlerRootView className="flex-1">
      <ThemeProvider value={NAV_THEME.light}>
        <AnimatedSplashOverlay />
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
          <Stack.Screen name="reward/[sku]" />
        </Stack>
        <PortalHost />
        <AppToaster />
      </ThemeProvider>
    </GestureHandlerRootView>
  );
}
