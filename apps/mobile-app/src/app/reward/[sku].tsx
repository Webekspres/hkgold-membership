import { useLocalSearchParams } from 'expo-router';

import { ComingSoonScreen } from '@/components/coming-soon-screen';

export default function RewardDetailScreen() {
  const { sku } = useLocalSearchParams<{ sku: string }>();

  const title = typeof sku === 'string' ? `Reward ${sku}` : 'Detail Reward';

  return <ComingSoonScreen title={title} />;
}
