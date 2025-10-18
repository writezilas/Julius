/**
 * Modern Payment Confirmation Modal JavaScript
 * Enhanced mobile support with keyboard handling and responsiveness
 */

(function() {
    'use strict';

    let modalInstance = null;
    let keyboardHeightCache = 0;

    // Initialize modal functionality
    function initializeModal() {
        const modal = document.querySelector('#modern-payment-confirmation-modal');
        if (!modal) return;

        // Enable mobile optimizations
        if (isMobileDevice()) {
            enableMobileOptimizations(modal);
        }

        // Initialize form submission handling
        initializeFormHandling(modal);

        // Initialize close button handling
        initializeCloseHandling(modal);

        // Store modal reference
        modalInstance = modal;
    }

    // Check if device is mobile
    function isMobileDevice() {
        return window.innerWidth <= 768 || 
               /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    }

    // Enable mobile-specific optimizations
    function enableMobileOptimizations(modal) {
        // Viewport handling for mobile browsers
        setupViewportHandling(modal);
        
        // Keyboard visibility detection
        setupKeyboardHandling(modal);
        
        // Touch and scroll optimization
        setupTouchOptimizations(modal);

        // iOS specific fixes
        if (isIOS()) {
            setupIOSOptimizations(modal);
        }
    }

    // Setup viewport handling
    function setupViewportHandling(modal) {
        // Prevent zoom on input focus for iOS
        const inputs = modal.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                preventZoom();
            });

            input.addEventListener('blur', function() {
                restoreZoom();
            });
        });

        // Handle orientation change
        window.addEventListener('orientationchange', function() {
            setTimeout(() => {
                adjustModalForOrientation(modal);
            }, 100);
        });

        // Handle window resize
        let resizeTimeout;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                adjustModalForResize(modal);
            }, 150);
        });
    }

    // Setup keyboard handling using Visual Viewport API or fallback
    function setupKeyboardHandling(modal) {
        if ('visualViewport' in window) {
            // Modern approach using Visual Viewport API
            window.visualViewport.addEventListener('resize', () => {
                handleKeyboardToggle(modal);
            });
        } else {
            // Fallback for older browsers
            setupKeyboardFallback(modal);
        }
    }

    // Fallback keyboard detection for older browsers
    function setupKeyboardFallback(modal) {
        let initialViewportHeight = window.innerHeight;
        
        window.addEventListener('resize', function() {
            const currentHeight = window.innerHeight;
            const heightDifference = initialViewportHeight - currentHeight;
            
            if (heightDifference > 150) {
                // Keyboard likely visible
                showKeyboard(modal);
            } else {
                // Keyboard likely hidden
                hideKeyboard(modal);
            }
        });

        // Focus/blur detection for inputs
        const inputs = modal.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.addEventListener('focus', () => {
                setTimeout(() => showKeyboard(modal), 300);
            });
            
            input.addEventListener('blur', () => {
                setTimeout(() => hideKeyboard(modal), 300);
            });
        });
    }

    // Handle keyboard visibility toggle
    function handleKeyboardToggle(modal) {
        if (!window.visualViewport) return;

        const keyboardHeight = window.innerHeight - window.visualViewport.height;
        
        if (keyboardHeight > 150) {
            showKeyboard(modal, keyboardHeight);
        } else {
            hideKeyboard(modal);
        }
    }

    // Show keyboard state
    function showKeyboard(modal, keyboardHeight = 0) {
        modal.classList.add('keyboard-visible');
        document.body.classList.add('keyboard-visible');
        
        keyboardHeightCache = keyboardHeight;
        
        // Adjust modal content for keyboard
        adjustModalForKeyboard(modal, true, keyboardHeight);
    }

    // Hide keyboard state  
    function hideKeyboard(modal) {
        modal.classList.remove('keyboard-visible');
        document.body.classList.remove('keyboard-visible');
        
        // Restore modal layout
        adjustModalForKeyboard(modal, false);
    }

    // Adjust modal for keyboard visibility
    function adjustModalForKeyboard(modal, keyboardVisible, keyboardHeight = 0) {
        const modalContent = modal.querySelector('.modern-payment-content');
        const modalBody = modal.querySelector('.modern-payment-body');
        const modalFooter = modal.querySelector('.modern-payment-footer');
        
        if (!modalContent || !modalBody || !modalFooter) return;

        if (keyboardVisible) {
            // Keyboard is visible - adjust layout
            if (keyboardHeight > 0) {
                modalFooter.style.bottom = keyboardHeight + 'px';
                modalBody.style.paddingBottom = (modalFooter.offsetHeight + keyboardHeight + 20) + 'px';
            }
            
            // Ensure current input is visible
            const activeElement = document.activeElement;
            if (activeElement && (activeElement.tagName === 'INPUT' || activeElement.tagName === 'TEXTAREA')) {
                setTimeout(() => {
                    activeElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                }, 100);
            }
        } else {
            // Keyboard is hidden - restore layout
            modalFooter.style.bottom = '';
            modalBody.style.paddingBottom = '';
        }
    }

    // Setup touch optimizations
    function setupTouchOptimizations(modal) {
        const modalBody = modal.querySelector('.modern-payment-body');
        if (!modalBody) return;

        // Enable momentum scrolling on iOS
        modalBody.style.webkitOverflowScrolling = 'touch';
        
        // Prevent scroll chaining
        modalBody.addEventListener('touchstart', function(e) {
            const scrollTop = modalBody.scrollTop;
            const scrollHeight = modalBody.scrollHeight;
            const height = modalBody.clientHeight;
            const contentHeight = scrollHeight - height;

            if (scrollTop === 0) {
                modalBody.scrollTop = 1;
            } else if (scrollTop === contentHeight) {
                modalBody.scrollTop = contentHeight - 1;
            }
        });

        // Smooth scroll to top on modal open
        modal.addEventListener('shown.bs.modal', function() {
            modalBody.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }

    // Check if iOS
    function isIOS() {
        return /iPad|iPhone|iPod/.test(navigator.userAgent);
    }

    // iOS specific optimizations
    function setupIOSOptimizations(modal) {
        // Fix iOS viewport height issues
        function setIOSViewportHeight() {
            const vh = window.innerHeight * 0.01;
            document.documentElement.style.setProperty('--vh', `${vh}px`);
        }

        setIOSViewportHeight();
        window.addEventListener('resize', setIOSViewportHeight);
        window.addEventListener('orientationchange', () => {
            setTimeout(setIOSViewportHeight, 500);
        });

        // Prevent bounce scroll on modal background
        modal.addEventListener('touchmove', function(e) {
            if (e.target === modal) {
                e.preventDefault();
            }
        });
    }

    // Prevent zoom on input focus
    function preventZoom() {
        const viewport = document.querySelector('meta[name="viewport"]');
        if (viewport) {
            viewport.setAttribute('content', 
                'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no'
            );
        }
    }

    // Restore zoom capability
    function restoreZoom() {
        setTimeout(() => {
            const viewport = document.querySelector('meta[name="viewport"]');
            if (viewport) {
                viewport.setAttribute('content', 
                    'width=device-width, initial-scale=1.0'
                );
            }
        }, 500);
    }

    // Adjust modal for orientation change
    function adjustModalForOrientation(modal) {
        const modalContent = modal.querySelector('.modern-payment-content');
        if (!modalContent) return;

        // Reset any dynamic height adjustments
        modalContent.style.height = '';
        
        // Force layout recalculation
        modalContent.offsetHeight;

        // Reapply mobile styles if needed
        if (isMobileDevice()) {
            modalContent.style.height = '100vh';
        }
    }

    // Adjust modal for resize
    function adjustModalForResize(modal) {
        if (!isMobileDevice()) {
            // Desktop - restore normal modal behavior
            const modalContent = modal.querySelector('.modern-payment-content');
            if (modalContent) {
                modalContent.style.height = '';
            }
            hideKeyboard(modal);
        } else {
            // Mobile - ensure proper mobile layout
            adjustModalForOrientation(modal);
        }
    }

    // Initialize form handling
    function initializeFormHandling(modal) {
        const form = modal.querySelector('form');
        const confirmButton = modal.querySelector('.btn-primary');
        
        if (!form || !confirmButton) return;

        // Handle form submission
        form.addEventListener('submit', function(e) {
            // Disable submit button to prevent double submission
            confirmButton.disabled = true;
            confirmButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

            // If using AJAX, prevent default and handle manually
            // e.preventDefault();
            // handleAjaxSubmission(form, modal);
        });

        // Enable/disable submit button based on required fields
        const requiredFields = form.querySelectorAll('[required]');
        if (requiredFields.length > 0) {
            function validateForm() {
                const allValid = Array.from(requiredFields).every(field => {
                    return field.value.trim() !== '';
                });
                
                confirmButton.disabled = !allValid;
            }

            requiredFields.forEach(field => {
                field.addEventListener('input', validateForm);
                field.addEventListener('blur', validateForm);
            });

            // Initial validation
            validateForm();
        }
    }

    // Initialize close button handling
    function initializeCloseHandling(modal) {
        const closeButtons = modal.querySelectorAll('[data-bs-dismiss="modal"]');
        
        closeButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Clean up keyboard state
                hideKeyboard(modal);
                
                // Reset form if needed
                const form = modal.querySelector('form');
                if (form) {
                    const confirmButton = modal.querySelector('.btn-primary');
                    if (confirmButton) {
                        confirmButton.disabled = false;
                        confirmButton.innerHTML = confirmButton.getAttribute('data-original-text') || 
                                                'Confirm Payment';
                    }
                }
            });
        });

        // Handle ESC key
        modal.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                hideKeyboard(modal);
            }
        });
    }

    // Handle AJAX form submission (optional)
    function handleAjaxSubmission(form, modal) {
        const formData = new FormData(form);
        const confirmButton = modal.querySelector('.btn-primary');
        
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Handle success
                showSuccessMessage(modal, data.message);
                setTimeout(() => {
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    } else {
                        modal.querySelector('[data-bs-dismiss="modal"]').click();
                    }
                }, 2000);
            } else {
                // Handle error
                showErrorMessage(modal, data.message || 'An error occurred');
                confirmButton.disabled = false;
                confirmButton.innerHTML = confirmButton.getAttribute('data-original-text') || 
                                        'Confirm Payment';
            }
        })
        .catch(error => {
            console.error('Payment confirmation error:', error);
            showErrorMessage(modal, 'Network error. Please try again.');
            confirmButton.disabled = false;
            confirmButton.innerHTML = confirmButton.getAttribute('data-original-text') || 
                                    'Confirm Payment';
        });
    }

    // Show success message
    function showSuccessMessage(modal, message) {
        const alertContainer = modal.querySelector('.status-alert');
        if (alertContainer) {
            alertContainer.className = 'status-alert success-alert';
            alertContainer.querySelector('h6').textContent = 'Payment Confirmed!';
            alertContainer.querySelector('p').textContent = message;
            alertContainer.querySelector('i').className = 'fas fa-check';
        }
    }

    // Show error message
    function showErrorMessage(modal, message) {
        const alertContainer = modal.querySelector('.status-alert');
        if (alertContainer) {
            alertContainer.className = 'status-alert warning-alert';
            alertContainer.querySelector('h6').textContent = 'Error';
            alertContainer.querySelector('p').textContent = message;
            alertContainer.querySelector('i').className = 'fas fa-exclamation-triangle';
        }
    }

    // Public API
    window.ModernPaymentModal = {
        init: initializeModal,
        showKeyboard: function() {
            if (modalInstance) showKeyboard(modalInstance);
        },
        hideKeyboard: function() {
            if (modalInstance) hideKeyboard(modalInstance);
        },
        adjustForKeyboard: function(visible, height = 0) {
            if (modalInstance) adjustModalForKeyboard(modalInstance, visible, height);
        }
    };

    // Auto-initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeModal);
    } else {
        initializeModal();
    }

    // Reinitialize if modal is dynamically added
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList') {
                const addedNodes = Array.from(mutation.addedNodes);
                addedNodes.forEach(function(node) {
                    if (node.nodeType === Node.ELEMENT_NODE) {
                        if (node.id === 'modern-payment-confirmation-modal' ||
                            node.querySelector('#modern-payment-confirmation-modal')) {
                            initializeModal();
                        }
                    }
                });
            }
        });
    });

    observer.observe(document.body, {
        childList: true,
        subtree: true
    });

})();