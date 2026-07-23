import Constants from 'expo-constants';

/**
 * URL API dari Doppler (`EXPO_PUBLIC_API_URL`) saat `npm start` / `doppler run -- expo start`.
 * Jangan pakai `npx expo start` tanpa Doppler — env tidak ter-inject.
 */
export function getApiBaseUrl(): string {
  const fromEnv = process.env.EXPO_PUBLIC_API_URL?.trim();
  const fromExtra = (
    Constants.expoConfig?.extra as { apiUrl?: string } | undefined
  )?.apiUrl?.trim();

  const url = fromEnv || fromExtra;
  if (!url) {
    throw new Error(
      'EXPO_PUBLIC_API_URL kosong. Jalankan dengan: npm start (doppler run), bukan npx expo start.'
    );
  }
  return url.replace(/\/$/, '');
}
