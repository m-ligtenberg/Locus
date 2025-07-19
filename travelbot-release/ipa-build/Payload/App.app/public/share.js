/**
 * TravelBot v3.0 - Share Manager
 * Deelbare reisquotes als afbeeldingen en links
 */

class ShareManager {
    constructor() {
        this.canvas = null;
        this.ctx = null;
        this.shareModal = null;
        this.currentQuote = null;
        this.currentPersona = null;
        
        // Canvas dimensions
        this.canvasWidth = 600;
        this.canvasHeight = 400;
        
        // Design settings
        this.themes = {
            light: {
                background: '#ffffff',
                accent: '#2196F3',
                text: '#333333',
                textSecondary: '#666666',
                border: '#e0e0e0'
            },
            dark: {
                background: '#1e1e1e',
                accent: '#1976D2',
                text: '#ffffff',
                textSecondary: '#b0b0b0',
                border: '#333333'
            },
            night: {
                background: '#121212',
                accent: '#FF8F00',
                text: '#ffffff',
                textSecondary: '#b0b0b0',
                border: '#333333'
            }
        };
        
        this.init();
    }
    
    init() {
        this.setupCanvas();
        this.setupEventListeners();
        this.loadSettings();
    }
    
    setupCanvas() {
        this.canvas = document.getElementById('share-canvas');
        if (this.canvas) {
            this.ctx = this.canvas.getContext('2d');
            this.canvas.width = this.canvasWidth;
            this.canvas.height = this.canvasHeight;
        }
    }
    
