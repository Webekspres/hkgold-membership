import { Image } from "expo-image";
import { LinearGradient } from "expo-linear-gradient";
import { cssInterop } from "nativewind";
import { View } from "react-native";

import { Text } from "@/components/ui/text";
import { getTierIconSource } from "@/config/assets";
import {
  GOLD_GRADIENT_COLORS,
  GOLD_GRADIENT_END,
  GOLD_GRADIENT_START,
} from "@/config/brand";
import type { MemberTier } from "@/types/auth";

cssInterop(Image, { className: "style" });

type ProfilePointsTierCardProps = {
  points: number;
  tierName: string;
  currentTier: MemberTier;
};

function formatPoints(points: number) {
  return points.toLocaleString("id-ID");
}

export function ProfilePointsTierCard({
  points,
  tierName,
  currentTier,
}: ProfilePointsTierCardProps) {
  return (
    <LinearGradient
      colors={[...GOLD_GRADIENT_COLORS]}
      start={GOLD_GRADIENT_START}
      end={GOLD_GRADIENT_END}
      style={{ borderRadius: 16 }}
    >
      <Image
        source={require("@/assets/media/pattern-horizontal.webp")}
        style={{
          position: "absolute",
          inset: 0,
          opacity: 0.75,
          borderRadius: 16,
        }}
        contentFit="cover"
      />
      <View className="flex-row items-stretch px-4 py-4">
        <View className="w-3/5 justify-center">
          <Text variant="muted" className="text-sm text-white/85">
            Saldo poin
          </Text>
          <Text className="mt-0.5 text-3xl font-bold leading-tight text-white">
            {formatPoints(points)}
          </Text>
          <Text className="text-sm font-semibold uppercase tracking-wide text-white/85">
            poin
          </Text>
        </View>

        <View className="w-2/5 items-center justify-center rounded-2xl bg-white/20 px-3 py-3">
          <Image
            source={getTierIconSource(currentTier)}
            className="h-9 w-9"
            contentFit="contain"
            accessibilityLabel={`Tier ${tierName}`}
          />
          <Text className="mt-1 font-semibold text-white">{tierName}</Text>
        </View>
      </View>
    </LinearGradient>
  );
}
