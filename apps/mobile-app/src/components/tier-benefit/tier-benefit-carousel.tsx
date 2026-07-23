import { useCallback, useEffect, useRef, useState } from 'react';
import {
  FlatList,
  View,
  type ListRenderItem,
  type NativeScrollEvent,
  type NativeSyntheticEvent,
} from 'react-native';

import { TierBenefitSlideCard } from '@/components/tier-benefit/tier-benefit-slide-card';
import {
  TIER_HERO_HEIGHT,
  TIER_SLIDE_GAP,
  TIER_SLIDE_SIDE_PADDING,
  TIER_SLIDE_WIDTH,
  TIER_SNAP_INTERVAL,
} from '@/constants/layout/tier-benefit-carousel-layout';
import type { TierBenefitSlide } from '@/types/tier-benefit';

type TierBenefitCarouselProps = {
  slides: TierBenefitSlide[];
  initialIndex: number;
  onIndexChange: (index: number) => void;
  /** Silver/light hero pakai label gelap; tier gelap pakai label putih. */
  lightProgressLabels?: boolean;
};

const SideSpacer = () => <View style={{ width: TIER_SLIDE_SIDE_PADDING }} />;

export function TierBenefitCarousel({
  slides,
  initialIndex,
  onIndexChange,
  lightProgressLabels = false,
}: TierBenefitCarouselProps) {
  const listRef = useRef<FlatList<TierBenefitSlide>>(null);
  const [activeIndex, setActiveIndex] = useState(initialIndex);
  const hasScrolledToInitial = useRef(false);

  const updateIndex = useCallback(
    (index: number) => {
      const nextIndex = Math.min(Math.max(index, 0), slides.length - 1);
      setActiveIndex(nextIndex);
      onIndexChange(nextIndex);
    },
    [onIndexChange, slides.length],
  );

  const scrollToIndex = useCallback(
    (index: number) => {
      const clamped = Math.min(Math.max(index, 0), slides.length - 1);
      listRef.current?.scrollToOffset({
        offset: clamped * TIER_SNAP_INTERVAL,
        animated: true,
      });
      updateIndex(clamped);
    },
    [slides.length, updateIndex],
  );

  const resolveIndexFromOffset = useCallback((offsetX: number) => {
    return Math.round(offsetX / TIER_SNAP_INTERVAL);
  }, []);

  const handleScrollEnd = useCallback(
    (event: NativeSyntheticEvent<NativeScrollEvent>) => {
      const offsetX = event.nativeEvent.contentOffset.x;
      updateIndex(resolveIndexFromOffset(offsetX));
    },
    [resolveIndexFromOffset, updateIndex],
  );

  useEffect(() => {
    if (hasScrolledToInitial.current || initialIndex <= 0) return;

    hasScrolledToInitial.current = true;
    requestAnimationFrame(() => {
      listRef.current?.scrollToOffset({
        offset: initialIndex * TIER_SNAP_INTERVAL,
        animated: false,
      });
    });
  }, [initialIndex]);

  const getItemLayout = useCallback(
    (_: ArrayLike<TierBenefitSlide> | null | undefined, index: number) => ({
      length: TIER_SNAP_INTERVAL,
      offset: TIER_SLIDE_SIDE_PADDING + TIER_SNAP_INTERVAL * index,
      index,
    }),
    [],
  );

  const renderItem: ListRenderItem<TierBenefitSlide> = useCallback(
    ({ item, index }) => (
      <View
        style={{
          width: TIER_SLIDE_WIDTH,
          marginRight: index < slides.length - 1 ? TIER_SLIDE_GAP : 0,
          height: TIER_HERO_HEIGHT,
        }}
      >
        <TierBenefitSlideCard
          slide={item}
          isActive={index === activeIndex}
          lightProgressLabels={lightProgressLabels}
          onPressProgress={() => scrollToIndex(index)}
        />
      </View>
    ),
    [activeIndex, lightProgressLabels, scrollToIndex, slides.length],
  );

  if (slides.length === 0) {
    return null;
  }

  return (
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
      onMomentumScrollEnd={handleScrollEnd}
      onScrollEndDrag={handleScrollEnd}
      getItemLayout={getItemLayout}
      ListHeaderComponent={SideSpacer}
      ListFooterComponent={SideSpacer}
    />
  );
}
