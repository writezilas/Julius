/**
 * Enhanced Payment Submit Form JavaScript
 * Advanced interactions, animations, and user experience enhancements
 * Includes form validation, copy functionality, loading states, and accessibility features
 */

// Global configuration
const ENHANCED_PAYMENT_CONFIG = {
    validation: {
        transactionIdPattern: /^[A-Za-z0-9\s\-_.]{4,30}$/,
        transactionIdMinLength: 4,
        transactionIdMaxLength: 30
    },
    animations: {
        successDuration: 2000,
        loadingDelay: 500,
        rippleEffect: true
    },
    copy: {
        successMessage: 'M-Pesa number copied!',
        errorMessage: 'Failed to copy M-Pesa number'
    },
    accessibility: {
        announceActions: true,
        keyboardNavigation: true
    }
};

/**
 * Enhanced Payment Form Manager
 */
class EnhancedPaymentForm {
    constructor(formId, options = {}) {
        this.formId = formId;
        this.form = document.getElementById(formId);
        this.options = { ...ENHANCED_PAYMENT_CONFIG, ...options };
        this.isSubmitting = false;
        this.validationErrors = new Map();
        
        if (this.form) {
            this.init();
        }
    }
    
    /**
     * Initialize the form with all enhancements
     */
    init() {
        this.setupFormElements();
        this.setupValidation();
        this.setupAnimations();
        this.setupAccessibility();
        this.setupKeyboardHandlers();
        this.announceFormReady();
    }
    
    /**
     * Setup form elements and references
     */
    setupFormElements() {
        this.elements = {
            transactionInput: this.form.querySelector('[name="txs_id"]'),
            notesInput: this.form.querySelector('[name="note_by_sender"]'),
            submitBtn: this.form.querySelector('.submit-btn'),
            btnContent: this.form.querySelector('.btn-content'),
            btnLoader: this.form.querySelector('.btn-loader'),
            copyButtons: this.form.querySelectorAll('.copy-btn'),
            formGroups: this.form.querySelectorAll('.enhanced-form-group'),
            inputContainers: this.form.querySelectorAll('.input-container')
        };
        
        // Add unique IDs if needed
        this.ensureUniqueIds();
    }
    
    /**
     * Ensure all form elements have unique IDs for accessibility
     */
    ensureUniqueIds() {
        const formSuffix = this.formId.replace(/[^a-zA-Z0-9]/g, '');
        
        if (this.elements.transactionInput && !this.elements.transactionInput.id) {
            this.elements.transactionInput.id = `txs_id_${formSuffix}`;
        }
        
        if (this.elements.notesInput && !this.elements.notesInput.id) {
            this.elements.notesInput.id = `notes_${formSuffix}`;
        }
    }
    
    /**
     * Setup real-time form validation
     */
    setupValidation() {
        if (this.elements.transactionInput) {
            // Real-time validation for transaction ID
            this.elements.transactionInput.addEventListener('input', (e) => {
                this.validateTransactionId(e.target);
            });
            
            this.elements.transactionInput.addEventListener('blur', (e) => {
                this.validateTransactionId(e.target, true);
            });
            
            this.elements.transactionInput.addEventListener('focus', (e) => {
                this.clearFieldError(e.target);
            });
        }
        
        // Notes input character counter
        if (this.elements.notesInput) {
            this.setupCharacterCounter(this.elements.notesInput);
        }
    }
    
    /**
     * Validate transaction ID field
     */
    validateTransactionId(input, showErrors = false) {
        const value = input.value.trim();
        const isValid = this.isValidTransactionId(value);
        const isEmpty = value.length === 0;
        
        // Remove previous validation classes
        input.classList.remove('is-valid', 'is-invalid');
        
        if (isEmpty && !showErrors) {
            return true; // Don't validate empty fields on input
        }
        
        if (isEmpty && showErrors) {
            this.setFieldError(input, 'Transaction ID is required');
            return false;
        }
        
        if (isValid) {
            this.setFieldValid(input);
            this.validationErrors.delete(input.name);
            return true;
        } else {
            if (showErrors || value.length >= 3) {
                this.setFieldError(input, 'Invalid transaction ID format (4-30 characters, letters/numbers only)');
            }
            return false;
        }
    }
    
