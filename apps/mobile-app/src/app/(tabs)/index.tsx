import { useCallback, useState } from "react";
import { ScrollView, View } from "react-native";
import { SafeAreaView } from "react-native-safe-area-context";

import { DevTierSwitcher } from "@/components/dev/dev-tier-switcher";
import {
  HomeHeroHeader,
  HOME_HERO_CARD_OVERLAP,
} from "@/components/home/home-hero-header";
import { MemberWalletCard } from "@/components/home/member-wallet-card";
import { HomeShortcutGrid } from "@/components/home/home-shortcut-grid";
import { LatestNewsSection } from "@/components/home/latest-news-section";
import { NearestBranchSection } from "@/components/home/nearest-branch-section";
import { PromotionBannerSlider } from "@/components/home/promotion-banner-slider";
import { UpcomingEventsSection } from "@/components/home/upcoming-events-section";
import { RewardCatalogSection } from "@/components/home/reward-catalog-section";
import { createPullToRefreshControl } from "@/components/shared/pull-to-refresh";
import { useHomeRewardCatalog } from "@/hooks/use-home-reward-catalog";
import { useLatestNews } from "@/hooks/use-latest-news";
import { useMyProfile } from "@/hooks/use-my-profile";
import { usePromotionBanners } from "@/hooks/use-promotion-banners";
import { usePullToRefresh } from "@/hooks/use-pull-to-refresh";
import { useUpcomingEvents } from "@/hooks/use-upcoming-events";
import { getNearestBranch } from "@/services/branches";
import type { MemberTier } from "@/types/auth";

const nearestBranch = getNearestBranch();

export default function HomeScreen() {
  const { card, refetch: refetchProfile } = useMyProfile();
  const [devTier, setDevTier] = useState<MemberTier | null>(null);
  const {
    articles: latestNews,
    isError: latestNewsError,
    refetch: refetchNews,
  } = useLatestNews();
  const {
    events: upcomingEvents,
    isError: upcomingEventsError,
    refetch: refetchEvents,
  } = useUpcomingEvents();
  const { banners: promotionBanners, refetch: refetchBanners } =
    usePromotionBanners();
  const {
    categories: rewardCatalog,
    isError: rewardCatalogError,
    refetch: refetchRewards,
  } = useHomeRewardCatalog();

  const refresh = useCallback(
    () =>
      Promise.all([
        refetchProfile(),
        refetchNews(),
        refetchEvents(),
        refetchBanners(),
        refetchRewards(),
      ]),
    [refetchProfile, refetchNews, refetchEvents, refetchBanners, refetchRewards],
  );
  const { refreshing, onRefresh } = usePullToRefresh(refresh);

  const previewTier =
    __DEV__ && devTier
      ? devTier
      : ((card?.currentTier ?? "SILVER") as MemberTier);

  return (
    <View className="flex-1 bg-background">
      <SafeAreaView className="flex-1" edges={["left", "right", "bottom"]} style={{ paddingBottom: 4 }}>
        <ScrollView
          className="flex-1"
          contentContainerClassName="gap-6 pb-6"
          showsVerticalScrollIndicator={false}
          refreshControl={createPullToRefreshControl({
            refreshing,
            onRefresh,
          })}
        >
          <View>
            <HomeHeroHeader firstName={card?.firstName ?? "Member"} />

            {card ? (
              <View
                className="px-4"
                style={{ marginTop: -HOME_HERO_CARD_OVERLAP }}
              >
                <MemberWalletCard
                  fullName={card.fullName}
                  memberNumber={card.memberNumber}
                  currentTier={previewTier}
                  pointBalance={card.pointBalance}
                />
                {__DEV__ ? (
                  <DevTierSwitcher
                    selected={previewTier}
                    onSelect={setDevTier}
                  />
                ) : null}
              </View>
            ) : null}
          </View>

          <HomeShortcutGrid />

          <NearestBranchSection branch={nearestBranch} />

          {promotionBanners.length > 0 ? (
            <PromotionBannerSlider banners={promotionBanners} />
          ) : null}

          <UpcomingEventsSection events={upcomingEvents} isError={upcomingEventsError} />

          <LatestNewsSection articles={latestNews} isError={latestNewsError} />

          <RewardCatalogSection
            categories={rewardCatalog}
            isError={rewardCatalogError}
          />
        </ScrollView>
      </SafeAreaView>
    </View>
  );
}
