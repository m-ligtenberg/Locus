import jwt from 'jsonwebtoken';
import bcrypt from 'bcryptjs';

// Validate required environment variables
const JWT_SECRET = process.env.JWT_SECRET;
const JWT_REFRESH_SECRET = process.env.JWT_REFRESH_SECRET;

if (!JWT_SECRET || !JWT_REFRESH_SECRET) {
  throw new Error('Missing required environment variables: JWT_SECRET and JWT_REFRESH_SECRET');
}

import { PrismaClient } from '@prisma/client';
const prisma = new PrismaClient();

// Refresh tokens will be stored in the database through Prisma
async function storeRefreshToken(userId, token) {
  await prisma.refreshToken.create({
    data: {
      token,
      userId,
      expiresAt: new Date(Date.now() + 7 * 24 * 60 * 60 * 1000) // 7 days
    }
  });
}

async function revokeRefreshToken(token) {
  await prisma.refreshToken.delete({
    where: { token }
  });
}

export default async function handler(req, res) {
  // Proper CORS for frontend domain
  const origin = req.headers.origin;
  const allowedOrigins = [
    'http://localhost:3000',
    'https://locus.vercel.app',
    process.env.FRONTEND_URL
  ].filter(Boolean);

  if (allowedOrigins.includes(origin)) {
    res.setHeader('Access-Control-Allow-Origin', origin);
  }
  
  res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
  res.setHeader('Access-Control-Allow-Credentials', 'true');
  
  if (req.method === 'OPTIONS') {
    return res.status(200).end();
  }

  try {
    const { method, body } = req;
    
    if (method !== 'POST') {
      return res.status(405).json({ success: false, error: 'Method not allowed' });
    }

    // Parse action from URL path
    const urlPath = req.url || '';
    let action = body?.action;
    
    if (urlPath.includes('/register')) action = 'register';
    else if (urlPath.includes('/login')) action = 'login';
    else if (urlPath.includes('/refresh')) action = 'refresh';
    else if (urlPath.includes('/logout')) action = 'logout';

    switch (action) {
      case 'register':
        return await register(req, res);
      case 'login':
        return await login(req, res);
      case 'refresh':
        return await refresh(req, res);
      case 'logout':
        return await logout(req, res);
      default:
        return res.status(400).json({ success: false, error: 'Invalid action' });
    }
  } catch (error) {
    console.error('Auth error:', error);
    return res.status(500).json({ success: false, error: 'Internal server error' });
  }
}

async function register(req, res) {
  const { email, password, name } = req.body;

  if (!email || !password || !name) {
    return res.status(400).json({ success: false, error: 'Missing required fields' });
  }

  if (password.length < 6) {
    return res.status(400).json({ success: false, error: 'Password must be at least 6 characters' });
  }

  if (users.has(email)) {
    return res.status(400).json({ success: false, error: 'User already exists' });
  }

  const hashedPassword = await bcrypt.hash(password, 10);
  const userId = `user_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
  
  const user = {
    id: userId,
    email,
    name,
    password: hashedPassword,
    createdAt: new Date().toISOString()
  };

  users.set(email, user);

  const token = jwt.sign({ userId: user.id, email }, JWT_SECRET, { expiresIn: '1h' });
  const refreshToken = jwt.sign({ userId: user.id }, JWT_REFRESH_SECRET, { expiresIn: '7d' });
  
  refreshTokens.add(refreshToken);

  res.json({
    success: true,
    data: {
      user: { id: user.id, email: user.email, name: user.name },
      token,
      refreshToken
    }
  });
}

async function login(req, res) {
  const { email, password } = req.body;

  if (!email || !password) {
    return res.status(400).json({ success: false, error: 'Missing email or password' });
  }

  const user = users.get(email);
  if (!user) {
    return res.status(401).json({ success: false, error: 'Invalid credentials' });
  }

  const isValidPassword = await bcrypt.compare(password, user.password);
  if (!isValidPassword) {
    return res.status(401).json({ success: false, error: 'Invalid credentials' });
  }

  const token = jwt.sign({ userId: user.id, email }, JWT_SECRET, { expiresIn: '1h' });
  const refreshToken = jwt.sign({ userId: user.id }, JWT_REFRESH_SECRET, { expiresIn: '7d' });
  
  refreshTokens.add(refreshToken);

  res.json({
    success: true,
    data: {
      user: { id: user.id, email: user.email, name: user.name },
      token,
      refreshToken
    }
  });
}

async function refresh(req, res) {
  const { refreshToken } = req.body;

  if (!refreshToken || !refreshTokens.has(refreshToken)) {
    return res.status(401).json({ success: false, error: 'Invalid refresh token' });
  }

  try {
    const decoded = jwt.verify(refreshToken, JWT_REFRESH_SECRET);
    const newToken = jwt.sign({ userId: decoded.userId }, JWT_SECRET, { expiresIn: '1h' });
    
    res.json({ success: true, data: { token: newToken } });
  } catch (error) {
    refreshTokens.delete(refreshToken);
    return res.status(401).json({ success: false, error: 'Invalid refresh token' });
  }
}

async function logout(req, res) {
  const { refreshToken } = req.body;
  if (refreshToken) {
    refreshTokens.delete(refreshToken);
  }
  res.json({ success: true, message: 'Logged out successfully' });
}