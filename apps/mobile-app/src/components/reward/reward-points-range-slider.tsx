import Slider from "@react-native-community/slider";
import { useCallback } from "react";
import { View } from "react-native";

import { Text } from "@/components/ui/text";

type RewardPointsRangeSliderProps = {
  min: number;
  max: number;
  low: number;
  high: number;
  onChange: (low: number, high: number) => void;
};

const STEP = 100;
const TRACK_COLOR = "#e7e5e4";
const ACTIVE_TRACK_COLOR = "#e8a020";
const THUMB_COLOR = "#e8a020";
const HIGH_THUMB_COLOR = "#c4841a";

function snapToStep(value: number) {
  return Math.round(value / STEP) * STEP;
}

function formatPoints(value: number) {
  return value.toLocaleString("id-ID");
}

export function RewardPointsRangeSlider({
  min,
  max,
  low,
  high,
  onChange,
}: RewardPointsRangeSliderProps) {
  const handleLowChange = useCallback(
    (value: number) => {
      const nextLow = snapToStep(value);
      onChange(Math.min(nextLow, high), high);
    },
    [high, onChange],
  );

  const handleHighChange = useCallback(
    (value: number) => {
      const nextHigh = snapToStep(value);
      onChange(low, Math.max(nextHigh, low));
    },
    [low, onChange],
  );

  return (
    <View className="gap-4 py-1">
      <View className="gap-1">
        <View className="flex-row items-center justify-between">
          <Text variant="muted" className="text-xs">
            Minimum
          </Text>
          <Text className="text-xs font-medium text-[#b45309]">
            {formatPoints(low)}
          </Text>
        </View>
        <Slider
          value={low}
          minimumValue={min}
          maximumValue={max}
          step={STEP}
          onValueChange={handleLowChange}
          minimumTrackTintColor={ACTIVE_TRACK_COLOR}
          maximumTrackTintColor={TRACK_COLOR}
          thumbTintColor={THUMB_COLOR}
        />
      </View>

      <View className="gap-1">
        <View className="flex-row items-center justify-between">
          <Text variant="muted" className="text-xs">
            Maksimum
          </Text>
          <Text className="text-xs font-medium text-[#b45309]">
            {formatPoints(high)}
          </Text>
        </View>
        <Slider
          value={high}
          minimumValue={min}
          maximumValue={max}
          step={STEP}
          onValueChange={handleHighChange}
          minimumTrackTintColor={ACTIVE_TRACK_COLOR}
          maximumTrackTintColor={TRACK_COLOR}
          thumbTintColor={HIGH_THUMB_COLOR}
        />
      </View>
    </View>
  );
}
