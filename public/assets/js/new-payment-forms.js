/**
 * New Payment Forms JavaScript
 * Clean, modern functionality for payment submit and confirmation forms
 * Includes validation, copy functionality, form submission, and enhanced UX
 */

// Configuration
const PAYMENT_CONFIG = {
    validation: {
        transactionIdPattern: /^[A-Za-z0-9\s\-_.]{4,20}$/,
        transactionIdMinLength: 4,
        transactionIdMaxLength: 20
    },
    ui: {
        toastDuration: 3000,
        loadingDelay: 500
    }
};

/**
 * Payment Form Manager Class
 */
class NewPaymentFormManager {
    constructor() {
        this.isSubmitting = false;
        this.init();
    }

    /**
     * Initialize all payment forms
     */
    init() {
        this.setupFormValidation();
        this.setupCharacterCounters();
        this.setupTooltips();
        this.bindEvents();
    }

    /**
     * Setup form validation for all payment forms
     */
    setupFormValidation() {
        // Transaction ID validation
        document.querySelectorAll('input[name="txs_id"], input[name="transaction_id"]').forEach(input => {
            this.setupTransactionIdValidation(input);
        });

        // Notes character validation
        document.querySelectorAll('textarea[name="note_by_sender"], textarea[name="notes"]').forEach(textarea => {
            this.setupNotesValidation(textarea);
        });
    }

    /**
     * Setup transaction ID field validation
     */
    setupTransactionIdValidation(input) {
        // Real-time validation
        input.addEventListener('input', () => {
            this.validateTransactionId(input);
        });

        // Validation on blur
        input.addEventListener('blur', () => {
            this.validateTransactionId(input, true);
        });

        // Clear validation on focus
        input.addEventListener('focus', () => {
            this.clearFieldValidation(input);
        });
    }

    /**
     * Validate transaction ID
     */
    validateTransactionId(input, showErrors = false) {
        const value = input.value.trim().toUpperCase();
        const isEmpty = value.length === 0;

        // Update input value to uppercase
        input.value = value;

        // Clear previous validation
        this.clearFieldValidation(input);

        if (isEmpty && !showErrors) {
            return true;
        }

        if (isEmpty && showErrors) {
            this.setFieldError(input, 'Transaction ID is required');
            return false;
        }

        // Validate pattern and length
        const isValid = PAYMENT_CONFIG.validation.transactionIdPattern.test(value) &&
                        value.length >= PAYMENT_CONFIG.validation.transactionIdMinLength &&
                        value.length <= PAYMENT_CONFIG.validation.transactionIdMaxLength;

        if (isValid) {
            this.setFieldValid(input);
            return true;
        } else {
            if (showErrors || value.length >= 3) {
                this.setFieldError(input, `Invalid format. Use 4-${PAYMENT_CONFIG.validation.transactionIdMaxLength} characters (letters, numbers, spaces, hyphens, underscores)`);
            }
            return false;
        }
    }

    /**
     * Setup notes field validation
     */
    setupNotesValidation(textarea) {
        textarea.addEventListener('input', () => {
            this.updateCharacterCount(textarea);
        });
    }

    /**
     * Update character count for textarea
     */
    updateCharacterCount(textarea) {
        const maxLength = parseInt(textarea.getAttribute('maxlength')) || 500;
        const currentLength = textarea.value.length;
        const container = textarea.closest('.input-container');
        const counter = container?.querySelector('.character-count');

        if (counter) {
            const currentSpan = counter.querySelector('.current');
            const maxSpan = counter.querySelector('.max');

            if (currentSpan) {
                currentSpan.textContent = currentLength;
                currentSpan.classList.toggle('warning', currentLength > maxLength * 0.8);
            }

            if (maxSpan) {
                maxSpan.textContent = maxLength;
            }
        }

        // Validate length
        if (currentLength > maxLength) {
            this.setFieldError(textarea, `Maximum ${maxLength} characters allowed`);
        } else {
            this.clearFieldValidation(textarea);
        }
    }

    /**
     * Setup character counters for all textareas
     */
    setupCharacterCounters() {
        document.querySelectorAll('textarea[maxlength]').forEach(textarea => {
            this.updateCharacterCount(textarea);
        });
    }

