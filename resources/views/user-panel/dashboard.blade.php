@extends('layouts.master')
@section('title') @lang('translation.dashboard') @endsection

@section('css')
<!-- Enhanced Dashboard Styles -->
<link href="{{ URL::asset('/assets/css/dashboard-enhanced.css') }}" rel="stylesheet">
@endsection

@section('content')

    @component('components.breadcrumb')
        @slot('li_1') Home @endslot
        @slot('title') Dashboard @endslot
    @endcomponent
    
    <div class="enhanced-dashboard">

    <!-- Stats Overview Section -->    
    <div class="row">
        @php
            $investment = \App\Models\UserShare::where('status', 'completed')
                            ->where('user_id', auth()->user()->id)->sum('amount');
            $profit = \App\Models\UserShare::where('status', 'completed')
                    ->where('user_id', auth()->user()->id)->sum('profit_share');
            $referralAmount = auth()->user()->referrals ? auth()->user()->referrals->sum('ref_amount') : 0;
        @endphp
        
        <!-- Investment Stats -->        
        <div class="col-xl-3 col-md-6">
            <div class="card card-animate stats-card">
                <div class="card-body">
                    <div class="stats-icon">
                        <i data-feather="briefcase"></i>
                    </div>
                    <div class="stats-value counter-value" data-target="{{ $investment }}">0</div>
                    <div class="stats-label">Total Investment</div>
                </div>
            </div>
        </div>
        
        <!-- Earnings Stats -->        
        <div class="col-xl-3 col-md-6">
            <div class="card card-animate stats-card">
                <div class="card-body">
                    <div class="stats-icon warning">
                        <i data-feather="award"></i>
                    </div>
                    <div class="stats-value counter-value" data-target="{{ $profit }}">0</div>
                    <div class="stats-label">Total Earnings</div>
                </div>
            </div>
        </div>
        
        <!-- Expense Stats -->        
        <div class="col-xl-3 col-md-6">
            <div class="card card-animate stats-card">
                <div class="card-body">
                    <div class="stats-icon info">
                        <i data-feather="credit-card"></i>
                    </div>
                    <div class="stats-value counter-value" data-target="0">0</div>
                    <div class="stats-label">Total Expenses</div>
                </div>
            </div>
        </div>
        
        <!-- Referrals Stats -->        
        <div class="col-xl-3 col-md-6">
            <div class="card card-animate stats-card">
                <div class="card-body">
                    <div class="stats-icon success">
                        <i data-feather="users"></i>
                    </div>
                    <div class="stats-value counter-value" data-target="{{ $referralAmount }}">0</div>
                    <div class="stats-label">Referral Earnings</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Available Shares Section -->    
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Available Shares</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        @php
                            $trades = \App\Models\Trade::whereStatus('1')->get();
                        @endphp
                        @foreach($trades as $trade)
                            <div class="col-xl-3 col-md-6">
                                <div class="card card-animate stats-card mb-4">
                                    <div class="card-body">
                                        <div class="stats-icon">
                                            <i data-feather="trending-up"></i>
                                        </div>
                                        <div class="stats-value counter-value" data-target="{{ checkAvailableSharePerTrade($trade->id) }}">0</div>
                                        <div class="stats-label">{{ $trade->name }}</div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Trading Section with Modern UI -->    
    <div class="row mt-4">
        @php
            $appTimezone = get_app_timezone();
            $now = \Carbon\Carbon::now($appTimezone);
            $todayDate = $now->format('Y-m-d');
            
            // Use the helper functions for timezone-aware market timing
            $isTradeOpen = is_market_open();
            $timeSlots = get_markets();
            
            // Get next opening time if market is closed
            $open = null;
            if (!$isTradeOpen) {
                $open = get_next_market_open_time();
            }
        @endphp
        
        @if($isTradeOpen || count($timeSlots) == 0)
            @php
                $trades = \App\Models\Trade::where('status', 1)->OrderBy('id', 'desc')->get();
            @endphp
            @foreach($trades as $key => $trade)
                <div class="col-xl-6 col-lg-6">
                    <div class="card trading-card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h4 class="card-title mb-0 text-white">{{ $trade->name }}</h4>
                                <span class="badge bg-light text-dark">{{ checkAvailableSharePerTrade($trade->id) }} Shares Available</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('user.bid') }}" method="post" class="trading-form">
                                @csrf
                                <div class="mb-4">
                                    <div class="input-group mb-3">
                                        <span class="input-group-text">Amount</span>
                                        <input type="number" class="form-control" placeholder="Enter bid amount" name="amount" required>
                                        <input type="hidden" value="{{ $trade->id }}" name="trade_id">
                                    </div>
                                    <div class="input-group">
                                        <span class="input-group-text">Period</span>
                                        @php
                                            $periods = \App\Models\TradePeriod::where('status', 1)->orderBy('days', 'asc')->get();
                                        @endphp
                                        <select class="form-select" name="period" required>
                                            <option value="">Select investment period</option>
                                            @foreach($periods as $period)
                                                <option value="{{ $period->days }}">{{$period->days}} days ({{$period->percentage}}%)</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary w-100">Place Bid Now</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="col-lg-8 mx-auto">
                <div class="card market-closed-card">
                    <div class="card-body text-center p-5">
                        <div class="mb-4">
                            <i data-feather="clock" class="text-white" style="width: 60px; height: 60px;"></i>
                        </div>
                        <h2 class="text-white mb-3">Market Currently Closed</h2>
                        <p class="text-white opacity-75 mb-4 fs-5">The auction is closed at the moment. Please check back when it's time to bid.</p>
                        
                        @if($open)
                            <div class="mb-4">
                                <p class="text-white mb-2">Market will open at:</p>
                                <div class="countdown-timer" id="count-down" data-time="{{$open->utc()}}">0d 0h 0m 0s</div>
                                <small class="d-block text-white opacity-75">Time shown in {{ $appTimezone }} timezone</small>
                            </div>
                        @endif
                        
                        <a href="{{ route('user.dashboard') }}" class="btn btn-light btn-lg px-4">Refresh Page</a>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Live Statistics Section -->
    <div class="row live-stats-container">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="card-title mb-0">Live Statistics</h4>
                        <small class="text-muted">Real-time platform data (updates every 30 seconds)</small>
                    </div>
                    <div>
                        <button id="refresh-stats-btn" class="btn btn-sm btn-primary"><i data-feather="refresh-cw" class="icon-sm me-1"></i> Refresh</button>
                    </div>
                </div>
                <div class="card-body pb-0">
                    <div class="row">
                        <!-- Leaderboard Section -->
                        <div class="col-xl-4 col-lg-6 mb-4">
                            <div class="h-100">
                                <div class="card live-stats-card bg-primary text-white">
                                    <div class="card-body">
                                        <h5 class="card-title text-white mb-3 d-flex align-items-center">
                                            <i class="fas fa-trophy me-2"></i> Top Traders
                                        </h5>
                                        <div id="leaderboard-data">
                                            <div class="live-stats-loading">
                                                <div class="spinner-border text-light spinner-border-sm" role="status">
                                                    <span class="visually-hidden">Loading...</span>
                                                </div>
                                                <span class="ms-2">Loading...</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Real-time Stats Section -->
                        <div class="col-xl-4 col-lg-6 mb-4">
                            <div class="h-100">
                                <div class="card live-stats-card bg-success text-white">
                                    <div class="card-body">
                                        <h5 class="card-title text-white mb-3 d-flex align-items-center">
                                            <i class="fas fa-chart-line me-2"></i> Recent Activity
                                        </h5>
                                        <div id="realtime-stats">
                                            <div class="live-stats-loading">
                                                <div class="spinner-border text-light spinner-border-sm" role="status">
                                                    <span class="visually-hidden">Loading...</span>
                                                </div>
                                                <span class="ms-2">Loading...</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Top Referrers Section -->
                        <div class="col-xl-4 col-lg-12 mb-4">
                            <div class="h-100">
                                <div class="card live-stats-card bg-info text-white">
                                    <div class="card-body">
                                        <h5 class="card-title text-white mb-3 d-flex align-items-center">
                                            <i class="fas fa-users me-2"></i> Top Referrers
                                        </h5>
                                        <div id="referrers-data">
                                            <div class="live-stats-loading">
                                                <div class="spinner-border text-light spinner-border-sm" role="status">
                                                    <span class="visually-hidden">Loading...</span>
                                                </div>
                                                <span class="ms-2">Loading...</span>
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
    </div><!-- end live statistics row -->

    <!-- Activities and Announcements -->
    <div class="row mt-4">
        <div class="col-xl-6 col-lg-12 mb-4">
            <div class="card activity-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Recent Activities</h4>
                    <span class="badge bg-light text-primary">Your History</span>
                </div>
                <div class="card-body p-0">
                    <div data-simplebar style="max-height: 400px;">
                        <div class="p-3">
                            @php
                                if(auth()->check()) {
                                    $logs = auth()->user()->alllogs->sortByDesc('id');
                                }
                            @endphp

                            @if(count($logs) > 0)
                                @foreach($logs as $log)
                                    <div class="activity-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1">{{ $log->remarks }}</h6>
                                                <p class="text-muted mb-0 small">
                                                    <i class="mdi mdi-clock text-success me-1"></i>
                                                    {{ $log->created_at->diffForHumans() }}
                                                </p>
                                            </div>
                                            <div class="text-end">
                                                <h6 class="mb-1 text-success fw-semibold">
                                                    {{ $log->value }} {{ $log->type == 'share' ? 'share' : 'KSH' }}
                                                </h6>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="text-center p-4">
                                    <div class="mb-3">
                                        <i data-feather="activity" style="width: 40px; height: 40px; color: #ccc;"></i>
                                    </div>
                                    <h6 class="text-muted">No activities found</h6>
                                    <p class="small text-muted">Your recent activities will appear here</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-6 col-lg-12 mb-4">
            <div class="card announcement-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Announcements</h4>
                    <span class="badge bg-light text-primary">Latest Updates</span>
                </div>
                <div class="card-body p-0">
                    <div data-simplebar style="max-height: 400px;">
                        <div class="p-3">
                            @php
                                $announcements = \App\Models\Announcement::where('status', '1')->orderBy('id', 'desc')->paginate(5);
                            @endphp
                            
                            @if(count($announcements) > 0)
                                @foreach($announcements as $key => $announcement)
                                    <div class="announcement-item">
                                        <h4>{{ $announcement->title }}</h4>
                                        <p class="text-muted small mb-2">
                                            <i class="mdi mdi-calendar me-1"></i> {{ $announcement->created_at->diffForHumans() }}
                                        </p>
                                        <p class="mb-2">{{ $announcement->excerpt }}</p>
                                        <a href="javascript:void(0)" class="badge bg-primary" data-bs-toggle="modal" data-bs-target="#announcementModal{{ $announcement->id }}">View Details</a>
                                    </div>

                                    <!-- Announcement Modal -->
                                    <div class="modal fade" id="announcementModal{{ $announcement->id }}" tabindex="-1" aria-labelledby="announcementModalLabel{{ $announcement->id }}" aria-hidden="true">
                                        <div class="modal-dialog modal-lg modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="announcementModalLabel{{ $announcement->id }}">
                                                        {{ $announcement->title }}
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="announcement-content">
                                                        {!! $announcement->description !!}
                                                    </div>
                                                    @if($announcement->image)
                                                        <div class="announcement-image mt-3 rounded overflow-hidden">
                                                            <img src="{{ asset($announcement->image) }}" alt="{{ $announcement->title }}" class="img-fluid">
                                                        </div>
                                                    @endif
                                                    @if($announcement->video_url)
                                                        <div class="announcement-video mt-3 rounded overflow-hidden">
                                                            <iframe width="100%" height="315" src="{{ $announcement->video_url }}" allowfullscreen></iframe>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                
                                <div class="mt-3 d-flex justify-content-end">
                                    {{ $announcements->links() }}
                                </div>
                            @else
                                <div class="text-center p-4">
                                    <div class="mb-3">
                                        <i data-feather="bell" style="width: 40px; height: 40px; color: #ccc;"></i>
                                    </div>
                                    <h6 class="text-muted">No announcements found</h6>
                                    <p class="small text-muted">Check back later for updates</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div><!-- end enhanced dashboard wrapper -->
