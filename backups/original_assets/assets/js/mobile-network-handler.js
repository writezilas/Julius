/**
 * Mobile Network Handler
 * Handles connection timeouts, retries, and mobile-specific network optimizations
 * Version: 1.0.0
 */

(function() {
    'use strict';
    
    // Mobile detection
    const isMobile = /Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini|Mobile Safari/i.test(navigator.userAgent);
    const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
    const isAndroid = /Android/.test(navigator.userAgent);
    
    // Network configuration
    const config = {
        mobileTimeout: 60000,          // 60 seconds for mobile
        desktopTimeout: 30000,         // 30 seconds for desktop
        retryAttempts: 3,              // Number of retry attempts
        retryDelay: 2000,              // Delay between retries (ms)
        connectionCheckInterval: 30000, // Check connection every 30 seconds
        slowConnectionThreshold: 5000   // Consider connection slow if > 5 seconds
    };
    
    // Connection state tracking
    let connectionState = {
        isOnline: navigator.onLine,
        isSlowConnection: false,
        failedRequests: 0,
        lastSuccessfulRequest: Date.now()
    };
    
    // Request queue for retry management
    let requestQueue = [];
    
    /**
     * Initialize mobile network optimizations
     */
    function init() {
        console.log('Mobile Network Handler initializing...', {
            isMobile: isMobile,
            isIOS: isIOS,
            isAndroid: isAndroid,
            userAgent: navigator.userAgent
        });
        
        setupAjaxDefaults();
        setupConnectionMonitoring();
        setupErrorHandling();
        setupRetryMechanism();
        
        if (isMobile) {
            console.log('Mobile optimizations enabled');
        }
    }
    
    /**
     * Setup jQuery AJAX defaults for mobile optimization
     */
    function setupAjaxDefaults() {
        if (typeof $ !== 'undefined') {
            $.ajaxSetup({
                timeout: isMobile ? config.mobileTimeout : config.desktopTimeout,
                cache: false,
                beforeSend: function(xhr, settings) {
                    // Add mobile indicators
                    if (isMobile) {
                        xhr.setRequestHeader('X-Mobile-Request', 'true');
                        xhr.setRequestHeader('X-Mobile-Type', getMobileType());
                    }
                    
                    // Add connection quality indicator
                    xhr.setRequestHeader('X-Connection-Quality', getConnectionQuality());
                    
                    // Store request for retry mechanism
                    settings.requestId = 'req_' + Date.now() + '_' + Math.random();
                    settings.startTime = Date.now();
                    
                    console.log('AJAX request starting', {
                        url: settings.url,
                        method: settings.type,
                        timeout: settings.timeout,
                        requestId: settings.requestId,
                        isMobile: isMobile
                    });
                },
                success: function(data, textStatus, xhr) {
                    const endTime = Date.now();
                    const duration = endTime - this.startTime;
                    
                    connectionState.lastSuccessfulRequest = endTime;
                    connectionState.failedRequests = 0;
                    
                    // Check for slow connection
                    if (duration > config.slowConnectionThreshold) {
                        connectionState.isSlowConnection = true;
                        console.warn('Slow connection detected', { duration: duration });
                    } else {
                        connectionState.isSlowConnection = false;
                    }
                    
                    console.log('AJAX request successful', {
                        requestId: this.requestId,
                        duration: duration,
                        responseSize: xhr.responseText ? xhr.responseText.length : 0
                    });
                },
                error: function(xhr, status, error) {
                    const endTime = Date.now();
                    const duration = endTime - this.startTime;
                    
                    connectionState.failedRequests++;
                    
                    console.error('AJAX request failed', {
                        requestId: this.requestId,
                        status: status,
                        error: error,
                        httpStatus: xhr.status,
                        duration: duration,
                        url: this.url
                    });
                    
                    // Handle timeout specifically
                    if (status === 'timeout') {
                        handleTimeout(this, xhr, status, error);
                    }
                    
                    // Handle connection errors
                    if (status === 'error' && xhr.status === 0) {
                        handleConnectionError(this, xhr, status, error);
                    }
                }
            });
        } else {
            // Setup fetch defaults for non-jQuery environments
            setupFetchDefaults();
        }
    }
    
    /**
     * Setup fetch API defaults
     */
    function setupFetchDefaults() {
        if (typeof window.fetch !== 'undefined') {
            const originalFetch = window.fetch;
            
            window.fetch = function(url, options = {}) {
                // Add timeout
                const timeout = isMobile ? config.mobileTimeout : config.desktopTimeout;
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), timeout);
                
                // Merge options
                const fetchOptions = {
                    ...options,
                    signal: controller.signal,
                    headers: {
                        ...options.headers,
                        ...(isMobile && {
                            'X-Mobile-Request': 'true',
                            'X-Mobile-Type': getMobileType()
                        }),
                        'X-Connection-Quality': getConnectionQuality()
                    }
                };
                
                console.log('Fetch request starting', { url, options: fetchOptions });
                
                return originalFetch(url, fetchOptions)
                    .then(response => {
                        clearTimeout(timeoutId);
                        connectionState.lastSuccessfulRequest = Date.now();
                        connectionState.failedRequests = 0;
                        return response;
                    })
                    .catch(error => {
                        clearTimeout(timeoutId);
                        connectionState.failedRequests++;
                        
                        if (error.name === 'AbortError') {
                            console.error('Fetch timeout', { url, timeout });
                            throw new Error('Request timeout');
                        }
                        
                        throw error;
                    });
            };
        }
    }
    
    /**
     * Setup connection monitoring
     */
    function setupConnectionMonitoring() {
        // Online/offline detection
        window.addEventListener('online', function() {
            console.log('Connection restored');
            connectionState.isOnline = true;
            showConnectionStatus('Connection restored', 'success');
            
            // Retry queued requests
            processRequestQueue();
        });
        
        window.addEventListener('offline', function() {
            console.log('Connection lost');
            connectionState.isOnline = false;
            showConnectionStatus('Connection lost', 'error');
        });
        
        // Periodic connection check
        setInterval(function() {
            if (connectionState.failedRequests > 3) {
                console.warn('Multiple failed requests detected', connectionState);
                checkConnection();
            }
        }, config.connectionCheckInterval);
    }
    
    /**
     * Setup global error handling
     */
    function setupErrorHandling() {
        window.addEventListener('error', function(event) {
            if (event.message && event.message.includes('NetworkError')) {
                console.error('Network error detected', event);
                connectionState.failedRequests++;
            }
        });
        
        // Promise rejection handler
        window.addEventListener('unhandledrejection', function(event) {
            if (event.reason && event.reason.message && 
                (event.reason.message.includes('timeout') || 
                 event.reason.message.includes('network'))) {
                console.error('Unhandled network error', event.reason);
                connectionState.failedRequests++;
            }
        });
    }
    
    /**
     * Setup retry mechanism
     */
    function setupRetryMechanism() {
        // Process retry queue every 5 seconds
        setInterval(processRequestQueue, 5000);
    }
    
    /**
     * Handle timeout errors
     */
    function handleTimeout(requestConfig, xhr, status, error) {
        console.warn('Request timeout detected', {
            url: requestConfig.url,
            timeout: requestConfig.timeout,
            isMobile: isMobile
        });
        
        if (isMobile) {
            // Add to retry queue for mobile devices
            addToRetryQueue({
                url: requestConfig.url,
                method: requestConfig.type || 'GET',
                data: requestConfig.data,
                success: requestConfig.success,
                error: requestConfig.error,
                attempts: 0
            });
            
            showConnectionStatus('Request timed out, retrying...', 'warning');
        }
    }
    
    /**
     * Handle connection errors
     */
    function handleConnectionError(requestConfig, xhr, status, error) {
        console.error('Connection error detected', {
            url: requestConfig.url,
            status: xhr.status,
            readyState: xhr.readyState
        });
        
        if (!connectionState.isOnline) {
            addToRetryQueue({
                url: requestConfig.url,
                method: requestConfig.type || 'GET',
                data: requestConfig.data,
                success: requestConfig.success,
                error: requestConfig.error,
                attempts: 0
            });
        }
    }
    
    /**
     * Add request to retry queue
     */
    function addToRetryQueue(requestConfig) {
        if (requestQueue.length < 50) { // Prevent queue overflow
            requestQueue.push(requestConfig);
            console.log('Added request to retry queue', { 
                url: requestConfig.url, 
                queueSize: requestQueue.length 
            });
        }
    }
    
    /**
     * Process retry queue
     */
    function processRequestQueue() {
        if (!connectionState.isOnline || requestQueue.length === 0) {
            return;
        }
        
        console.log('Processing retry queue', { queueSize: requestQueue.length });
        
        const request = requestQueue.shift();
        if (request.attempts >= config.retryAttempts) {
            console.error('Max retry attempts reached', request);
            return;
        }
        
        request.attempts++;
        
        setTimeout(() => {
            retryRequest(request);
        }, config.retryDelay * request.attempts);
    }
    
    /**
     * Retry a failed request
     */
    function retryRequest(requestConfig) {
        console.log('Retrying request', {
            url: requestConfig.url,
            attempt: requestConfig.attempts
        });
        
        if (typeof $ !== 'undefined') {
            $.ajax({
                url: requestConfig.url,
                method: requestConfig.method,
                data: requestConfig.data,
                success: requestConfig.success,
                error: function(xhr, status, error) {
                    if (requestConfig.attempts < config.retryAttempts) {
                        addToRetryQueue(requestConfig);
                    } else if (requestConfig.error) {
                        requestConfig.error(xhr, status, error);
                    }
                }
            });
        }
    }
    
    /**
     * Get mobile device type
     */
    function getMobileType() {
        if (isIOS) {
            if (/iPad/.test(navigator.userAgent)) return 'iPad';
            if (/iPhone/.test(navigator.userAgent)) return 'iPhone';
            if (/iPod/.test(navigator.userAgent)) return 'iPod';
        }
        if (isAndroid) {
            return /Mobile/.test(navigator.userAgent) ? 'Android Phone' : 'Android Tablet';
        }
        if (/BlackBerry/.test(navigator.userAgent)) return 'BlackBerry';
        if (/Windows Phone/.test(navigator.userAgent)) return 'Windows Phone';
        return 'Mobile';
    }
    
    /**
     * Get connection quality indicator
     */
    function getConnectionQuality() {
        if (!connectionState.isOnline) return 'offline';
        if (connectionState.isSlowConnection) return 'slow';
        if (connectionState.failedRequests > 0) return 'unstable';
        return 'good';
    }
    
    /**
     * Check connection by making a simple request
     */
    function checkConnection() {
        const testUrl = '/api/ping';
        const startTime = Date.now();
        
        if (typeof $ !== 'undefined') {
            $.ajax({
                url: testUrl,
                method: 'GET',
                timeout: 5000,
                success: function() {
                    const duration = Date.now() - startTime;
                    connectionState.isSlowConnection = duration > config.slowConnectionThreshold;
                    connectionState.isOnline = true;
                    console.log('Connection check successful', { duration });
                },
                error: function() {
                    connectionState.isOnline = false;
                    console.error('Connection check failed');
                }
            });
        }
    }
    
    /**
     * Show connection status to user
     */
    function showConnectionStatus(message, type = 'info') {
        // Only show on mobile devices
        if (!isMobile) return;
        
        console.log(`Connection Status [${type}]: ${message}`);
        
        // Try to show notification if available
        if (typeof toastr !== 'undefined') {
            toastr[type](message);
        } else if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: message,
                icon: type,
                timer: 3000,
                showConfirmButton: false,
                position: 'top-end',
                toast: true
            });
        } else {
            // Fallback to console
            console.log('Network Status:', message);
        }
    }
    
    /**
     * Get current network statistics
     */
    function getNetworkStats() {
        return {
            ...connectionState,
            config: config,
            queueSize: requestQueue.length,
            isMobile: isMobile,
            mobileType: isMobile ? getMobileType() : null,
            connectionQuality: getConnectionQuality()
        };
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
    // Export for global access
    window.MobileNetworkHandler = {
        init: init,
        getStats: getNetworkStats,
        retryRequest: retryRequest,
        checkConnection: checkConnection,
        isMobile: () => isMobile,
        version: '1.0.0'
    };
    
    console.log('Mobile Network Handler loaded');
    
})();