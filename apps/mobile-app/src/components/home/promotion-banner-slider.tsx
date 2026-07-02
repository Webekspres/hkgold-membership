import { Image } from "expo-image";
import { useCallback, useState } from "react";
import {
  FlatList,
  NativeScrollEvent,
  NativeSyntheticEvent,
  View,
  type ListRenderItem,
} from "react-native";

import type { PromotionBanner } from "@/mocks/mock-banners";
import {
  CAROUSEL_ITEM_GAP,
  CAROUSEL_ITEM_WIDTH,
  CAROUSEL_LEFT_PADDING,
  CAROUSEL_PEEK,
  CAROUSEL_SNAP_INTERVAL,
} from "@/constants/layout/carousel-layout";
import { cn } from "@/lib/utils";

type PromotionBannerSliderProps = {
  banners: PromotionBanner[];
  className?: string;
};

export function PromotionBannerSlider({
  banners,
  className,
}: PromotionBannerSliderProps) {
  const [activeIndex, setActiveIndex] = useState(0);

  const handleScroll = useCallback(
    (event: NativeSyntheticEvent<NativeScrollEvent>) => {
      const offsetX = event.nativeEvent.contentOffset.x;
      const index = Math.round(offsetX / CAROUSEL_SNAP_INTERVAL);
      setActiveIndex(Math.min(Math.max(index, 0), banners.length - 1));
    },
    [banners.length],
  );

  const renderItem: ListRenderItem<PromotionBanner> = useCallback(
    ({ item }) => (
      <View
        style={{ width: CAROUSEL_ITEM_WIDTH, marginRight: CAROUSEL_ITEM_GAP }}
        className="overflow-hidden rounded-xl"
      >
        <Image
          source={item.image}
          style={{ width: "100%", aspectRatio: 21 / 9 }}
          contentFit="cover"
          accessibilityLabel="Banner promosi"
        />
      </View>
    ),
    [],
  );

  if (banners.length === 0) {
    return null;
  }

  return (
    <View className={cn("gap-3", className)}>
      <FlatList
        data={banners}
        keyExtractor={(item) => item.id}
        renderItem={renderItem}
        horizontal
        nestedScrollEnabled
        showsHorizontalScrollIndicator={false}
        decelerationRate="fast"
        snapToInterval={CAROUSEL_SNAP_INTERVAL}
        snapToAlignment="start"
        disableIntervalMomentum
        bounces={false}
        onScroll={handleScroll}
        scrollEventThrottle={16}
        contentContainerStyle={{
          paddingLeft: CAROUSEL_LEFT_PADDING,
          paddingRight: CAROUSEL_LEFT_PADDING - CAROUSEL_PEEK,
        }}
      />

      <View className="flex-row items-center justify-center gap-2">
        {banners.map((banner, index) => (
          <View
            key={banner.id}
            className={cn(
              "h-1.5 rounded-full",
              index === activeIndex ? "w-5 bg-[#e8a020]" : "w-1.5 bg-stone-300",
            )}
          />
        ))}
      </View>
    </View>
  );
}
