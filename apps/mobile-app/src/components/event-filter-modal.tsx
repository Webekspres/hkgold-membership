import { Modal, Pressable, View } from 'react-native';

import { Button } from '@/components/ui/button';
import { Text } from '@/components/ui/text';

type EventFilterModalProps = {
  visible: boolean;
  onClose: () => void;
};

export function EventFilterModal({ visible, onClose }: EventFilterModalProps) {
  return (
    <Modal visible={visible} transparent animationType="fade" onRequestClose={onClose}>
      <Pressable className="flex-1 justify-end bg-black/40" onPress={onClose}>
        <Pressable className="rounded-t-2xl bg-white px-4 pb-8 pt-5" onPress={(e) => e.stopPropagation()}>
          <View className="mb-1 h-1 w-10 self-center rounded-full bg-stone-200" />
          <Text className="text-base font-semibold text-stone-900">Filter Event</Text>
          <Text variant="muted" className="mt-2">
            Fitur filter akan segera hadir.
          </Text>
          <Button variant="outline" className="mt-6" onPress={onClose}>
            <Text>Tutup</Text>
          </Button>
        </Pressable>
      </Pressable>
    </Modal>
  );
}
