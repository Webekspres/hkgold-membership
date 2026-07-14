import { QrCodeCard } from '@/components/card/qr-code-card';

type MemberQrCardProps = {
  memberNumber: string;
  onPressMemberNumber?: () => void;
};

export function MemberQrCard({ memberNumber, onPressMemberNumber }: MemberQrCardProps) {
  return (
    <QrCodeCard
      value={memberNumber}
      label={memberNumber}
      onPressLabel={onPressMemberNumber}
      copyAccessibilityLabel="Salin nomor member"
    />
  );
}
