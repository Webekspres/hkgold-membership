import { SymbolView } from 'expo-symbols';
import { router } from 'expo-router';
import { useMemo, useState } from 'react';
import { ScrollView, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';

import { RewardCatalogGrid } from '@/components/reward-catalog-grid';
import { RewardFilterModal } from '@/components/reward-filter-modal';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Text } from '@/components/ui/text';
import {
  MOCK_REWARD_CATEGORIES,
  MOCK_REWARD_LIST,
} from '@/constants/mock-rewards';
import { SCREEN_HORIZONTAL_PADDING } from '@/constants/screen-layout';
import {
  applyRewardFilters,
  createDefaultRewardFilter,
  getRewardPointsBounds,
  hasActiveRewardFilter,
  type RewardFilterState,
} from '@/lib/filter-rewards';

const BACK_ICON = { ios: 'chevron.left', android: 'arrow_back', web: 'arrow_back' } as const;
const FILTER_ICON = {
  ios: 'line.3.horizontal.decrease.circle',
  android: 'filter_list',
  web: 'filter_list',
} as const;

const POINTS_BOUNDS = getRewardPointsBounds(MOCK_REWARD_LIST);
const DEFAULT_FILTER = createDefaultRewardFilter(POINTS_BOUNDS);

export default function RewardListScreen() {
  const [filterVisible, setFilterVisible] = useState(false);
  const [appliedFilter, setAppliedFilter] = useState<RewardFilterState>(DEFAULT_FILTER);
  const [draftFilter, setDraftFilter] = useState<RewardFilterState>(DEFAULT_FILTER);

  const filteredRewards = useMemo(
    () => applyRewardFilters(MOCK_REWARD_LIST, appliedFilter),
    [appliedFilter]
  );

  const hasActiveFilter = hasActiveRewardFilter(appliedFilter, POINTS_BOUNDS);

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
            placeholder="Cari reward..."
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
          {filteredRewards.length === 0 ? (
            <View className="items-center px-4 py-12">
              <Text variant="muted">Tidak ada hadiah pada filter ini.</Text>
            </View>
          ) : (
            <RewardCatalogGrid rewards={filteredRewards} />
          )}
        </ScrollView>
      </SafeAreaView>

      <RewardFilterModal
        visible={filterVisible}
        categories={MOCK_REWARD_CATEGORIES}
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
