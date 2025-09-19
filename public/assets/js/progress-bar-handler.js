/**
 * TradingProgressBarHandler - Real-time Progress Bar Management
 * 
 * This class handles real-time updates of trading progress bars on the dashboard.
 * It provides functionality for:
 * - Real-time progress updates via API calls
 * - Event-driven updates (trade completion, trade failure)
 * - Smooth animations and visual feedback
 * - Failed trade progress restoration with notifications
 * - Periodic background refresh
 * 
 * Usage:
 *   const handler = new TradingProgressBarHandler();
 *   handler.registerProgressBar(tradeId, progressBarElement);
 *   handler.startPeriodicRefresh();
 */

class TradingProgressBarHandler {
    constructor() {
        this.progressBars = new Map(); // tradeId -> { element, lastProgress, container }
        this.refreshInterval = null;
        this.apiEndpoint = '/api/trade-progress/all';
        this.isRefreshing = false;
        this.eventListeners = new Set();
        
        // Initialize event listeners
        this.initializeEventListeners();
        
        console.log('📊 TradingProgressBarHandler initialized');
    }

    /**
     * Register a progress bar element for a specific trade
     * @param {number} tradeId - The trade ID
     * @param {HTMLElement} progressBarElement - The progress bar element
     */
    registerProgressBar(tradeId, progressBarElement) {
        if (!progressBarElement) {
            console.warn(`⚠️ Progress bar element not found for trade ${tradeId}`);
            return;
        }

        // Find the container card for additional context
        const container = progressBarElement.closest('.bidding-card');
        
        this.progressBars.set(tradeId, {
            element: progressBarElement,
            container: container,
            lastProgress: this.getCurrentProgress(progressBarElement),
            lastUpdate: Date.now()
        });

        console.log(`✅ Registered progress bar for trade ${tradeId}`);
    }

    /**
     * Unregister a progress bar
     * @param {number} tradeId - The trade ID to unregister
     */
    unregisterProgressBar(tradeId) {
        if (this.progressBars.has(tradeId)) {
            this.progressBars.delete(tradeId);
            console.log(`❌ Unregistered progress bar for trade ${tradeId}`);
        }
    }

    /**
     * Initialize event listeners for trade events
     */
    initializeEventListeners() {
        // Listen for trade completed events
        document.addEventListener('tradeCompleted', (event) => {
            const { tradeId, newProgress, shares } = event.detail;
            this.handleTradeCompleted(tradeId, newProgress, shares);
        });

        // Listen for trade failed events
        document.addEventListener('tradeFailed', (event) => {
            const { tradeId, restoredShares, restoredProgress } = event.detail;
            this.handleTradeFailed(tradeId, restoredShares, restoredProgress);
        });

        console.log('🎯 Event listeners initialized');
    }

    /**
     * Handle trade completion event
     * @param {number} tradeId 
     * @param {number} newProgress 
     * @param {number} shares 
     */
    handleTradeCompleted(tradeId, newProgress, shares) {
        if (!this.progressBars.has(tradeId)) return;

        const barData = this.progressBars.get(tradeId);
        const { element, container } = barData;

        // Update progress with animation
        this.updateProgressWithAnimation(element, newProgress);

        // Show completion notification
        this.showProgressNotification(container, 'success', `✅ Trade completed! +${shares} shares`, 3000);

        // Update progress text in the card
        this.updateProgressText(container, newProgress);

        console.log(`🎉 Trade ${tradeId} completed: ${newProgress}%`);
    }

    /**
     * Handle trade failure event
     * @param {number} tradeId 
     * @param {number} restoredShares 
     * @param {number} restoredProgress 
     */
    handleTradeFailed(tradeId, restoredShares, restoredProgress) {
        if (!this.progressBars.has(tradeId)) return;

        const barData = this.progressBars.get(tradeId);
        const { element, container } = barData;

        // Update progress with restoration animation
        this.updateProgressWithAnimation(element, restoredProgress, 'restore');

        // Show restoration notification
        this.showProgressNotification(
            container, 
            'warning', 
            `🔄 Progress restored: ${restoredShares} shares returned`, 
            4000
        );

        // Update progress text
        this.updateProgressText(container, restoredProgress);

        console.log(`⚠️ Trade ${tradeId} failed: progress restored to ${restoredProgress}%`);
    }

    /**
     * Update progress bar with smooth animation
     * @param {HTMLElement} element 
     * @param {number} newProgress 
     * @param {string} animationType 
     */
    updateProgressWithAnimation(element, newProgress, animationType = 'normal') {
        if (!element) return;

        const currentWidth = parseFloat(element.style.width || '0');
        const targetWidth = Math.min(Math.max(newProgress, 0), 100);

        // Add animation class based on type
        const animationClass = animationType === 'restore' ? 'progress-restore' : 'progress-update';
        element.classList.add(animationClass);

        // Animate to new width
        element.style.transition = 'width 0.8s cubic-bezier(0.4, 0, 0.2, 1)';
        element.style.width = `${targetWidth}%`;

        // Remove animation class after animation completes
        setTimeout(() => {
            element.classList.remove(animationClass);
        }, 800);

        // Update color based on progress
        this.updateProgressColor(element, targetWidth);
    }

