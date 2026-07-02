import '@/global.css';

import { PortalHost } from '@rn-primitives/portal';
import { Stack, ThemeProvider } from 'expo-router';
import { useEffect } from 'react';
import { useColorScheme } from 'nativewind';
import { GestureHandlerRootView } from 'react-native-gesture-handler';

import { AnimatedSplashOverlay } from '@/components/animated-icon';
import { NAV_THEME } from '@/lib/theme';

export default function RootLayout() {
  const { setColorScheme } = useColorScheme();

  useEffect(() => {
    setColorScheme('light');
  }, [setColorScheme]);

  return (
    <GestureHandlerRootView className="flex-1">
      <ThemeProvider value={NAV_THEME.light}>
        <AnimatedSplashOverlay />
        <Stack screenOptions={{ headerShown: false }}>
          <Stack.Screen name="(tabs)" />
          <Stack.Screen
            name="login"
            options={{ contentStyle: { backgroundColor: 'transparent' } }}
          />
          <Stack.Screen
            name="register"
            options={{ contentStyle: { backgroundColor: 'transparent' } }}
          />
          <Stack.Screen name="cms" />
          <Stack.Screen name="events" />
          <Stack.Screen name="berita" />
          <Stack.Screen name="cabang" />
          <Stack.Screen name="event/[slug]" />
          <Stack.Screen name="reward" />
        </Stack>
        <PortalHost />
      </ThemeProvider>
    </GestureHandlerRootView>
  );
}
