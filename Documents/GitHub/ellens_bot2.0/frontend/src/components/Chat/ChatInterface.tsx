import React, { useState, useRef, useEffect } from 'react';
import { Message } from '../../types';
import MessageBubble from './MessageBubble';
import TypingIndicator from './TypingIndicator';

interface ChatInterfaceProps {
  messages: Message[];
  onSendMessage: (message: string) => void;
  onUserTyping?: (isTyping: boolean) => void;
  isEllensTyping: boolean;
  ellensTypingMood?: string;
  isConnected?: boolean;
  connectionError?: string | null;
  onRetryConnection?: () => void;
}

const ChatInterface: React.FC<ChatInterfaceProps> = ({
  messages,
  onSendMessage,
  onUserTyping,
  isEllensTyping,
  ellensTypingMood = 'chill',
  isConnected = true,
  connectionError,
  onRetryConnection
}) => {
  const [inputMessage, setInputMessage] = useState('');
  const [isUserTyping, setIsUserTyping] = useState(false);
  const messagesEndRef = useRef<HTMLDivElement>(null);

  const scrollToBottom = () => {
    messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
  };

  useEffect(() => {
    scrollToBottom();
  }, [messages, isEllensTyping]);

  const handleSendMessage = (e: React.FormEvent) => {
    e.preventDefault();
    if (inputMessage.trim()) {
      onSendMessage(inputMessage.trim());
      setInputMessage('');
      setIsUserTyping(false);
    }
  };

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setInputMessage(e.target.value);
    const typing = e.target.value.length > 0;
    setIsUserTyping(typing);
    onUserTyping?.(typing);
  };

  return (
    <div className="flex flex-col h-screen bg-primary text-primary chat-container overflow-hidden">
      {/* Header */}
      <div className="bg-secondary border-b border-custom p-3 md:p-4 shadow-lg">
        <div className="flex items-center justify-between">
          <div className="flex-1 min-w-0">
            <h1 className="text-lg md:text-xl font-street text-accent-green neon-glow truncate">
              🎤 Young Ellens Chat
            </h1>
            <p className="text-xs md:text-sm text-secondary mt-1 truncate">
              {connectionError ? (
                <span className="text-red-500">❌ Connection Error</span>
              ) : !isConnected ? (
                <span className="text-yellow-500">🔄 Connecting...</span>
              ) : isEllensTyping ? (
                <span className={ellensTypingMood === 'chaotic' ? 'glitch-effect' : ''}>
                  Ellens is {ellensTypingMood}...
                </span>
              ) : (
                "Chat met Mr. Cocaine (die er natuurlijk niet op is)"
              )}
            </p>
          </div>
          <div className="flex items-center space-x-2 ml-2">
            <div className={`w-3 h-3 rounded-full transition-all duration-300 ${
              isConnected && !connectionError 
                ? 'bg-accent-green connection-pulse shadow-glow' 
                : 'bg-red-500'
            }`} />
            {connectionError && onRetryConnection && (
              <button 
                onClick={onRetryConnection}
                className="text-xs bg-accent-green text-black px-2 py-1 rounded hover:bg-accent-yellow transition-colors font-bold min-h-[28px]"
              >
                Retry
              </button>
            )}
          </div>
        </div>
        
        {/* Chaos Level Indicator */}
        {isConnected && (
          <div className="mt-2">
            <div className="flex items-center justify-between text-xs text-muted mb-1">
              <span>Chaos Level</span>
              <span>50%</span>
            </div>
            <div className="w-full bg-tertiary rounded-full h-1 overflow-hidden">
              <div className="chaos-bar w-1/2 h-full"></div>
            </div>
          </div>
        )}
      </div>

      {/* Messages Area */}
      <div className="flex-1 overflow-y-auto p-3 md:p-4 space-y-3 md:space-y-4 scroll-smooth">
        {messages.length === 0 && (
          <div className="text-center text-secondary py-8 md:py-12">
            <div className="max-w-sm mx-auto px-4">
              <p className="mb-3 text-lg">💬 Start een gesprek met Young Ellens!</p>
              <p className="text-sm text-muted">Vraag maar wat over... ehh... muziek natuurlijk</p>
              <div className="mt-4 space-y-2 text-xs text-muted">
                <p>💡 Probeer te vragen over drugs (hij ontkent alles)</p>
                <p>🎵 Of praat over muziek en rap</p>
                <p>😵‍💫 Let op zijn chaos level!</p>
              </div>
            </div>
          </div>
        )}
        
        {messages.map((message, index) => (
          <div key={message.id} className={
            message.sender === 'ellens' ? 'message-enter-left' : 'message-enter-right'
          }>
            <MessageBubble message={message} />
          </div>
        ))}
        
        {isEllensTyping && (
          <div className="message-enter-left">
            <TypingIndicator mood={ellensTypingMood} />
          </div>
        )}
        
        <div ref={messagesEndRef} />
      </div>

      {/* Input Area */}
      <div className="bg-secondary border-t border-custom p-3 md:p-4 shadow-lg">
        <form onSubmit={handleSendMessage} className="flex gap-2 md:gap-3">
          <input
            type="text"
            value={inputMessage}
            onChange={handleInputChange}
            placeholder={!isConnected ? "Connecting..." : connectionError ? "Connection error..." : "Type je message..."}
            disabled={!isConnected || !!connectionError}
            className="flex-1 bg-tertiary text-primary border border-custom rounded-lg px-3 md:px-4 py-2 md:py-3 text-sm md:text-base focus:outline-none focus:border-accent-green focus:ring-1 focus:ring-accent-green disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 min-h-[44px]"
            maxLength={500}
          />
          <button
            type="submit"
            disabled={!inputMessage.trim() || !isConnected || !!connectionError}
            className="bg-accent-green text-black font-bold px-4 md:px-6 py-2 md:py-3 rounded-lg hover:bg-accent-yellow transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed shadow-lg hover:shadow-xl text-sm md:text-base min-h-[44px] min-w-[60px] md:min-w-[80px]"
          >
            <span className="hidden sm:inline">Send</span>
            <span className="sm:hidden">📤</span>
          </button>
        </form>
        
        <div className="flex items-center justify-between mt-2">
          {isUserTyping && (
            <div className="text-xs text-secondary">
              <span className="inline-flex items-center">
                You are typing
                <span className="ml-1 flex space-x-1">
                  <span className="typing-dot w-1 h-1 bg-accent-green rounded-full"></span>
                  <span className="typing-dot w-1 h-1 bg-accent-green rounded-full"></span>
                  <span className="typing-dot w-1 h-1 bg-accent-green rounded-full"></span>
                </span>
              </span>
            </div>
          )}
          
          <div className="text-xs text-muted ml-auto">
            {inputMessage.length}/500
          </div>
        </div>
      </div>
    </div>
  );
};

export default ChatInterface;