import { View } from 'react-native';

import { Text } from '@/components/ui/text';
import type { PointMutationItem } from '@/types/point-ledger';
import { cn } from '@/lib/utils';

type PointMutationCardProps = {
  item: PointMutationItem;
};

function formatDate(dateStr: string): string {
  const d = new Date(dateStr);
  const day = String(d.getDate()).padStart(2, '0');
  const month = String(d.getMonth() + 1).padStart(2, '0');
  const year = d.getFullYear();
  const hour = String(d.getHours()).padStart(2, '0');
  const minute = String(d.getMinutes()).padStart(2, '0');
  return `${day}/${month}/${year} ${hour}:${minute}`;
}

function formatPoints(points: number): string {
  return points.toLocaleString('id-ID');
}

export function PointMutationCard({ item }: PointMutationCardProps) {
  const isIssued = item.pointsIssued > 0;
  const points = isIssued ? item.pointsIssued : item.pointsRedeemed;
  const sign = isIssued ? '+' : '-';

  return (
    <View className="rounded-xl border border-stone-200 bg-white p-4 shadow-sm shadow-black/5">
      <View className="flex-row items-start justify-between gap-3">
        <View className="flex-1 gap-1">
          <Text className="text-sm font-semibold text-stone-900">
            {item.type}
          </Text>
          <Text className="text-xs text-stone-500">
            {formatDate(item.transactionDate)}
          </Text>
          {item.branch ? (
            <Text className="text-xs text-stone-400 mt-1">
              {item.branch.name}
            </Text>
          ) : null}
        </View>

        <View className="items-end gap-1">
          <Text
            className={cn(
              'text-lg font-bold',
              isIssued ? 'text-green-600' : 'text-red-500',
            )}>
            {sign}{formatPoints(points)}
          </Text>
          <Text className="text-xs text-stone-400">
            Saldo: {formatPoints(item.balanceAfter)}
          </Text>
        </View>
      </View>
    </View>
  );
}
