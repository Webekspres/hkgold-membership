import * as ImagePicker from "expo-image-picker";
import { Stack, router } from "expo-router";
import dayjs from "dayjs";
import { Camera } from "lucide-react-native";
import { useEffect, useState } from "react";
import { Pressable, View } from "react-native";
import { Dropdown } from "react-native-element-dropdown";
import DateTimePicker, {
  useDefaultStyles,
  type DateType,
} from "react-native-ui-datepicker";

import {
  AuthField,
  AUTH_INPUT_CLASSNAME,
  AUTH_PLACEHOLDER_COLOR,
} from "@/components/auth/auth-field";
import { AuthScreenShell } from "@/components/auth/auth-screen-shell";
import { GoldButton } from "@/components/shared/gold-button";
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";
import { Icon } from "@/components/ui/icon";
import { Input } from "@/components/ui/input";
import { Text } from "@/components/ui/text";
import { useMyProfile } from "@/hooks/use-my-profile";
import {
  useUpdateMyProfile,
  useUploadMyAvatar,
} from "@/hooks/use-update-my-profile";
import { toast } from "@/lib/sonner";
import {
  getAddressCascadeOptions,
  getNameInitials,
  parseDateOnly,
  toDateOnlyString,
} from "@/services/member";
import type {
  AddressCascadeLevel,
  AddressCascadeOption,
  MemberGender,
  MemberProfile,
} from "@/types/member";

const GENDER_OPTIONS: { label: string; value: MemberGender }[] = [
  { label: "Laki-laki", value: "MALE" },
  { label: "Perempuan", value: "FEMALE" },
];

const MAX_AVATAR_BYTES = 5 * 1024 * 1024;
const ALLOWED_MIME = new Set([
  "image/jpeg",
  "image/jpg",
  "image/png",
  "image/webp",
]);

type PendingAvatar = {
  uri: string;
  mimeType?: string | null;
  fileName?: string | null;
};

type AddressDropdownProps = {
  label: string;
  placeholder: string;
  options: AddressCascadeOption[];
  value: number | null;
  disabled?: boolean;
  loading?: boolean;
  onChange: (value: number) => void;
};

function AddressDropdown({
  label,
  placeholder,
  options,
  value,
  disabled = false,
  loading = false,
  onChange,
}: AddressDropdownProps) {
  return (
    <AuthField label={label}>
      <Dropdown
        style={{
          height: 44,
          borderWidth: 1,
          borderColor: "#d6d3d1",
          borderRadius: 8,
          paddingHorizontal: 12,
          backgroundColor: disabled ? "#f5f5f4" : "#ffffff",
          opacity: disabled ? 0.7 : 1,
        }}
        containerStyle={{
          borderRadius: 8,
          borderWidth: 1,
          borderColor: "#e7e5e4",
          overflow: "hidden",
        }}
        data={options}
        labelField="label"
        valueField="id"
        placeholder={loading ? "Memuat..." : placeholder}
        search
        searchPlaceholder={`Cari ${label.toLowerCase()}...`}
        value={value}
        disable={disabled || loading}
        onChange={(item) => onChange(item.id)}
        placeholderStyle={{ fontSize: 14, color: "#a8a29e" }}
        selectedTextStyle={{ fontSize: 14, color: "#44403c" }}
        inputSearchStyle={{
          height: 40,
          fontSize: 14,
          borderRadius: 6,
          borderColor: "#d6d3d1",
          color: "#44403c",
        }}
      />
    </AuthField>
  );
}

type ProfileEditFormProps = {
  profile: MemberProfile;
  card: {
    avatarUri?: string;
    avatarFallback: string;
  } | null;
};