    /**
     * Check if transaction ID is valid
     */
    isValidTransactionId(value) {
        if (!value || typeof value !== 'string') return false;
        return this.options.validation.transactionIdPattern.test(value) &&
               value.length >= this.options.validation.transactionIdMinLength &&
               value.length <= this.options.validation.transactionIdMaxLength;
    }
    
    /**
     * Set field as valid
     */
    setFieldValid(input) {
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
        
        const container = input.closest('.input-container');
        if (container) {
            const errorElement = container.querySelector('.enhanced-error');
            if (errorElement) {
                errorElement.style.display = 'none';
            }
        }
        
        this.announceToScreenReader(`${this.getFieldLabel(input)} is valid`);
    }
    
    /**
     * Set field error
     */
    setFieldError(input, message) {
        input.classList.remove('is-valid');
        input.classList.add('is-invalid');
        
        this.validationErrors.set(input.name, message);
        
        const container = input.closest('.enhanced-form-group');
        if (container) {
            let errorElement = container.querySelector('.enhanced-error');
            if (!errorElement) {
                errorElement = document.createElement('div');
                errorElement.className = 'enhanced-error';
                errorElement.innerHTML = '<i class="ri-error-warning-line"></i><span class="error-text"></span>';
                container.appendChild(errorElement);
            }
            
            const errorText = errorElement.querySelector('.error-text');
            if (errorText) {
                errorText.textContent = message;
            } else {
                errorElement.innerHTML = `<i class="ri-error-warning-line"></i>${message}`;
            }
            
            errorElement.style.display = 'flex';
        }
        
        this.announceToScreenReader(`Error in ${this.getFieldLabel(input)}: ${message}`);
    }
    
    /**
     * Clear field error
     */
    clearFieldError(input) {
        input.classList.remove('is-invalid');
        this.validationErrors.delete(input.name);
        
        const container = input.closest('.enhanced-form-group');
        if (container) {
            const errorElement = container.querySelector('.enhanced-error');
            if (errorElement) {
                errorElement.style.display = 'none';
            }
        }
    }
    
    /**
     * Get field label text
     */
    getFieldLabel(input) {
        const formGroup = input.closest('.enhanced-form-group');
        if (formGroup) {
            const label = formGroup.querySelector('.enhanced-form-label');
            return label ? label.textContent.trim().replace('*', '') : 'Field';
        }
        return 'Field';
    }
    
    /**
     * Setup character counter for textarea
     */
    setupCharacterCounter(textarea) {
        const maxLength = textarea.getAttribute('maxlength') || 500;
        
        // Create character counter element
        const counter = document.createElement('div');
        counter.className = 'character-counter';
        counter.innerHTML = `<span class="current">0</span>/<span class="max">${maxLength}</span>`;
        
        // Insert after the textarea container
        const container = textarea.closest('.input-container');
        if (container) {
            container.parentNode.insertBefore(counter, container.nextSibling);
        }
        
        // Update counter on input
        const updateCounter = () => {
            const current = textarea.value.length;
            const currentSpan = counter.querySelector('.current');
            if (currentSpan) {
                currentSpan.textContent = current;
                currentSpan.className = current > maxLength * 0.8 ? 'current warning' : 'current';
            }
        };
        
        textarea.addEventListener('input', updateCounter);
        updateCounter(); // Initial count
    }
    
    /**
     * Setup animations and micro-interactions
     */
    setupAnimations() {
        // Add ripple effects to buttons
        if (this.options.animations.rippleEffect) {
            this.setupRippleEffects();
        }
        
        // Setup hover animations for cards
        this.setupCardAnimations();
        
        // Setup input focus animations
        this.setupInputAnimations();
        
        // Initialize AOS (Animate On Scroll) if available
        this.initializeAOS();
    }
    
