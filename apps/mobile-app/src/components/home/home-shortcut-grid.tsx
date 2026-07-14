import { LinearGradient } from "expo-linear-gradient";
import { SymbolView } from "expo-symbols";
import { router } from "expo-router";
import { cssInterop } from "nativewind";
import { Fragment } from "react";
import { Platform, Pressable, View } from "react-native";

import { Text } from "@/components/ui/text";
import { HOME_SHORTCUTS } from "@/config/home-shortcuts";
import { cn } from "@/lib/utils";

cssInterop(LinearGradient, { className: "style" });

const ICON_COLOR = "#ffffff";
const SHORTCUT_SIZE = 44;
const SHORTCUT_ICON = 16;
/** Referensi mockup: kuning terang → amber dalam. */
const SHORTCUT_GRADIENT = ["#F9D423", "#F0B429", "#E08912"] as [
  string,
  string,
  string,
];

type HomeShortcutGridProps = {
  className?: string;
};

export function HomeShortcutGrid({ className }: HomeShortcutGridProps) {
  return (
    <View className={cn("gap-3 px-4", className)}>
      <Text className="text-base font-bold text-stone-900">Akses Cepat</Text>

      <View className="flex-row items-center">
        {HOME_SHORTCUTS.map((shortcut, index) => (
          <Fragment key={shortcut.id}>
            {index > 0 ? (
              <View className="mx-1 h-14 w-px self-center bg-stone-100" />
            ) : null}

            <Pressable
              className="min-w-0 flex-1 items-center gap-2 py-1 active:opacity-70"
              onPress={() => router.push(shortcut.href)}
              accessibilityRole="button"
              accessibilityLabel={shortcut.label}
            >
              {/* Shadow di wrapper — LinearGradient sering drop shadow di Android */}
              <View
                className="rounded-full shadow-md shadow-amber-700/40"
                style={
                  Platform.OS === "android"
                    ? { elevation: 6, backgroundColor: "transparent" }
                    : undefined
                }
              >
                <LinearGradient
                  colors={SHORTCUT_GRADIENT}
                  start={{ x: 0.15, y: 0 }}
                  end={{ x: 0.85, y: 1 }}
                  className="items-center justify-center rounded-full"
                  style={{
                    height: SHORTCUT_SIZE,
                    width: SHORTCUT_SIZE,
                    borderRadius: SHORTCUT_SIZE / 2,
                  }}
                >
                  <SymbolView
                    name={shortcut.icon}
                    size={SHORTCUT_ICON}
                    tintColor={ICON_COLOR}
                  />
                </LinearGradient>
              </View>
              <Text
                variant="small"
                className="text-center text-[13px] font-medium text-stone-900"
              >
                {shortcut.label}
              </Text>
            </Pressable>
          </Fragment>
        ))}
      </View>
    </View>
  );
}
