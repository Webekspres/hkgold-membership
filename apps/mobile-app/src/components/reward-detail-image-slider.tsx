import { Image } from 'expo-image';
import { SymbolView } from 'expo-symbols';
import { router } from 'expo-router';
import { useCallback, useState } from 'react';
import {
  Dimensions,
  FlatList,
  View,
  type ListRenderItem,
  type NativeScrollEvent,
  type NativeSyntheticEvent,
} from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';

import { Button } from '@/components/ui/button';

const SCREEN_WIDTH = Dimensions.get('window').width;

const BACK_ICON = { ios: 'chevron.left', android: 'arrow_back', web: 'arrow_back' } as const;

type RewardDetailImageSliderProps = {
  images: number[];
  title: string;
};

export function RewardDetailImageSlider({ images, title }: RewardDetailImageSliderProps) {
  const insets = useSafeAreaInsets();
  const [activeIndex, setActiveIndex] = useState(0);

  const handleScroll = useCallback((event: NativeSyntheticEvent<NativeScrollEvent>) => {
    const offsetX = event.nativeEvent.contentOffset.x;
    const index = Math.round(offsetX / SCREEN_WIDTH);
    setActiveIndex(index);
  }, []);

  const renderItem: ListRenderItem<number> = useCallback(
    ({ item }) => (
      <Image
        source={item}
        style={{ width: SCREEN_WIDTH, aspectRatio: 1 }}
        contentFit="cover"
        accessibilityLabel={title}
      />
    ),
    [title]
  );

  if (images.length === 0) {
    return null;
  }

  return (
    <View className="relative">
      <FlatList
        data={images}
        keyExtractor={(_, index) => `reward-image-${index}`}
        renderItem={renderItem}
        horizontal
        pagingEnabled
        nestedScrollEnabled
        showsHorizontalScrollIndicator={false}
        onScroll={handleScroll}
        scrollEventThrottle={16}
      />

      <View
        className="absolute left-4"
        style={{ top: insets.top + 8 }}>
        <Button
          variant="outline"
          size="icon"
          className="border-white/30 bg-black/35"
          onPress={() => router.back()}>
          <SymbolView name={BACK_ICON} size={20} tintColor="#ffffff" />
        </Button>
      </View>

      {images.length > 1 ? (
        <View className="absolute bottom-3 w-full flex-row items-center justify-center gap-2">
          {images.map((_, index) => (
            <View
              key={`dot-${index}`}
              className={`h-1.5 rounded-full ${
                index === activeIndex ? 'w-5 bg-[#e8a020]' : 'w-1.5 bg-white/70'
              }`}
            />
          ))}
        </View>
      ) : null}
    </View>
  );
}
