import { prisma } from '../../../db';
import { ITierService } from '../interfaces/tier.interface';
import {
  TierWithConversionRules,
  MemberTierData,
  TierLevelData,
  ConversionRuleData
} from '../types/tier.types';

const TIER_NAMES: Record<string, string> = {
  SILVER: 'Silver',
  GOLD: 'Gold',
  PLATINUM: 'Platinum',
  SAPPHIRE: 'Sapphire'
};

const TIER_COLORS: Record<string, string> = {
  SILVER: '#a8a29e',
  GOLD: '#f5c842',
  PLATINUM: '#64748b',
  SAPPHIRE: '#4338ca'
};

export class TierService implements ITierService {
  private mapTierLevel(tier: any): TierLevelData {
    return {
      id: tier.id,
      tierCode: tier.tierCode,
      tierName: TIER_NAMES[tier.tierCode] || tier.tierCode,
      minPoints: tier.minPoints,
      maxPoints: tier.maxPoints,
      color: TIER_COLORS[tier.tierCode],
      icon: tier.tierCode.toLowerCase()
    };
  }

  private mapConversionRule(rule: any): ConversionRuleData {
    return {
      id: rule.id,
      transactionTypeId: rule.transactionTypeId,
      transactionTypeKey: rule.transactionType.typeKey,
      transactionTypeName: rule.transactionType.displayName,
      tierMemberId: rule.tierMemberId,
      conversionNominal: Number(rule.conversionNominal)
    };
  }

  async getTierLevels(): Promise<TierWithConversionRules[]> {
    const tiers = await prisma.tierMember.findMany({
      orderBy: { minPoints: 'asc' },
      include: {
        conversionRules: {
          include: {
            transactionType: true
          }
        }
      }
    });

    return tiers.map(tier => ({
      ...this.mapTierLevel(tier),
      conversionRules: tier.conversionRules.map(rule =>
        this.mapConversionRule(rule)
      )
    }));
  }

  async getMemberTier(memberId: string): Promise<MemberTierData | null> {
    const member = await prisma.member.findUnique({
      where: { id: memberId },
      include: {
        tierMember: {
          include: {
            conversionRules: {
              include: {
                transactionType: true
              }
            }
          }
        }
      }
    });

    if (!member || !member.tierMember) {
      return null;
    }

    return {
      id: member.tierMember.id,
      tierCode: member.tierMember.tierCode,
      tierName: TIER_NAMES[member.tierMember.tierCode] || member.tierMember.tierCode,
      minPoints: member.tierMember.minPoints,
      maxPoints: member.tierMember.maxPoints,
      currentPoints: member.totalPoints,
      conversionRules: member.tierMember.conversionRules.map(rule =>
        this.mapConversionRule(rule)
      )
    };
  }
}

export const tierService = new TierService();
