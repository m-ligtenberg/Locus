import { NextFunction, Request, Response } from 'express';
import { ZodError } from 'zod';
import { logger } from '../utils/logger.js';

export interface AppError extends Error {
  statusCode: number;
  status: string;
}

export const errorHandler = (
  err: Error,
  req: Request,
  res: Response,
  next: NextFunction
) => {
  logger.error('Error:', {
    message: err.message,
    stack: err.stack,
    path: req.path,
    method: req.method
  });

  if (err instanceof ZodError) {
    return res.status(400).json({
      success: false,
      error: 'Validation error',
      details: err.errors
    });
  }

  if ((err as AppError).statusCode) {
    const appErr = err as AppError;
    return res.status(appErr.statusCode).json({
      success: false,
      error: err.message
    });
  }

  // Default to 500 server error
  return res.status(500).json({
    success: false,
    error: process.env.NODE_ENV === 'development' ? err.message : 'Internal server error'
  });
};
