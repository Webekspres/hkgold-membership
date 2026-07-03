import { router } from "expo-router";
import { ScrollView, View } from "react-native";
import { SafeAreaView } from "react-native-safe-area-context";

import { ActiveRedeemCountdown } from "@/components/card/active-redeem-countdown";
import { MemberQrCard } from "@/components/card/member-qr-card";
import { MemberWalletCard } from "@/components/home/member-wallet-card";
import { ProfileLastRewardCard } from "@/components/profile/profile-last-reward-card";
import { Text } from "@/components/ui/text";
import { copyMemberCode } from "@/lib/clipboard/copy-member-code";
import { MOCK_MEMBER } from "@/mocks/mock-member";
import { getActiveRedeem } from "@/services/active-redeem";
import type { RewardCatalogItem } from "@/types/reward";

const activeRedeem = getActiveRedeem();

const activeRedeemReward: RewardCatalogItem = {
  id: activeRedeem.redeemId,
  sku: activeRedeem.sku,
  name: activeRedeem.name,
  categoryId: 0,
  categoryName: activeRedeem.categoryName,
  categorySlug: "",
  pointsRequired: activeRedeem.pointsRequired,
  stockRemaining: 0,
  image: activeRedeem.image,
};

export default function MemberCardScreen() {
  return (
    <View className="flex-1 bg-background">
      <SafeAreaView className="flex-1" style={{ paddingBottom: 4 }}>
        <ScrollView
          className="flex-1"
          contentContainerClassName="gap-4 px-4 pb-8 pt-4"
          contentContainerStyle={{ paddingBottom: 16 }}
          showsVerticalScrollIndicator={false}
        >
          <View className="gap-1">
            <Text variant="h3" className="text-stone-900">
              Kartu Member
            </Text>
            <Text variant="muted">Tunjukkan QR ke kasir</Text>
          </View>

          <MemberQrCard memberNumber={MOCK_MEMBER.memberNumber} />

          <MemberWalletCard
            {...MOCK_MEMBER}
            pressable={false}
            onPressMemberNumber={() =>
              void copyMemberCode(MOCK_MEMBER.memberNumber)
            }
          />

          <ProfileLastRewardCard
            title="Reward sedang di-redeem"
            reward={activeRedeemReward}
            footer={
              <ActiveRedeemCountdown expiresAt={activeRedeem.expiresAt} />
            }
            onPress={() =>
              router.push({
                pathname: "/redeem/[id]",
                params: { id: activeRedeem.redeemId },
              })
            }
          />
        </ScrollView>
      </SafeAreaView>
    </View>
  );
}
