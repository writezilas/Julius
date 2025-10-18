/**
 * Modern Payment Confirmation Form JavaScript
 * Advanced form handling with validation, accessibility, progressive enhancement
 * Optimized for modern browsers with fallbacks for older ones
 */

// Configuration and constants
const MODERN_PAYMENT_CONFIG = {
    selectors: {
        modal: '.modern-payment-modal',
        form: '.modern-payment-form',
        submitBtn: '.modern-btn-primary',
        transactionInput: '.transaction-input',
        notesInput: '.notes-input',
        copyBtn: '.copy-number-btn',
        characterCounter: '.character-counter'
    },
    validation: {
        transactionId: {
            pattern: /^[A-Za-z0-9\s\-_.]{4,30}$/,
            minLength: 4,
            maxLength: 30
        },
        notes: {
            maxLength: 500
        }
    },
    animations: {
        duration: 300,
        easing: 'cubic-bezier(0.4, 0, 0.2, 1)'
    },
    accessibility: {
        announcements: true,
        liveRegion: 'modern-sr-announcements'
    }
};

/**
 * Modern Payment Form Class
 * Handles all form interactions, validation, and accessibility
 */
class ModernPaymentForm {
    constructor(formElement) {
        this.form = formElement;
        this.modal = formElement.closest(MODERN_PAYMENT_CONFIG.selectors.modal);
        this.formId = formElement.id;
        this.isSubmitting = false;
        this.validationErrors = new Map();
        this.observers = new Map();
        
        // Initialize if form exists
        if (this.form && this.modal) {
            this.init();
        }
    }
    
    /**
     * Initialize the form with all enhancements
     */
    init() {
        try {
            this.setupElements();
            this.setupValidation();
            this.setupAccessibility();
            this.setupInteractions();
            this.setupProgressiveEnhancement();
            this.announceReady();
            
            console.log('Modern Payment Form initialized:', this.formId);
        } catch (error) {
            console.error('Error initializing Modern Payment Form:', error);
        }
    }
    
    /**
     * Setup form element references
     */
    setupElements() {
        this.elements = {
            submitBtn: this.form.querySelector(MODERN_PAYMENT_CONFIG.selectors.submitBtn),
            transactionInput: this.form.querySelector(MODERN_PAYMENT_CONFIG.selectors.transactionInput),
            notesInput: this.form.querySelector(MODERN_PAYMENT_CONFIG.selectors.notesInput),
            copyBtns: this.form.querySelectorAll(MODERN_PAYMENT_CONFIG.selectors.copyBtn),
            characterCounters: this.form.querySelectorAll(MODERN_PAYMENT_CONFIG.selectors.characterCounter),
            formGroups: this.form.querySelectorAll('.form-group'),
            inputWrappers: this.form.querySelectorAll('.input-wrapper')
        };
        
        // Ensure unique IDs for accessibility
        this.ensureUniqueIds();
    }
    
    /**
     * Ensure all form elements have proper IDs for accessibility
     */
    ensureUniqueIds() {
        const formSuffix = this.formId.replace(/[^a-zA-Z0-9]/g, '');
        
        if (this.elements.transactionInput && !this.elements.transactionInput.id) {
            this.elements.transactionInput.id = `modern_txs_${formSuffix}`;
        }
        
        if (this.elements.notesInput && !this.elements.notesInput.id) {
            this.elements.notesInput.id = `modern_notes_${formSuffix}`;
        }
    }
    
    /**
     * Setup form validation with real-time feedback
     */
    setupValidation() {
        // Transaction ID validation
        if (this.elements.transactionInput) {
            this.setupFieldValidation(
                this.elements.transactionInput,
                this.validateTransactionId.bind(this)
            );
        }
        
        // Notes character counting and validation
        if (this.elements.notesInput) {
            this.setupCharacterCounter(this.elements.notesInput);
            this.setupFieldValidation(
                this.elements.notesInput,
                this.validateNotes.bind(this)
            );
        }
        
        // Form-level validation
        this.form.addEventListener('submit', this.handleSubmit.bind(this));
    }
    