    setupEventListeners() {
        // Share modal controls
        const shareModal = document.getElementById('share-modal');
        const closeBtn = shareModal?.querySelector('.close-btn');
        const downloadBtn = document.getElementById('download-image-btn');
        const copyLinkBtn = document.getElementById('copy-link-btn');
        
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                this.hideShareModal();
            });
        }
        
        if (downloadBtn) {
            downloadBtn.addEventListener('click', () => {
                this.downloadImage();
            });
        }
        
        if (copyLinkBtn) {
            copyLinkBtn.addEventListener('click', () => {
                this.copyShareLink();
            });
        }
        
        // Click outside modal to close
        if (shareModal) {
            shareModal.addEventListener('click', (e) => {
                if (e.target === shareModal) {
                    this.hideShareModal();
                }
            });
        }
        
        // Listen for share requests
        document.addEventListener('share-quote', (e) => {
            this.shareQuote(e.detail.text, e.detail.persona, e.detail.context);
        });
    }
    
    shareQuote(text, persona, context = {}) {
        this.currentQuote = text;
        this.currentPersona = persona;
        
        // Generate image
        this.generateQuoteImage(text, persona, context);
        
        // Show share modal
        this.showShareModal();
        
        // Generate shareable link
        this.generateShareLink(text, persona, context);
    }
    
    generateQuoteImage(text, persona, context = {}) {
        if (!this.canvas || !this.ctx) return;
        
        const theme = this.getTheme(context);
        const personaInfo = this.getPersonaInfo(persona);
        
        // Clear canvas
        this.ctx.clearRect(0, 0, this.canvasWidth, this.canvasHeight);
        
        // Draw background
        this.drawBackground(theme, context);
        
        // Draw persona avatar
        this.drawPersonaAvatar(personaInfo, theme);
        
        // Draw quote text
        this.drawQuoteText(text, theme, personaInfo);
        
        // Draw footer with branding
        this.drawFooter(theme, context);
        
        // Draw decorative elements
        this.drawDecorations(theme, context);
    }
    
    drawBackground(theme, context) {
        const gradient = this.ctx.createLinearGradient(0, 0, this.canvasWidth, this.canvasHeight);
        
        if (context.timeOfDay === 'night') {
            gradient.addColorStop(0, '#1a1a2e');
            gradient.addColorStop(1, '#16213e');
        } else if (context.isMoving) {
            gradient.addColorStop(0, theme.background);
            gradient.addColorStop(1, this.adjustColor(theme.background, -10));
        } else {
            gradient.addColorStop(0, theme.background);
            gradient.addColorStop(1, theme.background);
        }
        
        this.ctx.fillStyle = gradient;
        this.ctx.fillRect(0, 0, this.canvasWidth, this.canvasHeight);
        
        // Add subtle pattern for texture
        this.drawPattern(theme);
    }
    
    drawPattern(theme) {
        this.ctx.save();
        this.ctx.globalAlpha = 0.05;
        this.ctx.strokeStyle = theme.text;
        this.ctx.lineWidth = 1;
        
        // Draw diagonal lines pattern
        for (let i = 0; i < this.canvasWidth + this.canvasHeight; i += 20) {
            this.ctx.beginPath();
            this.ctx.moveTo(i, 0);
            this.ctx.lineTo(i - this.canvasHeight, this.canvasHeight);
            this.ctx.stroke();
        }
        
        this.ctx.restore();
    }
    
    drawPersonaAvatar(personaInfo, theme) {
        const centerX = this.canvasWidth / 2;
        const avatarY = 80;
        
        // Draw avatar background circle
        this.ctx.save();
        this.ctx.beginPath();
        this.ctx.arc(centerX, avatarY, 40, 0, 2 * Math.PI);
        this.ctx.fillStyle = theme.accent;
        this.ctx.fill();
        this.ctx.strokeStyle = theme.border;
        this.ctx.lineWidth = 3;
        this.ctx.stroke();
        
        // Draw persona emoji
        this.ctx.font = '48px serif';
        this.ctx.textAlign = 'center';
        this.ctx.textBaseline = 'middle';
        this.ctx.fillStyle = '#ffffff';
        this.ctx.fillText(personaInfo.emoji, centerX, avatarY);
        
        // Draw persona name
        this.ctx.font = 'bold 18px sans-serif';
        this.ctx.fillStyle = theme.text;
        this.ctx.fillText(personaInfo.name, centerX, avatarY + 70);
        
        this.ctx.restore();
    }
    
    drawQuoteText(text, theme, personaInfo) {
        const centerX = this.canvasWidth / 2;
        const textY = 200;
        const maxWidth = this.canvasWidth - 80;
        
        this.ctx.save();
        
        // Quote marks
        this.ctx.font = 'bold 36px serif';
        this.ctx.fillStyle = theme.accent;
        this.ctx.textAlign = 'left';
        this.ctx.fillText('"', 50, textY - 20);
        
        // Main quote text
        this.ctx.font = '20px sans-serif';
        this.ctx.fillStyle = theme.text;
        this.ctx.textAlign = 'center';
        
        // Word wrap the text
        const words = text.split(' ');
        const lines = [];
        let currentLine = '';
        
        words.forEach(word => {
            const testLine = currentLine + word + ' ';
            const metrics = this.ctx.measureText(testLine);
            
            if (metrics.width > maxWidth && currentLine !== '') {
                lines.push(currentLine.trim());
                currentLine = word + ' ';
            } else {
                currentLine = testLine;
            }
        });
        
        if (currentLine.trim()) {
            lines.push(currentLine.trim());
        }
        
        // Draw lines
        const lineHeight = 28;
        const startY = textY - ((lines.length - 1) * lineHeight) / 2;
        
        lines.forEach((line, index) => {
            this.ctx.fillText(line, centerX, startY + (index * lineHeight));
        });
        
        // Closing quote mark
        const lastLineWidth = this.ctx.measureText(lines[lines.length - 1] || '').width;
        this.ctx.font = 'bold 36px serif';
        this.ctx.fillStyle = theme.accent;
        this.ctx.textAlign = 'left';
        this.ctx.fillText('"', centerX + lastLineWidth/2 + 10, startY + ((lines.length - 1) * lineHeight) + 10);
        
        this.ctx.restore();
    }
    
    drawFooter(theme, context) {
        const footerY = this.canvasHeight - 50;
        const centerX = this.canvasWidth / 2;
        
        this.ctx.save();
        
        // App branding
        this.ctx.font = 'bold 16px sans-serif';
        this.ctx.fillStyle = theme.accent;
        this.ctx.textAlign = 'center';
        this.ctx.fillText('🚗 TravelBot v3.0', centerX, footerY);
        
        // Location info if available
        if (context.location) {
            this.ctx.font = '12px sans-serif';
            this.ctx.fillStyle = theme.textSecondary;
            this.ctx.fillText(`📍 ${context.location}`, centerX, footerY + 20);
        }
        
        // Timestamp
        const timestamp = new Date().toLocaleDateString();
        this.ctx.font = '10px sans-serif';
        this.ctx.textAlign = 'right';
        this.ctx.fillText(timestamp, this.canvasWidth - 20, this.canvasHeight - 10);
        
        this.ctx.restore();
    }
    
    drawDecorations(theme, context) {
        // Add decorative elements based on context
        if (context.isMoving) {
            this.drawMovementIndicators(theme);
        }
        
        if (context.timeOfDay === 'night') {
            this.drawNightElements(theme);
        }
        
        // Add border
        this.ctx.save();
        this.ctx.strokeStyle = theme.border;
        this.ctx.lineWidth = 2;
        this.ctx.strokeRect(10, 10, this.canvasWidth - 20, this.canvasHeight - 20);
        this.ctx.restore();
    }
    
    drawMovementIndicators(theme) {
        // Draw subtle motion lines
        this.ctx.save();
        this.ctx.strokeStyle = theme.accent;
        this.ctx.lineWidth = 2;
        this.ctx.globalAlpha = 0.3;
        
        for (let i = 0; i < 3; i++) {
            const y = 150 + (i * 10);
            this.ctx.beginPath();
            this.ctx.moveTo(30, y);
            this.ctx.lineTo(50, y);
            this.ctx.stroke();
            
            this.ctx.beginPath();
            this.ctx.moveTo(this.canvasWidth - 50, y);
            this.ctx.lineTo(this.canvasWidth - 30, y);
            this.ctx.stroke();
        }
        
        this.ctx.restore();
    }
    
    drawNightElements(theme) {
        // Draw stars
        this.ctx.save();
        this.ctx.fillStyle = '#FFD700';
        
        const stars = [
            { x: 100, y: 50 },
            { x: 200, y: 30 },
            { x: 400, y: 60 },
            { x: 500, y: 40 }
        ];
        
        stars.forEach(star => {
            this.ctx.beginPath();
            this.ctx.arc(star.x, star.y, 2, 0, 2 * Math.PI);
            this.ctx.fill();
        });
        
        this.ctx.restore();
    }
    
    getTheme(context) {
        if (context.timeOfDay === 'night') {
            return this.themes.night;
        } else if (document.body.classList.contains('night-mode')) {
            return this.themes.dark;
        } else {
            return this.themes.light;
        }
    }
    
    getPersonaInfo(persona) {
        const personas = {
            'amsterdammer': {
                name: 'De Amsterdammer',
                emoji: '🏛️',
                color: '#FF6B35'
            },
            'belg': {
                name: 'Neerslachtige Belg',
                emoji: '🍺',
                color: '#1976D2'
            },
            'brabander': {
                name: 'Brabander',
                emoji: '🍻',
                color: '#4CAF50'
            },
            'jordanees': {
                name: 'Jordanees',
                emoji: '👑',
                color: '#FF9800'
            }
        };
        
        return personas[persona] || personas['amsterdammer'];
    }
    
    adjustColor(color, amount) {
        // Simple color adjustment utility
        const num = parseInt(color.replace("#", ""), 16);
        const amt = Math.round(2.55 * amount);
        const R = (num >> 16) + amt;
        const G = (num >> 8 & 0x00FF) + amt;
        const B = (num & 0x0000FF) + amt;
        
        return "#" + (0x1000000 + (R < 255 ? R < 1 ? 0 : R : 255) * 0x10000 +
            (G < 255 ? G < 1 ? 0 : G : 255) * 0x100 +
            (B < 255 ? B < 1 ? 0 : B : 255))
            .toString(16).slice(1);
    }
    
    showShareModal() {
        const modal = document.getElementById('share-modal');
        if (modal) {
            modal.classList.remove('hidden');
        }
    }
    
    hideShareModal() {
        const modal = document.getElementById('share-modal');
        if (modal) {
            modal.classList.add('hidden');
        }
    }
    
    downloadImage() {
        if (!this.canvas) return;
        
        // Create download link
        const link = document.createElement('a');
        link.download = `travelbot-quote-${Date.now()}.png`;
        link.href = this.canvas.toDataURL('image/png');
        
        // Trigger download
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        // Show notification
        this.showNotification('Afbeelding gedownload!');
    }
    
    generateShareLink(text, persona, context) {
        // Create shareable URL
        const params = new URLSearchParams({
            quote: encodeURIComponent(text),
            persona: persona,
            timestamp: Date.now()
        });
        
        if (context.location) {
            params.set('location', encodeURIComponent(context.location));
        }
        
        const shareUrl = `${window.location.origin}/share?${params.toString()}`;
        
        // Store for copying
        this.currentShareUrl = shareUrl;
        
        // Also create a shortened version for social media
        this.createShortenedLink(shareUrl);
    }
    
    async createShortenedLink(url) {
        // For demo purposes - in production you'd use a URL shortening service
        try {
            // Example with a hypothetical shortening service
            // const response = await fetch('/api/shorten', {
            //     method: 'POST',
            //     headers: { 'Content-Type': 'application/json' },
            //     body: JSON.stringify({ url })
            // });
            // const data = await response.json();
            // this.currentShortenedUrl = data.shortened;
            
            // For now, just use the original URL
            this.currentShortenedUrl = url;
        } catch (error) {
            console.warn('Could not create shortened link:', error);
            this.currentShortenedUrl = url;
        }
    }
    
    async copyShareLink() {
        const url = this.currentShortenedUrl || this.currentShareUrl;
        
        if (!url) {
            this.showNotification('Geen link beschikbaar');
            return;
        }
        
        try {
            await navigator.clipboard.writeText(url);
            this.showNotification('Link gekopieerd naar klembord!');
        } catch (error) {
            console.warn('Could not copy to clipboard:', error);
            
            // Fallback: show URL for manual copying
            const input = document.createElement('input');
            input.value = url;
            document.body.appendChild(input);
            input.select();
            document.execCommand('copy');
            document.body.removeChild(input);
            
            this.showNotification('Link gekopieerd!');
        }
    }
    
    // Social media sharing
    shareToSocial(platform) {
        const text = `"${this.currentQuote}" - ${this.getPersonaInfo(this.currentPersona).name}`;
        const url = this.currentShortenedUrl || this.currentShareUrl;
        
        const shareUrls = {
            twitter: `https://twitter.com/intent/tweet?text=${encodeURIComponent(text)}&url=${encodeURIComponent(url)}&hashtags=TravelBot,AI,Travel`,
            facebook: `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}&quote=${encodeURIComponent(text)}`,
            linkedin: `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(url)}&title=${encodeURIComponent('TravelBot Quote')}&summary=${encodeURIComponent(text)}`,
            whatsapp: `https://wa.me/?text=${encodeURIComponent(text + ' ' + url)}`
        };
        
        const shareUrl = shareUrls[platform];
        if (shareUrl) {
            window.open(shareUrl, '_blank', 'width=600,height=400');
        }
    }
    
    // Web Share API (mobile)
    async shareNative() {
        if (!navigator.share) {
            this.showNotification('Native sharing niet ondersteund');
            return;
        }
        
        try {
            // Convert canvas to blob for sharing
            this.canvas.toBlob(async (blob) => {
                const file = new File([blob], 'travelbot-quote.png', { type: 'image/png' });
                
                const shareData = {
                    title: 'TravelBot Quote',
                    text: `"${this.currentQuote}" - ${this.getPersonaInfo(this.currentPersona).name}`,
                    files: [file]
                };
                
                if (navigator.canShare && navigator.canShare(shareData)) {
                    await navigator.share(shareData);
                    this.showNotification('Quote gedeeld!');
                } else {
                    // Fallback without file
                    await navigator.share({
                        title: shareData.title,
                        text: shareData.text,
                        url: this.currentShareUrl
                    });
                }
            }, 'image/png');
            
        } catch (error) {
            console.warn('Native sharing failed:', error);
            this.showNotification('Delen mislukt');
        }
    }
    
    showNotification(message) {
        const notification = document.createElement('div');
        notification.className = 'share-notification';
        notification.textContent = message;
        notification.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: var(--success-color);
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            z-index: 2000;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            animation: slideInUp 0.3s ease;
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.animation = 'slideOutDown 0.3s ease';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
    
    // Settings
    saveSettings() {
        const settings = {
            defaultTheme: this.defaultTheme,
            canvasSize: { width: this.canvasWidth, height: this.canvasHeight }
        };
        
        localStorage.setItem('travelbot-share-settings', JSON.stringify(settings));
    }
    
    loadSettings() {
        const saved = localStorage.getItem('travelbot-share-settings');
        if (saved) {
            try {
                const settings = JSON.parse(saved);
                // Apply loaded settings
                if (settings.canvasSize) {
                    this.canvasWidth = settings.canvasSize.width;
                    this.canvasHeight = settings.canvasSize.height;
                }
            } catch (error) {
                console.warn('Could not load share settings:', error);
            }
        }
    }
}

