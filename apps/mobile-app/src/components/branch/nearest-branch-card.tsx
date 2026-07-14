import { Image } from 'expo-image';
import { LinearGradient } from 'expo-linear-gradient';
import { Map, MapPin } from 'lucide-react-native';
import { cssInterop } from 'nativewind';
import { Pressable, View } from 'react-native';

import {
  GoldGradientIcon,
  GoldGradientText,
} from '@/components/shared/gold-gradient-text';
import { Icon } from '@/components/ui/icon';
import { Text } from '@/components/ui/text';
import {
  GOLD_GRADIENT_COLORS,
  GOLD_GRADIENT_END,
  GOLD_GRADIENT_START,
} from '@/config/brand';
import { formatBranchLocation } from '@/lib/format/format-branch-location';
import { openLocationUrl } from '@/lib/open-location-url';
import type { BranchItem } from '@/types/branch';

cssInterop(LinearGradient, { className: 'style' });
cssInterop(Image, { className: 'style' });

const PLACEHOLDER_IMAGE = require('@/assets/mockImage/mock-image-news.webp');

type NearestBranchCardProps = {
  branch: BranchItem;
};

function formatDistanceKm(km: number) {
  return `${km.toLocaleString('id-ID', { maximumFractionDigits: 1 })} km`;
}

export function NearestBranchCard({ branch }: NearestBranchCardProps) {
  const canOpenMap = Boolean(branch.locationUrl);

  return (
    <View className="overflow-hidden rounded-xl border border-stone-200 bg-white shadow-md shadow-stone-900/15">
      <View className="flex-row items-center gap-3 p-3">
        <Image
          source={branch.imageUrl ? { uri: branch.imageUrl } : PLACEHOLDER_IMAGE}
          className="h-20 w-20 rounded-lg"
          contentFit="cover"
          accessibilityLabel={`Foto ${branch.name}`}
        />

        <View className="min-w-0 flex-1 gap-1">
          <Text
            className="text-base font-semibold leading-snug text-stone-900"
            numberOfLines={2}>
            {branch.name}
          </Text>
          <Text variant="muted" className="text-sm" numberOfLines={1}>
            {formatBranchLocation(branch.subdistrict, branch.city)}
          </Text>
          {branch.distanceKm != null ? (
            <View className="mt-0.5 flex-row items-center gap-1">
              <GoldGradientIcon size={14}>
                <Icon as={MapPin} size={14} className="text-black" />
              </GoldGradientIcon>
              <GoldGradientText className="text-sm font-medium">
                {formatDistanceKm(branch.distanceKm)}
              </GoldGradientText>
            </View>
          ) : null}
        </View>

        <Pressable
          disabled={!canOpenMap}
          onPress={() => openLocationUrl(branch.locationUrl)}
          accessibilityRole="button"
          accessibilityLabel="Buka lokasi di peta"
          className="active:opacity-80 disabled:opacity-40">
          <LinearGradient
            colors={[...GOLD_GRADIENT_COLORS]}
            start={GOLD_GRADIENT_START}
            end={GOLD_GRADIENT_END}
            className="rounded-lg p-[1.5px]">
            <View className="h-11 w-11 items-center justify-center rounded-[7px] bg-white">
              <GoldGradientIcon size={22}>
                <Icon as={Map} size={22} className="text-black" />
              </GoldGradientIcon>
            </View>
          </LinearGradient>
        </Pressable>
      </View>
    </View>
  );
}
