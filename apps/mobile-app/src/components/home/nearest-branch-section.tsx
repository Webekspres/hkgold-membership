import { router } from 'expo-router';
import { Pressable, View } from 'react-native';

import { NearestBranchCard } from '@/components/branch/nearest-branch-card';
import { Text } from '@/components/ui/text';
import type { BranchItem } from '@/types/branch';
import { cn } from '@/lib/utils';

type NearestBranchSectionProps = {
  branch: BranchItem;
  className?: string;
};

export function NearestBranchSection({ branch, className }: NearestBranchSectionProps) {
  return (
    <View className={cn('gap-3', className)}>
      <View className="flex-row items-center justify-between px-4">
        <Text className="text-base font-semibold text-stone-900">Cabang Terdekat</Text>
        <Pressable onPress={() => router.push('/cabang')} className="active:opacity-70">
          <Text className="text-sm font-medium text-[#c4841a]">Lihat semua</Text>
        </Pressable>
      </View>

      <View className="px-4">
        <NearestBranchCard branch={branch} />
      </View>
    </View>
  );
}
