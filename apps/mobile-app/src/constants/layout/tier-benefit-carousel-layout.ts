import { Dimensions } from 'react-native';

const SCREEN_WIDTH = Dimensions.get('window').width;

/** Lebar slide hero — tier aktif dominan, sisi mengintip. */
export const TIER_SLIDE_WIDTH = SCREEN_WIDTH * 0.72;
export const TIER_SLIDE_GAP = 8;
export const TIER_SLIDE_SIDE_PADDING = (SCREEN_WIDTH - TIER_SLIDE_WIDTH) / 2;
export const TIER_SNAP_INTERVAL = TIER_SLIDE_WIDTH + TIER_SLIDE_GAP;

/** Tinggi area hero carousel (ikon + teks + track progres). */
export const TIER_HERO_HEIGHT = 300;
export const TIER_ICON_SECTION_HEIGHT = 132;
export const TIER_TEXT_SECTION_HEIGHT = 88;
export const TIER_PROGRESS_SECTION_HEIGHT = 40;
