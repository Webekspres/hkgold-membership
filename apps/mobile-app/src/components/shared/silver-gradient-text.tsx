import MaskedView from "@react-native-masked-view/masked-view";
import { LinearGradient } from "expo-linear-gradient";

import { Text } from "@/components/ui/text";
import {
  SILVER_GRADIENT_COLORS,
  SILVER_GRADIENT_END,
  SILVER_GRADIENT_LOCATIONS,
  SILVER_GRADIENT_START,
} from "@/config/brand";
import { FONT } from "@/lib/fonts";
import { cn } from "@/lib/utils";

type GradientFontFamily = keyof typeof FONT;
type GradientFontWeight = keyof typeof FONT.sans;

type SilverGradientTextProps = {
  children: string;
  className?: string;
  /** Default: sans (Rubik). */
  fontFamily?: GradientFontFamily;
  /** Default: regular. Pakai prop ini, bukan `font-bold` di className. */
  fontWeight?: GradientFontWeight;
};

function gradientFontStyle(
  fontFamily: GradientFontFamily,
  fontWeight: GradientFontWeight,
) {
  return {
    fontFamily: FONT[fontFamily][fontWeight],
    fontWeight: "400" as const,
  };
}

/** Teks gradasi silver. Serif: `fontFamily="serif"`. */
export function SilverGradientText({
  children,
  className,
  fontFamily = "sans",
  fontWeight = "regular",
}: SilverGradientTextProps) {
  const fontStyle = gradientFontStyle(fontFamily, fontWeight);

  return (
    <MaskedView
      maskElement={
        <Text
          className={cn("bg-transparent text-black", className)}
          style={fontStyle}
        >
          {children}
        </Text>
      }
    >
      <LinearGradient
        colors={[...SILVER_GRADIENT_COLORS]}
        locations={[...SILVER_GRADIENT_LOCATIONS]}
        start={SILVER_GRADIENT_START}
        end={SILVER_GRADIENT_END}
      >
        <Text className={cn("opacity-0", className)} style={fontStyle}>
          {children}
        </Text>
      </LinearGradient>
    </MaskedView>
  );
}
