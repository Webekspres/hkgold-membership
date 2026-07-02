import dayjs from 'dayjs';
import 'dayjs/locale/id';
import { Modal, Pressable, ScrollView, View } from 'react-native';
import DateTimePicker, { useDefaultStyles } from 'react-native-ui-datepicker';

import { Button } from '@/components/ui/button';
import { Text } from '@/components/ui/text';
import { formatDateRangeLabel, type DateRange } from '@/lib/date-range-filter';

dayjs.locale('id');

type DateRangeFilterModalProps = {
  visible: boolean;
  title: string;
  description: string;
  range: DateRange;
  onRangeChange: (range: DateRange) => void;
  onClose: () => void;
  onApply: () => void;
  onReset: () => void;
};

export function DateRangeFilterModal({
  visible,
  title,
  description,
  range,
  onRangeChange,
  onClose,
  onApply,
  onReset,
}: DateRangeFilterModalProps) {
  const defaultStyles = useDefaultStyles('light');

  return (
    <Modal visible={visible} transparent animationType="slide" onRequestClose={onClose}>
      <Pressable className="flex-1 justify-end bg-black/40" onPress={onClose}>
        <Pressable
          className="max-h-[85%] rounded-t-2xl bg-white pt-5"
          onPress={(e) => e.stopPropagation()}>
          <View className="mb-3 h-1 w-10 self-center rounded-full bg-stone-200" />

          <ScrollView
            className="px-4"
            showsVerticalScrollIndicator={false}
            bounces={false}
            keyboardShouldPersistTaps="handled">
            <Text className="text-base font-semibold text-stone-900">{title}</Text>
            <Text variant="muted" className="mt-1">
              {description}
            </Text>
            <Text className="mt-2 text-sm font-medium text-[#b45309]">
              {formatDateRangeLabel(range)}
            </Text>

            <View className="mt-4 overflow-hidden rounded-xl border border-stone-200">
              <DateTimePicker
                mode="range"
                locale="id"
                startDate={range.startDate}
                endDate={range.endDate}
                onChange={onRangeChange}
                styles={{
                  ...defaultStyles,
                  today: { borderColor: '#e8a020', borderWidth: 1 },
                  selected: { backgroundColor: '#e8a020' },
                  selected_label: { color: '#ffffff' },
                  range_fill: { backgroundColor: 'rgba(232, 160, 32, 0.12)' },
                  range_middle: { backgroundColor: 'rgba(232, 160, 32, 0.12)' },
                  range_middle_label: { color: '#b45309' },
                }}
              />
            </View>

            <View className="mt-4 flex-row gap-3">
              <Button variant="outline" className="flex-1" onPress={onReset}>
                <Text>Reset</Text>
              </Button>
              <Button className="flex-1 bg-[#e8a020] active:bg-[#d4921c]" onPress={onApply}>
                <Text className="text-stone-900">Terapkan</Text>
              </Button>
            </View>

            <Button variant="ghost" className="mt-2 mb-8" onPress={onClose}>
              <Text>Tutup</Text>
            </Button>
          </ScrollView>
        </Pressable>
      </Pressable>
    </Modal>
  );
}
