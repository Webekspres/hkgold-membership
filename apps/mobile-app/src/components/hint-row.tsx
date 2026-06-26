import type { ReactNode } from 'react';
import { View } from 'react-native';

import { Text } from '@/components/ui/text';

type HintRowProps = {
  title?: string;
  hint?: ReactNode;
};

export function HintRow({ title = 'Try editing', hint = 'app/index.tsx' }: HintRowProps) {
  return (
    <View className="flex-row justify-between">
      <Text variant="small">{title}</Text>
      <View className="rounded-lg bg-accent px-2 py-0.5">
        <Text variant="muted">{hint}</Text>
      </View>
    </View>
  );
}
