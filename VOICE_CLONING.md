# 🎤 Locus Voice Cloning Guide

**Locus v5.0** now includes powerful voice cloning capabilities using Coqui TTS, allowing you to create custom voices for AI personas that sound incredibly natural.

## 🚀 **Quick Setup**

### **Option 1: Automatic Setup (Recommended)**
```bash
./setup-tts.sh
```

### **Option 2: Manual Setup**
```bash
# Start with TTS-enabled services
docker-compose -f docker-compose.tts.yml up -d

# Wait for models to download (first time takes 5-10 minutes)
docker-compose -f docker-compose.tts.yml logs -f coqui-tts
```

## 🎯 **Features**

### **🎭 Instant Voice Cloning**
- Upload any audio file (5-30 seconds)
- Clone the voice with new text immediately  
- No training required for quick tests

### **🤖 Custom AI Personas**
- Create unique voices for each AI persona:
  - 🏛️ **Amsterdammer** - Direct Amsterdam style
  - 🍺 **Belgique** - Melancholic Belgian humor
  - 🍻 **Brabander** - Friendly Brabant charm
  - 👑 **Jordanees** - Amsterdam neighborhood character

### **🏋️ Voice Model Training**
- Upload 5-20 audio samples per persona
- Train permanent voice models
- Improved quality over time
- Multi-language support (NL, EN, DE, FR)

## 📱 **Using the Admin Panel**

### **Access Voice Management**
1. Visit http://localhost:3000
2. Login as admin
3. Go to **Admin Panel** → **Voice Management**

### **Quick Voice Upload**
1. Click **Quick Voice Upload** tab
2. **Drag & drop** audio files or click to browse
3. **Record directly** using the microphone button
4. Add **transcripts** for each audio file
5. **Test clone** with sample text
6. **Create voice model** for permanent use

### **Voice Model Management**
1. Go to **Voice Models** tab
2. **Create new model** for each persona
3. **Upload samples** (5-20 audio files recommended)
4. **Start training** for better quality
5. **Activate model** for production use

## 🎵 **Audio Requirements**

### **For Best Results:**
- **Format**: WAV, MP3, M4A, or FLAC
- **Duration**: 5-30 seconds per sample
- **Quality**: Clear, high-quality audio
- **Environment**: Quiet recording space
- **Content**: Natural speech, varied sentences
- **Quantity**: 5-20 samples for training

### **Sample Recording Tips:**
```
✅ Good samples:
- "Hallo, welkom bij Locus navigatie"
- "We gaan nu rechtsaf bij het volgende kruispunt"
- "De bestemming is over 500 meter bereikt"

❌ Avoid:
- Background noise or music
- Very short clips (< 3 seconds)
- Distorted or low-quality audio
- Single words or very long speeches
```

## 🔧 **API Usage**

### **Generate TTS**
```javascript
const response = await fetch('/api/tts/generate', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': `Bearer ${token}`
  },
  body: JSON.stringify({
    text: 'Hallo, dit is een test van de spraaksynthese',
    persona: 'AMSTERDAMMER',
    language: 'NL'
  })
});

const { audioUrl } = await response.json();
```

### **Clone Voice**
```javascript
const formData = new FormData();
formData.append('speakerAudio', audioFile);
formData.append('text', 'Te klonen tekst');

const response = await fetch('/api/tts/clone', {
  method: 'POST',
  headers: { 'Authorization': `Bearer ${token}` },
  body: formData
});
```

## 🐳 **Docker Configuration**

### **Services Overview**
```yaml
services:
  coqui-tts:      # Voice cloning engine
  backend:        # API with TTS integration  
  frontend:       # Admin panel for voice management
  postgres:       # Voice model database
  redis:          # Audio caching
```

### **GPU Support**
```bash
# Enable GPU acceleration (if available)
docker-compose -f docker-compose.tts.yml up -d

# Check GPU usage
docker exec locus-coqui-tts nvidia-smi
```

### **CPU-Only Mode**
```bash
# Edit docker-compose.tts.yml, comment out GPU sections:
# deploy:
#   resources:
#     reservations:
#       devices:
#         - driver: nvidia
```

## 🎪 **Workflow Examples**

### **Scenario 1: Quick Voice Test**
1. Record 10-second audio sample
2. Upload via "Quick Voice Upload"
3. Add transcript: "Hallo, dit is mijn stem"
4. Test clone with: "Welkom bij Locus navigatie"
5. Download generated audio

### **Scenario 2: Professional Persona Voice**
1. Record 20 varied audio samples (5-30s each)
2. Create voice model for "Amsterdammer" persona
3. Upload all samples with accurate transcripts
4. Start training process (takes 1-2 hours)
5. Activate trained model for production
6. All AI observations use the custom voice

### **Scenario 3: Multi-Language Setup**
1. Record samples in Dutch for Dutch persona
2. Record samples in English for international users
3. Create separate voice models per language
4. System automatically uses correct voice based on user language

## 🛠️ **Troubleshooting**

### **Common Issues**

**TTS service not responding:**
```bash
# Check service status
docker-compose -f docker-compose.tts.yml ps

# View logs  
docker-compose -f docker-compose.tts.yml logs coqui-tts

# Restart TTS service
docker-compose -f docker-compose.tts.yml restart coqui-tts
```

**Audio generation is slow:**
- First-time model download takes 5-10 minutes
- CPU-only mode is slower than GPU
- Large texts take longer to process

**Poor voice quality:**
- Upload more/better audio samples
- Ensure samples have clear audio
- Train voice model for better results
- Check microphone quality for recordings

**Upload failures:**
- Check file size (max 50MB per file)
- Verify audio format (WAV/MP3/M4A/FLAC)
- Ensure stable internet connection

### **Performance Optimization**

**For better quality:**
```bash
# Use more audio samples (10-20 per persona)
# Record in quiet environment
# Use high-quality microphone
# Train models for production use
```

**For faster processing:**
```bash
# Enable GPU support
# Use shorter text passages
# Cache frequently used phrases
```

## 📊 **Monitoring**

### **Health Checks**
- TTS Service: http://localhost:8000/health
- Backend API: http://localhost:5000/health  
- Frontend: http://localhost:3000

### **Usage Statistics**
- View TTS history in admin panel
- Monitor voice model training progress
- Track audio generation performance

## 🎉 **Advanced Features**

### **Batch Processing**
```bash
# Generate multiple audio files at once
curl -X POST http://localhost:8000/generate \
  -H "Content-Type: application/json" \
  -d '{"text": "Batch text", "language": "nl"}'
```

### **Custom Model Training**
```python
# Train custom models with specific voice characteristics
# Advanced users can modify training parameters
# Fine-tune for specific use cases
```

### **Voice Mixing**
```javascript
// Combine multiple voice characteristics
// Create unique persona voices
// Adjust speech speed, pitch, emotion
```

---

## 🎭 **Ready to Start?**

1. **Run setup**: `./setup-tts.sh`
2. **Visit admin panel**: http://localhost:3000/admin
3. **Upload voice samples**: Go to Voice Management
4. **Test immediately**: Use Quick Voice Upload
5. **Create personas**: Build custom voice models

**Happy voice cloning!** 🎤✨