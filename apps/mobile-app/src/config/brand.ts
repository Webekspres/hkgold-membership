// export const GOLD_GRADIENT_COLORS = ["#f5c842", "#e8a020"] as const;
export const GOLD_GRADIENT_COLORS = ["#D1A13B", "#ebca86", "#9A6B1F"] as const;

export const GOLD_GRADIENT_START = { x: 0, y: 0 } as const;
export const GOLD_GRADIENT_END = { x: 1, y: 1 } as const;

/** Sudut gradient shortcut — highlight lebih rendah, terasa lebih gelap. */
export const GOLD_GRADIENT_SHORTCUT_START = { x: 0.15, y: 0 } as const;
export const GOLD_GRADIENT_SHORTCUT_END = { x: 0.85, y: 1 } as const;

// ==========================================
// 1. INDIKATOR UI & HIGHLIGHT TEXT BRAND
// ==========================================

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
  "#F8FAFC", // Light silver-white
  "#D8E2EC", // Light metallic silver
  "#FFFFFF", // Pure specular shine
  "#dae2e8", // Deep metallic silver reflection for glossy chrome effect
  "#F1F5F9", // Bright metallic silver
] as const;

export const SILVER_GRADIENT_LOCATIONS = [0, 0.25, 0.5, 0.75, 1] as const;

export const SILVER_GRADIENT_START = { x: 0.15, y: 0 } as const;
export const SILVER_GRADIENT_END = { x: 0.85, y: 1 } as const;


// ==========================================
// 2. MEMBER CARD CONSTANTS BY TIER
// ==========================================

// ELITE Tier Card
export const ELITE_CARD_BACKGROUND_COLORS = ["#1e3a8a", "#3b82f6", "#1e40af"] as const;
export const ELITE_CARD_GRADIENT_START = { x: 0, y: 0 } as const;
export const ELITE_CARD_GRADIENT_END = { x: 1, y: 1 } as const;
export const ELITE_CARD_PATTERN_OPACITY = 0.90;
export const ELITE_CARD_VIGNETTE_COLORS = [
  "rgba(10, 10, 10, 0.75)",
  "rgba(10, 10, 10, 0.45)",
  "rgba(10, 10, 10, 0.75)",
] as const;
export const ELITE_CARD_VIGNETTE_START = { x: 0, y: 0 } as const;
export const ELITE_CARD_VIGNETTE_END = { x: 1, y: 1 } as const;
export const ELITE_CARD_DIVIDER_COLORS = ["transparent", "#3b82f6", "#60a5fa", "transparent"] as const;

// GOLD Tier Card
export const GOLD_CARD_GRADIENT_COLORS = [
  "#453116", // Medium-dark warm bronze
  "#614925", // Dark golden brass
  "#856837", // Medium antique gold
  "#AB884D", // Vibrant satin gold
  "#C9A86B", // Soft elegant gold highlight (shining but low-contrast)
  "#B59458", // Warm golden metallic
  "#73562A", // Medium-dark brass
  "#4E381C", // Medium-dark warm bronze shadow
] as const;
export const GOLD_CARD_GRADIENT_START = { x: 0.02, y: 0 } as const;
export const GOLD_CARD_GRADIENT_END = { x: 0.98, y: 1 } as const;
export const GOLD_CARD_PATTERN_OPACITY = 0.25;
export const GOLD_CARD_VIGNETTE = [
  "rgba(20, 12, 4, 0.35)",  // Softened warm shadow edge
  "rgba(20, 12, 4, 0.05)",  // Clear center
  "rgba(20, 12, 4, 0.35)",  // Softened warm shadow edge
] as const;
export const GOLD_CARD_VIGNETTE_START = GOLD_CARD_GRADIENT_START;
export const GOLD_CARD_VIGNETTE_END = GOLD_CARD_GRADIENT_END;
export const GOLD_CARD_DIVIDER_COLORS = [
  "transparent",
  GOLD_CARD_GRADIENT_COLORS[2],
  GOLD_CARD_GRADIENT_COLORS[4],
  "transparent",
] as const;

// SILVER Tier Card
export const SILVER_CARD_BACKGROUND_COLORS = [
  "#9CA7B3", // Cool medium-light silver
  "#B3BECB", // Cool bright silver
  "#CAD5E0", // Light silver-gray
  "#E8EFF7", // Sharp metallic silver reflection
  "#FFFFFF", // Specular white highlight (pure glossy shine)
  "#CAD5E0", // Light silver-gray (sharp drop)
  "#B3BECB", // Cool bright silver
  "#9CA7B3", // Cool medium-light silver
] as const;
export const SILVER_CARD_GRADIENT_START = { x: 0.02, y: 0 } as const;
export const SILVER_CARD_GRADIENT_END = { x: 0.98, y: 1 } as const;
export const SILVER_CARD_PATTERN_OPACITY = 0.25;
export const SILVER_CARD_VIGNETTE_COLORS = [
  "rgba(10, 10, 10, 0.28)",  // Softened shadow edge
  "rgba(10, 10, 10, 0.02)",  // Clear center for maximum glossy shine
  "rgba(10, 10, 10, 0.28)",  // Softened shadow edge
] as const;
export const SILVER_CARD_VIGNETTE_START = SILVER_CARD_GRADIENT_START;
export const SILVER_CARD_VIGNETTE_END = SILVER_CARD_GRADIENT_END;
export const SILVER_CARD_DIVIDER_COLORS = [
  "transparent",
  SILVER_CARD_BACKGROUND_COLORS[2],
  SILVER_CARD_BACKGROUND_COLORS[4],
  "transparent",
] as const;

