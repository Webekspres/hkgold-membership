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
import { formatBranchLocation } from '@/lib/format/format-branch-location';
import type { RewardBranchStockItem } from '@/types/reward';

type RewardRedeemDialogProps = {
  open: boolean;
  onOpenChange: (open: boolean) => void;
  branchStock: RewardBranchStockItem | null;
  isConfirming?: boolean;
  onConfirm: () => void;
};

export function RewardRedeemDialog({
  open,
  onOpenChange,
  branchStock,
  isConfirming = false,
  onConfirm,
}: RewardRedeemDialogProps) {
  return (
    <Dialog
      open={open}
      onOpenChange={(next) => {
        if (isConfirming) return;
        onOpenChange(next);
      }}>
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
            Poin akan ditahan dan stok direservasi. Tunjukkan QR ke kasir dalam 30 menit untuk
            menyelesaikan penukaran.
          </Text>
        </View>

        <DialogFooter className="flex-row gap-2">
          <Button
            variant="outline"
            className="flex-1"
            disabled={isConfirming}
            onPress={() => onOpenChange(false)}>
            <Text>Batal</Text>
          </Button>
          <Button
            className="flex-1 bg-[#e8a020]"
            disabled={!branchStock || isConfirming}
            onPress={onConfirm}>
            <Text className="text-white">{isConfirming ? 'Memproses...' : 'Lanjutkan'}</Text>
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  );
}
