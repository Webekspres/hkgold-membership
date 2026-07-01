import { SymbolView } from 'expo-symbols';
import { router } from 'expo-router';
import { Pressable, View } from 'react-native';

import { Text } from '@/components/ui/text';
import { HOME_SHORTCUTS } from '@/constants/home-shortcuts';
import { cn } from '@/lib/utils';

const ICON_COLOR = '#c4841a';
const CIRCLE_BORDER = 'rgba(232, 160, 32, 0.45)';

type HomeShortcutGridProps = {
  className?: string;
};

export function HomeShortcutGrid({ className }: HomeShortcutGridProps) {
  return (
    <View className={cn('gap-3 px-4', className)}>
      <Text className="text-base font-semibold text-stone-900">Akses Cepat</Text>

      <View className="flex-row justify-between">
        {HOME_SHORTCUTS.map((shortcut) => (
          <Pressable
            key={shortcut.id}
            className="max-w-[72px] flex-1 items-center gap-2 active:opacity-70"
            onPress={() => router.push(shortcut.href)}
            accessibilityRole="button"
            accessibilityLabel={shortcut.label}>
            <View
              className="h-14 w-14 items-center justify-center rounded-full bg-white"
              style={{ borderWidth: 1, borderColor: CIRCLE_BORDER }}>
              <SymbolView name={shortcut.icon} size={24} tintColor={ICON_COLOR} />
            </View>
            <Text variant="small" className="text-center text-stone-700">
              {shortcut.label}
            </Text>
          </Pressable>
        ))}
      </View>
    </View>
  );
}
