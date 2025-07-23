import OpenAI from 'openai';
import jwt from 'jsonwebtoken';

const openai = new OpenAI({
  apiKey: process.env.OPENAI_API_KEY
});

const JWT_SECRET = process.env.JWT_SECRET || 'fallback-secret';

// In-memory voice models store (replace with database in production)
const voiceModels = new Map();

// Initialize default personas
voiceModels.set('assistant', { name: 'Assistant', voice: 'alloy', description: 'Default helpful assistant' });
voiceModels.set('guide', { name: 'Travel Guide', voice: 'nova', description: 'Knowledgeable travel guide' });
voiceModels.set('planner', { name: 'Trip Planner', voice: 'echo', description: 'Detailed trip planner' });
voiceModels.set('explorer', { name: 'Explorer', voice: 'fable', description: 'Adventurous explorer' });

export default async function handler(req, res) {
  // Enable CORS
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
  
  if (req.method === 'OPTIONS') {
    return res.status(200).end();
  }

  // Auth middleware for POST requests
  if (req.method === 'POST') {
    const authHeader = req.headers.authorization;
    if (!authHeader?.startsWith('Bearer ')) {
      return res.status(401).json({ error: 'No token provided' });
    }

    const token = authHeader.substring(7);
    try {
      jwt.verify(token, JWT_SECRET);
    } catch (error) {
      return res.status(401).json({ error: 'Invalid token' });
    }
  }

  const { method } = req;

  try {
    switch (method) {
      case 'GET':
        return await getVoiceModels(req, res);
      case 'POST':
        if (req.url?.includes('/generate')) {
          return await generateSpeech(req, res);
        } else if (req.url?.includes('/upload')) {
          return await uploadVoiceModel(req, res);
        }
        return res.status(404).json({ error: 'Endpoint not found' });
      default:
        return res.status(405).json({ error: 'Method not allowed' });
    }
  } catch (error) {
    console.error('TTS error:', error);
    return res.status(500).json({ error: 'Internal server error' });
  }
}

async function generateSpeech(req, res) {
  const { text, persona = 'assistant', language = 'en' } = req.body;

  if (!text) {
    return res.status(400).json({ error: 'Text is required' });
  }

  const voiceModel = voiceModels.get(persona.toLowerCase());
  if (!voiceModel) {
    return res.status(404).json({ error: 'Voice model not found' });
  }

  try {
    // Use OpenAI TTS instead of Coqui for Vercel compatibility
    const mp3 = await openai.audio.speech.create({
      model: "tts-1",
      voice: voiceModel.voice || 'alloy',
      input: text,
      speed: 1.0
    });

    const buffer = Buffer.from(await mp3.arrayBuffer());
    
    res.setHeader('Content-Type', 'audio/mpeg');
    res.setHeader('Content-Length', buffer.length);
    res.setHeader('Content-Disposition', 'attachment; filename="speech.mp3"');
    
    return res.send(buffer);
  } catch (error) {
    console.error('TTS generation error:', error);
    return res.status(500).json({ error: 'Failed to generate speech' });
  }
}

async function getVoiceModels(req, res) {
  const models = Array.from(voiceModels.entries()).map(([id, model]) => ({
    id,
    ...model
  }));

  res.json({ models });
}

async function uploadVoiceModel(req, res) {
  // For Vercel, we'll simulate voice model upload
  // In a real implementation, you'd process the uploaded audio file
  const { name, description, persona } = req.body;

  if (!name || !persona) {
    return res.status(400).json({ error: 'Name and persona are required' });
  }

  // Map to available OpenAI voices
  const availableVoices = ['alloy', 'echo', 'fable', 'onyx', 'nova', 'shimmer'];
  const voice = availableVoices[Math.floor(Math.random() * availableVoices.length)];

  const voiceModel = {
    name,
    description: description || `Custom voice for ${persona}`,
    voice,
    custom: true,
    createdAt: new Date()
  };

  voiceModels.set(persona.toLowerCase(), voiceModel);

  res.json({
    message: 'Voice model created successfully',
    model: { id: persona.toLowerCase(), ...voiceModel }
  });
}