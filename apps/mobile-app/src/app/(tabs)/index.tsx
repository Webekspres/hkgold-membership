import { router } from "expo-router";
import { ScrollView, View } from "react-native";
import { SafeAreaView } from "react-native-safe-area-context";

import { MemberWalletCard } from "@/components/home/member-wallet-card";
import { HomeShortcutGrid } from "@/components/home/home-shortcut-grid";
import { LatestNewsSection } from "@/components/home/latest-news-section";
import { NearestBranchSection } from "@/components/home/nearest-branch-section";
import { PromotionBannerSlider } from "@/components/home/promotion-banner-slider";
import { UpcomingEventsSection } from "@/components/home/upcoming-events-section";
import { RewardCatalogSection } from "@/components/home/reward-catalog-section";
import { Button } from "@/components/ui/button";
import { Text } from "@/components/ui/text";
import { MOCK_PROMOTION_BANNERS } from "@/mocks/mock-banners";
import { getNearestBranch } from "@/services/branches";
import { getUpcomingEvents } from "@/services/events";
import { getLatestNews } from "@/services/news";
import { getRewardCatalog } from "@/services/rewards";
import { BottomTabInset } from "@/config/theme";
import { MOCK_MEMBER } from "@/mocks/mock-member";

const nearestBranch = getNearestBranch();
const upcomingEvents = getUpcomingEvents();
const latestNews = getLatestNews();
const rewardCatalog = getRewardCatalog();

export default function HomeScreen() {
  return (
    <View className="flex-1 bg-background">
      <SafeAreaView className="flex-1" style={{ paddingBottom: 4 }}>
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

          <NearestBranchSection branch={nearestBranch} />

          <PromotionBannerSlider banners={MOCK_PROMOTION_BANNERS} />

          <UpcomingEventsSection events={upcomingEvents} />

          <LatestNewsSection articles={latestNews} />

          <RewardCatalogSection categories={rewardCatalog} />
        </ScrollView>
      </SafeAreaView>
    </View>
  );
}
