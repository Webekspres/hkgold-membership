import { useQueryClient } from "@tanstack/react-query";
import { router, Stack } from "expo-router";
import { useEffect, useState } from "react";
import { View } from "react-native";

import { AuthCardHeader } from "@/components/auth/auth-card-header";
import {
  AuthField,
  AUTH_INPUT_CLASSNAME,
  AUTH_PLACEHOLDER_COLOR,
} from "@/components/auth/auth-field";
import { AuthFooterLink } from "@/components/auth/auth-footer-link";
import { AuthScreenShell } from "@/components/auth/auth-screen-shell";
import { GoldButton } from "@/components/shared/gold-button";
import { CardContent } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { OtpInput } from "@/components/ui/otp-input";
import { Text } from "@/components/ui/text";
import { useAuth } from "@/hooks/use-auth";
import {
  CHANGE_PHONE_STATUS_QUERY_KEY,
  useChangePhoneStatus,
} from "@/hooks/use-change-phone-status";
import { toast } from "@/lib/sonner";
import { openAdminWhatsapp } from "@/lib/admin-whatsapp";
import {
  cancelChangePhone,
  ChangePhoneApiError,
  confirmChangePhone,
  requestAdminAssistedChangePhone,
  sendChangePhoneOtpNew,
  sendChangePhoneOtpOld,
  verifyChangePhoneOtpOld,
} from "@/services/change-phone";

type Step =
  | "loading"
  | "status"
  | "otp-old"
  | "new-phone"
  | "otp-new"
  | "rejected";

/** Konfigurasi durasi timer (dapat diubah dengan mudah) */
const DEFAULT_OTP_EXPIRY_SECONDS = 300; // 5 menit masa berlaku token OTP
const DEFAULT_RESEND_COOLDOWN_SECONDS = 30; // 30 detik cooldown kirim ulang OTP

function secondsUntil(iso: string): number {
  return Math.max(0, Math.ceil((new Date(iso).getTime() - Date.now()) / 1000));
}

function formatMMSS(totalSeconds: number): string {
  const mins = Math.floor(Math.max(0, totalSeconds) / 60);
  const secs = Math.max(0, totalSeconds) % 60;
  return `${String(mins).padStart(2, "0")}:${String(secs).padStart(2, "0")}`;
}

