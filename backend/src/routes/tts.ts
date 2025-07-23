import { Router } from 'express';
import multer from 'multer';
import path from 'path';
import fs from 'fs-extra';
import { z } from 'zod';
import { prisma } from '../utils/database.js';
import { logger } from '../utils/logger.js';
import { coquiTTS } from '../services/coquiTTS.js';
import { authMiddleware } from '../middleware/auth.js';

const router = Router();

// Configure multer for voice sample uploads
const storage = multer.diskStorage({
  destination: async (req, file, cb) => {
    const uploadDir = path.join(process.cwd(), 'data', 'voice_samples');
    await fs.ensureDir(uploadDir);
    cb(null, uploadDir);
  },
  filename: (req, file, cb) => {
    const uniqueSuffix = Date.now() + '-' + Math.round(Math.random() * 1E9);
    cb(null, `sample-${uniqueSuffix}${path.extname(file.originalname)}`);
  }
});

const upload = multer({ 
  storage,
  limits: { fileSize: 50 * 1024 * 1024 }, // 50MB limit
  fileFilter: (req, file, cb) => {
    const allowedTypes = ['.wav', '.mp3', '.m4a', '.flac'];
    const ext = path.extname(file.originalname).toLowerCase();
    if (allowedTypes.includes(ext)) {
      cb(null, true);
    } else {
      cb(new Error('Invalid file type. Only audio files allowed.'));
    }
  }
});

// Validation schemas
const generateTTSSchema = z.object({
  text: z.string().min(1).max(1000),
  persona: z.enum(['AMSTERDAMMER', 'BELGIQUE', 'BRABANDER', 'JORDANEES']),
  language: z.enum(['NL', 'EN', 'DE', 'FR']),
});

const createVoiceModelSchema = z.object({
  name: z.string().min(1).max(100),
  persona: z.enum(['AMSTERDAMMER', 'BELGIQUE', 'BRABANDER', 'JORDANEES']),
  language: z.enum(['NL', 'EN', 'DE', 'FR']),
  description: z.string().optional(),
});

const uploadSampleSchema = z.object({
  voiceModelId: z.string(),
  transcript: z.string().min(1).max(500),
});

// Generate TTS audio
router.post('/generate', authMiddleware, async (req, res) => {
  try {
    const { text, persona, language } = generateTTSSchema.parse(req.body);

    logger.info(`🎤 TTS generation request: ${persona} (${language}) - "${text.substring(0, 50)}..."`);

    const audioPath = await coquiTTS.generateSpeech({
      text,
      persona: persona.toLowerCase(),
      language: language.toLowerCase(),
    });

    const audioUrl = `/api/tts/audio/${path.basename(audioPath)}`;

    res.json({
      success: true,
      data: {
        audioUrl,
        text,
        persona,
        language,
        duration: await getAudioDuration(audioPath),
      },
    });

  } catch (error) {
    logger.error('TTS generation error:', error);
    res.status(500).json({
      success: false,
      message: error instanceof Error ? error.message : 'TTS generation failed',
    });
  }
});

// Voice cloning endpoint
router.post('/clone', authMiddleware, upload.single('speakerAudio'), async (req, res) => {
  try {
    if (!req.file) {
      return res.status(400).json({
        success: false,
        message: 'Speaker audio file is required',
      });
    }

    const { text } = req.body;
    if (!text || text.trim().length === 0) {
      return res.status(400).json({
        success: false,
        message: 'Text is required for voice cloning',
      });
    }

    logger.info(`🎭 Voice cloning request with speaker: ${req.file.filename}`);

    const audioPath = await coquiTTS.cloneVoice({
      speakerWav: req.file.path,
      text: text.trim(),
    });

    const audioUrl = `/api/tts/audio/${path.basename(audioPath)}`;

    res.json({
      success: true,
      data: {
        audioUrl,
        text,
        clonedFrom: req.file.filename,
      },
    });

  } catch (error) {
    logger.error('Voice cloning error:', error);
    res.status(500).json({
      success: false,
      message: error instanceof Error ? error.message : 'Voice cloning failed',
    });
  }
});

// Serve generated audio files
router.get('/audio/:filename', (req, res) => {
  const filename = req.params.filename;
  const audioPath = path.join(process.cwd(), 'data', 'tts_output', filename);
  
  if (!fs.existsSync(audioPath)) {
    return res.status(404).json({
      success: false,
      message: 'Audio file not found',
    });
  }

  res.setHeader('Content-Type', 'audio/wav');
  res.setHeader('Content-Disposition', `inline; filename="${filename}"`);
  
  const stream = fs.createReadStream(audioPath);
  stream.pipe(res);
});

// Get TTS history
router.get('/history', authMiddleware, async (req, res) => {
  try {
    const { page = 1, limit = 20 } = req.query;
    const userId = req.user?.userId;

    const requests = await prisma.tTSRequest.findMany({
      where: userId ? { userId } : {},
      orderBy: { createdAt: 'desc' },
      skip: (Number(page) - 1) * Number(limit),
      take: Number(limit),
    });

    const total = await prisma.tTSRequest.count({
      where: userId ? { userId } : {},
    });

    res.json({
      success: true,
      data: requests,
      pagination: {
        page: Number(page),
        limit: Number(limit),
        total,
        hasNext: Number(page) * Number(limit) < total,
      },
    });

  } catch (error) {
    logger.error('TTS history error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to fetch TTS history',
    });
  }
});

