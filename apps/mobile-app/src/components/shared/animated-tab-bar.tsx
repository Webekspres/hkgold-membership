import { LinearGradient } from "expo-linear-gradient";
import { usePathname } from "expo-router";
import { TabList, Tabs, TabSlot, TabTrigger } from "expo-router/ui";
import { SymbolView } from "expo-symbols";
import { type ComponentProps, useEffect, useMemo, useState } from "react";
import { type LayoutChangeEvent, View } from "react-native";
import Animated, {
  Easing,
  interpolate,
  useAnimatedStyle,
  useSharedValue,
  withTiming,
} from "react-native-reanimated";

import { Text } from "@/components/ui/text";
import {
  GOLD_GRADIENT_COLORS,
  GOLD_GRADIENT_END,
  GOLD_GRADIENT_START,
} from "@/config/brand";
import { Colors } from "@/config/theme";

const AnimatedLinearGradient = Animated.createAnimatedComponent(LinearGradient);

type TabItem = {
  name: "index" | "card" | "reward" | "profile";
  href: "/" | "/card" | "/reward" | "/profile";
  label: string;
  icon: ComponentProps<typeof SymbolView>["name"];
};

const TABS: TabItem[] = [
  {
    name: "index",
    href: "/",
    label: "Home",
    icon: { ios: "house.fill", android: "home", web: "home" },
  },
  {
    name: "card",
    href: "/card",
    label: "Card",
    icon: { ios: "person.text.rectangle.fill", android: "badge", web: "badge" },
  },
  {
    name: "reward",
    href: "/reward",
    label: "Reward",
    icon: { ios: "gift.fill", android: "redeem", web: "redeem" },
  },
  {
    name: "profile",
    href: "/profile",
    label: "Profil",
    icon: { ios: "person.fill", android: "person", web: "person" },
  },
];

function getActiveIndex(pathname: string) {
  if (pathname.startsWith("/card")) return 1;
  if (pathname.startsWith("/reward")) return 2;
  if (pathname.startsWith("/profile")) return 3;
  return 0;
}

type AnimatedTabButtonProps = {
  icon: ComponentProps<typeof SymbolView>["name"];
  label: string;
  isFocused: boolean;
};

function AnimatedTabButton({ icon, label, isFocused }: AnimatedTabButtonProps) {
  const progress = useSharedValue(isFocused ? 1 : 0);

  useEffect(() => {
    progress.value = withTiming(isFocused ? 1 : 0, {
      duration: 220,
      easing: Easing.out(Easing.cubic),
    });
  }, [isFocused, progress]);

  const iconStyle = useAnimatedStyle(() => ({
    transform: [{ translateX: interpolate(progress.value, [0, 1], [0, -7]) }],
  }));

  const labelWrapStyle = useAnimatedStyle(() => ({
    width: interpolate(progress.value, [0, 1], [0, 56]),
    opacity: progress.value,
    transform: [{ translateX: interpolate(progress.value, [0, 1], [6, 0]) }],
  }));

  return (
    <View className="h-11 flex-1 flex-row items-center justify-center rounded-full">
      <Animated.View style={iconStyle}>
        <SymbolView
          name={icon}
          size={20}
          tintColor={isFocused ? "#422006" : Colors.light.textSecondary}
        />
      </Animated.View>
      <Animated.View className="ml-1 overflow-hidden" style={labelWrapStyle}>
        <Text variant="small" className="font-semibold text-amber-900">
          {label}
        </Text>
      </Animated.View>
    </View>
  );
}

export default function AnimatedTabBar() {
  const pathname = usePathname();
  const activeIndex = getActiveIndex(pathname);
  const [containerWidth, setContainerWidth] = useState(0);
  const indicatorX = useSharedValue(0);
  const itemWidth = useMemo(
    () => (containerWidth > 0 ? containerWidth / TABS.length : 0),
    [containerWidth],
  );

  const onLayout = (event: LayoutChangeEvent) => {
    const width = event.nativeEvent.layout.width;
    setContainerWidth(width);
    indicatorX.value = activeIndex * (width / TABS.length) + 4;
  };

  useEffect(() => {
    if (itemWidth <= 0) {
      return;
    }
    indicatorX.value = withTiming(activeIndex * itemWidth + 4, {
      duration: 230,
      easing: Easing.out(Easing.cubic),
    });
  }, [activeIndex, indicatorX, itemWidth]);

  const indicatorStyle = useAnimatedStyle(() => ({
    width: itemWidth > 0 ? itemWidth - 8 : 0,
    transform: [{ translateX: indicatorX.value }],
  }));

  return (
    <Tabs>
      <TabSlot style={{ height: "100%" }} />
      <TabList
        onLayout={onLayout}
        className="absolute bottom-3 left-3 right-3 flex-row items-center overflow-hidden rounded-full border border-stone-200 bg-background/95 p-1"
      >
        <AnimatedLinearGradient
          pointerEvents="none"
          colors={[...GOLD_GRADIENT_COLORS]}
          start={GOLD_GRADIENT_START}
          end={GOLD_GRADIENT_END}
          style={[
            {
              position: "absolute",
              top: 4,
              bottom: 4,
              opacity: 0.42,
              borderRadius: 999,
            },
            indicatorStyle,
          ]}
        />
        {TABS.map((tab, index) => (
          <TabTrigger
            key={tab.name}
            name={tab.name}
            href={tab.href}
            className="flex-1 active:opacity-80"
          >
            <AnimatedTabButton
              icon={tab.icon}
              label={tab.label}
              isFocused={activeIndex === index}
            />
          </TabTrigger>
        ))}
      </TabList>
    </Tabs>
  );
}
