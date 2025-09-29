@extends('layouts.master')
@php
$pageTitle = __('translation.soldshares');
@endphp
@section('title', $pageTitle)

@section('css')
<style>
.stats-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    color: white;
    transition: all 0.3s ease;
}

.stats-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.stats-card.success { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
.stats-card.warning { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
.stats-card.info { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
.stats-card.danger { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }
.stats-card.purple { background: linear-gradient(135deg, #a29bfe 0%, #6c5ce7 100%); }
.stats-card.orange { background: linear-gradient(135deg, #fd79a8 0%, #fdcb6e 100%); }

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    background: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}

.countdown-timer {
    color: #e74c3c;
    font-weight: 600;
}

.countdown-timer.matured {
    color: #27ae60;
}

/* Flip Card Timer Styles */
.flip-timer {
    display: inline-flex;
    gap: 3px;
    align-items: center;
    justify-content: center;
    line-height: 1;
}

.flip-card {
    display: inline-flex;
    flex-direction: column;
    align-items: center;
    margin: 0 1px;
}

.flip-card-inner {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 4px;
    padding: 3px 6px;
    box-shadow: 
        0 2px 4px rgba(0, 0, 0, 0.1),
        inset 0 1px 0 rgba(255, 255, 255, 0.1),
        inset 0 -1px 0 rgba(0, 0, 0, 0.1);
    position: relative;
    min-width: 24px;
    min-height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.flip-card-inner::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 1px;
    background: rgba(0, 0, 0, 0.15);
    transform: translateY(-50%);
}

.flip-card-number {
    color: white;
    font-size: 0.75rem;
    font-weight: 700;
    font-family: 'Arial', sans-serif;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
    line-height: 1;
}

.flip-card-label {
    color: #6c757d;
    font-size: 0.5rem;
    font-weight: 500;
    text-transform: uppercase;
    margin-top: 1px;
    letter-spacing: 0.3px;
}

.timer-separator {
    color: #6c757d;
    font-size: 1rem;
    font-weight: 500;
    margin: 0 2px;
    align-self: flex-start;
    margin-top: 8px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .flip-card-inner {
        min-width: 20px;
        min-height: 18px;
        padding: 2px 4px;
    }
    
    .flip-card-number {
        font-size: 0.65rem;
    }
    
    .flip-card-label {
        font-size: 0.45rem;
    }
    
    .flip-timer {
        gap: 2px;
    }
}

/* Filter form styling */
.form-select option {
    background: #fff !important;
    color: #000 !important;
}

.form-select:focus {
    background-color: rgba(255, 255, 255, 0.15) !important;
    border-color: rgba(255, 255, 255, 0.5) !important;
    color: white !important;
    box-shadow: 0 0 0 0.25rem rgba(255, 255, 255, 0.25) !important;
}</style>
</style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1') @lang('translation.dashboard') @endslot
        @slot('title') {{$pageTitle}} @endslot
    @endcomponent

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-2 col-md-6 col-sm-6 mb-3">
            <div class="card stats-card info">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon me-3">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h4 class="mb-1">{{$totalSoldShares ?? 0}}</h4>
                            <p class="mb-0 opacity-75">Total Sold</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-2 col-md-6 col-sm-6 mb-3">
            <div class="card stats-card warning">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon me-3">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h4 class="mb-1">{{$runningShares ?? 0}}</h4>
                            <p class="mb-0 opacity-75">Running</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-2 col-md-6 col-sm-6 mb-3">
            <div class="card stats-card success">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon me-3">
                            <i class="fas fa-check-double"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h4 class="mb-1">{{$maturedShares ?? 0}}</h4>
                            <p class="mb-0 opacity-75">Matured</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-2 col-md-6 col-sm-6 mb-3">
            <div class="card stats-card purple">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon me-3">
                            <i class="fas fa-coins"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h4 class="mb-1">KSH {{number_format($totalInvestment ?? 0, 2)}}</h4>
                            <p class="mb-0 opacity-75">Investment</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-2 col-md-6 col-sm-6 mb-3">
            <div class="card stats-card orange">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon me-3">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h4 class="mb-1">KSH {{number_format($totalEarnings ?? 0, 2)}}</h4>
                            <p class="mb-0 opacity-75">Earnings</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-2 col-md-6 col-sm-6 mb-3">
            <div class="card stats-card danger">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon me-3">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h4 class="mb-1">KSH {{number_format($totalReturn ?? 0, 2)}}</h4>
                            <p class="mb-0 opacity-75">Total Return</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <!-- Referral Bonus Trading Section - Hidden from sold-shares page -->
    {{-- 
    @if(isset($availableReferralBonuses) && $availableReferralBonuses->count() > 0)
    <div class="row mb-4">
        <div class="col-lg-12">
            <div class="card border border-success">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0 text-white">
                        <i class="fas fa-gift me-2"></i>Available Referral Bonus Trades
<small class="opacity-75 ms-2">(Automatically available for trading)</small>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Referral Bonus Trading:</strong> These bonus shares are automatically assigned to the trade with the most liquidity, ensuring they can be quickly sold to any available buyers.
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-success">
                                <tr>
                                    <th>Bonus From</th>
                                    <th>Ticket No</th>
                                    <th>Share Type</th>
                                    <th>Bonus Amount</th>
                                    <th>Earning Potential</th>
                                    <th>Total Value</th>
                                    <th>Market Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($availableReferralBonuses as $bonus)
                                    <tr>
                                        <td>
                                            <strong class="text-success">
                                                @if($bonus->invoice && $bonus->invoice->reff_user)
                                                    {{ $bonus->invoice->reff_user->name ?? $bonus->invoice->reff_user->username }}
                                                @else
                                                    Referral Bonus
                                                @endif
                                            </strong>
                                            <br><small class="text-muted">{{ $bonus->created_at->format('M d, Y') }}</small>
                                        </td>
                                        <td><code>{{ $bonus->ticket_no }}</code></td>
                                        <td>{{ $bonus->trade ? $bonus->trade->name : 'N/A' }}</td>
                                        <td>
                                            <span class="badge bg-success">
                                                KSH {{ number_format($bonus->share_will_get ?? 0, 2) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                KSH 0.00
                                            </span>
                                            <br><small class="text-muted">No interest earned</small>
                                        </td>
                                        <td>
                                            <strong class="text-success">
                                                KSH {{ number_format($bonus->share_will_get ?? 0, 2) }}
                                            </strong>
                                            <br><small class="text-muted">Face value only</small>
                                        </td>
                                        <td>
                                            @if($bonus->sold_quantity > 0 || $bonus->pairedWithThis->count() > 0)
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check-circle me-1"></i>Sold
                                                </span>
                                                <br><small class="text-muted">Payment confirmed</small>
                                            @else
                                                <span class="badge bg-info">
                                                    <i class="fas fa-shopping-cart me-1"></i>Available
                                                </span>
                                                <br><small class="text-muted">Ready for buyers</small>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
    --}}

    <!-- Main Table -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{$pageTitle}}</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="alternative-pagination" class="table align-middle table-hover table-bordered" style="width:100%">
                            <thead>
                            <tr>
                                <th>SR No.</th>
                                <th>Ticket no</th>
                                <th>Share type</th>
                                <th>Start date</th>
                                <th>Investment</th>
                                <th>Earning</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Time remaining</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @if(isset($soldShares) && $soldShares->count() > 0)
                                @foreach($soldShares as $share)
                                    <tr>
                                        <td>{{$loop->iteration}}</td>
                                        <td>{{ $share->ticket_no ?? 'N/A' }}</td>
                                        <td>
                                            @if($share->get_from === 'refferal-bonus')
                                                <span class="text-success"><i class="fas fa-gift me-1"></i>Referral Bonus</span>
                                            @else
                                                {{ $share->trade ? $share->trade->name : 'Trade Not Available' }}
                                            @endif
                                        </td>
                                        <td>{{ $share->start_date ? date('d M Y', strtotime($share->start_date)) : 'N/A' }}</td>
                                        <td>KSH {{ number_format($share->share_will_get ?? 0, 2) }}</td>
                                        <td>
                                            @if($share->get_from === 'refferal-bonus')
                                                <span class="text-muted">KSH 0.00</span>
                                            @else
                                                KSH {{ number_format($share->profit_share ?? 0, 2) }}
                                            @endif
                                        </td>
                                        <td>
                                            @if($share->get_from === 'refferal-bonus')
                                                <strong class="text-primary">KSH {{ number_format($share->share_will_get ?? 0, 2) }}</strong>
                                            @else
                                                KSH {{ number_format(($share->share_will_get ?? 0) + ($share->profit_share ?? 0), 2) }}
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $shareStatusService = app(\App\Services\ShareStatusService::class);
                                                $statusInfo = $shareStatusService->getShareStatus($share, 'sold');
                                            @endphp
                                            <span class="badge {{ $statusInfo['class'] }}" title="{{ $statusInfo['description'] }}">
                                                {{ $statusInfo['status'] }}
                                            </span>
                                        </td>
                                        <td>
                                            @php
                                                $shareStatusService = app(\App\Services\ShareStatusService::class);
                                                $timeInfo = $shareStatusService->getTimeRemaining($share, 'sold');
                                            @endphp
                                            
                                            @if($timeInfo['text'] === 'timer-active')
                                                <span class="countdown-timer" id="sold-share-timer{{ $share->id ?? 0 }}">Loading...</span>
                                            @else
                                                <span class="{{ $timeInfo['class'] }}" style="color: {{ $timeInfo['color'] }}; font-weight: bold;">{{ $timeInfo['text'] }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                @php
                                                    $shareStatusService = app(\App\Services\ShareStatusService::class);
                                                    $shouldUnlock = $shareStatusService->shouldUnlockDetailsButton($share);
                                                @endphp
                                                @if($shouldUnlock)
                                                    <a href="{{ route('sold-share.view', $share->id ?? 1) }}" class="btn btn-info btn-sm">Details</a>
                                                @else
                                                    <button type="button" class="btn btn-secondary btn-sm" disabled title="Details will be available once the share matures">
                                                        <i class="ri-lock-line align-middle me-1"></i>Details
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="10" class="text-center py-4">
                                        <i class="fas fa-chart-pie fa-3x text-muted mb-3"></i>
                                        <h5>No Sold Shares Found</h5>
                                        <p class="text-muted">You don't have any sold shares yet.</p>
                                    </td>
                                </tr>
                            @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    @if(isset($soldShares) && $soldShares->count() > 0)
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted">
                                Showing {{ $soldShares->firstItem() ?? 1 }} to {{ $soldShares->lastItem() ?? 0 }} 
                                of {{ $soldShares->total() ?? 0 }} shares
                            </div>
                            <nav>
                                {{ $soldShares->links('pagination::bootstrap-4') }}
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

@endsection

@section('script')
<script>
// Initialize countdown timers
@if(isset($soldShares) && $soldShares->count() > 0)
    @foreach($soldShares as $share)
        @if(isset($share->is_ready_to_sell) && $share->is_ready_to_sell == 0 && isset($share->start_date) && isset($share->period))
            getSoldShareCounterTime('{{ date("Y-m-d H:i:s", strtotime($share->start_date . " +" . $share->period . " days")) }}', 'sold-share-timer{{ $share->id }}', {{ $share->id }});
        @endif
    @endforeach
@endif

function getSoldShareCounterTime(startTime, id, shareId) {
    try {
        // Debug logging
        console.log('Initializing timer for:', {startTime, id, shareId});
        
        var countDownDate = new Date(startTime).getTime();
        
        // Validate date
        if (isNaN(countDownDate)) {
            console.error('Invalid date for timer:', startTime);
            return;
        }
        
        var timerElement = document.getElementById(id);
        if (!timerElement) {
            console.error('Timer element not found:', id);
            return;
        }

        var x = setInterval(function() {
            try {
                var now = new Date().getTime();
                var distance = countDownDate - now;

                var days = Math.floor(distance / (1000 * 60 * 60 * 24));
                var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                var seconds = Math.floor((distance % (1000 * 60)) / 1000);

                var timerElement = document.getElementById(id);
                if (timerElement) {
                    if (distance > 0) {
                        // Create flip-card timer structure
                        var flipTimerHTML = '<div class="flip-timer">';
                        
                        // Days
                        if (days > 0) {
                            flipTimerHTML += '<div class="flip-card">';
                            flipTimerHTML += '<div class="flip-card-inner">';
                            flipTimerHTML += '<span class="flip-card-number">' + String(days).padStart(2, '0') + '</span>';
                            flipTimerHTML += '</div>';
                            flipTimerHTML += '<span class="flip-card-label">Days</span>';
                            flipTimerHTML += '</div>';
                        }
                        
                        // Hours
                        if (hours > 0 || days > 0) {
                            flipTimerHTML += '<div class="flip-card">';
                            flipTimerHTML += '<div class="flip-card-inner">';
                            flipTimerHTML += '<span class="flip-card-number">' + String(hours).padStart(2, '0') + '</span>';
                            flipTimerHTML += '</div>';
                            flipTimerHTML += '<span class="flip-card-label">Hours</span>';
                            flipTimerHTML += '</div>';
                        }
                        
                        // Minutes
                        flipTimerHTML += '<div class="flip-card">';
                        flipTimerHTML += '<div class="flip-card-inner">';
                        flipTimerHTML += '<span class="flip-card-number">' + String(minutes).padStart(2, '0') + '</span>';
                        flipTimerHTML += '</div>';
                        flipTimerHTML += '<span class="flip-card-label">Minutes</span>';
                        flipTimerHTML += '</div>';
                        
                        // Seconds
                        flipTimerHTML += '<div class="flip-card">';
                        flipTimerHTML += '<div class="flip-card-inner">';
                        flipTimerHTML += '<span class="flip-card-number">' + String(seconds).padStart(2, '0') + '</span>';
                        flipTimerHTML += '</div>';
                        flipTimerHTML += '<span class="flip-card-label">Seconds</span>';
                        flipTimerHTML += '</div>';
                        
                        flipTimerHTML += '</div>';
                        
                        timerElement.innerHTML = flipTimerHTML;
                        timerElement.className = 'countdown-timer'; // Remove any color styling since flip cards handle it
                    } else {
                        clearInterval(x);
                        timerElement.innerHTML = 'Share Matured';
                        timerElement.className = 'countdown-timer matured';
                        timerElement.style.color = '#27ae60'; // Green
                        console.log('Timer completed for share:', shareId);
                    }
                } else {
                    console.warn('Timer element disappeared:', id);
                    clearInterval(x);
                }
            } catch (error) {
                console.error('Timer update error:', error);
                clearInterval(x);
            }
        }, 1000);
        
        console.log('Timer started successfully for share:', shareId);
        
    } catch (error) {
        console.error('Timer initialization error:', error);
    }
}

// Referral bonuses are now automatically floated to market
// No manual intervention required
</script>
@endsection
