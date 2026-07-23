/**
 * Preload for `bun test`:
 * 1) Point DATABASE_URL at hkgold_membership_test (Doppler keeps host/user/pass for dev).
 * 2) Fail fast if schema incomplete / wrong DB name.
 */
const TEST_DATABASE = 'hkgold_membership_test';

function rewriteDatabaseUrlToTest(): void {
  const raw = process.env.DATABASE_URL;
  if (!raw) {
    throw new Error(
      'DATABASE_URL kosong. Jalankan via `doppler run -- bun test` (config dev_backend).',
    );
  }

  let url: URL;
  try {
    url = new URL(raw);
  } catch {
    throw new Error(`DATABASE_URL tidak valid: ${raw.slice(0, 40)}…`);
  }

  // mysql://user:pass@host:port/dbname — pathname is "/dbname"
  url.pathname = `/${TEST_DATABASE}`;
  process.env.DATABASE_URL = url.toString();

  const dbName = url.pathname.replace(/^\//, '');
  if (dbName !== TEST_DATABASE || !dbName.endsWith('_test')) {
    throw new Error(
      `Test harus pakai DB ${TEST_DATABASE}, dapat: ${dbName}. Cek rewrite DATABASE_URL di test-setup.`,
    );
  }
}

rewriteDatabaseUrlToTest();

const { prisma } = await import('./db');

const CORE_TABLES = [
  'branches',
  'users',
  'members',
  'rewards',
  'reward_branch_stocks',
  'category_rewards',
] as const;

async function assertDatabaseReady(): Promise<void> {
  try {
    const rows = await prisma.$queryRawUnsafe<Array<{ table_name: string }>>(
      `SELECT table_name AS table_name FROM information_schema.tables
       WHERE table_schema = DATABASE()
         AND table_name IN (${CORE_TABLES.map((t) => `'${t}'`).join(',')})`,
    );

    const present = new Set(
      rows.map((r) => String((r as any).table_name ?? (r as any).TABLE_NAME ?? '')),
    );
    const missing = CORE_TABLES.filter((t) => !present.has(t));

    if (missing.length > 0) {
      throw new Error(
        [
          `DATABASE_URL schema tidak lengkap di ${TEST_DATABASE} (hilang: ${missing.join(', ')}).`,
          'Jalankan: cd apps/backoffice-filament && doppler run -- env DB_DATABASE=hkgold_membership_test php artisan migrate:fresh --seed',
          'Dev DB = hkgold_membership; test DB = hkgold_membership_test (jangan paralelkan suite antar process di DB yang sama).',
        ].join(' '),
      );
    }
  } catch (error) {
    if (error instanceof Error && error.message.includes('schema tidak lengkap')) {
      throw error;
    }

    const message = error instanceof Error ? error.message : String(error);
    const emptyDb =
      message.includes('does not exist') ||
      message.includes('P2021') ||
      message.includes("doesn't exist");

    if (emptyDb) {
      throw new Error(
        [
          `DATABASE_URL menunjuk ${TEST_DATABASE} kosong / belum ada schema.`,
          'Jalankan: cd apps/backoffice-filament && doppler run -- env DB_DATABASE=hkgold_membership_test php artisan migrate:fresh --seed',
          `Detail: ${message.slice(0, 200)}`,
        ].join(' '),
      );
    }

    throw error;
  }
}

await assertDatabaseReady();
