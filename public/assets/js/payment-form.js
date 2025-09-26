/**
 * Enhanced Payment Form JavaScript Module
 * Handles form validation, submission, audio notifications, and user interactions
 */

// Initialize audio notifications if not already defined
if (typeof window.audioNotifications === 'undefined') {
    window.audioNotifications = {
        isEnabled: true,
        
        playSuccess: function() {
            console.log('Playing success sound');
            try {
                // Create audio context if supported
                if (typeof AudioContext !== 'undefined' || typeof webkitAudioContext !== 'undefined') {
                    const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                    const oscillator = audioContext.createOscillator();
                    const gainNode = audioContext.createGain();
                    
                    oscillator.connect(gainNode);
                    gainNode.connect(audioContext.destination);
                    
                    oscillator.frequency.setValueAtTime(800, audioContext.currentTime);
                    oscillator.frequency.setValueAtTime(1000, audioContext.currentTime + 0.1);
                    
                    gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
                    gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.3);
                    
                    oscillator.start(audioContext.currentTime);
                    oscillator.stop(audioContext.currentTime + 0.3);
                }
            } catch (error) {
                console.log('Audio not supported or failed:', error);
            }
        },
        
        playError: function() {
            console.log('Playing error sound');
            try {
                if (typeof AudioContext !== 'undefined' || typeof webkitAudioContext !== 'undefined') {
                    const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                    const oscillator = audioContext.createOscillator();
                    const gainNode = audioContext.createGain();
                    
                    oscillator.connect(gainNode);
                    gainNode.connect(audioContext.destination);
                    
                    oscillator.frequency.setValueAtTime(400, audioContext.currentTime);
                    oscillator.frequency.setValueAtTime(300, audioContext.currentTime + 0.1);
                    
                    gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
                    gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.4);
                    
                    oscillator.start(audioContext.currentTime);
                    oscillator.stop(audioContext.currentTime + 0.4);
                }
            } catch (error) {
                console.log('Audio not supported or failed:', error);
            }
        },
        
        playNotificationSoundAfterDelay: function(delay) {
            setTimeout(() => {
                this.playSuccess();
            }, delay || 3000);
        }
    };
}

/**
 * Enhanced form validation and submission function
 * @param {string} formId - The ID of the form to submit
 * @param {HTMLElement} buttonElement - The submit button element
 */
function submitPaymentForm(formId, buttonElement) {
    console.log('=== ENHANCED PAYMENT FORM SUBMISSION START ===');
    console.log('Form ID:', formId);
    console.log('Button Element:', buttonElement);
    
    // Get form element with multiple fallback methods
    let form = document.getElementById(formId);
    
    // Alternative: try to find form from the button context if ID lookup fails
    if (!form && buttonElement) {
        const modal = buttonElement.closest('.modal');
        if (modal) {
            form = modal.querySelector('form.payment-form');
            console.log('Form found via modal context:', form);
        }
    }
    
    // Final check for form
    if (!form) {
        console.error('Form not found:', formId);
        showFormError('Form not found. Please refresh the page and try again.');
        return false;
    }
    
    console.log('Form found:', form);
    console.log('Form action:', form.action);
    
    // Enhanced transaction ID field detection with multiple methods
    console.log('Searching for transaction ID field in form...');
    
    let txsIdField = findTransactionIdField(form);
    
    // Validate transaction ID field
    const isTransactionIdValid = validateTransactionId(txsIdField, form);
    if (!isTransactionIdValid) {
        return false;
    }
    
    // Validate all required fields
    if (!validateRequiredFields(form)) {
        return false;
    }
    
    // Validate form data integrity
    if (!validateFormData(form)) {
        return false;
    }
    
    // Play success sound for successful validation
    console.log('All validations passed - playing success sound');
    window.audioNotifications.playSuccess();
    
    // User confirmation - DISABLED
    // if (!confirm('Are you sure you want to submit this payment information?')) {
    //     return false;
    // }
    
    // Submit form with loading state
    return submitFormWithLoadingState(form, buttonElement);
}

/**
 * Find transaction ID field using multiple detection methods
 * @param {HTMLElement} form - The form element
 * @returns {HTMLElement|null} The transaction ID input field
 */
