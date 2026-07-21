import { View } from 'react-native';
import Animated, { FadeIn } from 'react-native-reanimated';

import { Text } from '@/components/ui/text';
import type { TierBenefitRow } from '@/types/tier-benefit';

type TierBenefitTableProps = {
  benefits: TierBenefitRow[];
  slideKey: string;
  tierTitle: string;
};

export function TierBenefitTable({ benefits, slideKey, tierTitle }: TierBenefitTableProps) {
  return (
    <View
      className="overflow-hidden rounded-3xl bg-white"
      style={{
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.08,
        shadowRadius: 16,
        elevation: 6,
      }}
    >
      <View className="border-b border-stone-100 px-5 pb-4 pt-5">
        <Text className="text-lg font-bold text-stone-900">Keuntungan {tierTitle}</Text>
        <Text className="mt-1 text-sm text-stone-500">
          Khusus untuk Anda pelanggan {tierTitle}
        </Text>
      </View>

      <View className="flex-row border-b border-stone-100 bg-stone-50 px-5 py-3">
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
            className={`flex-row px-5 py-3.5 ${
              index < benefits.length - 1 ? 'border-b border-stone-100' : ''
            }`}
          >
            <Text className="w-[42%] pr-2 text-sm font-medium text-stone-800">{row.label}</Text>
            <Text className="flex-1 text-sm leading-5 text-stone-600">{row.value}</Text>
          </View>
        ))}
      </Animated.View>
    </View>
  );
}
