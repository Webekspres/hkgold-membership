import { Image } from 'expo-image';
import { router } from 'expo-router';
import { View } from 'react-native';

import { GoldButton } from '@/components/gold-button';
import { Text } from '@/components/ui/text';
import { GRID_ITEM_WIDTH } from '@/constants/grid-layout';
import type { RewardCatalogItem } from '@/constants/mock-rewards';
import { formatStockRemaining } from '@/lib/format-reward-points';

type RewardCatalogCardProps = {
  reward: RewardCatalogItem;
};

export function RewardCatalogCard({ reward }: RewardCatalogCardProps) {
  return (
    <View
      style={{ width: GRID_ITEM_WIDTH }}
      className="rounded-xl border border-stone-100 shadow-md shadow-stone-900/25">
      <View className="overflow-hidden rounded-xl bg-white">
        <Image
          source={reward.image}
          style={{ width: '100%', aspectRatio: 1 }}
          contentFit="cover"
          accessibilityLabel={reward.name}
        />

        <View className="gap-2 p-3">
          <View className="gap-1">
            <Text variant="muted" className="text-[11px] uppercase tracking-wide">
              {reward.categoryName}
            </Text>
            <Text className="text-sm font-semibold leading-snug text-stone-900" numberOfLines={2}>
              {reward.name}
            </Text>
          </View>

          <View className="rounded-lg bg-[#fffbeb] px-2.5 py-2">
            <Text className="text-xl font-bold leading-none text-[#b45309]">
              {reward.pointsRequired.toLocaleString('id-ID')}
            </Text>
            <Text className="mt-0.5 text-xs font-semibold uppercase tracking-wide text-[#c4841a]">
              poin
            </Text>
          </View>

          <Text variant="muted" className="text-xs">
            {formatStockRemaining(reward.stockRemaining)}
          </Text>

          <GoldButton
            variant="outline"
            width="full"
            label="Lihat detail"
            onPress={() =>
              router.push({ pathname: '/reward/[sku]', params: { sku: reward.sku } })
            }
          />
        </View>
      </View>
    </View>
  );
}
