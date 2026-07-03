import type { ReactNode } from 'react';
import { Image } from 'expo-image';
import { Pressable, View } from 'react-native';

import { Text } from '@/components/ui/text';
import { cn } from '@/lib/utils';
import type { RewardCatalogItem } from '@/types/reward';

type ProfileLastRewardCardProps = {
  title?: string;
  reward: RewardCatalogItem;
  onPress?: () => void;
  footer?: ReactNode;
  pressable?: boolean;
};

export function ProfileLastRewardCard({
  title = 'Reward terakhir diklaim',
  reward,
  onPress,
  footer,
  pressable = true,
}: ProfileLastRewardCardProps) {
  const cardClassName =
    'flex-row gap-3 rounded-xl border border-stone-200 bg-white shadow-md shadow-stone-900/15';

  const cardContent = (
    <>
      <Image
        source={reward.image}
        style={{
          width: 112,
          aspectRatio: 1,
          borderTopLeftRadius: 12,
          borderBottomLeftRadius: 12,
        }}
        contentFit="cover"
        accessibilityLabel={reward.name}
      />
      <View className="min-w-0 flex-1 justify-center gap-1">
        <Text variant="muted" className="text-[11px] uppercase tracking-wide">
          {reward.categoryName}
        </Text>
        <Text className="text-base font-semibold leading-snug text-stone-900" numberOfLines={2}>
          {reward.name}
        </Text>
        {footer ?? (
          <Text className="text-sm font-medium text-amber-700">
            {reward.pointsRequired.toLocaleString('id-ID')} poin
          </Text>
        )}
      </View>
    </>
  );

  return (
    <View className="gap-2">
      <Text className="text-base font-semibold text-stone-900">{title}</Text>
      {pressable && onPress ? (
        <Pressable className={cn(cardClassName, 'active:opacity-90')} onPress={onPress}>
          {cardContent}
        </Pressable>
      ) : (
        <View className={cardClassName}>{cardContent}</View>
      )}
    </View>
  );
}
