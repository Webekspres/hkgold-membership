import { LinearGradient } from "expo-linear-gradient";
import type { LucideIcon } from "lucide-react-native";
import { cssInterop } from "nativewind";
import { Platform, View } from "react-native";

import { Icon } from "@/components/ui/icon";
import {
  GOLD_GRADIENT_COLORS,
  GOLD_GRADIENT_SHORTCUT_END,
  GOLD_GRADIENT_SHORTCUT_START,
} from "@/config/brand";
import { cn } from "@/lib/utils";

cssInterop(LinearGradient, { className: "style" });

type GoldCircleIconProps = {
  icon: LucideIcon;
  /** Circle size — pass static NativeWind literals, e.g. `size-9` / `size-11`. */
  circleClassName?: string;
  /** Icon size/color — pass static NativeWind literals, e.g. `size-4 text-white`. */
  iconClassName?: string;
  /** Shadow on the outer wrapper. */
  shadowClassName?: string;
  /** Destructive/logout style: glossy red gradient instead of gold. */
  destructive?: boolean;
  className?: string;
};

export function GoldCircleIcon({
  icon,
  circleClassName = "size-9",
  iconClassName = "size-4 text-white",
  shadowClassName = "shadow-sm shadow-amber-700/30",
  destructive = false,
  className,
}: GoldCircleIconProps) {
  if (destructive) {
    return (
      <View
        className={cn(
          "rounded-full shadow-sm shadow-red-700/30",
          className,
        )}
        style={
          Platform.OS === "android"
            ? { elevation: 4, backgroundColor: "transparent" }
            : undefined
        }
      >
        <LinearGradient
          colors={["#7F1D1D", "#FCA5A5", "#DC2626", "#7F1D1D"]}
          locations={[0, 0.32, 0.62, 1]}
          start={{ x: 0, y: 0 }}
          end={{ x: 1, y: 1 }}
          className={cn(
            "items-center justify-center rounded-full",
            circleClassName,
          )}
        >
          <Icon as={icon} className={iconClassName} />
        </LinearGradient>
      </View>
    );
  }

  return (
    <View
      className={cn("rounded-full", shadowClassName, className)}
      style={
        Platform.OS === "android"
          ? { elevation: 4, backgroundColor: "transparent" }
          : undefined
      }
    >
      <LinearGradient
        colors={[...GOLD_GRADIENT_COLORS]}
        start={GOLD_GRADIENT_SHORTCUT_START}
        end={GOLD_GRADIENT_SHORTCUT_END}
        className={cn(
          "items-center justify-center rounded-full",
          circleClassName,
        )}
      >
        <Icon as={icon} className={iconClassName} />
      </LinearGradient>
    </View>
  );
}
