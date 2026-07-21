import { router } from "expo-router";
import { Fragment } from "react";
import { Pressable, View } from "react-native";

import { GoldCircleIcon } from "@/components/shared/gold-circle-icon";
import { Text } from "@/components/ui/text";
import { HOME_SHORTCUTS } from "@/config/home-shortcuts";
import { cn } from "@/lib/utils";

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
              <GoldCircleIcon
                icon={shortcut.icon}
                circleClassName="size-11"
                iconClassName="size-5 text-white"
                shadowClassName="shadow-md shadow-amber-700/40"
              />
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
