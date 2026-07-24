import { describe, test, expect, beforeAll, afterAll } from 'bun:test';
import { branchService } from '../services/branch.service';
import { prisma } from '../../../db';

describe('Branch Module - getNearest', () => {
  const testSuffix = Date.now().toString().slice(-6);
  let jakartaId = 0;
  let bandungId = 0;
  let warehouseId = 0;

  beforeAll(async () => {
    const jakarta = await prisma.branch.create({
      data: {
        branchCode: `NJ${testSuffix}`,
        name: `Nearest Jakarta ${testSuffix}`,
        address: 'Jl. Sudirman, Jakarta',
        latitude: -6.2088,
        longitude: 106.8456,
        isOnlineWarehouse: false,
      },
    });
    jakartaId = jakarta.id;

    const bandung = await prisma.branch.create({
      data: {
        branchCode: `NB${testSuffix}`,
        name: `Nearest Bandung ${testSuffix}`,
        address: 'Jl. Asia Afrika, Bandung',
        latitude: -6.9175,
        longitude: 107.6191,
        isOnlineWarehouse: false,
      },
    });
    bandungId = bandung.id;

    const warehouse = await prisma.branch.create({
      data: {
        branchCode: `NW${testSuffix}`,
        name: `Nearest Warehouse ${testSuffix}`,
        address: 'Warehouse',
        latitude: -6.2,
        longitude: 106.84,
        isOnlineWarehouse: true,
      },
    });
    warehouseId = warehouse.id;
  });

  afterAll(async () => {
    for (const id of [jakartaId, bandungId, warehouseId]) {
      try {
        await prisma.branch.delete({ where: { id } });
      } catch {}
    }
  });

  test('near Jakarta coords returns Jakarta branch', async () => {
    const nearest = await branchService.getNearest(-6.21, 106.85);
    expect(nearest).not.toBeNull();
    expect(nearest!.id).toBe(jakartaId);
    expect(nearest!.distanceKm).toBeGreaterThanOrEqual(0);
    expect(nearest!.distanceKm).toBeLessThan(5);
  });

  test('warehouse excluded even if closer', async () => {
    const nearest = await branchService.getNearest(-6.2, 106.84);
    expect(nearest).not.toBeNull();
    expect(nearest!.id).not.toBe(warehouseId);
    expect(nearest!.isOnlineWarehouse).toBe(false);
  });
});
