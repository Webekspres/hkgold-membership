import { SymbolView } from 'expo-symbols';
import { router } from 'expo-router';
import { useState } from 'react';
import { FlatList, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';

import { EventFilterModal } from '@/components/event-filter-modal';
import { EventListCard } from '@/components/event-list-card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Text } from '@/components/ui/text';
import { MOCK_EVENT_LIST } from '@/constants/mock-events';
import { SCREEN_HORIZONTAL_PADDING } from '@/constants/screen-layout';

const BACK_ICON = { ios: 'chevron.left', android: 'arrow_back', web: 'arrow_back' } as const;
const FILTER_ICON = {
  ios: 'line.3.horizontal.decrease.circle',
  android: 'filter_list',
  web: 'filter_list',
} as const;

export default function EventsScreen() {
  const [filterVisible, setFilterVisible] = useState(false);

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

          <Button variant="outline" size="icon" onPress={() => setFilterVisible(true)}>
            <SymbolView name={FILTER_ICON} size={20} tintColor="#44403c" />
          </Button>
        </View>

        <FlatList
          data={MOCK_EVENT_LIST}
          keyExtractor={(item) => item.id}
          renderItem={({ item }) => <EventListCard event={item} />}
          ItemSeparatorComponent={() => <View className="h-4" />}
          contentContainerStyle={{ paddingHorizontal: SCREEN_HORIZONTAL_PADDING, paddingVertical: 16, paddingBottom: 24 }}
          showsVerticalScrollIndicator={false}
          ListEmptyComponent={
            <View className="items-center py-12">
              <Text variant="muted">Belum ada event.</Text>
            </View>
          }
        />
      </SafeAreaView>

      <EventFilterModal visible={filterVisible} onClose={() => setFilterVisible(false)} />
    </View>
  );
}
