export type MemberTier = 'SILVER' | 'GOLD' | 'PLATINUM' | 'ELITE';

export type TierBenefitRow = {
  label: string;
  value: string;
};

export type TierBenefitSlide = {
  tier: MemberTier;
  title: string;
  subtitle: string;
  accentColors: [string, string];
  iconClassName: string;
  textClassName: string;
  benefits: TierBenefitRow[];
};
