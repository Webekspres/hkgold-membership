import { LinearGradient } from "expo-linear-gradient";
import { Pressable, View } from "react-native";

import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";
import { Text } from "@/components/ui/text";
import {
  GOLD_GRADIENT_COLORS,
  GOLD_GRADIENT_END,
  GOLD_GRADIENT_START,
} from "@/config/brand";

type ProfileMemberCardProps = {
  fullName: string;
  memberCode: string;
  avatarUri?: string;
  avatarFallback: string;
  birthDateLabel?: string | null;
  onPressMemberCode: () => void;
};

export function ProfileMemberCard({
  fullName,
  memberCode,
  avatarUri,
  avatarFallback,
  birthDateLabel,
  onPressMemberCode,
}: ProfileMemberCardProps) {
  return (
    <LinearGradient
      colors={[...GOLD_GRADIENT_COLORS]}
      start={GOLD_GRADIENT_START}
      end={GOLD_GRADIENT_END}
      style={{ borderRadius: 20, padding: 2 }}>
      <View className="rounded-[18px] bg-white p-4">
        <View className="flex-row items-center gap-3">
          <Avatar alt={fullName} className="size-16 border border-stone-200">
            <AvatarImage source={avatarUri ? { uri: avatarUri } : undefined} />
            <AvatarFallback>
              <Text className="text-lg font-semibold text-stone-700">{avatarFallback}</Text>
            </AvatarFallback>
          </Avatar>

          <View className="min-w-0 flex-1">
            <Text className="mt-1 text-lg font-semibold text-stone-900" numberOfLines={1}>
              {fullName}
            </Text>
            <Pressable
              className="mt-1 self-start rounded-full bg-stone-100 px-3 py-1 active:opacity-80"
              onPress={onPressMemberCode}>
              <Text variant="small" className="text-stone-700">
                {memberCode}
              </Text>
            </Pressable>
            {birthDateLabel ? (
              <Text variant="muted" className="mt-1 text-xs">
                Lahir {birthDateLabel}
              </Text>
            ) : null}
          </View>
        </View>
      </View>
    </LinearGradient>
  );
}
