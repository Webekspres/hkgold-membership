import { router } from "expo-router";
import { View } from "react-native";

import { GoldButton } from "@/components/shared/gold-button";
import { Text } from "@/components/ui/text";
import type { UpcomingEvent } from "@/types/event";
import { formatEventDateLabel } from "@/lib/format/format-event-date";

type UpcomingEventCardProps = {
  event: UpcomingEvent;
};

export function UpcomingEventCard({ event }: UpcomingEventCardProps) {
  const { day, month } = formatEventDateLabel(event.eventDate);

  return (
    <View className="rounded-xl border border-stone-200 shadow-md shadow-stone-900/15">
      <View className="rounded-[10px] border border-stone-200 bg-white p-4">
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
                  pathname: "/events/[id]",
                  params: { id: event.id },
                })
              }
            />
          </View>
        </View>
      </View>
    </View>
  );
}