    /**
     * Update progress bar color based on completion percentage
     * @param {HTMLElement} element 
     * @param {number} progress 
     */
    updateProgressColor(element, progress) {
        // Remove existing color classes
        element.classList.remove('progress-low', 'progress-medium', 'progress-high', 'progress-complete');

        if (progress >= 100) {
            element.classList.add('progress-complete');
        } else if (progress >= 75) {
            element.classList.add('progress-high');
        } else if (progress >= 50) {
            element.classList.add('progress-medium');
        } else {
            element.classList.add('progress-low');
        }
    }

    /**
     * Show notification on the trading card
     * @param {HTMLElement} container 
     * @param {string} type 
     * @param {string} message 
     * @param {number} duration 
     */
    showProgressNotification(container, type, message, duration = 3000) {
        if (!container) return;

        // Remove existing notifications
        const existingNotification = container.querySelector('.progress-notification');
        if (existingNotification) {
            existingNotification.remove();
        }

        // Create notification element
        const notification = document.createElement('div');
        notification.className = `progress-notification progress-notification-${type}`;
        notification.textContent = message;

        // Insert notification
        const cardHeader = container.querySelector('.bidding-card-header');
        if (cardHeader) {
            cardHeader.appendChild(notification);
        } else {
            container.appendChild(notification);
        }

        // Auto-remove after duration
        setTimeout(() => {
            if (notification.parentNode) {
                notification.classList.add('fade-out');
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }
        }, duration);
    }

    /**
     * Update progress text in the card
     * @param {HTMLElement} container 
     * @param {number} progress 
     */
    updateProgressText(container, progress) {
        if (!container) return;

        // Update progress text in bidding info section (third item is progress)
        const progressInfoValue = container.querySelector('.bidding-info-item:last-child .bidding-info-value');
        if (progressInfoValue) {
            progressInfoValue.textContent = `${progress.toFixed(1)}%`;
        }

        // Update progress label in progress section
        const progressLabel = container.querySelector('.progress-label .progress-text');
        if (progressLabel) {
            progressLabel.textContent = `${progress.toFixed(1)}% Complete`;
        }

        // Update data attribute for consistency
        const progressBar = container.querySelector('.bidding-progress-bar');
        if (progressBar) {
            progressBar.setAttribute('data-progress', progress.toFixed(1));
        }
    }

    /**
     * Get current progress from element
     * @param {HTMLElement} element 
     * @returns {number}
     */
    getCurrentProgress(element) {
        if (!element) return 0;
        
        // Try to get from style width first
        let width = element.style.width || '0%';
        let progress = parseFloat(width.replace('%', ''));
        
        // If no style width, try to get from data attribute
        if (isNaN(progress) || progress === 0) {
            const dataProgress = element.getAttribute('data-progress');
            if (dataProgress) {
                progress = parseFloat(dataProgress);
            }
        }
        
        return isNaN(progress) ? 0 : progress;
    }

    /**
     * Fetch updated progress data from API
     * @returns {Promise<Object>}
     */
    async fetchProgressData() {
        try {
            const response = await fetch(this.apiEndpoint, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                credentials: 'same-origin'
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();
            
            if (data.success && data.data) {
                return data.data;
            } else {
                throw new Error(data.message || 'Invalid response format');
            }
        } catch (error) {
            console.error('❌ Error fetching progress data:', error);
            return null;
        }
    }

    /**
     * Update all registered progress bars with fresh data
     */
    async refreshAllProgressBars() {
        if (this.isRefreshing || this.progressBars.size === 0) return;

        this.isRefreshing = true;
        console.log('🔄 Refreshing progress bars...');

        try {
            const progressData = await this.fetchProgressData();
            
            if (!progressData) {
                console.warn('⚠️ No progress data received from API');
                return;
            }

            let updatedCount = 0;

            // Update each registered progress bar
            for (const [tradeId, barData] of this.progressBars.entries()) {
                const tradeProgress = progressData.find(item => item.trade_id == tradeId);
                
                if (tradeProgress) {
                    const newProgress = tradeProgress.progress_percentage;
                    const currentProgress = barData.lastProgress;

                    // Only update if progress has changed significantly
                    if (Math.abs(newProgress - currentProgress) > 0.1) {
                        this.updateProgressWithAnimation(barData.element, newProgress);
                        this.updateProgressText(barData.container, newProgress);
                        
                        barData.lastProgress = newProgress;
                        barData.lastUpdate = Date.now();
                        updatedCount++;

                        console.log(`📊 Updated trade ${tradeId}: ${currentProgress}% → ${newProgress}%`);
                    }
                }
            }

            console.log(`✅ Progress refresh completed (${updatedCount} bars updated)`);

        } catch (error) {
            console.error('❌ Error during progress refresh:', error);
        } finally {
            this.isRefreshing = false;
        }
    }

    /**
     * Start periodic refresh of progress bars
     * @param {number} intervalMs - Refresh interval in milliseconds (default: 30 seconds)
     */
    startPeriodicRefresh(intervalMs = 30000) {
        // Stop any existing interval
        this.stopPeriodicRefresh();

        this.refreshInterval = setInterval(() => {
            this.refreshAllProgressBars();
        }, intervalMs);

        console.log(`⏰ Started periodic refresh (every ${intervalMs / 1000}s)`);
    }

    /**
     * Stop periodic refresh
     */
    stopPeriodicRefresh() {
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
            this.refreshInterval = null;
            console.log('⏹️ Stopped periodic refresh');
        }
    }

