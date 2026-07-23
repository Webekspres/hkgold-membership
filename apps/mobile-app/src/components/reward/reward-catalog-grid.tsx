import { View } from 'react-native';

import { RewardCatalogCard } from '@/components/reward/reward-catalog-card';
import {
  GRID_COLUMN_GAP,
  GRID_HORIZONTAL_PADDING,
} from '@/constants/layout/grid-layout';
import type { RewardCatalogItem } from '@/types/reward';

type RewardCatalogGridProps = {
  rewards: RewardCatalogItem[];
};

export function RewardCatalogGrid({ rewards }: RewardCatalogGridProps) {
  return (
    <View
      className="flex-row flex-wrap"
      style={{
        columnGap: GRID_COLUMN_GAP,
        rowGap: GRID_COLUMN_GAP,
        paddingHorizontal: GRID_HORIZONTAL_PADDING,
      }}>
      {rewards.map((reward) => (
        <RewardCatalogCard key={reward.id} reward={reward} />
      ))}
    </View>
  );
}
