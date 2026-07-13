import { ScrollView, View } from "react-native";
import { SafeAreaView } from "react-native-safe-area-context";

import { MemberWalletCard } from "@/components/home/member-wallet-card";
import { HomeShortcutGrid } from "@/components/home/home-shortcut-grid";
import { LatestNewsSection } from "@/components/home/latest-news-section";
import { NearestBranchSection } from "@/components/home/nearest-branch-section";
import { PromotionBannerSlider } from "@/components/home/promotion-banner-slider";
import { UpcomingEventsSection } from "@/components/home/upcoming-events-section";
import { RewardCatalogSection } from "@/components/home/reward-catalog-section";
import { Text } from "@/components/ui/text";
import { useLatestNews } from "@/hooks/use-latest-news";
import { useMyProfile } from "@/hooks/use-my-profile";
import { usePromotionBanners } from "@/hooks/use-promotion-banners";
import { useUpcomingEvents } from "@/hooks/use-upcoming-events";
import { getNearestBranch } from "@/services/branches";
import { getRewardCatalog } from "@/services/rewards";

const nearestBranch = getNearestBranch();
const rewardCatalog = getRewardCatalog();

export default function HomeScreen() {
  const { card } = useMyProfile();
  const { articles: latestNews, isError: latestNewsError } = useLatestNews();
  const { events: upcomingEvents, isError: upcomingEventsError } = useUpcomingEvents();
  const { banners: promotionBanners } = usePromotionBanners();

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
              Halo, {card?.firstName ?? "Member"}
            </Text>
            <Text variant="muted">Selamat datang di HK Gold VIP</Text>
          </View>

          {card ? (
            <View className="px-4">
              <MemberWalletCard
                fullName={card.fullName}
                memberNumber={card.memberNumber}
                currentTier={card.currentTier}
                pointBalance={card.pointBalance}
              />
            </View>
          ) : null}

          <HomeShortcutGrid />

          <NearestBranchSection branch={nearestBranch} />

          {promotionBanners.length > 0 ? (
            <PromotionBannerSlider banners={promotionBanners} />
          ) : null}

          <UpcomingEventsSection events={upcomingEvents} isError={upcomingEventsError} />

          <LatestNewsSection articles={latestNews} isError={latestNewsError} />

          <RewardCatalogSection categories={rewardCatalog} />
        </ScrollView>
      </SafeAreaView>
    </View>
  );
}
