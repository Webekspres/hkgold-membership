import { Image } from "expo-image";
import { router } from "expo-router";
import { View } from "react-native";

import { GoldButton } from "@/components/shared/gold-button";
import { Text } from "@/components/ui/text";
import type { NewsArticle } from "@/types/news";
import { CAROUSEL_ITEM_WIDTH } from "@/constants/layout/carousel-layout";
import { cn } from "@/lib/utils";

const PLACEHOLDER_IMAGE = require("@/assets/mockImage/mock-image-news.webp");

type NewsArticleCardProps = {
  article: NewsArticle;
  fullWidth?: boolean;
};

export function NewsArticleCard({
  article,
  fullWidth = false,
}: NewsArticleCardProps) {
  return (
    <View
      style={fullWidth ? undefined : { width: CAROUSEL_ITEM_WIDTH }}
      className={cn(
        "rounded-xl border border-stone-200 shadow-md shadow-stone-900/15",
        fullWidth && "w-full",
      )}
    >
      <View className="overflow-hidden rounded-xl bg-white">
        <Image
          source={
            article.imageUrl ? { uri: article.imageUrl } : PLACEHOLDER_IMAGE
          }
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
                pathname: "/berita/[id]",
                params: { id: article.id },
              })
            }
          />
        </View>
      </View>
    </View>
  );
}
