import Redis from 'ioredis';

export type RedisLike = Pick<Redis, 'get' | 'set' | 'del' | 'quit' | 'ttl'>;

function createRedisClient(): Redis {
  const url = process.env.REDIS_URL;
  // enableOfflineQueue harus true: lazyConnect + offlineQueue=false
  // bikin perintah pertama (ttl/set) sering ditolak saat socket belum ready —
  // gejala: kirim OTP pertama gagal, kedua sukses.
  const shared = {
    maxRetriesPerRequest: 3,
    lazyConnect: true,
    enableOfflineQueue: true,
  } as const;

  if (url) {
    return new Redis(url, shared);
  }

  return new Redis({
    host: process.env.REDIS_HOST ?? '127.0.0.1',
    port: Number(process.env.REDIS_PORT ?? 6379),
    password: process.env.REDIS_PASSWORD || undefined,
    db: Number(process.env.REDIS_DB ?? 2),
    ...shared,
  });
}

let client: RedisLike | null = null;
let connectPromise: Promise<void> | null = null;

async function ensureRedisConnected(redis: Redis): Promise<void> {
  if (redis.status === 'ready') return;
  // Koneksi putus setelah idle — reset promise biar connect ulang.
  if (redis.status === 'end' || redis.status === 'close') {
    connectPromise = null;
  }
  if (!connectPromise) {
    connectPromise = redis.connect().then(
      () => undefined,
      (error: unknown) => {
        connectPromise = null;
        throw error;
      },
    );
  }
  await connectPromise;
}

function wrapWithEnsureConnect(redis: Redis): RedisLike {
  return {
    get: async (...args) => {
      await ensureRedisConnected(redis);
      return redis.get(...args);
    },
    set: async (...args) => {
      await ensureRedisConnected(redis);
      return redis.set(...args);
    },
    del: async (...args) => {
      await ensureRedisConnected(redis);
      return redis.del(...args);
    },
    ttl: async (...args) => {
      await ensureRedisConnected(redis);
      return redis.ttl(...args);
    },
    quit: async () => {
      connectPromise = null;
      return redis.quit();
    },
  };
}

/** Singleton Redis for OTP cache (prefer DB 2 via REDIS_URL / REDIS_DB). */
export function getRedis(): RedisLike {
  if (!client) {
    client = wrapWithEnsureConnect(createRedisClient());
  }
  return client;
}

/** Test/DI override — replace singleton (e.g. in-memory fake). */
export function setRedisForTests(redis: RedisLike | null): void {
  client = redis;
  connectPromise = null;
}

export async function disconnectRedis(): Promise<void> {
  if (client) {
    await client.quit().catch(() => undefined);
    client = null;
    connectPromise = null;
  }
}

/** Minimal Redis stand-in for unit tests when real Redis is down. */
export function createMemoryRedis(): RedisLike {
  const store = new Map<string, { value: string; expiresAt: number | null }>();

  return {
    async get(key: string) {
      const row = store.get(key);
      if (!row) return null;
      if (row.expiresAt !== null && Date.now() >= row.expiresAt) {
        store.delete(key);
        return null;
      }
      return row.value;
    },
    async set(key: string, value: string, ...args: unknown[]) {
      let expiresAt: number | null = null;
      if (args[0] === 'EX' && typeof args[1] === 'number') {
        expiresAt = Date.now() + args[1] * 1000;
      }
      store.set(key, { value, expiresAt });
      return 'OK';
    },
    async del(...keys: string[]) {
      let n = 0;
      for (const key of keys) {
        if (store.delete(key)) n += 1;
      }
      return n;
    },
    async ttl(key: string) {
      const row = store.get(key);
      if (!row) return -2;
      if (row.expiresAt === null) return -1;
      const remaining = Math.ceil((row.expiresAt - Date.now()) / 1000);
      if (remaining <= 0) {
        store.delete(key);
        return -2;
      }
      return remaining;
    },
    async quit() {
      store.clear();
      return 'OK';
    },
  };
}
