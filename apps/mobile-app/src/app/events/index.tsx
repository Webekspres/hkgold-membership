import { SymbolView } from 'expo-symbols';
import { router } from 'expo-router';
import { useMemo, useState } from 'react';
import { FlatList, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';

import { EventFilterModal } from '@/components/event/event-filter-modal';
import { EventListCard } from '@/components/event/event-list-card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Text } from '@/components/ui/text';
import { getEventList } from '@/services/events';
import { SCREEN_HORIZONTAL_PADDING } from '@/constants/layout/screen-layout';
import {
  EMPTY_DATE_RANGE,
  hasActiveDateRange,
  type DateRange,
} from '@/lib/date-range-filter';
import { filterEventsByDateRange } from '@/lib/filters/filter-events-by-date-range';

const BACK_ICON = { ios: 'chevron.left', android: 'arrow_back', web: 'arrow_back' } as const;
const FILTER_ICON = {
  ios: 'line.3.horizontal.decrease.circle',
  android: 'filter_list',
  web: 'filter_list',
} as const;

const eventList = getEventList();

export default function EventsScreen() {
  const [filterVisible, setFilterVisible] = useState(false);
  const [appliedRange, setAppliedRange] = useState<DateRange>(EMPTY_DATE_RANGE);
  const [draftRange, setDraftRange] = useState<DateRange>(EMPTY_DATE_RANGE);

  const filteredEvents = useMemo(
    () => filterEventsByDateRange(eventList, appliedRange),
    [appliedRange]
  );

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

          <Input
            className="min-w-0 flex-1"
            placeholder="Cari event..."
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

        <FlatList
          data={filteredEvents}
          keyExtractor={(item) => item.id}
          renderItem={({ item }) => <EventListCard event={item} />}
          ItemSeparatorComponent={() => <View className="h-4" />}
          contentContainerStyle={{
            paddingHorizontal: SCREEN_HORIZONTAL_PADDING,
            paddingVertical: 16,
            paddingBottom: 24,
          }}
          showsVerticalScrollIndicator={false}
          ListEmptyComponent={
            <View className="items-center py-12">
              <Text variant="muted">Tidak ada event pada rentang tanggal ini.</Text>
            </View>
          }
        />
      </SafeAreaView>

      <EventFilterModal
        visible={filterVisible}
        range={draftRange}
        onRangeChange={setDraftRange}
        onClose={() => setFilterVisible(false)}
        onApply={handleApplyFilter}
        onReset={handleResetFilter}
      />
    </View>
  );
}
