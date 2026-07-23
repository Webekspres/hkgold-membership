import { Linking } from 'react-native';
import { openBrowserAsync, WebBrowserPresentationStyle } from 'expo-web-browser';

export async function openLocationUrl(url: string | null | undefined) {
  if (!url) {
    return;
  }

  if (process.env.EXPO_OS === 'web') {
    await Linking.openURL(url);
    return;
  }

  await openBrowserAsync(url, {
    presentationStyle: WebBrowserPresentationStyle.AUTOMATIC,
  });
}
