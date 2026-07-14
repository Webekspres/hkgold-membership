import { useLocalSearchParams } from 'expo-router';
import { useCallback } from 'react';
import { ActivityIndicator, Pressable, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';

import { ContentDetailScreen } from '@/components/shared/content-detail-screen';
import { createPullToRefreshControl } from '@/components/shared/pull-to-refresh';
import { Text } from '@/components/ui/text';
import { useNewsDetail } from '@/hooks/use-news-detail';
import { usePullToRefresh } from '@/hooks/use-pull-to-refresh';

export default function NewsDetailScreen() {
  const { id } = useLocalSearchParams<{ id: string }>();
  const { article, isLoading, isError, refetch } = useNewsDetail(
    typeof id === 'string' ? id : undefined,
  );

  const refresh = useCallback(() => refetch(), [refetch]);
  const { refreshing, onRefresh } = usePullToRefresh(refresh);

  if (isLoading) {
    return (
      <View className="flex-1 items-center justify-center bg-background">
        <ActivityIndicator color="#b45309" />
      </View>
    );
  }

  if (isError || !article) {
    return (
      <SafeAreaView className="flex-1 items-center justify-center gap-3 bg-background px-6">
        <Text className="text-center text-base font-semibold text-stone-900">
          Berita tidak ditemukan
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
    <ContentDetailScreen
      images={article.imageUrls}
      title={article.title}
      refreshControl={createPullToRefreshControl({
        refreshing,
        onRefresh,
      })}>
      <View className="gap-2">
        <Text className="text-2xl font-semibold leading-snug text-stone-900">
          {article.title}
        </Text>
        <Text variant="muted" className="text-sm">
          {article.publishedAtLabel}
        </Text>
      </View>

      <Text className="text-sm leading-relaxed text-stone-700">{article.bodyContent}</Text>
    </ContentDetailScreen>
  );
}
