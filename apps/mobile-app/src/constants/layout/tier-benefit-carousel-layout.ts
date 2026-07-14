import { Dimensions } from 'react-native';

const SCREEN_WIDTH = Dimensions.get('window').width;

export const TIER_SLIDE_WIDTH = SCREEN_WIDTH * 0.75;
export const TIER_SLIDE_GAP = 12;
export const TIER_SLIDE_SIDE_PADDING = (SCREEN_WIDTH - TIER_SLIDE_WIDTH) / 2;
export const TIER_SNAP_INTERVAL = TIER_SLIDE_WIDTH + TIER_SLIDE_GAP;
