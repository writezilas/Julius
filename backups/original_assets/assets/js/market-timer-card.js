/**
 * Floating Market Timer Card
 * Shows countdown timer when market is open, hides when market is closed
 */

class MarketTimerCard {
    constructor() {
        this.timerElement = null;
        this.intervalId = null;
        this.isVisible = false;
        this.closeTime = null;
        this.totalDuration = null;
        this.appTimezone = null;
        this.isMarketOpen = false;
        this.urgentModeActive = false;
        
        this.init();
    }
    
    init() {
        // Check if market data is available
        if (typeof window.marketData === 'undefined') {
            console.warn('Market Timer: Market data not available');
            return;
        }
        
        this.isMarketOpen = window.marketData.isOpen;
        this.closeTime = window.marketData.closeTime;
        this.appTimezone = window.marketData.timezone;
        
        // Only show timer if market is open and close time is available
        if (this.isMarketOpen && this.closeTime) {
            this.createTimerElement();
            this.calculateTotalDuration();
            this.startTimer();
            this.show();
        } else {
            console.log('Market Timer: Market is closed or no close time available');
        }
    }
    
    createTimerElement() {
        // Remove existing timer if present
        const existingTimer = document.getElementById('floating-market-timer');
        if (existingTimer) {
            existingTimer.remove();
        }
        
        // Create timer HTML - simplified to match screenshot
        const timerHTML = `
            <div id="floating-market-timer" class="floating-market-timer" role="timer" aria-label="Market closing countdown">
                <div class="timer-card-body">
                    <div class="market-close-message">
                        Time remaining until market closes
                    </div>
                    <div class="timer-display" aria-live="polite">
                        <div class="timer-unit">
                            <div class="timer-value" id="timer-hours">00</div>
                            <div class="timer-label">HOURS</div>
                        </div>
                        <div class="timer-separator">:</div>
                        <div class="timer-unit">
                            <div class="timer-value" id="timer-minutes">00</div>
                            <div class="timer-label">MINUTES</div>
                        </div>
                        <div class="timer-separator">:</div>
                        <div class="timer-unit">
                            <div class="timer-value" id="timer-seconds">00</div>
                            <div class="timer-label">SECONDS</div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Insert into DOM
        document.body.insertAdjacentHTML('beforeend', timerHTML);
        this.timerElement = document.getElementById('floating-market-timer');
        
        // Initialize feather icons if available
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    }
    
    calculateTotalDuration() {
        if (!this.closeTime) return;
        
        try {
            const now = new Date();
            const closeDateTime = new Date(this.closeTime);
            this.totalDuration = Math.max(0, closeDateTime - now);
        } catch (error) {
            console.error('Market Timer: Error calculating duration', error);
            this.totalDuration = 0;
        }
    }
    
    startTimer() {
        if (!this.closeTime) return;
        
        // Clear any existing interval
        if (this.intervalId) {
            clearInterval(this.intervalId);
        }
        
        this.updateTimer(); // Initial update
        
        // Update every second
        this.intervalId = setInterval(() => {
            this.updateTimer();
        }, 1000);
    }
    
    updateTimer() {
        try {
            const now = new Date();
            const closeDateTime = new Date(this.closeTime);
            const timeRemaining = Math.max(0, closeDateTime - now);
            
            if (timeRemaining <= 0) {
                this.onMarketClosed();
                return;
            }
            
            // Calculate time units
            const hours = Math.floor(timeRemaining / (1000 * 60 * 60));
            const minutes = Math.floor((timeRemaining % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((timeRemaining % (1000 * 60)) / 1000);
            
            // Check if we're in the urgent zone (60 seconds or less)
            const totalSeconds = Math.floor(timeRemaining / 1000);
            const isUrgent = totalSeconds <= 60;
            
            // Update display
            this.updateTimeDisplay(hours, minutes, seconds, isUrgent);
            // Progress bar is hidden via CSS
            
        } catch (error) {
            console.error('Market Timer: Error updating timer', error);
            this.hide();
        }
    }
    
    updateTimeDisplay(hours, minutes, seconds, isUrgent = false) {
        const hoursEl = document.getElementById('timer-hours');
        const minutesEl = document.getElementById('timer-minutes');
        const secondsEl = document.getElementById('timer-seconds');
        
        // Update time values
        if (hoursEl) hoursEl.textContent = this.padZero(hours);
        if (minutesEl) minutesEl.textContent = this.padZero(minutes);
        if (secondsEl) secondsEl.textContent = this.padZero(seconds);
        
        // Apply urgent styling when 60 seconds or less remain
        const timerUnits = document.querySelectorAll('.timer-unit');
        const timerSeparators = document.querySelectorAll('.timer-separator');
        
        if (isUrgent) {
            // Add urgent classes
            timerUnits.forEach(unit => unit.classList.add('urgent'));
            timerSeparators.forEach(separator => separator.classList.add('urgent'));
            
            // Log urgency activation (only once)
            if (!this.urgentModeActive) {
                console.log('⚠️ URGENT: Less than 60 seconds remaining!');
                this.urgentModeActive = true;
            }
        } else {
            // Remove urgent classes
            timerUnits.forEach(unit => unit.classList.remove('urgent'));
            timerSeparators.forEach(separator => separator.classList.remove('urgent'));
            
            // Reset urgency flag
            this.urgentModeActive = false;
        }
    }
    
    updateProgress(timeRemaining) {
        if (!this.totalDuration || this.totalDuration <= 0) return;
        
        const progressEl = document.getElementById('timer-progress');
        if (!progressEl) return;
        
        const progress = Math.max(0, Math.min(100, ((this.totalDuration - timeRemaining) / this.totalDuration) * 100));
        progressEl.style.width = `${progress}%`;
    }
    
    onMarketClosed() {
        console.log('Market Timer: Market has closed');
        
        // Clear interval
        if (this.intervalId) {
            clearInterval(this.intervalId);
            this.intervalId = null;
        }
        
        // Update display to show market closed
        if (this.timerElement) {
            const messageEl = this.timerElement.querySelector('.market-close-message');
            
            if (messageEl) messageEl.textContent = 'Market is now closed';
            
            // Show final time as 00:00:00
            this.updateTimeDisplay(0, 0, 0, false);
        }
        
        // Auto-refresh page after brief delay
        setTimeout(() => {
            console.log('🔄 Market closed - Auto-refreshing from timer...');
            window.location.reload();
        }, 3000);
    }
    
    show() {
        if (!this.timerElement || this.isVisible) return;
        
        // Force reflow to ensure proper animation
        this.timerElement.offsetHeight;
        
        this.timerElement.classList.add('show');
        this.timerElement.classList.remove('hide');
        this.isVisible = true;
        
        console.log('Market Timer: Timer card shown');
    }
    
    hide() {
        if (!this.timerElement || !this.isVisible) return;
        
        this.timerElement.classList.add('hide');
        this.timerElement.classList.remove('show');
        this.isVisible = false;
        
        // Clear interval
        if (this.intervalId) {
            clearInterval(this.intervalId);
            this.intervalId = null;
        }
        
        // Remove element after animation
        setTimeout(() => {
            if (this.timerElement && this.timerElement.parentNode) {
                this.timerElement.parentNode.removeChild(this.timerElement);
            }
        }, 400);
        
        console.log('Market Timer: Timer card hidden');
    }
    
    padZero(num) {
        return num.toString().padStart(2, '0');
    }
    
    destroy() {
        if (this.intervalId) {
            clearInterval(this.intervalId);
            this.intervalId = null;
        }
        
        if (this.timerElement && this.timerElement.parentNode) {
            this.timerElement.parentNode.removeChild(this.timerElement);
        }
        
        this.timerElement = null;
        this.isVisible = false;
        console.log('Market Timer: Timer card destroyed');
    }
}

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Only initialize if we're on the dashboard page
    if (window.location.pathname.includes('dashboard') || document.querySelector('.enhanced-dashboard')) {
        // Small delay to ensure all other scripts have loaded
        setTimeout(() => {
            window.marketTimer = new MarketTimerCard();
        }, 500);
    }
});

// Cleanup on page unload
window.addEventListener('beforeunload', function() {
    if (window.marketTimer) {
        window.marketTimer.destroy();
    }
});

// Export for manual initialization if needed
window.MarketTimerCard = MarketTimerCard;