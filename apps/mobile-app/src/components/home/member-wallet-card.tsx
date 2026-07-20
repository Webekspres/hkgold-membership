import { Image } from "expo-image";

import { LinearGradient } from "expo-linear-gradient";

import { router } from "expo-router";

import { cssInterop } from "nativewind";

import { Platform, Pressable, View } from "react-native";

import { SilverGradientText } from "@/components/shared/silver-gradient-text";

import { Text } from "@/components/ui/text";

import {
  GOLD_CARD_GRADIENT_END,
  GOLD_CARD_GRADIENT_START,
  GOLD_CARD_VIGNETTE,
  GOLD_GRADIENT_END,
  GOLD_GRADIENT_START,
  GOLD_MEMBER_PILL_COLORS,
  SILVER_GRADIENT_COLORS,
  TIER_GRADIENTS,
} from "@/config/brand";

import { cn } from "@/lib/utils";

import type { MemberTier } from "@/types/auth";

cssInterop(LinearGradient, { className: "style" });

cssInterop(Image, { className: "style" });

const SWOOSH_ELITE = require("@/assets/media/tier/elite-swoosh.webp");
const SWOOSH_GOLD = require("@/assets/media/tier/gold-swoosh.webp");

function swooshAssetForTier(tier: MemberTier) {
  return tier === "ELITE" ? SWOOSH_ELITE : SWOOSH_GOLD;
}

/** Tinggi dekorasi swoosh di bawah kartu. */

const SWOOSH_H = 56;

const SWOOSH_SHADOW = Platform.select({
  ios: {
    shadowColor: "#000",
    shadowOffset: { width: 0, height: -2 },
    shadowOpacity: 0.2,
    shadowRadius: 3,
  },
  android: {
    elevation: 3,
    backgroundColor: "transparent",
  },
  default: {},
});

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

    textClassName: "text-[#3D2608]",

    backgroundColors: TIER_GRADIENTS.GOLD.colors,

    dividerColors: TIER_GRADIENTS.GOLD.divider,
  },

  PLATINUM: {
    label: "Platinum",

    textClassName: "text-slate-200",

    backgroundColors: TIER_GRADIENTS.PLATINUM.colors,

    dividerColors: TIER_GRADIENTS.PLATINUM.divider,
  },

  ELITE: {
    label: "Elite",

    textClassName: "text-indigo-950",

    backgroundColors: TIER_GRADIENTS.ELITE.colors,

    dividerColors: TIER_GRADIENTS.ELITE.divider,
  },
};

/** Pattern fade gelap agar teks tetap terbaca di kartu hitam. */

const PATTERN_FADE = [
  "rgba(10,10,10,0.75)",

  "rgba(10,10,10,0.45)",

  "rgba(10,10,10,0.75)",
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
  textClassName,
  currentTier,
}: {
  memberNumber: string;
  onPressMemberNumber?: () => void;
  textClassName: string;
  currentTier: MemberTier;
}) {
  const pillColors =
    currentTier === "GOLD"
      ? GOLD_MEMBER_PILL_COLORS
      : SILVER_GRADIENT_COLORS;

  const pill = (
    <View className="self-start overflow-hidden rounded-full px-3 py-1">
      <LinearGradient
        colors={[...pillColors]}
        start={GOLD_GRADIENT_START}
        end={GOLD_GRADIENT_END}
        className="absolute inset-0"
      />
      <Text className={cn("text-xs font-medium", textClassName)}>
        {memberNumber}
      </Text>
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

function CardBottomSwoosh({ currentTier }: { currentTier: MemberTier }) {
  return (
    <View
      pointerEvents="none"

      className="absolute bottom-0 left-0 right-0"

      style={{ height: SWOOSH_H }}
    >
      <View className="size-full" style={SWOOSH_SHADOW}>
        <Image
          source={swooshAssetForTier(currentTier)}

          className="size-full"

          contentFit="fill"

          accessibilityElementsHidden

          importantForAccessibility="no-hide-descendants"
        />
      </View>
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
  const gradientStart =
    currentTier === "GOLD" ? GOLD_CARD_GRADIENT_START : GOLD_GRADIENT_START;
  const gradientEnd =
    currentTier === "GOLD" ? GOLD_CARD_GRADIENT_END : GOLD_GRADIENT_END;

  return (
    <CardWrapper pressable={pressable} className={className}>
      <View
        className={cn(
          "overflow-hidden rounded-xl border-0 px-5 py-5 shadow-lg shadow-stone-900/30",
          currentTier === "GOLD" &&
            "border border-amber-200/15 shadow-xl shadow-amber-950/45",
        )}
      >
        {/* Tier-specific background gradient */}

        <LinearGradient
          colors={tier.backgroundColors as any}

          start={gradientStart}

          end={gradientEnd}

          className="absolute inset-0"
        />

        {currentTier === "GOLD" ? (
          <View className="absolute inset-0" pointerEvents="none">
            <Image
              source={require("@/assets/media/pattern-horizontal.webp")}
              className="absolute inset-0 size-full scale-150 opacity-25"
              contentFit="cover"
            />
            <LinearGradient
              colors={[...GOLD_CARD_VIGNETTE]}
              start={GOLD_CARD_GRADIENT_START}
              end={GOLD_CARD_GRADIENT_END}
              className="absolute inset-0"
            />
          </View>
        ) : (
          <View className="absolute inset-0" pointerEvents="none">
            <Image
              source={require("@/assets/media/pattern-horizontal.webp")}

              className="absolute inset-0 size-full opacity-90 scale-150"

              contentFit="cover"
            />

            <LinearGradient
              colors={[...PATTERN_FADE]}

              start={GOLD_GRADIENT_START}

              end={GOLD_GRADIENT_END}

              className="absolute inset-0"
            />
          </View>
        )}

        <CardBottomSwoosh currentTier={currentTier} />

        <View className="relative z-10 pb-2">
          <SilverGradientText
            className="mb-2 text-xl"
            fontWeight="bold"
            fontFamily="serif"
          >
            {fullName}
          </SilverGradientText>

          <View className="flex-row items-stretch justify-between">
            {/* Bottom row: tier label left, points right */}

            <View className="flex-col justify-between self-stretch">
              <MemberNumber
                memberNumber={memberNumber}
                onPressMemberNumber={onPressMemberNumber}
                textClassName={tier.textClassName}
                currentTier={currentTier}
              />

              <SilverGradientText
                className="text-2xl"
                fontFamily="serif"
                fontWeight="bold"
              >
                {tier.label}
              </SilverGradientText>
            </View>

            <View className="items-end mt-4">
              <Text
                className={cn(
                  "mb-2 text-base leading-none",
                  currentTier === "GOLD" ? "text-amber-100/55" : "text-white/55",
                )}
              >
                Point :
              </Text>

              <SilverGradientText
                fontFamily="serif"
                fontWeight="bold"
                className="font-libre-baskerville-bold text-6xl leading-none"
              >
                {formatPointBalance(pointBalance)}
              </SilverGradientText>
            </View>
          </View>
        </View>
      </View>
    </CardWrapper>
  );
}
