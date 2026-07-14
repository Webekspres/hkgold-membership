import { View } from 'react-native';
import Animated, { FadeIn } from 'react-native-reanimated';

import { Text } from '@/components/ui/text';
import type { TierBenefitRow } from '@/types/tier-benefit';

type TierBenefitTableProps = {
  benefits: TierBenefitRow[];
  slideKey: string;
};

export function TierBenefitTable({ benefits, slideKey }: TierBenefitTableProps) {
  return (
    <View className="overflow-hidden rounded-2xl border border-stone-100 bg-white">
      <View className="flex-row border-b border-stone-100 bg-stone-50 px-4 py-3">
        <Text className="w-[42%] text-[11px] font-semibold uppercase tracking-wide text-stone-500">
          Benefit
        </Text>
        <Text className="flex-1 text-[11px] font-semibold uppercase tracking-wide text-stone-500">
          Keterangan
        </Text>
      </View>

      <Animated.View key={slideKey} entering={FadeIn.duration(200)}>
        {benefits.map((row, index) => (
          <View
            key={`${slideKey}-${row.label}`}
            className={`flex-row px-4 py-3.5 ${
              index < benefits.length - 1 ? 'border-b border-stone-100' : ''
            }`}>
            <Text className="w-[42%] pr-2 text-sm font-medium text-stone-800">{row.label}</Text>
            <Text className="flex-1 text-sm leading-5 text-stone-600">{row.value}</Text>
          </View>
        ))}
      </Animated.View>
    </View>
  );
}
