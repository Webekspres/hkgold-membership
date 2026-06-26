import * as Device from 'expo-device';
import { Platform, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';

import { AnimatedIcon } from '@/components/animated-icon';
import { HintRow } from '@/components/hint-row';
import { Text } from '@/components/ui/text';
import { WebBadge } from '@/components/web-badge';
import { BottomTabInset, MaxContentWidth } from '@/constants/theme';

function getDevMenuHint() {
  if (Platform.OS === 'web') {
    return <Text variant="small">use browser devtools</Text>;
  }
  if (Device.isDevice) {
    return (
      <Text variant="small">
        shake device or press <Text variant="code">m</Text> in terminal
      </Text>
    );
  }
  const shortcut = Platform.OS === 'android' ? 'cmd+m (or ctrl+m)' : 'cmd+d';
  return (
    <Text variant="small">
      press <Text variant="code">{shortcut}</Text>
    </Text>
  );
}

export default function HomeScreen() {
  return (
    <View className="flex-1 flex-row justify-center bg-background">
      <SafeAreaView
        className="max-w-[800px] flex-1 items-center gap-4 px-6"
        style={{ paddingBottom: BottomTabInset + 16 }}>
        <View className="flex-1 items-center justify-center gap-6 px-6">
          <AnimatedIcon />
          <Text variant="h1" className="text-center text-5xl font-semibold leading-[52px]">
            Welcome to&nbsp;Expo
          </Text>
        </View>

        <Text variant="code" className="uppercase">
          get started
        </Text>

        <View className="gap-4 self-stretch rounded-3xl bg-muted px-4 py-6">
          <HintRow title="Try editing" hint={<Text variant="code">src/app/index.tsx</Text>} />
          <HintRow title="Dev tools" hint={getDevMenuHint()} />
          <HintRow
            title="Fresh start"
            hint={<Text variant="code">npm run reset-project</Text>}
          />
        </View>

        {Platform.OS === 'web' && <WebBadge />}
      </SafeAreaView>
    </View>
  );
}
