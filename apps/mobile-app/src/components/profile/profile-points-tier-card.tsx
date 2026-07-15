import { Image } from "expo-image";
import { LinearGradient } from "expo-linear-gradient";
import { cssInterop } from "nativewind";
import { View } from "react-native";

import { SilverGradientText } from "@/components/shared/silver-gradient-text";
import { Text } from "@/components/ui/text";
import { getTierIconSource } from "@/config/assets";
import {
  GOLD_GRADIENT_END,
  GOLD_GRADIENT_START,
  TIER_GRADIENTS,
} from "@/config/brand";
import { cn } from "@/lib/utils";
import type { MemberTier } from "@/types/auth";

cssInterop(Image, { className: "style" });
cssInterop(LinearGradient, { className: "style" });

const PATTERN_FADE = [
  "rgba(10,10,10,0.45)",
  "rgba(10,10,10,0.78)",
  "rgba(10,10,10,0.94)",
] as const;

type ProfilePointsTierCardProps = {
  points: number;
  tierName: string;
  currentTier: MemberTier;
};

const TIER_TEXT_COLORS: Record<MemberTier, string> = {
  SILVER: "text-stone-200",
  GOLD: "text-[#f5c842]",
  PLATINUM: "text-slate-200",
  SAPPHIRE: "text-indigo-200",
};

function formatPoints(points: number) {
  return points.toLocaleString("id-ID");
}

export function ProfilePointsTierCard({
  points,
  tierName,
  currentTier,
}: ProfilePointsTierCardProps) {
  const tierColors = TIER_GRADIENTS[currentTier];
  const tierTextColor = TIER_TEXT_COLORS[currentTier];

  return (
    <View className="overflow-hidden rounded-xl border-0 shadow-lg shadow-stone-900/30">
      {/* Tier-specific background gradient */}
      <LinearGradient
        colors={tierColors.colors as any}
        start={GOLD_GRADIENT_START}
        end={GOLD_GRADIENT_END}
        className="absolute inset-0"
      />

      {/* Pattern overlay & fade */}
      <View className="absolute inset-0" pointerEvents="none">
        <Image
          source={require("@/assets/media/pattern-horizontal.webp")}
          className="absolute inset-0 size-full opacity-80"
          contentFit="cover"
        />
        <LinearGradient
          colors={[...PATTERN_FADE]}
          start={GOLD_GRADIENT_START}
          end={GOLD_GRADIENT_END}
          className="absolute inset-0"
        />
      </View>

      <View className="relative z-10 flex-row items-stretch px-4 py-4">
        <View className="flex-1 justify-center">
          <Text variant="small" className="text-white/55">
            Saldo poin
          </Text>
          <SilverGradientText className="mt-0.5 text-3xl font-bold leading-tight">
            {formatPoints(points)}
          </SilverGradientText>
          <Text variant="small" className="text-white/55">
            Poin
          </Text>
        </View>

        <View className="items-center justify-center rounded-2xl bg-white/10 px-4 py-3">
          <Image
            source={getTierIconSource(currentTier)}
            className="h-9 w-9"
            contentFit="contain"
            accessibilityLabel={`Tier ${tierName}`}
          />
          <Text className={cn("mt-1 font-semibold", tierTextColor)}>{tierName}</Text>
        </View>
      </View>
    </View>
  );
}
