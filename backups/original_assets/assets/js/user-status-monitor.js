/**
 * User Status Monitor
 * Monitors user suspension/blocking status and automatically logs out users
 * who have been suspended/blocked while authenticated
 */

class UserStatusMonitor {
    constructor() {
        this.checkInterval = 30000; // Check every 30 seconds
        this.intervalId = null;
        this.init();
    }

    init() {
        // Only run if user is authenticated
        if (!window.Laravel || !window.Laravel.user) {
            console.log('UserStatusMonitor: No authenticated user found');
            return;
        }

        console.log('UserStatusMonitor: Starting monitoring for user:', window.Laravel.user.username);
        
        // Start monitoring
        this.startMonitoring();
        
        // Monitor when page becomes visible again (in case of suspension while tab was inactive)
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                this.checkUserStatus();
            }
        });
    }

    startMonitoring() {
        // Check immediately
        this.checkUserStatus();
        
        // Then check every 30 seconds
        this.intervalId = setInterval(() => {
            this.checkUserStatus();
        }, this.checkInterval);
    }

    async checkUserStatus() {
        try {
            const response = await fetch('/suspension/status', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.ok) {
                const data = await response.json();
                
                if (data.status === 'suspended') {
                    this.handleSuspension(data);
                } else if (data.status === 'blocked') {
                    this.handleBlocking(data);
                } else if (data.status === 'active') {
                    // User is active, continue monitoring
                    console.log('UserStatusMonitor: User status is active');
                }
            } else if (response.status === 401) {
                // User is already logged out
                console.log('UserStatusMonitor: User not authenticated, stopping monitor');
                this.stopMonitoring();
            }
        } catch (error) {
            console.error('UserStatusMonitor: Error checking user status:', error);
        }
    }

    handleSuspension(data) {
        console.log('UserStatusMonitor: User suspended, redirecting...');
        this.stopMonitoring();
        
        // Show notification
        this.showNotification('Your account has been suspended. You will be redirected to the login page.', 'warning');
        
        // Redirect after a short delay
        setTimeout(() => {
            if (data.suspension_until) {
                window.location.href = `/account/suspended?user=${window.Laravel.user.id}`;
            } else {
                // Indefinite suspension
                window.location.href = '/login';
            }
        }, 2000);
    }

    handleBlocking(data) {
        console.log('UserStatusMonitor: User blocked, redirecting...');
        this.stopMonitoring();
        
        // Show notification
        this.showNotification('Your account has been blocked. You will be redirected to the login page.', 'error');
        
        // Redirect after a short delay
        setTimeout(() => {
            if (data.block_until) {
                window.location.href = '/login';
            } else {
                window.location.href = `/account/blocked?user=${window.Laravel.user.id}`;
            }
        }, 2000);
    }

    showNotification(message, type = 'info') {
        // Try to use existing toast system if available
        if (typeof toastr !== 'undefined') {
            toastr[type](message);
        } else if (typeof showToast === 'function') {
            showToast('Account Status', message, type);
        } else {
            // Fallback to alert
            alert(message);
        }
    }

    stopMonitoring() {
        if (this.intervalId) {
            clearInterval(this.intervalId);
            this.intervalId = null;
        }
    }
}

// Initialize the monitor when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Only initialize if we're on an authenticated page
    if (document.body.classList.contains('auth-page') || 
        window.location.pathname.includes('/login') || 
        window.location.pathname.includes('/register')) {
        return; // Don't monitor on auth pages
    }

    window.userStatusMonitor = new UserStatusMonitor();
});

// Clean up on page unload
window.addEventListener('beforeunload', function() {
    if (window.userStatusMonitor) {
        window.userStatusMonitor.stopMonitoring();
    }
});
