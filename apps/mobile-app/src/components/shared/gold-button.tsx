import { LinearGradient } from "expo-linear-gradient";
import { Pressable, View } from "react-native";

import { Text } from "@/components/ui/text";
import {
  GOLD_GRADIENT_COLORS,
  GOLD_GRADIENT_END,
  GOLD_GRADIENT_START,
} from "@/config/brand";
import { cn } from "@/lib/utils";

type GoldButtonProps = {
  label: string;
  onPress: () => void;
  variant?: "filled" | "outline";
  width?: "fit" | "full";
  disabled?: boolean;
  className?: string;
};

export function GoldButton({
  label,
  onPress,
  variant = "outline",
  width = "fit",
  disabled = false,
  className,
}: GoldButtonProps) {
  const isFullWidth = width === "full";

  return (
    <Pressable
      className={cn(
        !disabled && "active:opacity-90",
        isFullWidth && "w-full",
        className,
      )}
      disabled={disabled}
      onPress={onPress}
    >
      {disabled ? (
        variant === "outline" ? (
          <View className="items-center rounded-md border border-stone-200 bg-stone-100 px-4 py-2.5">
            <Text className="font-semibold text-stone-400">{label}</Text>
          </View>
        ) : (
          <View className="items-center rounded-md bg-stone-200 px-4 py-2.5">
            <Text className="font-semibold text-stone-400">{label}</Text>
          </View>
        )
      ) : variant === "outline" ? (
        <LinearGradient
          colors={[...GOLD_GRADIENT_COLORS]}
          start={GOLD_GRADIENT_START}
          end={GOLD_GRADIENT_END}
          style={{
            borderRadius: 6,
            padding: 2,
            width: isFullWidth ? "100%" : undefined,
          }}
        >
          <View className="items-center rounded-[4px] bg-white px-4 py-2.5">
            <Text className="font-semibold text-[#9A6B1F]">{label}</Text>
          </View>
        </LinearGradient>
      ) : (
        <LinearGradient
          colors={[...GOLD_GRADIENT_COLORS]}
          start={GOLD_GRADIENT_START}
          end={GOLD_GRADIENT_END}
          style={{
            borderRadius: 6,
            paddingVertical: 10,
            paddingHorizontal: 16,
            alignItems: "center",
            width: isFullWidth ? "100%" : undefined,
          }}
        >
          <Text className="font-semibold text-stone-800">{label}</Text>
        </LinearGradient>
      )}
    </Pressable>
  );
}

