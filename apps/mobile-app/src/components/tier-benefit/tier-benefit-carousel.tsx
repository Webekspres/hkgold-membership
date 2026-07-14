import { useCallback, useRef, useState } from 'react';
import {
  FlatList,
  View,
  type ListRenderItem,
  type NativeScrollEvent,
  type NativeSyntheticEvent,
} from 'react-native';

import { TierBenefitSlideCard } from '@/components/tier-benefit/tier-benefit-slide-card';
import {
  TIER_SLIDE_GAP,
  TIER_SLIDE_SIDE_PADDING,
  TIER_SLIDE_WIDTH,
  TIER_SNAP_INTERVAL,
} from '@/constants/layout/tier-benefit-carousel-layout';
import { cn } from '@/lib/utils';
import type { TierBenefitSlide } from '@/types/tier-benefit';

type TierBenefitCarouselProps = {
  slides: TierBenefitSlide[];
  initialIndex: number;
  onIndexChange: (index: number) => void;
};

export function TierBenefitCarousel({
  slides,
  initialIndex,
  onIndexChange,
}: TierBenefitCarouselProps) {
  const listRef = useRef<FlatList<TierBenefitSlide>>(null);
  const [activeIndex, setActiveIndex] = useState(initialIndex);

  const updateIndex = useCallback(
    (index: number) => {
      const nextIndex = Math.min(Math.max(index, 0), slides.length - 1);
      setActiveIndex(nextIndex);
      onIndexChange(nextIndex);
    },
    [onIndexChange, slides.length],
  );

  const handleScroll = useCallback(
    (event: NativeSyntheticEvent<NativeScrollEvent>) => {
      const offsetX = event.nativeEvent.contentOffset.x;
      const index = Math.round(offsetX / TIER_SNAP_INTERVAL);
      updateIndex(index);
    },
    [updateIndex],
  );

  const getItemLayout = useCallback(
    (_: ArrayLike<TierBenefitSlide> | null | undefined, index: number) => ({
      length: TIER_SNAP_INTERVAL,
      offset: TIER_SNAP_INTERVAL * index,
      index,
    }),
    [],
  );

  const renderItem: ListRenderItem<TierBenefitSlide> = useCallback(
    ({ item }) => (
      <View
        style={{
          width: TIER_SLIDE_WIDTH,
          marginRight: TIER_SLIDE_GAP,
          aspectRatio: 1,
        }}>
        <TierBenefitSlideCard slide={item} />
      </View>
    ),
    [],
  );

  if (slides.length === 0) {
    return null;
  }

  return (
    <View className="gap-3">
      <FlatList
        ref={listRef}
        data={slides}
        keyExtractor={(item) => item.tier}
        renderItem={renderItem}
        horizontal
        nestedScrollEnabled
        showsHorizontalScrollIndicator={false}
        decelerationRate="fast"
        snapToInterval={TIER_SNAP_INTERVAL}
        snapToAlignment="start"
        disableIntervalMomentum
        bounces={false}
        onScroll={handleScroll}
        scrollEventThrottle={16}
        initialScrollIndex={initialIndex}
        getItemLayout={getItemLayout}
        contentContainerStyle={{
          paddingHorizontal: TIER_SLIDE_SIDE_PADDING,
        }}
      />

      <View className="flex-row items-center justify-center gap-2">
        {slides.map((slide, index) => (
          <View
            key={slide.tier}
            className={cn(
              'h-1.5 rounded-full',
              index === activeIndex ? 'w-5 bg-[#e8a020]' : 'w-1.5 bg-stone-300',
            )}
          />
        ))}
      </View>
    </View>
  );
}
