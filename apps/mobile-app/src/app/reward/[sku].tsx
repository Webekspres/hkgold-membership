import { router, useLocalSearchParams } from 'expo-router';
import { useCallback, useState } from 'react';
import { ActivityIndicator, Pressable, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';

import { ContentDetailScreen } from '@/components/shared/content-detail-screen';
import { createPullToRefreshControl } from '@/components/shared/pull-to-refresh';
import { RewardBranchStockCard } from '@/components/reward/reward-branch-stock-card';
import { RewardRedeemDialog } from '@/components/reward/reward-redeem-dialog';
import { Text } from '@/components/ui/text';
import { useCreateRedeemToken } from '@/hooks/use-create-redeem-token';
import { usePullToRefresh } from '@/hooks/use-pull-to-refresh';
import { useRewardDetail } from '@/hooks/use-reward-detail';
import { filterAvailableBranchStocks } from '@/lib/reward/filter-available-branch-stocks';
import { toast } from '@/lib/sonner';
import type { RewardBranchStockItem } from '@/types/reward';

export default function RewardDetailScreen() {
  const { sku } = useLocalSearchParams<{ sku: string }>();
  const [selectedBranchStock, setSelectedBranchStock] =
    useState<RewardBranchStockItem | null>(null);
  const [redeemDialogOpen, setRedeemDialogOpen] = useState(false);

  const { reward, isLoading, isError, refetch } = useRewardDetail(
    typeof sku === 'string' ? sku : undefined,
  );
  const createRedeem = useCreateRedeemToken();

  const refresh = useCallback(() => refetch(), [refetch]);
  const { refreshing, onRefresh } = usePullToRefresh(refresh);

  const openRedeemDialog = (stock: RewardBranchStockItem) => {
    setSelectedBranchStock(stock);
    setRedeemDialogOpen(true);
  };

  const handleConfirmRedeem = () => {
    if (!reward || !selectedBranchStock) return;

    createRedeem.mutate(
      { rewardId: reward.id, branchId: selectedBranchStock.branchId },
      {
        onSuccess: () => {
          setRedeemDialogOpen(false);
          toast.success('Token redeem berhasil dibuat', { duration: 3000 });
          router.push('/card/redeem-qr');
        },
        onError: (error) => {
          toast.error(error instanceof Error ? error.message : 'Gagal menukar hadiah', {
            duration: 4000,
          });
        },
      },
    );
  };

  if (isLoading) {
    return (
      <View className="flex-1 items-center justify-center bg-background">
        <ActivityIndicator color="#b45309" />
      </View>
    );
  }

  const availableBranchStocks = reward ? filterAvailableBranchStocks(reward.branchStocks) : [];

  if (isError || !reward) {
    return (
      <SafeAreaView className="flex-1 items-center justify-center gap-3 bg-background px-6">
        <Text className="text-center text-base font-semibold text-stone-900">
          Reward tidak ditemukan
        </Text>
        <Text variant="muted" className="text-center">
          Konten mungkin sudah dihapus atau koneksi gagal.
        </Text>
        {isError ? (
          <Pressable onPress={() => void refetch()} className="active:opacity-70">
            <Text className="font-semibold text-[#c4841a]">Coba lagi</Text>
          </Pressable>
        ) : null}
      </SafeAreaView>
    );
  }

  return (
    <>
      <ContentDetailScreen
        images={reward.images}
        title={reward.name}
        refreshControl={createPullToRefreshControl({
          refreshing,
          onRefresh,
        })}>
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
          {availableBranchStocks.length === 0 ? (
            <Text variant="muted">Stok habis di semua cabang.</Text>
          ) : (
            availableBranchStocks.map((stock) => (
              <RewardBranchStockCard
                key={stock.branchId}
                stock={stock}
                onRedeem={openRedeemDialog}
              />
            ))
          )}
        </View>
      </ContentDetailScreen>

      <RewardRedeemDialog
        open={redeemDialogOpen}
        onOpenChange={setRedeemDialogOpen}
        branchStock={selectedBranchStock}
        isConfirming={createRedeem.isPending}
        onConfirm={handleConfirmRedeem}
      />
    </>
  );
}
