import { SymbolView } from "expo-symbols";
import {
  useCallback,
  useEffect,
  useMemo,
  useRef,
  useState,
  type Dispatch,
  type SetStateAction,
} from "react";
import { Modal, Pressable, ScrollView, View } from "react-native";

import { RewardPointsRangeSlider } from "@/components/reward-points-range-slider";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Text } from "@/components/ui/text";
import type { RewardCategory } from "@/constants/mock-rewards";
import type {
  RewardFilterState,
  RewardPointsBounds,
} from "@/lib/filter-rewards";
import { cn } from "@/lib/utils";

const CHECK_ICON = {
  ios: "checkmark",
  android: "check",
  web: "check",
} as const;

type RewardFilterModalProps = {
  visible: boolean;
  categories: RewardCategory[];
  bounds: RewardPointsBounds;
  filter: RewardFilterState;
  onFilterChange: Dispatch<SetStateAction<RewardFilterState>>;
  onClose: () => void;
  onApply: () => void;
  onReset: () => void;
};

function clampPoints(value: number, min: number, max: number) {
  return Math.min(Math.max(value, min), max);
}

function parsePointsInput(value: string) {
  const parsed = Number.parseInt(value.replace(/\D/g, ""), 10);
  return Number.isFinite(parsed) ? parsed : null;
}

