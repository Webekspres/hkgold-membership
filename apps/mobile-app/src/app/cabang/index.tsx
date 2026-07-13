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

import { BranchCityFilterDropdown } from '@/components/branch/branch-city-filter-dropdown';
import { BranchListCard } from '@/components/branch/branch-list-card';
import { SearchInput } from '@/components/shared/search-input';
import { Button } from '@/components/ui/button';
import { Text } from '@/components/ui/text';
import { useBranchCities } from '@/hooks/use-branch-cities';
import { useBranchesList } from '@/hooks/use-branches-list';
import { useDebouncedValue } from '@/hooks/use-debounced-value';
import { SCREEN_HORIZONTAL_PADDING } from '@/constants/layout/screen-layout';

const BACK_ICON = { ios: 'chevron.left', android: 'arrow_back', web: 'arrow_back' } as const;

export default function BranchListScreen() {
  const [selectedCity, setSelectedCity] = useState<string | null>('all');
  const [search, setSearch] = useState('');
  const debouncedSearch = useDebouncedValue(search, 500);

  const q = useMemo(() => {
    const trimmed = debouncedSearch.trim();
    return trimmed.length > 2 ? trimmed : undefined;
  }, [debouncedSearch]);

  const city = selectedCity && selectedCity !== 'all' ? selectedCity : undefined;
  const hasActiveFilter = Boolean(city);

  const { options: cityOptions } = useBranchCities();
  const {
    branches,
    isLoading,
    isError,
    refetch,
    fetchNextPage,
    hasNextPage,
    isFetchingNextPage,
  } = useBranchesList({ q, city });

  return (
    <View className="flex-1 bg-background">
      <SafeAreaView className="flex-1" edges={['top']}>
        <View
          className="gap-3 border-b border-stone-200 bg-background py-3"
          style={{ paddingHorizontal: SCREEN_HORIZONTAL_PADDING }}>
          <View className="flex-row items-center gap-2">
            <Button variant="outline" size="icon" onPress={() => router.back()}>
              <SymbolView name={BACK_ICON} size={20} tintColor="#44403c" />
            </Button>

            <SearchInput
              placeholder="Cari cabang..."
              value={search}
              onChangeText={setSearch}
            />
          </View>

          <BranchCityFilterDropdown
            data={cityOptions}
            value={selectedCity}
            onChange={setSelectedCity}
            active={hasActiveFilter}
          />
        </View>

        <FlatList
          data={branches}
          keyExtractor={(item) => item.id.toString()}
          renderItem={({ item }) => <BranchListCard branch={item} />}
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
                    Gagal memuat cabang. Periksa koneksi lalu coba lagi.
                  </Text>
                  <Pressable onPress={() => void refetch()} className="active:opacity-70">
                    <Text className="font-semibold text-[#c4841a]">Coba lagi</Text>
                  </Pressable>
                </View>
              ) : (
                <Text variant="muted">Tidak ada cabang.</Text>
              )}
            </View>
          }
        />
      </SafeAreaView>
    </View>
  );
}
