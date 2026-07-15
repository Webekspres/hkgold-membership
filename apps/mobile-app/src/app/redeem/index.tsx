import { SymbolView } from 'expo-symbols';
import { router } from 'expo-router';
import { useEffect, useMemo, useState } from 'react';
import { ActivityIndicator, Pressable, ScrollView, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';

import { RedeemHistoryFilterModal } from '@/components/reward/redeem-history-filter-modal';
import { RedeemHistoryList } from '@/components/reward/redeem-history-list';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Text } from '@/components/ui/text';
import { SCREEN_HORIZONTAL_PADDING } from '@/constants/layout/screen-layout';
import { useRedeemHistory } from '@/hooks/use-redeem-history';
import { useRewardCategories } from '@/hooks/use-reward-categories';
import {
  applyRedeemHistoryFilters,
  createDefaultRedeemHistoryFilter,
  getRedeemHistoryPointsBounds,
  hasActiveRedeemHistoryFilter,
  type RedeemHistoryFilterState,
} from '@/lib/filters/filter-redeem-history';

const BACK_ICON = { ios: 'chevron.left', android: 'arrow_back', web: 'arrow_back' } as const;
const FILTER_ICON = {
  ios: 'line.3.horizontal.decrease.circle',
  android: 'filter_list',
  web: 'filter_list',
} as const;

export default function RedeemHistoryScreen() {
  const { items, isLoading, isError, refetch, fetchNextPage, hasNextPage, isFetchingNextPage } =
    useRedeemHistory();
  const { categories } = useRewardCategories();

  const pointsBounds = useMemo(() => getRedeemHistoryPointsBounds(items), [items]);
  const defaultFilter = useMemo(
    () => createDefaultRedeemHistoryFilter(pointsBounds),
    [pointsBounds],
  );

  const [filterVisible, setFilterVisible] = useState(false);
  const [appliedFilter, setAppliedFilter] = useState<RedeemHistoryFilterState>(defaultFilter);
  const [draftFilter, setDraftFilter] = useState<RedeemHistoryFilterState>(defaultFilter);

  useEffect(() => {
    setAppliedFilter(defaultFilter);
    setDraftFilter(defaultFilter);
  }, [defaultFilter]);

  const filteredItems = useMemo(
    () => applyRedeemHistoryFilters(items, appliedFilter),
    [items, appliedFilter],
  );

  const hasActiveFilter = hasActiveRedeemHistoryFilter(appliedFilter, pointsBounds);

  const openFilter = () => {
    setDraftFilter(appliedFilter);
    setFilterVisible(true);
  };

  const handleApplyFilter = () => {
    setAppliedFilter(draftFilter);
    setFilterVisible(false);
  };

  const handleResetFilter = () => {
    setDraftFilter(defaultFilter);
    setAppliedFilter(defaultFilter);
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

        {isLoading ? (
          <View className="flex-1 items-center justify-center">
            <ActivityIndicator color="#b45309" />
          </View>
        ) : isError ? (
          <View className="flex-1 items-center justify-center gap-3 px-6">
            <Text className="text-center text-base font-semibold text-stone-900">
              Gagal memuat riwayat
            </Text>
            <Pressable onPress={() => void refetch()} className="active:opacity-70">
              <Text className="font-semibold text-[#c4841a]">Coba lagi</Text>
            </Pressable>
          </View>
        ) : (
          <ScrollView
            showsVerticalScrollIndicator={false}
            contentContainerStyle={{ paddingVertical: 16, paddingBottom: 24 }}
            onScroll={({ nativeEvent }) => {
              const { layoutMeasurement, contentOffset, contentSize } = nativeEvent;
              const nearBottom =
                layoutMeasurement.height + contentOffset.y >= contentSize.height - 120;
              if (nearBottom && hasNextPage && !isFetchingNextPage) {
                void fetchNextPage();
              }
            }}
            scrollEventThrottle={200}>
            {filteredItems.length === 0 ? (
              <View className="items-center px-4 py-12">
                <Text variant="muted">Tidak ada riwayat redeem pada filter ini.</Text>
              </View>
            ) : (
              <RedeemHistoryList items={filteredItems} />
            )}
            {isFetchingNextPage ? (
              <View className="items-center py-4">
                <ActivityIndicator color="#b45309" />
              </View>
            ) : null}
          </ScrollView>
        )}
      </SafeAreaView>

      <RedeemHistoryFilterModal
        visible={filterVisible}
        categories={categories}
        bounds={pointsBounds}
        filter={draftFilter}
        onFilterChange={setDraftFilter}
        onClose={() => setFilterVisible(false)}
        onApply={handleApplyFilter}
        onReset={handleResetFilter}
      />
    </View>
  );
}