export function RewardFilterModal({
  visible,
  categories,
  bounds,
  filter,
  onFilterChange,
  onClose,
  onApply,
  onReset,
}: RewardFilterModalProps) {
  const [categoryQuery, setCategoryQuery] = useState("");
  const [minInput, setMinInput] = useState(String(filter.pointsMin));
  const [maxInput, setMaxInput] = useState(String(filter.pointsMax));
  const isEditingMinRef = useRef(false);
  const isEditingMaxRef = useRef(false);

  useEffect(() => {
    if (!visible) {
      return;
    }

    setCategoryQuery("");
    setMinInput(String(filter.pointsMin));
    setMaxInput(String(filter.pointsMax));
    isEditingMinRef.current = false;
    isEditingMaxRef.current = false;
  }, [visible]);

  useEffect(() => {
    if (!isEditingMinRef.current) {
      setMinInput(String(filter.pointsMin));
    }
  }, [filter.pointsMin]);

  useEffect(() => {
    if (!isEditingMaxRef.current) {
      setMaxInput(String(filter.pointsMax));
    }
  }, [filter.pointsMax]);

  const filteredCategories = useMemo(() => {
    const query = categoryQuery.trim().toLowerCase();
    if (!query) {
      return categories;
    }

    return categories.filter((category) =>
      category.name.toLowerCase().includes(query),
    );
  }, [categories, categoryQuery]);

  const toggleCategory = (categoryId: number) => {
    onFilterChange((prev) => {
      const isSelected = prev.categoryIds.includes(categoryId);
      const categoryIds = isSelected
        ? prev.categoryIds.filter((id) => id !== categoryId)
        : [...prev.categoryIds, categoryId];

      return { ...prev, categoryIds };
    });
  };

  const updatePoints = useCallback(
    (pointsMin: number, pointsMax: number) => {
      onFilterChange((prev) => {
        const nextMin = clampPoints(
          Math.min(pointsMin, pointsMax),
          bounds.min,
          bounds.max,
        );
        const nextMax = clampPoints(
          Math.max(pointsMin, pointsMax),
          bounds.min,
          bounds.max,
        );

        if (nextMin === prev.pointsMin && nextMax === prev.pointsMax) {
          return prev;
        }

        return {
          ...prev,
          pointsMin: nextMin,
          pointsMax: nextMax,
        };
      });
    },
    [bounds.max, bounds.min, onFilterChange],
  );

  const commitMinInput = useCallback(() => {
    isEditingMinRef.current = false;
    const parsed = parsePointsInput(minInput);

    if (parsed === null) {
      setMinInput(String(filter.pointsMin));
      return;
    }

    updatePoints(parsed, filter.pointsMax);
  }, [filter.pointsMax, filter.pointsMin, minInput, updatePoints]);

  const commitMaxInput = useCallback(() => {
    isEditingMaxRef.current = false;
    const parsed = parsePointsInput(maxInput);

    if (parsed === null) {
      setMaxInput(String(filter.pointsMax));
      return;
    }

    updatePoints(filter.pointsMin, parsed);
  }, [filter.pointsMax, filter.pointsMin, maxInput, updatePoints]);

  return (
    <Modal
      visible={visible}
      transparent
      animationType="slide"
      onRequestClose={onClose}
    >
      <View className="flex-1 justify-end">
        <Pressable className="absolute inset-0 bg-black/40" onPress={onClose} />

        <View className="max-h-[88%] rounded-t-2xl bg-white pt-5">
          <View className="mb-3 h-1 w-10 self-center rounded-full bg-stone-200" />

          <View className="px-4">
            <Text className="text-base font-semibold text-stone-900">
              Filter Hadiah
            </Text>
            <Text variant="muted" className="mt-1">
              Pilih rentang poin dan kategori
            </Text>
          </View>

          <View className="mt-5 gap-3 px-4">
            <Text className="text-sm font-semibold text-stone-800">
              Rentang poin
            </Text>

            <View className="flex-row gap-3">
              <View className="flex-1 gap-1">
                <Text variant="muted" className="text-xs">
                  Dari
                </Text>
                <Input
                  keyboardType="number-pad"
                  value={minInput}
                  onChangeText={setMinInput}
                  onFocus={() => {
                    isEditingMinRef.current = true;
                  }}
                  onBlur={commitMinInput}
                />
              </View>
              <View className="flex-1 gap-1">
                <Text variant="muted" className="text-xs">
                  Sampai
                </Text>
                <Input
                  keyboardType="number-pad"
                  value={maxInput}
                  onChangeText={setMaxInput}
                  onFocus={() => {
                    isEditingMaxRef.current = true;
                  }}
                  onBlur={commitMaxInput}
                />
              </View>
            </View>

            <RewardPointsRangeSlider
              min={bounds.min}
              max={bounds.max}
              low={filter.pointsMin}
              high={filter.pointsMax}
              onChange={updatePoints}
            />
          </View>

          <ScrollView
            className="mt-6 px-4"
            showsVerticalScrollIndicator={false}
            keyboardShouldPersistTaps="handled"
            nestedScrollEnabled
          >
            <View className="gap-3">
              <Text className="text-sm font-semibold text-stone-800">
                Kategori
              </Text>
              <Input
                placeholder="Cari kategori..."
                placeholderTextColor="#a8a29e"
                value={categoryQuery}
                onChangeText={setCategoryQuery}
              />

              <View className="gap-2">
                {filteredCategories.map((category) => {
                  const selected = filter.categoryIds.includes(category.id);

                  return (
                    <Pressable
                      key={category.id}
                      className={cn(
                        "flex-row items-center gap-3 rounded-lg border px-3 py-3",
                        selected
                          ? "border-[#e8a020] bg-[#fffbeb]"
                          : "border-stone-200 bg-white",
                      )}
                      onPress={() => toggleCategory(category.id)}
                    >
                      <View
                        className={cn(
                          "h-5 w-5 items-center justify-center rounded border",
                          selected
                            ? "border-[#e8a020] bg-[#e8a020]"
                            : "border-stone-300 bg-white",
                        )}
                      >
                        {selected ? (
                          <SymbolView
                            name={CHECK_ICON}
                            size={12}
                            tintColor="#ffffff"
                          />
                        ) : null}
                      </View>
                      <Text className="text-sm text-stone-800">
                        {category.name}
                      </Text>
                    </Pressable>
                  );
                })}
              </View>
            </View>
          </ScrollView>

          <View className="mt-5 gap-3 px-4 pb-8">
            <View className="flex-row gap-3">
              <Button variant="outline" className="flex-1" onPress={onReset}>
                <Text>Reset</Text>
              </Button>
              <Button
                className="flex-1 bg-[#e8a020] active:bg-[#d4921c]"
                onPress={onApply}
              >
                <Text className="text-stone-900">Terapkan</Text>
              </Button>
            </View>

            <Button variant="ghost" onPress={onClose}>
              <Text>Tutup</Text>
            </Button>
          </View>
        </View>
      </View>
    </Modal>
  );
}
