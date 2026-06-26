import { Image } from 'expo-image';
import { SymbolView } from 'expo-symbols';
import { Platform, Pressable, ScrollView, View } from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';

import { ExternalLink } from '@/components/external-link';
import { Text } from '@/components/ui/text';
import { Collapsible } from '@/components/ui/collapsible';
import { WebBadge } from '@/components/web-badge';
import { BottomTabInset } from '@/constants/theme';
import { useTheme } from '@/hooks/use-theme';

export default function TabTwoScreen() {
  const safeAreaInsets = useSafeAreaInsets();
  const insets = {
    ...safeAreaInsets,
    bottom: safeAreaInsets.bottom + BottomTabInset + 16,
  };
  const theme = useTheme();

  const contentPlatformClassName = Platform.select({
    web: 'pt-16 pb-6',
    default: '',
  });

  return (
    <ScrollView
      className="flex-1 bg-background"
      contentInset={insets}
      contentContainerClassName="flex-row justify-center">
      <View className={`max-w-[800px] grow ${contentPlatformClassName}`}>
        <View className="items-center gap-4 px-6 py-16">
          <Text className="text-[32px] font-semibold leading-[44px]">Explore</Text>
          <Text variant="muted" className="text-center">
            This starter app includes example{'\n'}code to help you get started.
          </Text>

          <ExternalLink href="https://docs.expo.dev" asChild>
            <Pressable className="active:opacity-70">
              <View className="flex-row items-center justify-center gap-1 rounded-[32px] bg-muted px-6 py-2">
                <Text variant="small">Expo documentation</Text>
                <SymbolView
                  tintColor={theme.text}
                  name={{ ios: 'arrow.up.right.square', android: 'link', web: 'link' }}
                  size={12}
                />
              </View>
            </Pressable>
          </ExternalLink>
        </View>

        <View className="gap-8 px-6 pt-4">
          <Collapsible title="File-based routing">
            <Text variant="small">
              This app has two screens: <Text variant="code">src/app/(tabs)/index.tsx</Text> and{' '}
              <Text variant="code">src/app/(tabs)/explore.tsx</Text>
            </Text>
            <Text variant="small">
              The layout file in <Text variant="code">src/app/_layout.tsx</Text> sets up the stack
              navigator, and <Text variant="code">src/app/(tabs)/_layout.tsx</Text> sets up the tab
              navigator.
            </Text>
            <ExternalLink href="https://docs.expo.dev/router/introduction">
              <Text className="text-sm text-primary">Learn more</Text>
            </ExternalLink>
          </Collapsible>

          <Collapsible title="Android, iOS, and web support">
            <View className="items-center">
              <Text variant="small">
                You can open this project on Android, iOS, and the web. To open the web version,
                press <Text className="text-sm font-bold">w</Text> in the terminal running this
                project.
              </Text>
              <Image
                source={require('@/assets/images/tutorial-web.png')}
                className="mt-2 aspect-[296/171] w-full rounded-2xl"
              />
            </View>
          </Collapsible>

          <Collapsible title="Images">
            <Text variant="small">
              For static images, you can use the <Text variant="code">@2x</Text> and{' '}
              <Text variant="code">@3x</Text> suffixes to provide files for different screen
              densities.
            </Text>
            <Image
              source={require('@/assets/images/react-logo.png')}
              className="h-[100px] w-[100px] self-center"
            />
            <ExternalLink href="https://reactnative.dev/docs/images">
              <Text className="text-sm text-primary">Learn more</Text>
            </ExternalLink>
          </Collapsible>

          <Collapsible title="Light and dark mode components">
            <Text variant="small">
              This template has light and dark mode support. The{' '}
              <Text variant="code">useColorScheme()</Text> hook lets you inspect what the
              user&apos;s current color scheme is, and so you can adjust UI colors accordingly.
            </Text>
            <ExternalLink href="https://docs.expo.dev/develop/user-interface/color-themes/">
              <Text className="text-sm text-primary">Learn more</Text>
            </ExternalLink>
          </Collapsible>

          <Collapsible title="Animations">
            <Text variant="small">
              This template includes an example of an animated component. The{' '}
              <Text variant="code">src/components/ui/collapsible.tsx</Text> component uses the
              powerful <Text variant="code">react-native-reanimated</Text> library to animate
              opening this hint.
            </Text>
          </Collapsible>
        </View>
        {Platform.OS === 'web' && <WebBadge />}
      </View>
    </ScrollView>
  );
}
