import express from 'express';
import cors from 'cors';
import helmet from 'helmet';
import { createServer } from 'http';
import { Server } from 'socket.io';
import dotenv from 'dotenv';

import { authRouter } from './routes/auth.js';
import { usersRouter } from './routes/users.js';
import { routesRouter } from './routes/routes.js';
import { observationsRouter } from './routes/observations.js';
import { navigationRouter } from './routes/navigation.js';
import { locationRouter } from './routes/location.js';

import { authMiddleware } from './middleware/auth.js';
import { errorHandler } from './middleware/errorHandler.js';
import { rateLimiter } from './middleware/rateLimiter.js';
import { logger } from './utils/logger.js';
import { setupWebSocket } from './services/websocket.js';
import { AIObservationService } from './services/aiObservation.js';
import { setupCronJobs } from './jobs/index.js';

dotenv.config();

const app = express();
const server = createServer(app);
const io = new Server(server, {
  cors: {
    origin: process.env.FRONTEND_URL || 'http://localhost:3000',
    methods: ['GET', 'POST'],
  },
});

const PORT = process.env.PORT || 5000;

// Security middleware
app.use(helmet());
app.use(cors({
  origin: process.env.FRONTEND_URL || 'http://localhost:3000',
  credentials: true,
}));

// Rate limiting
app.use(rateLimiter);

// Body parsing
app.use(express.json({ limit: '10mb' }));
app.use(express.urlencoded({ extended: true }));

// Health check
app.get('/health', (req, res) => {
  res.json({ 
    status: 'healthy', 
    timestamp: new Date().toISOString(),
    version: '5.0.0' 
  });
});

// Public routes
app.use('/api/auth', authRouter);

// Protected routes
app.use('/api/users', authMiddleware, usersRouter);
app.use('/api/routes', authMiddleware, routesRouter);
app.use('/api/observations', authMiddleware, observationsRouter);
app.use('/api/navigation', authMiddleware, navigationRouter);
app.use('/api/location', authMiddleware, locationRouter);

// Error handling
app.use(errorHandler);

// 404 handler
app.use('*', (req, res) => {
  res.status(404).json({ 
    success: false, 
    message: 'Route not found' 
  });
});

// Setup WebSocket
setupWebSocket(io);

// Initialize services
const aiObservationService = new AIObservationService();
app.locals.aiObservationService = aiObservationService;

// Setup cron jobs
setupCronJobs();

// Start server
server.listen(PORT, () => {
  logger.info(`🚀 Locus Backend v5.0 running on port ${PORT}`);
  logger.info(`📍 Environment: ${process.env.NODE_ENV || 'development'}`);
  logger.info(`🌐 Frontend URL: ${process.env.FRONTEND_URL || 'http://localhost:3000'}`);
});

// Graceful shutdown
process.on('SIGTERM', () => {
  logger.info('SIGTERM received, shutting down gracefully');
  server.close(() => {
    logger.info('Process terminated');
    process.exit(0);
  });
});

export { app, io };