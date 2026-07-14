import { MOCK_BRANCH_LIST } from '@/mocks/mock-branches';
import type {
  RewardBranchStockItem,
  RewardCatalogItem,
  RewardCategory,
  RewardCategoryGroup,
  RewardDetail,
} from '@/types/reward';

export type {
  RewardBranchStockItem,
  RewardCatalogItem,
  RewardCategory,
  RewardDetail,
};

const MOCK_REWARD_IMAGE = require('@/assets/mockImage/mock-image-news.webp');

export const MOCK_REWARD_CATEGORIES: RewardCategory[] = [
  { id: 1, name: 'E-Voucher', slug: 'e-voucher' },
  { id: 2, name: 'Merchandise', slug: 'merchandise' },
  { id: 3, name: 'Perhiasan', slug: 'perhiasan' },
];

function createBranchStocks(
  entries: Array<{ branchIndex: number; actualStock: number; heldStock: number }>
): RewardBranchStockItem[] {
  return entries.map(({ branchIndex, actualStock, heldStock }) => {
    const branch = MOCK_BRANCH_LIST[branchIndex];

    return {
      branchId: branch.id,
      branchName: branch.name,
      subdistrict: branch.subdistrict,
      city: branch.city,
      locationUrl: branch.locationUrl,
      actualStock,
      heldStock,
    };
  });
}

const REWARD_DETAIL_DATA: Record<
  string,
  Pick<RewardDetail, 'description' | 'images' | 'branchStocks'>
