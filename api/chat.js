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

// In-memory conversation store (replace with database in production)
const conversations = new Map();

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

  // Auth middleware
  const authHeader = req.headers.authorization;
  if (!authHeader?.startsWith('Bearer ')) {
    return res.status(401).json({ success: false, error: 'No token provided' });
  }

  const token = authHeader.substring(7);
  let userId;
  
  try {
    const decoded = jwt.verify(token, JWT_SECRET);
    userId = decoded.userId;
  } catch (error) {
    return res.status(401).json({ success: false, error: 'Invalid token' });
  }

  try {
    const { method } = req;

    switch (method) {
      case 'POST':
        return await createMessage(req, res, userId);
      case 'GET':
        return await getConversations(req, res, userId);
      default:
        return res.status(405).json({ success: false, error: 'Method not allowed' });
    }
  } catch (error) {
    console.error('Chat error:', error);
    return res.status(500).json({ success: false, error: 'Internal server error' });
  }
}

async function createMessage(req, res, userId) {
  const { message, conversationId, persona = 'assistant' } = req.body;

  if (!message) {
    return res.status(400).json({ success: false, error: 'Message is required' });
  }

  const convId = conversationId || `conv_${Date.now()}_${userId}`;
  
  if (!conversations.has(convId)) {
    conversations.set(convId, {
      id: convId,
      userId,
      messages: [],
      createdAt: new Date().toISOString(),
      persona
    });
  }

  const conversation = conversations.get(convId);
  
  // Add user message
  const userMessage = {
    id: `msg_${Date.now()}_user`,
    role: 'user',
    content: message,
    timestamp: new Date().toISOString()
  };
  
  conversation.messages.push(userMessage);

  // Get AI response
  const systemPrompt = getPersonaPrompt(persona);
  const messages = [
    { role: 'system', content: systemPrompt },
    ...conversation.messages.slice(-10).map(m => ({ role: m.role, content: m.content }))
  ];

  try {
    const completion = await openai.chat.completions.create({
      model: 'gpt-4',
      messages,
      temperature: 0.7,
      max_tokens: 800
    });

    const aiMessage = {
      id: `msg_${Date.now()}_assistant`,
      role: 'assistant',
      content: completion.choices[0].message.content,
      timestamp: new Date().toISOString(),
      persona
    };

    conversation.messages.push(aiMessage);

    res.json({
      success: true,
      data: {
        conversationId: convId,
        message: aiMessage,
        totalMessages: conversation.messages.length
      }
    });
  } catch (openaiError) {
    console.error('OpenAI API error:', openaiError);
    return res.status(500).json({ 
      success: false, 
      error: 'Failed to generate AI response. Please try again.' 
    });
  }
}

async function getConversations(req, res, userId) {
  const userConversations = Array.from(conversations.values())
    .filter(conv => conv.userId === userId)
    .map(conv => ({
      id: conv.id,
      persona: conv.persona,
      lastMessage: conv.messages[conv.messages.length - 1],
      messageCount: conv.messages.length,
      createdAt: conv.createdAt
    }))
    .sort((a, b) => new Date(b.createdAt) - new Date(a.createdAt));

  res.json({ 
    success: true, 
    data: { conversations: userConversations } 
  });
}

function getPersonaPrompt(persona) {
  const prompts = {
    assistant: "You are Locus, a helpful AI travel assistant. You help users plan trips, find destinations, and provide travel advice. Be friendly, knowledgeable, and concise in your responses.",
    guide: "You are an experienced travel guide with deep knowledge of local cultures, hidden gems, and authentic experiences. Share insider tips and cultural insights.",
    planner: "You are a meticulous travel planner focused on logistics, budgets, and detailed itineraries. Help users organize their trips efficiently and cost-effectively.",
    explorer: "You are an adventurous explorer who loves off-the-beaten-path destinations and unique experiences. Inspire users to try new adventures and discover amazing places."
  };
  
  return prompts[persona] || prompts.assistant;
}