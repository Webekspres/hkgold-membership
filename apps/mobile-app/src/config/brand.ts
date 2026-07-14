// export const GOLD_GRADIENT_COLORS = ["#f5c842", "#e8a020"] as const;
export const GOLD_GRADIENT_COLORS = ["#D1A13B", "#ebca86", "#9A6B1F"] as const;

export const GOLD_GRADIENT_START = { x: 0, y: 0 } as const;
export const GOLD_GRADIENT_END = { x: 1, y: 1 } as const;

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
