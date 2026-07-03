import { LinearGradient } from 'expo-linear-gradient';
import { Image } from 'expo-image';
import { Crown } from 'lucide-react-native';
import { View } from 'react-native';

import { Icon } from '@/components/ui/icon';
import { Text } from '@/components/ui/text';
import { GOLD_GRADIENT_END, GOLD_GRADIENT_START } from '@/config/brand';
import { cn } from '@/lib/utils';
import type { TierBenefitSlide } from '@/types/tier-benefit';

type TierBenefitSlideCardProps = {
  slide: TierBenefitSlide;
};

export function TierBenefitSlideCard({ slide }: TierBenefitSlideCardProps) {
  const isLightText = slide.tier === 'SAPPHIRE';

  return (
    <LinearGradient
      colors={slide.accentColors}
      start={GOLD_GRADIENT_START}
      end={GOLD_GRADIENT_END}
      style={{ flex: 1, borderRadius: 20, padding: 2 }}>
      <View className="flex-1 overflow-hidden rounded-[18px] bg-white/10">
        <Image
          source={require('@/assets/media/background.webp')}
          style={{
            position: 'absolute',
            inset: 0,
            opacity: slide.tier === 'SILVER' ? 0.08 : 0.12,
            borderRadius: 18,
          }}
          contentFit="cover"
        />
        <View className="flex-1 items-center justify-center px-6">
          <View className="items-center justify-center rounded-full bg-white/25 p-5">
            <Icon as={Crown} size={48} className={cn(slide.iconClassName)} />
          </View>
          <Text
            className={cn(
              'mt-4 text-3xl font-bold',
              isLightText ? 'text-white' : slide.textClassName,
            )}>
            {slide.title}
          </Text>
          <Text
            className={cn(
              'mt-2 text-center text-sm',
              isLightText ? 'text-indigo-100' : 'text-stone-600',
            )}>
            {slide.subtitle}
          </Text>
        </View>
      </View>
    </LinearGradient>
  );
}
