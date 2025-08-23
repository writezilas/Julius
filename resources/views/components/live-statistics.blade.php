<!-- Live Statistics Section -->
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header border-0 align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">
                    <i class="ri-pulse-line me-2"></i>Live Statistics
                </h4>
                <div class="flex-shrink-0">
                    <small class="text-muted" id="last-updated">Updated just now</small>
                </div>
            </div>
            <div class="card-body p-0">
                <!-- Navigation Tabs -->
                <ul class="nav nav-tabs nav-justified" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#leaderboard-tab" role="tab">
                            <i class="ri-trophy-line me-2"></i>Leaderboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#realtime-tab" role="tab">
                            <i class="ri-time-line me-2"></i>Real-time Stats
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#referrers-tab" role="tab">
                            <i class="ri-group-line me-2"></i>Top Referrers
                        </a>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content">
                    <!-- Leaderboard Tab -->
                    <div class="tab-pane active" id="leaderboard-tab" role="tabpanel">
                        <div class="p-3">
                            <h6 class="mb-3">Top Traders (Investment + Profit)</h6>
                            <div id="leaderboard-content">
                                <div class="d-flex justify-content-center py-4">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Real-time Stats Tab -->
                    <div class="tab-pane" id="realtime-tab" role="tabpanel">
                        <div class="p-3">
                            <h6 class="mb-3">Recent Activities (Last 24 Hours)</h6>
                            <div id="realtime-content">
                                <div class="d-flex justify-content-center py-4">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Top Referrers Tab -->
                    <div class="tab-pane" id="referrers-tab" role="tabpanel">
                        <div class="p-3">
                            <h6 class="mb-3">Top 10 Referrers</h6>
                            <div id="referrers-content">
                                <div class="d-flex justify-content-center py-4">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Custom styles for live statistics */
.live-stats-item {
    border-left: 4px solid #405189;
    transition: all 0.3s ease;
}

.live-stats-item:hover {
    background-color: #f8f9fa;
    border-left-color: #0ab39c;
}

.activity-item {
    border-left: 3px solid #e9ecef;
    transition: all 0.3s ease;
}

.activity-item.bought {
    border-left-color: #0ab39c;
}

.activity-item.sold {
    border-left-color: #f7b84b;
}

.leaderboard-rank {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 0.8rem;
}

