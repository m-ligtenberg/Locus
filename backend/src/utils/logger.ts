import winston from 'winston';
import env from './env.js';

const alignColorsAndTime = winston.format.combine(
  winston.format.colorize({
    all: true
  }),
  winston.format.timestamp({
    format: 'YYYY-MM-DD HH:mm:ss.SSS'
  }),
  winston.format.printf(
    info => `${info.timestamp} ${info.level}: ${info.message}`
  )
);

const logger = winston.createLogger({
  level: env.NODE_ENV === 'development' ? 'debug' : 'info',
  format: winston.format.combine(
    winston.format.errors({ stack: true }),
    winston.format.json()
  ),
  defaultMeta: { service: 'locus-backend' },
  transports: [
    new winston.transports.File({ 
      filename: 'logs/error.log', 
      level: 'error',
      maxsize: 5242880, // 5MB
      maxFiles: 5,
    }),
    new winston.transports.File({ 
      filename: 'logs/combined.log',
      maxsize: 5242880,
      maxFiles: 5,
    })
  ]
});

// If we're not in production, log to the console with colors
if (env.NODE_ENV !== 'production') {
  logger.add(new winston.transports.Console({
    format: alignColorsAndTime
  }));
}

// Create a stream for Morgan
export const stream = {
  write: (message: string) => {
    logger.info(message.trim());
  }
};

export { logger };
