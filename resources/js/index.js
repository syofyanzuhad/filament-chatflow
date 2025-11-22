// Filament Chatflow JavaScript
// Main entry point for the plugin's JavaScript functionality

export default function filamentChatflow() {
    return {
        // Plugin initialization
        init() {
            console.log('Filament Chatflow initialized');
        },

        // Sound notification helper
        playNotificationSound(soundUrl) {
            if (!soundUrl) return;

            const audio = new Audio(soundUrl);
            audio.volume = 0.5;
            audio.play().catch(err => {
                console.warn('Unable to play notification sound:', err);
            });
        },

        // Local storage helpers for chat widget
        storage: {
            get(key, defaultValue = null) {
                try {
                    const item = localStorage.getItem(`chatflow_${key}`);
                    return item ? JSON.parse(item) : defaultValue;
                } catch (error) {
                    console.error('Error reading from localStorage:', error);
                    return defaultValue;
                }
            },

            set(key, value) {
                try {
                    localStorage.setItem(`chatflow_${key}`, JSON.stringify(value));
                } catch (error) {
                    console.error('Error writing to localStorage:', error);
                }
            },

            remove(key) {
                try {
                    localStorage.removeItem(`chatflow_${key}`);
                } catch (error) {
                    console.error('Error removing from localStorage:', error);
                }
            }
        }
    };
}

// Auto-initialize if Alpine is available
if (typeof window !== 'undefined') {
    window.filamentChatflow = filamentChatflow;

    // Register as Alpine.js component if Alpine is loaded
    document.addEventListener('alpine:init', () => {
        if (window.Alpine) {
            window.Alpine.data('filamentChatflow', filamentChatflow);
        }
    });
}
