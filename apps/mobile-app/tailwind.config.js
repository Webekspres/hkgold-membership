const { hairlineWidth } = require('nativewind/theme');

const FONT = {
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
};

/** @type {import('tailwindcss').Config} */
module.exports = {
  darkMode: 'class',
  content: ['./src/**/*.{js,jsx,ts,tsx}'],
  presets: [require('nativewind/preset')],
  theme: {
    extend: {
      colors: {
        border: 'hsl(var(--border))',
        input: 'hsl(var(--input))',
        ring: 'hsl(var(--ring))',
        background: 'hsl(var(--background))',
        foreground: 'hsl(var(--foreground))',
        primary: {
          DEFAULT: 'hsl(var(--primary))',
          foreground: 'hsl(var(--primary-foreground))',
        },
        secondary: {
          DEFAULT: 'hsl(var(--secondary))',
          foreground: 'hsl(var(--secondary-foreground))',
        },
        destructive: {
          DEFAULT: 'hsl(var(--destructive))',
          foreground: 'hsl(var(--destructive-foreground))',
        },
        muted: {
          DEFAULT: 'hsl(var(--muted))',
          foreground: 'hsl(var(--muted-foreground))',
        },
        accent: {
          DEFAULT: 'hsl(var(--accent))',
          foreground: 'hsl(var(--accent-foreground))',
        },
        popover: {
          DEFAULT: 'hsl(var(--popover))',
          foreground: 'hsl(var(--popover-foreground))',
        },
        card: {
          DEFAULT: 'hsl(var(--card))',
          foreground: 'hsl(var(--card-foreground))',
        },
      },
      borderRadius: {
        lg: 'var(--radius)',
        md: 'calc(var(--radius) - 2px)',
        sm: 'calc(var(--radius) - 4px)',
      },
      borderWidth: {
        hairline: hairlineWidth(),
      },
      keyframes: {
        'accordion-down': {
          from: { height: '0' },
          to: { height: 'var(--radix-accordion-content-height)' },
        },
        'accordion-up': {
          from: { height: 'var(--radix-accordion-content-height)' },
          to: { height: '0' },
        },
      },
      animation: {
        'accordion-down': 'accordion-down 0.2s ease-out',
        'accordion-up': 'accordion-up 0.2s ease-out',
      },
      fontFamily: {
        sans: [FONT.sans.regular],
        'libre-baskerville': [FONT.serif.regular],
      },
    },
  },
  plugins: [
    require('tailwindcss-animate'),
    function ({ addUtilities }) {
      addUtilities({
        '.font-sans': {
          fontFamily: FONT.sans.regular,
          fontWeight: '400',
        },
        '.font-libre-baskerville': {
          fontFamily: FONT.serif.regular,
          fontWeight: '400',
        },
        '.font-libre-baskerville-medium': {
          fontFamily: FONT.serif.medium,
          fontWeight: '400',
        },
        '.font-libre-baskerville-semibold': {
          fontFamily: FONT.serif.semibold,
          fontWeight: '400',
        },
        '.font-libre-baskerville-bold': {
          fontFamily: FONT.serif.bold,
          fontWeight: '400',
        },
        '.font-medium': {
          fontFamily: FONT.sans.medium,
          fontWeight: '400',
        },
        '.font-semibold': {
          fontFamily: FONT.sans.semibold,
          fontWeight: '400',
        },
        '.font-bold': {
          fontFamily: FONT.sans.bold,
          fontWeight: '400',
        },
        '.font-extrabold': {
          fontFamily: FONT.sans.bold,
          fontWeight: '400',
        },
      });
    },
  ],
};
