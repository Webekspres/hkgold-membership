import { LinearGradient } from 'expo-linear-gradient';
import { router } from 'expo-router';
import { Pressable, View } from 'react-native';

import { Text } from '@/components/ui/text';
import {
  GOLD_GRADIENT_COLORS,
  GOLD_GRADIENT_END,
  GOLD_GRADIENT_START,
} from '@/constants/brand';
import { cn } from '@/lib/utils';

export type MemberTier = 'SILVER' | 'GOLD' | 'PLATINUM' | 'SAPPHIRE';

export type MemberWalletCardProps = {
  fullName: string;
  memberNumber: string;
  currentTier: MemberTier;
  pointBalance: number;
  className?: string;
};

const TIER_STYLES: Record<
  MemberTier,
  { label: string; badgeClassName: string; textClassName: string }
> = {
  SILVER: {
    label: 'Silver',
    badgeClassName: 'bg-stone-200',
    textClassName: 'text-stone-700',
  },
  GOLD: {
    label: 'Gold',
    badgeClassName: 'bg-[#fef3c7]',
    textClassName: 'text-[#b45309]',
  },
  PLATINUM: {
    label: 'Platinum',
    badgeClassName: 'bg-slate-200',
    textClassName: 'text-slate-700',
  },
  SAPPHIRE: {
    label: 'Sapphire',
    badgeClassName: 'bg-indigo-100',
    textClassName: 'text-indigo-800',
  },
};

function formatPointBalance(points: number) {
  return `${points.toLocaleString('id-ID')} poin`;
}

export function MemberWalletCard({
  fullName,
  memberNumber,
  currentTier,
  pointBalance,
  className,
}: MemberWalletCardProps) {
  const tier = TIER_STYLES[currentTier];

  return (
    <Pressable
      className={cn('active:opacity-95', className)}
      onPress={() => router.push('/card')}
      accessibilityRole="button"
      accessibilityLabel="Buka kartu member">
      <LinearGradient
        colors={[...GOLD_GRADIENT_COLORS]}
        start={GOLD_GRADIENT_START}
        end={GOLD_GRADIENT_END}
        style={{ borderRadius: 20, padding: 2 }}>
        <View className="rounded-[18px] bg-white px-5 py-5">
          <View className="flex-row items-start justify-between gap-3">
            <View className="min-w-0 flex-1 gap-1">
              <Text variant="muted" className="text-xs uppercase tracking-wide">
                Member HK Gold
              </Text>
              <Text className="text-xl font-semibold text-stone-900" numberOfLines={2}>
                {fullName}
              </Text>
              <Text variant="small" className="text-stone-500">
                {memberNumber}
              </Text>
            </View>

            <View className={cn('rounded-full px-3 py-1', tier.badgeClassName)}>
              <Text variant="small" className={cn('font-semibold', tier.textClassName)}>
                {tier.label}
              </Text>
            </View>
          </View>

          <View className="mt-5 border-t border-stone-100 pt-4">
            <Text variant="muted" className="text-xs">
              Saldo poin
            </Text>
            <Text className="mt-0.5 text-2xl font-bold text-stone-900">
              {formatPointBalance(pointBalance)}
            </Text>
          </View>
        </View>
      </LinearGradient>
    </Pressable>
  );
}
