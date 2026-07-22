import { Eye, EyeOff } from 'lucide-react-native';
import { useEffect, useState } from 'react';
import { Pressable, View } from 'react-native';

import {
  AuthField,
  AUTH_INPUT_CLASSNAME,
  AUTH_PLACEHOLDER_COLOR,
} from '@/components/auth/auth-field';
import { GoldButton } from '@/components/shared/gold-button';
import { Icon } from '@/components/ui/icon';
import { Input } from '@/components/ui/input';
import { OtpInput } from '@/components/ui/otp-input';
import { Text } from '@/components/ui/text';
import { openAdminWhatsapp } from '@/lib/admin-whatsapp';
import { toast } from '@/lib/sonner';
import {
  AuthApiError,
  resetPasswordWithOtp,
  sendForgotPasswordOtp,
} from '@/services/auth';

export type ForgotPasswordMode = 'public' | 'authenticated';

type Step = 'identity' | 'otp' | 'password';

type ForgotPasswordFlowProps = {
  mode: ForgotPasswordMode;
  onSuccess: () => void;
  onCancel?: () => void;
};

function secondsUntil(iso: string): number {
  return Math.max(0, Math.ceil((new Date(iso).getTime() - Date.now()) / 1000));
}

export function ForgotPasswordFlow({
  mode,
  onSuccess,
  onCancel,
}: ForgotPasswordFlowProps) {
  const [step, setStep] = useState<Step>(
    mode === 'authenticated' ? 'otp' : 'identity',
  );
  const [identifier, setIdentifier] = useState('');
  const [otp, setOtp] = useState('');
  const [newPassword, setNewPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [showNewPassword, setShowNewPassword] = useState(false);
  const [showConfirmPassword, setShowConfirmPassword] = useState(false);
  const [maskedPhone, setMaskedPhone] = useState<string | null>(null);
  const [resendAvailableAt, setResendAvailableAt] = useState<string | null>(
    null,
  );
  const [resendSeconds, setResendSeconds] = useState(0);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [waNotSet, setWaNotSet] = useState(false);

  useEffect(() => {
    if (!resendAvailableAt) {
      setResendSeconds(0);
      return;
    }
    const tick = () => setResendSeconds(secondsUntil(resendAvailableAt));
    tick();
    const id = setInterval(tick, 1000);
    return () => clearInterval(id);
  }, [resendAvailableAt]);

  async function handleSendOtp() {
    if (isSubmitting) return;

    if (mode === 'public') {
      const trimmed = identifier.trim();
      if (!trimmed) {
        toast.error('Email atau nomor HP wajib diisi', { duration: 4000 });
        return;
      }
      if (/^\d{4}-\d{4}$/.test(trimmed)) {
        toast.error('Gunakan email atau nomor HP, bukan nomor member', {
          duration: 4000,
        });
        return;
      }
    }

    setIsSubmitting(true);
    setWaNotSet(false);
    try {
      const result = await sendForgotPasswordOtp(
        mode === 'public' ? identifier.trim() : undefined,
      );
      setMaskedPhone(result.maskedPhone);
      setResendAvailableAt(result.resendAvailableAt);
      setStep('otp');
      toast.success('OTP berhasil dikirim via WhatsApp', { duration: 3000 });
    } catch (error) {
      const code = error instanceof AuthApiError ? error.code : undefined;
      if (code === 'WA_NOT_SET') {
        setWaNotSet(true);
        toast.error(
          error instanceof Error
            ? error.message
            : 'Kamu belum atur nomor WA. Hubungi admin',
          { duration: 5000 },
        );
      } else {
        toast.error(
          error instanceof Error ? error.message : 'Gagal mengirim OTP',
          { duration: 4000 },
        );
      }
    } finally {
      setIsSubmitting(false);
    }
  }

  async function handleVerifyAndContinue() {
    if (isSubmitting) return;
    if (!/^\d{6}$/.test(otp.trim())) {
      toast.error('Masukkan kode OTP 6 digit', { duration: 4000 });
      return;
    }
    setStep('password');
  }

  async function handleResetPassword() {
    if (isSubmitting) return;

    if (!newPassword || !confirmPassword) {
      toast.error('Password baru dan konfirmasi wajib diisi', {
        duration: 4000,
      });
      return;
    }
    if (newPassword.length < 8) {
      toast.error('Password baru minimal 8 karakter', { duration: 4000 });
      return;
    }
    if (newPassword !== confirmPassword) {
      toast.error('Konfirmasi password tidak sama', { duration: 4000 });
      return;
    }

    setIsSubmitting(true);
    try {
      await resetPasswordWithOtp({
        ...(mode === 'public' ? { identifier: identifier.trim() } : {}),
        otp: otp.trim(),
        newPassword,
      });
      toast.success('Password berhasil diubah', { duration: 3000 });
      onSuccess();
    } catch (error) {
      toast.error(
        error instanceof Error ? error.message : 'Gagal mengubah password',
        { duration: 4000 },
      );
    } finally {
      setIsSubmitting(false);
    }
  }

  return (
    <View className="gap-4">
      {step === 'identity' ? (
        <>
          <AuthField
            label="Email / Nomor HP"
            helperText="Masukkan email atau nomor HP terdaftar (bukan nomor member).">
            <Input
              className={AUTH_INPUT_CLASSNAME}
              placeholder="email@example.com atau 08…"
              placeholderTextColor={AUTH_PLACEHOLDER_COLOR}
              autoCapitalize="none"
              autoCorrect={false}
              keyboardType="email-address"
              value={identifier}
              onChangeText={setIdentifier}
              editable={!isSubmitting}
            />
          </AuthField>
          <GoldButton
            variant="filled"
            width="full"
            label={isSubmitting ? 'Mengirim…' : 'Kirim OTP WhatsApp'}
            onPress={() => {
              void handleSendOtp();
            }}
          />
        </>
      ) : null}

      {step === 'otp' ? (
        <>
          {mode === 'authenticated' && !maskedPhone ? (
            <Text variant="muted" className="text-center text-stone-600">
              OTP akan dikirim ke nomor WhatsApp terdaftar di akun Anda.
            </Text>
          ) : null}
          {maskedPhone ? (
            <Text variant="muted" className="text-center text-stone-600">
              Kode OTP dikirim ke {maskedPhone}
            </Text>
          ) : null}

          {mode === 'authenticated' && !maskedPhone ? (
            <GoldButton
              variant="filled"
              width="full"
              label={isSubmitting ? 'Mengirim…' : 'Kirim OTP WhatsApp'}
              onPress={() => {
                void handleSendOtp();
              }}
            />
          ) : (
            <>
              <AuthField label="Kode OTP">
                <OtpInput
                  value={otp}
                  onChangeText={setOtp}
                  editable={!isSubmitting}
                />
              </AuthField>
              <GoldButton
                variant="filled"
                width="full"
                label="Lanjut"
                onPress={() => {
                  void handleVerifyAndContinue();
                }}
              />
              <GoldButton
                variant="outline"
                width="full"
                label={
                  isSubmitting
                    ? 'Mengirim…'
                    : resendSeconds > 0
                      ? `Kirim ulang (${resendSeconds}s)`
                      : 'Kirim ulang OTP'
                }
                onPress={() => {
                  if (resendSeconds > 0 || isSubmitting) return;
                  void handleSendOtp();
                }}
              />
            </>
          )}
        </>
      ) : null}

      {step === 'password' ? (
        <>
          <AuthField label="Password Baru">
            <View className="relative">
              <Input
                className={`${AUTH_INPUT_CLASSNAME} pr-11`}
                placeholder="Minimal 8 karakter"
                placeholderTextColor={AUTH_PLACEHOLDER_COLOR}
                secureTextEntry={!showNewPassword}
                autoComplete="new-password"
                value={newPassword}
                onChangeText={setNewPassword}
                editable={!isSubmitting}
              />
              <Pressable
                onPress={() => setShowNewPassword((prev) => !prev)}
                className="absolute right-3 top-0 h-11 items-center justify-center"
                hitSlop={8}>
                <Icon
                  as={showNewPassword ? EyeOff : Eye}
                  size={18}
                  className="text-stone-500"
                />
              </Pressable>
            </View>
          </AuthField>
          <AuthField label="Konfirmasi Password Baru">
            <View className="relative">
              <Input
                className={`${AUTH_INPUT_CLASSNAME} pr-11`}
                placeholder="Ketik ulang password baru"
                placeholderTextColor={AUTH_PLACEHOLDER_COLOR}
                secureTextEntry={!showConfirmPassword}
                autoComplete="new-password"
                value={confirmPassword}
                onChangeText={setConfirmPassword}
                editable={!isSubmitting}
              />
              <Pressable
                onPress={() => setShowConfirmPassword((prev) => !prev)}
                className="absolute right-3 top-0 h-11 items-center justify-center"
                hitSlop={8}>
                <Icon
                  as={showConfirmPassword ? EyeOff : Eye}
                  size={18}
                  className="text-stone-500"
                />
              </Pressable>
            </View>
          </AuthField>
          <GoldButton
            variant="filled"
            width="full"
            label={isSubmitting ? 'Menyimpan…' : 'Simpan Password Baru'}
            onPress={() => {
              void handleResetPassword();
            }}
          />
        </>
      ) : null}

      {waNotSet ? (
        <View className="gap-2 rounded-lg border border-stone-200 bg-stone-50 p-3">
          <Text variant="small" className="text-center text-stone-700">
            Kamu belum atur nomor WA. Hubungi admin.
          </Text>
          <GoldButton
            variant="outline"
            width="full"
            label="Hubungi Admin"
            onPress={() => {
              void openAdminWhatsapp().catch(() => {
                toast.error('Tidak bisa membuka WhatsApp', { duration: 4000 });
              });
            }}
          />
        </View>
      ) : null}

      {onCancel ? (
        <Pressable
          onPress={onCancel}
          disabled={isSubmitting}
          className="items-center py-1 active:opacity-70">
          <Text variant="small" className="font-semibold text-[#c4841a]">
            {mode === 'authenticated' ? 'Kembali ke ganti password' : 'Batal'}
          </Text>
        </Pressable>
      ) : null}
    </View>
  );
}
