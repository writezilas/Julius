/**
 * Enhanced Dashboard JavaScript
 * Provides additional functionality for the redesigned dashboard
 */

class DashboardEnhancer {
    constructor() {
        this.isTouch = 'ontouchstart' in window;
        this.currentTheme = document.documentElement.getAttribute('data-layout-mode') || 'light';
        this.init();
    }

    init() {
        this.setupScrollAnimations();
        this.setupTooltips();
        this.setupFormValidation();
        this.setupResponsiveFeatures();
        this.setupAccessibility();
        this.setupLoadingStates();
        this.setupThemeDetection();
        this.setupMobileOptimizations();
        this.setupPerformanceOptimizations();
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
            // Only show error for critical errors, not minor issues like API empty responses
            if (e.error && e.error.message) {
                // Check if it's a critical error that should be shown to user
                const isCritical = e.error.message.includes('undefined') || 
                                 e.error.message.includes('null') || 
                                 e.error.message.includes('Cannot read') ||
                                 e.error.message.includes('TypeError') ||
                                 e.error.message.includes('is not a function') ||
                                 e.error.message.includes('Cannot access');
                
                if (isCritical) {
                    console.warn('Critical dashboard error detected:', e.error.message);
                    this.showUserFriendlyError('A technical issue occurred. Please refresh the page if problems persist.');
                } else {
                    // Just log non-critical errors without showing to user
                    console.warn('Non-critical dashboard error suppressed:', e.error.message);
                }
            }
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
    /**
     * Setup theme detection and handling
     */
    setupThemeDetection() {
        // Watch for theme changes
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.type === 'attributes' && mutation.attributeName === 'data-layout-mode') {
                    this.currentTheme = document.documentElement.getAttribute('data-layout-mode');
                    this.handleThemeChange();
                }
            });
        });
        
        observer.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['data-layout-mode']
        });
        
        // Handle system theme preference
        if (window.matchMedia) {
            const darkModeQuery = window.matchMedia('(prefers-color-scheme: dark)');
            darkModeQuery.addEventListener('change', (e) => {
                if (!localStorage.getItem('theme-preference')) {
                    this.currentTheme = e.matches ? 'dark' : 'light';
                    this.handleThemeChange();
                }
            });
        }
    }
    
    /**
     * Handle theme changes
     */
    handleThemeChange() {
        // Update chart colors if charts exist
        this.updateChartsTheme();
        
        // Update custom elements
        this.updateCustomElementsTheme();
        
        // Dispatch theme change event
        window.dispatchEvent(new CustomEvent('themeChanged', {
            detail: { theme: this.currentTheme }
        }));
    }
    
    /**
     * Update charts theme colors
     */
    updateChartsTheme() {
        // This can be extended to update any charts or visualizations
        const isDark = this.currentTheme === 'dark';
        const textColor = isDark ? '#e2e8f0' : '#334155';
        
        // Update any ApexCharts instances if they exist
        if (window.ApexCharts && window.ApexCharts.exec) {
            // Update chart text colors
            document.querySelectorAll('[data-apexcharts]').forEach(chart => {
                try {
                    const chartId = chart.id;
                    if (chartId) {
                        window.ApexCharts.exec(chartId, 'updateOptions', {
                            chart: {
                                foreColor: textColor
                            }
                        });
                    }
                } catch (e) {
                    // Chart might not be initialized yet
                }
            });
        }
    }
    
    /**
     * Update custom elements for theme
     */
    updateCustomElementsTheme() {
        // Update any custom tooltips or elements that need theme updates
        document.querySelectorAll('.custom-tooltip').forEach(tooltip => {
            const isDark = this.currentTheme === 'dark';
            tooltip.style.background = isDark ? 'rgba(30, 41, 59, 0.9)' : 'rgba(0, 0, 0, 0.8)';
        });
    }
    
    /**
     * Mobile-specific optimizations
     */
    setupMobileOptimizations() {
        if (!this.isTouch) return;
        
        // Optimize touch interactions
        this.setupTouchInteractions();
        
        // Mobile-specific performance optimizations
        this.setupMobilePerformance();
        
        // Handle orientation changes
        this.setupOrientationHandling();
    }
    
    /**
     * Setup touch interactions
     */
    setupTouchInteractions() {
        // Add touch feedback to interactive elements
        document.querySelectorAll('.btn, .card, .badge').forEach(element => {
            element.addEventListener('touchstart', function() {
                this.style.opacity = '0.8';
            }, { passive: true });
            
            element.addEventListener('touchend', function() {
                this.style.opacity = '1';
            }, { passive: true });
        });
        
        // Prevent zoom on double-tap for form inputs
        document.querySelectorAll('input, select, textarea').forEach(input => {
            input.addEventListener('touchend', function(e) {
                e.preventDefault();
                this.focus();
            });
        });
    }
    
    /**
     * Mobile performance optimizations
     */
    setupMobilePerformance() {
        // Disable hover effects on touch devices
        if (this.isTouch) {
            document.body.classList.add('touch-device');
            
            // Add CSS to disable hover animations
            const style = document.createElement('style');
            style.textContent = `
                .touch-device .card:hover,
                .touch-device .stats-card:hover .stats-icon,
                .touch-device .activity-item:hover {
                    transform: none !important;
                    animation: none !important;
                }
            `;
            document.head.appendChild(style);
        }
        
        // Optimize animations for mobile
        if ('ontouchstart' in window && navigator.userAgent.match(/Mobi/)) {
            document.body.classList.add('mobile-device');
        }
    }
    
    /**
     * Handle orientation changes
     */
    setupOrientationHandling() {
        window.addEventListener('orientationchange', () => {
            // Delay to ensure orientation change is complete
            setTimeout(() => {
                // Trigger resize events for responsive components
                window.dispatchEvent(new Event('resize'));
                
                // Refresh any charts or complex layouts
                this.refreshResponsiveComponents();
            }, 300);
        });
    }
    
    /**
     * Refresh responsive components after orientation change
     */
    refreshResponsiveComponents() {
        // Refresh live statistics if they exist
        const refreshButton = document.getElementById('refresh-stats-btn');
        if (refreshButton && window.loadLiveStatistics) {
            window.loadLiveStatistics();
        }
        
        // Recalculate any dynamic layouts
        document.querySelectorAll('.card').forEach(card => {
            card.style.height = 'auto';
        });
    }
    
    /**
     * Performance optimizations
     */
    setupPerformanceOptimizations() {
        // Intersection Observer for lazy loading
        this.setupLazyLoading();
        
        // Debounce expensive operations
        this.setupDebouncedOperations();
        
        // Memory management
        this.setupMemoryManagement();
    }
    
    /**
     * Setup lazy loading for images and heavy content
     */
    setupLazyLoading() {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    if (img.dataset.src) {
                        img.src = img.dataset.src;
                        img.classList.remove('lazy');
                        observer.unobserve(img);
                    }
                }
            });
        });
        
        document.querySelectorAll('img[data-src]').forEach(img => {
            imageObserver.observe(img);
        });
    }
    
    /**
     * Setup debounced operations
     */
    setupDebouncedOperations() {
        // Debounce resize handler
        let resizeTimer;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => {
                this.handleResize();
            }, 250);
        });
        
        // Debounce scroll handler
        let scrollTimer;
        window.addEventListener('scroll', () => {
            clearTimeout(scrollTimer);
            scrollTimer = setTimeout(() => {
                this.handleScroll();
            }, 100);
        }, { passive: true });
    }
    
    /**
     * Handle resize events
     */
    handleResize() {
        // Update responsive breakpoints
        const isMobile = window.innerWidth < 768;
        document.body.classList.toggle('mobile-layout', isMobile);
        
        // Update card layouts
        this.updateCardLayouts();
    }
    
    /**
     * Handle scroll events
     */
    handleScroll() {
        // Update scroll-based animations or effects
        const scrollY = window.pageYOffset;
        
        // Add scroll classes for styling
        document.body.classList.toggle('scrolled', scrollY > 100);
    }
    
    /**
     * Update card layouts for responsive design
     */
    updateCardLayouts() {
        const isMobile = window.innerWidth < 768;
        
        document.querySelectorAll('.enhanced-dashboard .row').forEach(row => {
            if (isMobile) {
                row.classList.add('mobile-row');
            } else {
                row.classList.remove('mobile-row');
            }
        });
    }
    
    /**
     * Setup memory management
     */
    setupMemoryManagement() {
        // Clean up event listeners on page unload
        window.addEventListener('beforeunload', () => {
            // Remove custom event listeners
            this.cleanup();
        });
    }
    
    /**
     * Cleanup resources
     */
    cleanup() {
        // Clear any intervals or timeouts
        if (this.statsInterval) {
            clearInterval(this.statsInterval);
        }
        
        // Remove event listeners
        document.querySelectorAll('.enhanced-dashboard *').forEach(element => {
            // Clone node to remove all event listeners
            const newElement = element.cloneNode(true);
            if (element.parentNode) {
                element.parentNode.replaceChild(newElement, element);
            }
        });
    }
    
    optimizePerformance() {
        // This method is maintained for backward compatibility
        this.setupPerformanceOptimizations();
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
