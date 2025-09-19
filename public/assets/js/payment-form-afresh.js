/**
 * Afresh Payment Form - Enhanced Interactive JavaScript Module
 * Features: Advanced validation, particle effects, haptic feedback, progressive enhancement
 */

// Enhanced Audio System with Multiple Sound Types
window.afreshAudio = {
    isEnabled: true,
    context: null,
    
    init() {
        try {
            this.context = new (window.AudioContext || window.webkitAudioContext)();
            console.log('Afresh Audio System initialized');
        } catch (error) {
            console.warn('Audio context not supported:', error);
            this.isEnabled = false;
        }
    },
    
    playTone(frequency, duration = 0.4, type = 'sine', volume = 0.3) {
        if (!this.isEnabled || !this.context) return;
        
        try {
            const oscillator = this.context.createOscillator();
            const gainNode = this.context.createGain();
            
            oscillator.connect(gainNode);
            gainNode.connect(this.context.destination);
            
            oscillator.frequency.setValueAtTime(frequency, this.context.currentTime);
            oscillator.type = type;
            
            gainNode.gain.setValueAtTime(volume, this.context.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, this.context.currentTime + duration);
            
            oscillator.start(this.context.currentTime);
            oscillator.stop(this.context.currentTime + duration);
        } catch (error) {
            console.warn('Audio playback failed:', error);
        }
    },
    
    playSuccess() {
        this.playTone(800, 0.2);
        setTimeout(() => this.playTone(1000, 0.2), 100);
        setTimeout(() => this.playTone(1200, 0.3), 200);
    },
    
    playError() {
        this.playTone(400, 0.2);
        setTimeout(() => this.playTone(300, 0.3), 150);
    },
    
    playClick() {
        this.playTone(600, 0.1, 'square', 0.2);
    },
    
    playNotification() {
        this.playTone(880, 0.15);
        setTimeout(() => this.playTone(1100, 0.15), 200);
        setTimeout(() => this.playTone(880, 0.2), 400);
    }
};

