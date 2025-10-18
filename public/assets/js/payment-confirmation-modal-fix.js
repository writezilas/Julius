/**
 * Payment Confirmation Modal JavaScript Fixes
 * Handles dynamic behavior, mobile scrolling, and enforces light mode
 */

(function() {
    'use strict';
    
    let isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
    let isAndroid = /Android/.test(navigator.userAgent);
    let isMobile = window.innerWidth <= 768;
    
    // Track keyboard visibility
    let keyboardVisible = false;
    let originalViewportHeight = window.innerHeight;
    
    /**
     * Force light mode styles on modal elements
     */
    function enforceLightModeStyles(modal) {
        if (!modal) return;
        
        // Add light mode class to modal
        modal.classList.add('payment-confirmation-modal');
        
        // Force color scheme
        modal.style.setProperty('color-scheme', 'light', 'important');
        
        // Ensure all child elements inherit light mode
        const allElements = modal.querySelectorAll('*');
        allElements.forEach(element => {
            element.style.setProperty('color-scheme', 'light', 'important');
        });
        
        // Force background colors on key elements
        const modalContent = modal.querySelector('.modal-content, .payment-modal-content');
        if (modalContent) {
            modalContent.style.setProperty('background-color', '#ffffff', 'important');
            modalContent.style.setProperty('color', '#212529', 'important');
        }
        
        const modalBody = modal.querySelector('.modal-body, .payment-modal-body');
        if (modalBody) {
            modalBody.style.setProperty('background-color', '#ffffff', 'important');
            modalBody.style.setProperty('color', '#212529', 'important');
        }
    }
    
    /**
     * Apply mobile scrolling fixes
     */
    function applyMobileScrollingFixes(modal) {
        if (!modal || !isMobile) return;
        
        const modalDialog = modal.querySelector('.modal-dialog');
        const modalContent = modal.querySelector('.modal-content, .payment-modal-content');
        const modalBody = modal.querySelector('.modal-body, .payment-modal-body');
        
        if (modalDialog) {
            modalDialog.style.setProperty('max-height', 'none', 'important');
            modalDialog.style.setProperty('height', 'auto', 'important');
            modalDialog.style.setProperty('display', 'flex', 'important');
            modalDialog.style.setProperty('flex-direction', 'column', 'important');
            modalDialog.style.setProperty('max-width', '95vw', 'important');
        }
        
        if (modalContent) {
            modalContent.style.setProperty('max-height', 'none', 'important');
            modalContent.style.setProperty('height', 'auto', 'important');
            modalContent.style.setProperty('display', 'flex', 'important');
            modalContent.style.setProperty('flex-direction', 'column', 'important');
            modalContent.style.setProperty('flex', '1 1 auto', 'important');
            modalContent.style.setProperty('overflow', 'hidden', 'important');
        }
        
        if (modalBody) {
            modalBody.style.setProperty('max-height', 'none', 'important');
            modalBody.style.setProperty('height', 'auto', 'important');
            modalBody.style.setProperty('flex', '1 1 auto', 'important');
            modalBody.style.setProperty('overflow-y', 'auto', 'important');
            modalBody.style.setProperty('-webkit-overflow-scrolling', 'touch', 'important');
            modalBody.style.setProperty('overscroll-behavior', 'contain', 'important');
        }
    }
    
    /**
     * Handle keyboard visibility detection (iOS/Android)
     */
    function handleKeyboardVisibility() {
        if (!isMobile) return;
        
        const currentViewportHeight = window.innerHeight;
        const heightDifference = originalViewportHeight - currentViewportHeight;
        
        // Threshold for detecting virtual keyboard (usually 150px+)
        const keyboardThreshold = 150;
        
        if (heightDifference > keyboardThreshold && !keyboardVisible) {
            keyboardVisible = true;
            document.body.classList.add('keyboard-visible');
            
            // Find active payment confirmation modal
            const activeModal = document.querySelector('.payment-confirmation-modal.show');
            if (activeModal) {
                activeModal.classList.add('keyboard-visible');
            }
        } else if (heightDifference <= keyboardThreshold && keyboardVisible) {
            keyboardVisible = false;
            document.body.classList.remove('keyboard-visible');
            
            // Remove keyboard class from all modals
            document.querySelectorAll('.payment-confirmation-modal').forEach(modal => {
                modal.classList.remove('keyboard-visible');
            });
        }
    }
    
    /**
     * Prevent body scroll when modal is open
     */
    function handleBodyScrollLock(modal, isOpening) {
        if (!isMobile) return;
        
        if (isOpening) {
            document.body.classList.add('payment-confirmation-modal-open');
            document.body.style.setProperty('overflow', 'hidden', 'important');
            document.body.style.setProperty('position', 'fixed', 'important');
            document.body.style.setProperty('width', '100%', 'important');
        } else {
            document.body.classList.remove('payment-confirmation-modal-open');
            document.body.style.removeProperty('overflow');
            document.body.style.removeProperty('position');
            document.body.style.removeProperty('width');
        }
    }
    
    /**
     * Initialize modal fixes when modal is shown
     */
    function initializeModalFixes(modal) {
        enforceLightModeStyles(modal);
        applyMobileScrollingFixes(modal);
        handleBodyScrollLock(modal, true);
        
        // Add touch-specific behaviors for iOS/Android
        if (isIOS || isAndroid) {
            const modalBody = modal.querySelector('.modal-body, .payment-modal-body');
            if (modalBody) {
                // Prevent bounce scrolling on iOS
                modalBody.addEventListener('touchstart', function(e) {
                    if (modalBody.scrollTop === 0) {
                        modalBody.scrollTop = 1;
                    } else if (modalBody.scrollHeight === modalBody.scrollTop + modalBody.offsetHeight) {
                        modalBody.scrollTop = modalBody.scrollTop - 1;
                    }
                });
                
                // Enable momentum scrolling
                modalBody.style.setProperty('-webkit-overflow-scrolling', 'touch', 'important');
            }
        }
        
        console.log('Payment confirmation modal fixes applied');
    }
    
    /**
     * Clean up when modal is hidden
     */
    function cleanupModalFixes(modal) {
        handleBodyScrollLock(modal, false);
    }
    
    /**
     * Watch for theme changes and re-enforce light mode
     */
    function watchThemeChanges() {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'attributes' && 
                    (mutation.attributeName === 'data-layout-mode' || 
                     mutation.attributeName === 'class')) {
                    
                    // Re-enforce light mode on payment confirmation modals
                    document.querySelectorAll('.payment-confirmation-modal').forEach(modal => {
                        enforceLightModeStyles(modal);
                    });
                }
            });
        });
        
        observer.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['data-layout-mode', 'class']
        });
        
        observer.observe(document.body, {
            attributes: true,
            attributeFilter: ['data-layout-mode', 'class']
        });
    }
    
    /**
     * Initialize all event listeners and observers
     */
    function initialize() {
        // Update mobile detection on resize
        window.addEventListener('resize', function() {
            isMobile = window.innerWidth <= 768;
            handleKeyboardVisibility();
        });
        
        // Handle viewport changes for keyboard detection
        if (isMobile) {
            window.addEventListener('resize', handleKeyboardVisibility);
            
            // Initial viewport height
            originalViewportHeight = window.innerHeight;
        }
        
        // Watch for Bootstrap modal events
        document.addEventListener('show.bs.modal', function(e) {
            const modal = e.target;
            if (modal.classList.contains('payment-confirmation-modal') ||
                modal.querySelector('.payment-confirmation-modal') ||
                modal.classList.contains('modal') && modal.id && modal.id.includes('payment')) {
                
                // Ensure modal has the correct class
                modal.classList.add('payment-confirmation-modal');
                
                setTimeout(() => initializeModalFixes(modal), 50);
            }
        });
        
        document.addEventListener('shown.bs.modal', function(e) {
            const modal = e.target;
            if (modal.classList.contains('payment-confirmation-modal')) {
                // Double-check fixes are applied after animation
                setTimeout(() => {
                    enforceLightModeStyles(modal);
                    applyMobileScrollingFixes(modal);
                }, 100);
            }
        });
        
        document.addEventListener('hide.bs.modal', function(e) {
            const modal = e.target;
            if (modal.classList.contains('payment-confirmation-modal')) {
                cleanupModalFixes(modal);
            }
        });
        
        // Watch for dynamically added modals
        const modalObserver = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === Node.ELEMENT_NODE) {
                        // Check if added node is a payment modal
                        if (node.classList && 
                            (node.classList.contains('payment-confirmation-modal') ||
                             (node.classList.contains('modal') && node.innerHTML.includes('payment')))) {
                            
                            node.classList.add('payment-confirmation-modal');
                            initializeModalFixes(node);
                        }
                        
                        // Check if added node contains payment modals
                        const paymentModals = node.querySelectorAll && 
                            node.querySelectorAll('.modal[class*="payment"], .payment-confirmation-modal');
                        if (paymentModals) {
                            paymentModals.forEach(modal => {
                                modal.classList.add('payment-confirmation-modal');
                                initializeModalFixes(modal);
                            });
                        }
                    }
                });
            });
        });
        
        modalObserver.observe(document.body, {
            childList: true,
            subtree: true
        });
        
        // Watch for theme changes
        watchThemeChanges();
        
        // Apply fixes to existing modals
        document.querySelectorAll('.payment-confirmation-modal, .modal[class*="payment"]').forEach(modal => {
            modal.classList.add('payment-confirmation-modal');
            if (modal.classList.contains('show')) {
                initializeModalFixes(modal);
            }
        });
        
        console.log('Payment confirmation modal fix script initialized');
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialize);
    } else {
        initialize();
    }
    
    // Also initialize when window loads (fallback)
    window.addEventListener('load', initialize);
    
    // Debug helpers (remove in production)
    window.PaymentModalFix = {
        enforceLightMode: enforceLightModeStyles,
        applyMobileFixes: applyMobileScrollingFixes,
        isKeyboardVisible: () => keyboardVisible,
        isMobile: () => isMobile,
        version: '1.0.0'
    };
    
})();