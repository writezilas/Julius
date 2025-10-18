{{-- Payment Modal Light Mode Fix - Integration Component --}}
{{-- Include this in your master layout to ensure all payment modals display in light mode --}}

{{-- CSS Fix - High Priority Loading --}}
<link rel="stylesheet" href="{{ asset('assets/css/payment-modal-light-mode-fix.css') }}" id="payment-modal-light-mode-css">

{{-- Inline Critical CSS for Immediate Effect --}}
<style id="payment-modal-critical-css">
/* CRITICAL: Immediate light mode fix for payment modals */
/* This runs before external CSS loads to prevent flash of dark content */

.clean-payment-modal,
.payment-modal,
.payment-confirmation-modal,
.modal[id*="payment"],
.modal[id*="Payment"],
.modal[id*="soldShareDetails"],
.modal[class*="payment"],
[data-layout-mode="dark"] .clean-payment-modal,
[data-layout-mode="dark"] .payment-modal,
[data-layout-mode="dark"] .payment-confirmation-modal,
[data-layout-mode="dark"] .modal[id*="payment"],
[data-layout-mode="dark"] .modal[id*="Payment"],
[data-layout-mode="dark"] .modal[id*="soldShareDetails"],
[data-layout-mode="dark"] .modal[class*="payment"] {
    color-scheme: light !important;
    --bs-body-bg: #ffffff !important;
    --bs-body-color: #212529 !important;
}

.clean-payment-modal .modal-content,
.payment-modal .modal-content,
.payment-confirmation-modal .modal-content,
.modal[id*="payment"] .modal-content,
.modal[id*="soldShareDetails"] .modal-content,
[data-layout-mode="dark"] .clean-payment-modal .modal-content,
[data-layout-mode="dark"] .payment-modal .modal-content,
[data-layout-mode="dark"] .payment-confirmation-modal .modal-content,
[data-layout-mode="dark"] .modal[id*="payment"] .modal-content,
[data-layout-mode="dark"] .modal[id*="soldShareDetails"] .modal-content {
    background-color: #ffffff !important;
    color: #212529 !important;
    border: 1px solid #dee2e6 !important;
}

.clean-payment-modal .modal-body,
.payment-modal .modal-body,
.payment-confirmation-modal .modal-body,
.modal[id*="soldShareDetails"] .modal-body,
[data-layout-mode="dark"] .clean-payment-modal .modal-body,
[data-layout-mode="dark"] .payment-modal .modal-body,
[data-layout-mode="dark"] .payment-confirmation-modal .modal-body,
[data-layout-mode="dark"] .modal[id*="soldShareDetails"] .modal-body {
    background-color: #ffffff !important;
    color: #212529 !important;
}

/* All text elements */
.clean-payment-modal h1, .clean-payment-modal h2, .clean-payment-modal h3, 
.clean-payment-modal h4, .clean-payment-modal h5, .clean-payment-modal h6,
.clean-payment-modal p, .clean-payment-modal span, .clean-payment-modal div,
.clean-payment-modal label, .clean-payment-modal small,
.payment-modal h1, .payment-modal h2, .payment-modal h3, 
.payment-modal h4, .payment-modal h5, .payment-modal h6,
.payment-modal p, .payment-modal span, .payment-modal div,
.payment-modal label, .payment-modal small,
[data-layout-mode="dark"] .clean-payment-modal h1, [data-layout-mode="dark"] .clean-payment-modal h2, 
[data-layout-mode="dark"] .clean-payment-modal h3, [data-layout-mode="dark"] .clean-payment-modal h4, 
[data-layout-mode="dark"] .clean-payment-modal h5, [data-layout-mode="dark"] .clean-payment-modal h6,
[data-layout-mode="dark"] .clean-payment-modal p, [data-layout-mode="dark"] .clean-payment-modal span, 
[data-layout-mode="dark"] .clean-payment-modal div, [data-layout-mode="dark"] .clean-payment-modal label, 
[data-layout-mode="dark"] .clean-payment-modal small,
[data-layout-mode="dark"] .payment-modal h1, [data-layout-mode="dark"] .payment-modal h2, 
[data-layout-mode="dark"] .payment-modal h3, [data-layout-mode="dark"] .payment-modal h4, 
[data-layout-mode="dark"] .payment-modal h5, [data-layout-mode="dark"] .payment-modal h6,
[data-layout-mode="dark"] .payment-modal p, [data-layout-mode="dark"] .payment-modal span, 
[data-layout-mode="dark"] .payment-modal div, [data-layout-mode="dark"] .payment-modal label, 
[data-layout-mode="dark"] .payment-modal small {
    color: #212529 !important;
    text-shadow: none !important;
    opacity: 1 !important;
    filter: none !important;
    background: none !important;
    -webkit-background-clip: initial !important;
    -webkit-text-fill-color: #212529 !important;
    background-clip: initial !important;
}

