import { router, type Href } from "expo-router";
import {
  BellRing,
  Crown,
  Gift,
  HelpCircle,
  History,
  MapPin,
  Newspaper,
  Settings,
} from "lucide-react-native";
import { ScrollView, View } from "react-native";
import { SafeAreaView } from "react-native-safe-area-context";

import { ProfileLastRewardCard } from "@/components/profile/profile-last-reward-card";
import { ProfileMemberCard } from "@/components/profile/profile-member-card";
import { ProfileMenuList, type ProfileMenuItem } from "@/components/profile/profile-menu-list";
import { ProfilePointsTierCard } from "@/components/profile/profile-points-tier-card";
import { copyMemberCode } from "@/lib/clipboard/copy-member-code";
import { getRewardList } from "@/services/rewards";
import { MOCK_MEMBER } from "@/mocks/mock-member";

const PROFILE_AVATAR_URI = "https://i.pravatar.cc/300?img=68";
const LAST_REDEEMED_REWARD = getRewardList()[0];

const profileMenus: ProfileMenuItem[] = [
  { key: "redeem-history", title: "Riwayat Redeem", icon: History },
  { key: "reward-catalog", title: "Katalog Reward", icon: Gift },
  { key: "tier-benefit", title: "Tier Benefit", icon: Crown },
  { key: "event", title: "Event", icon: BellRing },
  { key: "news", title: "Berita", icon: Newspaper },
  { key: "branch-location", title: "Lokasi Cabang", icon: MapPin },
  { key: "faq", title: "FAQ", icon: HelpCircle },
  { key: "account-settings", title: "Pengaturan Akun", icon: Settings },
];

function formatTierName(tier: string) {
  return tier.charAt(0) + tier.slice(1).toLowerCase();
}

function handlePressProfileMenu(item: ProfileMenuItem) {
  switch (item.key) {
    case "redeem-history":
      router.push("/redeem" as Href);
      return;
    case "reward-catalog":
      router.push("/reward");
      return;
    case "event":
      router.push("/events");
      return;
    case "news":
      router.push("/berita");
      return;
    case "branch-location":
      router.push("/cabang");
      return;
    case "faq":
      router.push("/faq" as Href);
      return;
    case "tier-benefit":
      router.push("/tier-benefit" as Href);
      return;
    default:
      router.push("/cms");
  }
}

export default function ProfileScreen() {
  return (
    <View className="flex-1 bg-background">
      <SafeAreaView className="flex-1" style={{ paddingBottom: 4 }}>
        <ScrollView
          className="flex-1"
          contentContainerClassName="gap-4 px-4 pb-8 pt-4"
          showsVerticalScrollIndicator={false}>
          <ProfileMemberCard
            fullName={MOCK_MEMBER.fullName}
            memberCode={MOCK_MEMBER.memberNumber}
            avatarUri={PROFILE_AVATAR_URI}
            avatarFallback="HK"
            onPressMemberCode={() => void copyMemberCode(MOCK_MEMBER.memberNumber)}
          />

          <ProfilePointsTierCard
            points={MOCK_MEMBER.pointBalance}
            tierName={formatTierName(MOCK_MEMBER.currentTier)}
          />

          <ProfileLastRewardCard reward={LAST_REDEEMED_REWARD} onPress={() => router.push("/cms")} />

          <ProfileMenuList items={profileMenus} onPressItem={handlePressProfileMenu} />
        </ScrollView>
      </SafeAreaView>
    </View>
  );
}
