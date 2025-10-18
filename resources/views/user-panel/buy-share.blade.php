@extends('layouts.master')
@php
$pageTitle = 'Buy Shares - ' . ($trade->name ?? 'Trade');
@endphp
@section('title', $pageTitle)

@section('css')
<!-- Enhanced Bidding Cards Styles -->
<link href="{{ URL::asset('/assets/css/enhanced-bidding-cards.css') }}?v={{ time() }}&t=20250826094729" rel="stylesheet">
<style>
.trade-info-card {
    background: linear-gradient(135deg, var(--theme-primary, #405189) 0%, var(--theme-secondary, #3577f1) 100%);
    color: white;
    border-radius: 12px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 8px 25px rgba(64, 81, 137, 0.3);
}

.trade-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1.5rem;
    margin-top: 1.5rem;
}

.trade-stat-item {
    text-align: center;
    padding: 1rem;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    backdrop-filter: blur(10px);
}

.trade-stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    display: block;
}

.trade-stat-label {
    font-size: 0.875rem;
    opacity: 0.9;
    font-weight: 500;
}

.bid-form-container {
    background: #ffffff;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(64, 81, 137, 0.15);
}

.form-label {
    font-weight: 600;
    color: var(--theme-dark, #212529);
    margin-bottom: 0.5rem;
}

.form-control {
    border: 2px solid rgba(64, 81, 137, 0.2);
    border-radius: 8px;
    padding: 0.75rem 1rem;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: var(--theme-primary, #405189);
    box-shadow: 0 0 0 0.2rem rgba(64, 81, 137, 0.25);
}

.btn-buy-shares {
    background: linear-gradient(135deg, var(--theme-success, #0ab39c) 0%, #16a34a 100%);
    border: none;
    color: white;
    padding: 0.875rem 2rem;
    border-radius: 8px;
    font-weight: 600;
    font-size: 1rem;
    transition: all 0.3s ease;
    cursor: pointer;
    width: 100%;
    margin-top: 1rem;
}

.btn-buy-shares:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(10, 179, 156, 0.4);
}

.btn-back {
    background: transparent;
    border: 2px solid var(--theme-primary, #405189);
    color: var(--theme-primary, #405189);
    padding: 0.625rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
    text-decoration: none;
    display: inline-block;
    transition: all 0.3s ease;
}

.btn-back:hover {
    background: var(--theme-primary, #405189);
    color: white;
    transform: translateY(-2px);
}

.alert-info {
    background: linear-gradient(135deg, rgba(41, 156, 219, 0.1) 0%, rgba(255, 255, 255, 0.9) 100%);
    border: 1px solid var(--theme-info, #299cdb);
    color: var(--theme-info, #299cdb);
    border-radius: 8px;
}

.calculations {
    background: rgba(248, 250, 252, 0.8);
    border: 1px solid rgba(226, 232, 240, 0.6);
    border-radius: 8px;
    padding: 1rem;
    margin-top: 1rem;
}

.calc-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
    border-bottom: 1px solid rgba(226, 232, 240, 0.5);
}

.calc-row:last-child {
    border-bottom: none;
    font-weight: 600;
    color: var(--theme-success, #0ab39c);
}

@media (max-width: 768px) {
    .trade-stats {
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }
    
    .trade-info-card, .bid-form-container {
        padding: 1.5rem;
    }
}
</style>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <!-- Trade Information Card -->
            <div class="trade-info-card">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h2 class="mb-1">{{ $trade->name }}</h2>
                        <p class="mb-0 opacity-75">{{ $trade->description ?? 'Premium Trading Opportunity' }}</p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <div class="d-flex align-items-center justify-content-md-end">
                            <div class="me-3">
                                <i data-feather="trending-up" style="width: 30px; height: 30px;"></i>
                            </div>
                            <div>
                                <div class="h4 mb-0">{{ $trade->category ?? 'Trading' }}</div>
                                <small class="opacity-75">Category</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="trade-stats">
                    <div class="trade-stat-item">
                        <span class="trade-stat-value">{{ $availableShares ?? 0 }}</span>
                        <span class="trade-stat-label">Available Shares</span>
                    </div>
                    <div class="trade-stat-item">
                                <span class="trade-stat-value">{{ formatPrice($trade->price ?? $trade->amount ?? 0) }}</span>
                        <span class="trade-stat-label">Price per Share</span>
                    </div>
                    <div class="trade-stat-item">
                        <span class="trade-stat-value">{{ formatPrice($minAmount ?? 0) }}</span>
                        <span class="trade-stat-label">Min Amount</span>
                    </div>
                    <div class="trade-stat-item">
                        <span class="trade-stat-value">{{ formatPrice($maxAmount ?? 0) }}</span>
                        <span class="trade-stat-label">Max Amount</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Buy Shares Form -->
        <div class="col-lg-8">
            <div class="bid-form-container">
                <h4 class="mb-3">
                    <i data-feather="shopping-cart" style="width: 20px; height: 20px; margin-right: 8px;"></i>
                    Purchase Shares
                </h4>
                
                @if($availableShares <= 0)
                    <div class="alert alert-warning">
                        <i data-feather="alert-triangle" style="width: 16px; height: 16px; margin-right: 8px;"></i>
                        No shares are currently available for this trade. Please check back later.
                    </div>
                @else
                    <form action="{{ route('user.bid') }}" method="POST" id="bidForm">
                        @csrf
                        <input type="hidden" name="trade_id" value="{{ $trade->id }}">
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="amount" class="form-label">
                                        Investment Amount (KSH)
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" 
                                           class="form-control" 
                                           id="amount" 
                                           name="amount" 
                                           min="{{ $minAmount }}" 
                                           max="{{ $maxAmount }}"
                                           step="0.01"
                                           required
                                           oninput="calculateShares()">
                                    <div class="form-text">
                                        Enter amount between KSH {{ number_format($minAmount, 2) }} and KSH {{ number_format($maxAmount, 2) }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="period" class="form-label">
                                        Investment Period
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-control" id="period" name="period" onchange="onPeriodChange()" required>
                                        <option value="">Select investment period</option>
                                        @php
                                            $periods = \App\Models\TradePeriod::where('status', 1)->orderBy('days', 'asc')->get();
                                        @endphp
                                        @if($periods->count() > 0)
                                            @foreach($periods as $period)
                                                <option value="{{ $period->days }}">
                                                    {{ $period->days }} {{ $period->days == 1 ? 'Day' : 'Days' }}
                                                    @if($period->percentage)
                                                        ({{ $period->percentage }}% Returns)
                                                    @endif
                                                </option>
                                            @endforeach
                                        @else
                                            <option value="1">1 Day (Default)</option>
                                        @endif
                                    </select>
                                    <div class="form-text">
                                        Choose your investment duration with expected returns
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Calculations Display -->
                        <div class="calculations" id="calculations" style="display: none;">
                            <h6 class="mb-2">Purchase Summary</h6>
                            <div class="calc-row">
                                <span>Investment Amount:</span>
                                <span id="investmentAmount">KSH 0.00</span>
                            </div>
                            <div class="calc-row">
                                <span>Price per Share:</span>
                                <span>KSH {{ number_format($trade->price ?? $trade->amount ?? 0, 2) }}</span>
                            </div>
                            <div class="calc-row">
                                <span>Shares You'll Get:</span>
                                <span id="sharesCount">0 shares</span>
                            </div>
                            <div class="calc-row" id="periodInfo" style="display: none;">
                                <span>Investment Period:</span>
                                <span id="selectedPeriod">-</span>
                            </div>
                            <div class="calc-row" id="expectedReturns" style="display: none;">
                                <span>Expected Returns:</span>
                                <span id="expectedReturnsAmount">KSH 0.00</span>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn-buy-shares">
                            <i data-feather="shopping-bag" style="width: 16px; height: 16px; margin-right: 8px;"></i>
                            Buy Shares Now
                        </button>
                    </form>
                @endif
            </div>
        </div>
        
        <!-- Information Panel -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Trading Information</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i data-feather="info" style="width: 16px; height: 16px; margin-right: 8px;"></i>
                        <strong>Important:</strong> After purchasing shares, you'll need to make payment within the specified time limit to complete your transaction.
                    </div>
                    
                    <h6>How it works:</h6>
                    <ol class="small">
                        <li>Enter your investment amount</li>
                        <li>Review the shares you'll receive</li>
                        <li>Click "Buy Shares Now"</li>
                        <li>Make payment within the deadline</li>
                        <li>Track your shares in "Bought Shares" section</li>
                    </ol>
                    
                    <div class="mt-3">
                        <a href="{{ route('root') }}" class="btn-back">
                            <i data-feather="arrow-left" style="width: 16px; height: 16px; margin-right: 5px;"></i>
                            Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('script')
<script>
// Audio Notification System
class AudioNotificationManager {
    constructor() {
        this.successAudio = new Audio('/audio/success.mp3');
        this.errorAudio = new Audio('/audio/error.mp3');
        this.isInitialized = false;
        this.init();
    }
    
    init() {
        // Preload audio files
        this.successAudio.preload = 'auto';
        this.errorAudio.preload = 'auto';
        
        // Set volume
        this.successAudio.volume = 0.7;
        this.errorAudio.volume = 0.7;
        
        // Handle loading errors
        this.successAudio.addEventListener('error', (e) => {
            console.warn('Success audio failed to load:', e);
        });
        
        this.errorAudio.addEventListener('error', (e) => {
            console.warn('Error audio failed to load:', e);
        });
        
        // Enable audio on first user interaction (required by modern browsers)
        this.enableAudioOnInteraction();
        
        this.isInitialized = true;
    }
    
    enableAudioOnInteraction() {
        const enableAudio = () => {
            // Try to load audio files on first interaction
            this.successAudio.load();
            this.errorAudio.load();
            
            // Remove event listeners after first interaction
            document.removeEventListener('click', enableAudio);
            document.removeEventListener('touchstart', enableAudio);
            document.removeEventListener('keydown', enableAudio);
        };
        
        // Add event listeners for user interactions
        document.addEventListener('click', enableAudio);
        document.addEventListener('touchstart', enableAudio);
        document.addEventListener('keydown', enableAudio);
    }
    
    playSuccess() {
        if (this.isInitialized) {
            this.successAudio.currentTime = 0;
            this.successAudio.play().catch(e => console.warn('Success audio play failed:', e));
        }
    }
    
    playError() {
        if (this.isInitialized) {
            this.errorAudio.currentTime = 0;
            this.errorAudio.play().catch(e => console.warn('Error audio play failed:', e));
        }
    }
}

// Initialize audio manager
const audioManager = new AudioNotificationManager();

// Store periods data from backend
const periodsData = {
    @if(isset($periods) && $periods->count() > 0)
        @foreach($periods as $period)
            '{{ $period->days }}': {
                'days': {{ $period->days }},
                'percentage': {{ $period->percentage ?? 0 }},
                'name': '{{ $period->days }} {{ $period->days == 1 ? "Day" : "Days" }}'
            },
        @endforeach
    @endif
};

// Calculate shares and returns based on amount and period
function calculateShares() {
    const amount = parseFloat(document.getElementById('amount').value) || 0;
    const periodSelect = document.getElementById('period');
    const selectedPeriod = periodSelect.value;
    const pricePerShare = {{ $trade->price ?? $trade->amount ?? 1 }};
    const availableShares = {{ $availableShares ?? 0 }};
    
    if (amount > 0) {
        const sharesWillGet = Math.floor(amount / pricePerShare);
        
        // Update basic calculations
        document.getElementById('investmentAmount').textContent = 'KSH ' + amount.toLocaleString('en-US', { minimumFractionDigits: 2 });
        document.getElementById('sharesCount').textContent = sharesWillGet.toLocaleString() + ' shares';
        document.getElementById('calculations').style.display = 'block';
        
        // Show period and returns info if period is selected
        if (selectedPeriod && periodsData[selectedPeriod]) {
            const periodData = periodsData[selectedPeriod];
            const expectedReturns = amount * (periodData.percentage / 100);
            const totalReturns = amount + expectedReturns;
            
            document.getElementById('periodInfo').style.display = 'flex';
            document.getElementById('selectedPeriod').textContent = periodData.name + ' (' + periodData.percentage + '% returns)';
            
            document.getElementById('expectedReturns').style.display = 'flex';
            document.getElementById('expectedReturnsAmount').innerHTML = `
                <span class="text-success">+KSH ${expectedReturns.toLocaleString('en-US', { minimumFractionDigits: 2 })}</span>
                <small class="d-block text-muted">Total: KSH ${totalReturns.toLocaleString('en-US', { minimumFractionDigits: 2 })}</small>
            `;
        } else {
            document.getElementById('periodInfo').style.display = 'none';
            document.getElementById('expectedReturns').style.display = 'none';
        }
        
        // Validate against available shares
        if (sharesWillGet > availableShares) {
            document.getElementById('calculations').innerHTML = `
                <h6 class="mb-2 text-danger">⚠️ Not Enough Shares Available</h6>
                <p class="text-danger small mb-0">You're trying to buy ${sharesWillGet.toLocaleString()} shares, but only ${availableShares.toLocaleString()} are available. Please reduce your amount.</p>
            `;
        }
    } else {
        document.getElementById('calculations').style.display = 'none';
    }
}

// Handle period selection change
function onPeriodChange() {
    calculateShares(); // Recalculate when period changes
}

// Form validation and delayed audio notifications
document.getElementById('bidForm').addEventListener('submit', function(e) {
    const amount = parseFloat(document.getElementById('amount').value) || 0;
    const minAmount = {{ $minAmount ?? 0 }};
    const maxAmount = {{ $maxAmount ?? 1000000 }};
    const pricePerShare = {{ $trade->price ?? $trade->amount ?? 1 }};
    const availableShares = {{ $availableShares ?? 0 }};
    
    // Validation checks for client-side validation
    if (amount < minAmount) {
        e.preventDefault();
        toastr.error(`Minimum investment amount is KSH ${minAmount.toLocaleString()}`);
        return;
    }
    
    if (amount > maxAmount) {
        e.preventDefault();
        toastr.error(`Maximum investment amount is KSH ${maxAmount.toLocaleString()}`);
        return;
    }
    
    const sharesWillGet = Math.floor(amount / pricePerShare);
    if (sharesWillGet > availableShares) {
        e.preventDefault();
        toastr.error(`Not enough shares available. Only ${availableShares.toLocaleString()} shares remaining.`);
        return;
    }
    
    if (sharesWillGet === 0) {
        e.preventDefault();
        toastr.error('Investment amount too small. Please increase the amount.');
        return;
    }
    
    // Form submission without audio notification
    console.log('Share purchase form submitted');
});

// Notification Audio Integration System
class NotificationAudioIntegrator {
    constructor(audioManager) {
        this.audioManager = audioManager;
        this.lastCheckedToast = null;
        this.delayMs = 4000; // 4 second delay
        this.processedMessages = new Set(); // Track processed messages to prevent duplicates
        this.formSubmitted = false; // Track if form has been submitted
        this.init();
    }
    
    init() {
        // Monitor for success messages
        this.monitorToastrMessages();
        
        // Monitor DOM changes for toastr notifications
        this.observeToastrContainer();
        
        // Check URL parameters for flash messages
        this.checkUrlParams();
    }
    
    monitorToastrMessages() {
        // Override toastr methods to catch messages
        if (typeof toastr !== 'undefined') {
            const originalSuccess = toastr.success;
            const originalError = toastr.error;
            
            toastr.success = (message, title, options) => {
                const result = originalSuccess.call(toastr, message, title, options);
                this.scheduleSuccessAudio(message);
                return result;
            };
            
            toastr.error = (message, title, options) => {
                const result = originalError.call(toastr, message, title, options);
                this.scheduleErrorAudio(message);
                return result;
            };
        }
    }
    
    observeToastrContainer() {
        // Create observer to watch for toastr DOM changes
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.type === 'childList') {
                    mutation.addedNodes.forEach((node) => {
                        if (node.nodeType === 1 && node.classList) {
                            if (node.classList.contains('toast-success')) {
                                const message = node.querySelector('.toast-message')?.textContent || node.textContent || '';
                                // Only trigger on specific share purchase success messages
                                if (message.toLowerCase().includes('bought successfully') || message.toLowerCase().includes('share bought')) {
                                    this.scheduleSuccessAudio(message);
                                }
                            } else if (node.classList.contains('toast-error')) {
                                const message = node.querySelector('.toast-message')?.textContent || node.textContent || '';
                                // Only trigger on specific share purchase error messages
                                if (message.toLowerCase().includes('not bought') || message.toLowerCase().includes('shares not') || message.toLowerCase().includes('failed')) {
                                    this.scheduleErrorAudio(message);
                                }
                            }
                        }
                    });
                }
            });
        });
        
        // Start observing when DOM is ready
        const startObserver = () => {
            const toastrContainer = document.querySelector('#toast-container') || document.body;
            observer.observe(toastrContainer, {
                childList: true,
                subtree: true
            });
        };
        
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', startObserver);
        } else {
            startObserver();
        }
    }
    
    checkUrlParams() {
        // Check for URL parameters that might indicate success/error
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('success')) {
            this.scheduleSuccessAudio('Operation completed successfully');
        } else if (urlParams.has('error')) {
            this.scheduleErrorAudio('Operation failed');
        }
    }
    
    scheduleSuccessAudio(message) {
        // Only play audio if this is after a form submission or explicit success session
        if (!this.formSubmitted && !message.toLowerCase().includes('bought successfully')) {
            return;
        }
        
        const messageKey = 'success_' + message.trim().substring(0, 50);
        if (this.processedMessages.has(messageKey)) {
            return; // Already processed this message
        }
        
        this.processedMessages.add(messageKey);
        setTimeout(() => {
            this.audioManager.playSuccess();
        }, this.delayMs);
    }
    
    scheduleErrorAudio(message) {
        // Only play audio if this is after a form submission or explicit error
        if (!this.formSubmitted && !message.toLowerCase().includes('not bought') && !message.toLowerCase().includes('failed')) {
            return;
        }
        
        const messageKey = 'error_' + message.trim().substring(0, 50);
        if (this.processedMessages.has(messageKey)) {
            return; // Already processed this message
        }
        
        this.processedMessages.add(messageKey);
        setTimeout(() => {
            this.audioManager.playError();
        }, this.delayMs);
    }
}

// Initialize notification audio integration
const notificationAudioIntegrator = new NotificationAudioIntegrator(audioManager);

// Server-side flash message detection
@if(session('success'))
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(() => {
            audioManager.playSuccess();
        }, 4000);
    });
