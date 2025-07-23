# Locus - Vercel Deployment Guide

## 🚀 Deploy to Vercel

### 1. Install Vercel CLI
```bash
npm install -g vercel
```

### 2. Login to Vercel
```bash
vercel login
```

### 3. Deploy the Project
```bash
cd /home/mich/Locus
vercel --prod
```

### 4. Set Environment Variables in Vercel Dashboard

Go to your Vercel project dashboard and add these environment variables:

**Required Variables:**
- `OPENAI_API_KEY`: `your-actual-openai-api-key`
- `JWT_SECRET`: `locus-production-jwt-secret-2024-super-secure-random-string`
- `JWT_REFRESH_SECRET`: `locus-production-refresh-secret-2024-another-secure-random-string`
- `NODE_ENV`: `production`

### 5. API Endpoints

Once deployed, your API will be available at:
- Authentication: `https://your-app.vercel.app/api/auth`
- Chat: `https://your-app.vercel.app/api/chat`
- TTS: `https://your-app.vercel.app/api/tts`

### 6. Features Available

✅ **Working Features:**
- User authentication (register/login)
- AI chat with OpenAI GPT-4
- Text-to-speech using OpenAI TTS
- Multiple AI personas (assistant, guide, planner, explorer)
- Voice model management
- Responsive React frontend

⚠️ **Limitations:**
- No Coqui TTS (replaced with OpenAI TTS)
- In-memory data storage (no persistent database)
- Basic voice model simulation

### 7. Frontend Configuration

The frontend is automatically configured to use Vercel API endpoints via:
```typescript
const API_BASE = process.env.VITE_API_URL || '/api';
```

### 8. Local Development
```bash
# Install dependencies
npm install

# Start development server
vercel dev

# Build for production
npm run build
```

### 9. Project Structure
```
Locus/
├── api/                 # Vercel serverless functions
│   ├── auth.js         # Authentication API
│   ├── chat.js         # Chat/AI API
│   └── tts.js          # Text-to-speech API
├── frontend/           # React frontend
├── vercel.json         # Vercel configuration
└── package.json        # Dependencies
```

### 10. Database Integration (Optional)

For persistent data, integrate with:
- **Vercel Postgres**: Add to your project in Vercel dashboard
- **PlanetScale**: MySQL-compatible serverless database
- **Supabase**: PostgreSQL with real-time features

Update `DATABASE_URL` environment variable accordingly.