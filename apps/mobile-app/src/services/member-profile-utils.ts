import type {
  AddressOption,
  MemberProfile,
  UpdateMyProfileInput,
} from '@/types/member';

/** Build PATCH body — never includes email/phone/memberNumber. */
export function buildUpdateMyProfilePayload(
  input: UpdateMyProfileInput,
): UpdateMyProfileInput {
  const payload: UpdateMyProfileInput = {};

  if (input.fullName !== undefined) {
    payload.fullName = input.fullName.trim();
  }
  if (input.birthDate !== undefined) {
    payload.birthDate = input.birthDate;
  }
  if (input.gender !== undefined) {
    payload.gender = input.gender;
  }
  if (input.address !== undefined) {
    payload.address = {
      villageId: input.address.villageId,
      postalCodeId: input.address.postalCodeId,
      street: input.address.street.trim(),
    };
  }

  return payload;
}

export function formatAddressOptionLabel(option: AddressOption): string {
  return `${option.villageName}, ${option.subDistrictName}, ${option.cityName}, ${option.provinceName} (${option.kodepos})`;
}

/** Serialize Date → YYYY-MM-DD without timezone shift. */
export function toDateOnlyString(date: Date): string {
  const y = date.getFullYear();
  const m = String(date.getMonth() + 1).padStart(2, '0');
  const d = String(date.getDate()).padStart(2, '0');
  return `${y}-${m}-${d}`;
}

export function parseDateOnly(value: string | null | undefined): Date | null {
  if (!value) return null;
  const match = /^(\d{4})-(\d{2})-(\d{2})/.exec(value);
  if (!match) return null;
  const year = Number(match[1]);
  const month = Number(match[2]);
  const day = Number(match[3]);
  const date = new Date(year, month - 1, day);
  if (Number.isNaN(date.getTime())) return null;
  return date;
}

export function formatGenderLabel(gender: string | null | undefined): string {
  if (gender === 'MALE') return 'Laki-laki';
  if (gender === 'FEMALE') return 'Perempuan';
  return '-';
}

export function formatAddressLine(profile: MemberProfile): string {
  const address = profile.address;
  if (!address) return '-';
  const { region } = address;
  return [
    address.street,
    region.villageName,
    region.subDistrictName,
    region.cityName,
    region.provinceName,
    address.kodepos,
  ]
    .filter(Boolean)
    .join(', ');
}
