import { SymbolView } from 'expo-symbols';
import { router } from 'expo-router';
import { useMemo, useState } from 'react';
import { ScrollView, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';

import { RedeemHistoryFilterModal } from '@/components/reward/redeem-history-filter-modal';
import { RedeemHistoryList } from '@/components/reward/redeem-history-list';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Text } from '@/components/ui/text';
import { SCREEN_HORIZONTAL_PADDING } from '@/constants/layout/screen-layout';
import {
  applyRedeemHistoryFilters,
  createDefaultRedeemHistoryFilter,
  getRedeemHistoryPointsBounds,
  hasActiveRedeemHistoryFilter,
  type RedeemHistoryFilterState,
} from '@/lib/filters/filter-redeem-history';
import { getRedeemHistoryList } from '@/services/redeem-history';
import { getRewardCategories } from '@/services/rewards';

const BACK_ICON = { ios: 'chevron.left', android: 'arrow_back', web: 'arrow_back' } as const;
const FILTER_ICON = {
  ios: 'line.3.horizontal.decrease.circle',
  android: 'filter_list',
  web: 'filter_list',
} as const;

const redeemHistoryList = getRedeemHistoryList();
const rewardCategories = getRewardCategories();
const POINTS_BOUNDS = getRedeemHistoryPointsBounds(redeemHistoryList);
const DEFAULT_FILTER = createDefaultRedeemHistoryFilter(POINTS_BOUNDS);

export default function RedeemHistoryScreen() {
  const [filterVisible, setFilterVisible] = useState(false);
  const [appliedFilter, setAppliedFilter] = useState<RedeemHistoryFilterState>(DEFAULT_FILTER);
  const [draftFilter, setDraftFilter] = useState<RedeemHistoryFilterState>(DEFAULT_FILTER);

  const filteredItems = useMemo(
    () => applyRedeemHistoryFilters(redeemHistoryList, appliedFilter),
    [appliedFilter]
  );

  const hasActiveFilter = hasActiveRedeemHistoryFilter(appliedFilter, POINTS_BOUNDS);

  const openFilter = () => {
    setDraftFilter(appliedFilter);
    setFilterVisible(true);
  };

  const handleApplyFilter = () => {
    setAppliedFilter(draftFilter);
    setFilterVisible(false);
  };

  const handleResetFilter = () => {
    setDraftFilter(DEFAULT_FILTER);
    setAppliedFilter(DEFAULT_FILTER);
    setFilterVisible(false);
  };

  return (
    <View className="flex-1 bg-background">
      <SafeAreaView className="flex-1" edges={['top']}>
        <View
          className="flex-row items-center gap-2 border-b border-stone-200 bg-background py-3"
          style={{ paddingHorizontal: SCREEN_HORIZONTAL_PADDING }}>
          <Button variant="outline" size="icon" onPress={() => router.back()}>
            <SymbolView name={BACK_ICON} size={20} tintColor="#44403c" />
          </Button>

          <Input
            className="min-w-0 flex-1"
            placeholder="Cari riwayat redeem..."
            placeholderTextColor="#a8a29e"
            editable
          />

          <Button
            variant="outline"
            size="icon"
            className={hasActiveFilter ? 'border-[#e8a020] bg-[#fffbeb]' : undefined}
            onPress={openFilter}>
            <SymbolView
              name={FILTER_ICON}
              size={20}
              tintColor={hasActiveFilter ? '#b45309' : '#44403c'}
            />
          </Button>
        </View>

        <ScrollView
          showsVerticalScrollIndicator={false}
          contentContainerStyle={{ paddingVertical: 16, paddingBottom: 24 }}>
          {filteredItems.length === 0 ? (
            <View className="items-center px-4 py-12">
              <Text variant="muted">Tidak ada riwayat redeem pada filter ini.</Text>
            </View>
          ) : (
            <RedeemHistoryList items={filteredItems} />
          )}
        </ScrollView>
      </SafeAreaView>

      <RedeemHistoryFilterModal
        visible={filterVisible}
        categories={rewardCategories}
        bounds={POINTS_BOUNDS}
        filter={draftFilter}
        onFilterChange={setDraftFilter}
        onClose={() => setFilterVisible(false)}
        onApply={handleApplyFilter}
        onReset={handleResetFilter}
      />
    </View>
  );
}
