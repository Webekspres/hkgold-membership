import { LinearGradient } from "expo-linear-gradient";
import { Image } from "expo-image";
import { router } from "expo-router";
import { Crown } from "lucide-react-native";
import { Pressable, View } from "react-native";

import { Icon } from "@/components/ui/icon";
import { Text } from "@/components/ui/text";
import {
  GOLD_GRADIENT_COLORS,
  GOLD_GRADIENT_END,
  GOLD_GRADIENT_START,
} from "@/config/brand";
import { cn } from "@/lib/utils";

export type MemberTier = "SILVER" | "GOLD" | "PLATINUM" | "SAPPHIRE";

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
    panelClassName: string;
    iconClassName: string;
    textClassName: string;
  }
> = {
  SILVER: {
    label: "Silver",
    panelClassName: "bg-stone-100",
    iconClassName: "text-stone-500",
    textClassName: "text-stone-700",
  },
  GOLD: {
    label: "Gold",
    panelClassName: "bg-amber-100/90",
    iconClassName: "text-amber-600",
    textClassName: "text-[#b45309]",
  },
  PLATINUM: {
    label: "Platinum",
    panelClassName: "bg-slate-100",
    iconClassName: "text-slate-500",
    textClassName: "text-slate-700",
  },
  SAPPHIRE: {
    label: "Sapphire",
    panelClassName: "bg-indigo-100",
    iconClassName: "text-indigo-600",
    textClassName: "text-indigo-800",
  },
};

function formatPointBalance(points: number) {
  return points.toLocaleString("id-ID");
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
        accessibilityLabel="Buka kartu member">
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
  if (onPressMemberNumber) {
    return (
      <Pressable
        className="self-start rounded-full bg-stone-100 px-3 py-1 active:opacity-80"
        onPress={onPressMemberNumber}
        accessibilityRole="button"
        accessibilityLabel="Salin nomor member">
        <Text variant="small" className="text-stone-700">
          {memberNumber}
        </Text>
      </Pressable>
    );
  }

  return (
    <Text variant="small" className="text-stone-500">
      {memberNumber}
    </Text>
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
      <LinearGradient
        colors={[...GOLD_GRADIENT_COLORS]}
        start={GOLD_GRADIENT_START}
        end={GOLD_GRADIENT_END}
        style={{ borderRadius: 20, padding: 2 }}>
        <View className="rounded-[18px] bg-white px-5 py-5">
          <Image
            source={require("@/assets/media/background.webp")}
            style={{
              position: "absolute",
              inset: 0,
              opacity: 0.06,
              borderRadius: 18,
            }}
            contentFit="cover"
          />
          <View className="mb-1">
            <Text className="text-xl font-semibold text-stone-900" numberOfLines={2}>
              {fullName}
            </Text>
          </View>

          <View className="flex-row items-stretch gap-3">
            <View className="w-3/5 justify-center">
              <MemberNumber
                memberNumber={memberNumber}
                onPressMemberNumber={onPressMemberNumber}
              />
              <View className="my-4 h-0.5 w-full rounded-full bg-stone-100" />

              <Text variant="muted" className="text-xs uppercase tracking-wide">
                Saldo poin
              </Text>
              <Text className="mt-1 text-3xl font-bold leading-tight text-stone-900">
                {formatPointBalance(pointBalance)}
              </Text>
              <Text variant="small" className="text-stone-600">
                Poin
              </Text>
            </View>

            <View
              className={cn(
                "w-2/5 items-center justify-center rounded-2xl px-3 py-3",
                tier.panelClassName,
              )}>
              <Icon as={Crown} size={34} className={cn(tier.iconClassName)} />
              <Text
                variant="small"
                className={cn("mt-1.5 font-semibold", tier.textClassName)}>
                {tier.label}
              </Text>
            </View>
          </View>
        </View>
      </LinearGradient>
    </CardWrapper>
  );
}
