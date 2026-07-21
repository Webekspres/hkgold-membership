import { LinearGradient } from 'expo-linear-gradient';
import { SymbolView } from 'expo-symbols';
import { cssInterop } from 'nativewind';
import { router } from 'expo-router';
import { useMemo, useState } from 'react';
import { Pressable, ScrollView, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';

import { TierBenefitCarousel } from '@/components/tier-benefit/tier-benefit-carousel';
import { TierBenefitTable } from '@/components/tier-benefit/tier-benefit-table';
import { Text } from '@/components/ui/text';
import { SCREEN_HORIZONTAL_PADDING } from '@/constants/layout/screen-layout';
import { MOCK_MEMBER } from '@/mocks/mock-member';
import { getTierBenefitInitialIndex, getTierBenefitSlides } from '@/services/tier-benefits';

const BACK_ICON = { ios: 'chevron.left', android: 'arrow_back', web: 'arrow_back' } as const;

cssInterop(LinearGradient, { className: 'style' });

const tierBenefitSlides = getTierBenefitSlides();
const initialTierIndex = getTierBenefitInitialIndex(MOCK_MEMBER.currentTier);

function buildHeroBackgroundColors(colors: readonly string[]): string[] {
  const mid = colors[Math.floor(colors.length / 2)] ?? colors[0];
  return [...colors.slice(0, 3), mid, '#FFFFFF'];
}

export default function TierBenefitScreen() {
  const [activeIndex, setActiveIndex] = useState(initialTierIndex);
  const activeSlide = tierBenefitSlides[activeIndex] ?? tierBenefitSlides[0];

  const heroBackgroundColors = useMemo(
    () => buildHeroBackgroundColors(activeSlide.backgroundColors),
    [activeSlide.backgroundColors],
  );

  return (
    <View className="flex-1 bg-white">
      <LinearGradient
        colors={heroBackgroundColors as [string, string, ...string[]]}
        start={activeSlide.gradientStart}
        end={{ x: activeSlide.gradientEnd.x, y: 1 }}
        locations={[0, 0.25, 0.5, 0.72, 1]}
        className="absolute inset-x-0 top-0"
        style={{ height: 520 }}
      />

      <SafeAreaView className="flex-1" edges={['top']}>
        <View className="relative flex-row items-center justify-center px-4 py-3">
          <Pressable
            className="absolute left-4 size-10 items-center justify-center rounded-full active:opacity-70"
            onPress={() => router.back()}
            accessibilityRole="button"
            accessibilityLabel="Kembali"
          >
            <SymbolView
              name={BACK_ICON}
              size={22}
              tintColor={activeSlide.tier === 'SILVER' ? '#44403c' : '#FFFFFF'}
            />
          </Pressable>
          <Text
            className={`text-lg font-semibold ${
              activeSlide.tier === 'SILVER' ? 'text-stone-800' : 'text-white'
            }`}
          >
            Keuntungan Tier
          </Text>
        </View>

        <ScrollView
          showsVerticalScrollIndicator={false}
          contentContainerStyle={{
            paddingBottom: 32,
          }}
        >
          <TierBenefitCarousel
            slides={tierBenefitSlides}
            initialIndex={initialTierIndex}
            onIndexChange={setActiveIndex}
            lightProgressLabels={activeSlide.tier === 'SILVER'}
          />

          <View
            style={{
              paddingHorizontal: SCREEN_HORIZONTAL_PADDING,
              paddingTop: 8,
            }}
          >
            <TierBenefitTable
              benefits={activeSlide.benefits}
              slideKey={activeSlide.tier}
              tierTitle={activeSlide.title}
            />
          </View>
        </ScrollView>
      </SafeAreaView>
    </View>
  );
}
