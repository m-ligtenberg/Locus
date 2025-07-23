#!/bin/bash

# Locus TTS Setup Script
# Sets up Coqui TTS voice cloning integration

echo "🎤 Setting up Locus with Coqui TTS Voice Cloning..."

# Create necessary directories
echo "📁 Creating data directories..."
mkdir -p data/voice_models
mkdir -p data/tts_output  
mkdir -p data/voice_samples
mkdir -p docker/coqui-tts

# Set permissions
chmod 755 data/voice_models data/tts_output data/voice_samples

echo "📦 Building Docker containers..."

# Check if Docker is installed
if ! command -v docker &> /dev/null; then
    echo "❌ Docker is not installed. Please install Docker first."
    exit 1
fi

# Check if Docker Compose is installed
if ! command -v docker-compose &> /dev/null; then
    echo "❌ Docker Compose is not installed. Please install Docker Compose first."
    exit 1
fi

# Build and start services
echo "🚀 Starting Locus with TTS services..."
docker-compose -f docker-compose.tts.yml up -d --build

# Wait for services to be ready
echo "⏳ Waiting for services to be ready..."
sleep 30

# Check service health
echo "🔍 Checking service health..."

# Check Coqui TTS service
if curl -f http://localhost:8000/health > /dev/null 2>&1; then
    echo "✅ Coqui TTS service is running"
else
    echo "⚠️  Coqui TTS service may still be starting (this can take a few minutes)"
fi

# Check backend service  
if curl -f http://localhost:5000/health > /dev/null 2>&1; then
    echo "✅ Backend service is running"
else
    echo "⚠️  Backend service may still be starting"
fi

# Check frontend service
if curl -f http://localhost:3000 > /dev/null 2>&1; then
    echo "✅ Frontend service is running"
else
    echo "⚠️  Frontend service may still be starting"
fi

echo ""
echo "🎉 Setup complete!"
echo ""
echo "📍 Access points:"
echo "  • Frontend:    http://localhost:3000"
echo "  • Backend API: http://localhost:5000"  
echo "  • TTS Service: http://localhost:8000"
echo ""
echo "🎤 Voice cloning features:"
echo "  • Upload audio samples via admin panel"
echo "  • Clone voices instantly with any text"
echo "  • Train custom voice models"
echo "  • Multiple AI personas with unique voices"
echo ""
echo "📖 Next steps:"
echo "  1. Visit http://localhost:3000 to access Locus"
echo "  2. Go to Admin Panel → Voice Management"
echo "  3. Upload voice samples (5-20 audio files)"
echo "  4. Test voice cloning with sample text"
echo "  5. Create permanent voice models for personas"
echo ""

# Check GPU support
if command -v nvidia-smi &> /dev/null; then
    echo "🎯 GPU detected! TTS will use GPU acceleration for faster processing."
else
    echo "💻 No GPU detected. TTS will use CPU (slower but still functional)."
fi

echo ""
echo "🛠️  Troubleshooting:"
echo "  • If services are slow to start, wait 2-3 minutes for model downloads"
echo "  • Check logs: docker-compose -f docker-compose.tts.yml logs -f"
echo "  • Restart services: docker-compose -f docker-compose.tts.yml restart"
echo ""

echo "🎤 Locus TTS setup complete! Happy voice cloning! 🎭"