function findTransactionIdField(form) {
    // Method 1: By name attribute
    let txsIdField = form.querySelector('input[name="txs_id"]');
    console.log('Method 1 (by name):', txsIdField);
    
    // Method 2: By class
    if (!txsIdField) {
        txsIdField = form.querySelector('.transaction-input');
        console.log('Method 2 (by class):', txsIdField);
    }
    
    // Method 3: By ID pattern
    if (!txsIdField) {
        const inputs = form.querySelectorAll('input[id*="txsIdField"]');
        console.log('Method 3 (by ID pattern) found:', inputs.length, 'matches');
        if (inputs.length > 0) {
            txsIdField = inputs[0];
        }
    }
    
    // Method 4: Scan all inputs for transaction-related attributes
    if (!txsIdField) {
        const allInputs = Array.from(form.querySelectorAll('input'));
        console.log('Method 4: Scanning all inputs:', allInputs.length);
        
        txsIdField = allInputs.find(input => 
            (input.id && input.id.includes('txsId')) || 
            (input.name && input.name === 'txs_id') ||
            input.classList.contains('transaction-input') ||
            (input.placeholder && input.placeholder.toLowerCase().includes('transaction'))
        );
    }
    
    return txsIdField;
}

/**
 * Validate transaction ID field
 * @param {HTMLElement} txsIdField - The transaction ID input field
 * @param {HTMLElement} form - The form element
 * @returns {boolean} True if valid or optional, false if invalid
 */
function validateTransactionId(txsIdField, form) {
    const fieldIsRequired = form.querySelector('input[name="txs_id"][required]') !== null;
    console.log('Transaction ID field is required:', fieldIsRequired);
    
    if (!txsIdField) {
        if (fieldIsRequired) {
            console.error('Transaction ID field not found - this is required');
            showFormError('Transaction ID field not found. Please refresh the page and try again.');
            return false;
        } else {
            console.warn('Transaction ID field not found, but continuing since it\'s optional');
            return true;
        }
    }
    
    console.log('Transaction ID field found:', txsIdField);
    console.log('Field ID:', txsIdField.id);
    console.log('Field value:', txsIdField.value);
    
    const value = txsIdField.value ? txsIdField.value.trim() : '';
    
    // Validate if field is required or has content
    if (fieldIsRequired || value.length > 0) {
        if (fieldIsRequired && (!value || value.length === 0)) {
            window.audioNotifications.playError();
            showFormError('Please enter the M-Pesa Transaction ID. This field is required.');
            focusField(txsIdField);
            return false;
        }
        
        // Validate format if value is provided
        if (value.length > 0) {
            const pattern = /^[A-Za-z0-9\s\-_.]{4,30}$/;
            if (!pattern.test(value)) {
                window.audioNotifications.playError();
                showFormError('Invalid M-Pesa Transaction ID format. Please use 4-30 characters (letters, numbers, spaces, hyphens allowed)');
                focusField(txsIdField);
                return false;
            } else {
                txsIdField.classList.remove('is-invalid');
                txsIdField.classList.add('is-valid');
            }
        }
    }
    
    return true;
}

/**
 * Validate all required fields in the form
 * @param {HTMLElement} form - The form element
 * @returns {boolean} True if all required fields are valid
 */
function validateRequiredFields(form) {
    const requiredFields = form.querySelectorAll('[required]');
    let allValid = true;
    let firstInvalidField = null;
    
    console.log('Validating', requiredFields.length, 'required fields');
    
    requiredFields.forEach(field => {
        const value = field.value ? field.value.trim() : '';
        
        if (!value) {
            field.classList.add('is-invalid');
            field.classList.remove('is-valid');
            allValid = false;
            
            if (!firstInvalidField) {
                firstInvalidField = field;
            }
        } else {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
        }
    });
    
    if (!allValid) {
        window.audioNotifications.playError();
        showFormError('Please fill in all required fields.');
        
        if (firstInvalidField) {
            focusField(firstInvalidField);
        }
        
        return false;
    }
    
    return true;
}

/**
 * Validate form data integrity
 * @param {HTMLElement} form - The form element
 * @returns {boolean} True if form data is valid
 */