@endsection
@section('script')
    <!-- apexcharts -->
    <script src="{{ URL::asset('/assets/libs/apexcharts/apexcharts.min.js') }}"></script>
    <script src="{{ URL::asset('/assets/libs/jsvectormap/jsvectormap.min.js') }}"></script>
    <script src="{{ URL::asset('assets/libs/swiper/swiper.min.js')}}"></script>
    <!-- dashboard init -->
    <script src="{{ URL::asset('/assets/js/pages/dashboard-ecommerce.init.js') }}"></script>
    <!-- Enhanced dashboard functionality -->
    <script src="{{ URL::asset('/assets/js/dashboard-enhanced.js') }}"></script>

    <script>
        // Enhanced Counter Animation
        document.addEventListener('DOMContentLoaded', function() {
            const counterElements = document.querySelectorAll('.counter-value');
            
            counterElements.forEach(counter => {
                const target = parseInt(counter.getAttribute('data-target')) || 0;
                const duration = 1500; // Animation duration in milliseconds
                const frameDuration = 1000 / 60; // 60fps
                const totalFrames = Math.round(duration / frameDuration);
                const easeOutQuad = t => t * (2 - t); // Easing function
                
                let frame = 0;
                const countTo = target;
                
                // Start the animation
                const animate = () => {
                    frame++;
                    const progress = easeOutQuad(frame / totalFrames);
                    const currentCount = Math.round(countTo * progress);
                    
                    // Update the count
                    counter.innerHTML = currentCount.toLocaleString();
                    
                    // If we haven't reached the target yet, request another frame
                    if (frame < totalFrames) {
                        requestAnimationFrame(animate);
                    }
                };
                
                animate();
            });
            
            // Countdown Timer for Market Open
            const countdownElement = document.getElementById('count-down');
            if (countdownElement) {
                const getCounterTime = (startTime, id) => {
                    // Parse the input date string into a UTC date object
                    const countDownDate = new Date(startTime + ' UTC').getTime();
                    
                    // Update the count down every 1 second
                    const x = setInterval(function() {
                        // Get the current UTC date and time
                        const now = new Date().getTime();
                        
                        // Find the distance between now and the count down date
                        const distance = countDownDate - now;
                        
                        // Time calculations for days, hours, minutes, and seconds
                        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                        const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                        
                        // Output the result in an element with id="count-down"
                        document.getElementById(id).innerHTML = `${days}d ${hours}h ${minutes}m ${seconds}s`;
                        
                        // If the count down is over, refresh the page
                        if (distance < 0) {
                            clearInterval(x);
                            document.getElementById(id).innerHTML = "Market open now!";
                            setTimeout(() => {
                                window.location.reload();
                            }, 3000);
                        }
                    }, 1000);
                };
                
                const countdownTime = countdownElement.getAttribute('data-time');
                if (countdownTime) {
                    getCounterTime(countdownTime, "count-down");
                }
            }
            
            // Live Statistics Functions with loading animation
            const loadLiveStatistics = () => {
                // Show loading indicators
                document.getElementById('leaderboard-data').innerHTML = `
                    <div class="live-stats-loading">
                        <div class="spinner-border text-light spinner-border-sm" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <span class="ms-2">Loading...</span>
                    </div>
                `;
                document.getElementById('realtime-stats').innerHTML = `
                    <div class="live-stats-loading">
                        <div class="spinner-border text-light spinner-border-sm" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <span class="ms-2">Loading...</span>
                    </div>
                `;
                document.getElementById('referrers-data').innerHTML = `
                    <div class="live-stats-loading">
                        <div class="spinner-border text-light spinner-border-sm" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <span class="ms-2">Loading...</span>
                    </div>
                `;
                
                // Load Leaderboard
                fetch('/api/live-statistics?type=leaderboard')
                    .then(response => response.json())
                    .then(data => {
                        let html = '';
                        if (data.leaderboard && data.leaderboard.length > 0) {
                            data.leaderboard.forEach((trader, index) => {
                                const position = index + 1;
                                const badge = position <= 3 ? 
                                    `<span class="badge bg-warning live-stats-position">#${position}</span>` : 
                                    `<small class="text-light live-stats-position">#${position}</small>`;
                                    
                                html += `
                                    <div class="live-stats-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                ${badge}
                                                <span class="ms-2 fw-medium">${trader.username}</span>
                                            </div>
                                            <div class="text-end">
                                                <div class="live-stats-value">KSH ${parseFloat(trader.total_investment || 0).toFixed(2)}</div>
                                                <small class="live-stats-subtitle">Investment</small>
                                            </div>
                                        </div>
                                    </div>
                                `;
                            });
                        } else {
                            html = '<div class="text-center text-light py-3"><small>No traders found</small></div>';
                        }
                        document.getElementById('leaderboard-data').innerHTML = html;
                    })
                    .catch(error => {
                        console.error('Error loading leaderboard:', error);
                        document.getElementById('leaderboard-data').innerHTML = '<div class="text-center text-light py-3"><small>Error loading data</small></div>';
                    });
    
                // Load Real-time Stats
                fetch('/api/live-statistics?type=realtime')
                    .then(response => response.json())
                    .then(data => {
                        let html = '';
                        if (data.activities && data.activities.length > 0) {
                            data.activities.forEach(activity => {
                                const actionText = activity.type === 'bought' ? 'Bought shares' : 'Sold shares';
                                html += `
                                    <div class="live-stats-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="fw-medium">${activity.username}</div>
                                                <small class="live-stats-subtitle">${actionText}</small>
                                            </div>
                                            <div class="text-end">
                                                <div class="live-stats-value">KSH ${parseFloat(activity.amount).toFixed(2)}</div>
                                                <small class="live-stats-subtitle">${activity.time}</small>
                                            </div>
                                        </div>
                                    </div>
                                `;
                            });
                        } else {
                            html = '<div class="text-center text-light py-3"><small>No recent activity</small></div>';
                        }
                        document.getElementById('realtime-stats').innerHTML = html;
                    })
                    .catch(error => {
                        console.error('Error loading real-time stats:', error);
                        document.getElementById('realtime-stats').innerHTML = '<div class="text-center text-light py-3"><small>Error loading data</small></div>';
                    });
    
                // Load Top Referrers
                fetch('/api/live-statistics?type=referrers')
                    .then(response => response.json())
                    .then(data => {
                        let html = '';
                        if (data.referrers && data.referrers.length > 0) {
                            data.referrers.forEach((referrer, index) => {
                                const position = index + 1;
                                const badge = position <= 3 ? 
                                    `<span class="badge bg-warning live-stats-position">#${position}</span>` : 
                                    `<small class="text-light live-stats-position">#${position}</small>`;
                                    
                                html += `
                                    <div class="live-stats-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                ${badge}
                                                <span class="ms-2 fw-medium">${referrer.username}</span>
                                            </div>
                                            <div class="text-end">
                                                <div class="live-stats-value">${referrer.referral_count}</div>
                                                <small class="live-stats-subtitle">Referrals</small>
                                            </div>
                                        </div>
                                    </div>
                                `;
                            });
                        } else {
                            html = '<div class="text-center text-light py-3"><small>No referrers found</small></div>';
                        }
                        document.getElementById('referrers-data').innerHTML = html;
                    })
                    .catch(error => {
                        console.error('Error loading referrers:', error);
                        document.getElementById('referrers-data').innerHTML = '<div class="text-center text-light py-3"><small>Error loading data</small></div>';
                    });
            };
    
            // Load statistics on page load and refresh every 30 seconds
            loadLiveStatistics();
            const statsInterval = setInterval(loadLiveStatistics, 30000); // Refresh every 30 seconds
            
            // Manual refresh button
            const refreshButton = document.getElementById('refresh-stats-btn');
            if (refreshButton) {
                refreshButton.addEventListener('click', function() {
                    this.classList.add('disabled');
                    const icon = this.querySelector('svg');
                    if (icon) icon.classList.add('spin-animation');
                    
                    loadLiveStatistics();
                    
                    // Re-enable button after 2 seconds
                    setTimeout(() => {
                        this.classList.remove('disabled');
                        if (icon) icon.classList.remove('spin-animation');
                    }, 2000);
                });
            }
            
            // Add a spinning animation style
            const style = document.createElement('style');
            style.textContent = `
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
                .spin-animation {
                    animation: spin 1s linear infinite;
                    transform-origin: center;
                }
            `;
            document.head.appendChild(style);
        });
    </script>
@endsection
