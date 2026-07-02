import { useLocalSearchParams } from 'expo-router';
import { useMemo } from 'react';
import { View } from 'react-native';

import { ComingSoonScreen } from '@/components/shared/coming-soon-screen';
import { ContentDetailScreen } from '@/components/shared/content-detail-screen';
import { Text } from '@/components/ui/text';
import { getNewsBySlug } from '@/services/news';

export default function NewsDetailScreen() {
  const { slug } = useLocalSearchParams<{ slug: string }>();

  const article = useMemo(() => {
    if (typeof slug !== 'string') {
      return null;
    }

    return getNewsBySlug(slug);
  }, [slug]);

  if (!article) {
    return <ComingSoonScreen title="Detail Berita" />;
  }

  return (
    <ContentDetailScreen images={article.images} title={article.title}>
      <View className="gap-2">
        <Text variant="muted" className="text-xs uppercase tracking-wide">
          {article.categoryName}
        </Text>
        <Text className="text-2xl font-semibold leading-snug text-stone-900">
          {article.title}
        </Text>
        <Text variant="muted" className="text-sm">
          {article.publishedAtLabel}
        </Text>
      </View>

      <Text className="text-sm leading-relaxed text-stone-700">{article.description}</Text>
    </ContentDetailScreen>
  );
}
