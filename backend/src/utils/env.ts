import { z } from 'zod';
import dotenv from 'dotenv';

dotenv.config();

const envSchema = z.object({
  NODE_ENV: z.enum(['development', 'test', 'production']).default('development'),
  PORT: z.string().transform(Number).default('5000'),
  DATABASE_URL: z.string(),
  JWT_SECRET: z.string(),
  JWT_REFRESH_SECRET: z.string(),
  FRONTEND_URL: z.string().url(),
  REDIS_URL: z.string().optional(),
  OPENAI_API_KEY: z.string(),
  COQUI_API_KEY: z.string(),
  UPLOAD_DIR: z.string().default('./data/uploads'),
  MAX_FILE_SIZE: z.string().transform(Number).default('52428800'), // 50MB
});

const env = envSchema.parse(process.env);

export default env;