    /**
     * Setup ripple effects for buttons
     */
    setupRippleEffects() {
        const buttons = this.form.querySelectorAll('.enhanced-btn, .copy-btn');
        
        buttons.forEach(button => {
            button.addEventListener('click', (e) => {
                this.createRippleEffect(e, button);
            });
        });
    }
    
    /**
     * Create ripple effect
     */
    createRippleEffect(event, element) {
        const rect = element.getBoundingClientRect();
        const x = event.clientX - rect.left;
        const y = event.clientY - rect.top;
        
        const ripple = document.createElement('span');
        ripple.className = 'ripple-effect';
        ripple.style.left = x + 'px';
        ripple.style.top = y + 'px';
        
        element.style.position = 'relative';
        element.style.overflow = 'hidden';
        element.appendChild(ripple);
        
        setTimeout(() => {
            ripple.remove();
        }, 600);
    }
    
    /**
     * Setup card hover animations
     */
    setupCardAnimations() {
        const cards = this.form.querySelectorAll('.enhanced-card');
        
        cards.forEach(card => {
            card.addEventListener('mouseenter', () => {
                this.animateCardHover(card, true);
            });
            
            card.addEventListener('mouseleave', () => {
                this.animateCardHover(card, false);
            });
        });
    }
    
    /**
     * Animate card hover effect
     */
    animateCardHover(card, isHover) {
        const decoration = card.querySelector('.card-decoration');
        if (decoration) {
            decoration.style.transform = isHover ? 'scale(1.2) rotate(180deg)' : 'scale(1) rotate(0deg)';
            decoration.style.opacity = isHover ? '0.2' : '0.1';
        }
    }
    
    /**
     * Setup input focus animations
     */
    setupInputAnimations() {
        const inputs = this.form.querySelectorAll('.enhanced-form-control');
        
        inputs.forEach(input => {
            input.addEventListener('focus', () => {
                this.animateInputFocus(input, true);
            });
            
            input.addEventListener('blur', () => {
                this.animateInputFocus(input, false);
            });
        });
    }
    
    /**
     * Animate input focus
     */
    animateInputFocus(input, isFocused) {
        const container = input.closest('.input-container');
        if (container) {
            const icon = container.querySelector('.input-icon');
            if (icon) {
                icon.style.transform = isFocused ? 'translateY(-50%) scale(1.1)' : 'translateY(-50%) scale(1)';
                icon.style.color = isFocused ? 'var(--enhanced-primary)' : 'var(--enhanced-gray-400)';
            }
        }
    }
    
    /**
     * Initialize AOS (Animate On Scroll) if available
     */
    initializeAOS() {
        if (typeof AOS !== 'undefined') {
            AOS.init({
                duration: 600,
                easing: 'ease-out-cubic',
                once: true,
                offset: 50
            });
        }
    }
    
    /**
     * Setup accessibility features
     */
    setupAccessibility() {
        // Add ARIA labels and descriptions
        this.enhanceARIA();
        
        // Setup focus management
        this.setupFocusManagement();
        
        // Create screen reader announcements area
        this.createAnnouncementArea();
    }
    
    /**
     * Enhance ARIA attributes
     */
    enhanceARIA() {
        // Add role and aria-label to form
        this.form.setAttribute('role', 'form');
        this.form.setAttribute('aria-label', 'Payment submission form');
        
        // Enhance input fields
        if (this.elements.transactionInput) {
            this.elements.transactionInput.setAttribute('aria-describedby', 'txs-help');
            this.elements.transactionInput.setAttribute('aria-required', 'true');
        }
        
        if (this.elements.notesInput) {
            this.elements.notesInput.setAttribute('aria-describedby', 'notes-help');
        }
        
        // Enhance buttons
        if (this.elements.submitBtn) {
            this.elements.submitBtn.setAttribute('aria-describedby', 'submit-help');
        }
        
        // Add aria-live region for dynamic content
        const summaryGrid = this.form.querySelector('.summary-grid');
        if (summaryGrid) {
            summaryGrid.setAttribute('aria-live', 'polite');
            summaryGrid.setAttribute('aria-label', 'Payment summary');
        }
    }
    
