# Multi-stage build for Node.js backend
FROM node:20-alpine AS builder

WORKDIR /app

# Copy package files
COPY package*.json ./
COPY prisma ./prisma/

# Install dependencies
RUN npm ci --only=production && npm cache clean --force

# Generate Prisma client
RUN npx prisma generate

# Copy source code
COPY src ./src
COPY tsconfig.json ./

# Build TypeScript
RUN npm run build

# Production stage
FROM node:20-alpine AS production

WORKDIR /app

# Create non-root user
RUN addgroup -g 1001 -S nodejs && \
    adduser -S locus -u 1001

# Copy built application
COPY --from=builder --chown=locus:nodejs /app/dist ./dist
COPY --from=builder --chown=locus:nodejs /app/node_modules ./node_modules
COPY --from=builder --chown=locus:nodejs /app/prisma ./prisma
COPY --from=builder --chown=locus:nodejs /app/package*.json ./

# Create uploads directory
RUN mkdir -p uploads && chown -R locus:nodejs uploads

# Switch to non-root user
USER locus

# Expose port
EXPOSE 5000

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
  CMD node -e "require('http').get('http://localhost:5000/health', (res) => { process.exit(res.statusCode === 200 ? 0 : 1) })"

# Start application
CMD ["node", "dist/index.js"]