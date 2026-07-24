import { Image } from 'expo-image';
import type { PropsWithChildren, ReactNode } from 'react';
import { useEffect, useState } from 'react';
import {
  Keyboard,
  Platform,
  ScrollView,
  StatusBar,
  StyleSheet,
  View,
  type KeyboardEvent,
} from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';

import { Card } from '@/components/ui/card';
import { SCREEN_HORIZONTAL_PADDING } from '@/constants/layout/screen-layout';

/** Match backoffice mobile login chrome — dark + pattern. */
const AUTH_BG = '#0a0a0a';

const CARD_CLASSNAME =
  'w-full rounded-[20px] border-black/5 bg-white shadow-md shadow-black/20';

type AuthScreenShellProps = PropsWithChildren<{
  scrollable?: boolean;
  /** Rendered below the white card (e.g. security footer on dark bg). */
  footer?: ReactNode;
}>;

function AuthCard({ children }: PropsWithChildren) {
  return <Card className={CARD_CLASSNAME}>{children}</Card>;
}

function applyAuthStatusBar() {
  StatusBar.setBarStyle('light-content');
  if (Platform.OS === 'android') {
    StatusBar.setBackgroundColor('transparent');
    StatusBar.setTranslucent(true);
  }
}

function restoreDefaultStatusBar() {
  StatusBar.setBarStyle('dark-content');
  if (Platform.OS === 'android') {
    StatusBar.setBackgroundColor('transparent');
    StatusBar.setTranslucent(true);
  }
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
export function AuthScreenShell({
  children,
  scrollable = false,
  footer,
}: AuthScreenShellProps) {
  const [keyboardHeight, setKeyboardHeight] = useState(0);

  useEffect(() => {
    applyAuthStatusBar();
    return () => {
      restoreDefaultStatusBar();
    };
  }, []);

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

  const body = (
    <View style={styles.cardSlot}>
      <AuthCard>{children}</AuthCard>
      {footer ? <View style={styles.footerSlot}>{footer}</View> : null}
    </View>
  );

  return (
    <View style={styles.container}>
      <StatusBar
        barStyle="light-content"
        backgroundColor="transparent"
        translucent
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
            {body}
          </ScrollView>
        ) : (
          body
        )}
      </SafeAreaView>
    </View>
  );
}

export const authLogoStyle = StyleSheet.create({
  /** Oval mark — match backoffice login card. */
  icon: {
    width: 72,
    height: 72,
    alignSelf: 'center',
  },
});

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: AUTH_BG,
  },
  background: {
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    bottom: 0,
    opacity: 0.38,
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
    maxWidth: 416,
    alignSelf: 'center',
  },
  footerSlot: {
    marginTop: 20,
    alignItems: 'center',
  },
});
