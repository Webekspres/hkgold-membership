import { Dimensions } from 'react-native';

import { SCREEN_HORIZONTAL_PADDING } from '@/constants/screen-layout';

const SCREEN_WIDTH = Dimensions.get('window').width;

export const CAROUSEL_LEFT_PADDING = SCREEN_HORIZONTAL_PADDING;
export const CAROUSEL_PEEK = 56;
export const CAROUSEL_ITEM_GAP = 12;
export const CAROUSEL_ITEM_WIDTH = SCREEN_WIDTH - CAROUSEL_LEFT_PADDING - CAROUSEL_PEEK;
export const CAROUSEL_SNAP_INTERVAL = CAROUSEL_ITEM_WIDTH + CAROUSEL_ITEM_GAP;
