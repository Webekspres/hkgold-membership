import { Image } from "expo-image";
import { router } from "expo-router";
import { View } from "react-native";

import { GoldButton } from "@/components/gold-button";
import { Text } from "@/components/ui/text";
import type { NewsArticle } from "@/constants/mock-news";
import { CAROUSEL_ITEM_WIDTH } from "@/constants/carousel-layout";

type NewsArticleCardProps = {
  article: NewsArticle;
};

export function NewsArticleCard({ article }: NewsArticleCardProps) {
  return (
    <View
      style={{ width: CAROUSEL_ITEM_WIDTH }}
      className="rounded-xl shadow-md shadow-stone-900/25 border border-stone-100"
    >
      <View className="overflow-hidden rounded-xl bg-white">
        <Image
          source={article.image}
          style={{ width: "100%", aspectRatio: 16 / 9 }}
          contentFit="cover"
          accessibilityLabel={article.title}
        />

        <View className="gap-3 p-4">
          <View className="gap-1">
            <Text
              className="text-base font-semibold leading-snug text-stone-900"
              numberOfLines={2}
            >
              {article.title}
            </Text>
            <Text variant="muted" className="text-xs">
              {article.publishedAtLabel}
            </Text>
          </View>

          <GoldButton
            variant="outline"
            width="full"
            label="Lihat sekarang"
            onPress={() =>
              router.push({
                pathname: "/berita/[slug]",
                params: { slug: article.slug },
              })
            }
          />
        </View>
      </View>
    </View>
  );
}