function validateFormData(form) {
    // Check if amount field exists and has valid value
    const amountField = form.querySelector('[name="amount"]');
    if (amountField) {
        const amount = parseFloat(amountField.value);
        if (isNaN(amount) || amount <= 0) {
            showFormError('Invalid amount. Please refresh the page and try again.');
            return false;
        }
    }
    
    // Check if receiver ID exists
    const receiverField = form.querySelector('[name="receiver_id"]');
    if (!receiverField || !receiverField.value) {
        showFormError('Invalid recipient information. Please refresh the page and try again.');
        return false;
    }
    
    // Check if user share ID exists
    const shareField = form.querySelector('[name="user_share_id"]');
    if (!shareField || !shareField.value) {
        showFormError('Invalid share information. Please refresh the page and try again.');
        return false;
    }
    
    return true;
}

/**
 * Submit form with loading state management
 * @param {HTMLElement} form - The form element
 * @param {HTMLElement} buttonElement - The submit button
 * @returns {boolean} True if submission started successfully
 */
function submitFormWithLoadingState(form, buttonElement) {
    try {
        // Show loading state
        setButtonLoadingState(buttonElement, true);
        
        // Add loading class to form
        form.classList.add('payment-form-loading');
        
        console.log('Submitting form...');
        form.submit();
        console.log('Form submitted successfully');
        
        // Schedule delayed notification sound after form submission
        console.log('Payment form submitted - scheduling delayed notification sound');
        window.audioNotifications.playNotificationSoundAfterDelay(3000);
        
        return true;
        
    } catch (error) {
        console.error('Form submission error:', error);
        showFormError('Error submitting form: ' + error.message);
        
        // Play error sound immediately for submission errors
        window.audioNotifications.playError();
        
        // Reset button state on error
        setButtonLoadingState(buttonElement, false);
        form.classList.remove('payment-form-loading');
        
        return false;
    }
}

/**
 * Set button loading state
 * @param {HTMLElement} buttonElement - The button element
 * @param {boolean} isLoading - Whether to show loading state
 */
function setButtonLoadingState(buttonElement, isLoading) {
    if (!buttonElement) return;
    
    const spinner = buttonElement.querySelector('.spinner-border');
    
    if (isLoading) {
        buttonElement.disabled = true;
        if (spinner) {
            spinner.classList.remove('d-none');
        }
        
        // Save original content if not already saved
        if (!buttonElement.dataset.originalContent) {
            buttonElement.dataset.originalContent = buttonElement.innerHTML;
        }
        
        buttonElement.innerHTML = '<i class="ri-loader-2-line me-2 spinner-border spinner-border-sm"></i>Submitting...';
    } else {
        buttonElement.disabled = false;
        if (spinner) {
            spinner.classList.add('d-none');
        }
        
        // Restore original content
        if (buttonElement.dataset.originalContent) {
            buttonElement.innerHTML = buttonElement.dataset.originalContent;
        }
    }
}

/**
 * Focus on a field and scroll it into view
 * @param {HTMLElement} field - The field to focus
 */
function focusField(field) {
    if (field) {
        field.focus();
        field.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}

/**
 * Show form error message
 * @param {string} message - The error message to display
 */
function showFormError(message) {
    alert(message);
    console.error('Form Error:', message);
}

/**
 * Copy text to clipboard with visual feedback
 * @param {string} text - Text to copy
 */
function copyToClipboard(text) {
    if (!text || text === 'Not Set') {
        showFormError('No valid number to copy');
        return;
    }
    
    // Use modern clipboard API if available
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(function() {
            showCopySuccess();
        }).catch(function(err) {
            console.error('Failed to copy: ', err);
            fallbackCopyTextToClipboard(text);
        });
    } else {
        // Fallback for older browsers
        fallbackCopyTextToClipboard(text);
    }
}

/**
 * Fallback copy method for older browsers
 * @param {string} text - Text to copy
 */
function fallbackCopyTextToClipboard(text) {
    const textArea = document.createElement("textarea");
    textArea.value = text;
    
    // Avoid scrolling to bottom
    textArea.style.top = "0";
    textArea.style.left = "0";
    textArea.style.position = "fixed";
    
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    
    try {
        const successful = document.execCommand('copy');
        if (successful) {
            showCopySuccess();
        } else {
            showFormError('Failed to copy to clipboard');
        }
    } catch (err) {
        console.error('Fallback: Could not copy text: ', err);
        showFormError('Failed to copy to clipboard');
    }
    
    document.body.removeChild(textArea);
}

