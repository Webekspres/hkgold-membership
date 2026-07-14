import {
  TierWithConversionRules,
  MemberTierData
} from '../types/tier.types';

export interface ITierService {
  getTierLevels(): Promise<TierWithConversionRules[]>;

  getMemberTier(memberId: string): Promise<MemberTierData | null>;
}
