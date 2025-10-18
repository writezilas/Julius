/**
 * Mobile Payment Modal Fix
 * Universal script to fix mobile scrolling issues for any payment confirmation modal
 * Works with both modern-payment-confirmation-form and payment-confirmation-modal
 */

(function() {
    'use strict';
    
    let isMobile = window.innerWidth <= 768;
    let isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
    let isAndroid = /Android/.test(navigator.userAgent);
    
    // Update mobile detection on resize
    window.addEventListener('resize', () => {
        isMobile = window.innerWidth <= 768;
    });
    
    /**
     * Apply mobile adjustments to any payment modal
     */
    function adjustPaymentModalForMobile(modal) {
        if (!isMobile || !modal) return;
        
        console.log('Applying mobile adjustments to payment modal...');
        
        const dialog = modal.querySelector('.modal-dialog');
        const content = modal.querySelector('.modal-content');
        const body = modal.querySelector('.payment-modal-body, .modern-body, .modal-body');
        const footer = modal.querySelector('.payment-modal-footer, .modern-footer, .modal-footer');
        
        if (dialog) {
            dialog.style.margin = '0';
            dialog.style.height = '100vh';
            dialog.style.maxHeight = '100vh';
            dialog.style.width = '100vw';
            dialog.style.maxWidth = '100vw';
            dialog.style.display = 'flex';
            dialog.style.alignItems = 'stretch';
        }
        
        if (content) {
            content.style.height = '100vh';
            content.style.maxHeight = '100vh';
            content.style.borderRadius = '0';
            content.style.display = 'flex';
            content.style.flexDirection = 'column';
            content.style.border = 'none';
        }
        
        if (body) {
            body.style.flex = '1';
            body.style.minHeight = '0';
            body.style.maxHeight = 'none';
            body.style.overflowY = 'auto';
            body.style.webkitOverflowScrolling = 'touch';
            body.style.overscrollBehavior = 'contain';
        }
        
        if (footer) {
            footer.style.flexShrink = '0';
            footer.style.position = 'sticky';
            footer.style.bottom = '0';
            footer.style.zIndex = '10';
            footer.style.boxShadow = '0 -2px 10px rgba(0, 0, 0, 0.1)';
        }
    }
    
    /**
     * Handle virtual keyboard appearance/disappearance
     */
    function handleVirtualKeyboard() {
        if (!window.visualViewport) return;
        
        window.visualViewport.addEventListener('resize', () => {
            const modals = document.querySelectorAll('.payment-confirmation-modal.show, .modern-payment-modal.show');
            
            modals.forEach(modal => {
                if (!modal.classList.contains('show')) return;
                
                const viewportHeight = window.visualViewport.height;
                const screenHeight = window.screen.height;
                const keyboardHeight = screenHeight - viewportHeight;
                
                if (keyboardHeight > 150) {
                    // Keyboard is visible
                    modal.classList.add('keyboard-visible');
                    document.body.classList.add('keyboard-visible');
                } else {
                    // Keyboard is hidden
                    modal.classList.remove('keyboard-visible');
                    document.body.classList.remove('keyboard-visible');
                }
            });
        });
    }
    
    /**
     * Setup modal event listeners
     */
    function setupModalListeners() {
        // Handle shown.bs.modal event for any payment modal
        document.addEventListener('shown.bs.modal', (event) => {
            const modal = event.target;
            
            // Check if it's a payment modal
            if (modal.classList.contains('payment-confirmation-modal') || 
                modal.classList.contains('modern-payment-modal') ||
                modal.querySelector('.payment-modal-content, .modern-modal-content')) {
                
                // Apply mobile adjustments
                adjustPaymentModalForMobile(modal);
                
                // Prevent body scroll on mobile
                if (isMobile) {
                    document.body.style.position = 'fixed';
                    document.body.style.top = `-${window.scrollY}px`;
                    document.body.style.width = '100%';
                    document.body.style.height = '100%';
                    document.body.style.overflow = 'hidden';
                }
            }
        });
        
        // Handle hidden.bs.modal event
        document.addEventListener('hidden.bs.modal', (event) => {
            const modal = event.target;
            
            if (modal.classList.contains('payment-confirmation-modal') || 
                modal.classList.contains('modern-payment-modal') ||
                modal.querySelector('.payment-modal-content, .modern-modal-content')) {
                
                // Restore body scroll
                if (isMobile) {
                    const scrollY = document.body.style.top;
                    document.body.style.position = '';
                    document.body.style.top = '';
                    document.body.style.width = '';
                    document.body.style.height = '';
                    document.body.style.overflow = '';
                    window.scrollTo(0, parseInt(scrollY || '0', 10) * -1);
                }
                
                // Clean up keyboard visibility classes
                modal.classList.remove('keyboard-visible');
                document.body.classList.remove('keyboard-visible');
            }
        });
    }
    
    /**
     * Fix input font size to prevent zoom on iOS
     */
    function fixIOSInputZoom() {
        if (!isIOS) return;
        
        const observer = new MutationObserver(() => {
            const paymentModals = document.querySelectorAll('.payment-confirmation-modal, .modern-payment-modal');
            
            paymentModals.forEach(modal => {
                const inputs = modal.querySelectorAll('input, textarea');
                inputs.forEach(input => {
                    if (parseFloat(getComputedStyle(input).fontSize) < 16) {
                        input.style.fontSize = '16px';
                    }
                });
            });
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }
    
    /**
     * Initialize mobile payment modal fixes
     */
    function init() {
        console.log('Initializing mobile payment modal fixes...');
        
        setupModalListeners();
        handleVirtualKeyboard();
        fixIOSInputZoom();
        
        // Handle any modals that are already open
        document.addEventListener('DOMContentLoaded', () => {
            const openModals = document.querySelectorAll('.payment-confirmation-modal.show, .modern-payment-modal.show');
            openModals.forEach(adjustPaymentModalForMobile);
        });
    }
    
    // Initialize when DOM is ready or immediately if already ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
    /**
     * Enhanced timeout handling for mobile payment requests
     */
    function setupMobileTimeoutHandling() {
        // Override form submissions for mobile
        document.addEventListener('submit', function(e) {
            if (!isMobile) return;
            
            const form = e.target;
            if (form.classList.contains('payment-form') || 
                form.querySelector('.payment-confirmation') ||
                form.action.includes('payment')) {
                
                console.log('Mobile payment form submission detected');
                
                // Show loading indicator
                showMobileLoadingIndicator(form);
                
                // Set up timeout handling
                const timeoutId = setTimeout(() => {
                    console.warn('Mobile payment form timeout detected');
                    showMobileTimeoutMessage(form);
                }, 60000); // 60 second timeout for mobile
                
                // Clear timeout on success
                form.addEventListener('success', () => {
                    clearTimeout(timeoutId);
                    hideMobileLoadingIndicator(form);
                });
                
                // Clear timeout on error
                form.addEventListener('error', () => {
                    clearTimeout(timeoutId);
                    hideMobileLoadingIndicator(form);
                });
            }
        });
    }
    
    /**
     * Show mobile loading indicator
     */
    function showMobileLoadingIndicator(form) {
        const indicator = document.createElement('div');
        indicator.className = 'mobile-loading-overlay';
        indicator.innerHTML = `
            <div class="mobile-loading-content">
                <div class="spinner-border" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <p>Processing your request...</p>
                <small>This may take longer on mobile connections</small>
            </div>
        `;
        
        form.appendChild(indicator);
        form.classList.add('mobile-loading');
    }
    
    /**
     * Hide mobile loading indicator
     */
    function hideMobileLoadingIndicator(form) {
        const indicator = form.querySelector('.mobile-loading-overlay');
        if (indicator) {
            indicator.remove();
        }
        form.classList.remove('mobile-loading');
    }
    
    /**
     * Show timeout message for mobile users
     */
    function showMobileTimeoutMessage(form) {
        hideMobileLoadingIndicator(form);
        
        const message = document.createElement('div');
        message.className = 'mobile-timeout-message alert alert-warning';
        message.innerHTML = `
            <h5>Connection Taking Longer Than Expected</h5>
            <p>Your mobile connection appears to be slow. The request is still processing.</p>
            <button type="button" class="btn btn-primary btn-sm" onclick="this.parentElement.remove(); location.reload();">Refresh Page</button>
            <button type="button" class="btn btn-secondary btn-sm ml-2" onclick="this.parentElement.remove();">Continue Waiting</button>
        `;
        
        form.insertBefore(message, form.firstChild);
    }
    
    /**
     * Enhanced connection monitoring for mobile devices
     */
    function setupMobileConnectionMonitoring() {
        if (!isMobile) return;
        
        let connectionCheckInterval;
        
        function startConnectionCheck() {
            connectionCheckInterval = setInterval(() => {
                if (!navigator.onLine) {
                    showMobileOfflineMessage();
                } else {
                    hideMobileOfflineMessage();
                }
            }, 5000);
        }
        
        function stopConnectionCheck() {
            if (connectionCheckInterval) {
                clearInterval(connectionCheckInterval);
            }
        }
        
        window.addEventListener('online', () => {
            console.log('Mobile connection restored');
            hideMobileOfflineMessage();
            showMobileConnectionRestored();
        });
        
        window.addEventListener('offline', () => {
            console.log('Mobile connection lost');
            showMobileOfflineMessage();
        });
        
        startConnectionCheck();
    }
    
    /**
     * Show mobile offline message
     */
    function showMobileOfflineMessage() {
        if (document.querySelector('.mobile-offline-message')) return;
        
        const message = document.createElement('div');
        message.className = 'mobile-offline-message alert alert-danger fixed-top m-2';
        message.style.zIndex = '9999';
        message.innerHTML = `
            <strong>No Internet Connection</strong>
            <p class="mb-0">Please check your mobile connection and try again.</p>
        `;
        
        document.body.insertBefore(message, document.body.firstChild);
    }
    
    /**
     * Hide mobile offline message
     */
    function hideMobileOfflineMessage() {
        const message = document.querySelector('.mobile-offline-message');
        if (message) {
            message.remove();
        }
    }
    
    /**
     * Show connection restored message
     */
    function showMobileConnectionRestored() {
        const message = document.createElement('div');
        message.className = 'mobile-connection-restored alert alert-success fixed-top m-2';
        message.style.zIndex = '9999';
        message.innerHTML = `
            <strong>Connection Restored</strong>
            <p class="mb-0">Your internet connection is back online.</p>
        `;
        
        document.body.insertBefore(message, document.body.firstChild);
        
        // Auto-hide after 3 seconds
        setTimeout(() => {
            if (message) {
                message.remove();
            }
        }, 3000);
    }
    
    // Initialize enhanced features
    if (isMobile) {
        setupMobileTimeoutHandling();
        setupMobileConnectionMonitoring();
    }
    
    // Export for global access
    window.PaymentModalMobileFix = {
        adjustModalForMobile: adjustPaymentModalForMobile,
        isMobile: () => isMobile,
        isIOS: () => isIOS,
        isAndroid: () => isAndroid,
        showLoadingIndicator: showMobileLoadingIndicator,
        hideLoadingIndicator: hideMobileLoadingIndicator,
        version: '2.0.0'
    };
    
})();