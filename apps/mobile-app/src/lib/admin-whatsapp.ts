import { Linking } from 'react-native';

export function getAdminWhatsappNumber(): string {
  const raw = process.env.EXPO_PUBLIC_ADMIN_WHATSAPP ?? '6282258119788';
  return raw.replace(/\D/g, '');
}

export async function openAdminWhatsapp(): Promise<void> {
  const phone = getAdminWhatsappNumber();
  const url = `https://wa.me/${phone}`;
  await Linking.openURL(url);
}
