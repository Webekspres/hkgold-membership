import { NativeTabs } from 'expo-router/unstable-native-tabs';

import {
  GOLD_TAB_INDICATOR,
  GOLD_TAB_RIPPLE,
  GOLD_TAB_SELECTED,
} from '@/config/brand';
import { Colors } from '@/config/theme';

export default function AppTabs() {
  const colors = Colors.light;

  return (
    <NativeTabs
      backgroundColor={colors.background}
      indicatorColor={GOLD_TAB_INDICATOR}
      rippleColor={GOLD_TAB_RIPPLE}
      tintColor={GOLD_TAB_SELECTED}
      iconColor={{
        default: colors.textSecondary,
        selected: GOLD_TAB_SELECTED,
      }}
      labelStyle={{
        default: { color: colors.textSecondary },
        selected: { color: GOLD_TAB_SELECTED, fontWeight: '600' },
      }}>
      <NativeTabs.Trigger name="index">
        <NativeTabs.Trigger.Icon
          sf={{ default: 'house', selected: 'house.fill' }}
          md="home"
        />
        <NativeTabs.Trigger.Label>Home</NativeTabs.Trigger.Label>
      </NativeTabs.Trigger>

      <NativeTabs.Trigger name="card">
        <NativeTabs.Trigger.Icon
          sf={{
            default: 'person.text.rectangle',
            selected: 'person.text.rectangle.fill',
          }}
          md="badge"
        />
        <NativeTabs.Trigger.Label>Card</NativeTabs.Trigger.Label>
      </NativeTabs.Trigger>

      <NativeTabs.Trigger name="reward">
        <NativeTabs.Trigger.Icon
          sf={{ default: 'gift', selected: 'gift.fill' }}
          md="redeem"
        />
        <NativeTabs.Trigger.Label>Reward</NativeTabs.Trigger.Label>
      </NativeTabs.Trigger>

      <NativeTabs.Trigger name="profile">
        <NativeTabs.Trigger.Icon
          sf={{ default: 'person', selected: 'person.fill' }}
          md="person"
        />
        <NativeTabs.Trigger.Label>Profil</NativeTabs.Trigger.Label>
      </NativeTabs.Trigger>
    </NativeTabs>
  );
}
