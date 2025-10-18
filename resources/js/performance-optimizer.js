/**
 * Performance Optimizer for Autobidder
 * Implements lazy loading, image optimization, and resource management
 */

class PerformanceOptimizer {
    constructor() {
        this.init();
        this.lazyLoadImages();
        this.optimizeScriptLoading();
        this.setupResourceHints();
        this.deferNonCriticalCSS();
    }

    init() {
        // Initialize performance monitoring
        this.performance = {
            start: performance.now(),
            metrics: {}
        };

        // Setup intersection observer for lazy loading
        this.setupIntersectionObserver();
    }

    /**
     * Lazy load images and improve loading performance
     */
    lazyLoadImages() {
        const images = document.querySelectorAll('img[data-src]');
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });

        images.forEach(img => imageObserver.observe(img));
    }

    /**
     * Setup intersection observer for various elements
     */
    setupIntersectionObserver() {
        if ('IntersectionObserver' in window) {
            this.observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animate-in');
                    }
                });
            }, {
                threshold: 0.1,
                rootMargin: '50px'
            });
        }
    }

    /**
     * Optimize script loading with dynamic imports
     */
    optimizeScriptLoading() {
        // Load non-critical scripts after page load
        window.addEventListener('load', () => {
            this.loadNonCriticalScripts();
        });

        // Preload critical resources
        this.preloadCriticalResources();
    }

    /**
     * Load non-critical scripts dynamically
     */
    async loadNonCriticalScripts() {
        const scripts = [
            '/assets/js/pages/dashboard-analytics.init.js',
            '/assets/js/pages/apexcharts-area.init.js',
            '/assets/js/pages/chartjs.init.js'
        ];

        // Load scripts with a delay to avoid blocking
        for (let i = 0; i < scripts.length; i++) {
            setTimeout(() => {
                this.loadScript(scripts[i]);
            }, i * 100);
        }
    }

    /**
     * Load script dynamically
     */
    loadScript(src) {
        return new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = src;
            script.onload = resolve;
            script.onerror = reject;
            document.head.appendChild(script);
        });
    }

    /**
     * Preload critical resources
     */
    preloadCriticalResources() {
        const criticalResources = [
            { href: '/assets/css/bootstrap.min.css', as: 'style' },
            { href: '/assets/css/app.min.css', as: 'style' },
            { href: '/assets/js/app.min.js', as: 'script' }
        ];

        criticalResources.forEach(resource => {
            const link = document.createElement('link');
            link.rel = 'preload';
            link.href = resource.href;
            link.as = resource.as;
            document.head.appendChild(link);
        });
    }

    /**
     * Setup resource hints for better performance
     */
    setupResourceHints() {
        // DNS prefetch for external domains
        const domains = [
            '//fonts.googleapis.com',
            '//cdn.jsdelivr.net'
        ];

        domains.forEach(domain => {
            const link = document.createElement('link');
            link.rel = 'dns-prefetch';
            link.href = domain;
            document.head.appendChild(link);
        });
    }

    /**
     * Defer non-critical CSS
     */
    deferNonCriticalCSS() {
        const nonCriticalCSS = [
            '/assets/css/icons.css',
            '/assets/libs/apexcharts/apexcharts.min.css',
            '/assets/libs/sweetalert2/sweetalert2.min.css'
        ];

        // Load non-critical CSS after page load
        window.addEventListener('load', () => {
            nonCriticalCSS.forEach(href => {
                const link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = href;
                link.media = 'print';
                link.onload = function() {
                    this.media = 'all';
                };
                document.head.appendChild(link);
            });
        });
    }

    /**
     * Optimize table loading for large datasets
     */
    optimizeTableLoading(tableSelector) {
        const table = document.querySelector(tableSelector);
        if (!table) return;

        // Implement virtual scrolling for large tables
        this.implementVirtualScrolling(table);
    }

    /**
     * Simple virtual scrolling implementation
     */
    implementVirtualScrolling(table) {
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        const rowHeight = 50; // Average row height
        const containerHeight = 400; // Visible container height
        const visibleRows = Math.ceil(containerHeight / rowHeight);
        
        let scrollTop = 0;
        let startIndex = 0;
        
        // Create virtual scroll container
        const container = document.createElement('div');
        container.style.height = containerHeight + 'px';
        container.style.overflow = 'auto';
        
        const virtualTable = table.cloneNode(false);
        const virtualTbody = tbody.cloneNode(false);
        virtualTable.appendChild(table.querySelector('thead').cloneNode(true));
        virtualTable.appendChild(virtualTbody);
        container.appendChild(virtualTable);
        
        // Replace original table
        table.parentNode.replaceChild(container, table);
        
        const updateTable = () => {
            virtualTbody.innerHTML = '';
            const endIndex = Math.min(startIndex + visibleRows, rows.length);
            
            for (let i = startIndex; i < endIndex; i++) {
                virtualTbody.appendChild(rows[i].cloneNode(true));
            }
        };
        
        container.addEventListener('scroll', () => {
            scrollTop = container.scrollTop;
            startIndex = Math.floor(scrollTop / rowHeight);
            updateTable();
        });
        
        updateTable();
    }

    /**
     * Debounce function for search inputs
     */
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    /**
     * Optimize search inputs with debouncing
     */
    optimizeSearchInputs() {
        const searchInputs = document.querySelectorAll('input[type="search"], input[data-search]');
        
        searchInputs.forEach(input => {
            const debouncedSearch = this.debounce((value) => {
                // Trigger search event
                input.dispatchEvent(new CustomEvent('optimized-search', {
                    detail: { value }
                }));
            }, 300);
            
            input.addEventListener('input', (e) => {
                debouncedSearch(e.target.value);
            });
        });
    }

    /**
     * Image compression and optimization
     */
    compressImage(file, maxWidth = 1920, quality = 0.8) {
        return new Promise((resolve) => {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            const img = new Image();
            
            img.onload = () => {
                const ratio = Math.min(maxWidth / img.width, maxWidth / img.height);
                canvas.width = img.width * ratio;
                canvas.height = img.height * ratio;
                
                ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                canvas.toBlob(resolve, 'image/jpeg', quality);
            };
            
            img.src = URL.createObjectURL(file);
        });
    }

    /**
     * Setup performance monitoring
     */
    monitorPerformance() {
        // Monitor page load times
        window.addEventListener('load', () => {
            const loadTime = performance.now() - this.performance.start;
            console.log(`Page loaded in ${loadTime.toFixed(2)}ms`);
            
            // Send metrics to analytics if needed
            this.sendMetrics({ loadTime });
        });

        // Monitor JavaScript errors
        window.addEventListener('error', (e) => {
            console.error('JavaScript error:', e.error);
            this.sendMetrics({ 
                error: e.message,
                file: e.filename,
                line: e.lineno
            });
        });
    }

    /**
     * Send metrics to analytics
     */
    sendMetrics(metrics) {
        // Implement analytics sending if needed
        // For now, just log to console
        console.log('Performance metrics:', metrics);
    }

    /**
     * Clean up resources
     */
    cleanup() {
        if (this.observer) {
            this.observer.disconnect();
        }
    }
}

// Initialize performance optimizer when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.performanceOptimizer = new PerformanceOptimizer();
});

// Export for module use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = PerformanceOptimizer;
}