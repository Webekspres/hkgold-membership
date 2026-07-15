import { Image } from "expo-image";
import { LinearGradient } from "expo-linear-gradient";
import { cssInterop } from "nativewind";
import { Pressable, View } from "react-native";

import { SilverGradientText } from "@/components/shared/silver-gradient-text";
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";
import { Text } from "@/components/ui/text";
import {
  GOLD_GRADIENT_END,
  GOLD_GRADIENT_START,
  TIER_GRADIENTS,
} from "@/config/brand";
import { cn } from "@/lib/utils";
import type { MemberTier } from "@/types/auth";

cssInterop(LinearGradient, { className: "style" });
cssInterop(Image, { className: "style" });

const PATTERN_FADE = [
  "rgba(10,10,10,0.45)",
  "rgba(10,10,10,0.78)",
  "rgba(10,10,10,0.94)",
] as const;

const TIER_TEXT_COLORS: Record<MemberTier, string> = {
  SILVER: "text-stone-200",
  GOLD: "text-[#f5c842]",
  PLATINUM: "text-slate-200",
  SAPPHIRE: "text-indigo-200",
};

function formatPoints(points: number) {
  return points.toLocaleString("id-ID");
}

type ProfileMemberCardProps = {
  fullName: string;
  memberCode: string;
  currentTier: MemberTier;
  points: number;
  tierName: string;
  avatarUri?: string;
  avatarFallback: string;
  onPressMemberCode: () => void;
};

export function ProfileMemberCard({
  fullName,
  memberCode,
  currentTier,
  points,
  tierName,
  avatarUri,
  avatarFallback,
  onPressMemberCode,
}: ProfileMemberCardProps) {
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

      <View className="relative z-10 p-4">
        {/* Top section: Avatar + Name + Member Code */}
        <View className="flex-row items-center gap-3">
          <Avatar alt={fullName} className="size-16 border border-white/20">
            <AvatarImage source={avatarUri ? { uri: avatarUri } : undefined} />
            <AvatarFallback>
              <Text className="text-lg font-semibold text-stone-700">{avatarFallback}</Text>
            </AvatarFallback>
          </Avatar>

          <View className="min-w-0 flex-1">
            <SilverGradientText className="text-lg font-semibold">
              {fullName}
            </SilverGradientText>
            <Pressable
              className="mt-1 self-start rounded-full bg-white/10 px-3 py-1 active:opacity-80"
              onPress={onPressMemberCode}>
              <SilverGradientText className="text-sm">
                {memberCode}
              </SilverGradientText>
            </Pressable>
          </View>
        </View>

        {/* Bottom row: Tier label left, Points right */}
        <View className="mt-4 flex-row items-end justify-between">
          <Text className={cn("text-lg font-semibold", tierTextColor)}>
            {tierName}
          </Text>

          <View className="items-end">
            <Text variant="small" className="text-white/55">
              Poin
            </Text>
            <SilverGradientText className="text-4xl font-bold leading-tight">
              {formatPoints(points)}
            </SilverGradientText>
          </View>
        </View>
      </View>
    </View>
  );
}
