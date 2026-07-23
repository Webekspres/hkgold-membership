import { View } from 'react-native';

import { RedeemHistoryCard } from '@/components/reward/redeem-history-card';
import type { RedeemHistoryItem } from '@/types/redeem';

type RedeemHistoryListProps = {
  items: RedeemHistoryItem[];
};

export function RedeemHistoryList({ items }: RedeemHistoryListProps) {
  return (
    <View className="gap-4">
      {items.map((item) => (
        <RedeemHistoryCard key={item.id} item={item} />
      ))}
    </View>
  );
}
