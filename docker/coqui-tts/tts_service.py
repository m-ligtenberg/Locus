#!/usr/bin/env python3
"""
Coqui TTS Microservice for Locus
Provides REST API for text-to-speech and voice cloning
"""

import os
import json
import tempfile
import uuid
from pathlib import Path
from flask import Flask, request, jsonify, send_file
from flask_cors import CORS
import torch
from TTS.api import TTS
from TTS.tts.configs.xtts_config import XttsConfig
from TTS.tts.models.xtts import Xtts
import soundfile as sf
import librosa

app = Flask(__name__)
CORS(app)

# Configuration
MODELS_DIR = Path("/app/voice_models")
OUTPUT_DIR = Path("/app/tts_output")
SAMPLES_DIR = Path("/app/voice_samples")
CACHE_DIR = Path("/app/.cache")

# Ensure directories exist
MODELS_DIR.mkdir(exist_ok=True)
OUTPUT_DIR.mkdir(exist_ok=True)
SAMPLES_DIR.mkdir(exist_ok=True)
CACHE_DIR.mkdir(exist_ok=True)

# Global TTS models
tts_models = {}
device = "cuda" if torch.cuda.is_available() else "cpu"

class TTSService:
    def __init__(self):
        self.models = {}
        self.device = device
        print(f"🎤 TTS Service initializing on device: {self.device}")
        self.load_default_models()

    def load_default_models(self):
        """Load default multilingual models"""
        try:
            # Load XTTS v2 for voice cloning
            print("📥 Loading XTTS v2 model...")
            self.models['xtts'] = TTS("tts_models/multilingual/multi-dataset/xtts_v2").to(self.device)
            
            # Load language-specific models
            language_models = {
                'nl': 'tts_models/nl/css10/vits',
                'en': 'tts_models/en/ljspeech/tacotron2-DDC',
                'de': 'tts_models/de/thorsten/tacotron2-DDC',
                'fr': 'tts_models/fr/css10/vits'
            }
            
            for lang, model_name in language_models.items():
                try:
                    print(f"📥 Loading {lang} model: {model_name}")
                    self.models[lang] = TTS(model_name).to(self.device)
                except Exception as e:
                    print(f"⚠️  Failed to load {lang} model: {e}")
                    
            print("✅ TTS Service loaded successfully")
            
        except Exception as e:
            print(f"❌ Failed to load TTS models: {e}")
            raise

    def generate_speech(self, text, language='en', speaker_wav=None, output_path=None):
        """Generate speech from text"""
        if output_path is None:
            output_path = OUTPUT_DIR / f"tts_{uuid.uuid4().hex[:8]}.wav"
        
        try:
            if speaker_wav and 'xtts' in self.models:
                # Use voice cloning with XTTS
                print(f"🎭 Generating speech with voice cloning: {speaker_wav}")
                self.models['xtts'].tts_to_file(
                    text=text,
                    speaker_wav=speaker_wav,
                    language=language,
                    file_path=str(output_path)
                )
            elif language in self.models:
                # Use language-specific model
                print(f"🗣️  Generating speech with {language} model")
                self.models[language].tts_to_file(
                    text=text,
                    file_path=str(output_path)
                )
            else:
                # Fallback to XTTS without speaker
                print(f"🗣️  Generating speech with XTTS (no speaker)")
                self.models['xtts'].tts_to_file(
                    text=text,
                    language=language,
                    file_path=str(output_path)
                )
                
            return str(output_path)
            
        except Exception as e:
            print(f"❌ Speech generation failed: {e}")
            raise

    def clone_voice(self, text, speaker_wav_path, language='en', output_path=None):
        """Clone voice from speaker sample"""
        if output_path is None:
            output_path = OUTPUT_DIR / f"clone_{uuid.uuid4().hex[:8]}.wav"
            
        try:
            if 'xtts' not in self.models:
                raise Exception("XTTS model not loaded for voice cloning")
                
            print(f"🎭 Cloning voice from: {speaker_wav_path}")
            
            self.models['xtts'].tts_to_file(
                text=text,
                speaker_wav=speaker_wav_path,
                language=language,
                file_path=str(output_path)
            )
            
            return str(output_path)
            
        except Exception as e:
            print(f"❌ Voice cloning failed: {e}")
            raise

    def get_audio_info(self, audio_path):
        """Get audio file information"""
        try:
            y, sr = librosa.load(audio_path)
            duration = librosa.get_duration(y=y, sr=sr)
            return {
                'duration': duration,
                'sample_rate': sr,
                'channels': 1 if y.ndim == 1 else y.shape[0]
            }
        except Exception as e:
            print(f"⚠️  Failed to get audio info: {e}")
            return {'duration': 0, 'sample_rate': 22050, 'channels': 1}

