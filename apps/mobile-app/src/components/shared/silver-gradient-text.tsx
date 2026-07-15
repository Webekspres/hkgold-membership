import MaskedView from "@react-native-masked-view/masked-view";
import { LinearGradient } from "expo-linear-gradient";

import { Text } from "@/components/ui/text";
import {
  GOLD_GRADIENT_END,
  GOLD_GRADIENT_START,
  SILVER_GRADIENT_COLORS,
} from "@/config/brand";
import { cn } from "@/lib/utils";

type SilverGradientTextProps = {
  children: string;
  className?: string;
};

/** Teks dengan fill gradasi silver (mask + LinearGradient). */
export function SilverGradientText({
  children,
  className,
}: SilverGradientTextProps) {
  return (
    <MaskedView
      maskElement={
        <Text className={cn("bg-transparent text-black", className)}>
          {children}
        </Text>
      }
    >
      <LinearGradient
        colors={[...SILVER_GRADIENT_COLORS]}
        start={GOLD_GRADIENT_START}
        end={GOLD_GRADIENT_END}
      >
        <Text className={cn("opacity-0", className)}>{children}</Text>
      </LinearGradient>
    </MaskedView>
  );
}
