import { SymbolView } from 'expo-symbols';
import { router } from 'expo-router';
import { useState } from 'react';
import { ActivityIndicator, Pressable, ScrollView, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';

import { PointMutationList } from '@/components/point-ledger/point-mutation-list';
import { DateRangeFilterModal } from '@/components/shared/date-range-filter-modal';
import { Button } from '@/components/ui/button';
import { Text } from '@/components/ui/text';
import { SCREEN_HORIZONTAL_PADDING } from '@/constants/layout/screen-layout';
import { usePointLedger } from '@/hooks/use-point-ledger';
import {
  formatDateRangeLabel,
  hasActiveDateRange,
  dateRangeToApiParams,
  type DateRange,
} from '@/lib/date-range-filter';

const BACK_ICON = { ios: 'chevron.left', android: 'arrow_back', web: 'arrow_back' } as const;
const FILTER_ICON = {
  ios: 'line.3.horizontal.decrease.circle',
  android: 'filter_list',
  web: 'filter_list',
} as const;

export default function PointLedgerScreen() {
  const [filterVisible, setFilterVisible] = useState(false);
  const [appliedRange, setAppliedRange] = useState<DateRange>({
    startDate: undefined,
    endDate: undefined,
  });
  const [draftRange, setDraftRange] = useState<DateRange>({
    startDate: undefined,
    endDate: undefined,
  });

  const { dateFrom, dateTo } = dateRangeToApiParams(appliedRange);
  const {
    data,
    isLoading,
    isError,
    refetch,
    fetchNextPage,
    hasNextPage,
    isFetchingNextPage,
  } = usePointLedger({
    dateFrom,
    dateTo,
  });

  const items = data?.items ?? [];

  const hasActiveFilter = hasActiveDateRange(appliedRange);

  const openFilter = () => {
    setDraftRange(appliedRange);
    setFilterVisible(true);
  };

  const handleApplyFilter = () => {
    setAppliedRange(draftRange);
    setFilterVisible(false);
  };

  const handleResetFilter = () => {
    setDraftRange({ startDate: undefined, endDate: undefined });
    setAppliedRange({ startDate: undefined, endDate: undefined });
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

          <Text className="flex-1 text-base font-semibold text-stone-900">
            Riwayat Poin
          </Text>

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
              Gagal memuat riwayat poin
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
            {items.length === 0 ? (
              <View className="items-center px-4 py-12">
                <Text variant="muted">Tidak ada riwayat mutasi poin.</Text>
              </View>
            ) : (
              <View style={{ paddingHorizontal: SCREEN_HORIZONTAL_PADDING }}>
                <PointMutationList items={items} />
              </View>
            )}
            {isFetchingNextPage ? (
              <View className="items-center py-4">
                <ActivityIndicator color="#b45309" />
              </View>
            ) : null}
          </ScrollView>
        )}
      </SafeAreaView>

      <DateRangeFilterModal
        visible={filterVisible}
        title="Filter Tanggal"
        description="Pilih rentang tanggal mutasi poin"
        range={draftRange}
        onRangeChange={setDraftRange}
        onClose={() => setFilterVisible(false)}
        onApply={handleApplyFilter}
        onReset={handleResetFilter}
      />
    </View>
  );
}
