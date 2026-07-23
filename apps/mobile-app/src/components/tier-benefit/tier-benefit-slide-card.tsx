import { Image } from 'expo-image';
import { cssInterop } from 'nativewind';
import { Pressable, View } from 'react-native';

import { Text } from '@/components/ui/text';
import {
  TIER_ICON_SECTION_HEIGHT,
  TIER_PROGRESS_SECTION_HEIGHT,
  TIER_SLIDE_GAP,
  TIER_TEXT_SECTION_HEIGHT,
} from '@/constants/layout/tier-benefit-carousel-layout';
import { getTierIconSource } from '@/config/assets';
import { cn } from '@/lib/utils';
import type { TierBenefitSlide } from '@/types/tier-benefit';

cssInterop(Image, { className: 'style' });

type TierBenefitSlideCardProps = {
  slide: TierBenefitSlide;
  isActive: boolean;
  lightProgressLabels?: boolean;
  onPressProgress?: () => void;
};

export function TierBenefitSlideCard({
  slide,
  isActive,
  lightProgressLabels = false,
  onPressProgress,
}: TierBenefitSlideCardProps) {
  const isLightText =
    slide.tier === 'GOLD' ||
    slide.tier === 'PLATINUM' ||
    slide.tier === 'ELITE';

  const lineColor = lightProgressLabels
    ? 'rgba(68,64,60,0.25)'
    : 'rgba(255,255,255,0.35)';

  return (
    <View className="flex-1" style={{ opacity: isActive ? 1 : 0.45 }}>
      <View
        className="items-center justify-center px-4"
        style={{ height: TIER_ICON_SECTION_HEIGHT }}
      >
        <View
          className={cn(
            'items-center justify-center rounded-full bg-white/20 p-5',
            isActive ? 'scale-100' : 'scale-90',
          )}
        >
          <Image
            source={getTierIconSource(slide.tier)}
            className={cn(isActive ? 'h-24 w-24' : 'h-16 w-16')}
            contentFit="contain"
            accessibilityLabel={`Tier ${slide.title}`}
          />
        </View>
      </View>

      <View
        className="items-center px-4"
        style={{ height: TIER_TEXT_SECTION_HEIGHT }}
      >
        {isActive ? (
          <>
            <Text
              className={cn(
                'text-3xl font-bold',
                isLightText ? 'text-white' : slide.textClassName,
              )}
            >
              {slide.title}
            </Text>
            <Text
              className={cn(
                'mt-2 text-center text-sm leading-5',
                isLightText ? 'text-white/85' : 'text-stone-700',
              )}
            >
              {slide.subtitle}
            </Text>
          </>
        ) : (
          <Text
            className={cn(
              'mt-2 text-base font-semibold',
              isLightText ? 'text-white/70' : 'text-stone-600',
            )}
          >
            {slide.title}
          </Text>
        )}
      </View>

      <View
        className="items-center justify-center"
        style={{ height: TIER_PROGRESS_SECTION_HEIGHT }}
      >
        <View
          className="absolute h-px"
          style={{
            left: -(TIER_SLIDE_GAP / 2),
            right: -(TIER_SLIDE_GAP / 2),
            top: '50%',
            marginTop: -0.5,
            backgroundColor: lineColor,
          }}
        />

        <Pressable
          className="items-center gap-1"
          onPress={onPressProgress}
          accessibilityRole="button"
          accessibilityLabel={`Tier ${slide.title}`}
          accessibilityState={{ selected: isActive }}
        >
          <View
            className={cn(
              'rounded-full border-2',
              lightProgressLabels
                ? 'border-stone-400/60 bg-stone-300/40'
                : 'border-white/50 bg-white/25',
              isActive
                ? lightProgressLabels
                  ? 'size-3.5 bg-stone-700'
                  : 'size-3.5 bg-white'
                : 'size-2.5',
            )}
          />
          <Text
            className={cn(
              'text-[10px] font-medium',
              lightProgressLabels
                ? isActive
                  ? 'text-stone-800'
                  : 'text-stone-500'
                : isActive
                  ? 'text-white'
                  : 'text-white/60',
            )}
          >
            {slide.title}
          </Text>
        </Pressable>
      </View>
    </View>
  );
}
