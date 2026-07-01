import { router } from "expo-router";
import { ScrollView, View } from "react-native";
import { SafeAreaView } from "react-native-safe-area-context";

import { MemberWalletCard } from "@/components/member-wallet-card";
import { HomeShortcutGrid } from "@/components/home-shortcut-grid";
import { LatestNewsSection } from "@/components/latest-news-section";
import { PromotionBannerSlider } from "@/components/promotion-banner-slider";
import { UpcomingEventsSection } from "@/components/upcoming-events-section";
import { RewardCatalogSection } from "@/components/reward-catalog-section";
import { Button } from "@/components/ui/button";
import { Text } from "@/components/ui/text";
import { MOCK_PROMOTION_BANNERS } from "@/constants/mock-banners";
import { MOCK_UPCOMING_EVENTS } from "@/constants/mock-events";
import { MOCK_LATEST_NEWS } from "@/constants/mock-news";
import { MOCK_REWARD_CATALOG } from "@/constants/mock-rewards";
import { BottomTabInset } from "@/constants/theme";
import { MOCK_MEMBER } from "@/constants/mock-member";

export default function HomeScreen() {
  return (
    <View className="flex-1 bg-background">
      <SafeAreaView className="flex-1" style={{ paddingBottom: 16 }}>
        <ScrollView
          className="flex-1"
          contentContainerClassName="gap-6 pb-6 pt-4"
          showsVerticalScrollIndicator={false}
        >
          <View className="gap-1 px-4">
            <Text variant="h3" className="text-stone-900">
              Halo, {MOCK_MEMBER.fullName.split(" ")[0]}
            </Text>
            <Text variant="muted">Selamat datang di HK Gold VIP</Text>
          </View>

          <View className="px-4">
            <MemberWalletCard {...MOCK_MEMBER} />
          </View>

          <HomeShortcutGrid />
          <PromotionBannerSlider banners={MOCK_PROMOTION_BANNERS} />

          <UpcomingEventsSection events={MOCK_UPCOMING_EVENTS} />

          <LatestNewsSection articles={MOCK_LATEST_NEWS} />

          <View className="px-4">
            <Button
              variant="outline"
              className="self-stretch"
              onPress={() => router.push("/login")}
            >
              <Text>Masuk</Text>
            </Button>
          </View>

          <RewardCatalogSection categories={MOCK_REWARD_CATALOG} />
        </ScrollView>
      </SafeAreaView>
    </View>
  );
}