    /**
     * Setup focus management
     */
    setupFocusManagement() {
        // Focus first invalid field on validation error
        this.form.addEventListener('submit', (e) => {
            const firstInvalidField = this.form.querySelector('.is-invalid');
            if (firstInvalidField) {
                e.preventDefault();
                firstInvalidField.focus();
                this.announceToScreenReader('Please correct the errors in the form');
            }
        });
        
        // Manage focus when modal opens
        const modal = this.form.closest('.modal');
        if (modal) {
            modal.addEventListener('shown.bs.modal', () => {
                const firstInput = this.form.querySelector('.enhanced-form-control');
                if (firstInput) {
                    setTimeout(() => firstInput.focus(), 100);
                }
            });
        }
    }
    
    /**
     * Create screen reader announcement area
     */
    createAnnouncementArea() {
        if (!document.getElementById('sr-announcements')) {
            const announceArea = document.createElement('div');
            announceArea.id = 'sr-announcements';
            announceArea.setAttribute('aria-live', 'polite');
            announceArea.setAttribute('aria-atomic', 'true');
            announceArea.style.cssText = 'position: absolute; left: -10000px; width: 1px; height: 1px; overflow: hidden;';
            document.body.appendChild(announceArea);
        }
    }
    
    /**
     * Announce message to screen readers
     */
    announceToScreenReader(message) {
        if (!this.options.accessibility.announceActions) return;
        
        const announceArea = document.getElementById('sr-announcements');
        if (announceArea) {
            announceArea.textContent = message;
            setTimeout(() => {
                announceArea.textContent = '';
            }, 1000);
        }
    }
    
    /**
     * Announce form ready state
     */
    announceFormReady() {
        setTimeout(() => {
            this.announceToScreenReader('Payment form is ready. Complete the required fields to submit your payment.');
        }, 500);
    }
    
    /**
     * Setup keyboard navigation enhancements
     */
    setupKeyboardHandlers() {
        if (!this.options.accessibility.keyboardNavigation) return;
        
        // Enhanced Tab navigation
        this.form.addEventListener('keydown', (e) => {
            if (e.key === 'Tab') {
                this.handleTabNavigation(e);
            } else if (e.key === 'Enter') {
                this.handleEnterKey(e);
            } else if (e.key === 'Escape') {
                this.handleEscapeKey(e);
            }
        });
        
        // Add keyboard shortcuts
        this.setupKeyboardShortcuts();
    }
    
    /**
     * Handle Tab navigation
     */
    handleTabNavigation(e) {
        const focusableElements = this.form.querySelectorAll(
            'input:not([disabled]), button:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'
        );
        
        const firstElement = focusableElements[0];
        const lastElement = focusableElements[focusableElements.length - 1];
        
        if (e.shiftKey) {
            if (document.activeElement === firstElement) {
                e.preventDefault();
                lastElement.focus();
            }
        } else {
            if (document.activeElement === lastElement) {
                e.preventDefault();
                firstElement.focus();
            }
        }
    }
    
    /**
     * Handle Enter key
     */
    handleEnterKey(e) {
        if (e.target.tagName === 'TEXTAREA') return; // Allow newlines in textarea
        
        if (e.target.classList.contains('enhanced-form-control')) {
            e.preventDefault();
            const nextInput = this.getNextInput(e.target);
            if (nextInput) {
                nextInput.focus();
            } else if (this.elements.submitBtn && !this.elements.submitBtn.disabled) {
                this.elements.submitBtn.click();
            }
        }
    }
    
    /**
     * Handle Escape key
     */
    handleEscapeKey(e) {
        const modal = this.form.closest('.modal');
        if (modal) {
            const closeBtn = modal.querySelector('[data-bs-dismiss="modal"]');
            if (closeBtn) {
                closeBtn.click();
            }
        }
    }
    
    /**
     * Get next input element
     */
    getNextInput(currentInput) {
        const inputs = Array.from(this.form.querySelectorAll('.enhanced-form-control'));
        const currentIndex = inputs.indexOf(currentInput);
        return inputs[currentIndex + 1] || null;
    }
    
