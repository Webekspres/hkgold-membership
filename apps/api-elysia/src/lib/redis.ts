import Redis from 'ioredis';

export type RedisLike = Pick<Redis, 'get' | 'set' | 'del' | 'quit'>;

function createRedisClient(): Redis {
  const url = process.env.REDIS_URL;
  if (url) {
    return new Redis(url, {
      maxRetriesPerRequest: 3,
      lazyConnect: true,
      enableOfflineQueue: false,
    });
  }

  return new Redis({
    host: process.env.REDIS_HOST ?? '127.0.0.1',
    port: Number(process.env.REDIS_PORT ?? 6379),
    password: process.env.REDIS_PASSWORD || undefined,
    db: Number(process.env.REDIS_DB ?? 2),
    maxRetriesPerRequest: 3,
    lazyConnect: true,
    enableOfflineQueue: false,
  });
}

let client: RedisLike | null = null;

/** Singleton Redis for OTP cache (prefer DB 2 via REDIS_URL / REDIS_DB). */
export function getRedis(): RedisLike {
  if (!client) {
    client = createRedisClient();
  }
  return client;
}

/** Test/DI override — replace singleton (e.g. in-memory fake). */
export function setRedisForTests(redis: RedisLike | null): void {
  client = redis;
}

export async function disconnectRedis(): Promise<void> {
  if (client) {
    await client.quit().catch(() => undefined);
    client = null;
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
    async quit() {
      store.clear();
      return 'OK';
    },
  };
}
