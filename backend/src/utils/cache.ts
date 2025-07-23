import Redis from 'redis';
import { promisify } from 'util';
import env from './env.js';
import { logger } from './logger.js';

const redisClient = Redis.createClient({
  url: env.REDIS_URL,
  socket: {
    reconnectStrategy: (retries) => {
      if (retries > 10) {
        logger.error('Redis reconnection failed');
        return new Error('Redis max retries reached');
      }
      return Math.min(retries * 100, 3000);
    },
  }
});

redisClient.on('error', (err) => logger.error('Redis error:', err));
redisClient.on('connect', () => logger.info('Redis connected'));

const getAsync = promisify(redisClient.get).bind(redisClient);
const setAsync = promisify(redisClient.set).bind(redisClient);
const delAsync = promisify(redisClient.del).bind(redisClient);

export const cache = {
  async get<T>(key: string): Promise<T | null> {
    const data = await getAsync(key);
    return data ? JSON.parse(data) : null;
  },

  async set(key: string, value: any, expireSeconds?: number): Promise<void> {
    const stringValue = JSON.stringify(value);
    if (expireSeconds) {
      await setAsync(key, stringValue, 'EX', expireSeconds);
    } else {
      await setAsync(key, stringValue);
    }
  },

  async del(key: string): Promise<void> {
    await delAsync(key);
  },

  getClient() {
    return redisClient;
  }
};

export const cacheMiddleware = (expireSeconds = 300) => {
  return async (req: any, res: any, next: any) => {
    if (req.method !== 'GET') {
      return next();
    }

    const key = `cache:${req.originalUrl}`;
    
    try {
      const cachedData = await cache.get(key);
      if (cachedData) {
        return res.json(cachedData);
      }

      const originalJson = res.json;
      res.json = function(data: any) {
        cache.set(key, data, expireSeconds);
        return originalJson.call(this, data);
      };

      next();
    } catch (error) {
      logger.error('Cache middleware error:', error);
      next();
    }
  };
};
