import MaskedView from "@react-native-masked-view/masked-view";
import { LinearGradient } from "expo-linear-gradient";
import { usePathname } from "expo-router";
import { TabList, Tabs, TabSlot, TabTrigger } from "expo-router/ui";
import { SymbolView } from "expo-symbols";
import { type ComponentProps, useEffect } from "react";
import { Platform, StyleSheet, View } from "react-native";
import Animated, {
  Easing,
  interpolate,
  useAnimatedStyle,
  useSharedValue,
  withTiming,
} from "react-native-reanimated";
import { useSafeAreaInsets } from "react-native-safe-area-context";

import { Text } from "@/components/ui/text";
import {
  DARK_TAB_BAR_BACKGROUND,
  GOLD_GRADIENT_COLORS,
  GOLD_GRADIENT_END,
  GOLD_GRADIENT_START,
} from "@/config/brand";

const AnimatedLinearGradient = Animated.createAnimatedComponent(LinearGradient);
const TAB_TEXT_COLOR = "#000000";
const TAB_ICON_SIZE = 22;

type TabItem = {
  name: "index" | "card" | "reward" | "profile";
  href: "/" | "/card" | "/reward" | "/profile";
  label: string;
  labelWidth: number; // <--- Tambahkan ini
  icon: ComponentProps<typeof SymbolView>["name"];
};

const TABS: TabItem[] = [
  {
    name: "index",
    href: "/",
    label: "Home",
    labelWidth: 48, // "Home" pendek
    icon: { ios: "house.fill", android: "home", web: "home" },
  },
  {
    name: "card",
    href: "/card",
    label: "Card",
    labelWidth: 38, // "Card" paling pendek
    icon: { ios: "person.text.rectangle.fill", android: "badge", web: "badge" },
  },
  {
    name: "reward",
    href: "/reward",
    label: "Reward",
    labelWidth: 54, // "Reward" lebih panjang
    icon: { ios: "gift.fill", android: "redeem", web: "redeem" },
  },
  {
    name: "profile",
    href: "/profile",
    label: "Profil",
    labelWidth: 40,
    icon: { ios: "person.fill", android: "person", web: "person" },
  },
];

function getActiveIndex(pathname: string) {
  if (pathname.startsWith("/card")) return 1;
  if (pathname.startsWith("/reward")) return 2;
  if (pathname.startsWith("/profile")) return 3;
  return 0;
}

// type AnimatedTabButtonProps = {
//   icon: ComponentProps<typeof SymbolView>["name"];
//   label: string;
//   labelWidth: number;
//   isFocused: boolean;
// };

// Ukuran konstan untuk menghitung animasi pelebaran tab
const INACTIVE_WIDTH = 48; // Lebar icon saat normal
const ACTIVE_WIDTH = 96; // Lebar pill saat terbuka (sesuaikan jika teks kurang panjang)

type AnimatedTabButtonProps = {
  icon: ComponentProps<typeof SymbolView>["name"];
  label: string;
  labelWidth: number; // <--- Tangkap props baru ini
  isFocused: boolean;
};

