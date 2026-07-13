import { MemberTier } from '@prisma/client';

export interface TierLevelData {
  id: number;
  tierCode: MemberTier;
  tierName: string;
  minPoints: number;
  maxPoints: number;
  color?: string;
  icon?: string;
}

export interface ConversionRuleData {
  id: string;
  transactionTypeId: number;
  transactionTypeKey: string;
  transactionTypeName: string;
  tierMemberId: number;
  conversionNominal: number;
}

export interface TierWithConversionRules extends TierLevelData {
  conversionRules: ConversionRuleData[];
}

export interface MemberTierData {
  id: number;
  tierCode: MemberTier;
  tierName: string;
  minPoints: number;
  maxPoints: number;
  currentPoints: number;
  conversionRules: ConversionRuleData[];
}

export interface GetTierLevelsResponse {
  levels: TierLevelData[];
}

export interface GetMemberTierResponse {
  data: MemberTierData;
}
