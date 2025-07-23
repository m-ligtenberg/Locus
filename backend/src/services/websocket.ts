import { Server, Socket } from 'socket.io';
import jwt from 'jsonwebtoken';
import { logger } from '../utils/logger.js';
import { redis } from '../utils/redis.js';

interface AuthenticatedSocket extends Socket {
  userId?: string;
  userData?: {
    id: string;
    email: string;
    name: string;
  };
}

interface LocationUpdate {
  latitude: number;
  longitude: number;
  accuracy: number;
  speed?: number;
  heading?: number;
  timestamp: string;
}

interface NavigationInstruction {
  instruction: string;
  distance: number;
  maneuver: string;
  remainingDistance: number;
  remainingTime: number;
}

export const setupWebSocket = (io: Server) => {
  // Authentication middleware for WebSocket
  io.use(async (socket: AuthenticatedSocket, next) => {
    try {
      const token = socket.handshake.auth.token;
      if (!token) {
        return next(new Error('Authentication error'));
      }

      const decoded = jwt.verify(token, process.env.JWT_SECRET!) as any;
      socket.userId = decoded.userId;
      socket.userData = {
        id: decoded.userId,
        email: decoded.email,
        name: decoded.name,
      };

      logger.info(`User ${decoded.email} connected via WebSocket`);
      next();
    } catch (error) {
      logger.error('WebSocket authentication failed:', error);
      next(new Error('Authentication error'));
    }
  });

  io.on('connection', (socket: AuthenticatedSocket) => {
    const userId = socket.userId!;
    
    // Join user-specific room
    socket.join(`user:${userId}`);
    
    logger.info(`User ${userId} connected to WebSocket`);

    // Handle location updates
    socket.on('location:update', async (data: LocationUpdate) => {
      try {
        // Validate location data
        if (!data.latitude || !data.longitude) {
          socket.emit('error', { message: 'Invalid location data' });
          return;
        }

        // Store location update in Redis for real-time processing
        await redis.set(
          `location:${userId}`,
          JSON.stringify({
            ...data,
            timestamp: new Date().toISOString(),
          }),
          'EX',
          300 // 5 minutes TTL
        );

        // Broadcast to other connected clients for this user (if any)
        socket.to(`user:${userId}`).emit('location:updated', data);

        // Process location for AI observations
        socket.emit('location:processed', { success: true });
        
        logger.debug(`Location updated for user ${userId}`);
      } catch (error) {
        logger.error('Location update failed:', error);
        socket.emit('error', { message: 'Failed to process location update' });
      }
    });

    // Handle navigation events
    socket.on('navigation:start', async (routeId: string) => {
      try {
        // Join navigation room for real-time updates
        socket.join(`navigation:${routeId}`);
        
        // Notify other clients
        socket.to(`user:${userId}`).emit('navigation:started', { routeId });
        
        logger.info(`Navigation started for user ${userId}, route ${routeId}`);
      } catch (error) {
        logger.error('Navigation start failed:', error);
        socket.emit('error', { message: 'Failed to start navigation' });
      }
    });

    socket.on('navigation:stop', async (routeId: string) => {
      try {
        // Leave navigation room
        socket.leave(`navigation:${routeId}`);
        
        // Notify other clients
        socket.to(`user:${userId}`).emit('navigation:stopped', { routeId });
        
        logger.info(`Navigation stopped for user ${userId}, route ${routeId}`);
      } catch (error) {
        logger.error('Navigation stop failed:', error);
      }
    });

    // Handle AI observation requests
    socket.on('observation:request', async (locationData: LocationUpdate) => {
      try {
        // This would trigger AI observation generation
        // For now, emit a placeholder response
        socket.emit('observation:generated', {
          content: 'AI observation would be generated here',
          confidence: 0.8,
          timestamp: new Date().toISOString(),
        });
      } catch (error) {
        logger.error('Observation request failed:', error);
        socket.emit('error', { message: 'Failed to generate observation' });
      }
    });

    // Handle disconnection
    socket.on('disconnect', (reason) => {
      logger.info(`User ${userId} disconnected: ${reason}`);
      
      // Clean up user location data from Redis
      redis.del(`location:${userId}`).catch(err => {
        logger.error('Failed to clean up location data:', err);
      });
    });

    // Handle connection errors
    socket.on('error', (error) => {
      logger.error(`WebSocket error for user ${userId}:`, error);
    });
  });

  // Utility functions for sending messages to specific users
  const sendToUser = (userId: string, event: string, data: any) => {
    io.to(`user:${userId}`).emit(event, data);
  };

  const sendNavigationInstruction = (routeId: string, instruction: NavigationInstruction) => {
    io.to(`navigation:${routeId}`).emit('navigation:instruction', instruction);
  };

  const sendAIObservation = (userId: string, observation: any) => {
    io.to(`user:${userId}`).emit('ai:observation', observation);
  };

  // Export utility functions
  return {
    sendToUser,
    sendNavigationInstruction,
    sendAIObservation,
  };
};