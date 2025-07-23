import { spawn, ChildProcess } from 'child_process';
import fs from 'fs-extra';
import path from 'path';
import { v4 as uuidv4 } from 'uuid';
import ffmpeg from 'fluent-ffmpeg';
import { logger } from '../utils/logger.js';
import { prisma } from '../utils/database.js';

export interface TTSOptions {
  text: string;
  persona: string;
  language: string;
  outputPath?: string;
}

export interface VoiceCloneOptions {
  speakerWav: string;
  text: string;
  outputPath?: string;
  modelName?: string;
}

export class CoquiTTSService {
  private ttsPath: string;
  private modelsDir: string;
  private outputDir: string;
  private isInitialized: boolean = false;

  constructor() {
    this.ttsPath = process.env.COQUI_TTS_PATH || 'tts';
    this.modelsDir = path.join(process.cwd(), 'data', 'voice_models');
    this.outputDir = path.join(process.cwd(), 'data', 'tts_output');
    this.init();
  }

  private async init() {
    try {
      // Ensure directories exist
      await fs.ensureDir(this.modelsDir);
      await fs.ensureDir(this.outputDir);
      
      // Check if Coqui TTS is installed
      await this.checkCoquiInstallation();
      
      this.isInitialized = true;
      logger.info('🎤 Coqui TTS Service initialized successfully');
    } catch (error) {
      logger.error('❌ Failed to initialize Coqui TTS:', error);
      throw error;
    }
  }

  private async checkCoquiInstallation(): Promise<void> {
    return new Promise((resolve, reject) => {
      const process = spawn(this.ttsPath, ['--help']);
      
      process.on('error', () => {
        reject(new Error('Coqui TTS not found. Please install with: pip install TTS'));
      });
      
      process.on('close', (code) => {
        if (code === 0) {
          resolve();
        } else {
          reject(new Error('Coqui TTS installation check failed'));
        }
      });
    });
  }

  async generateSpeech(options: TTSOptions): Promise<string> {
    if (!this.isInitialized) {
      throw new Error('TTS Service not initialized');
    }

    const requestId = uuidv4();
    const outputPath = options.outputPath || path.join(this.outputDir, `${requestId}.wav`);

    try {
      // Create TTS request record
      const ttsRequest = await prisma.tTSRequest.create({
        data: {
          text: options.text,
          persona: options.persona.toUpperCase() as any,
          language: options.language.toUpperCase() as any,
          status: 'PROCESSING',
        },
      });

      // Find appropriate voice model
      const voiceModel = await prisma.voiceModel.findFirst({
        where: {
          persona: options.persona.toUpperCase() as any,
          language: options.language.toUpperCase() as any,
          isActive: true,
        },
      });

      let audioPath: string;

      if (voiceModel && voiceModel.modelPath) {
        // Use custom trained model
        audioPath = await this.generateWithCustomModel(options.text, voiceModel, outputPath);
      } else {
        // Use default Coqui model
        audioPath = await this.generateWithDefaultModel(options.text, options.language, outputPath);
      }

      // Update request with success
      await prisma.tTSRequest.update({
        where: { id: ttsRequest.id },
        data: {
          status: 'COMPLETED',
          audioPath,
          audioUrl: `/api/tts/audio/${path.basename(audioPath)}`,
          duration: await this.getAudioDuration(audioPath),
        },
      });

      logger.info(`✅ TTS generated successfully: ${audioPath}`);
      return audioPath;

    } catch (error) {
      logger.error('❌ TTS generation failed:', error);
      throw error;
    }
  }

  private async generateWithCustomModel(text: string, voiceModel: any, outputPath: string): Promise<string> {
    return new Promise((resolve, reject) => {
      const args = [
        '--model_path', voiceModel.modelPath,
        '--config_path', voiceModel.configPath,
        '--text', text,
        '--out_path', outputPath,
      ];

      if (voiceModel.speakerWav) {
        args.push('--speaker_wav', voiceModel.speakerWav);
      }

      const process = spawn(this.ttsPath, args);
      
      let stderr = '';
      process.stderr.on('data', (data) => {
        stderr += data.toString();
      });

      process.on('close', (code) => {
        if (code === 0) {
          resolve(outputPath);
        } else {
          reject(new Error(`TTS process failed: ${stderr}`));
        }
      });

      process.on('error', (error) => {
        reject(new Error(`TTS process error: ${error.message}`));
      });
    });
  }

  private async generateWithDefaultModel(text: string, language: string, outputPath: string): Promise<string> {
    return new Promise((resolve, reject) => {
      // Use Coqui's built-in multilingual model
      const modelName = this.getDefaultModelForLanguage(language);
      
      const args = [
        '--model_name', modelName,
        '--text', text,
        '--out_path', outputPath,
      ];

      const process = spawn(this.ttsPath, args);
      
      let stderr = '';
      process.stderr.on('data', (data) => {
        stderr += data.toString();
      });

      process.on('close', (code) => {
        if (code === 0) {
          resolve(outputPath);
        } else {
          reject(new Error(`TTS process failed: ${stderr}`));
        }
      });

      process.on('error', (error) => {
        reject(new Error(`TTS process error: ${error.message}`));
      });
    });
  }

  private getDefaultModelForLanguage(language: string): string {
    const models: Record<string, string> = {
      'nl': 'tts_models/nl/css10/vits',
      'en': 'tts_models/en/ljspeech/tacotron2-DDC',
      'de': 'tts_models/de/thorsten/tacotron2-DDC',
      'fr': 'tts_models/fr/css10/vits',
    };

    return models[language.toLowerCase()] || models['en'];
  }