// Static share page handler for direct links
class SharePageHandler {
    static init() {
        const urlParams = new URLSearchParams(window.location.search);
        const quote = urlParams.get('quote');
        const persona = urlParams.get('persona');
        const location = urlParams.get('location');
        
        if (quote && persona) {
            SharePageHandler.displaySharedQuote(
                decodeURIComponent(quote),
                persona,
                location ? decodeURIComponent(location) : null
            );
        }
    }
    
    static displaySharedQuote(quote, persona, location) {
        // Create a simplified display for shared quotes
        document.body.innerHTML = `
            <div class="shared-quote-container">
                <div class="shared-quote-header">
                    <h1>🚗 TravelBot Quote</h1>
                </div>
                <div class="shared-quote-content">
                    <div class="persona-info">
                        ${SharePageHandler.getPersonaEmoji(persona)} ${SharePageHandler.getPersonaName(persona)}
                    </div>
                    <blockquote class="quote-text">"${quote}"</blockquote>
                    ${location ? `<div class="quote-location">📍 ${location}</div>` : ''}
                </div>
                <div class="shared-quote-footer">
                    <a href="/">Try TravelBot yourself</a>
                </div>
            </div>
        `;
        
        // Add meta tags for social media
        SharePageHandler.updateMetaTags(quote, persona, location);
    }
    