    /**
     * Setup keyboard shortcuts
     */
    setupKeyboardShortcuts() {
        this.form.addEventListener('keydown', (e) => {
            // Ctrl/Cmd + Enter to submit
            if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                e.preventDefault();
                if (this.elements.submitBtn && !this.elements.submitBtn.disabled) {
                    this.elements.submitBtn.click();
                }
            }
        });
    }
    
    /**
     * Validate entire form
     */
    validateForm() {
        let isValid = true;
        this.validationErrors.clear();
        
        // Validate transaction ID
        if (this.elements.transactionInput) {
            const transactionValid = this.validateTransactionId(this.elements.transactionInput, true);
            isValid = isValid && transactionValid;
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
     * Submit form with enhanced UX
     */
    async submitForm() {
        if (this.isSubmitting) return false;
        
        // Validate form
        if (!this.validateForm()) {
            this.announceToScreenReader(`Form has ${this.validationErrors.size} error${this.validationErrors.size !== 1 ? 's' : ''}. Please correct them and try again.`);
            
            // Focus first invalid field
            const firstInvalidField = this.form.querySelector('.is-invalid');
            if (firstInvalidField) {
                firstInvalidField.focus();
            }
            
            return false;
        }
        
        // Show loading state
        this.setSubmittingState(true);
        
        // Stop payment timer if function exists
        this.stopPaymentTimerIfExists();
        
        try {
            // Submit form
            this.form.submit();
            return true;
        } catch (error) {
            console.error('Form submission error:', error);
            this.showErrorMessage('Error submitting form: ' + error.message);
            this.setSubmittingState(false);
            return false;
        }
    }
    
    /**
     * Set submitting state
     */
    setSubmittingState(isSubmitting) {
        this.isSubmitting = isSubmitting;
        
        if (this.elements.submitBtn) {
            this.elements.submitBtn.disabled = isSubmitting;
            this.elements.submitBtn.classList.toggle('loading', isSubmitting);
            
            if (isSubmitting) {
                this.announceToScreenReader('Submitting payment information, please wait...');
            }
        }
        
        // Disable form inputs during submission
        const inputs = this.form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.disabled = isSubmitting;
        });
    }
    
    /**
     * Stop payment timer if function exists
     */
    stopPaymentTimerIfExists() {
        const modalId = this.form.closest('.modal')?.id;
        if (modalId) {
            const timerKey = modalId.replace('enhancedPaymentModal', '');
            const timerId = 'payment-timer-' + timerKey;
            
            console.log('Stopping timer for payment submission:', { modalId, timerKey, timerId });
            
            // Call global timer stop function if available
            if (window.stopPaymentTimer && typeof window.stopPaymentTimer === 'function') {
                window.stopPaymentTimer(timerKey, timerId);
            }
            
            // Update UI immediately
            this.updatePaymentButtonUI(timerKey);
        }
    }
    
    /**
     * Update payment button UI
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
     * Show error message
     */
    showErrorMessage(message) {
        // You can customize this to show a toast, modal, or other notification
        alert(message);
        this.announceToScreenReader('Error: ' + message);
    }
    
    /**
     * Show success message
     */
    showSuccessMessage(message) {
        this.createSuccessNotification(message);
        this.announceToScreenReader('Success: ' + message);
    }
    
    /**
     * Create success notification
     */
    createSuccessNotification(message) {
        const notification = document.createElement('div');
        notification.className = 'copy-success';
        notification.innerHTML = `<i class="ri-check-fill"></i>${message}`;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, this.options.animations.successDuration);
    }
}

/**
 * Enhanced Copy to Clipboard Function
 */
async function copyToClipboard(text, buttonElement) {
    if (!text || text === 'Not Set') {
        const message = 'No M-Pesa number available to copy';
        alert(message);
        announceToScreenReader(message);
        return;
    }
    
    try {
        // Try modern clipboard API first
        if (navigator.clipboard && window.isSecureContext) {
            await navigator.clipboard.writeText(text);
            showCopySuccess(buttonElement, text);
        } else {
            // Fallback for older browsers
            await fallbackCopy(text, buttonElement);
        }
    } catch (error) {
        console.error('Copy failed:', error);
        const message = 'Failed to copy - please manually copy: ' + text;
        alert(message);
        announceToScreenReader('Copy failed: ' + error.message);
    }
}

