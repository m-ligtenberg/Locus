import { describe, it, expect, beforeEach, afterEach } from 'vitest';
import { PrismaClient } from '@prisma/client';
import supertest from 'supertest';
import { app } from '../src/index.js';
import jwt from 'jsonwebtoken';

const prisma = new PrismaClient();
const request = supertest(app);

describe('Auth Endpoints', () => {
  beforeEach(async () => {
    await prisma.user.deleteMany();
  });

  afterEach(async () => {
    await prisma.user.deleteMany();
  });

  it('should register a new user', async () => {
    const res = await request
      .post('/api/auth/register')
      .send({
        email: 'test@example.com',
        password: 'password123',
        name: 'Test User'
      });

    expect(res.status).toBe(201);
    expect(res.body.success).toBe(true);
    expect(res.body.data.user).toBeDefined();
    expect(res.body.data.token).toBeDefined();
  });

  it('should login an existing user', async () => {
    // First register a user
    await request
      .post('/api/auth/register')
      .send({
        email: 'test@example.com',
        password: 'password123',
        name: 'Test User'
      });

    // Then try to login
    const res = await request
      .post('/api/auth/login')
      .send({
        email: 'test@example.com',
        password: 'password123'
      });

    expect(res.status).toBe(200);
    expect(res.body.success).toBe(true);
    expect(res.body.data.user).toBeDefined();
    expect(res.body.data.token).toBeDefined();
  });
});

describe('Protected Routes', () => {
  let authToken: string;

  beforeEach(async () => {
    await prisma.user.deleteMany();
    
    // Create a test user and get token
    const registerRes = await request
      .post('/api/auth/register')
      .send({
        email: 'test@example.com',
        password: 'password123',
        name: 'Test User'
      });
    
    authToken = registerRes.body.data.token;
  });

  afterEach(async () => {
    await prisma.user.deleteMany();
  });

  it('should access protected route with valid token', async () => {
    const res = await request
      .get('/api/users/profile')
      .set('Authorization', `Bearer ${authToken}`);

    expect(res.status).toBe(200);
    expect(res.body.success).toBe(true);
  });

  it('should reject access without token', async () => {
    const res = await request
      .get('/api/users/profile');

    expect(res.status).toBe(401);
    expect(res.body.success).toBe(false);
  });
});
