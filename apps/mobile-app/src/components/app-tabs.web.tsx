import {
  Tabs,
  TabList,
  TabTrigger,
  TabSlot,
  TabTriggerSlotProps,
  TabListProps,
} from 'expo-router/ui';
import { SymbolView } from 'expo-symbols';
import { Pressable, View } from 'react-native';

import { Text } from '@/components/ui/text';
import { Colors, MaxContentWidth } from '@/constants/theme';

const TABS = [
  {
    name: 'home',
    href: '/',
    label: 'Home',
    icon: { ios: 'house.fill', android: 'home', web: 'home' },
  },
  {
    name: 'card',
    href: '/card',
    label: 'Card',
    icon: { ios: 'person.text.rectangle.fill', android: 'badge', web: 'badge' },
  },
  {
    name: 'profile',
    href: '/profile',
    label: 'Profil',
    icon: { ios: 'person.fill', android: 'person', web: 'person' },
  },
] as const;

export default function AppTabs() {
  return (
    <Tabs>
      <TabSlot style={{ height: '100%' }} />
      <TabList asChild>
        <CustomTabList>
          {TABS.map((tab) => (
            <TabTrigger key={tab.name} name={tab.name} href={tab.href} asChild>
              <TabButton icon={tab.icon}>{tab.label}</TabButton>
            </TabTrigger>
          ))}
        </CustomTabList>
      </TabList>
    </Tabs>
  );
}

type TabButtonProps = TabTriggerSlotProps & {
  icon: (typeof TABS)[number]['icon'];
};

export function TabButton({ children, isFocused, icon, ...props }: TabButtonProps) {
  const colors = Colors.light;

  return (
    <Pressable {...props} className="active:opacity-70">
      <View className={`items-center gap-0.5 rounded-2xl px-3 py-1 ${isFocused ? 'bg-accent' : ''}`}>
        <SymbolView
          name={icon}
          size={20}
          tintColor={isFocused ? colors.text : colors.textSecondary}
        />
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
  return (
    <View
      {...props}
      className="absolute w-full flex-row items-center justify-center border-t border-border bg-background p-2"
      style={{ maxWidth: MaxContentWidth }}>
      <View className="w-full max-w-[800px] flex-row items-center justify-around px-4 py-1">
        {props.children}
      </View>
    </View>
  );
}
