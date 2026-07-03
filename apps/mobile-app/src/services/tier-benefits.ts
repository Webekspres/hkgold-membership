import { MOCK_TIER_BENEFIT_SLIDES } from '@/mocks/mock-tier-benefits';
import type { MemberTier } from '@/types/tier-benefit';

export function getTierBenefitSlides() {
  return MOCK_TIER_BENEFIT_SLIDES;
}

export function getTierBenefitInitialIndex(currentTier: MemberTier) {
  const index = MOCK_TIER_BENEFIT_SLIDES.findIndex((slide) => slide.tier === currentTier);
  return index >= 0 ? index : 0;
}
