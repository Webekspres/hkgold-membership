import { View } from 'react-native';

import { Button } from '@/components/ui/button';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import { Text } from '@/components/ui/text';
import type { RewardBranchStockItem } from '@/constants/mock-rewards';
import { formatBranchLocation } from '@/lib/format-branch-location';

type RewardRedeemDialogProps = {
  open: boolean;
  onOpenChange: (open: boolean) => void;
  branchStock: RewardBranchStockItem | null;
};

export function RewardRedeemDialog({
  open,
  onOpenChange,
  branchStock,
}: RewardRedeemDialogProps) {
  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent className="gap-4">
        <DialogHeader>
          <DialogTitle>Tukarkan Hadiah</DialogTitle>
          <DialogDescription>
            {branchStock
              ? `Penukaran di ${branchStock.branchName}, ${formatBranchLocation(branchStock.subdistrict, branchStock.city)} akan segera hadir.`
              : 'Fitur penukaran hadiah akan segera hadir.'}
          </DialogDescription>
        </DialogHeader>

        <View className="rounded-lg bg-[#fffbeb] px-3 py-2">
          <Text className="text-sm text-stone-700">Coming soon</Text>
        </View>

        <DialogFooter>
          <Button variant="outline" onPress={() => onOpenChange(false)}>
            <Text>Tutup</Text>
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  );
}
