export type RewardCatalogItem = {
  id: string;
  sku: string;
  name: string;
  categoryName: string;
  pointsRequired: number;
  stockRemaining: number;
  image: number;
};

export type RewardCategoryGroup = {
  id: number;
  name: string;
  slug: string;
  rewards: RewardCatalogItem[];
};

const MOCK_REWARD_IMAGE = require('@/assets/mockImage/mock-image-news.webp');

export const MOCK_REWARD_CATALOG: RewardCategoryGroup[] = [
  {
    id: 1,
    name: 'E-Voucher',
    slug: 'e-voucher',
    rewards: [
      {
        id: 'reward-1',
        sku: 'EVC-001',
        name: 'Voucher Belanja Rp 100.000',
        categoryName: 'E-Voucher',
        pointsRequired: 2500,
        stockRemaining: 5,
        image: MOCK_REWARD_IMAGE,
      },
      {
        id: 'reward-2',
        sku: 'EVC-002',
        name: 'Voucher GrabFood Rp 50.000',
        categoryName: 'E-Voucher',
        pointsRequired: 1200,
        stockRemaining: 12,
        image: MOCK_REWARD_IMAGE,
      },
    ],
  },
  {
    id: 2,
    name: 'Merchandise',
    slug: 'merchandise',
    rewards: [
      {
        id: 'reward-3',
        sku: 'MCH-001',
        name: 'Tumbler HK Gold Limited Edition',
        categoryName: 'Merchandise',
        pointsRequired: 3500,
        stockRemaining: 3,
        image: MOCK_REWARD_IMAGE,
      },
      {
        id: 'reward-4',
        sku: 'MCH-002',
        name: 'Tote Bag Exclusive Member',
        categoryName: 'Merchandise',
        pointsRequired: 1800,
        stockRemaining: 8,
        image: MOCK_REWARD_IMAGE,
      },
    ],
  },
  {
    id: 3,
    name: 'Perhiasan',
    slug: 'perhiasan',
    rewards: [
      {
        id: 'reward-5',
        sku: 'PRH-001',
        name: 'Liontin Emas HK Gold 0.5 gr',
        categoryName: 'Perhiasan',
        pointsRequired: 15000,
        stockRemaining: 2,
        image: MOCK_REWARD_IMAGE,
      },
      {
        id: 'reward-6',
        sku: 'PRH-002',
        name: 'Gelang Emas Mini Collection',
        categoryName: 'Perhiasan',
        pointsRequired: 8500,
        stockRemaining: 5,
        image: MOCK_REWARD_IMAGE,
      },
    ],
  },
];
