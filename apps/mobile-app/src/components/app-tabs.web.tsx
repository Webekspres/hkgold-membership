import {
  Tabs,
  TabList,
  TabTrigger,
  TabSlot,
  TabTriggerSlotProps,
  TabListProps,
} from 'expo-router/ui';
import { SymbolView } from 'expo-symbols';
import { Pressable, useColorScheme, View } from 'react-native';

import { ExternalLink } from './external-link';
import { Text } from '@/components/ui/text';
import { Colors, MaxContentWidth } from '@/constants/theme';

export default function AppTabs() {
  return (
    <Tabs>
      <TabSlot style={{ height: '100%' }} />
      <TabList asChild>
        <CustomTabList>
          <TabTrigger name="home" href="/" asChild>
            <TabButton>Home</TabButton>
          </TabTrigger>
          <TabTrigger name="explore" href="/explore" asChild>
            <TabButton>Explore</TabButton>
          </TabTrigger>
        </CustomTabList>
      </TabList>
    </Tabs>
  );
}

export function TabButton({ children, isFocused, ...props }: TabTriggerSlotProps) {
  return (
    <Pressable {...props} className="active:opacity-70">
      <View
        className={`rounded-2xl px-4 py-1 ${isFocused ? 'bg-accent' : 'bg-muted'}`}>
        <Text
          variant="small"
          className={isFocused ? 'text-foreground' : 'text-muted-foreground'}>
          {children}
        </Text>
      </View>
    </Pressable>
  );
}

export function CustomTabList(props: TabListProps) {
  const scheme = useColorScheme();
  const colors = Colors[scheme === 'unspecified' ? 'light' : scheme];

  return (
    <View
      {...props}
      className="absolute w-full flex-row items-center justify-center p-4"
      style={{ maxWidth: MaxContentWidth }}>
      <View className="max-w-[800px] grow flex-row items-center gap-2 rounded-[32px] bg-muted px-8 py-2">
        <Text variant="small" className="mr-auto font-bold">
          Expo Starter
        </Text>

        {props.children}

        <ExternalLink href="https://docs.expo.dev" asChild>
          <Pressable className="ml-4 flex-row items-center justify-center gap-1">
            <Text variant="small" className="text-primary">
              Docs
            </Text>
            <SymbolView
              tintColor={colors.text}
              name={{ ios: 'arrow.up.right.square', web: 'link' }}
              size={12}
            />
          </Pressable>
        </ExternalLink>
      </View>
    </View>
  );
}
