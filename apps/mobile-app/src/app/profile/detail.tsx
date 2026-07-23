import { Stack, router } from "expo-router";
import { useCallback } from "react";
import { Pressable, ScrollView, View } from "react-native";
import { SafeAreaView } from "react-native-safe-area-context";

import { GoldButton } from "@/components/shared/gold-button";
import { createPullToRefreshControl } from "@/components/shared/pull-to-refresh";
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";
import { Text } from "@/components/ui/text";
import { useMyProfile } from "@/hooks/use-my-profile";
import { usePullToRefresh } from "@/hooks/use-pull-to-refresh";
import {
  formatAddressLine,
  formatGenderLabel,
  parseDateOnly,
} from "@/services/member";

function formatBirthDate(value: string | null | undefined): string {
  const date = parseDateOnly(value);
  if (!date) return "-";
  return date.toLocaleDateString("id-ID", {
    day: "numeric",
    month: "long",
    year: "numeric",
  });
}

function ProfileField({ label, value }: { label: string; value: string }) {
  return (
    <View className="gap-1 border-b border-stone-100 py-3">
      <Text className="text-xs font-medium uppercase tracking-wide text-stone-500">
        {label}
      </Text>
      <Text className="text-base text-stone-900">{value || "-"}</Text>
    </View>
  );
}

export default function ProfileDetailScreen() {
  const { profile, card, isLoading, refetch } = useMyProfile();
  const { refreshing, onRefresh } = usePullToRefresh(
    useCallback(async () => {
      await refetch();
    }, [refetch]),
  );

  return (
    <>
      <Stack.Screen
        options={{
          title: "Profil Saya",
          headerShown: true,
          headerBackTitle: "Profil",
          headerTintColor: "#57534e",
        }}
      />
      <SafeAreaView className="flex-1 bg-background" edges={["bottom"]}>
        <ScrollView
          className="flex-1"
          contentContainerClassName="gap-5 px-4 pb-8 pt-4"
          showsVerticalScrollIndicator={false}
          refreshControl={createPullToRefreshControl({ refreshing, onRefresh })}
        >
          {isLoading && !profile ? (
            <Text className="text-center text-sm text-stone-500">Memuat profil...</Text>
          ) : profile && card ? (
            <>
              <View className="items-center gap-3 rounded-xl border border-stone-100 bg-white px-4 py-6">
                <Avatar alt={card.fullName} className="size-24 border border-stone-200">
                  <AvatarImage
                    source={card.avatarUri ? { uri: card.avatarUri } : undefined}
                  />
                  <AvatarFallback>
                    <Text className="text-2xl font-semibold text-stone-700">
                      {card.avatarFallback}
                    </Text>
                  </AvatarFallback>
                </Avatar>
                <View className="items-center gap-1">
                  <Text className="text-xl font-bold text-stone-900">{card.fullName}</Text>
                  <Text className="text-sm text-stone-500">{profile.memberNumber}</Text>
                </View>
              </View>

              <View className="rounded-xl border border-stone-100 bg-white px-4">
                <ProfileField label="Nomor Member" value={profile.memberNumber} />
                <ProfileField label="Email" value={profile.user.email} />
                <ProfileField label="Nomor HP" value={profile.phoneNumber} />
                <ProfileField
                  label="Tanggal Lahir"
                  value={formatBirthDate(profile.birthDate)}
                />
                <ProfileField
                  label="Jenis Kelamin"
                  value={formatGenderLabel(profile.gender)}
                />
                <ProfileField label="Alamat" value={formatAddressLine(profile)} />
              </View>

              <GoldButton
                label="Edit Profil"
                width="full"
                variant="filled"
                onPress={() => router.push("/profile/edit")}
              />
            </>
          ) : (
            <View className="gap-3">
              <Text className="text-center text-sm text-stone-500">
                Profil tidak tersedia.
              </Text>
              <Pressable onPress={() => void refetch()} className="active:opacity-70">
                <Text className="text-center text-sm font-medium text-amber-700">
                  Coba lagi
                </Text>
              </Pressable>
            </View>
          )}
        </ScrollView>
      </SafeAreaView>
    </>
  );
}