> = {
  'EVC-001': {
    description:
      'Voucher belanja senilai Rp 100.000 yang dapat digunakan di merchant partner HK Gold. Berlaku 30 hari setelah penukaran.',
    images: [MOCK_REWARD_IMAGE, MOCK_REWARD_IMAGE, MOCK_REWARD_IMAGE],
    branchStocks: createBranchStocks([
      { branchIndex: 0, actualStock: 8, heldStock: 3 },
      { branchIndex: 1, actualStock: 5, heldStock: 1 },
      { branchIndex: 2, actualStock: 4, heldStock: 0 },
    ]),
  },
  'EVC-002': {
    description:
      'Voucher GrabFood Rp 50.000 untuk member aktif. Kode voucher akan dikirim setelah proses redeem disetujui.',
    images: [MOCK_REWARD_IMAGE, MOCK_REWARD_IMAGE],
    branchStocks: createBranchStocks([
      { branchIndex: 0, actualStock: 15, heldStock: 3 },
      { branchIndex: 3, actualStock: 10, heldStock: 2 },
    ]),
  },
  'EVC-003': {
    description:
      'Voucher belanja online marketplace pilihan senilai Rp 75.000. Cocok untuk belanja kebutuhan harian member.',
    images: [MOCK_REWARD_IMAGE, MOCK_REWARD_IMAGE],
    branchStocks: createBranchStocks([
      { branchIndex: 1, actualStock: 12, heldStock: 2 },
      { branchIndex: 8, actualStock: 9, heldStock: 1 },
    ]),
  },
  'EVC-004': {
    description:
      'Voucher FnB partner HK Gold senilai Rp 30.000. Dapat ditukar di cabang yang terdaftar sebagai partner.',
    images: [MOCK_REWARD_IMAGE],
    branchStocks: createBranchStocks([
      { branchIndex: 2, actualStock: 20, heldStock: 5 },
      { branchIndex: 11, actualStock: 14, heldStock: 4 },
    ]),
  },
  'MCH-001': {
    description:
      'Tumbler eksklusif edisi terbatas dengan logo HK Gold. Material stainless steel, kapasitas 500ml.',
    images: [MOCK_REWARD_IMAGE, MOCK_REWARD_IMAGE, MOCK_REWARD_IMAGE],
    branchStocks: createBranchStocks([
      { branchIndex: 0, actualStock: 6, heldStock: 3 },
      { branchIndex: 5, actualStock: 4, heldStock: 1 },
    ]),
  },
  'MCH-002': {
    description:
      'Tote bag eksklusif member dengan desain minimalis. Termasuk pouch kecil untuk menyimpan kartu member.',
    images: [MOCK_REWARD_IMAGE, MOCK_REWARD_IMAGE],
    branchStocks: createBranchStocks([
      { branchIndex: 1, actualStock: 10, heldStock: 2 },
      { branchIndex: 6, actualStock: 8, heldStock: 0 },
      { branchIndex: 9, actualStock: 5, heldStock: 1 },
    ]),
  },
  'MCH-003': {
    description:
      'Payung lipat premium HK Gold dengan lapisan UV protection. Warna emas dan putih khas brand.',
    images: [MOCK_REWARD_IMAGE, MOCK_REWARD_IMAGE],
    branchStocks: createBranchStocks([
      { branchIndex: 3, actualStock: 7, heldStock: 2 },
      { branchIndex: 10, actualStock: 6, heldStock: 1 },
    ]),
  },
  'MCH-004': {
    description:
      'Notebook kulit sintetis eksklusif member. Berisi 120 halaman dan penutup dengan emboss logo HK Gold.',
    images: [MOCK_REWARD_IMAGE],
    branchStocks: createBranchStocks([
      { branchIndex: 2, actualStock: 12, heldStock: 4 },
      { branchIndex: 7, actualStock: 9, heldStock: 3 },
    ]),
  },
  'PRH-001': {
    description:
      'Liontin emas HK Gold 0.5 gram dengan desain klasik. Dilengkapi sertifikat keaslian dan box eksklusif.',
    images: [MOCK_REWARD_IMAGE, MOCK_REWARD_IMAGE, MOCK_REWARD_IMAGE],
    branchStocks: createBranchStocks([
      { branchIndex: 0, actualStock: 3, heldStock: 1 },
      { branchIndex: 1, actualStock: 2, heldStock: 0 },
    ]),
  },
  'PRH-002': {
    description:
      'Gelang emas mini collection dengan ukiran motif tradisional. Berat bersih 0.3 gram.',
    images: [MOCK_REWARD_IMAGE, MOCK_REWARD_IMAGE],
    branchStocks: createBranchStocks([
      { branchIndex: 2, actualStock: 5, heldStock: 0 },
      { branchIndex: 4, actualStock: 4, heldStock: 1 },
      { branchIndex: 8, actualStock: 3, heldStock: 1 },
    ]),
  },
  'PRH-003': {
    description:
      'Anting emas HK Gold dengan batu zirconia. Desain elegan untuk acara formal maupun kasual.',
    images: [MOCK_REWARD_IMAGE, MOCK_REWARD_IMAGE],
    branchStocks: createBranchStocks([
      { branchIndex: 3, actualStock: 4, heldStock: 1 },
      { branchIndex: 11, actualStock: 3, heldStock: 0 },
    ]),
  },
  'PRH-004': {
    description:
      'Cincin emas HK Gold model solitaire mini. Ukuran dapat disesuaikan di cabang setelah penukaran.',
    images: [MOCK_REWARD_IMAGE, MOCK_REWARD_IMAGE, MOCK_REWARD_IMAGE],
    branchStocks: createBranchStocks([
      { branchIndex: 5, actualStock: 2, heldStock: 0 },
      { branchIndex: 6, actualStock: 2, heldStock: 1 },
    ]),
  },
};