// PLATINUM Tier Card
export const PLATINUM_CARD_BACKGROUND_COLORS = [
  "#090A0B", // Deep dark charcoal shadow
  "#141619", // Very dark slate-gray
  "#22252A", // Dark slate-gray
  "#494E59", // Slate-platinum (smooth transition)
  "#7A8392", // Brushed silver-titanium highlight (slightly brighter glow)
  "#545A66", // Slate-platinum (smooth transition)
  "#2C2F36", // Dark slate-gray
  "#0E1012", // Deep dark charcoal shadow
] as const;
export const PLATINUM_CARD_GRADIENT_START = { x: 0.02, y: 0 } as const;
export const PLATINUM_CARD_GRADIENT_END = { x: 0.98, y: 1 } as const;
export const PLATINUM_CARD_PATTERN_OPACITY = 0.25;
export const PLATINUM_CARD_VIGNETTE_COLORS = [
  "rgba(10, 10, 10, 0.45)",  // Shadow edge
  "rgba(10, 10, 10, 0.05)",  // Clear center for soft titanium glow
  "rgba(10, 10, 10, 0.45)",  // Shadow edge
] as const;
export const PLATINUM_CARD_VIGNETTE_START = PLATINUM_CARD_GRADIENT_START;
export const PLATINUM_CARD_VIGNETTE_END = PLATINUM_CARD_GRADIENT_END;
export const PLATINUM_CARD_DIVIDER_COLORS = [
  "transparent",
  PLATINUM_CARD_BACKGROUND_COLORS[3],
  PLATINUM_CARD_BACKGROUND_COLORS[4],
  "transparent",
] as const;


// ==========================================
// 3. TIER CARD REGISTRY
// ==========================================

export const TIER_GRADIENTS = {
  ELITE: {
    colors: ELITE_CARD_BACKGROUND_COLORS,
    start: ELITE_CARD_GRADIENT_START,
    end: ELITE_CARD_GRADIENT_END,
    patternOpacity: ELITE_CARD_PATTERN_OPACITY,
    vignetteColors: ELITE_CARD_VIGNETTE_COLORS,
    vignetteStart: ELITE_CARD_VIGNETTE_START,
    vignetteEnd: ELITE_CARD_VIGNETTE_END,
    divider: ELITE_CARD_DIVIDER_COLORS,
  },
  GOLD: {
    colors: GOLD_CARD_GRADIENT_COLORS,
    start: GOLD_CARD_GRADIENT_START,
    end: GOLD_CARD_GRADIENT_END,
    patternOpacity: GOLD_CARD_PATTERN_OPACITY,
    vignetteColors: GOLD_CARD_VIGNETTE,
    vignetteStart: GOLD_CARD_VIGNETTE_START,
    vignetteEnd: GOLD_CARD_VIGNETTE_END,
    divider: GOLD_CARD_DIVIDER_COLORS,
  },
  SILVER: {
    colors: SILVER_CARD_BACKGROUND_COLORS,
    start: SILVER_CARD_GRADIENT_START,
    end: SILVER_CARD_GRADIENT_END,
    patternOpacity: SILVER_CARD_PATTERN_OPACITY,
    vignetteColors: SILVER_CARD_VIGNETTE_COLORS,
    vignetteStart: SILVER_CARD_VIGNETTE_START,
    vignetteEnd: SILVER_CARD_VIGNETTE_END,
    divider: SILVER_CARD_DIVIDER_COLORS,
  },
  PLATINUM: {
    colors: PLATINUM_CARD_BACKGROUND_COLORS,
    start: PLATINUM_CARD_GRADIENT_START,
    end: PLATINUM_CARD_GRADIENT_END,
    patternOpacity: PLATINUM_CARD_PATTERN_OPACITY,
    vignetteColors: PLATINUM_CARD_VIGNETTE_COLORS,
    vignetteStart: PLATINUM_CARD_VIGNETTE_START,
    vignetteEnd: PLATINUM_CARD_VIGNETTE_END,
    divider: PLATINUM_CARD_DIVIDER_COLORS,
  },
} as const;

/** Tier-specific static card background images. */
export const TIER_CARD_BACKGROUND_IMAGES = {
  ELITE: require("@/assets/media/tier/card-elite.webp"),
  GOLD: require("@/assets/media/tier/card-gold.webp"),
  SILVER: require("@/assets/media/tier/card-silver.webp"),
  PLATINUM: require("@/assets/media/tier/card-platinum.webp"),
} as const;
