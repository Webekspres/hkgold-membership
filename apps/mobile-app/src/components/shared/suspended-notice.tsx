import { View } from 'react-native';

import { Text } from '@/components/ui/text';
import { cn } from '@/lib/utils';

type SuspendedNoticeProps = {
  className?: string;
  /** Shorter copy for compact surfaces (e.g. reward detail above branch list). */
  compact?: boolean;
};

export function SuspendedNotice({ className, compact = false }: SuspendedNoticeProps) {
  return (
    <View
      className={cn(
        'rounded-xl border border-red-200 bg-red-50 px-3 py-3',
        className,
      )}>
      <Text className="text-sm font-semibold text-red-800">
        Akun ditangguhkan
      </Text>
      <Text variant="muted" className="mt-1 text-sm text-red-700/90">
        {compact
          ? 'Penukaran hadiah tidak tersedia. Hubungi admin.'
          : 'Akun Anda ditangguhkan. Penukaran hadiah tidak tersedia. Hubungi admin.'}
      </Text>
    </View>
  );
}