    /**
     * Setup field validation with debouncing
     */
    setupFieldValidation(field, validator) {
        let debounceTimer;
        
        // Real-time validation on input
        field.addEventListener('input', (e) => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                validator(e.target, false);
            }, 300);
        });
        
        // Strict validation on blur
        field.addEventListener('blur', (e) => {
            clearTimeout(debounceTimer);
            validator(e.target, true);
        });
        
        // Clear errors on focus
        field.addEventListener('focus', (e) => {
            this.clearFieldError(e.target);
        });
    }
    
    /**
     * Validate transaction ID field
     */
    validateTransactionId(field, strict = false) {
        const value = field.value.trim();
        const isEmpty = value.length === 0;
        const config = MODERN_PAYMENT_CONFIG.validation.transactionId;
        
        // Clear previous validation state
        this.clearValidationState(field);
        
        if (isEmpty && !strict) {
            return true; // Don't validate empty fields during typing
        }
        
        if (isEmpty && strict) {
            this.setFieldError(field, 'Transaction ID is required');
            return false;
        }
        
        if (value.length < config.minLength) {
            if (strict || value.length >= 3) {
                this.setFieldError(field, `Transaction ID must be at least ${config.minLength} characters`);
            }
            return false;
        }
        
        if (value.length > config.maxLength) {
            this.setFieldError(field, `Transaction ID must not exceed ${config.maxLength} characters`);
            return false;
        }
        
        if (!config.pattern.test(value)) {
            if (strict || value.length >= 3) {
                this.setFieldError(field, 'Invalid transaction ID format (letters, numbers, spaces, hyphens, underscores, dots only)');
            }
            return false;
        }
        
        // Valid input
        this.setFieldValid(field);
        return true;
    }
    
    /**
     * Validate notes field
     */
    validateNotes(field, strict = false) {
        const value = field.value;
        const config = MODERN_PAYMENT_CONFIG.validation.notes;
        
        this.clearValidationState(field);
        
        if (value.length > config.maxLength) {
            this.setFieldError(field, `Notes cannot exceed ${config.maxLength} characters`);
            return false;
        }
        
        // Notes field is optional, so it's valid if within length limits
        if (value.length > 0) {
            this.setFieldValid(field);
        }
        return true;
    }
    
    /**
     * Setup character counter for textarea
     */
    setupCharacterCounter(textarea) {
        const maxLength = MODERN_PAYMENT_CONFIG.validation.notes.maxLength;
        const counter = textarea.closest('.form-group')?.querySelector('.character-counter');
        
        if (!counter) return;
        
        const currentSpan = counter.querySelector('.current');
        const maxSpan = counter.querySelector('.max');
        
        if (maxSpan) {
            maxSpan.textContent = maxLength;
        }
        
        const updateCounter = () => {
            const current = textarea.value.length;
            if (currentSpan) {
                currentSpan.textContent = current;
                currentSpan.className = current > maxLength * 0.8 ? 'current warning' : 'current';
            }
        };
        
        textarea.addEventListener('input', updateCounter);
        updateCounter(); // Initial count
    }
    
    /**
     * Set field validation state to valid
     */
    setFieldValid(field) {
        field.classList.remove('is-invalid');
        field.classList.add('is-valid');
        this.validationErrors.delete(field.name);
        
        this.hideFieldError(field);
        this.announceToScreenReader(`${this.getFieldLabel(field)} is valid`);
    }
    
    /**
     * Set field validation state to invalid with error message
     */
    setFieldError(field, message) {
        field.classList.remove('is-valid');
        field.classList.add('is-invalid');
        this.validationErrors.set(field.name, message);
        
        this.showFieldError(field, message);
        this.announceToScreenReader(`Error in ${this.getFieldLabel(field)}: ${message}`);
    }
    
    /**
     * Clear validation state from field
     */
    clearValidationState(field) {
        field.classList.remove('is-valid', 'is-invalid');
    }
    
    /**
     * Clear field error
     */
    clearFieldError(field) {
        this.clearValidationState(field);
        this.validationErrors.delete(field.name);
        this.hideFieldError(field);
    }
    
    /**
     * Show field error message
     */
    showFieldError(field, message) {
        const formGroup = field.closest('.form-group');
        if (!formGroup) return;
        
        let errorElement = formGroup.querySelector('.form-error');
        if (!errorElement) {
            errorElement = document.createElement('div');
            errorElement.className = 'form-error';
            errorElement.innerHTML = '<i class="ri-error-warning-line" aria-hidden="true"></i><span class="error-message"></span>';
            formGroup.appendChild(errorElement);
        }
        
        const messageSpan = errorElement.querySelector('.error-message');
        if (messageSpan) {
            messageSpan.textContent = message;
        }
        
        errorElement.style.display = 'flex';
        errorElement.setAttribute('role', 'alert');
        errorElement.setAttribute('aria-live', 'polite');
        
        // Smooth animation
        requestAnimationFrame(() => {
            errorElement.style.opacity = '1';
            errorElement.style.transform = 'translateY(0)';
        });
    }
    
    /**
     * Hide field error message
     */
    hideFieldError(field) {
        const formGroup = field.closest('.form-group');
        const errorElement = formGroup?.querySelector('.form-error');
        
        if (errorElement) {
            errorElement.style.opacity = '0';
            errorElement.style.transform = 'translateY(-5px)';
            
            setTimeout(() => {
                errorElement.style.display = 'none';
                errorElement.removeAttribute('role');
                errorElement.removeAttribute('aria-live');
            }, MODERN_PAYMENT_CONFIG.animations.duration);
        }
    }
    
    /**
     * Get field label for accessibility announcements
     */
    getFieldLabel(field) {
        const formGroup = field.closest('.form-group');
        const label = formGroup?.querySelector('.form-label .label-text');
        return label ? label.textContent.trim() : 'Field';
    }
    
    /**
     * Setup accessibility features
     */
    setupAccessibility() {
        // Enhance ARIA attributes
        this.enhanceARIA();
        
        // Setup keyboard navigation
        this.setupKeyboardNavigation();
        
        // Create announcement region
        this.createAnnouncementRegion();
        
        // Setup focus management
        this.setupFocusManagement();
    }
    
    /**
     * Enhance ARIA attributes for better accessibility
     */
    enhanceARIA() {
        // Add form role and label
        this.form.setAttribute('role', 'form');
        this.form.setAttribute('aria-label', 'Payment confirmation form');
        
        // Enhance input fields
        if (this.elements.transactionInput) {
            this.elements.transactionInput.setAttribute('aria-required', 'true');
            this.elements.transactionInput.setAttribute('aria-describedby', 'txs-help');
        }
        
        if (this.elements.notesInput) {
            this.elements.notesInput.setAttribute('aria-describedby', 'notes-help notes-counter');
        }
        
        // Enhance submit button
        if (this.elements.submitBtn) {
            this.elements.submitBtn.setAttribute('aria-describedby', 'submit-help');
        }
    }
    
    /**
     * Setup keyboard navigation enhancements
     */
    setupKeyboardNavigation() {
        this.form.addEventListener('keydown', (e) => {
            switch (e.key) {
                case 'Tab':
                    this.handleTabNavigation(e);
                    break;
                case 'Enter':
                    this.handleEnterKey(e);
                    break;
                case 'Escape':
                    this.handleEscapeKey(e);
                    break;
            }
        });
        
        // Ctrl+Enter shortcut for submit
        this.form.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                e.preventDefault();
                if (this.elements.submitBtn && !this.elements.submitBtn.disabled) {
                    this.elements.submitBtn.click();
                }
            }
        });
    }
    
    /**
     * Handle Tab navigation within form
     */
    handleTabNavigation(e) {
        const focusableElements = this.form.querySelectorAll(
            'input:not([disabled]), button:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'
        );
        
        const firstElement = focusableElements[0];
        const lastElement = focusableElements[focusableElements.length - 1];
        
        if (e.shiftKey && document.activeElement === firstElement) {
            e.preventDefault();
            lastElement.focus();
        } else if (!e.shiftKey && document.activeElement === lastElement) {
            e.preventDefault();
            firstElement.focus();
        }
    }
    
    /**
     * Handle Enter key navigation
     */
    handleEnterKey(e) {
        if (e.target.tagName === 'TEXTAREA') return; // Allow newlines in textarea
        
        if (e.target.matches('input[type="text"]')) {
            e.preventDefault();
            const nextInput = this.getNextInput(e.target);
            if (nextInput) {
                nextInput.focus();
            } else if (this.elements.submitBtn && !this.elements.submitBtn.disabled) {
                this.elements.submitBtn.focus();
            }
        }
    }
    
    /**
     * Handle Escape key to close modal
     */
    handleEscapeKey(e) {
        const closeBtn = this.modal.querySelector('[data-bs-dismiss="modal"]');
        if (closeBtn) {
            closeBtn.click();
        }
    }
    
    /**
     * Get next input element for navigation
     */
    getNextInput(currentInput) {
        const inputs = Array.from(this.form.querySelectorAll('input, textarea'));
        const currentIndex = inputs.indexOf(currentInput);
        return inputs[currentIndex + 1] || null;
    }
    
    /**
     * Create screen reader announcement region
     */
    createAnnouncementRegion() {
        if (!document.getElementById(MODERN_PAYMENT_CONFIG.accessibility.liveRegion)) {
            const region = document.createElement('div');
            region.id = MODERN_PAYMENT_CONFIG.accessibility.liveRegion;
            region.className = 'sr-only';
            region.setAttribute('aria-live', 'polite');
            region.setAttribute('aria-atomic', 'true');
            document.body.appendChild(region);
        }
    }
    
    /**
     * Setup focus management
     */
    setupFocusManagement() {
        // Focus first input when modal opens
        this.modal.addEventListener('shown.bs.modal', () => {
            // Apply mobile adjustments first
            this.adjustModalForMobile();
            
            const firstInput = this.form.querySelector('input, textarea');
            const isMobile = window.innerWidth <= 767;
            
            if (firstInput && !isMobile) {
                // Only auto-focus on desktop to avoid keyboard issues on mobile
                setTimeout(() => firstInput.focus(), 100);
            }
        });
        
        // Focus management on form submission
        this.form.addEventListener('submit', (e) => {
            const firstInvalidField = this.form.querySelector('.is-invalid');
            if (firstInvalidField && this.validationErrors.size > 0) {
                e.preventDefault();
                firstInvalidField.focus();
                this.announceToScreenReader(`Please correct ${this.validationErrors.size} error${this.validationErrors.size !== 1 ? 's' : ''} in the form`);
            }
        });
    }
    
    /**
     * Setup interactive enhancements
     */
    setupInteractions() {
        // Setup copy button functionality
        this.elements.copyBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const number = btn.getAttribute('onclick')?.match(/'([^']+)'/)?.[1];
                if (number) {
                    this.copyToClipboard(number, btn);
                }
            });
        });
        
        // Setup smooth animations
        this.setupSmoothAnimations();
        
        // Setup intersection observer for animations
        this.setupIntersectionObserver();
    }
    
    /**
     * Setup smooth animations for form elements
     */
    setupSmoothAnimations() {
        // Animate form sections on load
        const sections = this.form.querySelectorAll('section');
        sections.forEach((section, index) => {
            section.style.opacity = '0';
            section.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                section.style.transition = `opacity ${MODERN_PAYMENT_CONFIG.animations.duration}ms ${MODERN_PAYMENT_CONFIG.animations.easing}, transform ${MODERN_PAYMENT_CONFIG.animations.duration}ms ${MODERN_PAYMENT_CONFIG.animations.easing}`;
                section.style.opacity = '1';
                section.style.transform = 'translateY(0)';
            }, index * 100);
        });
        
        // Animate input focus effects
        const inputs = this.form.querySelectorAll('.form-input');
        inputs.forEach(input => {
            input.addEventListener('focus', () => {
                const wrapper = input.closest('.input-wrapper');
                const focusRing = wrapper?.querySelector('.input-focus-ring');
                if (focusRing) {
                    focusRing.style.opacity = '0.3';
                    focusRing.style.transform = 'scale(1)';
                }
            });
            
            input.addEventListener('blur', () => {
                const wrapper = input.closest('.input-wrapper');
                const focusRing = wrapper?.querySelector('.input-focus-ring');
                if (focusRing) {
                    focusRing.style.opacity = '0';
                    focusRing.style.transform = 'scale(0.95)';
                }
            });
        });
    }
    
    /**
     * Setup intersection observer for scroll-based animations
     */
    setupIntersectionObserver() {
        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animate-in');
                    }
                });
            }, {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            });
            
            const animatableElements = this.form.querySelectorAll('.overview-item, .instruction-step');
            animatableElements.forEach(el => observer.observe(el));
            
            this.observers.set('intersection', observer);
        }
    }
    
    /**
     * Setup progressive enhancement features
     */
    setupProgressiveEnhancement() {
        // Add support for modern features if available
        this.addModernFeatures();
        
        // Add polyfills for older browsers
        this.addPolyfills();
        
        // Optimize for performance
        this.optimizePerformance();
    }
    
    /**
     * Add modern browser features
     */
    addModernFeatures() {
        // Add CSS custom property support detection
        if (CSS.supports('color', 'var(--test)')) {
            this.form.classList.add('supports-custom-properties');
        }
        
        // Add container query support
        if (CSS.supports('container-type: inline-size')) {
            this.form.classList.add('supports-container-queries');
        }
        
        // Add backdrop filter support
        if (CSS.supports('backdrop-filter: blur(10px)')) {
            this.form.classList.add('supports-backdrop-filter');
        }
    }
    
    /**
     * Add polyfills for older browsers
     */
    addPolyfills() {
        // Focus-visible polyfill for older browsers
        if (!CSS.supports('selector(:focus-visible)')) {
            this.addFocusVisiblePolyfill();
        }
        
        // Scroll behavior polyfill
        if (!('scrollBehavior' in document.documentElement.style)) {
            this.addSmoothScrollPolyfill();
        }
    }
    
    /**
     * Add focus-visible polyfill
     */
    addFocusVisiblePolyfill() {
        let hadKeyboardEvent = true;
        
        const keyboardThrottleTimeout = 100;
        let keyboardTimeout;
        
        const pointerEvents = ['mousedown', 'touchstart'];
        const keyboardEvents = ['keydown'];
        
        const onPointerDown = () => {
            hadKeyboardEvent = false;
        };
        
        const onKeyDown = (e) => {
            if (e.metaKey || e.altKey || e.ctrlKey) return;
            hadKeyboardEvent = true;
        };
        
        const onFocus = (e) => {
            if (hadKeyboardEvent) {
                e.target.classList.add('focus-visible');
            }
        };
        
        const onBlur = (e) => {
            e.target.classList.remove('focus-visible');
        };
        
        pointerEvents.forEach(event => {
            document.addEventListener(event, onPointerDown, true);
        });
        
        keyboardEvents.forEach(event => {
            document.addEventListener(event, onKeyDown, true);
        });
        
        this.form.addEventListener('focus', onFocus, true);
        this.form.addEventListener('blur', onBlur, true);
    }
    
    /**
     * Optimize performance
     */
    optimizePerformance() {
        // Debounce resize events
        let resizeTimeout;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                this.handleResize();
            }, 100);
        });
        
        // Use passive event listeners where appropriate
        this.form.addEventListener('scroll', this.handleScroll.bind(this), { passive: true });
        this.form.addEventListener('touchstart', this.handleTouchStart.bind(this), { passive: true });
    }
    
    /**
     * Handle resize events
     */
    handleResize() {
        // Adjust modal positioning if needed
        const modal = this.modal;
        if (modal && modal.classList.contains('show')) {
            const dialog = modal.querySelector('.modern-modal-dialog');
            const isMobile = window.innerWidth <= 767;
            
            if (dialog && isMobile) {
                // Force full screen on mobile
                this.adjustModalForMobile();
            } else if (dialog) {
                // Force recalculation of modal position
                dialog.style.transform = 'none';
                requestAnimationFrame(() => {
                    dialog.style.transform = '';
                });
            }
        }
    }
    
    /**
     * Adjust modal for mobile viewport
     */
    adjustModalForMobile() {
        const isMobile = window.innerWidth <= 767;
        if (!isMobile) return;
        
        const dialog = this.modal.querySelector('.modern-modal-dialog');
        const content = this.modal.querySelector('.modern-modal-content');
        const body = this.modal.querySelector('.modern-body');
        const footer = this.modal.querySelector('.modern-footer');
        
        if (dialog) {
            dialog.style.margin = '0';
            dialog.style.height = '100vh';
            dialog.style.maxHeight = '100vh';
            dialog.style.width = '100vw';
            dialog.style.maxWidth = '100vw';
        }
        
        if (content) {
            content.style.height = '100vh';
            content.style.maxHeight = '100vh';
            content.style.borderRadius = '0';
            content.style.display = 'flex';
            content.style.flexDirection = 'column';
        }
        
        if (body) {
            body.style.flex = '1';
            body.style.minHeight = '0';
            body.style.maxHeight = 'none';
            body.style.overflowY = 'auto';
            body.style.webkitOverflowScrolling = 'touch';
        }
        
        if (footer) {
            footer.style.flexShrink = '0';
            footer.style.position = 'sticky';
            footer.style.bottom = '0';
            footer.style.backgroundColor = 'var(--mp-white)';
            footer.style.zIndex = '100';
            footer.style.boxShadow = '0 -2px 10px rgba(0, 0, 0, 0.1)';
        }
    }
    
    /**
     * Handle scroll events
     */
    handleScroll() {
        // Could add scroll-based animations or effects here
    }
    
    /**
     * Handle touch start events
     */
    handleTouchStart() {
        // Add touch-specific enhancements
        this.form.classList.add('is-touch-device');
    }
    
    /**
     * Copy text to clipboard with modern API and fallback
     */
    async copyToClipboard(text, buttonElement) {
        if (!text || text === 'Not Set') {
            this.showNotification('No number available to copy', 'warning');
            this.announceToScreenReader('No number available to copy');
            return;
        }
        
        try {
            // Try modern clipboard API first
            if (navigator.clipboard && window.isSecureContext) {
                await navigator.clipboard.writeText(text);
                this.showCopySuccess(buttonElement, text);
            } else {
                // Fallback for older browsers
                await this.fallbackCopy(text, buttonElement);
            }
        } catch (error) {
            console.error('Copy failed:', error);
            this.showNotification(`Failed to copy: ${text}`, 'error');
            this.announceToScreenReader(`Copy failed. Please manually copy: ${text}`);
        }
    }
    
    /**
     * Fallback copy method for older browsers
     */
    fallbackCopy(text, buttonElement) {
        return new Promise((resolve, reject) => {
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.cssText = 'position: fixed; opacity: 0; left: -999999px; top: -999999px;';
            
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            
            try {
                const successful = document.execCommand('copy');
                if (successful) {
                    this.showCopySuccess(buttonElement, text);
                    resolve();
                } else {
                    reject(new Error('Copy command failed'));
                }
            } catch (err) {
                reject(err);
            } finally {
                document.body.removeChild(textArea);
            }
        });
    }
    
    /**
     * Show copy success feedback
     */
    showCopySuccess(buttonElement, text) {
        // Update button state
        const icon = buttonElement.querySelector('i');
        const originalIcon = icon?.className || 'ri-file-copy-line';
        
        if (icon) {
            icon.className = 'ri-check-line';
            buttonElement.classList.add('copied');
        }
        
        // Show success notification
        this.showNotification('Number copied successfully!', 'success');
        this.announceToScreenReader('Number copied to clipboard');
        
        // Reset after delay
        setTimeout(() => {
            if (icon) {
                icon.className = originalIcon;
                buttonElement.classList.remove('copied');
            }
        }, 2000);
    }
    
    /**
     * Show notification message
     */
    showNotification(message, type = 'info') {
        const template = document.getElementById('success-notification-template');
        if (!template) return;
        
        const notification = template.content.cloneNode(true);
        const notificationElement = notification.querySelector('.success-notification');
        const messageElement = notification.querySelector('.notification-message');
        
        if (messageElement) {
            messageElement.textContent = message;
        }
        
        // Add type-specific styling
        if (type === 'error') {
            notificationElement.style.background = 'var(--mp-error-500, #ef4444)';
        } else if (type === 'warning') {
            notificationElement.style.background = 'var(--mp-warning-500, #f59e0b)';
        }
        
        document.body.appendChild(notification);
        
        // Remove after delay
        setTimeout(() => {
            if (notificationElement.parentNode) {
                notificationElement.parentNode.removeChild(notificationElement);
            }
        }, 3000);
    }
    
    /**
     * Handle form submission
     */
    handleSubmit(e) {
        e.preventDefault();
        
        if (this.isSubmitting) {
            return false;
        }
        
        // Validate entire form
        if (!this.validateForm()) {
            const firstInvalidField = this.form.querySelector('.is-invalid');
            if (firstInvalidField) {
                firstInvalidField.focus();
                this.announceToScreenReader(`Please correct ${this.validationErrors.size} error${this.validationErrors.size !== 1 ? 's' : ''} before submitting`);
            }
            return false;
        }
        
        // Set loading state
        this.setLoadingState(true);
        
        // Stop payment timer if it exists
        this.stopPaymentTimer();
        
        try {
            // Submit form
            this.form.submit();
        } catch (error) {
            console.error('Form submission error:', error);
            this.showNotification('Error submitting form. Please try again.', 'error');
            this.setLoadingState(false);
        }
    }
    
    /**
     * Validate entire form
     */
    validateForm() {
        this.validationErrors.clear();
        let isValid = true;
        
        // Validate transaction ID
        if (this.elements.transactionInput) {
            const txValid = this.validateTransactionId(this.elements.transactionInput, true);
            isValid = isValid && txValid;
        }
        
        // Validate notes
        if (this.elements.notesInput) {
            const notesValid = this.validateNotes(this.elements.notesInput, true);
            isValid = isValid && notesValid;
        }
        
        // Validate other required fields
        const requiredFields = this.form.querySelectorAll('[required]');
        requiredFields.forEach(field => {
            if (!field.value || !field.value.trim()) {
                this.setFieldError(field, 'This field is required');
                isValid = false;
            }
        });
        
        return isValid;
    }
    
    /**
     * Set form loading state
     */
    setLoadingState(isLoading) {
        this.isSubmitting = isLoading;
        
        if (this.elements.submitBtn) {
            this.elements.submitBtn.disabled = isLoading;
            this.elements.submitBtn.classList.toggle('loading', isLoading);
            
            if (isLoading) {
                this.announceToScreenReader('Submitting payment information, please wait...');
            }
        }
        
        // Disable form inputs
        const inputs = this.form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.disabled = isLoading;
        });
    }
    
    /**
     * Stop payment timer if it exists
     */
    stopPaymentTimer() {
        const modalId = this.modal?.id;
        if (modalId) {
            const timerKey = modalId.replace('modernPaymentModal', '');
            console.log('Stopping timer for modern payment submission:', { modalId, timerKey });
            
            // Call global timer stop function if available
            if (window.stopPaymentTimer && typeof window.stopPaymentTimer === 'function') {
                window.stopPaymentTimer(timerKey, `payment-timer-${timerKey}`);
            }
            
            // Update UI
            this.updatePaymentButtonUI(timerKey);
        }
    }
    
    /**
     * Update payment button UI after submission
     */
    updatePaymentButtonUI(timerKey) {
        const payButtonRow = document.querySelector(`#payment-timer-${timerKey}`)?.closest('tr');
        if (payButtonRow) {
            const actionCell = payButtonRow.querySelector('td:last-child');
            if (actionCell) {
                actionCell.innerHTML = '<span class="badge bg-info-subtle text-info px-3 py-2"><i class="ri-time-line align-middle me-1"></i>Payment Submitted - Awaiting Confirmation</span>';
            }
        }
    }
    
    /**
     * Announce message to screen readers
     */
    announceToScreenReader(message) {
        if (!MODERN_PAYMENT_CONFIG.accessibility.announcements) return;
        
        const region = document.getElementById(MODERN_PAYMENT_CONFIG.accessibility.liveRegion);
        if (region) {
            region.textContent = message;
            setTimeout(() => {
                region.textContent = '';
            }, 1000);
        }
    }
    
    /**
     * Announce form ready state
     */
    announceReady() {
        setTimeout(() => {
            this.announceToScreenReader('Modern payment form is ready. Please fill in the required information to submit your payment.');
        }, 500);
    }
    
    /**
     * Cleanup when form is destroyed
     */
    destroy() {
        // Clear observers
        this.observers.forEach((observer) => {
            if (observer && typeof observer.disconnect === 'function') {
                observer.disconnect();
            }
        });
        this.observers.clear();
        
        // Clear validation errors
        this.validationErrors.clear();
        
        console.log('Modern Payment Form destroyed:', this.formId);
    }
}

