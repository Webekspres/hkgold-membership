import { router, type Href } from "expo-router";
import {
  BellRing,
  Coins,
  Crown,
  Gift,
  HelpCircle,
  History,
  Key,
  LogOut,
  MapPin,
  Newspaper,
  Smartphone,
  UserRound,
} from "lucide-react-native";
import { useCallback, useState } from "react";
import { ScrollView, View } from "react-native";
import { SafeAreaView } from "react-native-safe-area-context";

import { ActiveRedeemCountdown } from "@/components/card/active-redeem-countdown";
import { ProfileLastRewardCard } from "@/components/profile/profile-last-reward-card";
import { ProfileMemberCard } from "@/components/profile/profile-member-card";
import {
  ProfileMenuList,
  type ProfileMenuItem,
  type ProfileMenuSection,
} from "@/components/profile/profile-menu-list";
import { createPullToRefreshControl } from "@/components/shared/pull-to-refresh";
import { SuspendedNotice } from "@/components/shared/suspended-notice";
import { Button } from "@/components/ui/button";
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog";
import { Text } from "@/components/ui/text";
import { useAuth } from "@/hooks/use-auth";
import { useIsMemberSuspended } from "@/hooks/use-is-member-suspended";
import { useMyProfile } from "@/hooks/use-my-profile";
import { useProfileRedeemHighlight } from "@/hooks/use-profile-redeem-highlight";
import { usePullToRefresh } from "@/hooks/use-pull-to-refresh";
import { copyMemberCode } from "@/lib/clipboard/copy-member-code";
import { toast } from "@/lib/sonner";

const profileMenuSections: ProfileMenuSection[] = [
  {
    key: "activity",
    title: "Aktivitas",
    items: [
      { key: "point-ledger", title: "Riwayat Poin", icon: Coins },
      { key: "redeem-history", title: "Riwayat Redeem", icon: History },
      { key: "reward-catalog", title: "Katalog Reward", icon: Gift },
      { key: "tier-benefit", title: "Tier Benefit", icon: Crown },
    ],
  },
  {
    key: "content",
    title: "Konten & Info",
    items: [
      { key: "event", title: "Event", icon: BellRing },
      { key: "news", title: "Berita", icon: Newspaper },
      { key: "branch-location", title: "Lokasi Cabang", icon: MapPin },
      { key: "faq", title: "FAQ", icon: HelpCircle },
    ],
  },
  {
    key: "account",
    title: "Akun",
    items: [
      { key: "change-password", title: "Ganti Password", icon: Key },
      { key: "change-phone", title: "Ganti Nomor HP", icon: Smartphone },
      { key: "account-settings", title: "Profil Saya", icon: UserRound },
      { key: "logout", title: "Logout", icon: LogOut, destructive: true },
    ],
  },
];

export default function ProfileScreen() {
  const { logout } = useAuth();
  const { card, refetch: refetchProfile } = useMyProfile();
  const isSuspended = useIsMemberSuspended();
  const { highlight, refetch: refetchRedeemHighlight } = useProfileRedeemHighlight();
  const [logoutOpen, setLogoutOpen] = useState(false);
  const [isLoggingOut, setIsLoggingOut] = useState(false);

  const refresh = useCallback(async () => {
    await Promise.all([refetchProfile(), refetchRedeemHighlight()]);
  }, [refetchProfile, refetchRedeemHighlight]);
  const { refreshing, onRefresh } = usePullToRefresh(refresh);

  function handlePressProfileMenu(item: ProfileMenuItem) {
    switch (item.key) {
      case "logout":
        setLogoutOpen(true);
        return;
      case "point-ledger":
        router.push("/point-ledger" as Href);
        return;
      case "redeem-history":
        router.push("/redeem" as Href);
        return;
      case "reward-catalog":
        router.push("/reward");
        return;
      case "event":
        router.push("/events");
        return;
      case "news":
        router.push("/berita");
        return;
      case "branch-location":
        router.push("/cabang");
        return;
      case "faq":
        router.push("/faq" as Href);
        return;
      case "tier-benefit":
        router.push("/tier-benefit" as Href);
        return;
      case "change-password":
        router.push("/change-password");
        return;
      case "change-phone":
        router.push("/change-phone" as Href);
        return;
      case "account-settings":
        router.push("/profile/detail" as Href);
        return;
      default:
        router.push("/cms");
    }
  }

  async function handleConfirmLogout() {
    if (isLoggingOut) return;
    setIsLoggingOut(true);
    try {
      await logout();
      setLogoutOpen(false);
      toast.success("Berhasil keluar", { duration: 3000 });
      router.replace("/login");
    } catch {
      toast.error("Gagal keluar. Coba lagi.", { duration: 4000 });
    } finally {
      setIsLoggingOut(false);
    }
  }

  return (
    <View className="flex-1 bg-background">
      <SafeAreaView className="flex-1" style={{ paddingBottom: 4 }}>
        <ScrollView
          className="flex-1"
          contentContainerClassName="gap-4 px-4 pb-8 pt-4"
          showsVerticalScrollIndicator={false}
          refreshControl={createPullToRefreshControl({
            refreshing,
            onRefresh,
          })}>
          {card ? (
            <>
              <ProfileMemberCard
                fullName={card.fullName}
                memberCode={card.memberNumber}
                currentTier={card.currentTier}
                avatarUri={card.avatarUri}
                avatarFallback={card.avatarFallback}
                onPressMemberCode={() => void copyMemberCode(card.memberNumber)}
              />
            </>
          ) : null}

          {isSuspended ? <SuspendedNotice /> : null}

          {highlight.kind === "pending" ? (
            <ProfileLastRewardCard
              title={highlight.title}
              reward={highlight.reward}
              footer={<ActiveRedeemCountdown expiresAt={highlight.activeRedeem.expiresAt} />}
              onPress={() => router.push(highlight.href as Href)}
            />
          ) : highlight.kind === "completed" ? (
            <ProfileLastRewardCard
              title={highlight.title}
              reward={highlight.reward}
              onPress={() => router.push(highlight.href)}
            />
          ) : (
            <View className="gap-2">
              <Text className="text-base font-bold text-stone-900">
                Reward terakhir diklaim
              </Text>
              <View className="rounded-xl border border-dashed border-amber-200/80 bg-amber-50/50 px-4 py-6">
                <Text className="text-center text-sm text-stone-600">
                  Belum ada reward diklaim
                </Text>
              </View>
            </View>
          )}

          <ProfileMenuList sections={profileMenuSections} onPressItem={handlePressProfileMenu} />
        </ScrollView>
      </SafeAreaView>

      <Dialog open={logoutOpen} onOpenChange={setLogoutOpen}>
        <DialogContent className="gap-4">
          <DialogHeader>
            <DialogTitle>Keluar?</DialogTitle>
            <DialogDescription>
              Anda yakin ingin keluar dari akun?
            </DialogDescription>
          </DialogHeader>
          <DialogFooter>
            <Button
              variant="outline"
              disabled={isLoggingOut}
              onPress={() => setLogoutOpen(false)}>
              <Text>Batal</Text>
            </Button>
            <Button
              variant="destructive"
              disabled={isLoggingOut}
              onPress={() => {
                void handleConfirmLogout();
              }}>
              <Text>{isLoggingOut ? "Keluar..." : "Keluar"}</Text>
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </View>
  );
}