/**
 * Show visual feedback for successful copy
 */
function showCopySuccess() {
    const btn = event.target.closest('button');
    if (!btn) return;
    
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<i class="ri-check-line me-1"></i>Copied!';
    btn.classList.remove('btn-outline-success');
    btn.classList.add('btn-success', 'copied');
    
    setTimeout(function() {
        btn.innerHTML = originalHtml;
        btn.classList.remove('btn-success', 'copied');
        btn.classList.add('btn-outline-success');
    }, 2000);
    
    // Play success sound
    window.audioNotifications.playSuccess();
}

/**
 * Initialize payment form functionality on DOM ready
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('=== PAYMENT FORM MODULE INITIALIZATION ===');
    
    // Initialize form debugging
    initializeFormDebugging();
    
    // Initialize audio notifications debugging
    initializeAudioDebugging();
    
    // Initialize form event listeners
    initializeFormEventListeners();
    
    console.log('Payment form module initialized successfully');
});

/**
 * Initialize form debugging information
 */
function initializeFormDebugging() {
    console.log('=== FORM DEBUGGING INFO ===');
    document.querySelectorAll('.payment-form').forEach(form => {
        console.log('Found payment form:', form.id);
        console.log('Form action:', form.action);
        
        const txsIdField = form.querySelector('[name="txs_id"]');
        console.log('Transaction ID field for', form.id, ':', txsIdField);
        if (txsIdField) {
            console.log('Field ID:', txsIdField.id);
            console.log('Field name:', txsIdField.name);
            console.log('Field required:', txsIdField.hasAttribute('required'));
        }
    });
}

/**
 * Initialize audio notifications debugging
 */
function initializeAudioDebugging() {
    console.log('=== AUDIO NOTIFICATIONS DEBUG ===');
    console.log('Audio notifications object:', window.audioNotifications);
    if (window.audioNotifications) {
        console.log('Audio enabled:', window.audioNotifications.isEnabled);
        
        // Test audio context support
        if (typeof AudioContext !== 'undefined' || typeof webkitAudioContext !== 'undefined') {
            console.log('Audio context supported');
        } else {
            console.log('Audio context not supported');
        }
    }
}

/**
 * Initialize form event listeners with throttling to prevent performance issues
 */
function initializeFormEventListeners() {
    // Add real-time validation for transaction ID fields with throttling
    document.querySelectorAll('.transaction-input').forEach(field => {
        let inputTimeout;
        
        field.addEventListener('input', function(e) {
            // Throttle input validation to prevent excessive calls
            clearTimeout(inputTimeout);
            inputTimeout = setTimeout(() => {
                validateTransactionIdRealTime(e.target);
            }, 150);
        });
        
        field.addEventListener('blur', function(e) {
            // Clear any pending throttled validation
            clearTimeout(inputTimeout);
            validateTransactionIdRealTime(e.target);
        });
    });
    
    // Add form submission prevention for invalid forms
    document.querySelectorAll('.payment-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('Form submission intercepted - use submitPaymentForm() instead');
        });
    });
    
    // Optimize modal opening performance
    document.querySelectorAll('.payment-modal').forEach(modal => {
        modal.addEventListener('show.bs.modal', function() {
            // Force reflow before showing to prevent flickering
            modal.offsetHeight;
        });
        
        modal.addEventListener('shown.bs.modal', function() {
            // Focus first input field after modal is fully shown
            const firstInput = modal.querySelector('.transaction-input');
            if (firstInput) {
                setTimeout(() => firstInput.focus(), 100);
            }
        });
    });
}

/**
 * Real-time validation for transaction ID field
 * @param {HTMLElement} field - The transaction ID input field
 */
function validateTransactionIdRealTime(field) {
    if (!field) return;
    
    const value = field.value.trim();
    
    if (value.length === 0) {
        field.classList.remove('is-valid', 'is-invalid');
        return;
    }
    
    const pattern = /^[A-Za-z0-9\s\-_.]{4,30}$/;
    
    if (pattern.test(value)) {
        field.classList.remove('is-invalid');
        field.classList.add('is-valid');
    } else {
        field.classList.remove('is-valid');
        field.classList.add('is-invalid');
    }
}

// Export functions for global access
window.submitPaymentForm = submitPaymentForm;
window.copyToClipboard = copyToClipboard;
