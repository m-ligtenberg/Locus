#!/bin/bash

echo "🧭 Starting Locus v5.0 with Voice Cloning..."

# Check if .env exists
if [ ! -f .env ]; then
    echo "❌ .env file not found! Please create it with your OpenAI API key."
    exit 1
fi

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    echo "❌ Docker is not running. Please start Docker first."
    exit 1
fi

# Create data directories if they don't exist
echo "📁 Creating data directories..."
mkdir -p data/voice_models data/tts_output data/voice_samples

# Check which compose file to use
if [ -f "docker-compose.tts.yml" ]; then
    echo "🎤 Starting with TTS voice cloning support..."
    docker-compose -f docker-compose.tts.yml up -d --build
else
    echo "📱 Starting basic Locus (no TTS support)..."
    docker-compose up -d --build
fi

echo "⏳ Waiting for services to start..."
sleep 10

# Check service status
echo "🔍 Checking services..."

if curl -f http://localhost:5000/health > /dev/null 2>&1; then
    echo "✅ Backend is running"
else
    echo "⚠️  Backend is still starting..."
fi

if curl -f http://localhost:3000 > /dev/null 2>&1; then
    echo "✅ Frontend is running"
else
    echo "⚠️  Frontend is still starting..."
fi

# Check if TTS service exists
if docker ps | grep -q "locus-coqui-tts"; then
    if curl -f http://localhost:8000/health > /dev/null 2>&1; then
        echo "✅ TTS service is running"
    else
        echo "⚠️  TTS service is still loading models (this can take 5-10 minutes on first run)"
    fi
fi

echo ""
echo "🎉 Locus is starting up!"
echo ""
echo "📍 Access points:"
echo "  • Frontend:  http://localhost:3000"
echo "  • Backend:   http://localhost:5000"
if docker ps | grep -q "locus-coqui-tts"; then
    echo "  • TTS API:   http://localhost:8000"
    echo ""
    echo "🎤 Voice Cloning Features:"
    echo "  • Go to Admin Panel → Voice Management"
    echo "  • Upload audio samples for voice cloning"
    echo "  • Create custom AI persona voices"
fi
echo ""
echo "📖 View logs: docker-compose logs -f"
echo "🛑 Stop all:  docker-compose down"
echo ""