// Enhanced Notification System
window.afreshNotifications = {
    container: null,
    
    init() {
        this.createContainer();
    },
    
    createContainer() {
        if (this.container) return;
        
        this.container = document.createElement('div');
        this.container.id = 'afresh-notifications';
        this.container.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000;
            pointer-events: none;
        `;
        document.body.appendChild(this.container);
    },
    
    show(message, type = 'info', duration = 4000) {
        const notification = document.createElement('div');
        const notificationId = 'notification-' + Date.now();
        
        const icons = {
            success: 'ri-check-fill',
            error: 'ri-error-warning-fill', 
            warning: 'ri-alert-fill',
            info: 'ri-information-fill'
        };
        
        const colors = {
            success: 'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)',
            error: 'linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%)',
            warning: 'linear-gradient(135deg, #feca57 0%, #ff9ff3 100%)',
            info: 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)'
        };
        
        notification.id = notificationId;
        notification.style.cssText = `
            background: ${colors[type] || colors.info};
            color: white;
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 12px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.3);
            backdrop-filter: blur(10px);
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 600;
            font-size: 14px;
            max-width: 350px;
            pointer-events: auto;
            transform: translateX(400px);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid rgba(255,255,255,0.2);
        `;
        
        notification.innerHTML = `
            <i class="${icons[type] || icons.info}" style="font-size: 18px;"></i>
            <span>${message}</span>
            <button onclick="afreshNotifications.hide('${notificationId}')" 
                    style="background: none; border: none; color: white; margin-left: auto; cursor: pointer; padding: 4px; border-radius: 50%; transition: background 0.2s;">
                <i class="ri-close-line"></i>
            </button>
        `;
        
        this.container.appendChild(notification);
        
        // Trigger slide-in animation
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 50);
        
        // Auto-hide after duration
        setTimeout(() => {
            this.hide(notificationId);
        }, duration);
        
        return notificationId;
    },
    
    hide(notificationId) {
        const notification = document.getElementById(notificationId);
        if (notification) {
            notification.style.transform = 'translateX(400px)';
            notification.style.opacity = '0';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 400);
        }
    }
};

// Particle System for Visual Effects
window.afreshParticles = {
    createCelebration(x, y) {
        const colors = ['#667eea', '#764ba2', '#43e97b', '#38f9d7', '#4facfe', '#00f2fe'];
        
        for (let i = 0; i < 12; i++) {
            setTimeout(() => {
                this.createParticle(x, y, colors[i % colors.length]);
            }, i * 50);
        }
    },
    
    createParticle(x, y, color) {
        const particle = document.createElement('div');
        particle.style.cssText = `
            position: fixed;
            width: 8px;
            height: 8px;
            background: ${color};
            border-radius: 50%;
            pointer-events: none;
            z-index: 10000;
            left: ${x}px;
            top: ${y}px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        `;
        
        document.body.appendChild(particle);
        
        const angle = (Math.PI * 2 * Math.random());
        const velocity = 50 + Math.random() * 100;
        const gravity = 0.5;
        const life = 2000 + Math.random() * 1000;
        
        let vx = Math.cos(angle) * velocity;
        let vy = Math.sin(angle) * velocity;
        let currentX = x;
        let currentY = y;
        
        const animate = () => {
            currentX += vx * 0.016;
            currentY += vy * 0.016;
            vy += gravity;
            
            particle.style.left = currentX + 'px';
            particle.style.top = currentY + 'px';
            particle.style.opacity = Math.max(0, 1 - (Date.now() - startTime) / life);
            
            if (Date.now() - startTime < life) {
                requestAnimationFrame(animate);
            } else {
                document.body.removeChild(particle);
            }
        };
        
        const startTime = Date.now();
        requestAnimationFrame(animate);
    }
};

// Haptic Feedback System
window.afreshHaptics = {
    isSupported: 'vibrate' in navigator,
    
    light() {
        if (this.isSupported) {
            navigator.vibrate(10);
        }
    },
    
    medium() {
        if (this.isSupported) {
            navigator.vibrate([20, 10, 20]);
        }
    },
    
    success() {
        if (this.isSupported) {
            navigator.vibrate([30, 10, 30, 10, 30]);
        }
    },
    
    error() {
        if (this.isSupported) {
            navigator.vibrate([50, 20, 50, 20, 100]);
        }
    }
};

// Enhanced Form Validation with Real-time Feedback
window.afreshValidation = {
    patterns: {
        transactionId: /^[A-Za-z0-9\s\-_.]{4,30}$/,
        phone: /^254[0-9]{9}$/,
        amount: /^\d+(\.\d{1,2})?$/
    },
    
    validateField(field, showFeedback = true) {
        const container = field.closest('.input-container');
        const validationMessage = container.querySelector('.validation-message');
        const value = field.value.trim();
        const fieldType = this.getFieldType(field);
        
        let isValid = true;
        let message = '';
        
        // Check if field is required and empty
        if (field.hasAttribute('required') && !value) {
            isValid = false;
            message = 'This field is required';
        }
        // Validate specific field types
        else if (value && fieldType) {
            switch (fieldType) {
                case 'transaction':
                    if (!this.patterns.transactionId.test(value)) {
                        isValid = false;
                        message = 'Invalid transaction ID format (4-30 characters, letters/numbers only)';
                    } else {
                        message = 'Valid transaction ID format';
                    }
                    break;
                case 'phone':
                    if (!this.patterns.phone.test(value)) {
                        isValid = false;
                        message = 'Invalid phone number format';
                    } else {
                        message = 'Valid phone number';
                    }
                    break;
                case 'amount':
                    const amount = parseFloat(value);
                    if (isNaN(amount) || amount <= 0) {
                        isValid = false;
                        message = 'Amount must be greater than 0';
                    } else {
                        message = 'Valid amount';
                    }
                    break;
            }
        }
        
        // Update visual state
        this.updateFieldState(container, field, isValid, message, showFeedback);
        
        return isValid;
    },
    
    getFieldType(field) {
        if (field.classList.contains('transaction-input') || field.name === 'txs_id') {
            return 'transaction';
        }
        if (field.type === 'tel' || field.name.includes('phone') || field.name.includes('number')) {
            return 'phone';
        }
        if (field.type === 'number' || field.name === 'amount') {
            return 'amount';
        }
        return null;
    },
    
    updateFieldState(container, field, isValid, message, showFeedback) {
        const validationMessage = container.querySelector('.validation-message');
        
        // Update container classes
        container.classList.remove('valid', 'invalid');
        field.classList.remove('is-valid', 'is-invalid');
        
        if (field.value.trim()) {
            if (isValid) {
                container.classList.add('valid');
                field.classList.add('is-valid');
            } else {
                container.classList.add('invalid');
                field.classList.add('is-invalid');
            }
        }
        
        // Update validation message
        if (showFeedback && validationMessage && message) {
            validationMessage.className = 'validation-message show ' + (isValid ? 'success' : 'error');
            validationMessage.innerHTML = `
                <i class="${isValid ? 'ri-check-fill' : 'ri-error-warning-fill'}"></i>
                ${message}
            `;
        }
    }
};

// Enhanced Animation System
window.afreshAnimations = {
    morphButton(button, newContent, duration = 400) {
        return new Promise(resolve => {
            const originalContent = button.innerHTML;
            const originalWidth = button.offsetWidth;
            
            // Store original for restoration
            if (!button.dataset.originalContent) {
                button.dataset.originalContent = originalContent;
                button.dataset.originalWidth = originalWidth + 'px';
            }
            
            // Animate out
            button.style.transition = `all ${duration/2}ms cubic-bezier(0.4, 0, 0.2, 1)`;
            button.style.transform = 'scale(0.95)';
            button.style.opacity = '0.7';
            
            setTimeout(() => {
                button.innerHTML = newContent;
                button.style.transform = 'scale(1)';
                button.style.opacity = '1';
                setTimeout(resolve, duration/2);
            }, duration/2);
        });
    },
    
    restoreButton(button, duration = 400) {
        if (button.dataset.originalContent) {
            return this.morphButton(button, button.dataset.originalContent, duration);
        }
        return Promise.resolve();
    },
    
    showFloatingSuccess(userId) {
        const floating = document.getElementById(`afreshSuccessAnimation${userId}`);
        if (!floating) return;
        
        floating.classList.remove('d-none');
        
        // Auto-hide after animation
        setTimeout(() => {
            floating.style.opacity = '0';
            floating.style.transform = 'translate(-50%, -50%) scale(0.5)';
            setTimeout(() => {
                floating.classList.add('d-none');
                floating.style.opacity = '';
                floating.style.transform = '';
            }, 500);
        }, 3000);
    },
    
    pulseElement(element, intensity = 1.1) {
        const originalTransform = element.style.transform;
        element.style.transition = 'transform 0.15s ease-out';
        element.style.transform = `scale(${intensity})`;
        
        setTimeout(() => {
            element.style.transform = originalTransform;
            setTimeout(() => {
                element.style.transition = '';
            }, 150);
        }, 150);
    }
};

// Enhanced Loading System
window.afreshLoading = {
    show(form, button) {
        // Add loading class to form
        form.classList.add('loading');
        
        // Morph submit button
        const loadingContent = `
            <div class="btn-loading">
                <div class="loading-spinner"></div>
                <span>Processing...</span>
            </div>
        `;
        
        window.afreshAnimations.morphButton(button, loadingContent);
        button.disabled = true;
        
        // Add particle effect at button position
        const rect = button.getBoundingClientRect();
        window.afreshParticles.createCelebration(
            rect.left + rect.width / 2,
            rect.top + rect.height / 2
        );
    },
    
    hide(form, button) {
        form.classList.remove('loading');
        window.afreshAnimations.restoreButton(button);
        button.disabled = false;
    }
};

/**
 * Main form submission function with enhanced features
 * @param {string} formId - The form ID
 * @param {HTMLElement} buttonElement - The submit button
 */
function submitAfreshPaymentForm(formId, buttonElement) {
    console.log('🚀 Afresh Payment Form Submission Started');
    console.log('Form ID:', formId);
    
    // Initialize audio if not done
    if (!window.afreshAudio.context) {
        window.afreshAudio.init();
    }
    
    // Play interaction sound
    window.afreshAudio.playClick();
    window.afreshHaptics.light();
    
    // Find form
    const form = findAfreshForm(formId, buttonElement);
    if (!form) {
        window.afreshNotifications.show('Form not found. Please refresh and try again.', 'error');
        window.afreshAudio.playError();
        window.afreshHaptics.error();
        return false;
    }
    
    // Enhanced validation process
    const validationResult = validateAfreshForm(form);
    if (!validationResult.isValid) {
        window.afreshNotifications.show(validationResult.message, 'error');
        window.afreshAudio.playError();
        window.afreshHaptics.error();
        
        if (validationResult.field) {
            focusFieldWithAnimation(validationResult.field);
        }
        return false;
    }
    
    // Success validation feedback
    window.afreshAudio.playSuccess();
    window.afreshHaptics.success();
    window.afreshNotifications.show('Validation successful!', 'success', 2000);
    
    // User confirmation with enhanced dialog
    const confirmResult = showEnhancedConfirmation(form);
    if (!confirmResult) {
        return false;
    }
    
    // Submit with enhanced loading
    return submitWithEnhancedFeedback(form, buttonElement);
}

/**
 * Find form with multiple fallback methods
 * @param {string} formId - The form ID
 * @param {HTMLElement} buttonElement - The submit button
 * @returns {HTMLElement|null} The form element
 */
function findAfreshForm(formId, buttonElement) {
    // Method 1: Direct ID lookup
    let form = document.getElementById(formId);
    
    // Method 2: Find via button context
    if (!form && buttonElement) {
        const modal = buttonElement.closest('.afresh-payment-modal');
        if (modal) {
            form = modal.querySelector('.afresh-payment-form');
        }
    }
    
    // Method 3: Look for any afresh payment form
    if (!form) {
        form = document.querySelector('.afresh-payment-form');
    }
    
    return form;
}

/**
 * Enhanced form validation with detailed feedback
 * @param {HTMLElement} form - The form element
 * @returns {Object} Validation result object
 */
function validateAfreshForm(form) {
    const result = {
        isValid: true,
        message: '',
        field: null
    };
    
    // Validate all fields with real-time feedback
    const fields = form.querySelectorAll('.form-input[required], .transaction-input');
    let firstInvalidField = null;
    
    fields.forEach(field => {
        const isFieldValid = window.afreshValidation.validateField(field, true);
        if (!isFieldValid && !firstInvalidField) {
            firstInvalidField = field;
        }
        result.isValid = result.isValid && isFieldValid;
    });
    
    // Specific validation checks
    if (!result.isValid) {
        result.message = 'Please correct the highlighted fields';
        result.field = firstInvalidField;
        return result;
    }
    
    // Validate transaction ID specifically
    const txField = form.querySelector('[name="txs_id"]');
    if (txField && txField.hasAttribute('required')) {
        const txValue = txField.value.trim();
        if (!txValue) {
            result.isValid = false;
            result.message = 'Transaction ID is required';
            result.field = txField;
            return result;
        }
        
        if (!window.afreshValidation.patterns.transactionId.test(txValue)) {
            result.isValid = false;
            result.message = 'Invalid transaction ID format';
            result.field = txField;
            return result;
        }
    }
    
    // Validate amount
    const amountField = form.querySelector('[name="amount"]');
    if (amountField) {
        const amount = parseFloat(amountField.value);
        if (isNaN(amount) || amount <= 0) {
            result.isValid = false;
            result.message = 'Invalid payment amount';
            result.field = amountField;
            return result;
        }
    }
    
    // Validate required hidden fields
    const requiredHiddenFields = ['receiver_id', 'sender_id', 'user_share_id'];
    for (const fieldName of requiredHiddenFields) {
        const field = form.querySelector(`[name="${fieldName}"]`);
        if (!field || !field.value) {
            result.isValid = false;
            result.message = 'Missing transaction data. Please refresh and try again.';
            return result;
        }
    }
    
    result.message = 'All validations passed successfully';
    return result;
}

/**
 * Focus field with smooth animation
 * @param {HTMLElement} field - The field to focus
 */
function focusFieldWithAnimation(field) {
    if (!field) return;
    
    // Scroll to field
    field.scrollIntoView({ 
        behavior: 'smooth', 
        block: 'center' 
    });
    
    // Animate attention
    setTimeout(() => {
        window.afreshAnimations.pulseElement(field.closest('.input-container'), 1.05);
        field.focus();
    }, 300);
}

/**
 * Show enhanced confirmation dialog
 * @param {HTMLElement} form - The form element
 * @returns {boolean} User confirmation result
 */
function showEnhancedConfirmation(form) {
    const amountField = form.querySelector('[name="amount"]');
    const amount = amountField ? amountField.value : '0';
    
    // Confirmation dialog DISABLED - always return true
    return true;
    
    // return confirm(
    //     `🔒 Confirm Payment Submission\n\n` +
    //     `Amount: KES ${amount}\n` +
    //     `Are you sure you want to submit this payment information?\n\n` +
    //     `⚠️ Make sure you have completed the M-Pesa payment before confirming.`
    // );
}

/**
 * Submit form with enhanced feedback and animations
 * @param {HTMLElement} form - The form element
 * @param {HTMLElement} buttonElement - The submit button
 * @returns {boolean} Submission success
 */
function submitWithEnhancedFeedback(form, buttonElement) {
    try {
        // Show loading state with animations
        window.afreshLoading.show(form, buttonElement);
        
        // Extract user ID for success animation
        const userId = extractUserIdFromForm(form);
        
        // Play notification sound
        window.afreshAudio.playNotification();
        window.afreshHaptics.medium();
        
        // Submit form
        console.log('📤 Submitting enhanced payment form...');
        form.submit();
        
        // Show floating success after delay
        if (userId) {
            setTimeout(() => {
                window.afreshAnimations.showFloatingSuccess(userId);
                window.afreshAudio.playSuccess();
                window.afreshHaptics.success();
            }, 1000);
        }
        
        // Show success notification
        window.afreshNotifications.show('Payment submitted successfully!', 'success');
        
        console.log('✅ Enhanced payment form submitted successfully');
        return true;
        
    } catch (error) {
        console.error('❌ Enhanced form submission error:', error);
        
        // Hide loading and show error
        window.afreshLoading.hide(form, buttonElement);
        window.afreshNotifications.show('Submission failed: ' + error.message, 'error');
        window.afreshAudio.playError();
        window.afreshHaptics.error();
        
        return false;
    }
}

/**
 * Extract user ID from form for animations
 * @param {HTMLElement} form - The form element
 * @returns {string|null} User ID
 */
function extractUserIdFromForm(form) {
    const submitBtn = form.querySelector('[id*="afreshSubmitBtn"]');
    if (submitBtn && submitBtn.id) {
        const match = submitBtn.id.match(/afreshSubmitBtn(\d+)/);
        return match ? match[1] : null;
    }
    return null;
}

/**
 * Enhanced copy functionality with particle effects
 * @param {string} text - Text to copy
 */
function copyMpesaNumber(text) {
    window.afreshAudio.playClick();
    window.afreshHaptics.light();
    
    if (!text || text === 'Not Set') {
        window.afreshNotifications.show('No M-Pesa number available', 'error');
        window.afreshAudio.playError();
        return;
    }
    
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(() => {
            handleCopySuccess();
        }).catch(() => {
            fallbackCopy(text);
        });
    } else {
        fallbackCopy(text);
    }
}

/**
 * Handle successful copy operation
 */
function handleCopySuccess() {
    const btn = event.target.closest('.copy-btn');
    if (!btn) return;
    
    // Visual feedback
    const icon = btn.querySelector('i');
    const text = btn.querySelector('span');
    const originalIcon = icon.className;
    const originalText = text.textContent;
    
    // Animate button
    window.afreshAnimations.pulseElement(btn, 1.1);
    
    // Update content
    icon.className = 'ri-check-fill';
    text.textContent = 'Copied!';
    btn.classList.add('copied');
    
    // Particle effect
    const rect = btn.getBoundingClientRect();
    window.afreshParticles.createCelebration(
        rect.left + rect.width / 2,
        rect.top + rect.height / 2
    );
    
    // Audio and haptic feedback
    window.afreshAudio.playSuccess();
    window.afreshHaptics.success();
    
    // Show notification
    window.afreshNotifications.show('M-Pesa number copied to clipboard!', 'success');
    
    // Reset after delay
    setTimeout(() => {
        icon.className = originalIcon;
        text.textContent = originalText;
        btn.classList.remove('copied');
    }, 2500);
}

/**
 * Fallback copy method for unsupported browsers
 * @param {string} text - Text to copy
 */
function fallbackCopy(text) {
    const textArea = document.createElement("textarea");
    textArea.value = text;
    textArea.style.position = "fixed";
    textArea.style.opacity = "0";
    textArea.style.top = "0";
    textArea.style.left = "0";
    
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    
    try {
        const successful = document.execCommand('copy');
        if (successful) {
            handleCopySuccess();
        } else {
            window.afreshNotifications.show('Copy failed - please try again', 'error');
            window.afreshAudio.playError();
        }
    } catch (err) {
        console.error('Copy command failed:', err);
        window.afreshNotifications.show('Copy not supported in this browser', 'error');
    }
    
    document.body.removeChild(textArea);
}

/**
 * Enhanced input interaction handlers
 */
function initializeAfreshInputHandlers() {
    document.querySelectorAll('.afresh-payment-form .form-input').forEach(input => {
        let validationTimeout;
        
        // Real-time validation with debouncing
        input.addEventListener('input', function(e) {
            clearTimeout(validationTimeout);
            validationTimeout = setTimeout(() => {
                window.afreshValidation.validateField(e.target, true);
            }, 300);
        });
        
        // Immediate validation on blur
        input.addEventListener('blur', function(e) {
            clearTimeout(validationTimeout);
            window.afreshValidation.validateField(e.target, true);
        });
        
        // Focus effects
        input.addEventListener('focus', function(e) {
            window.afreshHaptics.light();
            const container = e.target.closest('.input-container');
            if (container) {
                window.afreshAnimations.pulseElement(container, 1.02);
            }
        });
    });
    
    // Special handling for transaction input
    document.querySelectorAll('.transaction-input').forEach(input => {
        input.addEventListener('input', function(e) {
            // Auto-format transaction ID (uppercase, remove invalid chars)
            let value = e.target.value.toUpperCase().replace(/[^A-Z0-9\s\-_.]/g, '');
            if (value !== e.target.value) {
                e.target.value = value;
                window.afreshHaptics.light();
            }
        });
    });
}

/**
 * Initialize modal event handlers
 */
function initializeAfreshModalHandlers() {
    document.querySelectorAll('.afresh-payment-modal').forEach(modal => {
        // Modal show event
        modal.addEventListener('show.bs.modal', function(e) {
            console.log('🎯 Afresh payment modal opening');
            window.afreshAudio.playClick();
            
            // Prepare modal animations
            modal.style.opacity = '0';
            setTimeout(() => {
                modal.style.opacity = '1';
            }, 50);
        });
        
        // Modal shown event
        modal.addEventListener('shown.bs.modal', function(e) {
            console.log('✅ Afresh payment modal fully loaded');
            
            // Focus first input
            const firstInput = modal.querySelector('.transaction-input');
            if (firstInput) {
                setTimeout(() => {
                    focusFieldWithAnimation(firstInput);
                }, 200);
            }
            
            // Play welcome notification
            window.afreshAudio.playNotification();
            window.afreshNotifications.show('Payment form ready', 'info', 2000);
        });
        
        // Modal hide event
        modal.addEventListener('hide.bs.modal', function(e) {
            console.log('👋 Afresh payment modal closing');
            window.afreshAudio.playClick();
            
            // Reset all form states
            const form = modal.querySelector('.afresh-payment-form');
            if (form) {
                resetAfreshFormState(form);
            }
        });
    });
}

/**
 * Reset form state to initial clean state
 * @param {HTMLElement} form - The form element
 */
function resetAfreshFormState(form) {
    // Reset form classes
    form.classList.remove('loading');
    
    // Reset all input containers
    form.querySelectorAll('.input-container').forEach(container => {
        container.classList.remove('valid', 'invalid');
    });
    
    // Reset all inputs
    form.querySelectorAll('.form-input').forEach(input => {
        if (!input.readOnly) {
            input.classList.remove('is-valid', 'is-invalid');
        }
    });
    
    // Reset validation messages
    form.querySelectorAll('.validation-message').forEach(message => {
        message.classList.remove('show', 'success', 'error');
        message.innerHTML = '';
    });
    
    // Reset submit button
    const submitBtn = form.querySelector('.submit-btn');
    if (submitBtn) {
        window.afreshAnimations.restoreButton(submitBtn);
        submitBtn.disabled = false;
    }
}

/**
 * Initialize theme and accessibility features
 */
function initializeAfreshAccessibility() {
    // Dark mode detection
    if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
        document.documentElement.setAttribute('data-theme', 'dark');
    }
    
    // High contrast mode detection
    if (window.matchMedia && window.matchMedia('(prefers-contrast: high)').matches) {
        document.documentElement.setAttribute('data-contrast', 'high');
    }
    
    // Reduced motion detection
    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        document.documentElement.setAttribute('data-motion', 'reduced');
        window.afreshAudio.isEnabled = false;
    }
    
    // Keyboard navigation enhancement
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Tab') {
            document.body.classList.add('keyboard-navigation');
        }
    });
    
    document.addEventListener('mousedown', function(e) {
        document.body.classList.remove('keyboard-navigation');
    });
}

/**
 * Initialize performance optimizations
 */
function initializeAfreshPerformanceOptimizations() {
    // Lazy load heavy animations
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-ready');
            }
        });
    }, { threshold: 0.1 });
    
    document.querySelectorAll('.afresh-payment-modal').forEach(modal => {
        observer.observe(modal);
    });
    
    // Preload critical resources
    const link = document.createElement('link');
    link.rel = 'preload';
    link.href = 'data:text/css,/* Afresh Payment Form CSS loaded */';
    link.as = 'style';
    document.head.appendChild(link);
}

// Global event listeners and initialization
document.addEventListener('DOMContentLoaded', function() {
    console.log('🎨 Initializing Afresh Payment Form System');
    
    // Initialize all subsystems
    window.afreshAudio.init();
    window.afreshNotifications.init();
    
    // Initialize handlers
    initializeAfreshInputHandlers();
    initializeAfreshModalHandlers();
    initializeAfreshAccessibility();
    initializeAfreshPerformanceOptimizations();
    
    console.log('🎉 Afresh Payment Form System initialized successfully');
    
    // Show system ready notification - DISABLED for better UX
    // setTimeout(() => {
    //     window.afreshNotifications.show('Payment system ready', 'success', 2000);
    // }, 500);
});

// Error handling and recovery
window.addEventListener('error', function(e) {
    console.error('💥 Afresh Payment Form Error:', e.error);
    
    if (e.error && e.error.message.includes('afresh')) {
        window.afreshNotifications.show('System error occurred. Please refresh the page.', 'error');
        window.afreshAudio.playError();
    }
});

// Export functions for global access
window.submitAfreshPaymentForm = submitAfreshPaymentForm;
window.copyMpesaNumber = copyMpesaNumber;

// Utility function for showing notifications (used in blade component)
window.showAfreshNotification = function(message, type) {
    window.afreshNotifications.show(message, type);
};

// Utility function for playing sounds (used in blade component) 
window.playAfreshSound = function(type) {
    switch(type) {
        case 'success':
            window.afreshAudio.playSuccess();
            break;
        case 'error':
            window.afreshAudio.playError();
            break;
        case 'click':
            window.afreshAudio.playClick();
            break;
        case 'copy':
            window.afreshAudio.playSuccess();
            break;
        default:
            window.afreshAudio.playNotification();
    }
};
