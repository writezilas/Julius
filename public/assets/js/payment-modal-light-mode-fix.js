/**
 * Payment Modal Light Mode Fix - JavaScript
 * Ensures payment modals are always displayed in light mode
 * Applies dynamic fixes and monitors for new payment modals
 */

(function() {
    'use strict';
    
    // Configuration
    const PAYMENT_MODAL_SELECTORS = [
        '.clean-payment-modal',
        '.payment-modal',
        '.payment-confirmation-modal',
        '.modal[id*="payment"]',
        '.modal[id*="Payment"]',
        '.modal[id*="soldShareDetails"]',
        '.modal[class*="payment"]',
        '.modal.clean-payment-modal',
        '.modal.afresh-payment-modal'
    ];
    
    /**
     * Apply light mode styling to a payment modal
     * @param {HTMLElement} modal - The modal element
     */
    function applyLightModeToModal(modal) {
        if (!modal || modal.hasAttribute('data-light-mode-applied')) {
            return;
        }
        
        // Skip new payment confirmation modals - they handle their own theming
        if (modal.classList.contains('new-payment-confirmation-modal')) {
            return;
        }
        
        // Mark as processed
        modal.setAttribute('data-light-mode-applied', 'true');
        
        // Force light mode attributes
        modal.style.setProperty('color-scheme', 'light', 'important');
        modal.style.setProperty('--bs-body-bg', '#ffffff', 'important');
        modal.style.setProperty('--bs-body-color', '#212529', 'important');
        
        // Apply to modal content
        const modalContent = modal.querySelector('.modal-content');
        if (modalContent) {
            modalContent.style.setProperty('background-color', '#ffffff', 'important');
            modalContent.style.setProperty('color', '#212529', 'important');
            modalContent.style.setProperty('border', '1px solid #dee2e6', 'important');
        }
        
        // Apply to modal body
        const modalBody = modal.querySelector('.modal-body');
        if (modalBody) {
            modalBody.style.setProperty('background-color', '#ffffff', 'important');
            modalBody.style.setProperty('color', '#212529', 'important');
        }
        
        // Apply to all text elements
        const textElements = modal.querySelectorAll('h1, h2, h3, h4, h5, h6, p, span, div, label, small');
        textElements.forEach(element => {
            if (!element.closest('.modal-header')) { // Skip header elements
                element.style.setProperty('color', '#212529', 'important');
                element.style.setProperty('text-shadow', 'none', 'important');
                element.style.setProperty('opacity', '1', 'important');
                element.style.setProperty('filter', 'none', 'important');
            }
        });
        
        // Apply to form controls
        const formControls = modal.querySelectorAll('input, textarea, select');
        formControls.forEach(control => {
            control.style.setProperty('background-color', '#ffffff', 'important');
            control.style.setProperty('color', '#212529', 'important');
            control.style.setProperty('border', '1px solid #ced4da', 'important');
        });
        
        // Apply to cards and containers
        const containers = modal.querySelectorAll('.card, .payment-summary-card, .seller-card, .instructions-card, .summary-item');
        containers.forEach(container => {
            if (container.classList.contains('summary-item')) {
                container.style.setProperty('background-color', '#ffffff', 'important');
            } else {
                container.style.setProperty('background-color', '#f8f9fa', 'important');
            }
            container.style.setProperty('color', '#212529', 'important');
            container.style.setProperty('border', '1px solid #dee2e6', 'important');
        });
        
        // Apply to modal footer
        const modalFooter = modal.querySelector('.modal-footer');
        if (modalFooter) {
            modalFooter.style.setProperty('background-color', '#f8f9fa', 'important');
            modalFooter.style.setProperty('border-top', '1px solid #dee2e6', 'important');
        }
        
        console.log('Applied light mode fix to payment modal:', modal.id || modal.className);
    }
    
    /**
     * Apply light mode to all existing payment modals
     */
    function applyLightModeToExistingModals() {
        PAYMENT_MODAL_SELECTORS.forEach(selector => {
            const modals = document.querySelectorAll(selector);
            modals.forEach(modal => {
                // Skip new payment confirmation modals - they handle their own theming
                if (!modal.classList.contains('new-payment-confirmation-modal')) {
                    applyLightModeToModal(modal);
                }
            });
        });
    }
    
    /**
     * Create and inject the CSS fix if not already present
     */
    function injectCSSFix() {
        if (document.querySelector('#payment-modal-light-mode-fix')) {
            return; // Already injected
        }
        
        const linkElement = document.createElement('link');
        linkElement.id = 'payment-modal-light-mode-fix';
        linkElement.rel = 'stylesheet';
        linkElement.type = 'text/css';
        linkElement.href = '/assets/css/payment-modal-light-mode-fix.css';
        
        // Insert with high priority (before other stylesheets if possible)
        const firstStylesheet = document.querySelector('link[rel="stylesheet"]');
        if (firstStylesheet) {
            firstStylesheet.parentNode.insertBefore(linkElement, firstStylesheet);
        } else {
            document.head.appendChild(linkElement);
        }
        
        console.log('Injected payment modal light mode CSS fix');
    }
    
    /**
     * Observer for dynamically added modals
     */
    function setupMutationObserver() {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList') {
                    mutation.addedNodes.forEach(function(node) {
                        if (node.nodeType === Node.ELEMENT_NODE) {
                            // Check if the added node is a payment modal
                            PAYMENT_MODAL_SELECTORS.forEach(selector => {
                                if (node.matches && node.matches(selector)) {
                                    applyLightModeToModal(node);
                                }
                                
                                // Check for payment modals within the added node
                                const childModals = node.querySelectorAll ? node.querySelectorAll(selector) : [];
                                childModals.forEach(modal => {
                                    applyLightModeToModal(modal);
                                });
                            });
                        }
                    });
                }
                
                // Check for attribute changes that might affect theming
                if (mutation.type === 'attributes' && 
                    (mutation.attributeName === 'data-layout-mode' || 
                     mutation.attributeName === 'class')) {
                    // Re-apply fixes to all payment modals
                    setTimeout(applyLightModeToExistingModals, 100);
                }
            });
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true,
            attributes: true,
            attributeFilter: ['data-layout-mode', 'class']
        });
        
        return observer;
    }
    
    /**
     * Force override any Bootstrap modal events that might reset styling
     */
    function setupModalEventListeners() {
        document.addEventListener('show.bs.modal', function(event) {
            const modal = event.target;
            PAYMENT_MODAL_SELECTORS.forEach(selector => {
                if (modal.matches(selector)) {
                    // Apply fix when modal is about to show
                    setTimeout(() => applyLightModeToModal(modal), 10);
                }
            });
        });
        
        document.addEventListener('shown.bs.modal', function(event) {
            const modal = event.target;
            PAYMENT_MODAL_SELECTORS.forEach(selector => {
                if (modal.matches(selector)) {
                    // Re-apply fix after modal is fully shown
                    setTimeout(() => applyLightModeToModal(modal), 100);
                }
            });
        });
    }
    
    /**
     * Handle theme changes
     */
    function handleThemeChanges() {
        // Listen for theme changes
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'attributes' && 
                    mutation.attributeName === 'data-layout-mode') {
                    
                    // Theme changed, re-apply light mode to all payment modals
                    console.log('Theme change detected, re-applying payment modal light mode fixes');
                    setTimeout(applyLightModeToExistingModals, 200);
                }
            });
        });
        
        // Watch for theme changes on html or body
        [document.documentElement, document.body].forEach(element => {
            if (element) {
                observer.observe(element, {
                    attributes: true,
                    attributeFilter: ['data-layout-mode', 'data-theme', 'class']
                });
            }
        });
    }
    
    /**
     * Initialize the light mode fix system
     */
    function initialize() {
        console.log('Initializing payment modal light mode fix...');
        
        // Inject CSS fix
        injectCSSFix();
        
        // Apply to existing modals
        applyLightModeToExistingModals();
        
        // Setup observers for new modals
        setupMutationObserver();
        
        // Setup event listeners
        setupModalEventListeners();
        
        // Handle theme changes
        handleThemeChanges();
        
        // Periodic check (safety net)
        setInterval(applyLightModeToExistingModals, 5000);
        
        console.log('Payment modal light mode fix initialized successfully');
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialize);
    } else {
        initialize();
    }
    
    // Also initialize on window load as a fallback
    window.addEventListener('load', function() {
        setTimeout(initialize, 500);
    });
    
    // Expose global function for manual fixing
    window.fixPaymentModalLightMode = function() {
        applyLightModeToExistingModals();
    };
    
    // Handle page visibility changes (in case modal was opened while tab was hidden)
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) {
            setTimeout(applyLightModeToExistingModals, 100);
        }
    });
})();