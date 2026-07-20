import { GOLD_GRADIENT_COLORS } from '@/config/brand';
import type { TierBenefitSlide } from '@/types/tier-benefit';

export const MOCK_TIER_BENEFIT_SLIDES: TierBenefitSlide[] = [
  {
    tier: 'SILVER',
    title: 'Silver',
    subtitle: 'Tier awal keanggotaan HK GOLD VIP',
    accentColors: ['#e7e5e4', '#a8a29e'],
    iconClassName: 'text-stone-500',
    textClassName: 'text-stone-700',
    benefits: [
      { label: 'Multiplier poin', value: '1x dari setiap transaksi' },
      { label: 'Katalog reward', value: 'Akses reward dasar' },
      { label: 'Event member', value: 'Undangan event reguler' },
      { label: 'Layanan cabang', value: 'Antrian standar' },
    ],
  },
  {
    tier: 'GOLD',
    title: 'Gold',
    subtitle: 'Tier loyalitas premium untuk member aktif',
    accentColors: [...GOLD_GRADIENT_COLORS],
    iconClassName: 'text-amber-600',
    textClassName: 'text-[#b45309]',
    benefits: [
      { label: 'Multiplier poin', value: '1,25x dari setiap transaksi' },
      { label: 'Katalog reward', value: 'Akses reward menengah & premium' },
      { label: 'Event member', value: 'Prioritas undangan event eksklusif' },
      { label: 'Layanan cabang', value: 'Konsultasi emas prioritas' },
      { label: 'Promo spesial', value: 'Penawaran ulang tahun member' },
    ],
  },
  {
    tier: 'PLATINUM',
    title: 'Platinum',
    subtitle: 'Tier elit dengan benefit lebih lengkap',
    accentColors: ['#cbd5e1', '#64748b'],
    iconClassName: 'text-slate-500',
    textClassName: 'text-slate-700',
    benefits: [
      { label: 'Multiplier poin', value: '1,5x dari setiap transaksi' },
      { label: 'Katalog reward', value: 'Akses reward premium & terbatas' },
      { label: 'Event member', value: 'Akses VIP lounge event' },
      { label: 'Layanan cabang', value: 'Personal shopper dedicated' },
      { label: 'Promo spesial', value: 'Early access koleksi baru' },
    ],
  },
  {
    tier: 'ELITE',
    title: 'Elite',
    subtitle: 'Tier tertinggi dengan benefit eksklusif',
    accentColors: ['#a5b4fc', '#4338ca'],
    iconClassName: 'text-indigo-200',
    textClassName: 'text-white',
    benefits: [
      { label: 'Multiplier poin', value: '2x dari setiap transaksi' },
      { label: 'Katalog reward', value: 'Semua reward termasuk edisi Elite' },
      { label: 'Event member', value: 'Undangan private showcase & gala' },
      { label: 'Layanan cabang', value: 'Layanan concierge 24/7' },
      { label: 'Promo spesial', value: 'Hadiah anniversary & surprise reward' },
    ],
  },
];
