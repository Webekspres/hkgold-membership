import { useLocalSearchParams } from 'expo-router';
import { useMemo } from 'react';
import { View } from 'react-native';

import { ComingSoonScreen } from '@/components/shared/coming-soon-screen';
import { ContentDetailHighlightBox } from '@/components/shared/content-detail-highlight-box';
import { ContentDetailScreen } from '@/components/shared/content-detail-screen';
import { GoldButton } from '@/components/shared/gold-button';
import { Text } from '@/components/ui/text';
import { getEventBySlug } from '@/services/events';
import { formatEventDateTimeHighlight } from '@/lib/format/format-event-datetime';
import { openLocationUrl } from '@/lib/open-location-url';

export default function EventDetailScreen() {
  const { slug } = useLocalSearchParams<{ slug: string }>();

  const event = useMemo(() => {
    if (typeof slug !== 'string') {
      return null;
    }

    return getEventBySlug(slug);
  }, [slug]);

  if (!event) {
    return <ComingSoonScreen title="Detail Event" />;
  }

  return (
    <ContentDetailScreen images={event.images} title={event.title}>
      <View className="gap-2">
        <Text className="text-2xl font-semibold leading-snug text-stone-900">{event.title}</Text>
        <Text className="text-sm font-medium text-stone-800">{event.locationName}</Text>
        <Text variant="muted" className="text-sm leading-relaxed">
          {event.address}
        </Text>
      </View>

      <ContentDetailHighlightBox label="Tanggal & waktu">
        <Text className="mt-1 text-base font-semibold leading-snug text-[#b45309]">
          {formatEventDateTimeHighlight(event.eventDate)}
        </Text>
      </ContentDetailHighlightBox>

      <Text className="text-sm leading-relaxed text-stone-700">{event.description}</Text>

      {event.locationUrl ? (
        <GoldButton
          variant="filled"
          width="full"
          label="Lihat lokasi"
          onPress={() => openLocationUrl(event.locationUrl)}
        />
      ) : null}
    </ContentDetailScreen>
  );
}
