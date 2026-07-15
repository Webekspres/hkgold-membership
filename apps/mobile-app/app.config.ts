import type { ConfigContext, ExpoConfig } from 'expo/config';

export default ({ config }: ConfigContext): ExpoConfig => {
  const plugins = [...(config.plugins ?? [])];

  if (!plugins.some((p) => (Array.isArray(p) ? p[0] : p) === 'expo-notifications')) {
    plugins.push('expo-notifications');
  }

  return {
    ...config,
    name: config.name ?? 'mobile-app',
    slug: config.slug ?? 'mobile-app',
    plugins,
    android: {
      ...(config.android ?? {}),
      // Drop google-services.json from Firebase (same project as Filament) into apps/mobile-app/
      googleServicesFile: process.env.GOOGLE_SERVICES_JSON ?? './google-services.json',
    },
    ios: {
      ...(config.ios ?? {}),
      googleServicesFile: process.env.GOOGLE_SERVICES_PLIST ?? './GoogleService-Info.plist',
    },
    extra: {
      ...(config.extra ?? {}),
      apiUrl: process.env.EXPO_PUBLIC_API_URL ?? '',
    },
  };
};
