import { View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';

import { Text } from '@/components/ui/text';
import { BottomTabInset } from '@/constants/theme';

type ComingSoonScreenProps = {
  title: string;
};

export function ComingSoonScreen({ title }: ComingSoonScreenProps) {
  return (
    <View className="flex-1 bg-background">
      <SafeAreaView
        className="flex-1 items-center justify-center px-6"
        style={{ paddingBottom: BottomTabInset + 16 }}>
        <Text variant="h3" className="text-center text-stone-800">
          {title}
        </Text>
        <Text variant="muted" className="mt-2 text-center">
          Segera hadir
        </Text>
      </SafeAreaView>
    </View>
  );
}
