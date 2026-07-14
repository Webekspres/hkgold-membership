import { router } from "expo-router";
import { View } from "react-native";

import { EventDateBadge } from "@/components/event/event-date-badge";
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
    <View className="overflow-hidden rounded-xl border-0 bg-white p-4 shadow-lg shadow-stone-900/30">
      <View className="flex-row gap-4">
        <EventDateBadge day={day} month={month} />

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
  );
}
