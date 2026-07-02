import { View } from 'react-native';

import { RewardCatalogGrid } from '@/components/reward-catalog-grid';
import { Text } from '@/components/ui/text';
import type { RewardCategoryGroup } from '@/constants/mock-rewards';

type RewardCategorySubsectionProps = {
  category: RewardCategoryGroup;
};

export function RewardCategorySubsection({ category }: RewardCategorySubsectionProps) {
  if (category.rewards.length === 0) {
    return null;
  }

  return (
    <View className="gap-3">
      <Text className="px-4 text-sm font-semibold text-stone-700">{category.name}</Text>
      <RewardCatalogGrid rewards={category.rewards} />
    </View>
  );
}