    /**
     * Setup tooltips (if needed)
     */
    setupTooltips() {
        // Initialize Bootstrap tooltips if available
        if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
        }
    }

    /**
     * Bind global events
     */
    bindEvents() {
        // Form submissions
        document.addEventListener('keydown', (e) => {
            // Ctrl/Cmd + Enter to submit
            if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                const activeModal = document.querySelector('.modal.show');
                if (activeModal) {
                    const submitBtn = activeModal.querySelector('.btn-submit, .btn-confirm');
                    if (submitBtn && !submitBtn.disabled) {
                        e.preventDefault();
                        submitBtn.click();
                    }
                }
            }

            // Escape to close modal
            if (e.key === 'Escape') {
                const activeModal = document.querySelector('.modal.show');
                if (activeModal) {
                    const closeBtn = activeModal.querySelector('[data-bs-dismiss="modal"]');
                    if (closeBtn) {
                        closeBtn.click();
                    }
                }
            }
        });

        // Auto-focus first input when modal opens
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('shown.bs.modal', () => {
                const firstInput = modal.querySelector('input[type="text"], textarea');
                if (firstInput) {
                    setTimeout(() => firstInput.focus(), 100);
                }
            });
        });
    }

    /**
     * Set field as valid
     */
    setFieldValid(input) {
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
        
        const feedback = input.parentNode.querySelector('.input-feedback');
        if (feedback) {
            feedback.style.display = 'block';
        }

        this.hideFieldError(input);
    }

    /**
     * Set field error
     */
    setFieldError(input, message) {
        input.classList.remove('is-valid');
        input.classList.add('is-invalid');
        
        const feedback = input.parentNode.querySelector('.input-feedback');
        if (feedback) {
            feedback.style.display = 'block';
        }

        this.showFieldError(input, message);
    }

    /**
     * Clear field validation
     */
    clearFieldValidation(input) {
        input.classList.remove('is-valid', 'is-invalid');
        
        const feedback = input.parentNode.querySelector('.input-feedback');
        if (feedback) {
            feedback.style.display = 'none';
        }

        this.hideFieldError(input);
    }

    /**
     * Show field error message
     */
    showFieldError(input, message) {
        const formGroup = input.closest('.form-group');
        if (!formGroup) return;

        let errorElement = formGroup.querySelector('.form-error');
        if (!errorElement) {
            errorElement = document.createElement('div');
            errorElement.className = 'form-error';
            errorElement.innerHTML = `
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="15" y1="9" x2="9" y2="15"/>
                    <line x1="9" y1="9" x2="15" y2="15"/>
                </svg>
                <span class="error-message"></span>
            `;
            formGroup.appendChild(errorElement);
        }

        const messageSpan = errorElement.querySelector('.error-message');
        if (messageSpan) {
            messageSpan.textContent = message;
        }

        errorElement.style.display = 'flex';
    }

    /**
     * Hide field error message
     */
    hideFieldError(input) {
        const formGroup = input.closest('.form-group');
        if (!formGroup) return;

        const errorElement = formGroup.querySelector('.form-error');
        if (errorElement) {
            errorElement.style.display = 'none';
        }
    }

    /**
     * Validate entire form
     */
    validateForm(form) {
        let isValid = true;

        // Validate required fields
        const requiredInputs = form.querySelectorAll('input[required], textarea[required]');
        requiredInputs.forEach(input => {
            if (input.name === 'txs_id' || input.name === 'transaction_id') {
                const fieldValid = this.validateTransactionId(input, true);
                isValid = isValid && fieldValid;
            } else if (!input.value.trim()) {
                this.setFieldError(input, 'This field is required');
                isValid = false;
            } else {
                this.clearFieldValidation(input);
            }
        });

        // Validate textareas with maxlength
        const textareas = form.querySelectorAll('textarea[maxlength]');
        textareas.forEach(textarea => {
            const maxLength = parseInt(textarea.getAttribute('maxlength'));
            if (textarea.value.length > maxLength) {
                this.setFieldError(textarea, `Maximum ${maxLength} characters allowed`);
                isValid = false;
            }
        });

        return isValid;
    }

    /**
     * Show toast notification
     */
    showToast(title, message, type = 'success') {
        const toast = document.getElementById('paymentToast') || document.getElementById('confirmationToast');
        if (!toast) return;

        // Update content
        const titleElement = toast.querySelector('.toast-title');
        const messageElement = toast.querySelector('.toast-message');
        
        if (titleElement) titleElement.textContent = title;
        if (messageElement) messageElement.textContent = message;

        // Show toast
        toast.classList.add('show');

        // Auto-hide toast
        setTimeout(() => {
            toast.classList.remove('show');
        }, PAYMENT_CONFIG.ui.toastDuration);
    }

    /**
     * Show loading overlay
     */
    showLoadingOverlay() {
        const overlay = document.getElementById('paymentOverlay');
        if (overlay) {
            overlay.style.display = 'flex';
        }
    }

    /**
     * Hide loading overlay
     */
    hideLoadingOverlay() {
        const overlay = document.getElementById('paymentOverlay');
        if (overlay) {
            overlay.style.display = 'none';
        }
    }

    /**
     * Show success notification
     */
    showSuccessNotification() {
        const notification = document.getElementById('paymentSuccessNotification');
        if (notification) {
            notification.style.display = 'block';
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
                notification.style.display = 'none';
            }, 5000);
        }
    }
}

