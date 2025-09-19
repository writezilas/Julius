<!-- Live Statistics Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card bg-gradient-primary text-white border-0 shadow-lg">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h4 class="text-white mb-1">Live Statistics</h4>
                        <p class="text-white-75 mb-0 fs-14">Real-time system data updates every 30 seconds</p>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-light btn-sm" id="refreshStatsBtn">
                            <i class="fas fa-sync-alt me-1" id="refreshIcon"></i> Refresh
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row" id="liveStatsContainer">
    <!-- Top Traders Card -->
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 pb-0">
                <h5 class="text-dark mb-0">
                    <i class="fas fa-chart-line me-2 text-primary"></i>Top Traders
                </h5>
            </div>
            <div class="card-body flex-fill d-flex flex-column" style="min-height: 400px; max-height: 400px; overflow-y: auto;">
                <div id="topTradersContent" class="flex-fill">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 mb-0 text-muted">Loading top traders...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity Card -->
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 pb-0">
                <h5 class="text-dark mb-0">
                    <i class="fas fa-clock me-2 text-success"></i>Recent Activity
                </h5>
            </div>
            <div class="card-body flex-fill d-flex flex-column" style="min-height: 400px; max-height: 400px; overflow-y: auto;">
                <div id="recentActivityContent" class="flex-fill">
                    <div class="text-center py-4">
                        <div class="spinner-border text-success" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 mb-0 text-muted">Loading recent activity...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Referrers Card -->
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 pb-0">
                <h5 class="text-dark mb-0">
                    <i class="fas fa-users me-2 text-info"></i>Top Referrers
                </h5>
            </div>
            <div class="card-body flex-fill d-flex flex-column" style="min-height: 400px; max-height: 400px; overflow-y: auto;">
                <div id="topReferrersContent" class="flex-fill">
                    <div class="text-center py-4">
                        <div class="spinner-border text-info" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 mb-0 text-muted">Loading top referrers...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Custom gradient background for main header */
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
}

/* White card styling with subtle hover effects */
.card {
    background-color: #fff;
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

/* Ensure proper text contrast on white cards */
.card .text-primary {
    color: #0d6efd !important;
}

.card .text-success {
    color: #198754 !important;
}

.card .text-info {
    color: #0dcaf0 !important;
}

/* Badge styles for top 3 positions */
.badge.bg-warning {
    background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%) !important;
    box-shadow: 0 2px 4px rgba(255, 215, 0, 0.3);
}

