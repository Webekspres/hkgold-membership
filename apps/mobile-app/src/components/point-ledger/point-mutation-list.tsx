import { View } from 'react-native';

import { PointMutationCard } from './point-mutation-card';
import type { PointMutationItem } from '@/types/point-ledger';

type PointMutationListProps = {
  items: PointMutationItem[];
};

export function PointMutationList({ items }: PointMutationListProps) {
  return (
    <View className="gap-3">
      {items.map((item) => (
        <PointMutationCard key={item.id} item={item} />
      ))}
    </View>
  );
}
