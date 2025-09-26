/**
 * Audio Notifications System
 * Plays success or error sounds for user interactions
 * Handles modern browser autoplay policies
 */

class AudioNotifications {
    constructor() {
        this.audioPath = '/audio/';
        this.successSound = null;
        this.errorSound = null;
        this.isEnabled = true;
        this.volume = 0.7;
        this.audioContext = null;
        this.userInteracted = false;
        this.sounds = {};
        
        // Initialize audio system
        this.initializeAudioSystem();
        
        // Listen for first user interaction
        this.enableAudioOnFirstInteraction();
    }

    /**
     * Initialize the audio system with modern browser support
     */
    initializeAudioSystem() {
        try {
            // Check if Web Audio API is supported
            window.AudioContext = window.AudioContext || window.webkitAudioContext;
            
            if (window.AudioContext) {
                this.audioContext = new AudioContext();
                console.log('🎵 Web Audio API initialized');
            }
            
            // Preload audio files
            this.preloadSounds();
            
            // Check browser audio capabilities
            this.checkAudioSupport();
            
        } catch (error) {
            console.warn('⚠️ Audio system initialization failed:', error);
            // Fallback to basic audio
            this.preloadSounds();
        }
    }
    
    /**
     * Check browser audio support and format compatibility
     */
    checkAudioSupport() {
        const audio = new Audio();
        const formats = {
            mp3: audio.canPlayType('audio/mpeg'),
            ogg: audio.canPlayType('audio/ogg'),
            wav: audio.canPlayType('audio/wav')
        };
        
        console.log('🎵 Audio format support:', formats);
        
        // Use the best supported format
        if (formats.mp3) {
            this.audioFormat = 'mp3';
        } else if (formats.ogg) {
            this.audioFormat = 'ogg';
        } else {
            console.warn('⚠️ No supported audio formats found');
            this.isEnabled = false;
        }
    }
    
    /**
     * Enable audio on first user interaction (required by browsers)
     */
    enableAudioOnFirstInteraction() {
        const enableAudio = () => {
            if (this.userInteracted) return;
            
            this.userInteracted = true;
            console.log('👆 User interaction detected - enabling audio');
            
            // Resume audio context if suspended
            if (this.audioContext && this.audioContext.state === 'suspended') {
                this.audioContext.resume().then(() => {
                    console.log('🎵 Audio context resumed');
                }).catch(error => {
                    console.warn('⚠️ Failed to resume audio context:', error);
                });
            }
            
            // Test play a silent sound to "unlock" audio
            this.unlockAudio();
            
            // Remove event listeners after first interaction
            document.removeEventListener('click', enableAudio);
            document.removeEventListener('touchstart', enableAudio);
            document.removeEventListener('keydown', enableAudio);
        };
        
        // Listen for various user interactions
        document.addEventListener('click', enableAudio);
        document.addEventListener('touchstart', enableAudio);
        document.addEventListener('keydown', enableAudio);
    }
    
    /**
     * Unlock audio by playing silent sounds (browser requirement)
     */
    unlockAudio() {
        if (!this.successSound || !this.errorSound) return;
        
        try {
            // Create silent play promises
            const unlockSuccess = this.successSound.play();
            const unlockError = this.errorSound.play();
            
            // Immediately pause to make it silent
            Promise.all([unlockSuccess, unlockError]).then(() => {
                this.successSound.pause();
                this.errorSound.pause();
                this.successSound.currentTime = 0;
                this.errorSound.currentTime = 0;
                console.log('🔓 Audio unlocked successfully');
            }).catch(error => {
                console.log('🔇 Audio unlock failed (this is normal on some browsers):', error);
            });
        } catch (error) {
            console.log('🔇 Audio unlock error:', error);
        }
    }
    
    /**
     * Enhanced audio loading with multiple format support
     */
    loadAudioFile(name) {
        const formats = ['mp3', 'ogg'];
        let audio = null;
        
        for (const format of formats) {
            try {
                audio = new Audio(`${this.audioPath}${name}.${format}`);
                audio.preload = 'auto';
                audio.volume = this.volume;
                
                // Test if this format can be played
                if (audio.canPlayType(`audio/${format === 'ogg' ? 'ogg' : 'mpeg'}`)) {
                    console.log(`✅ Loaded ${name}.${format}`);
                    break;
                }
            } catch (error) {
                console.warn(`⚠️ Failed to load ${name}.${format}:`, error);
                continue;
            }
        }
        
        return audio;
    }

    /**
     * Preload audio files for better performance
     */
    preloadSounds() {
        try {
            this.successSound = new Audio(this.audioPath + 'success.mp3');
            this.errorSound = new Audio(this.audioPath + 'error.mp3');
            
            // Set volume
            this.successSound.volume = this.volume;
            this.errorSound.volume = this.volume;
            
            // Preload the audio files
            this.successSound.preload = 'auto';
            this.errorSound.preload = 'auto';
            
            console.log('✅ Audio notifications initialized successfully');
        } catch (error) {
            console.warn('⚠️ Audio notifications could not be initialized:', error);
            this.isEnabled = false;
        }
    }

