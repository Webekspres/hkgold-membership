import { SymbolView } from 'expo-symbols';
import { router } from 'expo-router';
import { useMemo, useState } from 'react';
import { FlatList, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';

import { BranchCityFilterDropdown } from '@/components/branch-city-filter-dropdown';
import { BranchListCard } from '@/components/branch-list-card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Text } from '@/components/ui/text';
import { MOCK_BRANCH_LIST } from '@/constants/mock-branches';
import { SCREEN_HORIZONTAL_PADDING } from '@/constants/screen-layout';
import {
  filterBranchesByCity,
  getBranchCityOptions,
} from '@/lib/filter-branches-by-city';

const BACK_ICON = { ios: 'chevron.left', android: 'arrow_back', web: 'arrow_back' } as const;

export default function BranchListScreen() {
  const [selectedCity, setSelectedCity] = useState<string | null>('all');

  const cityOptions = useMemo(() => getBranchCityOptions(MOCK_BRANCH_LIST), []);

  const filteredBranches = useMemo(
    () => filterBranchesByCity(MOCK_BRANCH_LIST, selectedCity),
    [selectedCity]
  );

  const hasActiveFilter = Boolean(selectedCity && selectedCity !== 'all');

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

            <Input
              className="min-w-0 flex-1"
              placeholder="Cari cabang..."
              placeholderTextColor="#a8a29e"
              editable
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
          data={filteredBranches}
          keyExtractor={(item) => item.id.toString()}
          renderItem={({ item }) => <BranchListCard branch={item} />}
          ItemSeparatorComponent={() => <View className="h-4" />}
          contentContainerStyle={{
            paddingHorizontal: SCREEN_HORIZONTAL_PADDING,
            paddingVertical: 16,
            paddingBottom: 24,
          }}
          showsVerticalScrollIndicator={false}
          ListEmptyComponent={
            <View className="items-center py-12">
              <Text variant="muted">Tidak ada cabang di kota ini.</Text>
            </View>
          }
        />
      </SafeAreaView>
    </View>
  );
}
