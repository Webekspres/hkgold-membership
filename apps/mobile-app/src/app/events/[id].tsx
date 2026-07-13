import { useLocalSearchParams } from 'expo-router';
import { ActivityIndicator, Pressable, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';

import { ContentDetailHighlightBox } from '@/components/shared/content-detail-highlight-box';
import { ContentDetailScreen } from '@/components/shared/content-detail-screen';
import { Text } from '@/components/ui/text';
import { useEventDetail } from '@/hooks/use-event-detail';
import { formatEventDateTimeHighlight } from '@/lib/format/format-event-datetime';

export default function EventDetailScreen() {
  const { id } = useLocalSearchParams<{ id: string }>();
  const { event, isLoading, isError, refetch } = useEventDetail(
    typeof id === 'string' ? id : undefined,
  );

  if (isLoading) {
    return (
      <View className="flex-1 items-center justify-center bg-background">
        <ActivityIndicator color="#b45309" />
      </View>
    );
  }

  if (isError || !event) {
    return (
      <SafeAreaView className="flex-1 items-center justify-center gap-3 bg-background px-6">
        <Text className="text-center text-base font-semibold text-stone-900">
          Event tidak ditemukan
        </Text>
        <Text variant="muted" className="text-center">
          Konten mungkin sudah dihapus atau koneksi gagal.
        </Text>
        {isError ? (
          <Pressable onPress={() => void refetch()} className="active:opacity-70">
            <Text className="font-semibold text-[#c4841a]">Coba lagi</Text>
          </Pressable>
        ) : null}
      </SafeAreaView>
    );
  }

  return (
    <ContentDetailScreen images={event.imageUrls} title={event.title}>
      <View className="gap-2">
        <Text className="text-2xl font-semibold leading-snug text-stone-900">{event.title}</Text>
      </View>

      <ContentDetailHighlightBox label="Tanggal & waktu">
        <Text className="mt-1 text-base font-semibold leading-snug text-[#b45309]">
          {formatEventDateTimeHighlight(event.eventDate)}
        </Text>
      </ContentDetailHighlightBox>

      <Text className="text-sm leading-relaxed text-stone-700">{event.bodyContent}</Text>
    </ContentDetailScreen>
  );
}