    /**
     * Play success sound with enhanced compatibility
     */
    playSuccess() {
        return this.playSound('success', this.successSound);
    }

    /**
     * Play error sound with enhanced compatibility
     */
    playError() {
        return this.playSound('error', this.errorSound);
    }
    
    /**
     * Enhanced audio playback with improved browser compatibility
     */
    playSound(soundName, audioElement) {
        if (!this.isEnabled || !audioElement) {
            console.log(`🔇 ${soundName} sound not available`);
            return Promise.reject(new Error(`${soundName} sound not available`));
        }
        
        // Check if user has interacted (required by modern browsers)
        if (!this.userInteracted) {
            console.warn(`🚫 Cannot play ${soundName} sound - user interaction required`);
            return Promise.reject(new Error('User interaction required'));
        }
        
        return new Promise((resolve, reject) => {
            try {
                // Reset audio to beginning
                audioElement.currentTime = 0;
                audioElement.volume = this.volume;
                
                // Create play promise
                const playPromise = audioElement.play();
                
                if (playPromise !== undefined) {
                    playPromise.then(() => {
                        const emoji = soundName === 'success' ? '✅' : '❌';
                        console.log(`${emoji} ${soundName} sound played successfully`);
                        resolve();
                    }).catch(error => {
                        console.error(`🔇 ${soundName} sound playback failed:`, error);
                        
                        // Try alternative playback method
                        this.tryAlternativePlayback(soundName, audioElement)
                            .then(resolve)
                            .catch(reject);
                    });
                } else {
                    // For older browsers that don't return a promise
                    const emoji = soundName === 'success' ? '✅' : '❌';
                    console.log(`${emoji} ${soundName} sound played (legacy mode)`);
                    resolve();
                }
            } catch (error) {
                console.error(`🔇 Error playing ${soundName} sound:`, error);
                reject(error);
            }
        });
    }
    
    /**
     * Try alternative playback methods if standard playback fails
     */
    tryAlternativePlayback(soundName, audioElement) {
        return new Promise((resolve, reject) => {
            console.log(`🔄 Trying alternative playback for ${soundName} sound`);
            
            // Method 1: Clone the audio element
            try {
                const clonedAudio = audioElement.cloneNode();
                clonedAudio.volume = this.volume;
                
                const playPromise = clonedAudio.play();
                if (playPromise !== undefined) {
                    playPromise.then(() => {
                        console.log(`✨ ${soundName} sound played via clone method`);
                        resolve();
                    }).catch(() => {
                        // Method 2: Create new audio element
                        this.tryNewAudioElement(soundName)
                            .then(resolve)
                            .catch(reject);
                    });
                } else {
                    resolve();
                }
            } catch (error) {
                // Method 2: Create new audio element
                this.tryNewAudioElement(soundName)
                    .then(resolve)
                    .catch(reject);
            }
        });
    }
    
    /**
     * Try creating a new audio element for playback
     */
    tryNewAudioElement(soundName) {
        return new Promise((resolve, reject) => {
            try {
                const audio = new Audio(`${this.audioPath}${soundName}.mp3`);
                audio.volume = this.volume;
                
                const playPromise = audio.play();
                if (playPromise !== undefined) {
                    playPromise.then(() => {
                        console.log(`🆕 ${soundName} sound played via new element`);
                        resolve();
                    }).catch(reject);
                } else {
                    resolve();
                }
            } catch (error) {
                reject(error);
            }
        });
    }

    /**
     * Set volume (0.0 to 1.0)
     */
    setVolume(volume) {
        this.volume = Math.max(0, Math.min(1, volume));
        
        if (this.successSound) {
            this.successSound.volume = this.volume;
        }
        
        if (this.errorSound) {
            this.errorSound.volume = this.volume;
        }
    }

    /**
     * Enable/disable audio notifications
     */
    setEnabled(enabled) {
        this.isEnabled = enabled;
        console.log(`🔊 Audio notifications ${enabled ? 'enabled' : 'disabled'}`);
    }

    /**
     * Play success sound with delay for notifications
     * @param {number} delay - Delay in milliseconds (default: 3000)
     */
    playSuccessDelayed(delay = 3000) {
        console.log(`🔊 Success sound scheduled to play in ${delay}ms`);
        return new Promise((resolve, reject) => {
            setTimeout(() => {
                console.log('🎵 Playing delayed success sound');
                this.playSuccess()
                    .then(() => {
                        console.log('✅ Delayed success sound completed');
                        resolve();
                    })
                    .catch(error => {
                        console.error('❌ Delayed success sound failed:', error);
                        reject(error);
                    });
            }, delay);
        });
    }

    /**
     * Play error sound with delay for notifications
     * @param {number} delay - Delay in milliseconds (default: 3000)
     */
    playErrorDelayed(delay = 3000) {
        console.log(`🔊 Error sound scheduled to play in ${delay}ms`);
        return new Promise((resolve, reject) => {
            setTimeout(() => {
                console.log('🎵 Playing delayed error sound');
                this.playError()
                    .then(() => {
                        console.log('✅ Delayed error sound completed');
                        resolve();
                    })
                    .catch(error => {
                        console.error('❌ Delayed error sound failed:', error);
                        reject(error);
                    });
            }, delay);
        });
    }

