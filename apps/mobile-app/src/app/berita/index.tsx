import { SymbolView } from 'expo-symbols';
import { router } from 'expo-router';
import { useMemo, useState } from 'react';
import {
  ActivityIndicator,
  FlatList,
  Pressable,
  View,
} from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';

import { DateRangeFilterModal } from '@/components/shared/date-range-filter-modal';
import { SearchInput } from '@/components/shared/search-input';
import { NewsArticleCard } from '@/components/berita/news-article-card';
import { Button } from '@/components/ui/button';
import { Text } from '@/components/ui/text';
import { useDebouncedValue } from '@/hooks/use-debounced-value';
import { useNewsList } from '@/hooks/use-news-list';
import { SCREEN_HORIZONTAL_PADDING } from '@/constants/layout/screen-layout';
import {
  EMPTY_DATE_RANGE,
  dateRangeToApiParams,
  hasActiveDateRange,
  type DateRange,
} from '@/lib/date-range-filter';

const BACK_ICON = { ios: 'chevron.left', android: 'arrow_back', web: 'arrow_back' } as const;
const FILTER_ICON = {
  ios: 'line.3.horizontal.decrease.circle',
  android: 'filter_list',
  web: 'filter_list',
} as const;

export default function NewsListScreen() {
  const [filterVisible, setFilterVisible] = useState(false);
  const [appliedRange, setAppliedRange] = useState<DateRange>(EMPTY_DATE_RANGE);
  const [draftRange, setDraftRange] = useState<DateRange>(EMPTY_DATE_RANGE);
  const [search, setSearch] = useState('');
  const debouncedSearch = useDebouncedValue(search, 500);

  const q = useMemo(() => {
    const trimmed = debouncedSearch.trim();
    return trimmed.length > 2 ? trimmed : undefined;
  }, [debouncedSearch]);

  const dateParams = useMemo(() => dateRangeToApiParams(appliedRange), [appliedRange]);

  const {
    articles,
    isLoading,
    isError,
    refetch,
    fetchNextPage,
    hasNextPage,
    isFetchingNextPage,
  } = useNewsList({ q, ...dateParams });

  const openFilter = () => {
    setDraftRange(appliedRange);
    setFilterVisible(true);
  };

  const handleApplyFilter = () => {
    setAppliedRange(draftRange);
    setFilterVisible(false);
  };

  const handleResetFilter = () => {
    setDraftRange(EMPTY_DATE_RANGE);
    setAppliedRange(EMPTY_DATE_RANGE);
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

          <SearchInput
            placeholder="Cari berita..."
            value={search}
            onChangeText={setSearch}
          />

          <Button
            variant="outline"
            size="icon"
            className={hasActiveDateRange(appliedRange) ? 'border-[#e8a020] bg-[#fffbeb]' : undefined}
            onPress={openFilter}>
            <SymbolView
              name={FILTER_ICON}
              size={20}
              tintColor={hasActiveDateRange(appliedRange) ? '#b45309' : '#44403c'}
            />
          </Button>
        </View>

        <FlatList
          data={articles}
          keyExtractor={(item) => item.id}
          renderItem={({ item }) => <NewsArticleCard article={item} fullWidth />}
          ItemSeparatorComponent={() => <View className="h-4" />}
          contentContainerStyle={{
            paddingHorizontal: SCREEN_HORIZONTAL_PADDING,
            paddingVertical: 16,
            paddingBottom: 24,
            flexGrow: 1,
          }}
          showsVerticalScrollIndicator={false}
          onEndReached={() => {
            if (hasNextPage && !isFetchingNextPage) {
              void fetchNextPage();
            }
          }}
          onEndReachedThreshold={0.4}
          ListFooterComponent={
            isFetchingNextPage ? (
              <View className="items-center py-4">
                <ActivityIndicator color="#b45309" />
              </View>
            ) : null
          }
          ListEmptyComponent={
            <View className="items-center justify-center py-12">
              {isLoading ? (
                <ActivityIndicator color="#b45309" />
              ) : isError ? (
                <View className="items-center gap-3 px-4">
                  <Text variant="muted" className="text-center">
                    Gagal memuat berita. Periksa koneksi lalu coba lagi.
                  </Text>
                  <Pressable onPress={() => void refetch()} className="active:opacity-70">
                    <Text className="font-semibold text-[#c4841a]">Coba lagi</Text>
                  </Pressable>
                </View>
              ) : (
                <Text variant="muted">Belum ada berita.</Text>
              )}
            </View>
          }
        />
      </SafeAreaView>

      <DateRangeFilterModal
        visible={filterVisible}
        title="Filter Berita"
        description="Pilih rentang tanggal publikasi"
        range={draftRange}
        onRangeChange={setDraftRange}
        onClose={() => setFilterVisible(false)}
        onApply={handleApplyFilter}
        onReset={handleResetFilter}
      />
    </View>
  );
}
