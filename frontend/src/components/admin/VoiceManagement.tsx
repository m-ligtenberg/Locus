import React, { useState, useEffect } from 'react';
import { Upload, Settings, Mic, Users } from 'lucide-react';
import { API_BASE_URL } from '../../config/api';

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

interface TTSRequest {
  id: string;
  text: string;
  persona: string;
  language: string;
  audioUrl?: string;
  duration?: number;
  status: string;
  createdAt: string;
}

export const VoiceManagement: React.FC = () => {
  const [activeTab, setActiveTab] = useState<'models' | 'samples' | 'generate' | 'history'>('models');
  const [voiceModels, setVoiceModels] = useState<VoiceModel[]>([]);
  const [ttsHistory, setTtsHistory] = useState<TTSRequest[]>([]);
  const [selectedModel, setSelectedModel] = useState<VoiceModel | null>(null);

  // Voice generation state
  const [generateText, setGenerateText] = useState('');
  const [selectedPersona, setSelectedPersona] = useState('AMSTERDAMMER');
  const [selectedLanguage, setSelectedLanguage] = useState('NL');
  const [isGenerating, setIsGenerating] = useState(false);
  const [generatedAudio, setGeneratedAudio] = useState<string | null>(null);

  // New model creation state
  const [showCreateModel, setShowCreateModel] = useState(false);
  const [newModelData, setNewModelData] = useState({
    name: '',
    persona: 'AMSTERDAMMER',
    language: 'NL',
    description: '',
  });

  // Sample upload state
  const [uploadSamples, setUploadSamples] = useState<File[]>([]);
  const [sampleTranscript, setSampleTranscript] = useState('');

  const personas = [
    { value: 'AMSTERDAMMER', label: '🏛️ Amsterdammer' },
    { value: 'BELGIQUE', label: '🍺 Belgique' },
    { value: 'BRABANDER', label: '🍻 Brabander' },
    { value: 'JORDANEES', label: '👑 Jordanees' },
  ];

  const languages = [
    { value: 'NL', label: '🇳🇱 Nederlands' },
    { value: 'EN', label: '🇬🇧 English' },
    { value: 'DE', label: '🇩🇪 Deutsch' },
    { value: 'FR', label: '🇫🇷 Français' },
  ];

  useEffect(() => {
    fetchVoiceModels();
    fetchTTSHistory();
  }, []);

  const fetchVoiceModels = async () => {
    try {
      const response = await fetch(`${API_BASE_URL}/api/tts/models`, {
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('accessToken')}`,
        },
      });
      const data = await response.json();
      if (data.success) {
        setVoiceModels(data.data);
      }
    } catch (error) {
      console.error('Failed to fetch voice models:', error);
    }
  };

  const fetchTTSHistory = async () => {
    try {
      const response = await fetch(`${API_BASE_URL}/api/tts/history`, {
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('accessToken')}`,
        },
      });
      const data = await response.json();
      if (data.success) {
        setTtsHistory(data.data);
      }
    } catch (error) {
      console.error('Failed to fetch TTS history:', error);
    }
  };

  const handleGenerateTTS = async () => {
    if (!generateText.trim()) return;

    setIsGenerating(true);
    try {
      const response = await fetch(`${API_BASE_URL}/api/tts/generate`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${localStorage.getItem('accessToken')}`,
        },
        body: JSON.stringify({
          text: generateText,
          persona: selectedPersona,
          language: selectedLanguage,
        }),
      });

      const data = await response.json();
      if (data.success) {
        setGeneratedAudio(data.data.audioUrl);
        fetchTTSHistory(); // Refresh history
      } else {
        alert('TTS generation failed: ' + data.message);
      }
    } catch (error) {
      console.error('TTS generation error:', error);
      alert('TTS generation failed');
    } finally {
      setIsGenerating(false);
    }
  };

  const handleCreateModel = async () => {
    if (!newModelData.name.trim()) return;

    try {
      const response = await fetch(`${API_BASE_URL}/api/tts/models`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${localStorage.getItem('accessToken')}`,
        },
        body: JSON.stringify(newModelData),
      });

      const data = await response.json();
      if (data.success) {
        setVoiceModels([...voiceModels, data.data]);
        setShowCreateModel(false);
        setNewModelData({ name: '', persona: 'AMSTERDAMMER', language: 'NL', description: '' });
      } else {
        alert('Model creation failed: ' + data.message);
      }
    } catch (error) {
      console.error('Model creation error:', error);
      alert('Model creation failed');
    }
  };

  const handleUploadSamples = async (modelId: string) => {
    if (uploadSamples.length === 0 || !sampleTranscript.trim()) {
      alert('Please select audio files and provide transcript');
      return;
    }

    const formData = new FormData();
    uploadSamples.forEach(file => formData.append('samples', file));
    formData.append('transcript', sampleTranscript);

    try {
      const response = await fetch(`${API_BASE_URL}/api/tts/models/${modelId}/samples`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('accessToken')}`,
        },
        body: formData,
      });

      const data = await response.json();
      if (data.success) {
        alert('Samples uploaded successfully!');
        fetchVoiceModels(); // Refresh models
        setUploadSamples([]);
        setSampleTranscript('');
      } else {
        alert('Sample upload failed: ' + data.message);
      }
    } catch (error) {
      console.error('Sample upload error:', error);
      alert('Sample upload failed');
    }
  };

  const handleStartTraining = async (modelId: string) => {
    if (!confirm('Start training this voice model? This may take several hours.')) return;

    try {
      const response = await fetch(`${API_BASE_URL}/api/tts/models/${modelId}/train`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('accessToken')}`,
        },
      });

      const data = await response.json();
      if (data.success) {
        alert('Training started! Check back later for results.');
        fetchVoiceModels(); // Refresh models
      } else {
        alert('Training start failed: ' + data.message);
      }
    } catch (error) {
      console.error('Training start error:', error);
      alert('Training start failed');
    }
  };

  return (
    <div className="max-w-7xl mx-auto p-6">
      <div className="mb-8">
        <h1 className="text-3xl font-bold text-gray-900 mb-2">🎤 Voice Management</h1>
        <p className="text-gray-600">Manage AI personas, voice models, and TTS generation</p>
      </div>

      {/* Tab Navigation */}
      <div className="border-b border-gray-200 mb-6">
        <nav className="-mb-px flex space-x-8">
          {[
            { id: 'models', label: 'Voice Models', icon: Users },
            { id: 'generate', label: 'Generate TTS', icon: Mic },
            { id: 'history', label: 'History', icon: Settings },
          ].map(tab => (
            <button
              key={tab.id}
              onClick={() => setActiveTab(tab.id as any)}
              className={`flex items-center py-2 px-1 border-b-2 font-medium text-sm ${
                activeTab === tab.id
                  ? 'border-blue-500 text-blue-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
              }`}
            >
              <tab.icon className="w-4 h-4 mr-2" />
              {tab.label}
            </button>
          ))}
        </nav>
      </div>

      {/* Voice Models Tab */}
      {activeTab === 'models' && (
        <div className="space-y-6">
          <div className="flex justify-between items-center">
            <h2 className="text-xl font-semibold">Voice Models</h2>
            <button
              onClick={() => setShowCreateModel(true)}
              className="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center"
            >
              <Users className="w-4 h-4 mr-2" />
              Create Model
            </button>
          </div>

          <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            {voiceModels.map(model => (
              <div key={model.id} className="bg-white rounded-lg shadow-md p-6 border">
                <div className="flex justify-between items-start mb-4">
                  <div>
                    <h3 className="font-semibold text-lg">{model.name}</h3>
                    <p className="text-sm text-gray-600">
                      {personas.find(p => p.value === model.persona)?.label} • {languages.find(l => l.value === model.language)?.label}
                    </p>
                  </div>
                  <div className={`px-2 py-1 rounded-full text-xs ${
                    model.isActive ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
                  }`}>
                    {model.isActive ? 'Active' : 'Inactive'}
                  </div>
                </div>

                {model.description && (
                  <p className="text-sm text-gray-600 mb-4">{model.description}</p>
                )}

                <div className="space-y-3">
                  <div className="flex justify-between text-sm">
                    <span>Samples:</span>
                    <span>{model.samples.length} uploaded</span>
                  </div>

                  {model.quality && (
                    <div className="flex justify-between text-sm">
                      <span>Quality:</span>
                      <span>{Math.round(model.quality * 100)}%</span>
                    </div>
                  )}

                  {model.isTraining && (
                    <div className="bg-yellow-50 border border-yellow-200 rounded p-3">
                      <p className="text-sm text-yellow-800">
                        🏋️ Training in progress... ({model.trainingStatus})
                      </p>
                    </div>
                  )}

                  <div className="flex space-x-2">
                    <button
                      onClick={() => setSelectedModel(model)}
                      className="flex-1 bg-gray-100 text-gray-700 px-3 py-2 rounded text-sm hover:bg-gray-200"
                    >
                      Manage
                    </button>
                    {!model.isTraining && model.samples.length >= 5 && (
                      <button
                        onClick={() => handleStartTraining(model.id)}
                        className="bg-blue-600 text-white px-3 py-2 rounded text-sm hover:bg-blue-700"
                      >
                        Train
                      </button>
                    )}
                  </div>
                </div>
              </div>
            ))}
          </div>

          {/* Create Model Modal */}
          {showCreateModel && (
            <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
              <div className="bg-white rounded-lg p-6 w-full max-w-md">
                <h3 className="text-lg font-semibold mb-4">Create Voice Model</h3>
                <div className="space-y-4">
                  <div>
                    <label className="block text-sm font-medium mb-2">Model Name</label>
                    <input
                      type="text"
                      value={newModelData.name}
                      onChange={(e) => setNewModelData({...newModelData, name: e.target.value})}
                      className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                      placeholder="e.g., Amsterdammer Voice v1"
                    />
                  </div>
                  <div>
                    <label className="block text-sm font-medium mb-2">Persona</label>
                    <select
                      value={newModelData.persona}
                      onChange={(e) => setNewModelData({...newModelData, persona: e.target.value})}
                      className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                      {personas.map(persona => (
                        <option key={persona.value} value={persona.value}>
                          {persona.label}
                        </option>
                      ))}
                    </select>
                  </div>
                  <div>
                    <label className="block text-sm font-medium mb-2">Language</label>
                    <select
                      value={newModelData.language}
                      onChange={(e) => setNewModelData({...newModelData, language: e.target.value})}
                      className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                      {languages.map(language => (
                        <option key={language.value} value={language.value}>
                          {language.label}
                        </option>
                      ))}
                    </select>
                  </div>
                  <div>
                    <label className="block text-sm font-medium mb-2">Description (Optional)</label>
                    <textarea
                      value={newModelData.description}
                      onChange={(e) => setNewModelData({...newModelData, description: e.target.value})}
                      className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                      rows={3}
                      placeholder="Describe this voice model..."
                    />
                  </div>
                </div>
                <div className="flex space-x-3 mt-6">
                  <button
                    onClick={() => setShowCreateModel(false)}
                    className="flex-1 px-4 py-2 text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200"
                  >
                    Cancel
                  </button>
                  <button
                    onClick={handleCreateModel}
                    className="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                  >
                    Create
                  </button>
                </div>
              </div>
            </div>
          )}

          {/* Model Management Modal */}
          {selectedModel && (
            <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
              <div className="bg-white rounded-lg p-6 w-full max-w-2xl max-h-[80vh] overflow-y-auto">
                <div className="flex justify-between items-center mb-4">
                  <h3 className="text-lg font-semibold">Manage {selectedModel.name}</h3>
                  <button
                    onClick={() => setSelectedModel(null)}
                    className="text-gray-400 hover:text-gray-600"
                  >
                    ✕
                  </button>
                </div>

                <div className="space-y-6">
                  <div>
                    <h4 className="font-medium mb-3">Upload Voice Samples</h4>
                    <div className="border-2 border-dashed border-gray-300 rounded-lg p-6">
                      <input
                        type="file"
                        multiple
                        accept="audio/*"
                        onChange={(e) => setUploadSamples(Array.from(e.target.files || []))}
                        className="w-full mb-4"
                      />
                      <textarea
                        value={sampleTranscript}
                        onChange={(e) => setSampleTranscript(e.target.value)}
                        placeholder="Enter the transcript of what's being said in the audio samples..."
                        className="w-full px-3 py-2 border border-gray-300 rounded-md"
                        rows={3}
                      />
                      <button
                        onClick={() => handleUploadSamples(selectedModel.id)}
                        disabled={uploadSamples.length === 0 || !sampleTranscript.trim()}
                        className="mt-3 bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 disabled:opacity-50"
                      >
                        <Upload className="w-4 h-4 inline mr-2" />
                        Upload Samples
                      </button>
                    </div>
                  </div>

                  <div>
                    <h4 className="font-medium mb-3">Current Samples ({selectedModel.samples.length})</h4>
                    <div className="space-y-2">
                      {selectedModel.samples.map(sample => (
                        <div key={sample.id} className="flex items-center justify-between bg-gray-50 p-3 rounded">
                          <div>
                            <span className="font-medium">{sample.filename}</span>
                            <span className="text-sm text-gray-600 ml-2">
                              ({Math.round(sample.duration)}s)
                            </span>
                          </div>
                          <div className={`px-2 py-1 rounded text-xs ${
                            sample.isApproved ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'
                          }`}>
                            {sample.isApproved ? 'Approved' : 'Pending'}
                          </div>
                        </div>
                      ))}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          )}
        </div>
      )}

      {/* Generate TTS Tab */}
      {activeTab === 'generate' && (
        <div className="max-w-2xl">
          <h2 className="text-xl font-semibold mb-6">Generate TTS Audio</h2>
          
          <div className="bg-white rounded-lg shadow-md p-6 space-y-4">
            <div className="grid grid-cols-2 gap-4">
              <div>
                <label className="block text-sm font-medium mb-2">Persona</label>
                <select
                  value={selectedPersona}
                  onChange={(e) => setSelectedPersona(e.target.value)}
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                  {personas.map(persona => (
                    <option key={persona.value} value={persona.value}>
                      {persona.label}
                    </option>
                  ))}
                </select>
              </div>
              <div>
                <label className="block text-sm font-medium mb-2">Language</label>
                <select
                  value={selectedLanguage}
                  onChange={(e) => setSelectedLanguage(e.target.value)}
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                  {languages.map(language => (
                    <option key={language.value} value={language.value}>
                      {language.label}
                    </option>
                  ))}
                </select>
              </div>
            </div>

            <div>
              <label className="block text-sm font-medium mb-2">Text to Generate</label>
              <textarea
                value={generateText}
                onChange={(e) => setGenerateText(e.target.value)}
                placeholder="Enter the text you want to convert to speech..."
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                rows={4}
              />
            </div>

            <button
              onClick={handleGenerateTTS}
              disabled={!generateText.trim() || isGenerating}
              className="w-full bg-blue-600 text-white py-3 rounded-md hover:bg-blue-700 disabled:opacity-50 flex items-center justify-center"
            >
              {isGenerating ? (
                <>
                  <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
                  Generating...
                </>
              ) : (
                <>
                  <Mic className="w-4 h-4 mr-2" />
                  Generate Speech
                </>
              )}
            </button>

            {generatedAudio && (
              <div className="mt-4 p-4 bg-green-50 border border-green-200 rounded">
                <p className="text-sm text-green-800 mb-2">Audio generated successfully!</p>
                <audio controls className="w-full">
                  <source src={generatedAudio} type="audio/wav" />
                  Your browser does not support audio playback.
                </audio>
              </div>
            )}
          </div>
        </div>
      )}

      {/* History Tab */}
      {activeTab === 'history' && (
        <div>
          <h2 className="text-xl font-semibold mb-6">TTS Generation History</h2>
          
          <div className="bg-white rounded-lg shadow-md overflow-hidden">
            {ttsHistory.length === 0 ? (
              <div className="p-8 text-center text-gray-500">
                No TTS history found. Generate some audio first!
              </div>
            ) : (
              <div className="divide-y divide-gray-200">
                {ttsHistory.map(request => (
                  <div key={request.id} className="p-4 hover:bg-gray-50">
                    <div className="flex items-start justify-between">
                      <div className="flex-1">
                        <p className="text-sm text-gray-600 mb-1">
                          {personas.find(p => p.value === request.persona)?.label} • {languages.find(l => l.value === request.language)?.label}
                        </p>
                        <p className="text-gray-900 mb-2">{request.text.substring(0, 100)}...</p>
                        <p className="text-xs text-gray-500">
                          {new Date(request.createdAt).toLocaleString()}
                          {request.duration && ` • ${Math.round(request.duration)}s`}
                        </p>
                      </div>
                      <div className="ml-4 flex items-center space-x-2">
                        <div className={`px-2 py-1 rounded text-xs ${
                          request.status === 'COMPLETED' ? 'bg-green-100 text-green-800' :
                          request.status === 'PROCESSING' ? 'bg-yellow-100 text-yellow-800' :
                          'bg-red-100 text-red-800'
                        }`}>
                          {request.status}
                        </div>
                        {request.audioUrl && (
                          <audio controls className="w-48">
                            <source src={request.audioUrl} type="audio/wav" />
                          </audio>
                        )}
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            )}
          </div>
        </div>
      )}
    </div>
  );
};