    /**
     * Force immediate refresh of all progress bars
     */
    forceRefresh() {
        console.log('🔄 Force refreshing progress bars...');
        this.refreshAllProgressBars();
    }

    /**
     * Get status information
     * @returns {Object}
     */
    getStatus() {
        return {
            registeredBars: this.progressBars.size,
            isRefreshing: this.isRefreshing,
            hasPeriodicRefresh: this.refreshInterval !== null,
            apiEndpoint: this.apiEndpoint
        };
    }

    /**
     * Cleanup - remove all event listeners and intervals
     */
    destroy() {
        this.stopPeriodicRefresh();
        this.progressBars.clear();
        console.log('🗑️ TradingProgressBarHandler destroyed');
    }
}

// Global helper functions for other scripts to trigger events
window.triggerTradeCompleted = function(tradeId, newProgress, shares = 1) {
    const event = new CustomEvent('tradeCompleted', {
        detail: { tradeId, newProgress, shares }
    });
    document.dispatchEvent(event);
    console.log(`🎯 Triggered tradeCompleted event for trade ${tradeId}`);
};

window.triggerTradeFailed = function(tradeId, restoredShares, restoredProgress) {
    const event = new CustomEvent('tradeFailed', {
        detail: { tradeId, restoredShares, restoredProgress }
    });
    document.dispatchEvent(event);
    console.log(`🎯 Triggered tradeFailed event for trade ${tradeId}`);
};

// Export the class for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = TradingProgressBarHandler;
}

// Add CSS styles for progress bar animations and notifications
document.addEventListener('DOMContentLoaded', function() {
    const style = document.createElement('style');
    style.textContent = `
        /* Progress Bar Animation Styles */
        .progress-update {
            box-shadow: 0 0 10px rgba(0, 123, 255, 0.5);
        }
        
        .progress-restore {
            box-shadow: 0 0 10px rgba(255, 193, 7, 0.5);
        }
        
        /* Progress Bar Color States */
        .progress-low {
            background: linear-gradient(90deg, #dc3545, #fd7e14);
        }
        
        .progress-medium {
            background: linear-gradient(90deg, #ffc107, #20c997);
        }
        
        .progress-high {
            background: linear-gradient(90deg, #20c997, #28a745);
        }
        
        .progress-complete {
            background: linear-gradient(90deg, #28a745, #17a2b8);
            box-shadow: 0 0 15px rgba(40, 167, 69, 0.4);
        }
        
        /* Progress Notifications */
        .progress-notification {
            position: absolute;
            top: -5px;
            right: -5px;
            background: rgba(0, 0, 0, 0.9);
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.7rem;
            z-index: 10;
            animation: slideInNotification 0.3s ease-out;
            max-width: 200px;
            text-overflow: ellipsis;
            overflow: hidden;
            white-space: nowrap;
        }
        
        .progress-notification-success {
            background: rgba(40, 167, 69, 0.9);
            border-left: 3px solid #28a745;
        }
        
        .progress-notification-warning {
            background: rgba(255, 193, 7, 0.9);
            border-left: 3px solid #ffc107;
            color: #212529;
        }
        
        .progress-notification-error {
            background: rgba(220, 53, 69, 0.9);
            border-left: 3px solid #dc3545;
        }
        
        .progress-notification.fade-out {
            animation: fadeOutNotification 0.3s ease-in;
            opacity: 0;
        }
        
        @keyframes slideInNotification {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes fadeOutNotification {
            from {
                opacity: 1;
            }
            to {
                opacity: 0;
            }
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .progress-notification {
                font-size: 0.65rem;
                padding: 2px 6px;
                max-width: 150px;
            }
        }
    `;
    
    document.head.appendChild(style);
    console.log('🎨 Progress bar handler styles loaded');
});

console.log('📊 TradingProgressBarHandler module loaded successfully');