/* Form controls */
.clean-payment-modal input, .clean-payment-modal textarea, .clean-payment-modal select,
.payment-modal input, .payment-modal textarea, .payment-modal select,
[data-layout-mode="dark"] .clean-payment-modal input, [data-layout-mode="dark"] .clean-payment-modal textarea,
[data-layout-mode="dark"] .clean-payment-modal select, [data-layout-mode="dark"] .payment-modal input,
[data-layout-mode="dark"] .payment-modal textarea, [data-layout-mode="dark"] .payment-modal select {
    background-color: #ffffff !important;
    color: #212529 !important;
    border: 1px solid #ced4da !important;
}
</style>

{{-- JavaScript Fix - Dynamic Application --}}
<script id="payment-modal-light-mode-js">
// Immediate JavaScript fix application
(function() {
    'use strict';
    
    function forcePaymentModalLightMode() {
        const paymentModals = document.querySelectorAll('.clean-payment-modal, .payment-modal, .payment-confirmation-modal, .modal[id*="payment"], .modal[id*="soldShareDetails"], .modal[class*="payment"]');
        
        paymentModals.forEach(function(modal) {
            if (modal && !modal.hasAttribute('data-light-mode-forced')) {
                modal.setAttribute('data-light-mode-forced', 'true');
                
                // Force light mode properties
                modal.style.setProperty('color-scheme', 'light', 'important');
                modal.style.setProperty('--bs-body-bg', '#ffffff', 'important');
                modal.style.setProperty('--bs-body-color', '#212529', 'important');
                
                const modalContent = modal.querySelector('.modal-content');
                if (modalContent) {
                    modalContent.style.setProperty('background-color', '#ffffff', 'important');
                    modalContent.style.setProperty('color', '#212529', 'important');
                }
                
                const modalBody = modal.querySelector('.modal-body');
                if (modalBody) {
                    modalBody.style.setProperty('background-color', '#ffffff', 'important');
                    modalBody.style.setProperty('color', '#212529', 'important');
                }
                
                // Force all text elements
                const textElements = modal.querySelectorAll('h1, h2, h3, h4, h5, h6, p, span, div:not(.modal-header), label, small');
                textElements.forEach(function(element) {
                    element.style.setProperty('color', '#212529', 'important');
                    element.style.setProperty('text-shadow', 'none', 'important');
                    element.style.setProperty('opacity', '1', 'important');
                    element.style.setProperty('filter', 'none', 'important');
                });
                
                // Force form controls
                const formControls = modal.querySelectorAll('input, textarea, select');
                formControls.forEach(function(control) {
                    control.style.setProperty('background-color', '#ffffff', 'important');
                    control.style.setProperty('color', '#212529', 'important');
                    control.style.setProperty('border', '1px solid #ced4da', 'important');
                });
            }
        });
    }
    
    // Apply immediately if DOM is ready
    if (document.readyState !== 'loading') {
        forcePaymentModalLightMode();
    }
    
    // Apply when DOM is ready
    document.addEventListener('DOMContentLoaded', forcePaymentModalLightMode);
    
    // Apply on modal show events
    document.addEventListener('show.bs.modal', function(event) {
        const modal = event.target;
        const isPaymentModal = modal.classList.contains('clean-payment-modal') || 
                              modal.classList.contains('payment-modal') ||
                              modal.classList.contains('payment-confirmation-modal') ||
                              modal.id.includes('payment') ||
                              modal.id.includes('soldShareDetails') ||
                              modal.className.includes('payment');
        
        if (isPaymentModal) {
            setTimeout(forcePaymentModalLightMode, 10);
        }
    });
    
    // Apply after modal is shown
    document.addEventListener('shown.bs.modal', function(event) {
        const modal = event.target;
        const isPaymentModal = modal.classList.contains('clean-payment-modal') || 
                              modal.classList.contains('payment-modal') ||
                              modal.classList.contains('payment-confirmation-modal') ||
                              modal.id.includes('payment') ||
                              modal.id.includes('soldShareDetails') ||
                              modal.className.includes('payment');
        
        if (isPaymentModal) {
            setTimeout(forcePaymentModalLightMode, 100);
        }
    });
    
    // Monitor for theme changes
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'attributes' && 
                (mutation.attributeName === 'data-layout-mode' || mutation.attributeName === 'data-theme')) {
                setTimeout(forcePaymentModalLightMode, 100);
            }
        });
    });
    
    // Watch document for theme changes
    observer.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['data-layout-mode', 'data-theme', 'class']
    });
    
    if (document.body) {
        observer.observe(document.body, {
            attributes: true,
            attributeFilter: ['data-layout-mode', 'data-theme', 'class']
        });
    }
    
    // Expose global function
    window.forcePaymentModalLightMode = forcePaymentModalLightMode;
    
    // Periodic check (safety net)
    setInterval(forcePaymentModalLightMode, 3000);
})();
</script>

{{-- Load the comprehensive JavaScript fix --}}
<script src="{{ asset('assets/js/payment-modal-light-mode-fix.js') }}" defer></script>

{{-- Debug Information (only in development) --}}
@if(config('app.debug'))
<script>
console.log('Payment Modal Light Mode Fix loaded');
console.log('Current theme:', document.documentElement.getAttribute('data-layout-mode') || 'light');
console.log('Fix files loaded:', {
    css: '{{ asset("assets/css/payment-modal-light-mode-fix.css") }}',
    js: '{{ asset("assets/js/payment-modal-light-mode-fix.js") }}'
});
</script>
@endif