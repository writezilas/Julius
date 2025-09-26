@extends('layouts.master')
@section('title') @lang('translation.dashboard') @endsection

@section('css')
<!-- Enhanced Dashboard Styles -->
<link href="{{ URL::asset('/assets/css/dashboard-enhanced.css') }}" rel="stylesheet">
<!-- Enhanced Bidding Cards Styles -->
<link href="{{ URL::asset('/assets/css/enhanced-bidding-cards.css') }}?v={{ time() }}" rel="stylesheet">
<!-- Enhanced Announcement Card Styles -->
<link href="{{ URL::asset('/assets/css/announcement-card.css') }}?v={{ time() }}" rel="stylesheet">
<!-- Enhanced Countdown Timer Styles -->
<link href="{{ URL::asset('/assets/css/enhanced-countdown.css') }}?v={{ time() }}" rel="stylesheet">
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
    
    <!-- Live Bidding Cards Section (Full Width) -->
    <section class="available-shares-section" aria-labelledby="available-shares-heading">
        <h2 id="available-shares-heading" class="visually-hidden">Available Trading Shares</h2>
        <div class="row">
            <div class="col-lg-12">
                <div class="card mb-3">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Live Trading Opportunities</h4>
                        <small class="text-muted">Real-time bidding with progress tracking</small>
                    </div>
                    <div class="card-body">
                        @php
                            $appTimezone = get_app_timezone();
                            $now = \Carbon\Carbon::now($appTimezone);
                            
                            // Use the same market timing logic as the trades component
                            $isTradeOpen = is_market_open();
                            $timeSlots = get_markets();
                            
                            // Get next opening time if market is closed
                            $open = null;
                            if (!$isTradeOpen) {
                                $open = get_next_market_open_time();
                            }
                        @endphp
                        @if($isTradeOpen) {{-- Show available shares only when market is open --}}
                        <div class="row">
                            @php
                                $trades = \App\Models\Trade::whereStatus('1')->get();
                            @endphp
                            @foreach($trades as $trade)
                                @php
                                    $availableShares = checkAvailableSharePerTrade($trade->id);
                                    
                                    // Use the centralized helper function for consistent progress calculation
                                    $progressPercentage = calculateTradeProgressPercentage($trade->id);
                                @endphp
                                <div class="col-xxl-3 col-xl-4 col-lg-6 col-md-6 col-sm-12 mb-3">
                                    <div class="bidding-card animate-slide-up h-100" data-trade-id="{{ $trade->id }}">
                                        <div class="share-type-badge">{{ $trade->category ?? 'Trading' }}</div>
                                        
                                        <div class="bidding-card-header">
                                            <div class="card-icon">
                                                <i data-feather="trending-up"></i>
                                            </div>
                                            <h5 class="bidding-card-title">{{ $trade->name }}</h5>
                                            <p class="bidding-card-subtitle">{{ $trade->description ?? 'Trading Opportunity' }}</p>
                                        </div>
                                        
                                        <div class="bidding-card-body">
                                            <!-- Progress Bar -->
                                            <div class="bidding-progress">
                                                <div class="progress-bar-wrapper">
                                                    <div class="bidding-progress-bar" style="width: {{ $progressPercentage }}%" data-progress="{{ $progressPercentage }}"></div>
                                                </div>
                                                <div class="progress-label">
                                                    <span class="progress-text">{{ number_format($progressPercentage, 1) }}% Complete</span>
                                                </div>
                                            </div>
                                            
                                            <!-- Bidding Info -->
                                            <div class="bidding-info">
                                                <div class="bidding-info-item stats-hover-effect">
                                                    <span class="bidding-info-value counter-value" data-target="{{ $availableShares }}">0</span>
                                                    <span class="bidding-info-label">Available</span>
                                                </div>
                                                <div class="bidding-info-item stats-hover-effect">
                                                    <span class="bidding-info-value">{{ formatPrice($trade->amount ?? 0) }}</span>
                                                    <span class="bidding-info-label">Price</span>
                                                </div>
                                                <div class="bidding-info-item stats-hover-effect">
                                                    <span class="bidding-info-value">{{ number_format($progressPercentage, 1) }}%</span>
                                                    <span class="bidding-info-label">Progress</span>
                                                </div>
                                            </div>
                                            
                                            <!-- Status Indicator -->
                                            @if($availableShares > 0)
                                                <div class="status-indicator active">
                                                    <i data-feather="check-circle" style="width: 12px; height: 12px;"></i>
                                                    <span>Active Trading</span>
                                                </div>
                                            @else
                                                <div class="status-indicator pending">
                                                    <i data-feather="clock" style="width: 12px; height: 12px;"></i>
                                                    <span>Coming Soon</span>
                                                </div>
                                            @endif
                                            
                                            <!-- Action Buttons -->
                                            <div class="bidding-actions">
                                                @if($availableShares > 0)
                                                    <button class="btn-bidding primary" onclick="handleBuySharesClick('{{ route('user.buyShare', $trade->id) }}')">
                                                        <i data-feather="shopping-cart" style="width: 16px; height: 16px; margin-right: 5px;"></i>
                                                        Buy Shares
                                                    </button>
                                                @else
                                                    <button class="btn-bidding outline" onclick="handleNotAvailableClick()" disabled>
                                                        <i data-feather="info" style="width: 16px; height: 16px; margin-right: 5px;"></i>
                                                        Not Available
                                                    </button>
                                                @endif
                                                <button class="btn-bidding outline" onclick="showTradeDetails('{{ $trade->id }}')">
                                                    <i data-feather="eye" style="width: 16px; height: 16px; margin-right: 5px;"></i>
                                                    Details
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        @if(count($trades) == 0)
                            <div class="text-center p-5">
                                <div class="mb-4">
                                    <i data-feather="inbox" style="width: 60px; height: 60px; color: #ccc;"></i>
                                </div>
                                <h4 class="text-muted mb-2">No Trading Opportunities Available</h4>
                                <p class="text-muted mb-4">Check back later for new trading opportunities.</p>
                                <button class="btn btn-primary" onclick="location.reload()">
                                    <i data-feather="refresh-cw" style="width: 16px; height: 16px; margin-right: 8px;"></i>
                                    Refresh Page
                                </button>
                            </div>
                        @endif
                        </div>
                        @else
                        <div class="text-center p-5">
                            <div class="mb-4">
                                <i data-feather="clock" style="width: 60px; height: 60px; color: #dc3545;"></i>
                            </div>
                            <h4 class="text-muted mb-2">Market Closed</h4>
                            <p class="text-muted mb-4">Auction closed at the moment. Click REFRESH when it is time to bid.</p>
                            @if($open)
                                <div class="mb-4">
                                    <p class="mb-2"><strong>Market will open at: {{ $open->format('h:i A') }}</strong></p>
                                    <p class="mb-2 small text-muted">Time shown in {{ $appTimezone }} timezone</p>
                                    <div class="mt-3">
                                        <div id="count-down" data-time="{{$open->utc()}}">Loading countdown...</div>
                                    </div>
                                </div>
                            @endif
                            <button class="btn btn-primary" onclick="location.reload()">
                                <i data-feather="refresh-cw" style="width: 16px; height: 16px; margin-right: 8px;"></i>
                                Refresh Page
                            </button>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Live Statistics Section -->
    @include('components.live-statistics')

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
            <div class="card announcement-card-enhanced h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <h4 class="card-title mb-0 me-2">Announcements</h4>
                        <span class="badge bg-light text-primary">Latest Updates</span>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.announcementCard?.expandAll()" title="Expand All">
                            <i class="mdi mdi-arrow-expand-all"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.announcementCard?.collapseAll()" title="Collapse All">
                            <i class="mdi mdi-arrow-collapse-all"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div data-simplebar style="max-height: 500px;">
                        <div class="p-3">
                            @php
                                $announcements = \App\Models\Announcement::where('status', '1')->orderBy('id', 'desc')->take(5)->get();
                            @endphp
                            
                            @if(count($announcements) > 0)
                                @foreach($announcements as $key => $announcement)
                                    <div class="announcement-item-enhanced" data-announcement-id="{{ $announcement->id }}">
                                        <div class="announcement-header">
                                            <div class="flex-grow-1">
                                                <h5 class="announcement-title">{{ $announcement->title }}</h5>
                                                <div class="announcement-meta">
                                                    <div class="announcement-date">
                                                        <i class="mdi mdi-calendar-clock text-muted"></i>
                                                        <span>{{ $announcement->created_at->diffForHumans() }}</span>
                                                    </div>
                                                    @if($announcement->created_at->diffInDays() <= 7)
                                                        <span class="announcement-badge new">New</span>
                                                    @elseif($announcement->updated_at > $announcement->created_at)
                                                        <span class="announcement-badge updated">Updated</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        
                                        @if($announcement->excerpt)
                                            <p class="announcement-excerpt">{{ $announcement->excerpt }}</p>
                                        @endif
                                        
                                        <div class="announcement-actions">
                                            <button type="button" class="btn-announcement announcement-toggle collapsed" 
                                                    aria-expanded="false" 
                                                    aria-controls="announcement-content-{{ $announcement->id }}"
                                                    tabindex="0">
                                                <i class="mdi mdi-chevron-down announcement-toggle-icon"></i>
                                                <span class="toggle-text">View Details</span>
                                            </button>
                                        </div>
                                        
                                        <div class="announcement-content" id="announcement-content-{{ $announcement->id }}">
                                            <div class="announcement-content-inner">
                                                @if($announcement->description)
                                                    <div class="announcement-description">
                                                        {!! $announcement->description !!}
                                                    </div>
                                                @endif
                                                
                                                @if($announcement->image)
                                                    <div class="announcement-image">
                                                        <img src="{{ asset($announcement->image) }}" 
                                                             alt="{{ $announcement->title }}" 
                                                             class="img-fluid" 
                                                             loading="lazy">
                                                    </div>
                                                @endif
                                                
                                                @if($announcement->video_url)
                                                    <div class="announcement-video">
                                                        <iframe width="100%" 
                                                                height="250" 
                                                                src="{{ $announcement->video_url }}" 
                                                                frameborder="0" 
                                                                allowfullscreen 
                                                                loading="lazy"></iframe>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="no-announcements">
                                    <i class="mdi mdi-bullhorn-outline"></i>
                                    <h6>No announcements found</h6>
                                    <p class="small text-muted">Check back later for updates</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                
                @if(count($announcements) > 0)
                    <div class="card-footer bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">Showing {{ count($announcements) }} announcement{{ count($announcements) > 1 ? 's' : '' }}</small>
                            <small class="text-muted">Click "View Details" to expand content inline</small>
                        </div>
                    </div>
                @endif
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
    <!-- Progress Bar Handler -->
    <script src="{{ URL::asset('/assets/js/progress-bar-handler.js') }}"></script>
    <!-- Enhanced Announcement Card -->
    <script src="{{ URL::asset('/assets/js/announcement-card.js') }}?v={{ time() }}"></script>

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
            
            // Enhanced Countdown Timer for Market Open
            const countdownElement = document.getElementById('count-down');
            if (countdownElement) {
                // Store previous values for flip animation
                let previousValues = { days: '', hours: '', minutes: '', seconds: '' };
                
                // Function to pad numbers with leading zeros
                const padNumber = (num) => num.toString().padStart(2, '0');
                
                // Function to create the enhanced countdown HTML structure
                const createCountdownHTML = (days, hours, minutes, seconds) => {
                    const daysStr = padNumber(days);
                    const hoursStr = padNumber(hours);
                    const minutesStr = padNumber(minutes);
                    const secondsStr = padNumber(seconds);
                    
                    return `
                        <div class="enhanced-countdown-container">
                            <div class="countdown-header">
                                <h3 class="countdown-title">TIME REMAINING</h3>
                            </div>
                            <div class="countdown-digits">
                                <div class="countdown-unit">
                                    <div class="countdown-digits-group">
                                        <div class="countdown-digit days" data-digit="days-tens">${daysStr[0]}</div>
                                        <div class="countdown-digit days" data-digit="days-ones">${daysStr[1]}</div>
                                    </div>
                                    <div class="countdown-label">DAYS</div>
                                </div>
                                <div class="countdown-unit">
                                    <div class="countdown-digits-group">
                                        <div class="countdown-digit hours" data-digit="hours-tens">${hoursStr[0]}</div>
                                        <div class="countdown-digit hours" data-digit="hours-ones">${hoursStr[1]}</div>
                                    </div>
                                    <div class="countdown-label">HOURS</div>
                                </div>
                                <div class="countdown-unit">
                                    <div class="countdown-digits-group">
                                        <div class="countdown-digit minutes" data-digit="minutes-tens">${minutesStr[0]}</div>
                                        <div class="countdown-digit minutes" data-digit="minutes-ones">${minutesStr[1]}</div>
                                    </div>
                                    <div class="countdown-label">MINUTES</div>
                                </div>
                                <div class="countdown-unit">
                                    <div class="countdown-digits-group">
                                        <div class="countdown-digit seconds" data-digit="seconds-tens">${secondsStr[0]}</div>
                                        <div class="countdown-digit seconds" data-digit="seconds-ones">${secondsStr[1]}</div>
                                    </div>
                                    <div class="countdown-label">SECONDS</div>
                                </div>
                            </div>
                        </div>
                    `;
                };
                
                // Function to update individual digits with flip animation
                const updateDigits = (days, hours, minutes, seconds) => {
                    const daysStr = padNumber(days);
                    const hoursStr = padNumber(hours);
                    const minutesStr = padNumber(minutes);
                    const secondsStr = padNumber(seconds);
                    
                    const digitUpdates = {
                        'days-tens': daysStr[0],
                        'days-ones': daysStr[1],
                        'hours-tens': hoursStr[0],
                        'hours-ones': hoursStr[1],
                        'minutes-tens': minutesStr[0],
                        'minutes-ones': minutesStr[1],
                        'seconds-tens': secondsStr[0],
                        'seconds-ones': secondsStr[1]
                    };
                    
                    // Update each digit with animation if it changed
                    Object.entries(digitUpdates).forEach(([digitType, newValue]) => {
                        const digitElement = document.querySelector(`[data-digit="${digitType}"]`);
                        if (digitElement && digitElement.textContent !== newValue) {
                            digitElement.classList.add('flip-in');
                            digitElement.textContent = newValue;
                            
                            // Remove animation class after animation completes
                            setTimeout(() => {
                                digitElement.classList.remove('flip-in');
                            }, 300);
                        }
                    });
                };
                
                const getCounterTime = (startTime, id) => {
                    // Parse the input date string into a UTC date object
                    const countDownDate = new Date(startTime + ' UTC').getTime();
                    
                    let isInitialized = false;
                    
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
                        
                        // If the count down is over, show completion message and refresh
                        if (distance < 0) {
                            clearInterval(x);
                            document.getElementById(id).innerHTML = `
                                <div class="enhanced-countdown-container countdown-ended">
                                    <div class="countdown-header">
                                        <h3 class="countdown-title">Market Open Now!</h3>
                                    </div>
                                </div>
                            `;
                            setTimeout(() => {
                                window.location.reload();
                            }, 3000);
                            return;
                        }
                        
                        // Initialize or update the countdown display
                        if (!isInitialized) {
                            document.getElementById(id).innerHTML = createCountdownHTML(days, hours, minutes, seconds);
                            isInitialized = true;
                        } else {
                            updateDigits(days, hours, minutes, seconds);
                        }
                    }, 1000);
                };
                
                const countdownTime = countdownElement.getAttribute('data-time');
                if (countdownTime) {
                    getCounterTime(countdownTime, "count-down");
                }
            }
            
            // Note: Live Statistics functionality is now handled by the component itself
            
            // Initialize Trading Progress Bar Handler
            if (typeof TradingProgressBarHandler !== 'undefined') {
                const progressHandler = new TradingProgressBarHandler();
                
                // Register all existing progress bars (if any are found)
                const progressBars = document.querySelectorAll('.bidding-progress-bar, .progress-bar, .trade-progress-bar');
                progressBars.forEach((bar, index) => {
                    const card = bar.closest('.bidding-card, .trading-card, .card');
                    const tradeId = extractTradeIdFromElement(card || bar);
                    if (tradeId) {
                        progressHandler.registerProgressBar(tradeId, bar);
                        console.log(`📊 Registered progress bar for trade ${tradeId}`);
                    }
                });
                
                // Set up periodic refresh every 30 seconds
                if (progressBars.length > 0) {
                    progressHandler.startPeriodicRefresh(30000);
                    console.log(`⏰ Started progress bar refresh for ${progressBars.length} bars`);
                }
                
                // Store handler globally for other scripts to use
                window.tradingProgressHandler = progressHandler;
                
                console.log('✅ Trading Progress Bar Handler initialized successfully!');
            } else {
                console.warn('⚠️ TradingProgressBarHandler not found - progress bars may not update in real-time');
            }
            
            // Helper function to extract trade ID from various element types
            function extractTradeIdFromElement(element) {
                if (!element) return null;
                
                // Try to find trade ID from various data attributes
                const dataTradeId = element.getAttribute('data-trade-id');
                if (dataTradeId) {
                    return parseInt(dataTradeId);
                }
                
                // Try to find from form elements
                const hiddenInput = element.querySelector('input[name="trade_id"]');
                if (hiddenInput) {
                    return parseInt(hiddenInput.value);
                }
                
                // Try to find from button URLs
                const buyButton = element.querySelector('a[href*="buyShare"], button[onclick*="buyShare"]');
                if (buyButton) {
                    const href = buyButton.getAttribute('href') || buyButton.getAttribute('onclick') || '';
                    const tradeIdMatch = href.match(/(?:buyShare|trade)\/(\d+)/i);
                    if (tradeIdMatch) {
                        return parseInt(tradeIdMatch[1]);
                    }
                }
                
                // Try to find from class names
                const classList = element.className;
                const classMatch = classList.match(/trade-(\d+)/i);
                if (classMatch) {
                    return parseInt(classMatch[1]);
                }
                
                return null;
            }
            
            // Dashboard button functions
            window.handleBuySharesClick = function(url) {
                console.log('Buy Shares button clicked - navigating to:', url);
                window.location.href = url;
            };
            
            window.handleNotAvailableClick = function() {
                console.log('Not Available button clicked');
                showToast('No shares available for this trade at the moment.', 'warning');
            };
            
            window.showTradeDetails = function(tradeId) {
                console.log('Showing trade details for:', tradeId);
                showToast('Trade details feature coming soon!', 'info');
            };
            
            // Helper function to show toast notifications
            function showToast(message, type = 'info', duration = 3000) {
                // Remove any existing toast
                const existingToast = document.querySelector('.custom-toast');
                if (existingToast) {
                    existingToast.remove();
                }
                
                // Create toast element
                const toast = document.createElement('div');
                toast.className = `custom-toast position-fixed top-50 start-50 translate-middle bg-${type === 'warning' ? 'warning' : type === 'info' ? 'info' : 'primary'} text-white px-4 py-3 rounded shadow-lg`;
                toast.style.zIndex = '9999';
                toast.style.opacity = '0';
                toast.style.transition = 'opacity 0.3s ease';
                
                // Add icon based on type
                const icon = type === 'warning' ? 'alert-triangle' : type === 'info' ? 'info' : 'check-circle';
                toast.innerHTML = `
                    <div class="d-flex align-items-center">
                        <i data-feather="${icon}" style="width: 16px; height: 16px; margin-right: 8px;"></i>
                        <span>${message}</span>
                    </div>
                `;
                
                document.body.appendChild(toast);
                
                // Initialize feather icons for the toast
                if (typeof feather !== 'undefined') {
                    feather.replace();
                }
                
                // Animate in
                setTimeout(() => {
                    toast.style.opacity = '1';
                }, 100);
                
                // Animate out and remove
                setTimeout(() => {
                    toast.style.opacity = '0';
                    setTimeout(() => {
                        if (toast.parentNode) {
                            toast.parentNode.removeChild(toast);
                        }
                    }, 300);
                }, duration);
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
