import { LinearGradient } from "expo-linear-gradient";
import { router } from "expo-router";
import { View } from "react-native";

import { GoldButton } from "@/components/gold-button";
import { Text } from "@/components/ui/text";
import {
  GOLD_GRADIENT_COLORS,
  GOLD_GRADIENT_END,
  GOLD_GRADIENT_START,
} from "@/constants/brand";
import type { UpcomingEvent } from "@/constants/mock-events";
import { formatEventDateLabel } from "@/lib/format-event-date";

type UpcomingEventCardProps = {
  event: UpcomingEvent;
};

export function UpcomingEventCard({ event }: UpcomingEventCardProps) {
  const { day, month } = formatEventDateLabel(event.eventDate);

  return (
    <View className="rounded-xl shadow-md shadow-stone-900/25">
      {/* <LinearGradient
        colors={[...GOLD_GRADIENT_COLORS]}
        start={GOLD_GRADIENT_START}
        end={GOLD_GRADIENT_END}
        style={{ borderRadius: 12, padding: 2 }}>
      </LinearGradient> */}
      <View className="rounded-[10px] bg-white p-4 border border-stone-200">
        <View className="flex-row gap-4">
          <View className="min-w-14 items-center justify-center">
            <Text className="text-3xl font-bold leading-none text-stone-900">
              {day}
            </Text>
            <Text className="mt-1 text-sm text-stone-500">{month}</Text>
          </View>

          <View className="min-w-0 flex-1 gap-3">
            <Text
              className="text-base font-semibold leading-snug text-stone-900"
              numberOfLines={2}
            >
              {event.title}
            </Text>

            <GoldButton
              variant="outline"
              width="full"
              label="Lihat sekarang"
              onPress={() =>
                router.push({
                  pathname: "/event/[slug]",
                  params: { slug: event.slug },
                })
              }
            />
          </View>
        </View>
      </View>
    </View>
  );
}
