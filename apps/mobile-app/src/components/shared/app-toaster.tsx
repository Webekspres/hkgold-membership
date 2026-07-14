import { Platform } from 'react-native';
import { Check } from 'lucide-react-native';

import { BottomTabInset } from '@/config/theme';
import { Toaster as SonnerToaster } from '@/lib/sonner';

export function AppToaster() {
  return (
    <SonnerToaster
      theme="light"
      position="bottom-center"
      offset={BottomTabInset + 12}
      visibleToasts={3}
      gap={10}
      closeButton
      swipeToDismissDirection="up"
      positionerStyle={{
        alignItems: 'flex-end',
        width: '100%',
        paddingHorizontal: 16,
      }}
      toastOptions={{
        style: {
          backgroundColor: '#ffffff',
          borderWidth: 1,
          borderColor: '#e7e5e4',
          borderRadius: 14,
          paddingHorizontal: 14,
          paddingVertical: 12,
          minWidth: 260,
          maxWidth: 320,
          marginHorizontal: 0,
          ...Platform.select({
            android: { elevation: 6 },
            ios: {
              shadowColor: '#1c1917',
              shadowOpacity: 0.12,
              shadowRadius: 10,
              shadowOffset: { width: 0, height: 4 },
            },
          }),
        },
        toastContentStyle: {
          alignItems: 'center',
        },
        textContainerStyle: {
          flex: 1,
          flexShrink: 1,
          minWidth: 0,
        },
        titleStyle: {
          color: '#1c1917',
          fontSize: 14,
          fontWeight: '600',
          lineHeight: 20,
        },
        descriptionStyle: {
          color: '#78716c',
          fontSize: 13,
          lineHeight: 18,
          marginTop: 2,
        },
        closeButtonStyle: {
          backgroundColor: '#f5f5f4',
          borderRadius: 999,
          marginLeft: 8,
        },
      }}
      icons={{
        success: <Check size={18} color="#b45309" />,
      }}
    />
  );
}
