import { router } from 'expo-router';
import { FlatList, Pressable, View, type ListRenderItem } from 'react-native';
import { useCallback } from 'react';

import { NewsArticleCard } from '@/components/news-article-card';
import { Text } from '@/components/ui/text';
import {
  CAROUSEL_ITEM_GAP,
  CAROUSEL_LEFT_PADDING,
  CAROUSEL_PEEK,
  CAROUSEL_SNAP_INTERVAL,
} from '@/constants/carousel-layout';
import type { NewsArticle } from '@/constants/mock-news';
import { cn } from '@/lib/utils';

type LatestNewsSectionProps = {
  articles: NewsArticle[];
  className?: string;
};

export function LatestNewsSection({ articles, className }: LatestNewsSectionProps) {
  const renderItem: ListRenderItem<NewsArticle> = useCallback(
    ({ item }) => (
      <View style={{ marginRight: CAROUSEL_ITEM_GAP }}>
        <NewsArticleCard article={item} />
      </View>
    ),
    []
  );

  if (articles.length === 0) {
    return null;
  }

  return (
    <View className={cn('gap-3', className)}>
      <View className="flex-row items-center justify-between px-4">
        <Text className="text-base font-semibold text-stone-900">Berita Terbaru</Text>
        <Pressable onPress={() => router.push('/cms')} className="active:opacity-70">
          <Text className="text-sm font-medium text-[#c4841a]">Lihat semua</Text>
        </Pressable>
      </View>

      <FlatList
        data={articles}
        keyExtractor={(item) => item.id}
        renderItem={renderItem}
        horizontal
        nestedScrollEnabled
        showsHorizontalScrollIndicator={false}
        decelerationRate="fast"
        snapToInterval={CAROUSEL_SNAP_INTERVAL}
        snapToAlignment="start"
        disableIntervalMomentum
        bounces={false}
        contentContainerStyle={{
          paddingLeft: CAROUSEL_LEFT_PADDING,
          paddingRight: CAROUSEL_LEFT_PADDING - CAROUSEL_PEEK,
        }}
      />
    </View>
  );
}
