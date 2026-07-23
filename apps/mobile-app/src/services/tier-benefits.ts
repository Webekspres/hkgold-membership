import { TIER_GRADIENTS } from '@/config/brand';
import { apiClient } from '@/lib/api-client';
import { asMemberTier } from '@/services/member';
import type { ApiEnvelope } from '@/types/auth';
import type {
  MemberTier,
  TierBenefitSlide,
  TierLevelsApiResponse,
} from '@/types/tier-benefit';

const TIER_UI: Record<
  MemberTier,
  Pick<TierBenefitSlide, 'iconClassName' | 'textClassName'>
> = {
  SILVER: {
    iconClassName: 'text-stone-500',
    textClassName: 'text-stone-800',
  },
  GOLD: {
    iconClassName: 'text-amber-600',
    textClassName: 'text-white',
  },
  PLATINUM: {
    iconClassName: 'text-slate-300',
    textClassName: 'text-white',
  },
  ELITE: {
    iconClassName: 'text-indigo-200',
    textClassName: 'text-white',
  },
};

function formatPointRange(minPoints: number, maxPoints: number): string {
  if (maxPoints >= 99999) {
    return `${minPoints}+`;
  }
  return `${minPoints} - ${maxPoints}`;
}

function buildSubtitle(minPoints: number, maxPoints: number): string {
  const range = formatPointRange(minPoints, maxPoints);
  if (maxPoints >= 99999) {
    return `Kumpulkan ${range} Poin setiap bulan untuk tier tertinggi`;
  }
  return `Kumpulkan ${range} Poin setiap bulan untuk naik tier`;
}

function mapTierLevelToSlide(level: TierLevelsApiResponse['levels'][number]): TierBenefitSlide {
  const tier = asMemberTier(level.tierCode) as MemberTier;
  const gradient = TIER_GRADIENTS[tier];
  const ui = TIER_UI[tier];
  const pointRange = formatPointRange(level.minPoints, level.maxPoints);

  return {
    tier,
    title: level.tierName,
    pointRange,
    subtitle: buildSubtitle(level.minPoints, level.maxPoints),
    backgroundColors: gradient.colors,
    gradientStart: gradient.start,
    gradientEnd: gradient.end,
    iconClassName: ui.iconClassName,
    textClassName: ui.textClassName,
    benefits: level.benefits.map((benefit) => ({
      label: benefit.title,
      value: benefit.description,
    })),
  };
}

export async function fetchTierBenefitSlides(): Promise<TierBenefitSlide[]> {
  const { data } = await apiClient.get<ApiEnvelope<TierLevelsApiResponse>>('/api/tier/levels');

  if (!data.success || !data.data?.levels?.length) {
    throw new Error(data.message || 'Gagal mengambil keuntungan tier');
  }

  return data.data.levels.map(mapTierLevelToSlide);
}

export function getTierBenefitInitialIndex(
  slides: TierBenefitSlide[],
  currentTier: MemberTier,
): number {
  const index = slides.findIndex((slide) => slide.tier === currentTier);
  return index >= 0 ? index : 0;
}
