import { SymbolView } from 'expo-symbols';
import { router } from 'expo-router';
import { useState } from 'react';
import { ScrollView, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';

import { TierBenefitCarousel } from '@/components/tier-benefit/tier-benefit-carousel';
import { TierBenefitTable } from '@/components/tier-benefit/tier-benefit-table';
import { Button } from '@/components/ui/button';
import { Text } from '@/components/ui/text';
import { SCREEN_HORIZONTAL_PADDING } from '@/constants/layout/screen-layout';
import { MOCK_MEMBER } from '@/mocks/mock-member';
import { getTierBenefitInitialIndex, getTierBenefitSlides } from '@/services/tier-benefits';

const BACK_ICON = { ios: 'chevron.left', android: 'arrow_back', web: 'arrow_back' } as const;

const tierBenefitSlides = getTierBenefitSlides();
const initialTierIndex = getTierBenefitInitialIndex(MOCK_MEMBER.currentTier);

export default function TierBenefitScreen() {
  const [activeIndex, setActiveIndex] = useState(initialTierIndex);
  const activeSlide = tierBenefitSlides[activeIndex] ?? tierBenefitSlides[0];

  return (
    <View className="flex-1 bg-background">
      <SafeAreaView className="flex-1" edges={['top']}>
        <View
          className="flex-row items-center gap-3 border-b border-stone-200 bg-background py-3"
          style={{ paddingHorizontal: SCREEN_HORIZONTAL_PADDING }}>
          <Button variant="outline" size="icon" onPress={() => router.back()}>
            <SymbolView name={BACK_ICON} size={20} tintColor="#44403c" />
          </Button>
          <Text className="text-lg font-semibold text-stone-900">Benefit Tier</Text>
        </View>

        <ScrollView
          showsVerticalScrollIndicator={false}
          contentContainerStyle={{
            paddingVertical: 16,
            paddingBottom: 24,
            gap: 16,
          }}>
          <TierBenefitCarousel
            slides={tierBenefitSlides}
            initialIndex={initialTierIndex}
            onIndexChange={setActiveIndex}
          />

          <View style={{ paddingHorizontal: SCREEN_HORIZONTAL_PADDING }}>
            <TierBenefitTable benefits={activeSlide.benefits} slideKey={activeSlide.tier} />
          </View>
        </ScrollView>
      </SafeAreaView>
    </View>
  );
}
