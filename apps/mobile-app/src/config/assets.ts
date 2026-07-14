import type { ImageSource } from 'expo-image';

import type { MemberTier } from '@/types/auth';

/**
 * Logo HK GOLD VIP (webp saja).
 * - `icon` — header home
 * - `hkgold` / `horizontal` / `vertical` — wordmark auth & screen lain
 */
export const LOGO_ASSETS = {
  icon: require('@/assets/logo/logo-icon.webp'),
  iconBw: require('@/assets/logo/logo-icon-bw.webp'),
  horizontal: require('@/assets/logo/logo-horizontal.webp'),
  horizontalBw: require('@/assets/logo/logo-horizontal-bw.webp'),
  vertical: require('@/assets/logo/logo-vertical.webp'),
  verticalBw: require('@/assets/logo/logo-vertical-bw.webp'),
  /** Wordmark legacy / auth */
  hkgold: require('@/assets/logo/logo-hkgold.webp'),
} as const;

export type LogoAssetKey = keyof typeof LOGO_ASSETS;

/** Ikon visual per member tier (webp). */
export const TIER_ICON_ASSETS: Record<MemberTier, ImageSource> = {
  SILVER: require('@/assets/media/tier/tier-silver.webp'),
  GOLD: require('@/assets/media/tier/tier-gold.webp'),
  PLATINUM: require('@/assets/media/tier/tier-platinum.webp'),
  SAPPHIRE: require('@/assets/media/tier/tier-sapphire.webp'),
};

export function getTierIconSource(tier: MemberTier | string): ImageSource {
  const key = String(tier).toUpperCase() as MemberTier;
  return TIER_ICON_ASSETS[key] ?? TIER_ICON_ASSETS.SILVER;
}

export function getLogoSource(key: LogoAssetKey): ImageSource {
  return LOGO_ASSETS[key];
}