# Initialize TTS service
print("🚀 Starting Coqui TTS Service...")
tts_service = TTSService()

@app.route('/health', methods=['GET'])
def health_check():
    """Health check endpoint"""
    return jsonify({
        'status': 'healthy',
        'device': device,
        'models_loaded': list(tts_service.models.keys()),
        'version': '1.0.0'
    })

@app.route('/models', methods=['GET'])
def list_models():
    """List available TTS models"""
    return jsonify({
        'success': True,
        'models': list(tts_service.models.keys()),
        'device': device
    })

@app.route('/generate', methods=['POST'])
def generate_tts():
    """Generate TTS audio"""
    try:
        data = request.get_json()
        text = data.get('text', '')
        language = data.get('language', 'en').lower()
        
        if not text.strip():
            return jsonify({'success': False, 'error': 'Text is required'}), 400
            
        # Generate speech
        output_path = tts_service.generate_speech(text, language)
        audio_info = tts_service.get_audio_info(output_path)
        
        return jsonify({
            'success': True,
            'audio_path': output_path,
            'audio_info': audio_info,
            'text': text,
            'language': language
        })
        
    except Exception as e:
        return jsonify({'success': False, 'error': str(e)}), 500

@app.route('/clone', methods=['POST'])
def clone_voice():
    """Clone voice from speaker sample"""
    try:
        # Get form data
        text = request.form.get('text', '')
        language = request.form.get('language', 'en').lower()
        
        if not text.strip():
            return jsonify({'success': False, 'error': 'Text is required'}), 400
            
        # Get uploaded speaker audio
        if 'speaker_audio' not in request.files:
            return jsonify({'success': False, 'error': 'Speaker audio file is required'}), 400
            
        speaker_file = request.files['speaker_audio']
        if speaker_file.filename == '':
            return jsonify({'success': False, 'error': 'No file selected'}), 400
            
        # Save speaker audio temporarily
        speaker_path = SAMPLES_DIR / f"speaker_{uuid.uuid4().hex[:8]}_{speaker_file.filename}"
        speaker_file.save(str(speaker_path))
        
        try:
            # Clone voice
            output_path = tts_service.clone_voice(text, str(speaker_path), language)
            audio_info = tts_service.get_audio_info(output_path)
            
            return jsonify({
                'success': True,
                'audio_path': output_path,
                'audio_info': audio_info,
                'text': text,
                'language': language,
                'speaker_file': speaker_file.filename
            })
            
        finally:
            # Clean up speaker file
            if speaker_path.exists():
                speaker_path.unlink()
                
    except Exception as e:
        return jsonify({'success': False, 'error': str(e)}), 500

@app.route('/audio/<path:filename>', methods=['GET'])
def serve_audio(filename):
    """Serve generated audio files"""
    try:
        file_path = OUTPUT_DIR / filename
        if not file_path.exists():
            return jsonify({'error': 'Audio file not found'}), 404
            
        return send_file(
            str(file_path),
            mimetype='audio/wav',
            as_attachment=False,
            download_name=filename
        )
        
    except Exception as e:
        return jsonify({'error': str(e)}), 500

@app.route('/process_samples', methods=['POST'])
def process_voice_samples():
    """Process multiple voice samples for training"""
    try:
        if 'samples' not in request.files:
            return jsonify({'success': False, 'error': 'No samples provided'}), 400
            
        files = request.files.getlist('samples')
        transcripts = request.form.getlist('transcripts')
        
        if len(files) != len(transcripts):
            return jsonify({'success': False, 'error': 'Number of files and transcripts must match'}), 400
            
        processed_samples = []
        
        for file, transcript in zip(files, transcripts):
            if file.filename == '':
                continue
                
            # Save sample
            sample_id = uuid.uuid4().hex[:8]
            sample_path = SAMPLES_DIR / f"sample_{sample_id}_{file.filename}"
            file.save(str(sample_path))
            
            # Get audio info
            audio_info = tts_service.get_audio_info(str(sample_path))
            
            processed_samples.append({
                'id': sample_id,
                'filename': file.filename,
                'path': str(sample_path),
                'transcript': transcript,
                'audio_info': audio_info
            })
            
        return jsonify({
            'success': True,
            'processed_samples': processed_samples,
            'count': len(processed_samples)
        })
        
    except Exception as e:
        return jsonify({'success': False, 'error': str(e)}), 500

if __name__ == '__main__':
    print("🎤 Coqui TTS Service starting on port 8000...")
    app.run(host='0.0.0.0', port=8000, debug=False)