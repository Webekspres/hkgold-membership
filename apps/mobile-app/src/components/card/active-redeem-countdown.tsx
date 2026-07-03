import { Text } from '@/components/ui/text';
import { useRemainingTime } from '@/hooks/use-remaining-time';

type ActiveRedeemCountdownProps = {
  expiresAt: string;
};

export function ActiveRedeemCountdown({ expiresAt }: ActiveRedeemCountdownProps) {
  const remainingLabel = useRemainingTime(expiresAt);

  return <Text className="text-sm font-medium text-amber-700">{remainingLabel}</Text>;
}
