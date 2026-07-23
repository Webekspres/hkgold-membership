import { clsx, type ClassValue } from 'clsx';
import { extendTailwindMerge } from 'tailwind-merge';

const twMerge = extendTailwindMerge({
  extend: {
    classGroups: {
      'font-family': [
        'font-sans',
        'font-libre-baskerville',
        'font-libre-baskerville-medium',
        'font-libre-baskerville-semibold',
        'font-libre-baskerville-bold',
        'font-medium',
        'font-semibold',
        'font-bold',
        'font-extrabold',
      ],
    },
  },
});

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs));
}

/** Class Tailwind memakai Libre Baskerville (font-libre-baskerville*). */
export function usesSerifFont(...inputs: ClassValue[]): boolean {
  return /\bfont-libre-baskerville(?:-medium|-semibold|-bold)?\b/.test(clsx(inputs));
}
