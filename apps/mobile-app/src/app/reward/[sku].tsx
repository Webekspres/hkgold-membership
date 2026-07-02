import { useLocalSearchParams } from 'expo-router';
import { useMemo, useState } from 'react';
import { View } from 'react-native';

import { ContentDetailScreen } from '@/components/shared/content-detail-screen';
import { RewardBranchStockCard } from '@/components/reward/reward-branch-stock-card';
import { RewardRedeemDialog } from '@/components/reward/reward-redeem-dialog';
import { ComingSoonScreen } from '@/components/shared/coming-soon-screen';
import { Text } from '@/components/ui/text';
import { getRewardBySku } from '@/services/rewards';
import type { RewardBranchStockItem } from '@/types/reward';

export default function RewardDetailScreen() {
  const { sku } = useLocalSearchParams<{ sku: string }>();
  const [selectedBranchStock, setSelectedBranchStock] = useState<RewardBranchStockItem | null>(
    null
  );
  const [redeemDialogOpen, setRedeemDialogOpen] = useState(false);

  const reward = useMemo(() => {
    if (typeof sku !== 'string') {
      return null;
    }

    return getRewardBySku(sku);
  }, [sku]);

  if (!reward) {
    return <ComingSoonScreen title="Detail Reward" />;
  }

  const openRedeemDialog = (stock: RewardBranchStockItem) => {
    setSelectedBranchStock(stock);
    setRedeemDialogOpen(true);
  };

  return (
    <>
      <ContentDetailScreen images={reward.images} title={reward.name}>
        <View className="gap-2">
          <Text variant="muted" className="text-xs uppercase tracking-wide">
            {reward.categoryName}
          </Text>
          <Text className="text-2xl font-semibold leading-snug text-stone-900">
            {reward.name}
          </Text>
          <Text variant="muted" className="text-sm">
            SKU: {reward.sku}
          </Text>
        </View>

        <Text className="text-sm leading-relaxed text-stone-700">{reward.description}</Text>

        <View className="rounded-lg bg-[#fffbeb] px-3 py-3">
          <Text className="text-xs font-semibold uppercase tracking-wide text-[#c4841a]">
            Poin dibutuhkan
          </Text>
          <Text className="mt-1 text-2xl font-bold text-[#b45309]">
            {reward.pointsRequired.toLocaleString('id-ID')}
          </Text>
          <Text className="text-xs font-medium text-[#c4841a]">poin</Text>
        </View>

        <View className="gap-3">
          <Text className="text-base font-semibold text-stone-900">Stok per Cabang</Text>
          {reward.branchStocks.map((stock) => (
            <RewardBranchStockCard
              key={stock.branchId}
              stock={stock}
              onRedeem={openRedeemDialog}
            />
          ))}
        </View>
      </ContentDetailScreen>

      <RewardRedeemDialog
        open={redeemDialogOpen}
        onOpenChange={setRedeemDialogOpen}
        branchStock={selectedBranchStock}
      />
    </>
  );
}
