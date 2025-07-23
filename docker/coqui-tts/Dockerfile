# Coqui TTS Docker Container
FROM python:3.9-slim

# Set working directory
WORKDIR /app

# Install system dependencies
RUN apt-get update && apt-get install -y \
    gcc \
    g++ \
    make \
    libffi-dev \
    libssl-dev \
    libsndfile1-dev \
    ffmpeg \
    espeak-ng \
    espeak-ng-data \
    wget \
    curl \
    && rm -rf /var/lib/apt/lists/*

# Install Python dependencies
RUN pip install --no-cache-dir \
    torch==2.1.0 \
    torchaudio==2.1.0 \
    --index-url https://download.pytorch.org/whl/cpu

# Install Coqui TTS
RUN pip install --no-cache-dir TTS==0.22.0

# Install additional audio processing libraries
RUN pip install --no-cache-dir \
    librosa==0.10.1 \
    soundfile==0.12.1 \
    pydub==0.25.1 \
    flask==2.3.3 \
    flask-cors==4.0.0

# Create directories
RUN mkdir -p /app/voice_models /app/tts_output /app/voice_samples /app/.cache

# Copy TTS service script
COPY tts_service.py /app/tts_service.py
COPY requirements.txt /app/requirements.txt

# Install additional requirements if any
RUN pip install --no-cache-dir -r requirements.txt

# Set environment variables
ENV PYTHONUNBUFFERED=1
ENV COQUI_TTS_CACHE_PATH=/app/.cache
ENV FLASK_APP=tts_service.py

# Expose port
EXPOSE 8000

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=60s --retries=3 \
    CMD curl -f http://localhost:8000/health || exit 1

# Start the TTS service
CMD ["python", "tts_service.py"]