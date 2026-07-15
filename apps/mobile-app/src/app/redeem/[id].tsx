import { useLocalSearchParams } from 'expo-router';
import { ActivityIndicator, Pressable, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';

import { ContentDetailScreen } from '@/components/shared/content-detail-screen';
import { Text } from '@/components/ui/text';
import { useRedeemHistoryById } from '@/hooks/use-redeem-history';
import { formatRedeemDate } from '@/lib/format/format-redeem-date';
import { formatRedeemStatus } from '@/lib/format/format-redeem-status';

export default function RedeemHistoryDetailScreen() {
  const { id } = useLocalSearchParams<{ id: string }>();
  const { item, isLoading, isError, refetch } = useRedeemHistoryById(
    typeof id === 'string' ? id : undefined,
  );

  if (isLoading) {
    return (
      <View className="flex-1 items-center justify-center bg-background">
        <ActivityIndicator color="#b45309" />
      </View>
    );
  }

  if (isError || !item) {
    return (
      <SafeAreaView className="flex-1 items-center justify-center gap-3 bg-background px-6">
        <Text className="text-center text-base font-semibold text-stone-900">
          Detail riwayat tidak ditemukan
        </Text>
        <Text variant="muted" className="text-center">
          Data mungkin sudah dihapus atau koneksi gagal.
        </Text>
        {isError ? (
          <Pressable onPress={() => void refetch()} className="active:opacity-70">
            <Text className="font-semibold text-[#c4841a]">Coba lagi</Text>
          </Pressable>
        ) : null}
      </SafeAreaView>
    );
  }

  const images = item.reward.imageUrl ? [item.reward.imageUrl] : [];

  return (
    <ContentDetailScreen images={images} title={item.reward.name}>
      <View className="gap-2">
        <Text variant="muted" className="text-xs uppercase tracking-wide">
          {item.invoiceNumber}
        </Text>
        <Text className="text-2xl font-semibold leading-snug text-stone-900">
          {item.reward.name}
        </Text>
        <Text variant="muted" className="text-sm">
          SKU: {item.reward.sku}
        </Text>
      </View>

      <View className="rounded-lg bg-[#fffbeb] px-3 py-3">
        <Text className="text-xs font-semibold uppercase tracking-wide text-[#c4841a]">
          Poin digunakan
        </Text>
        <Text className="mt-1 text-2xl font-bold text-[#b45309]">
          {item.pointsRedeemed.toLocaleString('id-ID')}
        </Text>
        <Text className="text-xs font-medium text-[#c4841a]">poin</Text>
      </View>

      <View className="gap-3 rounded-xl border border-stone-100 bg-white p-4">
        <View className="gap-1">
          <Text variant="muted" className="text-xs uppercase tracking-wide">
            Tanggal redeem
          </Text>
          <Text className="text-sm font-medium text-stone-800">
            {formatRedeemDate(item.redeemedAt)}
          </Text>
        </View>

        <View className="gap-1">
          <Text variant="muted" className="text-xs uppercase tracking-wide">
            Cabang
          </Text>
          <Text className="text-sm font-medium text-stone-800">{item.branch.name}</Text>
        </View>

        <View className="gap-1">
          <Text variant="muted" className="text-xs uppercase tracking-wide">
            Status
          </Text>
          <Text className="text-sm font-medium text-stone-800">
            {formatRedeemStatus(item.status)}
          </Text>
        </View>
      </View>
    </ContentDetailScreen>
  );
}
