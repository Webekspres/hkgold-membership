import { ScrollView, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';

// import { ActiveRedeemCountdown } from '@/components/card/active-redeem-countdown';
import { MemberQrCard } from '@/components/card/member-qr-card';
import { MemberWalletCard } from '@/components/home/member-wallet-card';
// import { ProfileLastRewardCard } from '@/components/profile/profile-last-reward-card';
import { Text } from '@/components/ui/text';
import { BottomTabInset } from '@/config/theme';
import { useMyProfile } from '@/hooks/use-my-profile';
// import { mapActiveRedeemToReward } from '@/lib/active-redeem/map-active-redeem-reward';
import { copyMemberCode } from '@/lib/clipboard/copy-member-code';
// import { getActiveRedeem } from '@/services/active-redeem';

// const activeRedeem = getActiveRedeem();
// const activeRedeemReward = mapActiveRedeemToReward(activeRedeem);

export default function MemberCardScreen() {
  const { card } = useMyProfile();

  if (!card) {
    return (
      <View className="flex-1 bg-background">
        <SafeAreaView className="flex-1 items-center justify-center">
          <Text variant="muted">Memuat data member...</Text>
        </SafeAreaView>
      </View>
    );
  }

  return (
    <View className="flex-1 bg-background">
      <SafeAreaView className="flex-1" style={{ paddingBottom: 4 }}>
        <ScrollView
          className="flex-1"
          contentContainerClassName="gap-4 px-4 pt-4"
          contentContainerStyle={{ paddingBottom: BottomTabInset + 16 }}
          showsVerticalScrollIndicator={false}>
          <View className="gap-1">
            <Text variant="h3" className="text-stone-900">
              Kartu Member
            </Text>
            <Text variant="muted">Tunjukkan QR ke kasir</Text>
          </View>

          <MemberQrCard
            memberNumber={card.memberNumber}
            onPressMemberNumber={() => void copyMemberCode(card.memberNumber)}
          />

          <MemberWalletCard
            fullName={card.fullName}
            memberNumber={card.memberNumber}
            currentTier={card.currentTier}
            pointBalance={card.pointBalance}
            pressable={false}
            onPressMemberNumber={() => void copyMemberCode(card.memberNumber)}
          />

          {/* TODO: Hadiah sedang diklaim - implementasi nanti */}
          {/* <ProfileLastRewardCard
            title="Hadiah sedang diklaim"
            reward={activeRedeemReward}
            footer={<ActiveRedeemCountdown expiresAt={activeRedeem.expiresAt} />}
            onPress={() => router.push('/card/redeem-qr' as Href)}
          /> */}
        </ScrollView>
      </SafeAreaView>
    </View>
  );
}