/**
 * Fallback copy method
 */
function fallbackCopy(text, buttonElement) {
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
                showCopySuccess(buttonElement, text);
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
function showCopySuccess(buttonElement, text) {
    // Update button state
    const icon = buttonElement.querySelector('i');
    const originalIcon = icon?.className || 'ri-file-copy-line';
    
    if (icon) {
        icon.className = 'ri-check-fill';
        buttonElement.classList.add('copied');
    }
    
    // Create and show success notification
    const notification = document.createElement('div');
    notification.className = 'copy-success';
    notification.innerHTML = '<i class="ri-check-fill"></i>M-Pesa number copied successfully!';
    document.body.appendChild(notification);
    
    // Announce to screen readers
    announceToScreenReader('M-Pesa number copied to clipboard');
    
    // Reset after 2 seconds
    setTimeout(() => {
        if (icon) {
            icon.className = originalIcon;
            buttonElement.classList.remove('copied');
        }
        
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, ENHANCED_PAYMENT_CONFIG.animations.successDuration);
}

/**
 * Submit Enhanced Payment Form
 * Main submission function called from the form
 */
async function submitEnhancedPaymentForm(formId, buttonElement) {
    console.log('Enhanced Payment Form Submission Started for:', formId);
    
    // Create form manager instance
    const formManager = new EnhancedPaymentForm(formId);
    
    // Submit form
    const success = await formManager.submitForm();
    
    if (success) {
        console.log('Form submission successful');
    } else {
        console.log('Form submission failed or was prevented');
    }
    
    return success;
}

/**
 * Announce to screen readers helper
 */
function announceToScreenReader(message) {
    const announceArea = document.getElementById('sr-announcements');
    if (announceArea) {
        announceArea.textContent = message;
        setTimeout(() => {
            announceArea.textContent = '';
        }, 1000);
    }
}

/**
 * Initialize enhanced forms when DOM is loaded
 */
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all enhanced payment forms
    const forms = document.querySelectorAll('.enhanced-payment-form');
    
    forms.forEach(form => {
        if (form.id) {
            new EnhancedPaymentForm(form.id);
        }
    });
    
    // Add global CSS for ripple effects
    if (!document.getElementById('enhanced-payment-styles')) {
        const style = document.createElement('style');
        style.id = 'enhanced-payment-styles';
        style.textContent = `
            .ripple-effect {
                position: absolute;
                border-radius: 50%;
                background: rgba(255, 255, 255, 0.6);
                animation: ripple 0.6s linear;
                pointer-events: none;
                width: 40px;
                height: 40px;
                margin-left: -20px;
                margin-top: -20px;
            }
            
            @keyframes ripple {
                0% {
                    transform: scale(0);
                    opacity: 1;
                }
                100% {
                    transform: scale(2);
                    opacity: 0;
                }
            }
            
            .character-counter {
                text-align: right;
                font-size: 0.75rem;
                color: var(--enhanced-gray-500, #6b7280);
                margin-top: 0.25rem;
            }
            
            .character-counter .current.warning {
                color: var(--enhanced-warning, #fbbf24);
                font-weight: 600;
            }
            
            .enhanced-btn.copied {
                background: var(--enhanced-success, #4ade80) !important;
                border-color: var(--enhanced-success, #4ade80) !important;
            }
            
            /* Loading state styles */
            .submit-btn.loading {
                pointer-events: none;
            }
            
            .btn-content {
                transition: opacity 0.15s ease;
            }
            
            .btn-loader {
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                opacity: 0;
                transition: opacity 0.15s ease;
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }
            
            .submit-btn.loading .btn-content {
                opacity: 0;
            }
            
            .submit-btn.loading .btn-loader {
                opacity: 1;
            }
        `;
        document.head.appendChild(style);
    }
    
    console.log('Enhanced Payment Forms initialized successfully');
});