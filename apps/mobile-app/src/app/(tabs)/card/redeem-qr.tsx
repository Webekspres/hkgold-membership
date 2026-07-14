import { router } from 'expo-router';
import { ScrollView, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';

import { ActiveRedeemCountdown } from '@/components/card/active-redeem-countdown';
import { QrCodeCard } from '@/components/card/qr-code-card';
import { ProfileLastRewardCard } from '@/components/profile/profile-last-reward-card';
import { GoldButton } from '@/components/shared/gold-button';
import { Text } from '@/components/ui/text';
import { BottomTabInset } from '@/config/theme';
import { mapActiveRedeemToReward } from '@/lib/active-redeem/map-active-redeem-reward';
import { copyRedeemToken } from '@/lib/clipboard/copy-redeem-token';
import { getActiveRedeem } from '@/services/active-redeem';

const activeRedeem = getActiveRedeem();
const activeRedeemReward = mapActiveRedeemToReward(activeRedeem);

export default function RedeemQrScreen() {
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
              Klaim Reward
            </Text>
            <Text variant="muted">Tunjukkan QR ke kasir</Text>
          </View>

          <QrCodeCard
            value={activeRedeem.redeemToken}
            label={activeRedeem.redeemToken}
            onPressLabel={() => void copyRedeemToken(activeRedeem.redeemToken)}
            copyAccessibilityLabel="Salin token redeem"
          />

          <ProfileLastRewardCard
            title="Hadiah sedang diklaim"
            reward={activeRedeemReward}
            pressable={false}
            footer={<ActiveRedeemCountdown expiresAt={activeRedeem.expiresAt} />}
          />

          <GoldButton
            label="Kembali ke Kartu Member"
            width="full"
            variant="outline"
            onPress={() => router.replace('/card')}
          />
        </ScrollView>
      </SafeAreaView>
    </View>
  );
}
