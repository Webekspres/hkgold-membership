import MaskedView from '@react-native-masked-view/masked-view';
import { LinearGradient } from 'expo-linear-gradient';
import type { ReactElement } from 'react';
import { View } from 'react-native';

import { Text } from '@/components/ui/text';
import {
  GOLD_GRADIENT_COLORS,
  GOLD_GRADIENT_END,
  GOLD_GRADIENT_START,
} from '@/config/brand';
import { cn } from '@/lib/utils';

type GoldGradientTextProps = {
  children: string;
  className?: string;
};

/** Teks dengan fill gradasi emas brand (mask + LinearGradient). */
export function GoldGradientText({ children, className }: GoldGradientTextProps) {
  return (
    <MaskedView
      maskElement={
        <Text className={cn('bg-transparent text-black', className)}>{children}</Text>
      }>
      <LinearGradient
        colors={[...GOLD_GRADIENT_COLORS]}
        start={GOLD_GRADIENT_START}
        end={GOLD_GRADIENT_END}>
        <Text className={cn('opacity-0', className)}>{children}</Text>
      </LinearGradient>
    </MaskedView>
  );
}

type GoldGradientIconProps = {
  size?: number;
  children: ReactElement;
};

/** Ikon siluet hitam → diisi gradasi emas. Child harus warna hitam solid. */
export function GoldGradientIcon({ size = 20, children }: GoldGradientIconProps) {
  return (
    <MaskedView
      style={{ width: size, height: size }}
      maskElement={<View className="items-center justify-center">{children}</View>}>
      <LinearGradient
        colors={[...GOLD_GRADIENT_COLORS]}
        start={GOLD_GRADIENT_START}
        end={GOLD_GRADIENT_END}
        style={{ flex: 1 }}
      />
    </MaskedView>
  );
}