export const MOCK_REWARD_LIST: RewardCatalogItem[] = [
  {
    id: 'reward-1',
    sku: 'EVC-001',
    name: 'Voucher Belanja Rp 100.000',
    categoryId: 1,
    categoryName: 'E-Voucher',
    categorySlug: 'e-voucher',
    pointsRequired: 2500,
    stockRemaining: 5,
    image: MOCK_REWARD_IMAGE,
  },
  {
    id: 'reward-2',
    sku: 'EVC-002',
    name: 'Voucher GrabFood Rp 50.000',
    categoryId: 1,
    categoryName: 'E-Voucher',
    categorySlug: 'e-voucher',
    pointsRequired: 1200,
    stockRemaining: 12,
    image: MOCK_REWARD_IMAGE,
  },
  {
    id: 'reward-3',
    sku: 'EVC-003',
    name: 'Voucher Marketplace Rp 75.000',
    categoryId: 1,
    categoryName: 'E-Voucher',
    categorySlug: 'e-voucher',
    pointsRequired: 1800,
    stockRemaining: 9,
    image: MOCK_REWARD_IMAGE,
  },
  {
    id: 'reward-4',
    sku: 'EVC-004',
    name: 'Voucher FnB Partner Rp 30.000',
    categoryId: 1,
    categoryName: 'E-Voucher',
    categorySlug: 'e-voucher',
    pointsRequired: 800,
    stockRemaining: 15,
    image: MOCK_REWARD_IMAGE,
  },
  {
    id: 'reward-5',
    sku: 'MCH-001',
    name: 'Tumbler HK Gold Limited Edition',
    categoryId: 2,
    categoryName: 'Merchandise',
    categorySlug: 'merchandise',
    pointsRequired: 3500,
    stockRemaining: 3,
    image: MOCK_REWARD_IMAGE,
  },
  {
    id: 'reward-6',
    sku: 'MCH-002',
    name: 'Tote Bag Exclusive Member',
    categoryId: 2,
    categoryName: 'Merchandise',
    categorySlug: 'merchandise',
    pointsRequired: 1800,
    stockRemaining: 8,
    image: MOCK_REWARD_IMAGE,
  },
  {
    id: 'reward-7',
    sku: 'MCH-003',
    name: 'Payung Lipat Premium HK Gold',
    categoryId: 2,
    categoryName: 'Merchandise',
    categorySlug: 'merchandise',
    pointsRequired: 2200,
    stockRemaining: 6,
    image: MOCK_REWARD_IMAGE,
  },
  {
    id: 'reward-8',
    sku: 'MCH-004',
    name: 'Notebook Kulit Eksklusif',
    categoryId: 2,
    categoryName: 'Merchandise',
    categorySlug: 'merchandise',
    pointsRequired: 1500,
    stockRemaining: 10,
    image: MOCK_REWARD_IMAGE,
  },
  {
    id: 'reward-9',
    sku: 'PRH-001',
    name: 'Liontin Emas HK Gold 0.5 gr',
    categoryId: 3,
    categoryName: 'Perhiasan',
    categorySlug: 'perhiasan',
    pointsRequired: 15000,
    stockRemaining: 2,
    image: MOCK_REWARD_IMAGE,
  },
  {
    id: 'reward-10',
    sku: 'PRH-002',
    name: 'Gelang Emas Mini Collection',
    categoryId: 3,
    categoryName: 'Perhiasan',
    categorySlug: 'perhiasan',
    pointsRequired: 8500,
    stockRemaining: 5,
    image: MOCK_REWARD_IMAGE,
  },
  {
    id: 'reward-11',
    sku: 'PRH-003',
    name: 'Anting Emas Zirconia',
    categoryId: 3,
    categoryName: 'Perhiasan',
    categorySlug: 'perhiasan',
    pointsRequired: 6200,
    stockRemaining: 4,
    image: MOCK_REWARD_IMAGE,
  },
  {
    id: 'reward-12',
    sku: 'PRH-004',
    name: 'Cincin Emas Solitaire Mini',
    categoryId: 3,
    categoryName: 'Perhiasan',
    categorySlug: 'perhiasan',
    pointsRequired: 9800,
    stockRemaining: 3,
    image: MOCK_REWARD_IMAGE,
  },
];

export const MOCK_REWARD_CATALOG: RewardCategoryGroup[] = MOCK_REWARD_CATEGORIES.map((category) => ({
  ...category,
  rewards: MOCK_REWARD_LIST.filter((reward) => reward.categoryId === category.id),
}));

export function getRewardDetailBySku(sku: string): RewardDetail | null {
  const reward = MOCK_REWARD_LIST.find((item) => item.sku === sku);
  const detail = REWARD_DETAIL_DATA[sku];

  if (!reward || !detail) {
    return null;
  }

  return {
    ...reward,
    ...detail,
  };
}

export function getAvailableBranchStock(stock: RewardBranchStockItem) {
  return Math.max(stock.actualStock - stock.heldStock, 0);
}
