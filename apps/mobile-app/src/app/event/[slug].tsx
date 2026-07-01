import { useLocalSearchParams } from 'expo-router';

import { ComingSoonScreen } from '@/components/coming-soon-screen';

export default function EventDetailScreen() {
  const { slug } = useLocalSearchParams<{ slug: string }>();

  const title =
    typeof slug === 'string'
      ? slug
          .split('-')
          .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
          .join(' ')
      : 'Detail Event';

  return <ComingSoonScreen title={title} />;
}
