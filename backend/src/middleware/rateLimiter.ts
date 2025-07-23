import { RateLimiterRedis } from 'rate-limiter-flexible';
import Redis from 'redis';
import { Request, Response, NextFunction } from 'express';
import env from '../utils/env.js';
import { logger } from '../utils/logger.js';
import { AppError } from './errorHandler.js';

const redisClient = Redis.createClient({
  url: env.REDIS_URL,
  enable_offline_queue: false,
});

redisClient.on('error', (err) => logger.error('Redis error:', err));

// General rate limiter - 100 requests per minute
const rateLimiter = new RateLimiterRedis({
  storeClient: redisClient,
  keyPrefix: 'middleware',
  points: 100, // Number of points
  duration: 60, // Per 60 seconds
});

// Stricter rate limit for auth endpoints - 5 attempts per minute
const authRateLimiter = new RateLimiterRedis({
  storeClient: redisClient,
  keyPrefix: 'auth',
  points: 5,
  duration: 60,
  blockDuration: 600, // Block for 10 minutes if exceeded
});

// Very strict rate limit for password reset - 3 attempts per hour
const passwordResetLimiter = new RateLimiterRedis({
  storeClient: redisClient,
  keyPrefix: 'reset',
  points: 3,
  duration: 3600,
  blockDuration: 3600,
});

export const rateLimiterMiddleware = async (req: Request, res: Response, next: NextFunction) => {
  const ip = req.ip;
  const userId = (req as any).user?.id || 'anonymous';
  const key = `${ip}-${userId}`;

  try {
    let limiter = rateLimiter;

    // Use stricter limits for sensitive endpoints
    if (req.path.includes('/auth')) {
      limiter = authRateLimiter;
    } else if (req.path.includes('/reset-password')) {
      limiter = passwordResetLimiter;
    }

    await limiter.consume(key);
    next();
  } catch (err) {
    if (err instanceof Error) {
      logger.warn(`Rate limit exceeded for ${key}`);
      const error = new AppError('Too many requests. Please try again later.');
      error.statusCode = 429;
      next(error);
    } else {
      next(err);
    }
  }
};
