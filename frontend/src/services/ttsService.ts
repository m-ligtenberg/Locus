import axios from 'axios';

const API_BASE = process.env.VITE_API_URL || '/api';

interface TTSGenerateRequest {
  text: string;
  persona: 'assistant' | 'guide' | 'planner' | 'explorer';
  language: 'en' | 'es' | 'fr' | 'de';
}

interface VoiceCloneRequest {
  speakerAudio: File;
  text: string;
}

interface VoiceModel {
  id: string;
  name: string;
  persona: string;
  language: string;
  description?: string;
  isActive: boolean;
  isTraining: boolean;
  trainingStatus?: string;
  quality?: number;
  samples: VoiceSample[];
  createdAt: string;
}

interface VoiceSample {
  id: string;
  filename: string;
  duration: number;
  isApproved: boolean;
}

interface CreateVoiceModelRequest {
  name: string;
  persona: 'assistant' | 'guide' | 'planner' | 'explorer';
  language: 'en' | 'es' | 'fr' | 'de';
  description?: string;
}

class TTSService {
  private getAuthHeaders() {
    const token = localStorage.getItem('accessToken');
    return {
      'Authorization': `Bearer ${token}`,
    };
  }

  async generateTTS(request: TTSGenerateRequest) {
    const response = await axios.post(`${API_BASE}/tts/generate`, request, {
      headers: this.getAuthHeaders(),
      responseType: 'blob'
    });
    return response.data;
  }

  async cloneVoice(request: VoiceCloneRequest) {
    const formData = new FormData();
    formData.append('speakerAudio', request.speakerAudio);
    formData.append('text', request.text);

    const response = await axios.post(`${API_BASE}/tts/clone`, formData, {
      headers: {
        ...this.getAuthHeaders(),
        'Content-Type': 'multipart/form-data',
      },
    });
    return response.data;
  }

  async getVoiceModels(): Promise<VoiceModel[]> {
    const response = await axios.get(`${API_BASE}/tts`, {
      headers: this.getAuthHeaders(),
    });
    return response.data.models;
  }

  async createVoiceModel(request: CreateVoiceModelRequest) {
    const response = await axios.post(`${API_BASE}/tts/upload`, request, {
      headers: this.getAuthHeaders(),
    });
    return response.data;
  }

  async uploadVoiceSamples(modelId: string, samples: File[], transcript: string) {
    const formData = new FormData();
    samples.forEach(sample => formData.append('samples', sample));
    formData.append('transcript', transcript);

    const response = await axios.post(`${API_BASE}/tts/models/${modelId}/samples`, formData, {
      headers: {
        ...this.getAuthHeaders(),
        'Content-Type': 'multipart/form-data',
      },
    });
    return response.data;
  }

  async startTraining(modelId: string) {
    const response = await axios.post(`${API_BASE}/tts/models/${modelId}/train`, {}, {
      headers: this.getAuthHeaders(),
    });
    return response.data;
  }

  async getTTSHistory(page: number = 1, limit: number = 20) {
    const response = await axios.get(`${API_BASE}/tts/history`, {
      params: { page, limit },
      headers: this.getAuthHeaders(),
    });
    return response.data;
  }

  async getAvailableModels() {
    const response = await axios.get(`${API_BASE}/tts/available-models`, {
      headers: this.getAuthHeaders(),
    });
    return response.data;
  }

  getAudioUrl(filename: string): string {
    return `${API_BASE}/tts/audio/${filename}`;
  }
}

export const ttsService = new TTSService();
export type { TTSGenerateRequest, VoiceCloneRequest, VoiceModel, CreateVoiceModelRequest };