export default function ChangePhoneScreen() {
  const { logout } = useAuth();
  const queryClient = useQueryClient();
  const { data: status, isLoading, refetch } = useChangePhoneStatus();

  const [step, setStep] = useState<Step>("loading");
  const [lostOldPhone, setLostOldPhone] = useState(false);
  const [challenge, setChallenge] = useState<string | null>(null);
  const [otp, setOtp] = useState("");
  const [newPhone, setNewPhone] = useState("");
  const [reason, setReason] = useState("");
  const [maskedPhone, setMaskedPhone] = useState<string | null>(null);

  const [otpExpiresAt, setOtpExpiresAt] = useState<string | null>(null);
  const [otpExpirySeconds, setOtpExpirySeconds] = useState(0);

  const [resendAvailableAt, setResendAvailableAt] = useState<string | null>(
    null,
  );
  const [resendSeconds, setResendSeconds] = useState(0);
  const [isSubmitting, setIsSubmitting] = useState(false);

  useEffect(() => {
    if (isLoading) {
      setStep("loading");
      return;
    }
    if (status?.status === "PENDING") {
      setStep("status");
      return;
    }
    if (status?.status === "REJECTED" && step === "loading") {
      setStep("rejected");
      return;
    }
    if (step === "loading") {
      setStep("otp-old");
    }
  }, [isLoading, status, step]);

  useEffect(() => {
    if (!otpExpiresAt && !resendAvailableAt) {
      setOtpExpirySeconds(0);
      setResendSeconds(0);
      return;
    }
    const tick = () => {
      if (otpExpiresAt) {
        setOtpExpirySeconds(secondsUntil(otpExpiresAt));
      }
      if (resendAvailableAt) {
        setResendSeconds(secondsUntil(resendAvailableAt));
      }
    };
    tick();
    const id = setInterval(tick, 1000);
    return () => clearInterval(id);
  }, [otpExpiresAt, resendAvailableAt]);

  async function handleForceLogout(message: string) {
    toast.success(message, { duration: 4000 });
    await logout();
    router.replace("/login");
  }

  function startOtpTimers(
    resendIso?: string | null,
    expiresIso?: string | null,
  ) {
    const now = Date.now();
    const expiryTime =
      expiresIso ??
      new Date(now + DEFAULT_OTP_EXPIRY_SECONDS * 1000).toISOString();
    const resendLength =
      resendIso ??
      new Date(now + DEFAULT_RESEND_COOLDOWN_SECONDS * 1000).toISOString();

    setOtpExpiresAt(expiryTime);
    setResendAvailableAt(resendLength);
    setOtpExpirySeconds(secondsUntil(expiryTime));
    setResendSeconds(secondsUntil(resendLength));
  }

  async function handleSendOtpOld() {
    if (isSubmitting) return;
    setIsSubmitting(true);
    try {
      const result = await sendChangePhoneOtpOld();
      setMaskedPhone(result.maskedPhone);
      startOtpTimers(result.resendAvailableAt, result.expiresAt);
      setOtp("");
      setStep("otp-old");
      toast.success("OTP dikirim ke nomor lama", { duration: 3000 });
    } catch (error) {
      toast.error(
        error instanceof Error ? error.message : "Gagal mengirim OTP",
        { duration: 4000 },
      );
    } finally {
      setIsSubmitting(false);
    }
  }

  async function handleVerifyOtpOld() {
    if (isSubmitting) return;
    if (otpExpirySeconds <= 0) {
      toast.error("Kode OTP telah kadaluarsa. Silakan kirim ulang OTP", {
        duration: 4000,
      });
      return;
    }
    if (!/^\d{6}$/.test(otp.trim())) {
      toast.error("Masukkan kode OTP 6 digit", { duration: 4000 });
      return;
    }
    setIsSubmitting(true);
    try {
      const result = await verifyChangePhoneOtpOld(otp.trim());
      setChallenge(result.challenge);
      setLostOldPhone(false);
      setMaskedPhone(null);
      setOtp("");
      setStep("new-phone");
    } catch (error) {
      toast.error(error instanceof Error ? error.message : "OTP tidak valid", {
        duration: 4000,
      });
    } finally {
      setIsSubmitting(false);
    }
  }

  function normalizeNewPhoneInput(raw: string): string {
    let phone = raw.trim();
    if (phone.startsWith("0")) {
      phone = `+62${phone.slice(1)}`;
    } else if (phone.startsWith("62")) {
      phone = `+${phone}`;
    } else if (!phone.startsWith("+62")) {
      phone = `+62${phone}`;
    }
    return phone;
  }

  async function handleSendOtpNew() {
    if (isSubmitting || lostOldPhone) return;
    const phone = normalizeNewPhoneInput(newPhone);
    if (!newPhone.trim()) {
      toast.error("Nomor baru wajib diisi", { duration: 4000 });
      return;
    }
    if (!challenge) {
      toast.error("Verifikasi nomor lama dulu", { duration: 4000 });
      return;
    }

    setIsSubmitting(true);
    try {
      const result = await sendChangePhoneOtpNew({
        newPhone: phone,
        challenge,
      });
      setMaskedPhone(result.maskedPhone);
      startOtpTimers(result.resendAvailableAt, result.expiresAt);
      setOtp("");
      setStep("otp-new");
      toast.success("OTP dikirim ke nomor baru", { duration: 3000 });
    } catch (error) {
      toast.error(
        error instanceof Error ? error.message : "Gagal mengirim OTP",
        { duration: 4000 },
      );
    } finally {
      setIsSubmitting(false);
    }
  }

  async function handleRequestAdmin() {
    if (isSubmitting || !lostOldPhone) return;
    const phone = normalizeNewPhoneInput(newPhone);
    if (!newPhone.trim()) {
      toast.error("Nomor baru wajib diisi", { duration: 4000 });
      return;
    }
    if (!reason.trim()) {
      toast.error("Alasan wajib diisi", { duration: 4000 });
      return;
    }

    setIsSubmitting(true);
    try {
      await requestAdminAssistedChangePhone({
        newPhone: phone,
        reason: reason.trim(),
      });
      await queryClient.invalidateQueries({
        queryKey: CHANGE_PHONE_STATUS_QUERY_KEY,
      });
      toast.success("Permintaan menunggu verifikasi admin", { duration: 4000 });
      setStep("status");
      await refetch();
    } catch (error) {
      toast.error(
        error instanceof Error ? error.message : "Gagal mengirim permintaan",
        { duration: 4000 },
      );
    } finally {
      setIsSubmitting(false);
    }
  }

  async function handleConfirm() {
    if (isSubmitting) return;
    if (otpExpirySeconds <= 0) {
      toast.error("Kode OTP telah kadaluarsa. Silakan kirim ulang OTP", {
        duration: 4000,
      });
      return;
    }
    if (!/^\d{6}$/.test(otp.trim())) {
      toast.error("Masukkan kode OTP 6 digit", { duration: 4000 });
      return;
    }
    setIsSubmitting(true);
    try {
      await confirmChangePhone(otp.trim());
      await queryClient.invalidateQueries({
        queryKey: CHANGE_PHONE_STATUS_QUERY_KEY,
      });
      await handleForceLogout(
        "Nomor HP berhasil diganti. Silakan login ulang.",
      );
    } catch (error) {
      toast.error(
        error instanceof Error ? error.message : "Gagal mengonfirmasi",
        { duration: 4000 },
      );
    } finally {
      setIsSubmitting(false);
    }
  }

  async function handleCancel() {
    if (isSubmitting) return;
    setIsSubmitting(true);
    try {
      await cancelChangePhone();
      toast.success("Permintaan dibatalkan", { duration: 3000 });
      await queryClient.invalidateQueries({
        queryKey: CHANGE_PHONE_STATUS_QUERY_KEY,
      });
      setLostOldPhone(false);
      setChallenge(null);
      setMaskedPhone(null);
      setNewPhone("");
      setReason("");
      setOtp("");
      setOtpExpiresAt(null);
      setResendAvailableAt(null);
      setStep("otp-old");
      await refetch();
    } catch (error) {
      toast.error(
        error instanceof ChangePhoneApiError
          ? error.message
          : "Gagal membatalkan",
        { duration: 4000 },
      );
    } finally {
      setIsSubmitting(false);
    }
  }

  function startLostOldPath() {
    setLostOldPhone(true);
    setChallenge(null);
    setMaskedPhone(null);
    setOtp("");
    setOtpExpiresAt(null);
    setResendAvailableAt(null);
    setStep("new-phone");
  }

  function startFresh() {
    setLostOldPhone(false);
    setChallenge(null);
    setMaskedPhone(null);
    setNewPhone("");
    setReason("");
    setOtp("");
    setOtpExpiresAt(null);
    setResendAvailableAt(null);
    setStep("otp-old");
  }

  return (
    <>
      <Stack.Screen
        options={{
          title: "",
          headerBackTitle: "Kembali",
          headerShown: true,
          headerTransparent: true,
          headerTintColor: "#57534e",
        }}
      />
      <AuthScreenShell scrollable>
        <AuthCardHeader title="Ganti Nomor HP" />
        <CardContent className="gap-4">
          {step === "loading" ? (
            <Text variant="muted" className="text-center text-stone-600">
              Memuat…
            </Text>
          ) : null}

          {step === "status" && status?.status === "PENDING" ? (
            <View className="gap-3">
              <View className="rounded-lg border border-amber-200 bg-amber-50 p-3">
                <Text className="text-base font-semibold text-amber-900">
                  Menunggu verifikasi admin
                </Text>
                <Text className="mt-1 text-sm text-amber-800">
                  Nomor baru: {status.newPhoneNumber}
                </Text>
                {status.reason ? (
                  <Text className="mt-0.5 text-sm text-amber-800">
                    Alasan: {status.reason}
                  </Text>
                ) : null}
                <Text className="mt-2 text-xs text-amber-700">
                  Hubungi admin via WhatsApp atau datang ke toko untuk verifikasi
                  identitas. Redeem dan ganti password dikunci sampai selesai.
                </Text>
              </View>
              <GoldButton
                variant="filled"
                label="Hubungi Admin via WhatsApp"
                onPress={() => void openAdminWhatsapp()}
                width="full"
              />
              <GoldButton
                variant="outline"
                label={isSubmitting ? "Membatalkan…" : "Batalkan Permintaan"}
                disabled={isSubmitting}
                onPress={() => void handleCancel()}
                width="full"
              />
            </View>
          ) : null}

          {step === "rejected" && status?.status === "REJECTED" ? (
            <View className="gap-3">
              <View className="rounded-lg border border-red-200 bg-red-50 p-3">
                <Text className="text-base font-semibold text-red-700">
                  Permintaan Ditolak
                </Text>
                {status.actionNotes ? (
                  <Text className="mt-1 text-sm text-red-600">
                    Catatan admin: {status.actionNotes}
                  </Text>
                ) : null}
              </View>
              <GoldButton
                variant="filled"
                label="Ajukan Lagi"
                onPress={startFresh}
                width="full"
              />
            </View>
          ) : null}

          {step === "otp-old" ? (
            <>
              {!maskedPhone ? (
                <>
                  <Text variant="muted" className="text-center text-stone-600">
                    Kami akan kirim kode OTP ke nomor WhatsApp lama yang terdaftar di akun Anda.
                  </Text>
                  <GoldButton
                    variant="filled"
                    label={isSubmitting ? "Mengirim…" : "Kirim OTP ke Nomor Lama"}
                    disabled={isSubmitting}
                    onPress={() => void handleSendOtpOld()}
                    width="full"
                  />
                </>
              ) : (
                <>
                  <AuthField
                    label="Kode OTP (Nomor Lama)"
                    helperText={
                      otpExpirySeconds > 0
                        ? `Dikirim ke ${maskedPhone} • Berlaku: ${formatMMSS(otpExpirySeconds)}`
                        : "Kode OTP telah kadaluarsa. Silakan kirim ulang."
                    }
                  >
                    <OtpInput
                      value={otp}
                      onChangeText={setOtp}
                      editable={!isSubmitting && otpExpirySeconds > 0}
                    />
                  </AuthField>
                  <GoldButton
                    variant="filled"
                    label={isSubmitting ? "Memverifikasi…" : "Verifikasi OTP"}
                    disabled={isSubmitting || otpExpirySeconds <= 0}
                    onPress={() => void handleVerifyOtpOld()}
                    width="full"
                  />
                  <GoldButton
                    variant="outline"
                    width="full"
                    disabled={isSubmitting || resendSeconds > 0}
                    label={
                      isSubmitting
                        ? "Mengirim…"
                        : resendSeconds > 0
                          ? `Kirim ulang (${resendSeconds}s)`
                          : "Kirim ulang OTP"
                    }
                    onPress={() => {
                      if (resendSeconds > 0 || isSubmitting) return;
                      void handleSendOtpOld();
                    }}
                  />
                </>
              )}
              <AuthFooterLink
                linkText="Nomor lama tidak bisa diakses?"
                onPress={startLostOldPath}
              />
            </>
          ) : null}

          {step === "new-phone" ? (
            <>
              <Text variant="muted" className="text-center text-stone-600">
                {lostOldPhone
                  ? "Nomor lama tidak aktif/hilang. Masukkan nomor HP baru dan alasan pengajuan."
                  : "Masukkan nomor HP baru Anda."}
              </Text>
              <AuthField label="Nomor HP Baru">
                <View className="flex-row items-center rounded-lg border border-stone-300 bg-white overflow-hidden">
                  <View className="flex-row items-center border-r border-stone-200 bg-stone-50 px-3.5 py-2.5">
                    <Text className="text-sm font-semibold text-stone-700">
                      +62
                    </Text>
                  </View>
                  <Input
                    className="h-11 flex-1 border-0 bg-transparent px-3 text-stone-700"
                    placeholder="8xxxxxxxxxx"
                    placeholderTextColor={AUTH_PLACEHOLDER_COLOR}
                    keyboardType="phone-pad"
                    value={newPhone}
                    onChangeText={setNewPhone}
                    editable={!isSubmitting}
                  />
                </View>
              </AuthField>
              {lostOldPhone ? (
                <AuthField label="Alasan Ganti Nomor">
                  <Input
                    className={AUTH_INPUT_CLASSNAME}
                    placeholder="Contoh: SIM card hilang / nomor mati"
                    placeholderTextColor={AUTH_PLACEHOLDER_COLOR}
                    value={reason}
                    onChangeText={setReason}
                    editable={!isSubmitting}
                  />
                </AuthField>
              ) : null}
              <GoldButton
                variant="filled"
                label={
                  isSubmitting
                    ? "Mengirim…"
                    : lostOldPhone
                      ? "Kirim Permintaan ke Admin"
                      : "Kirim OTP ke Nomor Baru"
                }
                disabled={isSubmitting}
                onPress={() =>
                  void (lostOldPhone ? handleRequestAdmin() : handleSendOtpNew())
                }
                width="full"
              />
              {!lostOldPhone ? (
                <AuthFooterLink
                  linkText="Nomor lama tidak bisa diakses?"
                  onPress={startLostOldPath}
                />
              ) : (
                <AuthFooterLink
                  linkText="Kembali ke verifikasi nomor lama"
                  onPress={startFresh}
                />
              )}
            </>
          ) : null}

          {step === "otp-new" && !lostOldPhone ? (
            <>
              <Text variant="muted" className="text-center text-stone-600">
                Masukkan kode OTP 6 digit yang dikirim ke nomor HP baru Anda.
              </Text>
              <AuthField
                label="Kode OTP (Nomor Baru)"
                helperText={
                  otpExpirySeconds > 0
                    ? `Dikirim ke ${maskedPhone ?? "nomor baru"} • Berlaku: ${formatMMSS(otpExpirySeconds)}`
                    : "Kode OTP telah kadaluarsa. Silakan kirim ulang."
                }
              >
                <OtpInput
                  value={otp}
                  onChangeText={setOtp}
                  editable={!isSubmitting && otpExpirySeconds > 0}
                />
              </AuthField>
              <GoldButton
                variant="filled"
                label={isSubmitting ? "Mengonfirmasi…" : "Konfirmasi Nomor Baru"}
                disabled={isSubmitting || otpExpirySeconds <= 0}
                onPress={() => void handleConfirm()}
                width="full"
              />
              <GoldButton
                variant="outline"
                width="full"
                disabled={isSubmitting || resendSeconds > 0}
                label={
                  isSubmitting
                    ? "Mengirim…"
                    : resendSeconds > 0
                      ? `Kirim ulang (${resendSeconds}s)`
                      : "Kirim ulang OTP"
                }
                onPress={() => {
                  if (resendSeconds > 0 || isSubmitting) return;
                  void handleSendOtpNew();
                }}
              />
            </>
          ) : null}
        </CardContent>
      </AuthScreenShell>
    </>
  );
}
