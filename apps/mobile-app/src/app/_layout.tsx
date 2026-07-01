import '@/global.css';

import { PortalHost } from '@rn-primitives/portal';
import { Stack, ThemeProvider } from 'expo-router';
import { useColorScheme as useNativeColorScheme } from 'react-native';
import { useEffect } from 'react';
import { useColorScheme } from 'nativewind';
import { GestureHandlerRootView } from 'react-native-gesture-handler';

import { AnimatedSplashOverlay } from '@/components/animated-icon';
import { NAV_THEME } from '@/lib/theme';

export default function RootLayout() {
  const nativeColorScheme = useNativeColorScheme();
  const { colorScheme, setColorScheme } = useColorScheme();

  useEffect(() => {
    setColorScheme(nativeColorScheme ?? 'light');
  }, [nativeColorScheme, setColorScheme]);

  const scheme = colorScheme ?? 'light';

  return (
    <GestureHandlerRootView className="flex-1">
      <ThemeProvider value={NAV_THEME[scheme]}>
        <AnimatedSplashOverlay />
        <Stack screenOptions={{ headerShown: false }}>
          <Stack.Screen name="(tabs)" />
          <Stack.Screen
            name="login"
            options={{ contentStyle: { backgroundColor: 'transparent' } }}
          />
        </Stack>
        <PortalHost />
      </ThemeProvider>
    </GestureHandlerRootView>
  );
}
