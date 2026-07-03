import { useEffect, useState } from 'react';

import { formatRemainingTime } from '@/lib/format/format-remaining-time';

const UPDATE_INTERVAL_MS = 30_000;

export function useRemainingTime(expiresAt: string) {
  const [label, setLabel] = useState(() => formatRemainingTime(expiresAt));

  useEffect(() => {
    const update = () => {
      setLabel(formatRemainingTime(expiresAt));
    };

    update();
    const intervalId = setInterval(update, UPDATE_INTERVAL_MS);

    return () => {
      clearInterval(intervalId);
    };
  }, [expiresAt]);

  return label;
}
