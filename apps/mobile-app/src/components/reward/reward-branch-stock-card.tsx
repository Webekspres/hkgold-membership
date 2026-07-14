import { SymbolView } from 'expo-symbols';
import { View } from 'react-native';

import { GoldButton } from '@/components/shared/gold-button';
import { Button } from '@/components/ui/button';
import { Text } from '@/components/ui/text';
import type { RewardBranchStockItem } from '@/types/reward';
import { formatBranchLocation } from '@/lib/format/format-branch-location';
import { openLocationUrl } from '@/lib/open-location-url';
import { getAvailableBranchStock } from '@/services/rewards';

const MAP_ICON = {
  ios: 'map',
  android: 'map',
  web: 'map',
} as const;

type RewardBranchStockCardProps = {
  stock: RewardBranchStockItem;
  onRedeem: (stock: RewardBranchStockItem) => void;
};

export function RewardBranchStockCard({ stock, onRedeem }: RewardBranchStockCardProps) {
  const availableStock = getAvailableBranchStock(stock);

  return (
    <View className="gap-3 rounded-xl border border-stone-200 bg-white p-4 shadow-sm shadow-stone-900/10">
      <View className="flex-row items-start gap-3">
        <View className="min-w-0 flex-1 gap-1">
          <Text className="text-base font-semibold leading-snug text-stone-900" numberOfLines={2}>
            {stock.branchName}
          </Text>
          <Text variant="muted" className="text-sm">
            {formatBranchLocation(stock.subdistrict, stock.city)}
          </Text>
          <Text className="text-sm font-medium text-[#b45309]">
            Sisa stok: {availableStock.toLocaleString('id-ID')}
          </Text>
        </View>

        <Button
          variant="outline"
          size="icon"
          className="border-[#e8a020] bg-[#fffbeb]"
          disabled={!stock.locationUrl}
          onPress={() => openLocationUrl(stock.locationUrl)}>
          <SymbolView name={MAP_ICON} size={20} tintColor="#b45309" />
        </Button>
      </View>

      <GoldButton
        variant="outline"
        width="full"
        label="Tukarkan pada cabang ini"
        onPress={() => onRedeem(stock)}
      />
    </View>
  );
}