/**
 * Global function for form submission (called from onclick)
 */
async function submitModernPaymentForm(formId, buttonElement) {
    console.log('Modern Payment Form Submission Started for:', formId);
    
    const form = document.getElementById(formId);
    if (!form) {
        console.error('Form not found:', formId);
        return false;
    }
    
    // Create or get existing form manager
    if (!form._modernPaymentForm) {
        form._modernPaymentForm = new ModernPaymentForm(form);
    }
    
    // Submit form using the manager
    const submitEvent = new Event('submit', { cancelable: true });
    form.dispatchEvent(submitEvent);
    
    return !submitEvent.defaultPrevented;
}

/**
 * Global copy function (called from onclick)
 */
async function modernCopyToClipboard(text, buttonElement) {
    const form = buttonElement.closest('.modern-payment-form');
    if (form && form._modernPaymentForm) {
        await form._modernPaymentForm.copyToClipboard(text, buttonElement);
    } else {
        // Fallback implementation
        try {
            if (navigator.clipboard && window.isSecureContext) {
                await navigator.clipboard.writeText(text);
            } else {
                // Create temporary textarea
                const textArea = document.createElement('textarea');
                textArea.value = text;
                textArea.style.cssText = 'position: fixed; opacity: 0; left: -999999px;';
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
            }
            
            // Show success feedback
            const icon = buttonElement.querySelector('i');
            if (icon) {
                const originalClass = icon.className;
                icon.className = 'ri-check-line';
                setTimeout(() => {
                    icon.className = originalClass;
                }, 2000);
            }
        } catch (error) {
            console.error('Copy failed:', error);
            alert(`Failed to copy. Please manually copy: ${text}`);
        }
    }
}

/**
 * Initialize all modern payment forms when DOM is ready
 */
function initializeModernPaymentForms() {
    const forms = document.querySelectorAll('.modern-payment-form');
    
    forms.forEach(form => {
        if (form.id && !form._modernPaymentForm) {
            form._modernPaymentForm = new ModernPaymentForm(form);
        }
    });
    
    console.log(`Initialized ${forms.length} modern payment forms`);
}

// Auto-initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeModernPaymentForms);
} else {
    initializeModernPaymentForms();
}

// Also initialize on dynamic content load (for SPAs)
if (typeof window.addEventListener === 'function') {
    window.addEventListener('modernPaymentFormsLoaded', initializeModernPaymentForms);
}

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { ModernPaymentForm, submitModernPaymentForm, modernCopyToClipboard };
}

// Add to global scope
window.ModernPaymentForm = ModernPaymentForm;
window.submitModernPaymentForm = submitModernPaymentForm;
window.modernCopyToClipboard = modernCopyToClipboard;