import React, { useState, useRef, useCallback } from 'react';
import { Upload, Mic, Play, Pause, Trash2, CheckCircle, AlertCircle, Volume2 } from 'lucide-react';

interface UploadedFile {
  id: string;
  file: File;
  name: string;
  duration: number;
  audioUrl: string;
  transcript: string;
  isPlaying: boolean;
}

interface VoiceCloneResult {
  audioUrl: string;
  text: string;
  clonedFrom: string;
}

export const SimpleVoiceUpload: React.FC = () => {
  const [uploadedFiles, setUploadedFiles] = useState<UploadedFile[]>([]);
  const [isRecording, setIsRecording] = useState(false);
  const [mediaRecorder, setMediaRecorder] = useState<MediaRecorder | null>(null);
  const [cloneText, setCloneText] = useState('Hallo, dit is een test van mijn gekloonde stem.');
  const [isCloning, setIsCloning] = useState(false);
  const [cloneResult, setCloneResult] = useState<VoiceCloneResult | null>(null);
  const [playingAudio, setPlayingAudio] = useState<HTMLAudioElement | null>(null);
  
  const fileInputRef = useRef<HTMLInputElement>(null);
  const audioRefs = useRef<Map<string, HTMLAudioElement>>(new Map());

  const handleFileUpload = useCallback((files: FileList | null) => {
    if (!files) return;

    Array.from(files).forEach(file => {
      if (!file.type.startsWith('audio/')) {
        alert(`${file.name} is not an audio file`);
        return;
      }

      const id = Math.random().toString(36).substr(2, 9);
      const audioUrl = URL.createObjectURL(file);
      
      // Get audio duration
      const audio = new Audio(audioUrl);
      audio.addEventListener('loadedmetadata', () => {
        const newFile: UploadedFile = {
          id,
          file,
          name: file.name,
          duration: audio.duration,
          audioUrl,
          transcript: '',
          isPlaying: false,
        };

        setUploadedFiles(prev => [...prev, newFile]);
      });
    });
  }, []);

  const startRecording = async () => {
    try {
      const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
      const recorder = new MediaRecorder(stream);
      const chunks: BlobPart[] = [];

      recorder.ondataavailable = (e) => chunks.push(e.data);
      recorder.onstop = () => {
        const blob = new Blob(chunks, { type: 'audio/wav' });
        const file = new File([blob], `recording-${Date.now()}.wav`, { type: 'audio/wav' });
        const fileList = new DataTransfer();
        fileList.items.add(file);
        handleFileUpload(fileList.files);
      };

      recorder.start();
      setMediaRecorder(recorder);
      setIsRecording(true);
    } catch (error) {
      console.error('Recording failed:', error);
      alert('Could not access microphone. Please check permissions.');
    }
  };

  const stopRecording = () => {
    if (mediaRecorder) {
      mediaRecorder.stop();
      mediaRecorder.stream.getTracks().forEach(track => track.stop());
      setMediaRecorder(null);
    }
    setIsRecording(false);
  };

  const playAudio = (fileId: string) => {
    const file = uploadedFiles.find(f => f.id === fileId);
    if (!file) return;

    // Pause currently playing audio
    if (playingAudio) {
      playingAudio.pause();
      playingAudio.currentTime = 0;
    }

    const audio = new Audio(file.audioUrl);
    audioRefs.current.set(fileId, audio);
    
    audio.onplay = () => {
      setUploadedFiles(prev => prev.map(f => 
        f.id === fileId ? { ...f, isPlaying: true } : { ...f, isPlaying: false }
      ));
      setPlayingAudio(audio);
    };

    audio.onpause = () => {
      setUploadedFiles(prev => prev.map(f => 
        f.id === fileId ? { ...f, isPlaying: false } : f
      ));
      setPlayingAudio(null);
    };

    audio.onended = () => {
      setUploadedFiles(prev => prev.map(f => 
        f.id === fileId ? { ...f, isPlaying: false } : f
      ));
      setPlayingAudio(null);
    };

    audio.play();
  };

  const pauseAudio = (fileId: string) => {
    const audio = audioRefs.current.get(fileId);
    if (audio) {
      audio.pause();
    }
  };

  const removeFile = (fileId: string) => {
    const file = uploadedFiles.find(f => f.id === fileId);
    if (file) {
      URL.revokeObjectURL(file.audioUrl);
      const audio = audioRefs.current.get(fileId);
      if (audio) {
        audio.pause();
        audioRefs.current.delete(fileId);
      }
    }
    setUploadedFiles(prev => prev.filter(f => f.id !== fileId));
  };

  const updateTranscript = (fileId: string, transcript: string) => {
    setUploadedFiles(prev => prev.map(f => 
      f.id === fileId ? { ...f, transcript } : f
    ));
  };

  const handleVoiceClone = async () => {
    if (uploadedFiles.length === 0) {
      alert('Please upload at least one audio file first');
      return;
    }

    if (!cloneText.trim()) {
      alert('Please enter text to clone');
      return;
    }

    // Use the first uploaded file as the speaker reference
    const speakerFile = uploadedFiles[0];
    
    setIsCloning(true);
    setCloneResult(null);

    try {
      const formData = new FormData();
      formData.append('speakerAudio', speakerFile.file);
      formData.append('text', cloneText);

      const response = await fetch('/api/tts/clone', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('accessToken')}`,
        },
        body: formData,
      });

      const data = await response.json();
      if (data.success) {
        setCloneResult(data.data);
      } else {
        alert('Voice cloning failed: ' + data.message);
      }
    } catch (error) {
      console.error('Voice cloning error:', error);
      alert('Voice cloning failed');
    } finally {
      setIsCloning(false);
    }
  };

  const createVoiceModel = async () => {
    if (uploadedFiles.length === 0) {
      alert('Please upload voice samples first');
      return;
    }

    const filesWithTranscripts = uploadedFiles.filter(f => f.transcript.trim());
    if (filesWithTranscripts.length === 0) {
      alert('Please add transcripts to your audio files');
      return;
    }

    try {
      // First create a voice model
      const modelResponse = await fetch('/api/tts/models', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${localStorage.getItem('accessToken')}`,
        },
        body: JSON.stringify({
          name: `Custom Voice ${Date.now()}`,
          persona: 'AMSTERDAMMER',
          language: 'NL',
          description: 'Uploaded voice samples',
        }),
      });

      const modelData = await modelResponse.json();
      if (!modelData.success) {
        throw new Error('Failed to create voice model');
      }

      // Upload samples to the model
      const formData = new FormData();
      filesWithTranscripts.forEach(file => {
        formData.append('samples', file.file);
      });
      formData.append('transcript', filesWithTranscripts[0].transcript); // Use first transcript

      const samplesResponse = await fetch(`/api/tts/models/${modelData.data.id}/samples`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('accessToken')}`,
        },
        body: formData,
      });

      const samplesData = await samplesResponse.json();
      if (samplesData.success) {
        alert('Voice model created successfully! You can now train it for better results.');
        setUploadedFiles([]);
      } else {
        alert('Failed to upload samples: ' + samplesData.message);
      }
    } catch (error) {
      console.error('Voice model creation error:', error);
      alert('Failed to create voice model');
    }
  };

  return (
    <div className="max-w-4xl mx-auto p-6">
      <div className="mb-8">
        <h1 className="text-3xl font-bold text-gray-900 mb-2">🎤 Quick Voice Upload</h1>
        <p className="text-gray-600">Upload audio files or record your voice to create AI voice clones</p>
      </div>

      {/* Upload Section */}
      <div className="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 className="text-xl font-semibold mb-4">Upload Voice Samples</h2>
        
        <div className="flex flex-col md:flex-row gap-4 mb-6">
          {/* File Upload */}
          <div 
            className="flex-1 border-2 border-dashed border-gray-300 rounded-lg p-8 text-center cursor-pointer hover:border-blue-400 transition-colors"
            onClick={() => fileInputRef.current?.click()}
            onDragOver={(e) => e.preventDefault()}
            onDrop={(e) => {
              e.preventDefault();
              handleFileUpload(e.dataTransfer.files);
            }}
          >
            <Upload className="w-12 h-12 text-gray-400 mx-auto mb-4" />
            <p className="text-lg font-medium text-gray-700 mb-2">Drop audio files here</p>
            <p className="text-sm text-gray-500">Or click to browse files</p>
            <p className="text-xs text-gray-400 mt-2">Supports: WAV, MP3, M4A, FLAC</p>
            <input
              ref={fileInputRef}
              type="file"
              multiple
              accept="audio/*"
              onChange={(e) => handleFileUpload(e.target.files)}
              className="hidden"
            />
          </div>

          {/* Voice Recording */}
          <div className="flex-1 border-2 border-gray-300 rounded-lg p-8 text-center">
            <Mic className={`w-12 h-12 mx-auto mb-4 ${isRecording ? 'text-red-500' : 'text-gray-400'}`} />
            <p className="text-lg font-medium text-gray-700 mb-4">Record Voice</p>
            {!isRecording ? (
              <button
                onClick={startRecording}
                className="bg-red-600 text-white px-6 py-3 rounded-lg hover:bg-red-700 flex items-center mx-auto"
              >
                <Mic className="w-4 h-4 mr-2" />
                Start Recording
              </button>
            ) : (
              <button
                onClick={stopRecording}
                className="bg-gray-600 text-white px-6 py-3 rounded-lg hover:bg-gray-700 flex items-center mx-auto"
              >
                <div className="w-4 h-4 bg-white rounded-sm mr-2"></div>
                Stop Recording
              </button>
            )}
          </div>
        </div>

        {/* Uploaded Files */}
        {uploadedFiles.length > 0 && (
          <div className="space-y-4">
            <h3 className="text-lg font-medium">Uploaded Files ({uploadedFiles.length})</h3>
            {uploadedFiles.map(file => (
              <div key={file.id} className="border border-gray-200 rounded-lg p-4">
                <div className="flex items-center justify-between mb-3">
                  <div className="flex items-center space-x-3">
                    <Volume2 className="w-5 h-5 text-gray-400" />
                    <div>
                      <p className="font-medium text-gray-900">{file.name}</p>
                      <p className="text-sm text-gray-500">{Math.round(file.duration)}s</p>
                    </div>
                  </div>
                  <div className="flex items-center space-x-2">
                    <button
                      onClick={() => file.isPlaying ? pauseAudio(file.id) : playAudio(file.id)}
                      className="bg-blue-100 text-blue-600 p-2 rounded-full hover:bg-blue-200"
                    >
                      {file.isPlaying ? <Pause className="w-4 h-4" /> : <Play className="w-4 h-4" />}
                    </button>
                    <button
                      onClick={() => removeFile(file.id)}
                      className="bg-red-100 text-red-600 p-2 rounded-full hover:bg-red-200"
                    >
                      <Trash2 className="w-4 h-4" />
                    </button>
                  </div>
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Transcript (what is being said):
                  </label>
                  <input
                    type="text"
                    value={file.transcript}
                    onChange={(e) => updateTranscript(file.id, e.target.value)}
                    placeholder="Enter what is being said in this audio..."
                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                  />
                </div>
              </div>
            ))}
          </div>
        )}
      </div>

      {/* Quick Voice Clone Section */}
      {uploadedFiles.length > 0 && (
        <div className="bg-white rounded-lg shadow-md p-6 mb-6">
          <h2 className="text-xl font-semibold mb-4">🎭 Quick Voice Clone Test</h2>
          <p className="text-gray-600 mb-4">
            Test voice cloning with your uploaded audio (uses the first file as reference)
          </p>
          
          <div className="space-y-4">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                Text to generate:
              </label>
              <textarea
                value={cloneText}
                onChange={(e) => setCloneText(e.target.value)}
                placeholder="Enter the text you want to hear in the cloned voice..."
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                rows={3}
              />
            </div>

            <button
              onClick={handleVoiceClone}
              disabled={isCloning || !cloneText.trim()}
              className="bg-purple-600 text-white px-6 py-3 rounded-lg hover:bg-purple-700 disabled:opacity-50 flex items-center"
            >
              {isCloning ? (
                <>
                  <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
                  Cloning Voice...
                </>
              ) : (
                <>
                  <Mic className="w-4 h-4 mr-2" />
                  Clone Voice
                </>
              )}
            </button>

            {cloneResult && (
              <div className="bg-green-50 border border-green-200 rounded-lg p-4">
                <div className="flex items-center mb-3">
                  <CheckCircle className="w-5 h-5 text-green-600 mr-2" />
                  <span className="font-medium text-green-800">Voice cloned successfully!</span>
                </div>
                <audio controls className="w-full">
                  <source src={cloneResult.audioUrl} type="audio/wav" />
                  Your browser does not support audio playback.
                </audio>
                <p className="text-sm text-green-700 mt-2">
                  Generated: "{cloneResult.text}"
                </p>
              </div>
            )}
          </div>
        </div>
      )}

      {/* Create Voice Model Section */}
      {uploadedFiles.length > 0 && (
        <div className="bg-white rounded-lg shadow-md p-6">
          <h2 className="text-xl font-semibold mb-4">🎯 Create Voice Model</h2>
          <p className="text-gray-600 mb-4">
            Create a permanent voice model that can be trained for better quality. 
            Make sure to add transcripts to your audio files first.
          </p>

          <div className="flex items-center justify-between p-4 bg-blue-50 border border-blue-200 rounded-lg mb-4">
            <div className="flex items-center">
              <AlertCircle className="w-5 h-5 text-blue-600 mr-2" />
              <span className="text-blue-800">
                Files with transcripts: {uploadedFiles.filter(f => f.transcript.trim()).length} / {uploadedFiles.length}
              </span>
            </div>
            <span className="text-sm text-blue-600">
              {uploadedFiles.filter(f => f.transcript.trim()).length >= 3 ? '✅ Ready to create' : '⚠️ Add more transcripts'}
            </span>
          </div>

          <button
            onClick={createVoiceModel}
            disabled={uploadedFiles.filter(f => f.transcript.trim()).length === 0}
            className="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 disabled:opacity-50 flex items-center"
          >
            <CheckCircle className="w-4 h-4 mr-2" />
            Create Voice Model
          </button>
        </div>
      )}

      {/* Instructions */}
      <div className="bg-gray-50 rounded-lg p-6 mt-6">
        <h3 className="text-lg font-semibold mb-3">📖 Quick Guide</h3>
        <div className="grid md:grid-cols-2 gap-6 text-sm text-gray-700">
          <div>
            <h4 className="font-medium mb-2">For best results:</h4>
            <ul className="space-y-1">
              <li>• Upload 5-20 audio samples</li>
              <li>• Each sample should be 5-30 seconds</li>
              <li>• Use clear, high-quality audio</li>
              <li>• Add accurate transcripts</li>
              <li>• Record in a quiet environment</li>
            </ul>
          </div>
          <div>
            <h4 className="font-medium mb-2">Workflow:</h4>
            <ul className="space-y-1">
              <li>1. Upload or record audio files</li>
              <li>2. Add transcripts for each file</li>
              <li>3. Test with quick voice clone</li>
              <li>4. Create permanent voice model</li>
              <li>5. Train model for better quality</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  );
};