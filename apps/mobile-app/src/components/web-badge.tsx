import { version } from 'expo/package.json';
import { Image } from 'expo-image';
import { useColorScheme, View } from 'react-native';

import { Text } from '@/components/ui/text';

export function WebBadge() {
  const scheme = useColorScheme();

  return (
    <View className="items-center gap-2 p-8">
      <Text variant="code" className="text-center text-muted-foreground">
        v{version}
      </Text>
      <Image
        source={
          scheme === 'dark'
            ? require('@/assets/images/expo-badge-white.png')
            : require('@/assets/images/expo-badge.png')
        }
        className="aspect-[123/24] w-[123px]"
      />
    </View>
  );
}