.badge.bg-light {
    background: linear-gradient(135deg, #c0c0c0 0%, #e8e8e8 100%) !important;
    box-shadow: 0 2px 4px rgba(192, 192, 192, 0.3);
}

.badge.bg-secondary {
    background: linear-gradient(135deg, #cd7f32 0%, #8b4513 100%) !important;
    box-shadow: 0 2px 4px rgba(205, 127, 50, 0.3);
}

/* Loading spinner */
.spinner-border-sm {
    width: 1rem;
    height: 1rem;
}

/* Text opacity utilities for better readability on colored backgrounds */
.text-white-75 {
    color: rgba(255, 255, 255, 0.75) !important;
}

.border-light.border-opacity-25 {
    border-color: rgba(255, 255, 255, 0.25) !important;
}
</style>

<script>
// JavaScript for Live Statistics
class LiveStatistics {
    constructor() {
        this.refreshInterval = null;
        this.isLoading = false;
        this.init();
    }

    init() {
        // Load initial data
        this.loadAllStats();
        
        // Set up auto-refresh every 30 seconds
        this.startAutoRefresh();
        
        // Set up manual refresh button
        document.getElementById('refreshStatsBtn').addEventListener('click', () => {
            this.manualRefresh();
        });
    }

    startAutoRefresh() {
        this.refreshInterval = setInterval(() => {
            this.loadAllStats();
        }, 30000); // 30 seconds
    }

    stopAutoRefresh() {
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
            this.refreshInterval = null;
        }
    }

    manualRefresh() {
        if (this.isLoading) return;
        
        const btn = document.getElementById('refreshStatsBtn');
        const icon = document.getElementById('refreshIcon');
        
        btn.disabled = true;
        icon.classList.add('fa-spin');
        
        this.loadAllStats().finally(() => {
            btn.disabled = false;
            icon.classList.remove('fa-spin');
        });
    }

    async loadAllStats() {
        if (this.isLoading) return;
        this.isLoading = true;

        try {
            const response = await fetch('{{ route("api.live-statistics") }}', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            
            // Update each section
            this.updateTopTraders(data.leaderboard || []);
            this.updateRecentActivity(data.realtime_stats || []);
            this.updateTopReferrers(data.top_referrers || []);
            
        } catch (error) {
            console.error('Error loading live statistics:', error);
            this.showError('Failed to load live statistics');
        } finally {
            this.isLoading = false;
        }
    }

    updateTopTraders(traders) {
        const container = document.getElementById('topTradersContent');
        
        if (!traders || traders.length === 0) {
            container.innerHTML = `
                <div class="text-center py-4">
                    <i class="fas fa-chart-line mb-3 text-muted" style="font-size: 2rem; opacity: 0.5;"></i>
                    <p class="mb-0 text-muted">No trading data available</p>
                </div>
            `;
            return;
        }

        let html = '';
        traders.slice(0, 10).forEach((trader, index) => {
            const position = index + 1;
            const badge = this.getBadgeForPosition(position);
            const amount = parseFloat(trader.total_investment || 0);
            
            html += `
                <div class="d-flex align-items-center justify-content-between py-2 border-bottom border-light">
                    <div class="d-flex align-items-center">
                        ${badge}
                        <div>
                            <div class="fw-bold text-dark">${this.escapeHtml(trader.username || 'Unknown')}</div>
                            <small class="text-muted">Investment</small>
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold text-primary">KSH ${this.formatCurrency(amount)}</div>
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
    }

    updateRecentActivity(activities) {
        const container = document.getElementById('recentActivityContent');
        
        if (!activities || activities.length === 0) {
            container.innerHTML = `
                <div class="text-center py-4">
                    <i class="fas fa-clock mb-3 text-muted" style="font-size: 2rem; opacity: 0.5;"></i>
                    <p class="mb-0 text-muted">No recent activity</p>
                </div>
            `;
            return;
        }

        let html = '';
        activities.slice(0, 10).forEach((activity) => {
            const amount = parseFloat(activity.amount || 0);
            const actionText = activity.type === 'bought' ? 'Bought shares' : 'Sold shares';
            
            html += `
                <div class="d-flex flex-column py-2 border-bottom border-light">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fw-bold text-dark">${this.escapeHtml(activity.username || 'Unknown')}</div>
                            <small class="text-muted">${actionText}</small>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold text-success">KSH ${this.formatCurrency(amount)}</div>
                            <small class="text-muted">${activity.time || 'Unknown time'}</small>
                        </div>
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
    }

    updateTopReferrers(referrers) {
        const container = document.getElementById('topReferrersContent');
        
        if (!referrers || referrers.length === 0) {
            container.innerHTML = `
                <div class="text-center py-4">
                    <i class="fas fa-users mb-3 text-muted" style="font-size: 2rem; opacity: 0.5;"></i>
                    <p class="mb-0 text-muted">No referrers yet</p>
                </div>
            `;
            return;
        }

        let html = '';
        referrers.slice(0, 10).forEach((referrer, index) => {
            const position = index + 1;
            const badge = this.getBadgeForPosition(position);
            const referralCount = parseInt(referrer.referral_count || 0);
            
            html += `
                <div class="d-flex align-items-center justify-content-between py-2 border-bottom border-light">
                    <div class="d-flex align-items-center">
                        ${badge}
                        <div>
                            <div class="fw-bold text-dark">${this.escapeHtml(referrer.username || 'Unknown')}</div>
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="fw-bold text-info">${referralCount}</div>
                        <small class="text-muted">Referrals</small>
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
    }

    getBadgeForPosition(position) {
        const badgeClass = 'badge rounded-pill me-2 fs-12';
        
        switch(position) {
            case 1:
                return `<span class="${badgeClass} bg-warning text-dark">#${position}</span>`;
            case 2:
                return `<span class="${badgeClass} bg-light text-dark">#${position}</span>`;
            case 3:
                return `<span class="${badgeClass} bg-secondary">#${position}</span>`;
            default:
                return `<span class="${badgeClass} bg-dark bg-opacity-50">#${position}</span>`;
        }
    }

    formatCurrency(amount) {
        return new Intl.NumberFormat('en-KE', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(amount);
    }

    escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.toString().replace(/[&<>"']/g, m => map[m]);
    }

    showError(message) {
        const containers = [
            'topTradersContent', 
            'recentActivityContent', 
            'topReferrersContent'
        ];
        
        containers.forEach(containerId => {
            const container = document.getElementById(containerId);
            if (container) {
                container.innerHTML = `
                    <div class="text-center py-4">
                        <i class="fas fa-exclamation-triangle mb-3 text-warning" style="font-size: 2rem; opacity: 0.5;"></i>
                        <p class="mb-0 text-muted">${message}</p>
                        <button class="btn btn-primary btn-sm mt-2" onclick="liveStats.loadAllStats()">
                            Retry
                        </button>
                    </div>
                `;
            }
        });
    }

    destroy() {
        this.stopAutoRefresh();
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.liveStats = new LiveStatistics();
});

// Cleanup when page unloads
window.addEventListener('beforeunload', function() {
    if (window.liveStats) {
        window.liveStats.destroy();
    }
});
</script>
