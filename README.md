# >� Locus v5.0 - Modern AI Navigation Platform

**Locus v5.0** is a complete rewrite of the navigation platform with modern, scalable architecture. Built with React, TypeScript, Node.js, and PostgreSQL for enterprise-grade reliability and performance.

## <� **Architecture Overview**

### **Frontend** (React + TypeScript + Vite)
- � **Modern Stack**: React 18, TypeScript, Vite, TailwindCSS
- <� **State Management**: Zustand for global state, React Query for server state
- =� **PWA Ready**: Service workers, offline functionality, installable
- =� **Maps**: React Leaflet with real-time updates
- = **Real-time**: Socket.IO for live location updates

### **Backend** (Node.js + Express + TypeScript)
- =� **Modern APIs**: RESTful endpoints with OpenAPI documentation
- = **Authentication**: JWT with refresh tokens, bcrypt hashing
- =� **Database**: PostgreSQL with Prisma ORM
- � **Caching**: Redis for session management and real-time data
- > **AI Integration**: OpenAI API for intelligent observations
- =� **WebSockets**: Real-time navigation and location updates

### **Infrastructure**
- =3 **Containerized**: Docker & Docker Compose for consistent deployment
-  **Cloud Ready**: Kubernetes manifests, CI/CD with GitHub Actions
- =� **Monitoring**: Structured logging, health checks, metrics
- = **Security**: Rate limiting, CORS, helmet.js, input validation

## =� **Quick Start**

### **Prerequisites**
- Node.js 20+
- Docker & Docker Compose
- PostgreSQL 16+ (or use Docker)

### **Development Setup**

1. **Clone and setup:**
```bash
git clone https://github.com/yourusername/locus.git
cd locus
cp backend/.env.example backend/.env
cp frontend/.env.example frontend/.env
```

2. **Start with Docker:**
```bash
docker-compose up -d
```

3. **Or run locally:**
```bash
# Backend
cd backend
npm install
npx prisma migrate dev
npm run dev

# Frontend (new terminal)
cd frontend
npm install
npm run dev
```

4. **Access the application:**
- Frontend: http://localhost:3000
- Backend API: http://localhost:5000
- API Docs: http://localhost:5000/api/docs

## =� **Features**

### >� **Core Navigation**
- Real-time GPS tracking with WebSocket updates
- Turn-by-turn navigation with voice instructions
- Route optimization using OpenRouteService API
- Offline map caching for areas without connectivity
- Smart rerouting when user deviates from route

### > **AI-Powered Observations**
- Context-aware location commentary
- POI detection and interesting landmark identification  
- Personality-driven responses (multiple Dutch personas)
- Smart timing to avoid observation spam
- Machine learning for improving relevance

### =e **User Management**
- Secure authentication with JWT tokens
- User preferences and persona selection
- Route history and favorite locations
- Privacy-first approach with data encryption
- Social features for sharing routes

### =� **Analytics & Insights**
- Route performance metrics
- User behavior analytics
- AI observation effectiveness tracking
- Real-time system monitoring
- A/B testing for feature improvements

## =� **Development**

### **Project Structure**
```
locus/
   frontend/                 # React TypeScript frontend
      src/
         components/      # Reusable UI components
         hooks/          # Custom React hooks
         pages/          # Page components
         stores/         # Zustand state stores
         services/       # API services
         types/          # TypeScript definitions
         utils/          # Helper functions
      public/             # Static assets
      tests/              # Frontend tests
   backend/                 # Node.js TypeScript backend
      src/
         routes/         # API route handlers
         services/       # Business logic services
         middleware/     # Express middleware
         utils/          # Helper utilities
         jobs/           # Background job processing
      prisma/             # Database schema & migrations
      tests/              # Backend tests
   docker-compose.yml      # Local development setup
   .github/workflows/      # CI/CD pipelines
   docs/                   # Documentation
```

### **Available Scripts**

**Frontend:**
```bash
npm run dev      # Start development server
npm run build    # Build for production
npm run test     # Run tests
npm run lint     # Run ESLint
```

**Backend:**
```bash
npm run dev      # Start development server with hot reload
npm run build    # Build TypeScript to JavaScript
npm run start    # Start production server
npm run test     # Run tests
npm run migrate  # Run database migrations
```

## =� **Deployment**

### **Production Deployment**

1. **Using Docker Compose:**
```bash
docker-compose -f docker-compose.prod.yml up -d
```

2. **Using Kubernetes:**
```bash
kubectl apply -f k8s/
```

3. **Environment Variables:**
```bash
# Backend
DATABASE_URL=postgresql://user:pass@host:5432/locus
REDIS_URL=redis://host:6379
JWT_SECRET=your-super-secret-key
OPENAI_API_KEY=sk-your-openai-key

# Frontend
VITE_API_URL=https://api.yourdomain.com/api
VITE_WS_URL=https://api.yourdomain.com
```

### **CI/CD Pipeline**
-  Automated testing on pull requests
- =3 Docker image building and pushing
- =� Automatic deployment to staging/production
- =� Health checks and rollback capabilities

## > **Contributing**

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## =� **License**

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## = **Links**

- **Documentation**: [docs.locus.app](https://docs.locus.app)
- **API Reference**: [api.locus.app/docs](https://api.locus.app/docs)
- **Demo**: [demo.locus.app](https://demo.locus.app)
- **Status Page**: [status.locus.app](https://status.locus.app)

---

**Built with d by the Locus team**