import MaskedView from "@react-native-masked-view/masked-view";
import { Image } from "expo-image";
import { LinearGradient } from "expo-linear-gradient";
import { cssInterop } from "nativewind";
import { View } from "react-native";

import { Text } from "@/components/ui/text";
import {
  GOLD_GRADIENT_COLORS,
  GOLD_GRADIENT_END,
  GOLD_GRADIENT_START,
} from "@/config/brand";

cssInterop(LinearGradient, { className: "style" });
cssInterop(Image, { className: "style" });

const PATTERN_VERTICAL = require("@/assets/media/pattern-vertical.webp");

type EventDateBadgeProps = {
  day: string;
  month: string;
};

function EventDateGradientLabel({ day, month }: EventDateBadgeProps) {
  return (
    <MaskedView
      maskElement={
        <View className="items-center bg-transparent">
          <Text className="text-3xl font-bold leading-none text-black">
            {day}
          </Text>
          <Text className="mt-0.5 text-[10px] font-medium text-black">
            {month}
          </Text>
        </View>
      }
    >
      <LinearGradient
        colors={[...GOLD_GRADIENT_COLORS]}
        start={GOLD_GRADIENT_START}
        end={GOLD_GRADIENT_END}
      >
        <View className="items-center opacity-0">
          <Text className="text-3xl font-bold leading-none">{day}</Text>
          <Text className="mt-0.5 text-[10px] font-medium">{month}</Text>
        </View>
      </LinearGradient>
    </MaskedView>
  );
}

export function EventDateBadge({ day, month }: EventDateBadgeProps) {
  return (
    <View className="shadow-lg shadow-stone-900/30">
      <LinearGradient
        colors={[...GOLD_GRADIENT_COLORS]}
        start={GOLD_GRADIENT_START}
        end={GOLD_GRADIENT_END}
        className="rounded-full p-[1.5px]"
      >
        <View className="size-20 items-center justify-center overflow-hidden rounded-full bg-white">
          <Image
            source={PATTERN_VERTICAL}
            className="absolute inset-0 opacity-20"
            contentFit="cover"
          />
          <EventDateGradientLabel day={day} month={month} />
        </View>
      </LinearGradient>
    </View>
  );
}
