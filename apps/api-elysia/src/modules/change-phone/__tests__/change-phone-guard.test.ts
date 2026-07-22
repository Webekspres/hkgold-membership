import { describe, expect, test } from 'bun:test';
import { ChangePhoneError } from '../types/change-phone.types';
import { changePhoneService } from '../services/change-phone.service';

describe('ChangePhoneService guards', () => {
  test('assertNoPending throws PENDING_EXISTS when hasPending true', async () => {
    const original = changePhoneService.hasPending.bind(changePhoneService);
    changePhoneService.hasPending = async () => true;
    try {
      await expect(changePhoneService.assertNoPending('member-x')).rejects.toMatchObject({
        code: 'PENDING_EXISTS',
      } satisfies Partial<ChangePhoneError>);
    } finally {
      changePhoneService.hasPending = original;
    }
  });

  test('assertNoPending passes when no pending', async () => {
    const original = changePhoneService.hasPending.bind(changePhoneService);
    changePhoneService.hasPending = async () => false;
    try {
      await changePhoneService.assertNoPending('member-x');
    } finally {
      changePhoneService.hasPending = original;
    }
  });
});