    static updateMetaTags(quote, persona, location) {
        const title = `TravelBot Quote from ${SharePageHandler.getPersonaName(persona)}`;
        const description = `"${quote}"${location ? ` - ${location}` : ''}`;
        
        // Update page title
        document.title = title;
        
        // Update meta tags
        SharePageHandler.setMetaTag('description', description);
        SharePageHandler.setMetaTag('og:title', title);
        SharePageHandler.setMetaTag('og:description', description);
        SharePageHandler.setMetaTag('twitter:card', 'summary');
        SharePageHandler.setMetaTag('twitter:title', title);
        SharePageHandler.setMetaTag('twitter:description', description);
    }
    
    static setMetaTag(name, content) {
        let tag = document.querySelector(`meta[name="${name}"], meta[property="${name}"]`);
        if (!tag) {
            tag = document.createElement('meta');
            tag.setAttribute(name.startsWith('og:') ? 'property' : 'name', name);
            document.head.appendChild(tag);
        }
        tag.setAttribute('content', content);
    }
    
    static getPersonaEmoji(persona) {
        const emojis = {
            'amsterdammer': '🏛️',
            'belg': '🍺',
            'brabander': '🍻',
            'jordanees': '👑'
        };
        return emojis[persona] || '🤖';
    }
    
    static getPersonaName(persona) {
        const names = {
            'amsterdammer': 'De Amsterdammer',
            'belg': 'Neerslachtige Belg',
            'brabander': 'Brabander',
            'jordanees': 'Jordanees'
        };
        return names[persona] || persona;
    }
}

// Initialize based on context
document.addEventListener('DOMContentLoaded', () => {
    if (window.location.search.includes('quote=')) {
        // This is a shared quote page
        SharePageHandler.init();
    } else {
        // This is the main app
        window.shareManager = new ShareManager();
    }
});

// Export for other modules
window.ShareManager = ShareManager;
