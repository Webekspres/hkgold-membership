import { Image } from 'expo-image';
import { router } from 'expo-router';
import { View } from 'react-native';

import { GoldButton } from '@/components/shared/gold-button';
import { Text } from '@/components/ui/text';
import { SCREEN_HORIZONTAL_PADDING } from '@/constants/layout/screen-layout';
import { formatRedeemDate } from '@/lib/format/format-redeem-date';
import type { RedeemHistoryItem } from '@/types/redeem';

type RedeemHistoryCardProps = {
  item: RedeemHistoryItem;
};

export function RedeemHistoryCard({ item }: RedeemHistoryCardProps) {
  return (
    <View
      style={{ paddingHorizontal: SCREEN_HORIZONTAL_PADDING }}
      className="rounded-xl border border-stone-100 shadow-md shadow-stone-900/25">
      <View className="overflow-hidden rounded-xl bg-white">
        <Image
          source={item.image}
          style={{ width: '100%', aspectRatio: 1 }}
          contentFit="cover"
          accessibilityLabel={item.name}
        />

        <View className="gap-2 p-3">
          <View className="gap-1">
            <Text variant="muted" className="text-[11px] uppercase tracking-wide">
              {item.categoryName}
            </Text>
            <Text className="text-sm font-semibold leading-snug text-stone-900" numberOfLines={2}>
              {item.name}
            </Text>
          </View>

          <View className="rounded-lg bg-[#fffbeb] px-2.5 py-2">
            <Text className="text-xl font-bold leading-none text-[#b45309]">
              {item.pointsRequired.toLocaleString('id-ID')}
            </Text>
            <Text className="mt-0.5 text-xs font-semibold uppercase tracking-wide text-[#c4841a]">
              poin
            </Text>
          </View>

          <Text variant="muted" className="text-xs">
            Ditebus pada {formatRedeemDate(item.redeemedAt)}
          </Text>

          <GoldButton
            variant="outline"
            width="full"
            label="Lihat detail"
            onPress={() =>
              router.push({ pathname: '/redeem/[id]', params: { id: item.id } })
            }
          />
        </View>
      </View>
    </View>
  );
}
