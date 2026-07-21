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
  SILVER_GRADIENT_COLORS,
  TIER_GRADIENTS,
} from "@/config/brand";
import { cn } from "@/lib/utils";
import type { MemberTier } from "@/types/auth";

cssInterop(LinearGradient, { className: "style" });
cssInterop(Image, { className: "style" });

const MEMBER_NUMBER_TEXT_COLORS: Record<MemberTier, string> = {
  SILVER: "text-stone-700",
  GOLD: "text-[#3D2608]",
  PLATINUM: "text-slate-800",
  ELITE: "text-indigo-950",
};

type ProfileMemberCardProps = {
  fullName: string;
  memberCode: string;
  currentTier: MemberTier;
  avatarUri?: string;
  avatarFallback: string;
  onPressMemberCode: () => void;
};

export function ProfileMemberCard({
  fullName,
  memberCode,
  currentTier,
  avatarUri,
  avatarFallback,
  onPressMemberCode,
}: ProfileMemberCardProps) {
  const cardGradient = TIER_GRADIENTS[currentTier];
  const isSilver = currentTier === "SILVER";

  return (
    <View
      className={cn(
        "overflow-hidden rounded-xl border-0 px-5 py-5 shadow-lg shadow-stone-900/30",
        currentTier === "GOLD" &&
          "border border-amber-200/15 shadow-xl shadow-amber-950/45",
      )}>
      <LinearGradient
        colors={cardGradient.colors as any}
        start={cardGradient.start}
        end={cardGradient.end}
        className="absolute inset-0"
      />

      <View className="absolute inset-0" pointerEvents="none">
        <Image
          source={require("@/assets/media/pattern-horizontal.webp")}
          className="absolute inset-0 size-full scale-150"
          style={{ opacity: cardGradient.patternOpacity }}
          contentFit="cover"
        />
        <LinearGradient
          colors={cardGradient.vignetteColors as any}
          start={cardGradient.vignetteStart}
          end={cardGradient.vignetteEnd}
          className="absolute inset-0"
        />
      </View>

      <View className="relative z-10 flex-row items-center gap-4">
        <Avatar alt={fullName} className="size-16 border border-white/20">
          <AvatarImage source={avatarUri ? { uri: avatarUri } : undefined} />
          <AvatarFallback>
            <Text className="text-lg font-semibold text-stone-700">{avatarFallback}</Text>
          </AvatarFallback>
        </Avatar>

        <View className="min-w-0 flex-1">
          <SilverGradientText
            className="mb-2 text-xl"
            fontFamily="serif"
            fontWeight="bold"
            solidWhite={isSilver}>
            {fullName}
          </SilverGradientText>
          <Pressable
            className="self-start overflow-hidden rounded-full px-3 py-1 active:opacity-80"
            onPress={onPressMemberCode}
            accessibilityRole="button"
            accessibilityLabel="Salin nomor member">
            <LinearGradient
              colors={[...SILVER_GRADIENT_COLORS]}
              start={GOLD_GRADIENT_START}
              end={GOLD_GRADIENT_END}
              className="absolute inset-0"
            />
            <Text
              className={cn(
                "text-xs font-semibold",
                MEMBER_NUMBER_TEXT_COLORS[currentTier],
              )}>
                {memberCode}
            </Text>
          </Pressable>
        </View>
      </View>
    </View>
  );
}
