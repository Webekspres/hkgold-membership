import { LinearGradient } from 'expo-linear-gradient';
import { SymbolView } from 'expo-symbols';
import { router } from 'expo-router';
import { Pressable, View } from 'react-native';

import { Text } from '@/components/ui/text';
import {
  GOLD_GRADIENT_COLORS,
  GOLD_GRADIENT_END,
  GOLD_GRADIENT_START,
} from '@/config/brand';
import { HOME_SHORTCUTS } from '@/config/home-shortcuts';
import { cn } from '@/lib/utils';

const ICON_COLOR = '#ffffff';

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
            <LinearGradient
              colors={[...GOLD_GRADIENT_COLORS]}
              start={GOLD_GRADIENT_START}
              end={GOLD_GRADIENT_END}
              style={{
                height: 56,
                width: 56,
                borderRadius: 28,
                alignItems: 'center',
                justifyContent: 'center',
              }}>
              <SymbolView name={shortcut.icon} size={24} tintColor={ICON_COLOR} />
            </LinearGradient>
            <Text variant="small" className="text-center text-stone-700">
              {shortcut.label}
            </Text>
          </Pressable>
        ))}
      </View>
    </View>
  );
}
