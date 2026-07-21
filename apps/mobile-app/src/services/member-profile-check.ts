import {
  buildUpdateMyProfilePayload,
  formatAddressOptionLabel,
  toDateOnlyString,
} from './member-profile-utils';

function assertEqual(actual: unknown, expected: unknown) {
  if (JSON.stringify(actual) !== JSON.stringify(expected)) {
    throw new Error(`Expected ${JSON.stringify(expected)}, got ${JSON.stringify(actual)}`);
  }
}

function main() {
  const payload = buildUpdateMyProfilePayload({
    fullName: '  Andi  ',
    birthDate: '1990-05-01',
    gender: 'FEMALE',
    address: {
      villageId: 1,
      postalCodeId: 2,
      street: '  Jl. Merdeka  ',
    },
  });

  assertEqual(payload, {
    fullName: 'Andi',
    birthDate: '1990-05-01',
    gender: 'FEMALE',
    address: {
      villageId: 1,
      postalCodeId: 2,
      street: 'Jl. Merdeka',
    },
  });

  assertEqual(Object.prototype.hasOwnProperty.call(payload, 'email'), false);
  assertEqual(Object.prototype.hasOwnProperty.call(payload, 'phoneNumber'), false);
  assertEqual(Object.prototype.hasOwnProperty.call(payload, 'memberNumber'), false);

  const local = new Date(2024, 0, 5);
  assertEqual(toDateOnlyString(local), '2024-01-05');

  assertEqual(
    formatAddressOptionLabel({
      villageId: 1,
      postalCodeId: 2,
      kodepos: '12345',
      villageName: 'Desa A',
      subDistrictName: 'Kec B',
      cityName: 'Kota C',
      provinceName: 'Prov D',
    }),
    'Desa A, Kec B, Kota C, Prov D (12345)',
  );

  console.log('member profile payload checks passed');
}

main();
