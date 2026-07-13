import { Image } from 'expo-image';
import type { PropsWithChildren } from 'react';
import {
  KeyboardAvoidingView,
  Platform,
  ScrollView,
  StyleSheet,
  View,
} from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';

import { Card } from '@/components/ui/card';

const CARD_CLASSNAME =
  'w-full border-stone-200 bg-stone-50 shadow-md shadow-stone-900/10';

type AuthScreenShellProps = PropsWithChildren<{
  scrollable?: boolean;
}>;

function AuthCard({ children }: PropsWithChildren) {
  return <Card className={CARD_CLASSNAME}>{children}</Card>;
}

export function AuthScreenShell({ children, scrollable = false }: AuthScreenShellProps) {
  return (
    <View style={styles.container}>
      <Image
        source={require('@/assets/media/background.webp')}
        style={styles.background}
        contentFit="cover"
      />
      <SafeAreaView style={styles.overlay}>
        {scrollable ? (
          <KeyboardAvoidingView
            style={styles.scroll}
            behavior={Platform.select({ ios: 'padding', android: 'height' })}
            keyboardVerticalOffset={Platform.select({ ios: 0, android: 24 })}>
            <ScrollView
              style={styles.scroll}
              contentContainerStyle={styles.scrollContent}
              keyboardShouldPersistTaps="handled"
              showsVerticalScrollIndicator={false}>
              <View style={styles.cardSlot}>
                <AuthCard>{children}</AuthCard>
              </View>
            </ScrollView>
          </KeyboardAvoidingView>
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
    backgroundColor: 'transparent',
  },
  background: {
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    bottom: 0,
  },
  overlay: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    paddingHorizontal: 24,
    backgroundColor: 'transparent',
  },
  scroll: {
    flex: 1,
    width: '100%',
  },
  scrollContent: {
    flexGrow: 1,
    justifyContent: 'center',
    paddingVertical: 24,
    width: '100%',
  },
  cardSlot: {
    width: '100%',
    maxWidth: 448,
    alignSelf: 'center',
  },
});
