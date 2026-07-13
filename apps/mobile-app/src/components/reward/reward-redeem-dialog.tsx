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
import type { RewardBranchStockItem } from '@/types/reward';
import { formatBranchLocation } from '@/lib/format/format-branch-location';

type RewardRedeemDialogProps = {
  open: boolean;
  onOpenChange: (open: boolean) => void;
  branchStock: RewardBranchStockItem | null;
  /** Dummy confirm — belum hit API redeem. */
  onConfirm: () => void;
};

export function RewardRedeemDialog({
  open,
  onOpenChange,
  branchStock,
  onConfirm,
}: RewardRedeemDialogProps) {
  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent className="gap-4">
        <DialogHeader>
          <DialogTitle>Tukarkan Hadiah</DialogTitle>
          <DialogDescription>
            {branchStock
              ? `Konfirmasi penukaran di ${branchStock.branchName}, ${formatBranchLocation(branchStock.subdistrict, branchStock.city)}.`
              : 'Pilih cabang untuk menukar hadiah.'}
          </DialogDescription>
        </DialogHeader>

        <View className="rounded-lg bg-[#fffbeb] px-3 py-2">
          <Text className="text-sm text-stone-700">
            Fitur penukaran belum tersedia. Tombol ini masih dummy.
          </Text>
        </View>

        <DialogFooter className="flex-row gap-2">
          <Button variant="outline" className="flex-1" onPress={() => onOpenChange(false)}>
            <Text>Batal</Text>
          </Button>
          <Button className="flex-1 bg-[#e8a020]" onPress={onConfirm}>
            <Text className="text-white">Lanjutkan</Text>
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  );
}
