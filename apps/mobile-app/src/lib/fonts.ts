import {
  LibreBaskerville_400Regular,
  LibreBaskerville_500Medium,
  LibreBaskerville_600SemiBold,
  LibreBaskerville_700Bold,
} from '@expo-google-fonts/libre-baskerville';
import {
  Rubik_400Regular,
  Rubik_500Medium,
  Rubik_600SemiBold,
  Rubik_700Bold,
  useFonts,
} from '@expo-google-fonts/rubik';

export const FONT = {
  sans: {
    regular: 'Rubik_400Regular',
    medium: 'Rubik_500Medium',
    semibold: 'Rubik_600SemiBold',
    bold: 'Rubik_700Bold',
  },
  serif: {
    regular: 'LibreBaskerville_400Regular',
    medium: 'LibreBaskerville_500Medium',
    semibold: 'LibreBaskerville_600SemiBold',
    bold: 'LibreBaskerville_700Bold',
  },
} as const;

export function useAppFonts(): boolean {
  const [loaded] = useFonts({
    Rubik_400Regular,
    Rubik_500Medium,
    Rubik_600SemiBold,
    Rubik_700Bold,
    LibreBaskerville_400Regular,
    LibreBaskerville_500Medium,
    LibreBaskerville_600SemiBold,
    LibreBaskerville_700Bold,
  });

  return loaded;
}
