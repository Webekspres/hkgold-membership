import { useQueryClient } from '@tanstack/react-query';
import { router } from 'expo-router';
import { useCallback, useEffect, useRef, useState } from 'react';
import { ActivityIndicator, ScrollView, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';

import { ActiveRedeemCountdown } from '@/components/card/active-redeem-countdown';
import { QrCodeCard } from '@/components/card/qr-code-card';
import { ProfileLastRewardCard } from '@/components/profile/profile-last-reward-card';
import { GoldButton } from '@/components/shared/gold-button';
import { createPullToRefreshControl } from '@/components/shared/pull-to-refresh';
import { Button } from '@/components/ui/button';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import { Text } from '@/components/ui/text';
import { BottomTabInset } from '@/config/theme';
import { useActiveRedeem } from '@/hooks/use-active-redeem';
import { useCancelRedeem } from '@/hooks/use-cancel-redeem';
import { MEMBER_ME_QUERY_KEY } from '@/hooks/use-my-profile';
import { usePullToRefresh } from '@/hooks/use-pull-to-refresh';
import { mapActiveRedeemToReward } from '@/lib/active-redeem/map-active-redeem-reward';
import { copyRedeemToken } from '@/lib/clipboard/copy-redeem-token';
import { toast } from '@/lib/sonner';
import { fetchRedeemTokenStatus } from '@/services/redeem';

export default function RedeemQrScreen() {
  const queryClient = useQueryClient();
  const { activeRedeem, isLoading, isFetched, refetch } = useActiveRedeem();
  const cancelRedeem = useCancelRedeem();
  const [cancelOpen, setCancelOpen] = useState(false);
  const redeemIdRef = useRef<string | null>(null);
  const navigatingRef = useRef(false);

  useEffect(() => {
    if (activeRedeem?.redeemId) {
      redeemIdRef.current = activeRedeem.redeemId;
    }
  }, [activeRedeem?.redeemId]);

  const leaveForStatus = useCallback(
    async (redeemId: string) => {
      if (navigatingRef.current) return true;
      const status = await fetchRedeemTokenStatus(redeemId).catch(() => null);
      if (!status || status.status === 'active') {
        return false;
      }

      navigatingRef.current = true;
      void queryClient.invalidateQueries({ queryKey: ['redeem', 'active'] });
      void queryClient.invalidateQueries({ queryKey: ['redeem', 'history'] });
      void queryClient.invalidateQueries({ queryKey: MEMBER_ME_QUERY_KEY });

      if (status.status === 'completed' && status.invoiceId) {
        toast.success('Redeem berhasil', { duration: 3000 });
        router.replace({ pathname: '/redeem/[id]', params: { id: status.invoiceId } });
        return true;
      }

      toast.success(
        status.status === 'released'
          ? 'Klaim sudah dibatalkan'
          : 'Token redeem sudah kedaluwarsa',
        { duration: 3000 },
      );
      router.replace('/card');
      return true;
    },
    [queryClient],
  );

  useEffect(() => {
    if (!isFetched || activeRedeem || navigatingRef.current) {
      return;
    }

    const redeemId = redeemIdRef.current;
    if (!redeemId) {
      router.replace('/card');
      return;
    }

    void leaveForStatus(redeemId).then((left) => {
      if (!left && !navigatingRef.current) {
        router.replace('/card');
      }
    });
  }, [isFetched, activeRedeem, leaveForStatus]);

  const refreshStatus = useCallback(async () => {
    const redeemId = redeemIdRef.current;
    await refetch();
    if (redeemId) {
      await leaveForStatus(redeemId);
    }
  }, [leaveForStatus, refetch]);

  const { refreshing, onRefresh } = usePullToRefresh(refreshStatus);

  async function handleConfirmCancel() {
    if (!activeRedeem || cancelRedeem.isPending) return;
    try {
      await cancelRedeem.mutateAsync(activeRedeem.redeemId);
      navigatingRef.current = true;
      setCancelOpen(false);
      toast.success('Klaim berhasil dibatalkan. Poin dikembalikan.', {
        duration: 3000,
      });
      router.replace('/card');
    } catch (error) {
      toast.error(
        error instanceof Error ? error.message : 'Gagal membatalkan klaim',
        { duration: 4000 },
      );
    }
  }

  if (isLoading || !activeRedeem) {
    return (
      <View className="flex-1 items-center justify-center bg-background">
        <ActivityIndicator color="#b45309" />
      </View>
    );
  }

  const activeRedeemReward = mapActiveRedeemToReward(activeRedeem);

  return (
    <View className="flex-1 bg-background">
      <SafeAreaView className="flex-1" style={{ paddingBottom: 4 }}>
        <ScrollView
          className="flex-1"
          contentContainerClassName="gap-4 px-4 pt-4"
          contentContainerStyle={{ paddingBottom: BottomTabInset + 16 }}
          showsVerticalScrollIndicator={false}
          refreshControl={createPullToRefreshControl({
            refreshing,
            onRefresh,
          })}>
          <View className="gap-1">
            <Text variant="h3" className="text-stone-900">
              Klaim Reward
            </Text>
            <Text variant="muted">Tunjukkan QR ke kasir</Text>
          </View>

          <QrCodeCard
            value={activeRedeem.tokenCode}
            label={activeRedeem.tokenCode}
            onPressLabel={() => void copyRedeemToken(activeRedeem.tokenCode)}
            copyAccessibilityLabel="Salin token redeem"
          />

          <ProfileLastRewardCard
            title="Hadiah sedang diklaim"
            reward={activeRedeemReward}
            pressable={false}
            footer={<ActiveRedeemCountdown expiresAt={activeRedeem.expiresAt} />}
          />

          <GoldButton
            label="Kembali ke Kartu Member"
            width="full"
            variant="outline"
            onPress={() => router.replace('/card')}
          />

          <Button
            variant="destructive"
            disabled={cancelRedeem.isPending}
            onPress={() => setCancelOpen(true)}>
            <Text>Batalkan klaim</Text>
          </Button>
        </ScrollView>
      </SafeAreaView>

      <Dialog open={cancelOpen} onOpenChange={setCancelOpen}>
        <DialogContent className="gap-4">
          <DialogHeader>
            <DialogTitle>Batalkan klaim?</DialogTitle>
            <DialogDescription>
              Yakin batalkan? Poin akan dikembalikan ke saldo Anda.
            </DialogDescription>
          </DialogHeader>
          <DialogFooter>
            <Button
              variant="outline"
              disabled={cancelRedeem.isPending}
              onPress={() => setCancelOpen(false)}>
              <Text>Tidak</Text>
            </Button>
            <Button
              variant="destructive"
              disabled={cancelRedeem.isPending}
              onPress={() => {
                void handleConfirmCancel();
              }}>
              <Text>{cancelRedeem.isPending ? 'Membatalkan...' : 'Ya, batalkan'}</Text>
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </View>
  );
}
