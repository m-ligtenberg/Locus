/**
 * TravelBot v3.0 - Interactive Chat Interface
 * Chatfunctionaliteit met AI persona-integratie
 */

class ChatManager {
    constructor() {
        this.messages = [];
        this.currentPersona = 'amsterdammer';
        this.isProcessing = false;
        this.apiEndpoint = '/api/chat'; // Backend endpoint
        this.maxMessages = 50;
        this.typingIndicatorTimeout = null;
        
        // Chat context for AI
        this.chatContext = {
            location: null,
            movement: null,
            timeOfDay: null,
            weatherCondition: null
        };
        
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.loadChatHistory();
        this.addWelcomeMessage();
    }
    
    setupEventListeners() {
        const chatInput = document.getElementById('chat-input');
        const sendBtn = document.getElementById('send-btn');
        
        if (chatInput) {
            chatInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    this.sendMessage();
                }
            });
            
            // Show typing indicator while user types
            chatInput.addEventListener('input', (e) => {
                this.handleTypingIndicator();
            });
        }
        
        if (sendBtn) {
            sendBtn.addEventListener('click', () => {
                this.sendMessage();
            });
        }
        
        // Listen for persona changes
        document.addEventListener('persona-changed', (e) => {
            this.setPersona(e.detail.persona);
        });
        
        // Listen for location updates
        if (window.locationManager) {
            window.locationManager.onLocation((location, summary) => {
                this.updateContext({
                    location: location,
                    movement: summary
                });
            });
        }
    }
    
    addWelcomeMessage() {
        const welcomeText = translator?.translate('welcome') || 'Welkom! Ik ga je vergezellen op je reis.';
        this.addMessage('assistant', welcomeText, {
            isWelcome: true,
            timestamp: Date.now()
        });
    }
    
    sendMessage() {
        const chatInput = document.getElementById('chat-input');
        if (!chatInput) return;
        
        const message = chatInput.value.trim();
        if (!message) return;
        
        // Add user message
        this.addMessage('user', message);
        chatInput.value = '';
        
        // Process message
        this.processMessage(message);
    }
    
    addMessage(sender, content, options = {}) {
        const message = {
            id: `msg-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`,
            sender: sender,
            content: content,
            timestamp: options.timestamp || Date.now(),
            persona: this.currentPersona,
            context: { ...this.chatContext },
            ...options
        };
        
        this.messages.push(message);
        this.renderMessage(message);
        this.scrollToBottom();
        this.saveChatHistory();
        
        // Limit message history
        if (this.messages.length > this.maxMessages) {
            this.messages = this.messages.slice(-this.maxMessages);
            this.removeOldMessages();
        }
        
        return message;
    }
    
    renderMessage(message) {
        const chatMessages = document.getElementById('chat-messages');
        if (!chatMessages) return;
        
        const messageElement = document.createElement('div');
        messageElement.className = `chat-message ${message.sender}`;
        messageElement.id = message.id;
        
        const bubbleElement = document.createElement('div');
        bubbleElement.className = 'message-bubble';
        
        // Add persona emoji for assistant messages
        if (message.sender === 'assistant') {
            const personaEmoji = this.getPersonaEmoji(message.persona);
            bubbleElement.innerHTML = `<span class="message-persona">${personaEmoji}</span> ${message.content}`;
        } else {
            bubbleElement.textContent = message.content;
        }
        
        // Add timestamp
        const timeElement = document.createElement('div');
        timeElement.className = 'message-time';
        timeElement.textContent = this.formatTime(message.timestamp);
        
        messageElement.appendChild(bubbleElement);
        messageElement.appendChild(timeElement);
        
        // Add message actions for assistant messages
        if (message.sender === 'assistant') {
            const actionsElement = this.createMessageActions(message);
            messageElement.appendChild(actionsElement);
        }
        
        chatMessages.appendChild(messageElement);
        
        // Animate in
        requestAnimationFrame(() => {
            messageElement.classList.add('message-visible');
        });
    }
    
    createMessageActions(message) {
        const actionsElement = document.createElement('div');
        actionsElement.className = 'message-actions';
        
        // Speak button
        const speakBtn = document.createElement('button');
        speakBtn.className = 'message-action-btn';
        speakBtn.innerHTML = '🔊';
        speakBtn.title = 'Spreek uit';
        speakBtn.onclick = () => this.speakMessage(message);
        
        // Share button
        const shareBtn = document.createElement('button');
        shareBtn.className = 'message-action-btn';
        shareBtn.innerHTML = '📤';
        shareBtn.title = 'Deel bericht';
        shareBtn.onclick = () => this.shareMessage(message);
        
        // New response button
        const newBtn = document.createElement('button');
        newBtn.className = 'message-action-btn';
        newBtn.innerHTML = '🔄';
        newBtn.title = 'Nieuwe reactie';
        newBtn.onclick = () => this.requestNewResponse();
        
        actionsElement.appendChild(speakBtn);
        actionsElement.appendChild(shareBtn);
        actionsElement.appendChild(newBtn);
        
        return actionsElement;
    }
    
    async processMessage(message) {
        this.isProcessing = true;
        this.showTypingIndicator();
        
        try {
            // Analyze message intent
            const intent = this.analyzeIntent(message);
            
            let response;
            
            // Handle different types of messages
            switch (intent.type) {
                case 'location_question':
                    response = this.handleLocationQuestion(message, intent);
                    break;
                case 'persona_question':
                    response = await this.handlePersonaQuestion(message, intent);
                    break;
                case 'general_chat':
                    response = await this.handleGeneralChat(message, intent);
                    break;
                case 'command':
                    response = this.handleCommand(message, intent);
                    break;
                default:
                    response = await this.handleGeneralChat(message, intent);
                    break;
            }
            
            this.hideTypingIndicator();
            this.addMessage('assistant', response);
            
        } catch (error) {
            console.error('Chat processing error:', error);
            this.hideTypingIndicator();
            
            const errorResponse = translator?.translate('chat_error') || 
                                'Sorry, ik kan je vraag nu niet beantwoorden. Probeer het later opnieuw.';
            this.addMessage('assistant', errorResponse, { isError: true });
        } finally {
            this.isProcessing = false;
        }
    }
    
    analyzeIntent(message) {
        const lowerMessage = message.toLowerCase();
        
        // Location questions
        if (this.matchesPatterns(lowerMessage, ['waar ben ik', 'waar zijn we', 'locatie', 'positie'])) {
            return { type: 'location_question', confidence: 0.9 };
        }
        
        // Persona questions
        if (this.matchesPatterns(lowerMessage, ['wat vind je', 'wat denk je', 'jouw mening'])) {
            return { type: 'persona_question', confidence: 0.8 };
        }
        
        // Commands
        if (this.matchesPatterns(lowerMessage, ['zeg iets', 'nieuwe opmerking', 'vertel me'])) {
            return { type: 'command', confidence: 0.7 };
        }
        
        // General chat
        return { type: 'general_chat', confidence: 0.5 };
    }
    
    matchesPatterns(text, patterns) {
        return patterns.some(pattern => text.includes(pattern));
    }
    
    handleLocationQuestion(message, intent) {
        const location = this.chatContext.location;
        
        if (!location) {
            return translator?.translate('location_not_available') || 
                   'Ik kan je locatie nog niet bepalen. Zorg dat GPS toegang is toegestaan.';
        }
        
        // Use reverse geocoded location if available
        const locationElement = document.getElementById('location-details');
        const locationText = locationElement?.textContent?.replace('📍 ', '') || 
                           `${location.latitude.toFixed(4)}, ${location.longitude.toFixed(4)}`;
        
        const responses = [
            `Je bent op dit moment hier: ${locationText}`,
            `Volgens mijn GPS ben je bij: ${locationText}`,
            `Je huidige locatie is: ${locationText}`
        ];
        
        return responses[Math.floor(Math.random() * responses.length)];
    }
    
    async handlePersonaQuestion(message, intent) {
        const context = this.buildChatContext(message);
        
        try {
            // Call backend API for persona response
            const response = await fetch('/api/persona-chat', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    message: message,
                    persona: this.currentPersona,
                    context: context,
                    language: translator?.getCurrentLanguage() || 'nl'
                })
            });
            
            if (response.ok) {
                const data = await response.json();
                return data.response;
            } else {
                throw new Error(`API error: ${response.status}`);
            }
        } catch (error) {
            console.error('Persona chat error:', error);
            
            // Fallback to local persona responses
            return this.getLocalPersonaResponse(message);
        }
    }
    
    async handleGeneralChat(message, intent) {
        // Similar to handlePersonaQuestion but for general conversation
        return await this.handlePersonaQuestion(message, intent);
    }
    
    handleCommand(message, intent) {
        const lowerMessage = message.toLowerCase();
        
        if (lowerMessage.includes('nieuwe opmerking')) {
            // Trigger new location-based comment
            this.requestLocationComment();
            return 'Ik ga een nieuwe observatie maken over je huidige omgeving...';
        }
        
        if (lowerMessage.includes('zeg iets cynisch')) {
            return this.getCynicalResponse();
        }
        
        return 'Ik begrijp je commando, maar kan het nu niet uitvoeren.';
    }
    
    buildChatContext(message) {
        const context = {
            ...this.chatContext,
            recentMessages: this.messages.slice(-5).map(m => ({
                sender: m.sender,
                content: m.content
            })),
            timeOfDay: this.getTimeOfDay(),
            dayOfWeek: this.getDayOfWeek(),
            language: translator?.getCurrentLanguage() || 'nl'
        };
        
        // Add location context if available
        if (this.chatContext.location) {
            context.speed = Math.round(this.chatContext.movement?.speed || 0);
            context.isMoving = (context.speed > 2);
            context.routeType = this.chatContext.movement?.routeType;
        }
        
        return context;
    }
    
    getLocalPersonaResponse(message) {
        // Fallback persona responses when API is unavailable
        const responses = {
            'amsterdammer': [
                'Ja, dat is een goede vraag.',
                'Kijk, dat zit zo...',
                'Nou, zo zie ik het wel.',
                'Dat is helder.'
            ],
            'belg': [
                'Alleeh, dat is een moeilijke vraag...',
                'Pff, wat moet ik daar nou van zeggen?',
                'C\'est bon, laat me even nadenken.',
                'Och ja, het leven is soms ingewikkeld.'
            ]
        };
        
        const personaResponses = responses[this.currentPersona] || responses['amsterdammer'];
        return personaResponses[Math.floor(Math.random() * personaResponses.length)];
    }
    
    getCynicalResponse() {
        const cynicalResponses = [
            'Het leven is een file, en wij staan stil.',
            'Weer een dag waarin we van A naar B rijden, alsof het uitmaakt.',
            'GPS weet waar we zijn, maar weten wij waar we naartoe gaan?',
            'Benzine wordt duurder, tijd wordt korter, maar de weg blijft hetzelfde.',
            'We jagen op groen licht, terwijl het leven op rood staat.'
        ];
        
        return cynicalResponses[Math.floor(Math.random() * cynicalResponses.length)];
    }
    
    requestLocationComment() {
        // Trigger main app to generate location-based comment
        const event = new CustomEvent('request-location-comment', {
            detail: { source: 'chat' }
        });
        document.dispatchEvent(event);
    }
    
    requestNewResponse() {
        // Request new response for current location
        this.addMessage('assistant', 'Ik ga een nieuwe observatie maken...');
        this.requestLocationComment();
    }
    
    showTypingIndicator() {
        const chatMessages = document.getElementById('chat-messages');
        if (!chatMessages) return;
        
        const indicator = document.createElement('div');
        indicator.id = 'typing-indicator';
        indicator.className = 'typing-indicator';
        indicator.innerHTML = `
            <div class="typing-dots">
                <div class="typing-dot"></div>
                <div class="typing-dot"></div>
                <div class="typing-dot"></div>
            </div>
            <span>${this.getPersonaEmoji(this.currentPersona)} is aan het typen...</span>
        `;
        
        chatMessages.appendChild(indicator);
        this.scrollToBottom();
    }
    
    hideTypingIndicator() {
        const indicator = document.getElementById('typing-indicator');
        if (indicator) {
            indicator.remove();
        }
    }
    
    handleTypingIndicator() {
        // Clear existing timeout
        if (this.typingIndicatorTimeout) {
            clearTimeout(this.typingIndicatorTimeout);
        }
        
        // Show user typing indication (could be shown to other users in multiplayer)
        this.typingIndicatorTimeout = setTimeout(() => {
            // Hide typing indication after user stops typing
        }, 1000);
    }
    
    speakMessage(message) {
        if (window.ttsManager) {
            window.ttsManager.speakPersonaResponse(message.content, message.persona);
        }
    }
    
    shareMessage(message) {
        if (window.shareManager) {
            window.shareManager.shareQuote(message.content, message.persona);
        } else {
            // Fallback: copy to clipboard
            navigator.clipboard.writeText(message.content).then(() => {
                this.showNotification('Bericht gekopieerd naar klembord');
            });
        }
    }
    
    setPersona(persona) {
        const oldPersona = this.currentPersona;
        this.currentPersona = persona;
        
        // Add persona change message
        if (oldPersona !== persona) {
            const changeMessage = `${this.getPersonaEmoji(persona)} ${this.getPersonaName(persona)} is nu je reisgenoot.`;
            this.addMessage('system', changeMessage, { isPersonaChange: true });
        }
    }
    
    updateContext(contextUpdate) {
        this.chatContext = { ...this.chatContext, ...contextUpdate };
    }
    
    scrollToBottom() {
        const chatMessages = document.getElementById('chat-messages');
        if (chatMessages) {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
    }
    
    formatTime(timestamp) {
        return new Date(timestamp).toLocaleTimeString([], { 
            hour: '2-digit', 
            minute: '2-digit' 
        });
    }
    
    getPersonaEmoji(persona) {
        const emojis = {
            'amsterdammer': '🏛️',
            'belg': '🍺',
            'brabander': '🍻',
            'jordanees': '👑'
        };
        return emojis[persona] || '🤖';
    }
    
    getPersonaName(persona) {
        const names = {
            'amsterdammer': 'De Amsterdammer',
            'belg': 'Neerslachtige Belg',
            'brabander': 'Brabander',
            'jordanees': 'Jordanees'
        };
        return names[persona] || persona;
    }
    
    getTimeOfDay() {
        const hour = new Date().getHours();
        if (hour < 6) return 'night';
        if (hour < 12) return 'morning';
        if (hour < 18) return 'afternoon';
        if (hour < 22) return 'evening';
        return 'night';
    }
    
    getDayOfWeek() {
        const days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        return days[new Date().getDay()];
    }
    
    showNotification(message) {
        // Simple notification system
        const notification = document.createElement('div');
        notification.className = 'chat-notification';
        notification.textContent = message;
        notification.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: var(--primary-color);
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            z-index: 1000;
            animation: slideInUp 0.3s ease;
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.animation = 'slideOutDown 0.3s ease';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
    
    // Chat history persistence
    saveChatHistory() {
        try {
            const history = this.messages.slice(-20); // Save last 20 messages
            localStorage.setItem('travelbot-chat-history', JSON.stringify(history));
        } catch (error) {
            console.warn('Could not save chat history:', error);
        }
    }
    
    loadChatHistory() {
        try {
            const saved = localStorage.getItem('travelbot-chat-history');
            if (saved) {
                const history = JSON.parse(saved);
                // Don't render old messages on load, just keep them in memory
                this.messages = history;
            }
        } catch (error) {
            console.warn('Could not load chat history:', error);
        }
    }
    
    removeOldMessages() {
        const chatMessages = document.getElementById('chat-messages');
        if (!chatMessages) return;
        
        const messageElements = chatMessages.querySelectorAll('.chat-message');
        const excessCount = messageElements.length - this.maxMessages;
        
        for (let i = 0; i < excessCount; i++) {
            messageElements[i].remove();
        }
    }
    
    clearChat() {
        this.messages = [];
        const chatMessages = document.getElementById('chat-messages');
        if (chatMessages) {
            chatMessages.innerHTML = '';
        }
        this.addWelcomeMessage();
        localStorage.removeItem('travelbot-chat-history');
    }
    
    // Export chat for sharing/debugging
    exportChat() {
        return {
            messages: this.messages,
            persona: this.currentPersona,
            context: this.chatContext,
            timestamp: Date.now()
        };
    }
}

// Initialize when DOM is loaded
let chatManager;
document.addEventListener('DOMContentLoaded', () => {
    chatManager = new ChatManager();
});

// Export for other modules
window.ChatManager = ChatManager;
