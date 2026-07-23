export type MemberTier = 'SILVER' | 'GOLD' | 'PLATINUM' | 'ELITE';

export type TierBenefitRow = {
  label: string;
  value: string;
};

export type TierBenefitSlide = {
  tier: MemberTier;
  title: string;
  /** Syarat poin untuk tier ini, ditampilkan di hero. */
  pointRange: string;
  subtitle: string;
  /** Palet gradient background hero — multi-stop dari brand config. */
  backgroundColors: readonly string[];
  gradientStart: { x: number; y: number };
  gradientEnd: { x: number; y: number };
  iconClassName: string;
  textClassName: string;
  benefits: TierBenefitRow[];
};

export type TierBenefitApiItem = {
  id: string;
  title: string;
  description: string;
  sortOrder: number;
};

export type TierLevelApiItem = {
  id: number;
  tierCode: MemberTier;
  tierName: string;
  minPoints: number;
  maxPoints: number;
  benefits: TierBenefitApiItem[];
};

export type TierLevelsApiResponse = {
  levels: TierLevelApiItem[];
};
