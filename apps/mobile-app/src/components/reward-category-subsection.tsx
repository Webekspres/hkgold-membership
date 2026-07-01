import { View } from "react-native";

import { RewardCatalogCard } from "@/components/reward-catalog-card";
import { Text } from "@/components/ui/text";
import {
  GRID_COLUMN_GAP,
  GRID_HORIZONTAL_PADDING,
} from "@/constants/grid-layout";
import type { RewardCategoryGroup } from "@/constants/mock-rewards";

type RewardCategorySubsectionProps = {
  category: RewardCategoryGroup;
};

export function RewardCategorySubsection({
  category,
}: RewardCategorySubsectionProps) {
  if (category.rewards.length === 0) {
    return null;
  }

  return (
    <View className="gap-3">
      <Text className="px-4 text-sm font-semibold text-stone-700">
        {category.name}
      </Text>

      <View
        className="flex-row flex-wrap"
        style={{
          columnGap: GRID_COLUMN_GAP,
          rowGap: GRID_COLUMN_GAP,
          paddingHorizontal: GRID_HORIZONTAL_PADDING,
        }}
      >
        {category.rewards.map((reward) => (
          <RewardCatalogCard key={reward.id} reward={reward} />
        ))}
      </View>
    </View>
  );
}
