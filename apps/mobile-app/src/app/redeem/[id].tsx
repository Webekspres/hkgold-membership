import { useLocalSearchParams } from 'expo-router';
import { useMemo } from 'react';
import { View } from 'react-native';

import { ComingSoonScreen } from '@/components/shared/coming-soon-screen';
import { ContentDetailScreen } from '@/components/shared/content-detail-screen';
import { Text } from '@/components/ui/text';
import { formatRedeemDate } from '@/lib/format/format-redeem-date';
import { getRedeemHistoryItemById } from '@/services/redeem-history';

const STATUS_LABELS = {
  selesai: 'Selesai',
  diproses: 'Diproses',
  ditolak: 'Ditolak',
} as const;

export default function RedeemHistoryDetailScreen() {
  const { id } = useLocalSearchParams<{ id: string }>();

  const item = useMemo(() => {
    if (typeof id !== 'string') {
      return null;
    }

    return getRedeemHistoryItemById(id);
  }, [id]);

  if (!item) {
    return <ComingSoonScreen title="Detail Riwayat Redeem" />;
  }

  return (
    <ContentDetailScreen images={[item.image]} title={item.name}>
      <View className="gap-2">
        <Text variant="muted" className="text-xs uppercase tracking-wide">
          {item.categoryName}
        </Text>
        <Text className="text-2xl font-semibold leading-snug text-stone-900">{item.name}</Text>
        <Text variant="muted" className="text-sm">
          SKU: {item.sku}
        </Text>
      </View>

      <View className="rounded-lg bg-[#fffbeb] px-3 py-3">
        <Text className="text-xs font-semibold uppercase tracking-wide text-[#c4841a]">
          Poin digunakan
        </Text>
        <Text className="mt-1 text-2xl font-bold text-[#b45309]">
          {item.pointsRequired.toLocaleString('id-ID')}
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
          <Text className="text-sm font-medium text-stone-800">{item.branchName}</Text>
        </View>

        <View className="gap-1">
          <Text variant="muted" className="text-xs uppercase tracking-wide">
            Status
          </Text>
          <Text className="text-sm font-medium text-stone-800">{STATUS_LABELS[item.status]}</Text>
        </View>
      </View>
    </ContentDetailScreen>
  );
}
