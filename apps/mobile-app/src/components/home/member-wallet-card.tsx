import { Image } from "expo-image";
import { LinearGradient } from "expo-linear-gradient";
import { router } from "expo-router";
import { cssInterop } from "nativewind";
import { Pressable, View } from "react-native";

import { SilverGradientText } from "@/components/shared/silver-gradient-text";
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

const SWOOSH_ASSET = require("@/assets/media/swoosh.webp");
/** Tinggi dekorasi swoosh di bawah kartu. */
const SWOOSH_H = 56;

export type { MemberTier };

export type MemberWalletCardProps = {
  fullName: string;
  memberNumber: string;
  currentTier: MemberTier;
  pointBalance: number;
  className?: string;
  pressable?: boolean;
  onPressMemberNumber?: () => void;
};

const TIER_STYLES: Record<
  MemberTier,
  {
    label: string;
    textClassName: string;
    backgroundColors: any;
    dividerColors: any;
  }
> = {
  SILVER: {
    label: "Silver",
    textClassName: "text-stone-200",
    backgroundColors: TIER_GRADIENTS.SILVER.colors,
    dividerColors: TIER_GRADIENTS.SILVER.divider,
  },
  GOLD: {
    label: "Gold",
    textClassName: "text-[#f5c842]",
    backgroundColors: TIER_GRADIENTS.GOLD.colors,
    dividerColors: TIER_GRADIENTS.GOLD.divider,
  },
  PLATINUM: {
    label: "Platinum",
    textClassName: "text-slate-200",
    backgroundColors: TIER_GRADIENTS.PLATINUM.colors,
    dividerColors: TIER_GRADIENTS.PLATINUM.divider,
  },
  SAPPHIRE: {
    label: "Sapphire",
    textClassName: "text-indigo-200",
    backgroundColors: TIER_GRADIENTS.SAPPHIRE.colors,
    dividerColors: TIER_GRADIENTS.SAPPHIRE.divider,
  },
};

/** Pattern fade gelap agar teks tetap terbaca di kartu hitam. */
const PATTERN_FADE = [
  "rgba(10,10,10,0.45)",
  "rgba(10,10,10,0.78)",
  "rgba(10,10,10,0.94)",
] as const;

function formatPointBalance(points: number) {
  return (points ?? 0).toLocaleString("id-ID");
}

function CardWrapper({
  pressable,
  className,
  children,
}: {
  pressable: boolean;
  className?: string;
  children: React.ReactNode;
}) {
  if (pressable) {
    return (
      <Pressable
        className={cn("active:opacity-95", className)}
        onPress={() => router.push("/card")}
        accessibilityRole="button"
        accessibilityLabel="Buka kartu member"
      >
        {children}
      </Pressable>
    );
  }

  return <View className={className}>{children}</View>;
}

function MemberNumber({
  memberNumber,
  onPressMemberNumber,
}: {
  memberNumber: string;
  onPressMemberNumber?: () => void;
}) {
  const pill = (
    <View className="self-start rounded-full bg-white/10 px-3 py-1">
      <SilverGradientText className="text-sm font-medium">
        {memberNumber}
      </SilverGradientText>
    </View>
  );

  if (onPressMemberNumber) {
    return (
      <Pressable
        className="self-start active:opacity-80"
        onPress={onPressMemberNumber}
        accessibilityRole="button"
        accessibilityLabel="Salin nomor member"
      >
        {pill}
      </Pressable>
    );
  }

  return pill;
}

/** Swoosh bawah kartu — asset full-bleed. */
function CardBottomSwoosh() {
  return (
    <View
      pointerEvents="none"
      className="absolute bottom-0 left-0 right-0 overflow-hidden"
      style={{ height: SWOOSH_H }}
    >
      <Image
        source={SWOOSH_ASSET}
        className="size-full"
        contentFit="fill"
        accessibilityElementsHidden
        importantForAccessibility="no-hide-descendants"
      />
    </View>
  );
}

export function MemberWalletCard({
  fullName,
  memberNumber,
  currentTier,
  pointBalance,
  className,
  pressable = true,
  onPressMemberNumber,
}: MemberWalletCardProps) {
  const tier = TIER_STYLES[currentTier];

  return (
    <CardWrapper pressable={pressable} className={className}>
      <View className="overflow-hidden rounded-xl border-0 px-5 py-5 shadow-lg shadow-stone-900/30">
        {/* Tier-specific background gradient */}
        <LinearGradient
          colors={tier.backgroundColors as any}
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

        <CardBottomSwoosh />

        <View className="relative z-10 pb-2">
          <SilverGradientText className="mb-1 text-xl font-semibold">
            {fullName}
          </SilverGradientText>

          <View className="flex-row items-stretch justify-between">
          {/* Bottom row: tier label left, points right */}
            <View className="flex-col justify-between self-stretch">
              <MemberNumber
                memberNumber={memberNumber}
                onPressMemberNumber={onPressMemberNumber}
              />
              <Text className={cn("text-lg font-semibold", tier.textClassName)}>
                {tier.label}
              </Text>
            </View>

            <View className="items-end pt-4">
              <Text variant="small" className="text-white/55">
                Poin
              </Text>
              <SilverGradientText className="text-7xl font-bold leading-none">
                {formatPointBalance(pointBalance)}
              </SilverGradientText>
            </View>
          </View>
        </View>
      </View>
    </CardWrapper>
  );
}