.leaderboard-rank.rank-1 {
    background: linear-gradient(45deg, #ffd700, #ffed4e);
    color: #8b5a00;
}

.leaderboard-rank.rank-2 {
    background: linear-gradient(45deg, #c0c0c0, #e8e8e8);
    color: #6c757d;
}

.leaderboard-rank.rank-3 {
    background: linear-gradient(45deg, #cd7f32, #d4941e);
    color: #fff;
}

.leaderboard-rank.rank-other {
    background-color: #f8f9fa;
    color: #6c757d;
    border: 2px solid #e9ecef;
}

.stats-badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}
</style>

<script>
// Live Statistics JavaScript
let statisticsUpdateInterval;

$(document).ready(function() {
    // Load initial statistics
    loadLiveStatistics();
    
    // Set up auto-refresh every 30 seconds
    statisticsUpdateInterval = setInterval(loadLiveStatistics, 30000);
    
    // Handle tab switching
    $('.nav-tabs a').on('shown.bs.tab', function(e) {
        const target = $(e.target).attr("href");
        if (target === '#realtime-tab') {
            loadRealtimeStats();
        }
    });
});

function loadLiveStatistics() {
    $.ajax({
        url: '{{ route("api.live-statistics") }}',
        type: 'GET',
        success: function(response) {
            updateLeaderboard(response.leaderboard);
            updateRealtimeStats(response.realtime_stats);
            updateTopReferrers(response.top_referrers);
            updateLastUpdatedTime(response.last_updated);
        },
        error: function(xhr, status, error) {
            console.error('Failed to load statistics:', error);
            showErrorMessage();
        }
    });
}

function updateLeaderboard(data) {
    let html = '';
    
    if (data.length === 0) {
        html = '<div class="text-center text-muted py-4">No leaderboard data available</div>';
    } else {
        data.forEach(function(user, index) {
            const rank = index + 1;
            const rankClass = rank === 1 ? 'rank-1' : rank === 2 ? 'rank-2' : rank === 3 ? 'rank-3' : 'rank-other';
            const investment = parseFloat(user.total_investment_profit).toLocaleString('en-US', {minimumFractionDigits: 2});
            
            html += `
                <div class="d-flex align-items-center py-3 px-3 live-stats-item mb-2 rounded">
                    <div class="leaderboard-rank ${rankClass} me-3">
                        ${rank}
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1 fs-15">${user.username}</h6>
                        <p class="text-muted mb-0 fs-13">${user.name || 'N/A'}</p>
                    </div>
                    <div class="text-end">
                        <h6 class="mb-0 fs-14 text-success">Ksh ${investment}</h6>
                        <small class="text-muted">Total Investment + Profit</small>
                    </div>
                </div>
            `;
        });
    }
    
    $('#leaderboard-content').html(html);
}

function updateRealtimeStats(data) {
    let html = '';
    
    if (data.length === 0) {
        html = '<div class="text-center text-muted py-4">No recent activities</div>';
    } else {
        data.forEach(function(activity) {
            const amount = parseFloat(activity.amount).toLocaleString('en-US', {minimumFractionDigits: 2});
            const badgeClass = activity.type === 'bought' ? 'bg-success' : 'bg-warning';
            const iconClass = activity.type === 'bought' ? 'ri-shopping-cart-line' : 'ri-money-dollar-circle-line';
            
            html += `
                <div class="d-flex align-items-center py-3 px-3 activity-item ${activity.type} mb-2 rounded">
                    <div class="avatar-sm bg-light rounded-circle me-3 d-flex align-items-center justify-content-center">
                        <i class="${iconClass} fs-18 text-${activity.type === 'bought' ? 'success' : 'warning'}"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1 fs-14">
                            ${activity.username}
                            <span class="badge ${badgeClass} stats-badge ms-2">${activity.type.toUpperCase()}</span>
                        </h6>
                        <p class="text-muted mb-0 fs-13">
                            ${activity.trade_name} • Ticket: ${activity.ticket_no}
                        </p>
                    </div>
                    <div class="text-end">
                        <h6 class="mb-0 fs-14">Ksh ${amount}</h6>
                        <small class="text-muted">${activity.time}</small>
                    </div>
                </div>
            `;
        });
    }
    
    $('#realtime-content').html(html);
}

function updateTopReferrers(data) {
    let html = '';
    
    if (data.length === 0) {
        html = '<div class="text-center text-muted py-4">No referral data available</div>';
    } else {
        data.forEach(function(referrer, index) {
            const rank = index + 1;
            const rankClass = rank <= 3 ? `rank-${rank}` : 'rank-other';
            
            html += `
                <div class="d-flex align-items-center py-3 px-3 live-stats-item mb-2 rounded">
                    <div class="leaderboard-rank ${rankClass} me-3">
                        ${rank}
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1 fs-15">${referrer.username}</h6>
                        <p class="text-muted mb-0 fs-13">${referrer.name || 'N/A'}</p>
                    </div>
                    <div class="text-end">
                        <h6 class="mb-0 fs-14 text-primary">${referrer.referral_count}</h6>
                        <small class="text-muted">Referrals</small>
                    </div>
                </div>
            `;
        });
    }
    
    $('#referrers-content').html(html);
}

function updateLastUpdatedTime(timestamp) {
    const lastUpdated = new Date(timestamp);
    const now = new Date();
    const diffMs = now - lastUpdated;
    const diffSecs = Math.floor(diffMs / 1000);
    
    let timeText;
    if (diffSecs < 60) {
        timeText = 'Updated just now';
    } else if (diffSecs < 3600) {
        const mins = Math.floor(diffSecs / 60);
        timeText = `Updated ${mins} min${mins > 1 ? 's' : ''} ago`;
    } else {
        timeText = `Updated ${lastUpdated.toLocaleTimeString()}`;
    }
    
    $('#last-updated').text(timeText);
}

function showErrorMessage() {
    const errorHtml = '<div class="text-center text-danger py-4"><i class="ri-error-warning-line fs-24 mb-2"></i><br>Failed to load statistics</div>';
    $('#leaderboard-content').html(errorHtml);
    $('#realtime-content').html(errorHtml);
    $('#referrers-content').html(errorHtml);
}

// Clean up interval when page unloads
$(window).on('beforeunload', function() {
    if (statisticsUpdateInterval) {
        clearInterval(statisticsUpdateInterval);
    }
});
</script>
