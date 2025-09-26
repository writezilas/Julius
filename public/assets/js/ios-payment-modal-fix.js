/**
 * iOS Safari Payment Modal Compatibility Fixes
 * JavaScript fixes for modal display and interaction issues on iOS devices
 */

(function() {
    'use strict';
    
    // iOS Detection
    const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) || 
                  (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
    
    const isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);
    const isIOSSafari = isIOS && isSafari;
    
    console.log('iOS Safari Detection:', { isIOS, isSafari, isIOSSafari });
    
    // Add class for CSS targeting
    if (isIOSSafari) {
        document.documentElement.classList.add('ios-safari');
        console.log('Added ios-safari class to document');
    }
    
    // Disable problematic audio on iOS Safari
    if (isIOSSafari && window.afreshAudio) {
        window.afreshAudio.isEnabled = false;
        console.log('Disabled afresh audio for iOS Safari');
    }
    
    /**
     * iOS Modal Fix - Enhanced Bootstrap Modal Handling
     */
    function initializeIOSModalFixes() {
        if (!isIOSSafari) return;
        
        console.log('Initializing iOS modal fixes...');
        
        // Fix modal backdrop click issues on iOS
        $(document).on('click', '.modal-backdrop', function(e) {
            console.log('Modal backdrop clicked on iOS');
            const modal = $('.modal.show');
            if (modal.length) {
                modal.modal('hide');
            }
        });
        
        // Enhanced modal show handler for iOS
        $(document).on('show.bs.modal', '.modal', function(e) {
            console.log('Modal show event triggered on iOS:', this.id);
            const modal = $(this);
            
            // Force modal to be visible
            modal.css({
                'display': 'block',
                'z-index': '9999'
            });
            
            // Force backdrop to be visible
            setTimeout(() => {
                $('.modal-backdrop').css({
                    'display': 'block',
                    'z-index': '9998',
                    'background': 'rgba(0, 0, 0, 0.5)'
                });
            }, 10);
        });
        
        // Enhanced modal shown handler for iOS
        $(document).on('shown.bs.modal', '.modal', function(e) {
            console.log('Modal shown event triggered on iOS:', this.id);
            const modal = $(this);
            
            // Ensure modal is properly displayed
            modal.addClass('show').css('display', 'block');
            $('body').addClass('modal-open');
            
            // Fix iOS Safari viewport issues
            if (window.visualViewport) {
                const viewport = window.visualViewport;
                modal.css('height', viewport.height + 'px');
            }
            
            // Focus management for iOS
            const firstInput = modal.find('input, select, textarea').first();
            if (firstInput.length) {
                setTimeout(() => {
                    firstInput.focus();
                    // Prevent iOS keyboard from covering modal
                    firstInput[0].scrollIntoView({ 
                        behavior: 'smooth', 
                        block: 'center' 
                    });
                }, 300);
            }
        });
        
        // Enhanced modal hide handler for iOS
        $(document).on('hide.bs.modal', '.modal', function(e) {
            console.log('Modal hide event triggered on iOS:', this.id);
        });
        
        // Enhanced modal hidden handler for iOS
        $(document).on('hidden.bs.modal', '.modal', function(e) {
            console.log('Modal hidden event triggered on iOS:', this.id);
            
            // Clean up iOS specific styles
            $(this).css({
                'display': '',
                'z-index': ''
            });
        });
    }
    
    /**
     * iOS Touch Event Fixes for Modal Triggers
     */
    function initializeIOSTouchFixes() {
        if (!isIOSSafari) return;
        
        console.log('Initializing iOS touch fixes...');
        
        // Fix button clicks that trigger modals
        $(document).on('touchstart', '[data-bs-toggle="modal"]', function(e) {
            console.log('Touch start on modal trigger:', this);
            $(this).addClass('touch-active');
        });
        
        $(document).on('touchend', '[data-bs-toggle="modal"]', function(e) {
            console.log('Touch end on modal trigger:', this);
            const $this = $(this);
            $this.removeClass('touch-active');
            
            // Ensure the modal actually opens
            const targetModal = $this.attr('data-bs-target');
            if (targetModal) {
                setTimeout(() => {
                    const modal = $(targetModal);
                    if (modal.length && !modal.hasClass('show')) {
                        console.log('Force opening modal:', targetModal);
                        modal.modal('show');
                    }
                }, 100);
            }
        });
        
        // Fix close button behavior on iOS
        $(document).on('touchend', '[data-bs-dismiss="modal"]', function(e) {
            console.log('Touch end on modal close button');
            const modal = $(this).closest('.modal');
            if (modal.length) {
                setTimeout(() => {
                    modal.modal('hide');
                }, 100);
            }
        });
    }
    
    /**
     * iOS Viewport Fixes
     */
    function initializeIOSViewportFixes() {
        if (!isIOSSafari || !window.visualViewport) return;
        
        console.log('Initializing iOS viewport fixes...');
        
        const viewport = window.visualViewport;
        
        function handleViewportChange() {
            const modal = $('.modal.show');
            if (modal.length) {
                console.log('Viewport changed, adjusting modal:', viewport.height);
                modal.css({
                    'height': viewport.height + 'px',
                    'top': viewport.offsetTop + 'px'
                });
                
                // Adjust modal dialog positioning
                const modalDialog = modal.find('.modal-dialog');
                if (modalDialog.length) {
                    modalDialog.css({
                        'margin-top': '10px',
                        'margin-bottom': '10px',
                        'max-height': (viewport.height - 20) + 'px'
                    });
                }
            }
        }
        
        viewport.addEventListener('resize', handleViewportChange);
        viewport.addEventListener('scroll', handleViewportChange);
    }
    
    /**
     * iOS Form Fixes
     */
    function initializeIOSFormFixes() {
        if (!isIOSSafari) return;
        
        console.log('Initializing iOS form fixes...');
        
        // Prevent iOS zoom when focusing inputs
        $(document).on('focus', 'input, select, textarea', function() {
            if ($(this).closest('.modal').length) {
                // For inputs in modals, ensure they don't cause zoom
                const originalViewport = $('meta[name=viewport]').attr('content');
                $('meta[name=viewport]').attr('content', 
                    'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no'
                );
                
                // Restore viewport after focus
                setTimeout(() => {
                    $('meta[name=viewport]').attr('content', originalViewport);
                }, 1000);
            }
        });
        
        // Fix form submission in modals on iOS
        $(document).on('submit', '.modal form', function(e) {
            console.log('Form submission in modal on iOS');
            
            // Ensure the form actually submits
            const form = this;
            if (!form.checkValidity()) {
                e.preventDefault();
                console.log('Form validation failed');
                return false;
            }
            
            // Show loading state
            const submitBtn = $(form).find('button[type="submit"], .submit-btn');
            if (submitBtn.length) {
                submitBtn.prop('disabled', true);
                submitBtn.find('.spinner-border').removeClass('d-none');
            }
        });
    }
    
    /**
     * iOS Debug Helper
     */
    function initializeIOSDebug() {
        if (!isIOSSafari) return;
        
        // Add debug info to console
        console.log('iOS Safari Payment Modal Debug Info:', {
            userAgent: navigator.userAgent,
            platform: navigator.platform,
            maxTouchPoints: navigator.maxTouchPoints,
            visualViewport: !!window.visualViewport,
            devicePixelRatio: window.devicePixelRatio,
            screenHeight: screen.height,
            screenWidth: screen.width,
            innerHeight: window.innerHeight,
            innerWidth: window.innerWidth
        });
        
        // Add debug class for CSS testing (remove in production)
        if (localStorage.getItem('ios-debug') === 'true') {
            document.body.classList.add('ios-debug');
        }
        
        // Debug modal events
        $(document).on('show.bs.modal shown.bs.modal hide.bs.modal hidden.bs.modal', '.modal', function(e) {
            console.log(`iOS Modal Event: ${e.type} on ${this.id || 'unnamed modal'}`);
        });
    }
    
    /**
     * Alternative Modal Trigger for iOS
     */
    function createIOSModalTrigger() {
        if (!isIOSSafari) return;
        
        console.log('Creating iOS modal trigger fallback...');
        
        // Fallback function to manually show modal if Bootstrap fails
        window.showModalOnIOS = function(modalId) {
            console.log('Manual modal trigger for iOS:', modalId);
            
            const modal = $(modalId);
            if (!modal.length) {
                console.error('Modal not found:', modalId);
                return;
            }
            
            // Force show the modal
            modal.css({
                'display': 'block',
                'z-index': '9999'
            }).addClass('show');
            
            // Create and show backdrop
            if (!$('.modal-backdrop').length) {
                $('<div class="modal-backdrop fade show"></div>')
                    .css('z-index', '9998')
                    .appendTo('body');
            }
            
            $('body').addClass('modal-open');
            
            // Trigger shown event
            modal.trigger('shown.bs.modal');
        };
        
        // Fallback function to manually hide modal
        window.hideModalOnIOS = function(modalId) {
            console.log('Manual modal hide for iOS:', modalId);
            
            const modal = $(modalId || '.modal.show');
            modal.removeClass('show').css('display', 'none');
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open');
            
            // Trigger hidden event
            modal.trigger('hidden.bs.modal');
        };
    }
    
    /**
     * Initialize all iOS fixes when DOM is ready
     */
    function initialize() {
        console.log('Initializing iOS Payment Modal Fixes...');
        
        initializeIOSModalFixes();
        initializeIOSTouchFixes();
        initializeIOSViewportFixes();
        initializeIOSFormFixes();
        initializeIOSDebug();
        createIOSModalTrigger();
        
        console.log('iOS Payment Modal Fixes initialized successfully');
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialize);
    } else {
        initialize();
    }
    
    // Also initialize when jQuery is ready (for Bootstrap compatibility)
    if (typeof jQuery !== 'undefined') {
        jQuery(document).ready(initialize);
    }
    
})();