  async cloneVoice(options: VoiceCloneOptions): Promise<string> {
    if (!this.isInitialized) {
      throw new Error('TTS Service not initialized');
    }

    const requestId = uuidv4();
    const outputPath = options.outputPath || path.join(this.outputDir, `cloned_${requestId}.wav`);

    return new Promise((resolve, reject) => {
      const args = [
        '--model_name', 'tts_models/multilingual/multi-dataset/your_tts',
        '--text', options.text,
        '--speaker_wav', options.speakerWav,
        '--language_idx', 'en', // Can be made dynamic
        '--out_path', outputPath,
      ];

      const process = spawn(this.ttsPath, args);
      
      let stderr = '';
      process.stderr.on('data', (data) => {
        stderr += data.toString();
      });

      process.on('close', (code) => {
        if (code === 0) {
          logger.info(`✅ Voice cloning completed: ${outputPath}`);
          resolve(outputPath);
        } else {
          logger.error(`❌ Voice cloning failed: ${stderr}`);
          reject(new Error(`Voice cloning failed: ${stderr}`));
        }
      });

      process.on('error', (error) => {
        reject(new Error(`Voice cloning process error: ${error.message}`));
      });
    });
  }

  async trainCustomVoice(voiceModelId: string, samplePaths: string[]): Promise<void> {
    if (!this.isInitialized) {
      throw new Error('TTS Service not initialized');
    }

    try {
      // Update model status to training
      await prisma.voiceModel.update({
        where: { id: voiceModelId },
        data: { 
          isTraining: true, 
          trainingStatus: 'training' 
        },
      });

      // Prepare training data
      const trainingDir = path.join(this.modelsDir, voiceModelId);
      await fs.ensureDir(trainingDir);

      // Copy and process audio samples
      const processedSamples = await this.preprocessAudioSamples(samplePaths, trainingDir);

      // Start training process (this is a simplified version)
      const modelPath = await this.startTraining(voiceModelId, processedSamples, trainingDir);

      // Update model with trained paths
      await prisma.voiceModel.update({
        where: { id: voiceModelId },
        data: {
          modelPath,
          configPath: path.join(trainingDir, 'config.json'),
          isTraining: false,
          trainingStatus: 'completed',
          isActive: true,
        },
      });

      logger.info(`✅ Voice training completed for model: ${voiceModelId}`);

    } catch (error) {
      // Update model with failure status
      await prisma.voiceModel.update({
        where: { id: voiceModelId },
        data: {
          isTraining: false,
          trainingStatus: 'failed',
        },
      });

      logger.error(`❌ Voice training failed for model ${voiceModelId}:`, error);
      throw error;
    }
  }

  private async preprocessAudioSamples(samplePaths: string[], outputDir: string): Promise<string[]> {
    const processedPaths: string[] = [];

    for (const samplePath of samplePaths) {
      const outputPath = path.join(outputDir, `processed_${path.basename(samplePath)}`);
      
      await new Promise<void>((resolve, reject) => {
        ffmpeg(samplePath)
          .audioFrequency(22050)
          .audioChannels(1)
          .audioCodec('pcm_s16le')
          .format('wav')
          .output(outputPath)
          .on('end', () => resolve())
          .on('error', reject)
          .run();
      });

      processedPaths.push(outputPath);
    }

    return processedPaths;
  }

  private async startTraining(modelId: string, samplePaths: string[], outputDir: string): Promise<string> {
    // This is a simplified training process
    // In a real implementation, you'd use Coqui's training scripts
    const modelPath = path.join(outputDir, 'model.pth');
    
    return new Promise((resolve, reject) => {
      // Placeholder for actual training command
      // You would use TTS training scripts here
      const args = [
        'train',
        '--config_path', path.join(outputDir, 'config.json'),
        '--output_path', outputDir,
        // Add more training parameters
      ];

      // For now, we'll simulate training completion
      setTimeout(() => {
        // Create placeholder model file
        fs.writeFileSync(modelPath, 'placeholder model data');
        resolve(modelPath);
      }, 5000);
    });
  }

  private async getAudioDuration(filePath: string): Promise<number> {
    return new Promise((resolve, reject) => {
      ffmpeg.ffprobe(filePath, (err, metadata) => {
        if (err) {
          reject(err);
        } else {
          resolve(metadata.format.duration || 0);
        }
      });
    });
  }

  async listAvailableModels(): Promise<any[]> {
    return new Promise((resolve, reject) => {
      const process = spawn(this.ttsPath, ['--list_models']);
      
      let stdout = '';
      process.stdout.on('data', (data) => {
        stdout += data.toString();
      });

      process.on('close', (code) => {
        if (code === 0) {
          try {
            const models = this.parseModelList(stdout);
            resolve(models);
          } catch (error) {
            reject(error);
          }
        } else {
          reject(new Error('Failed to list models'));
        }
      });
    });
  }

  private parseModelList(output: string): any[] {
    // Parse Coqui TTS model list output
    const lines = output.split('\n');
    const models: any[] = [];
    
    for (const line of lines) {
      if (line.includes('tts_models/')) {
        const modelName = line.trim();
        models.push({
          name: modelName,
          type: 'tts',
          language: this.extractLanguageFromModel(modelName),
        });
      }
    }
    
    return models;
  }

  private extractLanguageFromModel(modelName: string): string {
    const parts = modelName.split('/');
    return parts[1] || 'unknown';
  }
}

export const coquiTTS = new CoquiTTSService();