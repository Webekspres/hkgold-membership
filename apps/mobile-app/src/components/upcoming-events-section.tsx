import { router } from 'expo-router';
import { Pressable, View } from 'react-native';

import { UpcomingEventCard } from '@/components/upcoming-event-card';
import { Text } from '@/components/ui/text';
import type { UpcomingEvent } from '@/constants/mock-events';
import { cn } from '@/lib/utils';

type UpcomingEventsSectionProps = {
  events: UpcomingEvent[];
  className?: string;
};

export function UpcomingEventsSection({ events, className }: UpcomingEventsSectionProps) {
  if (events.length === 0) {
    return null;
  }

  return (
    <View className={cn('gap-3', className)}>
      <View className="flex-row items-center justify-between px-4">
        <Text className="text-base font-semibold text-stone-900">Event Terdekat</Text>
        <Pressable onPress={() => router.push('/events')} className="active:opacity-70">
          <Text className="text-sm font-medium text-[#c4841a]">Lihat semua</Text>
        </Pressable>
      </View>

      <View className="gap-3 px-4">
        {events.map((event) => (
          <UpcomingEventCard key={event.id} event={event} />
        ))}
      </View>
    </View>
  );
}
