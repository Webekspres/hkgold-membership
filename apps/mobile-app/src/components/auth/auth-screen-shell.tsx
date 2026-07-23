import { Image } from 'expo-image';
import { LinearGradient } from 'expo-linear-gradient';
import type { PropsWithChildren } from 'react';
import { useEffect, useState } from 'react';
import {
  Keyboard,
  Platform,
  ScrollView,
  StyleSheet,
  View,
  type KeyboardEvent,
} from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';

import { Card } from '@/components/ui/card';
import { SCREEN_HORIZONTAL_PADDING } from '@/constants/layout/screen-layout';

const CARD_CLASSNAME =
  'w-full border-stone-200 bg-stone-50 shadow-md shadow-stone-900/10';

/** Diagonal putih → emas tipis → putih (brand `#f5c842` / `#D1A13B`). */
const AUTH_BG_GRADIENT = [
  '#ffffff',
  'rgba(245, 200, 66, 0.28)',
  'rgba(209, 161, 59, 0.22)',
  '#ffffff',
] as const;

type AuthScreenShellProps = PropsWithChildren<{
  scrollable?: boolean;
}>;

function AuthCard({ children }: PropsWithChildren) {
  return <Card className={CARD_CLASSNAME}>{children}</Card>;
}

/**
 * Expo Go (Android): windowSoftInputMode project tidak berlaku — keyboard
 * menutupi layar tanpa resize. KeyboardAvoidingView sering tidak mengecilkan
 * viewport cukup, jadi ScrollView anggap konten "muat" dan tidak bisa scroll.
 *
 * Pola yang reliable di Expo Go: ScrollView penuh + paddingBottom = tinggi
 * keyboard. Konten jadi lebih tinggi dari layar → user bisa scroll sampai
 * CTA/footer di atas keyboard.
 */
export function AuthScreenShell({ children, scrollable = false }: AuthScreenShellProps) {
  const [keyboardHeight, setKeyboardHeight] = useState(0);

  useEffect(() => {
    const onShow = (e: KeyboardEvent) => {
      setKeyboardHeight(e.endCoordinates.height);
    };
    const onHide = () => {
      setKeyboardHeight(0);
    };

    const showEvent = Platform.OS === 'ios' ? 'keyboardWillShow' : 'keyboardDidShow';
    const hideEvent = Platform.OS === 'ios' ? 'keyboardWillHide' : 'keyboardDidHide';
    const showSub = Keyboard.addListener(showEvent, onShow);
    const hideSub = Keyboard.addListener(hideEvent, onHide);
    return () => {
      showSub.remove();
      hideSub.remove();
    };
  }, []);

  const keyboardOpen = keyboardHeight > 0;

  return (
    <View style={styles.container}>
      <LinearGradient
        colors={[...AUTH_BG_GRADIENT]}
        start={{ x: 0, y: 0 }}
        end={{ x: 1, y: 1 }}
        style={styles.gradient}
      />
      <Image
        source={require('@/assets/media/pattern-horizontal.webp')}
        style={styles.background}
        contentFit="cover"
      />
      <SafeAreaView style={styles.overlay} edges={['top', 'left', 'right']}>
        {scrollable ? (
          <ScrollView
            style={styles.scroll}
            contentContainerStyle={[
              styles.scrollContent,
              keyboardOpen && styles.scrollContentKeyboard,
              {
                paddingBottom: keyboardOpen ? keyboardHeight + 24 : 24,
              },
            ]}
            keyboardShouldPersistTaps="handled"
            keyboardDismissMode="on-drag"
            showsVerticalScrollIndicator={false}
            automaticallyAdjustKeyboardInsets={false}
            bounces>
            <View style={styles.cardSlot}>
              <AuthCard>{children}</AuthCard>
            </View>
          </ScrollView>
        ) : (
          <View style={styles.cardSlot}>
            <AuthCard>{children}</AuthCard>
          </View>
        )}
      </SafeAreaView>
    </View>
  );
}

export const authLogoStyle = StyleSheet.create({
  logo: {
    width: 192,
    height: 80,
    alignSelf: 'center',
  },
});

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#ffffff',
  },
  gradient: {
    ...StyleSheet.absoluteFillObject,
  },
  background: {
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    bottom: 0,
    opacity: 0.25,
    transform: [{ scale: 1.25 }],
  },
  overlay: {
    flex: 1,
    alignItems: 'center',
    paddingHorizontal: SCREEN_HORIZONTAL_PADDING,
    backgroundColor: 'transparent',
  },
  scroll: {
    flex: 1,
    width: '100%',
  },
  scrollContent: {
    flexGrow: 1,
    justifyContent: 'center',
    paddingTop: 24,
    width: '100%',
  },
  scrollContentKeyboard: {
    justifyContent: 'flex-start',
  },
  cardSlot: {
    width: '100%',
    maxWidth: 448,
    alignSelf: 'center',
  },
});
