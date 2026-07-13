import { SymbolView } from 'expo-symbols';
import { useCallback, useMemo, useState } from 'react';
import { ActivityIndicator, FlatList, Pressable, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';

import { RewardCatalogCard } from '@/components/reward/reward-catalog-card';
import { RewardFilterModal } from '@/components/reward/reward-filter-modal';
import { createPullToRefreshControl } from '@/components/shared/pull-to-refresh';
import { SearchInput } from '@/components/shared/search-input';
import { Button } from '@/components/ui/button';
import { Text } from '@/components/ui/text';
import {
  GRID_COLUMN_GAP,
  GRID_HORIZONTAL_PADDING,
} from '@/constants/layout/grid-layout';
import { SCREEN_HORIZONTAL_PADDING } from '@/constants/layout/screen-layout';
import { usePullToRefresh } from '@/hooks/use-pull-to-refresh';
import { useRewardCatalog } from '@/hooks/use-reward-catalog';
import { useRewardCategories } from '@/hooks/use-reward-categories';
import { useDebouncedValue } from '@/hooks/use-debounced-value';
import {
  createDefaultRewardFilter,
  hasActiveRewardFilter,
} from '@/lib/filters/filter-rewards';
import type { RewardFilterState, RewardPointsBounds } from '@/types/filter';
import type { RewardCatalogItem } from '@/types/reward';

const FILTER_ICON = {
  ios: 'line.3.horizontal.decrease.circle',
  android: 'filter_list',
  web: 'filter_list',
} as const;

/** Bounds tetap — list server-side; slider butuh min/max. */
const POINTS_BOUNDS: RewardPointsBounds = { min: 0, max: 500_000 };

export default function RewardTabScreen() {
  const [search, setSearch] = useState('');
  const debouncedSearch = useDebouncedValue(search, 500);
  const searchParam = useMemo(() => {
    const trimmed = debouncedSearch.trim();
    return trimmed.length > 2 ? trimmed : undefined;
  }, [debouncedSearch]);

  const [filterVisible, setFilterVisible] = useState(false);
  const [appliedFilter, setAppliedFilter] = useState<RewardFilterState>(() =>
    createDefaultRewardFilter(POINTS_BOUNDS),
  );
  const [draftFilter, setDraftFilter] = useState<RewardFilterState>(() =>
    createDefaultRewardFilter(POINTS_BOUNDS),
  );

  const { categories, refetch: refetchCategories } = useRewardCategories();
  const {
    rewards,
    isLoading,
    isError,
    isFetchingNextPage,
    hasNextPage,
    fetchNextPage,
    refetch,
  } = useRewardCatalog({
    search: searchParam,
    appliedFilter,
    pointsBounds: POINTS_BOUNDS,
  });

  const refresh = useCallback(
    () => Promise.all([refetch(), refetchCategories()]),
    [refetch, refetchCategories],
  );
  const { refreshing, onRefresh } = usePullToRefresh(refresh);

  const filterActive = hasActiveRewardFilter(appliedFilter, POINTS_BOUNDS);

  const openFilter = useCallback(() => {
    setDraftFilter(appliedFilter);
    setFilterVisible(true);
  }, [appliedFilter]);

  const handleApplyFilter = useCallback(() => {
    setAppliedFilter(draftFilter);
    setFilterVisible(false);
  }, [draftFilter]);

  const handleResetFilter = useCallback(() => {
    const reset = createDefaultRewardFilter(POINTS_BOUNDS);
    setDraftFilter(reset);
    setAppliedFilter(reset);
    setFilterVisible(false);
  }, []);

  const handleEndReached = useCallback(() => {
    if (hasNextPage && !isFetchingNextPage) {
      void fetchNextPage();
    }
  }, [hasNextPage, isFetchingNextPage, fetchNextPage]);

  const emptyMessage = useMemo(() => {
    if (isError) return null;
    return 'Tidak ada hadiah tersedia.';
  }, [isError]);

  return (
    <View className="flex-1 bg-background">
      <SafeAreaView className="flex-1" edges={['top']}>
        <View
          className="flex-row items-center gap-2 border-b border-stone-200 bg-background py-3"
          style={{ paddingHorizontal: SCREEN_HORIZONTAL_PADDING }}>
          <SearchInput
            placeholder="Cari reward..."
            value={search}
            onChangeText={setSearch}
          />
          <Button
            variant="outline"
            size="icon"
            className={filterActive ? 'border-[#e8a020] bg-[#fffbeb]' : undefined}
            onPress={openFilter}>
            <SymbolView
              name={FILTER_ICON}
              size={20}
              tintColor={filterActive ? '#b45309' : '#44403c'}
            />
          </Button>
        </View>

        {isLoading ? (
          <View className="flex-1 items-center justify-center">
            <ActivityIndicator color="#b45309" />
          </View>
        ) : (
          <FlatList
            data={rewards}
            keyExtractor={(item: RewardCatalogItem) => item.id}
            numColumns={2}
            renderItem={({ item }) => <RewardCatalogCard reward={item} />}
            columnWrapperStyle={{
              columnGap: GRID_COLUMN_GAP,
              paddingHorizontal: GRID_HORIZONTAL_PADDING,
            }}
            ItemSeparatorComponent={() => <View style={{ height: GRID_COLUMN_GAP }} />}
            onEndReached={handleEndReached}
            onEndReachedThreshold={0.5}
            ListFooterComponent={
              isFetchingNextPage ? (
                <ActivityIndicator color="#b45309" className="my-4" />
              ) : null
            }
            ListEmptyComponent={
              <View className="items-center gap-3 px-4 py-12">
                {isError ? (
                  <>
                    <Text variant="muted" className="text-center">
                      Gagal memuat reward. Periksa koneksi lalu coba lagi.
                    </Text>
                    <Pressable onPress={() => void refetch()} className="active:opacity-70">
                      <Text className="font-semibold text-[#c4841a]">Coba lagi</Text>
                    </Pressable>
                  </>
                ) : (
                  <Text variant="muted">{emptyMessage}</Text>
                )}
              </View>
            }
            contentContainerStyle={{ paddingVertical: 16, paddingBottom: 24, flexGrow: 1 }}
            showsVerticalScrollIndicator={false}
            refreshControl={createPullToRefreshControl({
              refreshing,
              onRefresh,
            })}
          />
        )}
      </SafeAreaView>

      <RewardFilterModal
        visible={filterVisible}
        categories={categories}
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