function ProfileEditForm({ profile, card }: ProfileEditFormProps) {
  const updateProfile = useUpdateMyProfile();
  const uploadAvatar = useUploadMyAvatar();
  const dateStyles = useDefaultStyles();

  const [fullName, setFullName] = useState(profile.user.fullName);
  const [birthDate, setBirthDate] = useState<Date | null>(
    parseDateOnly(profile.birthDate),
  );
  const [showDatePicker, setShowDatePicker] = useState(false);
  const [gender, setGender] = useState<MemberGender | null>(profile.gender);
  const [street, setStreet] = useState(profile.address?.street ?? "");
  const [provinceId, setProvinceId] = useState<number | null>(
    profile.address?.region.provinceId ?? null,
  );
  const [cityId, setCityId] = useState<number | null>(
    profile.address?.region.cityId ?? null,
  );
  const [subDistrictId, setSubDistrictId] = useState<number | null>(
    profile.address?.region.subDistrictId ?? null,
  );
  const [villageId, setVillageId] = useState<number | null>(
    profile.address?.region.villageId ?? null,
  );
  const [postalCodeId, setPostalCodeId] = useState<number | null>(
    profile.address?.postalCodeId ?? null,
  );
  const [provinceOptions, setProvinceOptions] = useState<AddressCascadeOption[]>([]);
  const [cityOptions, setCityOptions] = useState<AddressCascadeOption[]>([]);
  const [subDistrictOptions, setSubDistrictOptions] = useState<AddressCascadeOption[]>([]);
  const [villageOptions, setVillageOptions] = useState<AddressCascadeOption[]>([]);
  const [postalCodeOptions, setPostalCodeOptions] = useState<AddressCascadeOption[]>([]);
  const [pendingAvatar, setPendingAvatar] = useState<PendingAvatar | null>(null);
  const [loadingAddressLevel, setLoadingAddressLevel] =
    useState<AddressCascadeLevel | null>("province");

  useEffect(() => {
    let cancelled = false;
    const region = profile.address?.region;

    void (async () => {
      try {
        const [provinces, cities, subDistricts, villages, postalCodes] =
          await Promise.all([
            getAddressCascadeOptions("province"),
            region
              ? getAddressCascadeOptions("city", region.provinceId)
              : Promise.resolve([]),
            region
              ? getAddressCascadeOptions("subDistrict", region.cityId)
              : Promise.resolve([]),
            region
              ? getAddressCascadeOptions("village", region.subDistrictId)
              : Promise.resolve([]),
            region
              ? getAddressCascadeOptions("postalCode", region.subDistrictId)
              : Promise.resolve([]),
          ]);

        if (cancelled) return;
        setProvinceOptions(provinces);
        setCityOptions(cities);
        setSubDistrictOptions(subDistricts);
        setVillageOptions(villages);
        setPostalCodeOptions(postalCodes);
      } catch {
        if (!cancelled) {
          toast.error("Gagal memuat pilihan alamat", { duration: 3000 });
        }
      } finally {
        if (!cancelled) setLoadingAddressLevel(null);
      }
    })();

    return () => {
      cancelled = true;
    };
  }, [profile]);

  async function loadAddressOptions(
    level: AddressCascadeLevel,
    parentId: number,
    setter: (options: AddressCascadeOption[]) => void,
  ) {
    setLoadingAddressLevel(level);
    try {
      setter(await getAddressCascadeOptions(level, parentId));
    } catch {
      toast.error("Gagal memuat pilihan alamat", { duration: 3000 });
    } finally {
      setLoadingAddressLevel(null);
    }
  }

  function handleProvinceChange(nextProvinceId: number) {
    setProvinceId(nextProvinceId);
    setCityId(null);
    setSubDistrictId(null);
    setVillageId(null);
    setPostalCodeId(null);
    setCityOptions([]);
    setSubDistrictOptions([]);
    setVillageOptions([]);
    setPostalCodeOptions([]);
    void loadAddressOptions("city", nextProvinceId, setCityOptions);
  }

  function handleCityChange(nextCityId: number) {
    setCityId(nextCityId);
    setSubDistrictId(null);
    setVillageId(null);
    setPostalCodeId(null);
    setSubDistrictOptions([]);
    setVillageOptions([]);
    setPostalCodeOptions([]);
    void loadAddressOptions("subDistrict", nextCityId, setSubDistrictOptions);
  }

  function handleSubDistrictChange(nextSubDistrictId: number) {
    setSubDistrictId(nextSubDistrictId);
    setVillageId(null);
    setPostalCodeId(null);
    setVillageOptions([]);
    setPostalCodeOptions([]);
    setLoadingAddressLevel("village");
    void Promise.all([
      getAddressCascadeOptions("village", nextSubDistrictId),
      getAddressCascadeOptions("postalCode", nextSubDistrictId),
    ])
      .then(([villages, postalCodes]) => {
        setVillageOptions(villages);
        setPostalCodeOptions(postalCodes);
      })
      .catch(() => {
        toast.error("Gagal memuat kelurahan dan kode pos", { duration: 3000 });
      })
      .finally(() => setLoadingAddressLevel(null));
  }

  function handleVillageChange(nextVillageId: number) {
    setVillageId(nextVillageId);
    setPostalCodeId(null);
  }

  const avatarUri = pendingAvatar?.uri ?? card?.avatarUri;
  const avatarFallback = card?.avatarFallback ?? getNameInitials(fullName || "HK");
  const isSubmitting = updateProfile.isPending || uploadAvatar.isPending;

  async function handlePickAvatar() {
    const permission = await ImagePicker.requestMediaLibraryPermissionsAsync();
    if (!permission.granted) {
      toast.error("Izin galeri diperlukan untuk ganti foto", { duration: 4000 });
      return;
    }

    const result = await ImagePicker.launchImageLibraryAsync({
      mediaTypes: ["images"],
      allowsEditing: true,
      aspect: [1, 1],
      quality: 0.85,
    });

    if (result.canceled || !result.assets[0]) return;

    const asset = result.assets[0];
    const mimeType = asset.mimeType || "image/jpeg";
    if (!ALLOWED_MIME.has(mimeType)) {
      toast.error("Format foto harus JPEG, PNG, atau WebP", { duration: 4000 });
      return;
    }
    if (asset.fileSize && asset.fileSize > MAX_AVATAR_BYTES) {
      toast.error("Ukuran foto maksimal 5 MB", { duration: 4000 });
      return;
    }

    setPendingAvatar({
      uri: asset.uri,
      mimeType,
      fileName: asset.fileName,
    });
  }

  async function handleSave() {
    if (isSubmitting) return;

    const trimmedName = fullName.trim();
    if (!trimmedName) {
      toast.error("Nama lengkap wajib diisi", { duration: 4000 });
      return;
    }
    if (trimmedName.length > 150) {
      toast.error("Nama lengkap maksimal 150 karakter", { duration: 4000 });
      return;
    }

    const trimmedStreet = street.trim();
    const wantsAddress = Boolean(
      trimmedStreet ||
        provinceId ||
        cityId ||
        subDistrictId ||
        villageId ||
        postalCodeId,
    );
    if (
      wantsAddress &&
      (!trimmedStreet ||
        !provinceId ||
        !cityId ||
        !subDistrictId ||
        !villageId ||
        !postalCodeId)
    ) {
      toast.error("Lengkapi seluruh data alamat", { duration: 4000 });
      return;
    }

    try {
      if (pendingAvatar) {
        await uploadAvatar.mutateAsync(pendingAvatar);
      }

      const nextBirthDate = birthDate ? toDateOnlyString(birthDate) : null;
      const currentBirthDate = profile.birthDate
        ? toDateOnlyString(parseDateOnly(profile.birthDate) ?? new Date(0))
        : null;

      const payload: Parameters<typeof updateProfile.mutateAsync>[0] = {};

      if (trimmedName !== profile.user.fullName) {
        payload.fullName = trimmedName;
      }
      if (nextBirthDate !== currentBirthDate) {
        payload.birthDate = nextBirthDate;
      }
      if (gender !== profile.gender) {
        payload.gender = gender;
      }
      if (wantsAddress && villageId && postalCodeId) {
        const sameAddress =
          profile.address?.street === trimmedStreet &&
          profile.address.region.villageId === villageId &&
          profile.address.postalCodeId === postalCodeId;
        if (!sameAddress) {
          payload.address = {
            villageId,
            postalCodeId,
            street: trimmedStreet,
          };
        }
      }

      if (Object.keys(payload).length > 0) {
        await updateProfile.mutateAsync(payload);
      } else if (!pendingAvatar) {
        toast.error("Tidak ada perubahan", { duration: 3000 });
        return;
      }

      toast.success("Profil berhasil diperbarui", { duration: 3000 });
      router.back();
    } catch (error) {
      toast.error(
        error instanceof Error ? error.message : "Gagal menyimpan profil",
        { duration: 4000 },
      );
    }
  }

  return (
    <>
      <Stack.Screen
        options={{
          title: "Edit Profil",
          headerShown: true,
          headerBackTitle: "Detail",
          headerTintColor: "#57534e",
        }}
      />
      <AuthScreenShell scrollable>
        <View className="gap-4">
            <View className="items-center gap-3 py-2">
              <Pressable
                onPress={() => void handlePickAvatar()}
                className="relative active:opacity-80"
                accessibilityRole="button"
                accessibilityLabel="Ubah foto profil"
              >
                <Avatar alt={fullName || "Profil"} className="size-24 border border-stone-200">
                  <AvatarImage source={avatarUri ? { uri: avatarUri } : undefined} />
                  <AvatarFallback>
                    <Text className="text-2xl font-semibold text-stone-700">
                      {avatarFallback}
                    </Text>
                  </AvatarFallback>
                </Avatar>
                <View className="absolute bottom-0 right-0 rounded-full bg-amber-600 p-1.5">
                  <Icon as={Camera} className="size-3.5 text-white" />
                </View>
              </Pressable>
              <Text className="text-xs text-stone-500">Ketuk untuk ganti foto</Text>
            </View>

            <AuthField label="Nomor Member" helperText="Tidak dapat diubah">
              <Input
                value={profile.memberNumber}
                editable={false}
                className={`${AUTH_INPUT_CLASSNAME} opacity-70`}
              />
            </AuthField>

            <AuthField label="Email" helperText="Tidak dapat diubah">
              <Input
                value={profile.user.email}
                editable={false}
                className={`${AUTH_INPUT_CLASSNAME} opacity-70`}
              />
            </AuthField>

            <AuthField label="Nomor HP" helperText="Tidak dapat diubah">
              <Input
                value={profile.phoneNumber}
                editable={false}
                className={`${AUTH_INPUT_CLASSNAME} opacity-70`}
              />
            </AuthField>

            <AuthField label="Nama Lengkap">
              <Input
                value={fullName}
                onChangeText={setFullName}
                placeholder="Nama lengkap"
                placeholderTextColor={AUTH_PLACEHOLDER_COLOR}
                className={AUTH_INPUT_CLASSNAME}
                maxLength={150}
              />
            </AuthField>

            <AuthField label="Tanggal Lahir">
              <Pressable
                onPress={() => setShowDatePicker((open) => !open)}
                className="h-11 justify-center rounded-lg border border-stone-300 bg-white px-3 active:opacity-80"
              >
                <Text className={birthDate ? "text-stone-700" : "text-stone-400"}>
                  {birthDate
                    ? birthDate.toLocaleDateString("id-ID", {
                        day: "numeric",
                        month: "long",
                        year: "numeric",
                      })
                    : "Pilih tanggal lahir"}
                </Text>
              </Pressable>
              {showDatePicker ? (
                <View className="mt-2 overflow-hidden rounded-lg border border-stone-200 bg-white p-2">
                  <DateTimePicker
                    mode="single"
                    locale="id"
                    date={birthDate ?? new Date(2000, 0, 1)}
                    maxDate={new Date()}
                    onChange={({ date }: { date: DateType }) => {
                      if (!date) return;
                      const next = dayjs(date).toDate();
                      if (Number.isNaN(next.getTime())) return;
                      setBirthDate(next);
                      setShowDatePicker(false);
                    }}
                    styles={{
                      ...dateStyles,
                      today: { borderColor: "#e8a020", borderWidth: 1 },
                      selected: { backgroundColor: "#e8a020" },
                      selected_label: { color: "#ffffff" },
                    }}
                  />
                </View>
              ) : null}
            </AuthField>

            <AuthField label="Jenis Kelamin">
              <Dropdown
                style={{
                  height: 44,
                  borderWidth: 1,
                  borderColor: "#d6d3d1",
                  borderRadius: 8,
                  paddingHorizontal: 12,
                  backgroundColor: "#ffffff",
                }}
                data={GENDER_OPTIONS}
                labelField="label"
                valueField="value"
                placeholder="Pilih jenis kelamin"
                value={gender}
                onChange={(item) => setGender(item.value)}
                placeholderStyle={{ fontSize: 14, color: "#a8a29e" }}
                selectedTextStyle={{ fontSize: 14, color: "#44403c" }}
              />
            </AuthField>

            <View className="mt-2 gap-4 border-t border-stone-200 pt-4">
              <Text className="text-base font-bold text-stone-900">Alamat</Text>

              <AddressDropdown
                label="Provinsi"
                placeholder="Pilih provinsi"
                options={provinceOptions}
                value={provinceId}
                loading={loadingAddressLevel === "province"}
                disabled={
                  loadingAddressLevel !== null &&
                  loadingAddressLevel !== "province"
                }
                onChange={handleProvinceChange}
              />

              <AddressDropdown
                label="Kota / Kabupaten"
                placeholder="Pilih kota / kabupaten"
                options={cityOptions}
                value={cityId}
                loading={loadingAddressLevel === "city"}
                disabled={!provinceId || loadingAddressLevel !== null}
                onChange={handleCityChange}
              />

              <AddressDropdown
                label="Kecamatan"
                placeholder="Pilih kecamatan"
                options={subDistrictOptions}
                value={subDistrictId}
                loading={loadingAddressLevel === "subDistrict"}
                disabled={!cityId || loadingAddressLevel !== null}
                onChange={handleSubDistrictChange}
              />

              <AddressDropdown
                label="Kelurahan"
                placeholder="Pilih kelurahan"
                options={villageOptions}
                value={villageId}
                loading={loadingAddressLevel === "village"}
                disabled={!subDistrictId || loadingAddressLevel !== null}
                onChange={handleVillageChange}
              />

              <AddressDropdown
                label="Kode Pos"
                placeholder="Pilih kode pos"
                options={postalCodeOptions}
                value={postalCodeId}
                loading={loadingAddressLevel === "postalCode"}
                disabled={!villageId || loadingAddressLevel !== null}
                onChange={setPostalCodeId}
              />

              <AuthField label="Alamat Lengkap">
                <Input
                  value={street}
                  onChangeText={setStreet}
                  placeholder="Nama jalan, nomor rumah, RT/RW"
                  placeholderTextColor={AUTH_PLACEHOLDER_COLOR}
                  className={AUTH_INPUT_CLASSNAME}
                />
              </AuthField>
            </View>

            <GoldButton
              label={isSubmitting ? "Menyimpan..." : "Simpan"}
              width="full"
              variant="filled"
              onPress={() => {
                if (!isSubmitting) void handleSave();
              }}
            />
        </View>
      </AuthScreenShell>
    </>
  );
}

export default function ProfileEditScreen() {
  const { profile, card } = useMyProfile();

  if (!profile) {
    return (
      <>
        <Stack.Screen
          options={{
            title: "Edit Profil",
            headerShown: true,
            headerBackTitle: "Detail",
            headerTintColor: "#57534e",
          }}
        />
        <AuthScreenShell>
          <Text className="text-center text-sm text-stone-500">
            Memuat profil...
          </Text>
        </AuthScreenShell>
      </>
    );
  }

  return (
    <ProfileEditForm
      key={`${profile.id}:${profile.updatedAt ?? ""}`}
      profile={profile}
      card={card}
    />
  );
}