    /**
     * Play notification sound based on detected notification type
     * Automatically detects success/error from page notifications after delay
     * @param {number} delay - Delay in milliseconds to wait for notifications (default: 3000)
     */
    playNotificationSoundAfterDelay(delay = 3000) {
        console.log(`🔍 Checking for notifications in ${delay}ms...`);
        
        setTimeout(() => {
            // Check for toastr notifications
            const toastrSuccess = document.querySelector('.toast-success, .toastr-success, [class*="toast-success"]');
            const toastrError = document.querySelector('.toast-error, .toastr-error, [class*="toast-error"]');
            
            // Check for alert notifications
            const alertSuccess = document.querySelector('.alert-success:not(.d-none)');
            const alertError = document.querySelector('.alert-danger:not(.d-none), .alert-error:not(.d-none)');
            
            // Check for SweetAlert notifications
            const sweetSuccess = document.querySelector('.swal2-success');
            const sweetError = document.querySelector('.swal2-error');
            
            if (toastrSuccess || alertSuccess || sweetSuccess) {
                console.log('✅ Success notification detected - playing success sound');
                this.playSuccess().catch(error => {
                    console.error('Failed to play success sound:', error);
                });
            } else if (toastrError || alertError || sweetError) {
                console.log('❌ Error notification detected - playing error sound');
                this.playError().catch(error => {
                    console.error('Failed to play error sound:', error);
                });
            } else {
                console.log('ℹ️ No notifications detected - no sound played');
            }
        }, delay);
    }

    /**
     * Test audio playbook
     */
    test() {
        console.log('🔊 Testing audio notifications...');
        console.log('User interacted:', this.userInteracted);
        console.log('Audio enabled:', this.isEnabled);
        
        if (!this.userInteracted) {
            console.warn('🚫 Please click on the page first to enable audio, then run the test again');
            return;
        }
        
        setTimeout(() => {
            console.log('🔊 Playing success sound...');
            this.playSuccess().catch(error => {
                console.error('Success sound test failed:', error);
            });
        }, 500);
        
        setTimeout(() => {
            console.log('🔊 Playing error sound...');
            this.playError().catch(error => {
                console.error('Error sound test failed:', error);
            });
        }, 1500);
    }
    
    /**
     * Force enable audio (for testing purposes)
     */
    forceEnable() {
        console.log('🔥 Force enabling audio...');
        this.userInteracted = true;
        
        if (this.audioContext && this.audioContext.state === 'suspended') {
            this.audioContext.resume().then(() => {
                console.log('🎵 Audio context force resumed');
                this.unlockAudio();
            });
        } else {
            this.unlockAudio();
        }
    }
    
    /**
     * Get comprehensive audio system status
     */
    getStatus() {
        return {
            enabled: this.isEnabled,
            userInteracted: this.userInteracted,
            volume: this.volume,
            audioContextState: this.audioContext ? this.audioContext.state : 'not supported',
            successSoundLoaded: !!this.successSound,
            errorSoundLoaded: !!this.errorSound,
            successSoundSrc: this.successSound ? this.successSound.src : null,
            errorSoundSrc: this.errorSound ? this.errorSound.src : null,
            successSoundReadyState: this.successSound ? this.successSound.readyState : null,
            errorSoundReadyState: this.errorSound ? this.errorSound.readyState : null
        };
    }
    
    /**
     * Comprehensive diagnostic information
     */
    diagnose() {
        console.log('🕵️ Audio System Diagnostic Report');
        console.log('=====================================');
        
        const status = this.getStatus();
        console.table(status);
        
        // Browser compatibility
        console.log('🌍 Browser Compatibility:');
        console.log('- Audio API supported:', 'Audio' in window);
        console.log('- Web Audio API supported:', 'AudioContext' in window || 'webkitAudioContext' in window);
        console.log('- Promise support:', 'Promise' in window);
        
        // Audio format support
        console.log('🎵 Audio Format Support:');
        const audio = new Audio();
        const formats = {
            'MP3': audio.canPlayType('audio/mpeg'),
            'OGG': audio.canPlayType('audio/ogg'), 
            'WAV': audio.canPlayType('audio/wav'),
            'M4A': audio.canPlayType('audio/mp4')
        };
        console.table(formats);
        
        // Recommendations
        console.log('💡 Recommendations:');
        if (!status.userInteracted) {
            console.warn('- User interaction required! Click anywhere on the page to enable audio.');
        }
        if (!status.enabled) {
            console.warn('- Audio notifications are disabled.');
        }
        if (status.audioContextState === 'suspended') {
            console.warn('- Audio context is suspended. Try calling forceEnable().');
        }
        if (!status.successSoundLoaded || !status.errorSoundLoaded) {
            console.warn('- Some audio files failed to load. Check network and file paths.');
        }
        
        return status;
    }
}

// Create global instance
window.audioNotifications = new AudioNotifications();

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AudioNotifications;
}
