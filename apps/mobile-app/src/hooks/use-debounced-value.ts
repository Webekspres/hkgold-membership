import { useEffect, useState } from 'react';

/** Debounce nilai (mis. search) sebelum dipakai query. */
export function useDebouncedValue<T>(value: T, delayMs: number): T {
  // Lazy init — hindari Hermes "Value not coercible to object" dengan primitive + React Compiler
  const [debounced, setDebounced] = useState(() => value);

  useEffect(() => {
    const timer = setTimeout(() => {
      setDebounced(() => value);
    }, delayMs);
    return () => clearTimeout(timer);
  }, [value, delayMs]);

  return debounced;
}
