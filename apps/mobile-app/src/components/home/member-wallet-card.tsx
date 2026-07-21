import { Image } from "expo-image";

import { LinearGradient } from "expo-linear-gradient";

import { router } from "expo-router";

import { cssInterop } from "nativewind";

import { Platform, Pressable, View } from "react-native";

import { SilverGradientText } from "@/components/shared/silver-gradient-text";

import { Text } from "@/components/ui/text";

import {
  GOLD_GRADIENT_END,
  GOLD_GRADIENT_START,
  SILVER_GRADIENT_COLORS,
  TIER_CARD_BACKGROUND_IMAGES,
} from "@/config/brand";

import { cn } from "@/lib/utils";

import type { MemberTier } from "@/types/auth";

cssInterop(LinearGradient, { className: "style" });

cssInterop(Image, { className: "style" });

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
  }
> = {
  SILVER: {
    label: "Silver",

    textClassName: "text-stone-700",
  },

  GOLD: {
    label: "Gold",

    textClassName: "text-[#3D2608]",
  },

  PLATINUM: {
    label: "Platinum",

    textClassName: "text-slate-800",
  },

  ELITE: {
    label: "Elite",

    textClassName: "text-indigo-950",
  },
};

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
}: {
  memberNumber: string;
  onPressMemberNumber?: () => void;
  textClassName: string;
}) {
  const pillColors = SILVER_GRADIENT_COLORS;

  const pill = (
    <View className="self-start overflow-hidden rounded-full px-3 py-1">
      <LinearGradient
        colors={[...pillColors]}
        start={GOLD_GRADIENT_START}
        end={GOLD_GRADIENT_END}
        className="absolute inset-0"
      />
      <Text className={cn("text-xs font-semibold", textClassName)}>
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
  const isSilver = currentTier === "SILVER";

  return (
    <CardWrapper pressable={pressable} className={className}>
      <View
        className={cn(
          "overflow-hidden rounded-xl border-0 px-5 py-5 shadow-lg shadow-stone-900/30",
          currentTier === "GOLD" &&
            "border border-amber-200/15 shadow-xl shadow-amber-950/45",
        )}
      >
        {/* Tier-specific static card background image */}
        <Image
          source={TIER_CARD_BACKGROUND_IMAGES[currentTier]}
          className="absolute"
          style={{
            top: -40,
            bottom: 0,
            left: -10,
            right: -10,
          }}
          contentFit="cover"
          pointerEvents="none"
        />

        <View className="relative z-10 pb-2">
          <SilverGradientText
            className="mb-2 text-xl"
            fontWeight="bold"
            fontFamily="serif"
            solidWhite={isSilver}
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
              />
              <View>
                <LinearGradient
                  colors={[
                    "rgba(110,110,110,0.75)",
                    "rgba(255,255,255,0.95)",
                    "rgba(110,110,110,0.65)",
                    "rgba(110,110,110,0)",
                  ]}
                  locations={[0, 0.3, 0.65, 1]}
                  start={{ x: 0, y: 0.5 }}
                  end={{ x: 1, y: 0.5 }}
                  className="mb-2 h-0.5 w-32"
                />

                <SilverGradientText
                  className="text-2xl"
                  fontFamily="serif"
                  fontWeight="bold"
                  solidWhite={isSilver}
                >
                  {tier.label}
                </SilverGradientText>
              </View>
            </View>

            <View className="items-start mt-6">
              <Text className={cn("mb-2 text-base leading-none text-white")}>
                Point :
              </Text>

              <SilverGradientText
                fontFamily="serif"
                fontWeight="bold"
                className="font-libre-baskerville-bold text-6xl leading-none"
                solidWhite={isSilver}
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
