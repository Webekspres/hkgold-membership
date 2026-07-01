import { Dimensions } from "react-native";

import { SCREEN_HORIZONTAL_PADDING } from "@/constants/screen-layout";

const SCREEN_WIDTH = Dimensions.get("window").width;

export const GRID_HORIZONTAL_PADDING = SCREEN_HORIZONTAL_PADDING;
export const GRID_COLUMN_GAP = 6;
export const GRID_COLUMNS = 2;
export const GRID_ITEM_WIDTH =
  (SCREEN_WIDTH -
    GRID_HORIZONTAL_PADDING * 2 -
    GRID_COLUMN_GAP * (GRID_COLUMNS - 1)) /
  GRID_COLUMNS;
