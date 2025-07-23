import OpenAI from 'openai';
import jwt from 'jsonwebtoken';

// Validate required environment variables
const OPENAI_API_KEY = process.env.OPENAI_API_KEY;
const JWT_SECRET = process.env.JWT_SECRET;

if (!OPENAI_API_KEY) {
  throw new Error('Missing required environment variable: OPENAI_API_KEY');
}

if (!JWT_SECRET) {
  throw new Error('Missing required environment variable: JWT_SECRET');
}

const openai = new OpenAI({
  apiKey: OPENAI_API_KEY
});

// In-memory stores (replace with database in production)
const voiceModels = new Map();
const ttsHistory = new Map();

// Initialize default personas with OpenAI voices
voiceModels.set('assistant', { 
  id: 'assistant',
  name: 'Travel Assistant', 
  voice: 'alloy', 
  description: 'Helpful travel assistant',
  persona: 'assistant',
  language: 'en',
  isActive: true,
  isTraining: false,
  samples: [],
  createdAt: new Date().toISOString()
});

voiceModels.set('guide', { 
  id: 'guide',
  name: 'Travel Guide', 
  voice: 'nova', 
  description: 'Experienced travel guide',
  persona: 'guide',
  language: 'en',
  isActive: true,
  isTraining: false,
  samples: [],
  createdAt: new Date().toISOString()
});

voiceModels.set('planner', { 
  id: 'planner',
  name: 'Trip Planner', 
  voice: 'echo', 
  description: 'Detailed trip planner',
  persona: 'planner',
  language: 'en',
  isActive: true,
  isTraining: false,
  samples: [],
  createdAt: new Date().toISOString()
});

voiceModels.set('explorer', { 
  id: 'explorer',
  name: 'Adventure Explorer', 
  voice: 'fable', 
  description: 'Adventurous explorer',
  persona: 'explorer',
  language: 'en',
  isActive: true,
  isTraining: false,
  samples: [],
  createdAt: new Date().toISOString()
});

export default async function handler(req, res) {
  // Proper CORS for frontend domain
  const origin = req.headers.origin;
  const allowedOrigins = [
    'http://localhost:3000',
    'https://locus.vercel.app',
    process.env.FRONTEND_URL
  ].filter(Boolean);

  if (allowedOrigins.includes(origin)) {
    res.setHeader('Access-Control-Allow-Origin', origin);
  }
  
  res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
  res.setHeader('Access-Control-Allow-Credentials', 'true');
  
  if (req.method === 'OPTIONS') {
    return res.status(200).end();
  }

  // Auth middleware for most endpoints
  let userId = null;
  if (req.method === 'POST' || (req.method === 'GET' && !req.url?.includes('/models'))) {
    const authHeader = req.headers.authorization;
    if (!authHeader?.startsWith('Bearer ')) {
      return res.status(401).json({ success: false, error: 'No token provided' });
    }

    const token = authHeader.substring(7);
    try {
      const decoded = jwt.verify(token, JWT_SECRET);
      userId = decoded.userId;
    } catch (error) {
      return res.status(401).json({ success: false, error: 'Invalid token' });
    }
  }

  try {
    const { method } = req;
    const urlPath = req.url || '';

    switch (method) {
      case 'GET':
        if (urlPath.includes('/models') || urlPath === '/tts') {
          return await getVoiceModels(req, res);
        } else if (urlPath.includes('/history')) {
          return await getTTSHistory(req, res, userId);
        }
        return res.status(404).json({ success: false, error: 'Endpoint not found' });
        
      case 'POST':
        if (urlPath.includes('/generate')) {
          return await generateSpeech(req, res, userId);
        } else if (urlPath.includes('/models') && !urlPath.includes('/samples') && !urlPath.includes('/train')) {
          return await createVoiceModel(req, res, userId);
        } else if (urlPath.includes('/samples')) {
          return await uploadVoiceSamples(req, res, userId);
        } else if (urlPath.includes('/train')) {
          return await startTraining(req, res, userId);
        } else if (urlPath.includes('/upload')) {
          return await createVoiceModel(req, res, userId);
        }
        return res.status(404).json({ success: false, error: 'Endpoint not found' });
        
      default:
        return res.status(405).json({ success: false, error: 'Method not allowed' });
    }
  } catch (error) {
    console.error('TTS error:', error);
    return res.status(500).json({ success: false, error: 'Internal server error' });
  }
}

