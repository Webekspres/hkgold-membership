// export const GOLD_GRADIENT_COLORS = ["#f5c842", "#e8a020"] as const;
export const GOLD_GRADIENT_COLORS = ["#D1A13B", "#ebca86", "#9A6B1F"] as const;

export const GOLD_GRADIENT_START = { x: 0, y: 0 } as const;
export const GOLD_GRADIENT_END = { x: 1, y: 1 } as const;

/** Sudut gradient shortcut — highlight lebih rendah, terasa lebih gelap. */
export const GOLD_GRADIENT_SHORTCUT_START = { x: 0.15, y: 0 } as const;
export const GOLD_GRADIENT_SHORTCUT_END = { x: 0.85, y: 1 } as const;

/** Kartu member Gold — deep antique gold, lebih gelap & mewah. */
export const GOLD_CARD_GRADIENT_COLORS = [
  "#2A1805",
  "#5C3D12",
  "#9A7228",
  "#C9A24E",
  "#3D2608",
] as const;

export const GOLD_CARD_GRADIENT_START = { x: 0.05, y: 0 } as const;
export const GOLD_CARD_GRADIENT_END = { x: 0.95, y: 1 } as const;

/** Vignette hangat — depth tanpa memudarkan seperti fade abu-abu. */
export const GOLD_CARD_VIGNETTE = [
  "rgba(20,12,4,0.5)",
  "rgba(20,12,4,0.12)",
  "rgba(20,12,4,0.5)",
] as const;

/** Pill nomor member di kartu Gold. */
export const GOLD_MEMBER_PILL_COLORS = [
  "#F8EDD4",
  "#E8D4A8",
  "#C9A24E",
] as const;

/** Background indikator tab aktif — gold transparan (selaras gradient button). */
export const GOLD_TAB_INDICATOR_COLORS = [
  "rgba(245, 200, 66, 0.3)",
  "rgba(232, 160, 32, 0.3)",
] as const;

export const GOLD_TAB_INDICATOR = "rgba(232, 160, 32, 0.4)";

export const GOLD_TAB_RIPPLE = "rgba(232, 160, 32, 0.2)";

export const GOLD_TAB_SELECTED = "#D1A13B";

/** Dark floating tab bar — near-black pill background. */
export const DARK_TAB_BAR_BACKGROUND = "#1A1A1A";

/** Active tab pill — solid white elevated circle/pill. */
export const DARK_TAB_ACTIVE_BACKGROUND = "#FFFFFF";

/** Inactive tab icons on dark bar. */
export const DARK_TAB_ICON_INACTIVE = "rgba(255,255,255,0.6)";

/** Active tab icon — reuse gold brand accent. */
export const DARK_TAB_ICON_ACTIVE = GOLD_TAB_SELECTED;

/** Silver gradient — cool metallic chrome untuk teks kartu member. */
export const SILVER_GRADIENT_COLORS = [
  "#8E9DAA",
  "#C5D0DA",
  "#FFFFFF",
  "#E4ECF2",
  "#A8B6C2",
] as const;

export const SILVER_GRADIENT_LOCATIONS = [0, 0.25, 0.5, 0.75, 1] as const;

export const SILVER_GRADIENT_START = { x: 0.15, y: 0 } as const;
export const SILVER_GRADIENT_END = { x: 0.85, y: 1 } as const;

/** Tier-specific background & divider gradients. */
export const TIER_GRADIENTS = {
  ELITE: {
    colors: ["#1e3a8a", "#3b82f6", "#1e40af"] as const,
    divider: ["transparent", "#3b82f6", "#60a5fa", "transparent"] as const,
  },
  GOLD: {
    colors: GOLD_CARD_GRADIENT_COLORS,
    divider: [
      "transparent",
      GOLD_CARD_GRADIENT_COLORS[0],
      GOLD_CARD_GRADIENT_COLORS[1],
      "transparent",
    ] as const,
  },
  SILVER: {
    colors: ["#71717a", "#a1a1aa", "#52525b"] as const,
    divider: ["transparent", "#a1a1aa", "#d4d4d8", "transparent"] as const,
  },
  PLATINUM: {
    colors: ["#27272a", "#3f3f46", "#18181b"] as const,
    divider: ["transparent", "#52525b", "#71717a", "transparent"] as const,
  },
} as const;
