import OpenAI from 'openai';
import jwt from 'jsonwebtoken';

const openai = new OpenAI({
  apiKey: process.env.OPENAI_API_KEY
});

const JWT_SECRET = process.env.JWT_SECRET || 'fallback-secret';

// In-memory conversation store (replace with database in production)
const conversations = new Map();

export default async function handler(req, res) {
  // Enable CORS
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
  
  if (req.method === 'OPTIONS') {
    return res.status(200).end();
  }

  // Auth middleware
  const authHeader = req.headers.authorization;
  if (!authHeader?.startsWith('Bearer ')) {
    return res.status(401).json({ error: 'No token provided' });
  }

  const token = authHeader.substring(7);
  let userId;
  
  try {
    const decoded = jwt.verify(token, JWT_SECRET);
    userId = decoded.userId;
  } catch (error) {
    return res.status(401).json({ error: 'Invalid token' });
  }

  const { method } = req;

  try {
    switch (method) {
      case 'POST':
        return await createMessage(req, res, userId);
      case 'GET':
        return await getConversations(req, res, userId);
      default:
        return res.status(405).json({ error: 'Method not allowed' });
    }
  } catch (error) {
    console.error('Chat error:', error);
    return res.status(500).json({ error: 'Internal server error' });
  }
}

async function createMessage(req, res, userId) {
  const { message, conversationId, persona = 'assistant' } = req.body;

  if (!message) {
    return res.status(400).json({ error: 'Message is required' });
  }

  const convId = conversationId || `conv_${Date.now()}_${userId}`;
  
  if (!conversations.has(convId)) {
    conversations.set(convId, {
      id: convId,
      userId,
      messages: [],
      createdAt: new Date(),
      persona
    });
  }

  const conversation = conversations.get(convId);
  
  // Add user message
  const userMessage = {
    id: `msg_${Date.now()}_user`,
    role: 'user',
    content: message,
    timestamp: new Date()
  };
  
  conversation.messages.push(userMessage);

  // Get AI response
  const systemPrompt = getPersonaPrompt(persona);
  const messages = [
    { role: 'system', content: systemPrompt },
    ...conversation.messages.slice(-10).map(m => ({ role: m.role, content: m.content }))
  ];

  const completion = await openai.chat.completions.create({
    model: 'gpt-4',
    messages,
    temperature: 0.7,
    max_tokens: 500
  });

  const aiMessage = {
    id: `msg_${Date.now()}_assistant`,
    role: 'assistant',
    content: completion.choices[0].message.content,
    timestamp: new Date(),
    persona
  };

  conversation.messages.push(aiMessage);

  res.json({
    conversationId: convId,
    message: aiMessage,
    totalMessages: conversation.messages.length
  });
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

  res.json({ conversations: userConversations });
}

function getPersonaPrompt(persona) {
  const prompts = {
    assistant: "You are Locus, a helpful AI travel assistant. You help users plan trips, find destinations, and provide travel advice.",
    guide: "You are a knowledgeable travel guide with expertise in local cultures, hidden gems, and authentic experiences.",
    planner: "You are a meticulous travel planner focused on logistics, budgets, and detailed itineraries.",
    explorer: "You are an adventurous explorer who loves off-the-beaten-path destinations and unique experiences."
  };
  
  return prompts[persona] || prompts.assistant;
}