async function generateSpeech(req, res, userId) {
  const { text, persona = 'assistant', language = 'en' } = req.body;

  if (!text) {
    return res.status(400).json({ success: false, error: 'Text is required' });
  }

  const voiceModel = voiceModels.get(persona.toLowerCase());
  if (!voiceModel) {
    return res.status(404).json({ success: false, error: 'Voice model not found' });
  }

  try {
    // Use OpenAI TTS
    const mp3 = await openai.audio.speech.create({
      model: "tts-1",
      voice: voiceModel.voice || 'alloy',
      input: text,
      speed: 1.0
    });

    const buffer = Buffer.from(await mp3.arrayBuffer());
    
    // Store in history
    const historyId = `tts_${Date.now()}_${userId}`;
    ttsHistory.set(historyId, {
      id: historyId,
      text,
      persona,
      language,
      status: 'COMPLETED',
      duration: Math.floor(text.length / 10), // Rough estimate
      createdAt: new Date().toISOString(),
      userId
    });
    
    res.setHeader('Content-Type', 'audio/mpeg');
    res.setHeader('Content-Length', buffer.length);
    res.setHeader('Content-Disposition', 'attachment; filename="speech.mp3"');
    
    return res.send(buffer);
  } catch (error) {
    console.error('TTS generation error:', error);
    return res.status(500).json({ success: false, error: 'Failed to generate speech' });
  }
}

async function getVoiceModels(req, res) {
  const models = Array.from(voiceModels.values());
  res.json({ success: true, data: { models } });
}

async function createVoiceModel(req, res, userId) {
  const { name, description, persona, language = 'en' } = req.body;

  if (!name || !persona) {
    return res.status(400).json({ success: false, error: 'Name and persona are required' });
  }

  // Map to available OpenAI voices
  const availableVoices = ['alloy', 'echo', 'fable', 'onyx', 'nova', 'shimmer'];
  const voice = availableVoices[Math.floor(Math.random() * availableVoices.length)];

  const modelId = `${persona.toLowerCase()}_${Date.now()}`;
  const voiceModel = {
    id: modelId,
    name,
    description: description || `Custom voice for ${persona}`,
    voice,
    persona: persona.toLowerCase(),
    language: language.toLowerCase(),
    isActive: false,
    isTraining: false,
    samples: [],
    createdAt: new Date().toISOString(),
    userId
  };

  voiceModels.set(modelId, voiceModel);

  res.json({
    success: true,
    data: voiceModel,
    message: 'Voice model created successfully'
  });
}

async function uploadVoiceSamples(req, res, userId) {
  const urlPath = req.url || '';
  const modelId = urlPath.split('/models/')[1]?.split('/samples')[0];
  
  if (!modelId) {
    return res.status(400).json({ success: false, error: 'Model ID is required' });
  }

  const voiceModel = voiceModels.get(modelId);
  if (!voiceModel) {
    return res.status(404).json({ success: false, error: 'Voice model not found' });
  }

  // For demo purposes, simulate sample upload
  const { transcript } = req.body;
  if (!transcript) {
    return res.status(400).json({ success: false, error: 'Transcript is required' });
  }

  // Add fake samples
  const sampleId = `sample_${Date.now()}`;
  const sample = {
    id: sampleId,
    filename: `sample_${Date.now()}.wav`,
    duration: 5.2,
    isApproved: true,
    transcript,
    uploadedAt: new Date().toISOString()
  };

  voiceModel.samples.push(sample);
  voiceModels.set(modelId, voiceModel);

  res.json({
    success: true,
    data: sample,
    message: 'Voice samples uploaded successfully'
  });
}

async function startTraining(req, res, userId) {
  const urlPath = req.url || '';
  const modelId = urlPath.split('/models/')[1]?.split('/train')[0];
  
  if (!modelId) {
    return res.status(400).json({ success: false, error: 'Model ID is required' });
  }

  const voiceModel = voiceModels.get(modelId);
  if (!voiceModel) {
    return res.status(404).json({ success: false, error: 'Voice model not found' });
  }

  if (voiceModel.samples.length < 5) {
    return res.status(400).json({ 
      success: false, 
      error: 'At least 5 voice samples are required for training' 
    });
  }

  // Simulate training
  voiceModel.isTraining = true;
  voiceModel.trainingStatus = 'Initializing training...';
  voiceModels.set(modelId, voiceModel);

  // Simulate training completion after a short delay
  setTimeout(() => {
    voiceModel.isTraining = false;
    voiceModel.isActive = true;
    voiceModel.quality = 0.85;
    voiceModel.trainingStatus = 'Training completed';
    voiceModels.set(modelId, voiceModel);
  }, 5000);

  res.json({
    success: true,
    message: 'Training started successfully',
    data: { modelId, status: 'training' }
  });
}

async function getTTSHistory(req, res, userId) {
  const userHistory = Array.from(ttsHistory.values())
    .filter(item => item.userId === userId)
    .sort((a, b) => new Date(b.createdAt) - new Date(a.createdAt))
    .slice(0, 50); // Limit to 50 recent items

  res.json({
    success: true,
    data: { history: userHistory, total: userHistory.length }
  });
}