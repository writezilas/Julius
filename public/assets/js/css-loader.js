/*! CSS Loader Optimization Script */
(function() {
    'use strict';
    
    /**
     * Load CSS files asynchronously with fallback
     */
    function loadCSS(href, before, media) {
        var doc = window.document;
        var ss = doc.createElement("link");
        var ref;
        if (before) {
            ref = before;
        } else {
            var refs = (doc.body || doc.getElementsByTagName("head")[0]).childNodes;
            ref = refs[refs.length - 1];
        }

        var sheets = doc.styleSheets;
        ss.rel = "stylesheet";
        ss.href = href;
        ss.media = "only x";

        function ready(cb) {
            if (doc.body) {
                return cb();
            }
            setTimeout(function() {
                ready(cb);
            });
        }

        function loadCB() {
            var defined;
            for (var i = 0; i < sheets.length; i++) {
                if (sheets[i].href && sheets[i].href.indexOf(href) > -1) {
                    defined = true;
                }
            }
            if (defined) {
                ss.media = media || "all";
            } else {
                setTimeout(loadCB);
            }
        }

        ready(function() {
            ref.parentNode.insertBefore(ss, (before ? ref : ref.nextSibling));
        });
        
        ss.addEventListener('load', loadCB);
        ss.addEventListener('error', function() {
            console.warn('Failed to load CSS:', href);
        });
        
        return ss;
    }

    /**
     * Preload critical resources
     */
    function preloadCriticalResources() {
        // Check if browser supports preload
        if (!('preload' in document.createElement('link'))) {
            // Fallback for older browsers
            var criticalCSS = [
                '/assets/css/bootstrap.min.css',
                '/assets/css/icons.min.css',
                '/assets/css/app.min.css'
            ];
            
            criticalCSS.forEach(function(css) {
                var link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = css;
                document.head.appendChild(link);
            });
        }
    }

    /**
     * Monitor CSS loading performance
     */
    function monitorCSSPerformance() {
        if ('performance' in window && 'getEntriesByType' in performance) {
            window.addEventListener('load', function() {
                setTimeout(function() {
                    var resources = performance.getEntriesByType('resource');
                    var cssResources = resources.filter(function(resource) {
                        return resource.name.indexOf('.css') > -1;
                    });
                    
                    console.group('CSS Loading Performance');
                    cssResources.forEach(function(resource) {
                        console.log(resource.name.split('/').pop() + ': ' + 
                                  Math.round(resource.duration) + 'ms');
                    });
                    console.groupEnd();
                }, 1000);
            });
        }
    }

    /**
     * Initialize CSS optimization
     */
    function init() {
        preloadCriticalResources();
        
        // Enable performance monitoring in development
        if (window.location.hostname === 'localhost' || 
            window.location.hostname === '127.0.0.1') {
            monitorCSSPerformance();
        }
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Export for global use
    window.CSSLoader = {
        loadCSS: loadCSS
    };

})();