function AnimatedTabButton({
  icon,
  label,
  labelWidth,
  isFocused,
}: AnimatedTabButtonProps) {
  const progress = useSharedValue(isFocused ? 1 : 0);

  useEffect(() => {
    progress.value = withTiming(isFocused ? 1 : 0, {
      duration: 250,
      easing: Easing.out(Easing.cubic),
    });
  }, [isFocused, progress]);

  // Lebar statis untuk lingkaran ikon saat inaktif
  const INACTIVE_WIDTH = 48;

  // Lebar pill otomatis dikalkulasi = Lebar ikon + Lebar teks + Padding/Margin
  const ACTIVE_WIDTH = INACTIVE_WIDTH + labelWidth + 8;

  const containerStyle = useAnimatedStyle(() => ({
    // Lebar kontainer utama akan menyesuaikan panjang teks saat animasi berjalan
    width: interpolate(progress.value, [0, 1], [INACTIVE_WIDTH, ACTIVE_WIDTH]),
  }));

  const pillStyle = useAnimatedStyle(() => ({
    opacity: progress.value,
  }));

  const labelWrapStyle = useAnimatedStyle(() => ({
    // Lebar teks tepat berhenti di labelWidth yang spesifik
    width: interpolate(progress.value, [0, 1], [0, labelWidth]),
    opacity: interpolate(progress.value, [0.5, 1], [0, 1]),
    transform: [{ translateX: interpolate(progress.value, [0, 1], [-10, 0]) }],
  }));

  return (
    <Animated.View style={[styles.tabButton, containerStyle]}>
      <AnimatedLinearGradient
        pointerEvents="none"
        colors={[...GOLD_GRADIENT_COLORS]}
        start={GOLD_GRADIENT_START}
        end={GOLD_GRADIENT_END}
        style={[styles.activePill, pillStyle]}
      />

      <View style={styles.tabContent}>
        {isFocused ? (
          <SymbolView name={icon} size={TAB_ICON_SIZE} tintColor={TAB_TEXT_COLOR} />
        ) : (
          <MaskedView
            style={styles.iconGradientMask}
            maskElement={
              <SymbolView name={icon} size={TAB_ICON_SIZE} tintColor="#000000" />
            }
          >
            <LinearGradient
              colors={[...GOLD_GRADIENT_COLORS]}
              start={GOLD_GRADIENT_START}
              end={GOLD_GRADIENT_END}
              style={styles.iconGradientMask}
            />
          </MaskedView>
        )}
        <Animated.View style={[styles.labelWrap, labelWrapStyle]}>
          <Text
            variant="small"
            className="font-semibold"
            style={{
              color: TAB_TEXT_COLOR,
              marginLeft: 6,
              // Lebar tidak perlu diset hardcode lagi di sini
            }}
            numberOfLines={1}
          >
            {label}
          </Text>
        </Animated.View>
      </View>
    </Animated.View>
  );
}

export default function AnimatedTabBar() {
  const pathname = usePathname();
  const activeIndex = getActiveIndex(pathname);
  const insets = useSafeAreaInsets();

  return (
    <Tabs style={styles.tabs}>
      <TabSlot
        style={[
          styles.tabSlot,
          { paddingBottom: 64 + Math.max(insets.bottom, 12) },
        ]}
      />
      <TabList
        style={[
          styles.tabList,
          {
            bottom: Math.max(insets.bottom, 12),
            backgroundColor: DARK_TAB_BAR_BACKGROUND,
          },
        ]}
      >
        {TABS.map((tab, index) => (
          <TabTrigger
            key={tab.name}
            name={tab.name}
            href={tab.href}
            style={styles.tabTrigger}
          >
            <AnimatedTabButton
              icon={tab.icon}
              label={tab.label}
              labelWidth={tab.labelWidth} // <--- Kirim datanya di sini
              isFocused={activeIndex === index}
            />
          </TabTrigger>
        ))}
      </TabList>
    </Tabs>
  );
}

const styles = StyleSheet.create({
  tabs: {
    flex: 1,
  },
  tabSlot: {
    flex: 1,
  },
  tabList: {
    position: "absolute",
    left: 12,
    right: 12,
    zIndex: 10,
    paddingHorizontal: 8,
    paddingVertical: 8,
    flexDirection: "row",
    // PENTING: space-between memastikan tab lain terdorong otomatis ke samping
    // jika ada salah satu item yang mengubah lebarnya (width)
    justifyContent: "space-between",
    alignItems: "center",
    borderRadius: 999,
    shadowColor: "#000",
    shadowOffset: { width: 0, height: 6 },
    shadowOpacity: 0.3,
    shadowRadius: 10,
    elevation: 10,
  },
  tabTrigger: {
    // PENTING: flex: 1 SUDAH DIHAPUS.
    // Kita biarkan ukurannya dikendalikan oleh container di dalamnya.
    height: "100%",
    justifyContent: "center",
    alignItems: "center",
  },
  tabButton: {
    height: 48,
    alignItems: "center",
    justifyContent: "center",
  },
  activePill: {
    position: "absolute",
    top: 0,
    bottom: 0,
    left: 0,
    right: 0,
    borderRadius: 999,
  },
  tabContent: {
    flexDirection: "row",
    alignItems: "center",
    justifyContent: "center",
    zIndex: 2,
  },
  labelWrap: {
    overflow: "hidden",
    justifyContent: "center",
  },
  iconGradientMask: {
    width: TAB_ICON_SIZE,
    height: TAB_ICON_SIZE,
  },
});