/**
 * Copy M-Pesa number to clipboard
 */
async function copyMpesaNumber(number, buttonElement) {
    if (!number || number === 'Not Set') {
        showToast('Error', 'No M-Pesa number available to copy', 'error');
        return;
    }

    try {
        // Try modern clipboard API first
        if (navigator.clipboard && window.isSecureContext) {
            await navigator.clipboard.writeText(number);
            showCopySuccess(buttonElement);
        } else {
            // Fallback for older browsers
            await fallbackCopy(number);
            showCopySuccess(buttonElement);
        }
        
        // Show toast notification
        paymentFormManager.showToast('Success!', 'M-Pesa number copied to clipboard');
        
    } catch (error) {
        console.error('Copy failed:', error);
        showToast('Error', 'Failed to copy M-Pesa number', 'error');
    }
}

/**
 * Fallback copy method for older browsers
 */
function fallbackCopy(text) {
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
 * Show copy success visual feedback
 */
function showCopySuccess(buttonElement) {
    const originalContent = buttonElement.innerHTML;
    
    // Update button to show success
    buttonElement.innerHTML = `
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="20,6 9,17 4,12"/>
        </svg>
    `;
    buttonElement.style.background = 'var(--payment-success)';
    
    // Reset after 2 seconds
    setTimeout(() => {
        buttonElement.innerHTML = originalContent;
        buttonElement.style.background = '';
    }, 2000);
}

/**
 * Submit new payment form (for payment submission)
 */
async function submitNewPaymentForm(formId, buttonElement) {
    console.log('New Payment Form Submission Started for:', formId);
    
    const form = document.getElementById(formId);
    if (!form) {
        console.error('Form not found:', formId);
        return false;
    }

    // Prevent double submission
    if (paymentFormManager.isSubmitting) {
        return false;
    }

    // Validate form
    if (!paymentFormManager.validateForm(form)) {
        console.log('Form validation failed');
        
        // Focus first invalid field
        const firstInvalid = form.querySelector('.is-invalid');
        if (firstInvalid) {
            firstInvalid.focus();
            firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        
        return false;
    }

    // Set loading state
    setButtonLoading(buttonElement, true);
    paymentFormManager.isSubmitting = true;

    // Stop payment timer if exists
    stopPaymentTimerIfExists(form);

    try {
        // Submit form
        form.submit();
        return true;
        
    } catch (error) {
        console.error('Form submission error:', error);
        setButtonLoading(buttonElement, false);
        paymentFormManager.isSubmitting = false;
        paymentFormManager.showToast('Error', 'Failed to submit payment: ' + error.message, 'error');
        return false;
    }
}

/**
 * Confirm new payment (for payment confirmation)
 */
async function confirmNewPayment() {
    console.log('New Payment Confirmation Started');
    
    const modal = document.querySelector('.new-payment-confirmation-modal').closest('.modal');
    const button = document.getElementById('confirmPaymentBtn');
    
    if (!modal || !button) {
        console.error('Modal or button not found');
        return false;
    }

    // Prevent double submission
    if (paymentFormManager.isSubmitting) {
        return false;
    }

    // Get form data
    const transactionInput = modal.querySelector('#mpesa-transaction-id');
    const notesInput = modal.querySelector('#payment-notes');
    
    if (!transactionInput) {
        console.error('Transaction input not found');
        return false;
    }

    // Validate transaction ID
    if (!paymentFormManager.validateTransactionId(transactionInput, true)) {
        transactionInput.focus();
        return false;
    }

    // Prepare form data
    const formData = {
        transaction_id: transactionInput.value.trim(),
        notes: notesInput ? notesInput.value.trim() : '',
        _token: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    };

    // Set loading state
    setButtonLoading(button, true);
    paymentFormManager.isSubmitting = true;

    try {
        // Show loading overlay
        paymentFormManager.showLoadingOverlay();

        // Simulate API call (replace with actual submission)
        await new Promise(resolve => setTimeout(resolve, 2000));

        // Hide loading overlay
        paymentFormManager.hideLoadingOverlay();

        // Show success notification
        paymentFormManager.showSuccessNotification();

        // Close modal
        setTimeout(() => {
            const closeBtn = modal.querySelector('[data-bs-dismiss="modal"]');
            if (closeBtn) closeBtn.click();
        }, 1000);

        return true;
        
    } catch (error) {
        console.error('Payment confirmation error:', error);
        paymentFormManager.hideLoadingOverlay();
        paymentFormManager.showToast('Error', 'Failed to confirm payment: ' + error.message, 'error');
        return false;
        
    } finally {
        setButtonLoading(button, false);
        paymentFormManager.isSubmitting = false;
    }
}

/**
 * Set button loading state
 */
function setButtonLoading(button, isLoading) {
    if (isLoading) {
        button.disabled = true;
        button.classList.add('loading');
    } else {
        button.disabled = false;
        button.classList.remove('loading');
    }
}

/**
 * Stop payment timer if exists (for compatibility with existing system)
 */
function stopPaymentTimerIfExists(form) {
    try {
        const modalId = form.closest('.modal')?.id;
        if (modalId) {
            const timerKey = modalId.replace(/^new/, '').replace(/Modal.*/, '');
            const timerId = 'payment-timer-' + timerKey;
            
            console.log('Attempting to stop timer:', { modalId, timerKey, timerId });
            
            // Call global timer stop function if available
            if (window.stopPaymentTimer && typeof window.stopPaymentTimer === 'function') {
                window.stopPaymentTimer(timerKey, timerId);
            }
            
            // Update UI immediately
            updatePaymentButtonUI(timerKey);
        }
    } catch (error) {
        console.warn('Error stopping payment timer:', error);
    }
}

/**
 * Update payment button UI (for compatibility)
 */
function updatePaymentButtonUI(timerKey) {
    try {
        const payButtonRow = document.querySelector(`#payment-timer-${timerKey}`)?.closest('tr');
        if (payButtonRow) {
            const actionCell = payButtonRow.querySelector('td:last-child');
            if (actionCell) {
                actionCell.innerHTML = '<span class="badge bg-info-subtle text-info px-3 py-2"><i class="ri-time-line align-middle me-1"></i>Payment Submitted - Awaiting Confirmation</span>';
            }
        }
    } catch (error) {
        console.warn('Error updating payment button UI:', error);
    }
}

/**
 * Show toast notification (global function)
 */
function showToast(title, message, type = 'success') {
    if (window.paymentFormManager) {
        paymentFormManager.showToast(title, message, type);
    } else {
        // Fallback to console or alert
        console.log(`${type.toUpperCase()}: ${title} - ${message}`);
    }
}

// Initialize payment form manager when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('Initializing New Payment Forms...');
    
    // Create global instance
    window.paymentFormManager = new NewPaymentFormManager();
    
    console.log('New Payment Forms initialized successfully');
});

// Export for module use if needed
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        NewPaymentFormManager,
        copyMpesaNumber,
        submitNewPaymentForm,
        confirmNewPayment
    };
}