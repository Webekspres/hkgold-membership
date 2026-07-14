import { Image } from "expo-image";
import { router } from "expo-router";
import { cssInterop } from "nativewind";
import { View } from "react-native";

import { EventDateBadge } from "@/components/event/event-date-badge";
import { GoldButton } from "@/components/shared/gold-button";
import { Text } from "@/components/ui/text";
import type { EventItem } from "@/types/event";
import { formatEventDateLabel } from "@/lib/format/format-event-date";

cssInterop(Image, { className: "style" });

const PLACEHOLDER_IMAGE = require("@/assets/mockImage/mock-image-news.webp");

type EventListCardProps = {
  event: EventItem;
};

export function EventListCard({ event }: EventListCardProps) {
  const { day, month } = formatEventDateLabel(event.eventDate);

  return (
    <View className="overflow-hidden rounded-xl border-0 bg-white shadow-lg shadow-stone-900/30">
      <Image
        source={event.imageUrl ? { uri: event.imageUrl } : PLACEHOLDER_IMAGE}
        style={{ width: "100%", aspectRatio: 16 / 9 }}
        contentFit="cover"
        accessibilityLabel={event.title}
      />

      <View className="p-4">
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
    </View>
  );
}
