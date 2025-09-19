/**
 * Suspension Monitor - Auto logout users when suspended
 * Monitors user suspension status and forces logout/refresh when suspended
 */

class SuspensionMonitor {
    constructor() {
        this.checkInterval = 30000; // Check every 30 seconds
        this.intervalId = null;
        this.isChecking = false;
        this.init();
    }

    init() {
        // Only run for authenticated users
        if (window.Laravel && window.Laravel.user) {
            this.startMonitoring();
            
            // Also check when page becomes visible (in case user was suspended while away)
            document.addEventListener('visibilitychange', () => {
                if (!document.hidden) {
                    this.checkSuspensionStatus();
                }
            });

            // Check when window regains focus
            window.addEventListener('focus', () => {
                this.checkSuspensionStatus();
            });
        }
    }

    startMonitoring() {
        this.intervalId = setInterval(() => {
            this.checkSuspensionStatus();
        }, this.checkInterval);
    }

    stopMonitoring() {
        if (this.intervalId) {
            clearInterval(this.intervalId);
            this.intervalId = null;
        }
    }

    async checkSuspensionStatus() {
        if (this.isChecking) return;
        
        this.isChecking = true;

        try {
            const response = await fetch('/suspension/status', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                credentials: 'same-origin'
            });

            if (response.status === 401) {
                // User is not authenticated, stop monitoring
                this.stopMonitoring();
                return;
            }

            const data = await response.json();
            
            if (data.suspended) {
                this.handleSuspension(data);
            }

        } catch (error) {
            console.error('Error checking suspension status:', error);
        } finally {
            this.isChecking = false;
        }
    }

    handleSuspension(data) {
        // Stop monitoring
        this.stopMonitoring();

        // Show suspension alert
        this.showSuspensionAlert(data);

        // Auto logout and redirect after 5 seconds
        setTimeout(() => {
            this.forceLogout();
        }, 5000);
    }

    showSuspensionAlert(data) {
        // Create modal or alert to inform user
        const alertHtml = `
            <div id="suspension-alert" style="
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.8);
                z-index: 10000;
                display: flex;
                align-items: center;
                justify-content: center;
            ">
                <div style="
                    background: white;
                    padding: 30px;
                    border-radius: 10px;
                    text-align: center;
                    max-width: 500px;
                    margin: 20px;
                    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
                ">
                    <div style="color: #dc3545; font-size: 50px; margin-bottom: 20px;">
                        ⚠️
                    </div>
                    <h3 style="color: #dc3545; margin-bottom: 20px;">Account Suspended</h3>
                    <p style="margin-bottom: 20px; color: #666; line-height: 1.6;">
                        Your account has been suspended due to 3 consecutive payment failures. 
                        You will be automatically logged out in <span id="countdown">5</span> seconds.
                    </p>
                    <p style="font-size: 14px; color: #999;">
                        Suspension until: ${data.suspension_until ? new Date(data.suspension_until).toLocaleString() : 'N/A'}
                    </p>
                    <button onclick="suspensionMonitor.forceLogout()" style="
                        background: #dc3545;
                        color: white;
                        border: none;
                        padding: 10px 20px;
                        border-radius: 5px;
                        cursor: pointer;
                        margin-top: 15px;
                    ">
                        Logout Now
                    </button>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', alertHtml);

        // Countdown timer
        let countdown = 5;
        const countdownElement = document.getElementById('countdown');
        const countdownInterval = setInterval(() => {
            countdown--;
            if (countdownElement) {
                countdownElement.textContent = countdown;
            }
            if (countdown <= 0) {
                clearInterval(countdownInterval);
            }
        }, 1000);
    }

    forceLogout() {
        // Create a form to POST to logout route
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/logout';
        
        // Add CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken;
        form.appendChild(csrfInput);
        
        // Add to body and submit
        document.body.appendChild(form);
        form.submit();
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.suspensionMonitor = new SuspensionMonitor();
});

// Also handle AJAX responses that might indicate suspension
document.addEventListener('ajaxComplete', function(event, xhr, settings) {
    if (xhr.responseJSON && xhr.responseJSON.suspended) {
        window.suspensionMonitor.handleSuspension(xhr.responseJSON);
    }
});