@endif

@if(session('error'))
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(() => {
            audioManager.playError();
        }, 4000);
    });
@endif

@if(session('toast_success'))
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(() => {
            audioManager.playSuccess();
        }, 4000);
    });
@endif

@if(session('toast_error'))
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(() => {
            audioManager.playError();
        }, 4000);
    });
@endif

// Initialize feather icons
document.addEventListener('DOMContentLoaded', function() {
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
    
    // Additional check for Laravel flash messages
    setTimeout(() => {
        checkForLaravelFlashMessages();
    }, 100);
    
    // Initialize form submission monitoring
    monitorFormSubmissionResults();
    
    // Limited periodic check for late-loading notifications
    let checkCount = 0;
    const periodicCheck = setInterval(() => {
        checkCount++;
        checkForLaravelFlashMessages();
        
        // Stop after 3 checks (6 seconds) to prevent excessive checking
        if (checkCount >= 3) {
            clearInterval(periodicCheck);
        }
    }, 2000);
});

// Check for Laravel flash messages in session
function checkForLaravelFlashMessages() {
    // Check if there are any visible toastr notifications already
    const existingToasts = document.querySelectorAll('.toast');
    existingToasts.forEach(toast => {
        if (toast.classList.contains('toast-success')) {
            const message = toast.querySelector('.toast-message')?.textContent || '';
            if (message.toLowerCase().includes('bought successfully')) {
                notificationAudioIntegrator.scheduleSuccessAudio(message);
            }
        } else if (toast.classList.contains('toast-error')) {
            const message = toast.querySelector('.toast-message')?.textContent || '';
            notificationAudioIntegrator.scheduleErrorAudio(message);
        }
    });
    
    // Enhanced detection for various toastr container types
    const toastrContainers = [
        '#toast-container',
        '.toast-container',
        '.toastr-container',
        '.toast-top-right',
        '.toast-bottom-right'
    ];
    
    toastrContainers.forEach(selector => {
        const container = document.querySelector(selector);
        if (container) {
            const toasts = container.querySelectorAll('.toast, .toast-success, .toast-error');
            toasts.forEach(toast => {
                const message = toast.textContent || '';
                // Only trigger on actual toastr notifications with specific success/error classes
                if (toast.classList.contains('toast-success') && message.toLowerCase().includes('bought successfully')) {
                    notificationAudioIntegrator.scheduleSuccessAudio(message);
                } else if (toast.classList.contains('toast-error') && (message.toLowerCase().includes('not bought') || message.toLowerCase().includes('failed'))) {
                    notificationAudioIntegrator.scheduleErrorAudio(message);
                }
            });
        }
    });
    
    // Only check for very specific notification patterns to avoid false positives
    // This check is limited to actual notification containers only
}

// Additional fallback - Monitor for form submission results
function monitorFormSubmissionResults() {
    const form = document.getElementById('bidForm');
    if (form) {
        form.addEventListener('submit', function() {
            // Mark that form has been submitted
            notificationAudioIntegrator.formSubmitted = true;
            
            // Set up a delayed check for results only after form submission
            setTimeout(() => {
                checkForLaravelFlashMessages();
            }, 1000);
        });
    }
}

// Audio notification system is ready
</script>
@endsection
