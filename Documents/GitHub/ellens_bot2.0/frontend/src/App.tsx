import React from 'react';
import ChatInterface from './components/Chat/ChatInterface';
import { useWebSocketChat } from './hooks/useWebSocketChat';

function App() {
  const { 
    messages, 
    isEllensTyping, 
    ellensTypingMood, 
    isConnected, 
    connectionError, 
    sendMessage, 
    setUserTyping, 
    retryConnection 
  } = useWebSocketChat();

  return (
    <div className="App min-h-screen bg-primary-bg">
      <ChatInterface
        messages={messages}
        onSendMessage={sendMessage}
        onUserTyping={setUserTyping}
        isEllensTyping={isEllensTyping}
        ellensTypingMood={ellensTypingMood}
        isConnected={isConnected}
        connectionError={connectionError}
        onRetryConnection={retryConnection}
      />
    </div>
  );
}

export default App;
