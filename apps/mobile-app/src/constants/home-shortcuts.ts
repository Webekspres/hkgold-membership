import type { SymbolViewProps } from 'expo-symbols';

export type HomeShortcutHref = '/cms' | '/events';

export type HomeShortcut = {
  id: string;
  label: string;
  icon: SymbolViewProps['name'];
  href: HomeShortcutHref;
};

export const HOME_SHORTCUTS: HomeShortcut[] = [
  {
    id: 'event',
    label: 'Event',
    icon: { ios: 'calendar', android: 'event', web: 'event' },
    href: '/events',
  },
  {
    id: 'berita',
    label: 'Berita',
    icon: { ios: 'newspaper', android: 'article', web: 'article' },
    href: '/cms',
  },
  {
    id: 'cabang',
    label: 'Cabang',
    icon: { ios: 'mappin.and.ellipse', android: 'location_on', web: 'location_on' },
    href: '/cms',
  },
  {
    id: 'reward',
    label: 'Reward',
    icon: { ios: 'gift', android: 'redeem', web: 'redeem' },
    href: '/cms',
  },
];

export const CMS_HUB_ROUTE = '/cms' as const;
