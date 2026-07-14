import { Image } from "expo-image";
import { LinearGradient } from "expo-linear-gradient";
import { router } from "expo-router";
import { cssInterop } from "nativewind";
import { Pressable, View } from "react-native";

import { GoldGradientText } from "@/components/shared/gold-gradient-text";
import { Text } from "@/components/ui/text";
import { getTierIconSource } from "@/config/assets";
import {
  GOLD_GRADIENT_COLORS,
  GOLD_GRADIENT_END,
  GOLD_GRADIENT_START,
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
  }
> = {
  SILVER: {
    label: "Silver",
    textClassName: "text-stone-200",
  },
  GOLD: {
    label: "Gold",
    textClassName: "text-[#f5c842]",
  },
  PLATINUM: {
    label: "Platinum",
    textClassName: "text-slate-200",
  },
  SAPPHIRE: {
    label: "Sapphire",
    textClassName: "text-indigo-200",
  },
};

/** Pattern fade gelap agar teks tetap terbaca di kartu hitam. */
const PATTERN_FADE = [
  "rgba(10,10,10,0.45)",
  "rgba(10,10,10,0.78)",
  "rgba(10,10,10,0.94)",
] as const;

const DIVIDER_COLORS = [
  "transparent",
  GOLD_GRADIENT_COLORS[0],
  GOLD_GRADIENT_COLORS[1],
  "transparent",
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
      <GoldGradientText className="text-sm font-medium">
        {memberNumber}
      </GoldGradientText>
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
      <View className="overflow-hidden rounded-xl border-0 bg-[#0a0a0a] px-5 py-5 shadow-lg shadow-stone-900/30">
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

        <View className="relative z-10">
          <Text
            className="mb-2 text-xl font-semibold text-white"
            numberOfLines={2}
          >
            {fullName}
          </Text>

          <View className="flex-row items-stretch">
            <View className="min-w-0 flex-1 justify-center pr-4">
              <MemberNumber
                memberNumber={memberNumber}
                onPressMemberNumber={onPressMemberNumber}
              />

              <Text className="mt-4 text-xs uppercase tracking-wide text-white/55">
                Saldo poin
              </Text>
              <GoldGradientText className="mt-0.5 text-3xl font-bold leading-tight">
                {formatPointBalance(pointBalance)}
              </GoldGradientText>
              <Text variant="small" className="text-white/55">
                Poin
              </Text>
            </View>

            <LinearGradient
              colors={[...DIVIDER_COLORS]}
              locations={[0, 0.25, 0.75, 1]}
              start={{ x: 0.5, y: 0 }}
              end={{ x: 0.5, y: 1 }}
              className="w-[1.5px] self-stretch"
            />

            <View className="w-[30%] items-center justify-center pl-3">
              <Image
                source={getTierIconSource(currentTier)}
                className="h-20 w-20"
                contentFit="contain"
                accessibilityLabel={`Tier ${tier.label}`}
              />
              <Text
                variant="small"
                className={cn("mt-1.5 font-semibold", tier.textClassName)}
              >
                {tier.label}
              </Text>
              {/* <Text variant="small" className="text-white/55">
                Member
              </Text> */}
            </View>
          </View>
        </View>
      </View>
    </CardWrapper>
  );
}