// Voice Models Management
router.get('/models', authMiddleware, async (req, res) => {
  try {
    const models = await prisma.voiceModel.findMany({
      include: {
        samples: {
          select: {
            id: true,
            filename: true,
            duration: true,
            isApproved: true,
          },
        },
      },
      orderBy: { createdAt: 'desc' },
    });

    res.json({
      success: true,
      data: models,
    });

  } catch (error) {
    logger.error('Voice models fetch error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to fetch voice models',
    });
  }
});

router.post('/models', authMiddleware, async (req, res) => {
  try {
    const { name, persona, language, description } = createVoiceModelSchema.parse(req.body);

    const existingModel = await prisma.voiceModel.findUnique({
      where: {
        persona_language: {
          persona,
          language,
        },
      },
    });

    if (existingModel) {
      return res.status(400).json({
        success: false,
        message: `Voice model for ${persona} in ${language} already exists`,
      });
    }

    const model = await prisma.voiceModel.create({
      data: {
        name,
        persona,
        language,
        description,
      },
    });

    logger.info(`🎙️ Voice model created: ${name} (${persona}/${language})`);

    res.status(201).json({
      success: true,
      data: model,
      message: 'Voice model created successfully',
    });

  } catch (error) {
    logger.error('Voice model creation error:', error);
    res.status(500).json({
      success: false,
      message: error instanceof Error ? error.message : 'Failed to create voice model',
    });
  }
});

// Upload voice samples for training
router.post('/models/:modelId/samples', authMiddleware, upload.array('samples', 10), async (req, res) => {
  try {
    const { modelId } = req.params;
    const { transcript } = uploadSampleSchema.parse(req.body);
    
    if (!req.files || !Array.isArray(req.files) || req.files.length === 0) {
      return res.status(400).json({
        success: false,
        message: 'At least one audio sample is required',
      });
    }

    const model = await prisma.voiceModel.findUnique({
      where: { id: modelId },
    });

    if (!model) {
      return res.status(404).json({
        success: false,
        message: 'Voice model not found',
      });
    }

    const samples = await Promise.all(
      req.files.map(async (file) => {
        const duration = await getAudioDuration(file.path);
        
        return prisma.voiceSample.create({
          data: {
            voiceModelId: modelId,
            filename: file.filename,
            filePath: file.path,
            transcript,
            duration,
            sampleRate: 22050, // Default, could be detected
          },
        });
      })
    );

    logger.info(`📄 ${samples.length} voice samples uploaded for model ${modelId}`);

    res.json({
      success: true,
      data: samples,
      message: `${samples.length} voice samples uploaded successfully`,
    });

  } catch (error) {
    logger.error('Voice sample upload error:', error);
    res.status(500).json({
      success: false,
      message: error instanceof Error ? error.message : 'Failed to upload voice samples',
    });
  }
});

// Start training a voice model
router.post('/models/:modelId/train', authMiddleware, async (req, res) => {
  try {
    const { modelId } = req.params;

    const model = await prisma.voiceModel.findUnique({
      where: { id: modelId },
      include: {
        samples: {
          where: { isApproved: true },
        },
      },
    });

    if (!model) {
      return res.status(404).json({
        success: false,
        message: 'Voice model not found',
      });
    }

    if (model.samples.length < 5) {
      return res.status(400).json({
        success: false,
        message: 'At least 5 approved voice samples are required for training',
      });
    }

    if (model.isTraining) {
      return res.status(400).json({
        success: false,
        message: 'Model is already being trained',
      });
    }

    // Start training in background
    const samplePaths = model.samples.map(sample => sample.filePath);
    
    // Don't await - let it run in background
    coquiTTS.trainCustomVoice(modelId, samplePaths).catch(error => {
      logger.error(`Training failed for model ${modelId}:`, error);
    });

    logger.info(`🏋️ Training started for voice model ${modelId}`);

    res.json({
      success: true,
      message: 'Voice model training started',
      data: {
        modelId,
        samplesCount: model.samples.length,
        status: 'training',
      },
    });

  } catch (error) {
    logger.error('Voice model training error:', error);
    res.status(500).json({
      success: false,
      message: error instanceof Error ? error.message : 'Failed to start training',
    });
  }
});

// Get available Coqui models
router.get('/available-models', authMiddleware, async (req, res) => {
  try {
    const models = await coquiTTS.listAvailableModels();
    
    res.json({
      success: true,
      data: models,
    });

  } catch (error) {
    logger.error('Available models fetch error:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to fetch available models',
    });
  }
});

// Helper function to get audio duration
async function getAudioDuration(filePath: string): Promise<number> {
  const ffmpeg = require('fluent-ffmpeg');
  
  return new Promise((resolve, reject) => {
    ffmpeg.ffprobe(filePath, (err: any, metadata: any) => {
      if (err) {
        reject(err);
      } else {
        resolve(metadata.format.duration || 0);
      }
    });
  });
}

export { router as ttsRouter };