/**
 * Enhanced Dashboard JavaScript
 * Provides additional functionality for the redesigned dashboard
 */

class DashboardEnhancer {
    constructor() {
        this.init();
    }

    init() {
        this.setupScrollAnimations();
        this.setupTooltips();
        this.setupFormValidation();
        this.setupResponsiveFeatures();
        this.setupAccessibility();
        this.setupLoadingStates();
    }

    /**
     * Scroll-based animations for cards
     */
    setupScrollAnimations() {
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Observe all cards for scroll animations
        document.querySelectorAll('.enhanced-dashboard .card').forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(card);
        });
    }

    /**
     * Setup enhanced tooltips
     */
    setupTooltips() {
        // Initialize Bootstrap tooltips if available
        if (typeof bootstrap !== 'undefined') {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }

        // Add custom tooltips for stats cards
        document.querySelectorAll('.stats-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                const label = this.querySelector('.stats-label').textContent;
                const value = this.querySelector('.stats-value').textContent;
                
                // Create or update tooltip
                let tooltip = this.querySelector('.custom-tooltip');
                if (!tooltip) {
                    tooltip = document.createElement('div');
                    tooltip.className = 'custom-tooltip';
                    tooltip.style.cssText = `
                        position: absolute;
                        top: -40px;
                        left: 50%;
                        transform: translateX(-50%);
                        background: rgba(0, 0, 0, 0.8);
                        color: white;
                        padding: 8px 12px;
                        border-radius: 6px;
                        font-size: 12px;
                        white-space: nowrap;
                        z-index: 1000;
                        opacity: 0;
                        pointer-events: none;
                        transition: opacity 0.3s ease;
                    `;
                    this.appendChild(tooltip);
                }
                
                tooltip.textContent = `${label}: ${value}`;
                tooltip.style.opacity = '1';
            });

            card.addEventListener('mouseleave', function() {
                const tooltip = this.querySelector('.custom-tooltip');
                if (tooltip) {
                    tooltip.style.opacity = '0';
                }
            });
        });
    }

    /**
     * Enhanced form validation for trading forms
     */
    setupFormValidation() {
        document.querySelectorAll('.trading-form').forEach(form => {
            const amountInput = form.querySelector('input[name="amount"]');
            const periodSelect = form.querySelector('select[name="period"]');
            const submitButton = form.querySelector('button[type="submit"]');

            // Real-time validation
            if (amountInput) {
                amountInput.addEventListener('input', function() {
                    this.classList.remove('is-invalid', 'is-valid');
                    
                    if (this.value && parseFloat(this.value) > 0) {
                        this.classList.add('is-valid');
                    } else if (this.value) {
                        this.classList.add('is-invalid');
                    }
                });
            }

            if (periodSelect) {
                periodSelect.addEventListener('change', function() {
                    this.classList.remove('is-invalid', 'is-valid');
                    
                    if (this.value) {
                        this.classList.add('is-valid');
                    } else {
                        this.classList.add('is-invalid');
                    }
                });
            }

            // Enhanced submit behavior
            form.addEventListener('submit', function(e) {
                const isValid = this.checkValidity();
                
                if (isValid) {
                    submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Processing...';
                    submitButton.disabled = true;
                } else {
                    e.preventDefault();
                    
                    // Show validation feedback
                    const invalidInputs = this.querySelectorAll(':invalid');
                    invalidInputs.forEach(input => {
                        input.classList.add('is-invalid');
                    });
                }
            });
        });
    }

    /**
     * Responsive features for better mobile experience
     */
    setupResponsiveFeatures() {
        // Touch-friendly interactions for mobile
        if ('ontouchstart' in window) {
            document.querySelectorAll('.enhanced-dashboard .card').forEach(card => {
                card.style.cursor = 'pointer';
                
                card.addEventListener('touchstart', function() {
                    this.style.transform = 'scale(0.98)';
                });
                
                card.addEventListener('touchend', function() {
                    this.style.transform = '';
                });
            });
        }

        // Responsive navigation for stats
        this.setupResponsiveStats();
        
        // Mobile-friendly modals
        this.setupMobileModals();
    }

    /**
     * Setup responsive stats layout
     */
    setupResponsiveStats() {
        const handleResize = () => {
            const isMobile = window.innerWidth < 768;
            const statsCards = document.querySelectorAll('.stats-card');
            
            statsCards.forEach(card => {
                if (isMobile) {
                    card.classList.add('mobile-optimized');
                } else {
                    card.classList.remove('mobile-optimized');
                }
            });
        };

        window.addEventListener('resize', handleResize);
        handleResize(); // Initial call
    }

    /**
     * Mobile-friendly modal adjustments
     */
    setupMobileModals() {
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('show.bs.modal', function() {
                const isMobile = window.innerWidth < 768;
                if (isMobile) {
                    this.classList.add('mobile-modal');
                }
            });
        });
    }

    /**
     * Accessibility improvements
     */
    setupAccessibility() {
        // Keyboard navigation for cards
        document.querySelectorAll('.stats-card, .live-stats-card').forEach(card => {
            card.setAttribute('tabindex', '0');
            card.setAttribute('role', 'button');
            
            card.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    this.click();
                }
            });
        });

        // Screen reader friendly announcements
        this.announceUpdates();

        // Focus management for modals
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('shown.bs.modal', function() {
                const firstInput = this.querySelector('input, button, select, textarea');
                if (firstInput) {
                    firstInput.focus();
                }
            });
        });
    }

    /**
     * Announce dynamic content updates to screen readers
     */
    announceUpdates() {
        // Create announcement region for screen readers
        const announcer = document.createElement('div');
        announcer.id = 'sr-announcements';
        announcer.setAttribute('aria-live', 'polite');
        announcer.setAttribute('aria-atomic', 'true');
        announcer.style.cssText = 'position: absolute; left: -10000px; width: 1px; height: 1px; overflow: hidden;';
        document.body.appendChild(announcer);

        // Announce when stats are updated
        const originalFetch = window.fetch;
        window.fetch = function(...args) {
            return originalFetch(...args).then(response => {
                if (args[0] && args[0].includes('live-statistics')) {
                    setTimeout(() => {
                        announcer.textContent = 'Live statistics have been updated';
                    }, 1000);
                }
                return response;
            });
        };
    }

    /**
     * Loading states and skeleton screens
     */
    setupLoadingStates() {
        // Create skeleton loading for stats cards
        const createSkeleton = (card) => {
            const skeleton = document.createElement('div');
            skeleton.className = 'skeleton-loader';
            skeleton.style.cssText = `
                width: 100%;
                height: 100px;
                background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
                background-size: 200% 100%;
                animation: loading 1.5s infinite;
                border-radius: 8px;
            `;
            return skeleton;
        };

        // Show loading state for async content
        document.addEventListener('DOMContentLoaded', () => {
            const liveStatsCards = document.querySelectorAll('.live-stats-card');
            
            liveStatsCards.forEach(card => {
                const content = card.querySelector('.card-body');
                if (content) {
                    content.style.position = 'relative';
                }
            });
        });
    }

    /**
     * Enhanced error handling with user-friendly messages
     */
    setupErrorHandling() {
        window.addEventListener('error', (e) => {
            console.error('Dashboard error:', e.error);
            this.showUserFriendlyError('Something went wrong. Please refresh the page.');
        });

        // Network error detection
        window.addEventListener('online', () => {
            this.showSuccessMessage('Connection restored!');
        });

        window.addEventListener('offline', () => {
            this.showErrorMessage('No internet connection. Some features may not work.');
        });
    }

    /**
     * Show user-friendly error messages
     */
    showUserFriendlyError(message) {
        this.showToast(message, 'error');
    }

    /**
     * Show success messages
     */
    showSuccessMessage(message) {
        this.showToast(message, 'success');
    }

    /**
     * Show error messages
     */
    showErrorMessage(message) {
        this.showToast(message, 'warning');
    }

    /**
     * Toast notification system
     */
    showToast(message, type = 'info') {
        // Create toast container if it doesn't exist
        let toastContainer = document.getElementById('toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toast-container';
            toastContainer.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 10000;
            `;
            document.body.appendChild(toastContainer);
        }

        // Create toast element
        const toast = document.createElement('div');
        const colors = {
            success: '#28a745',
            error: '#dc3545',
            warning: '#ffc107',
            info: '#17a2b8'
        };

        toast.style.cssText = `
            background: ${colors[type] || colors.info};
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transform: translateX(100%);
            transition: transform 0.3s ease;
            max-width: 300px;
            word-wrap: break-word;
        `;

        toast.textContent = message;
        toastContainer.appendChild(toast);

        // Animate in
        setTimeout(() => {
            toast.style.transform = 'translateX(0)';
        }, 100);

        // Auto remove after 5 seconds
        setTimeout(() => {
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 300);
        }, 5000);

        // Click to dismiss
        toast.addEventListener('click', () => {
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 300);
        });
    }

    /**
     * Performance optimization for animations
     */
    optimizePerformance() {
        // Throttle resize events
        let resizeTimeout;
        const originalResize = window.onresize;
        
        window.onresize = function() {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                if (originalResize) originalResize();
            }, 250);
        };

        // Optimize scroll events
        let scrollTimeout;
        window.addEventListener('scroll', () => {
            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(() => {
                // Handle scroll-based optimizations
            }, 100);
        });
    }
}

// Initialize the dashboard enhancer when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    const dashboardEnhancer = new DashboardEnhancer();
    dashboardEnhancer.setupErrorHandling();
    dashboardEnhancer.optimizePerformance();
});

// Export for use in other scripts
window.DashboardEnhancer = DashboardEnhancer;
