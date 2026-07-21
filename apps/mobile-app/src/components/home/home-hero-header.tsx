import { Image } from "expo-image";
import { cssInterop } from "nativewind";
import { useWindowDimensions, View } from "react-native";
import { useSafeAreaInsets } from "react-native-safe-area-context";
import Svg, { Path } from "react-native-svg";

import { Text } from "@/components/ui/text";
import { LOGO_ASSETS } from "@/config/assets";
import { GOLD_GRADIENT_COLORS } from "@/config/brand";

cssInterop(Image, { className: "style" });

const HERO_BG = "#0a0a0a";
/** Tinggi swoosh — cukup besar agar kurva terlihat jelas. */
const WAVE_HEIGHT = 52;
/**
 * Seberapa jauh kartu naik ke area wave.
 * Kecil = swoosh hampir full terlihat; kartu duduk di bawah kurva.
 */
export const HOME_HERO_CARD_OVERLAP = 14;

type HomeHeroHeaderProps = {
  firstName: string;
};

function buildWaveCurve(width: number, height: number) {
  // Kiri turun dulu, lalu melengkung ke kanan (swoosh penuh).
  return `M0 ${height * 0.22} C ${width * 0.22} ${height * 1.05} ${width * 0.55} ${height * -0.05} ${width} ${height * 0.55}`;
}

export function HomeHeroHeader({ firstName }: HomeHeroHeaderProps) {
  const insets = useSafeAreaInsets();
  const { width } = useWindowDimensions();
  const waveCurve = buildWaveCurve(width, WAVE_HEIGHT);

  return (
    <View style={{ backgroundColor: HERO_BG, paddingTop: insets.top }}>
      <Image
        source={require("@/assets/media/pattern-horizontal.webp")}
        className="absolute inset-0 opacity-[0.18]"
        style={{ transform: [{ scale: 1.15 }] }}
        contentFit="cover"
      />

      <View className="flex-row items-start justify-between gap-3 px-4 pb-5 pt-2">
        <View className="min-w-0 flex-1 gap-0.5">
          <Text className="text-lg font-semibold tracking-tight text-white">
            Halo,{" "}
            <Text
              className="text-lg font-semibold tracking-tight"
              style={{ color: GOLD_GRADIENT_COLORS[0] }}
            >
              {firstName}
            </Text>
          </Text>
          <Text className="text-xs text-white/70">
            Selamat datang di HK Gold VIP
          </Text>
        </View>

        <Image
          source={LOGO_ASSETS.horizontal}

          className="h-12 w-28 "
          contentFit="contain"
          accessibilityLabel="HK Gold VIP"
        />
      </View>

      <Svg
        width={width}
        height={WAVE_HEIGHT}
        viewBox={`0 0 ${width} ${WAVE_HEIGHT}`}
        preserveAspectRatio="none"
        style={{ marginBottom: -1 }}
      >
        {/* Fill putih di bawah kurva */}
        <Path
          d={`${waveCurve} L ${width} ${WAVE_HEIGHT} L 0 ${WAVE_HEIGHT} Z`}
          fill="#ffffff"
        />
        {/* Garis emas mengikuti tepi swoosh */}
        <Path
          d={waveCurve}
          fill="none"
          stroke={GOLD_GRADIENT_COLORS[0]}
          strokeWidth={2}
          strokeLinecap="round"
        />
      </Svg>
    </View>
  );
}
