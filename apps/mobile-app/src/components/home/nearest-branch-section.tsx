import { router } from 'expo-router';
import { Pressable, View } from 'react-native';

import { NearestBranchCard } from '@/components/branch/nearest-branch-card';
import { GoldGradientText } from '@/components/shared/gold-gradient-text';
import { Text } from '@/components/ui/text';
import type { NearestBranchStatus } from '@/hooks/use-nearest-branch';
import type { BranchItem } from '@/types/branch';
import { cn } from '@/lib/utils';

type NearestBranchSectionProps = {
  branch: BranchItem | null;
  status: NearestBranchStatus;
  onRequestPermission?: () => void;
  className?: string;
};

function FallbackCard({
  message,
  onRequestPermission,
}: {
  message: string;
  onRequestPermission?: () => void;
}) {
  return (
    <View className="gap-3 rounded-xl border border-stone-200 bg-white p-4 shadow-sm shadow-stone-900/10">
      <Text className="text-sm leading-5 text-stone-600">{message}</Text>
      <View className="flex-row flex-wrap items-center gap-3">
        <Pressable
          onPress={() => {
            void onRequestPermission?.();
          }}
          className="active:opacity-70"
          accessibilityRole="button"
          accessibilityLabel="Aktifkan lokasi"
        >
          <GoldGradientText className="text-sm font-medium">Aktifkan lokasi</GoldGradientText>
        </Pressable>
        <Pressable
          onPress={() => router.push('/cabang')}
          className="active:opacity-70"
          accessibilityRole="button"
          accessibilityLabel="Lihat semua cabang"
        >
          <Text className="text-sm font-medium text-stone-500">Lihat semua cabang</Text>
        </Pressable>
      </View>
    </View>
  );
}

export function NearestBranchSection({
  branch,
  status,
  onRequestPermission,
  className,
}: NearestBranchSectionProps) {
  return (
    <View className={cn('gap-3', className)}>
      <View className="flex-row items-center justify-between px-4">
        <Text className="text-base font-semibold text-stone-900">Cabang Terdekat</Text>
        <Pressable onPress={() => router.push('/cabang')} className="active:opacity-70">
          <GoldGradientText className="text-sm font-medium">Lihat semua</GoldGradientText>
        </Pressable>
      </View>

      <View className="px-4">
        {status === 'loading' ? (
          <View className="rounded-xl border border-stone-200 bg-white p-4">
            <Text variant="muted" className="text-sm">
              Mencari cabang terdekat…
            </Text>
          </View>
        ) : null}

        {status === 'success' && branch ? <NearestBranchCard branch={branch} /> : null}

        {status === 'denied' ? (
          <FallbackCard
            message="Izinkan akses lokasi untuk menampilkan cabang HK Gold terdekat."
            onRequestPermission={onRequestPermission}
          />
        ) : null}

        {status === 'error' || status === 'empty' ? (
          <FallbackCard
            message="Lokasi belum tersedia. Aktifkan GPS atau lihat daftar cabang."
            onRequestPermission={onRequestPermission}
          />
        ) : null}
      </View>
    </View>
  );
}
