@extends('layouts.master')
@php
$pageTitle = __('translation.boughtshares');
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

.countdown-timer.completed {
    color: #27ae60;
}

.countdown-timer.pending {
    color: #f39c12;
}

/* Payment Deadline Timer Styling - Enhanced with theme colors */
.payment-deadline-timer {
    background: linear-gradient(135deg, var(--theme-primary, #405189) 0%, var(--theme-secondary, #3577f1) 100%);
    color: white !important;
    padding: 4px 8px;
    border-radius: 12px;
    font-weight: 600;
    font-size: 11px;
    text-shadow: 0 1px 2px rgba(0,0,0,0.2);
    box-shadow: 0 2px 4px rgba(64, 81, 137, 0.3);
    border: 1px solid rgba(255,255,255,0.2);
    transition: all 0.3s ease;
    display: inline-block;
    min-width: 60px;
    text-align: center;
}

.payment-deadline-timer:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(64, 81, 137, 0.4);
}

/* Timer color variations based on urgency */
.payment-deadline-timer.urgent {
    background: linear-gradient(135deg, #f06548 0%, #dc2626 100%);
    box-shadow: 0 2px 4px rgba(240, 101, 72, 0.3);
    animation: pulse 1s infinite;
}

.payment-deadline-timer.warning {
    background: linear-gradient(135deg, #f7b84b 0%, #d97706 100%);
    box-shadow: 0 2px 4px rgba(247, 184, 75, 0.3);
}

.payment-deadline-timer.expired {
    background: linear-gradient(135deg, #95a5a6 0%, #7f8c8d 100%);
    box-shadow: 0 2px 4px rgba(149, 165, 166, 0.3);
}

@keyframes pulse {
    0% {
        box-shadow: 0 2px 4px rgba(240, 101, 72, 0.3);
    }
    50% {
        box-shadow: 0 4px 12px rgba(240, 101, 72, 0.6);
        transform: translateY(-1px) scale(1.02);
    }
    100% {
        box-shadow: 0 2px 4px rgba(240, 101, 72, 0.3);
    }
}
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
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h4 class="mb-1">{{$totalShares ?? 0}}</h4>
                            <p class="mb-0 opacity-75">Total Bought</p>
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
                            <i class="fas fa-hourglass-half"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h4 class="mb-1">{{$activeShares ?? 0}}</h4>
                            <p class="mb-0 opacity-75">Pending</p>
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
                            <h4 class="mb-1">{{$completedShares ?? 0}}</h4>
                            <p class="mb-0 opacity-75">Completed</p>
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
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h4 class="mb-1">{{$failedShares ?? 0}}</h4>
                            <p class="mb-0 opacity-75">Failed</p>
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
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h4 class="mb-1">{{number_format((($completedShares ?? 0) / max(($totalShares ?? 1), 1)) * 100, 1)}}%</h4>
                            <p class="mb-0 opacity-75">Success Rate</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
                                <th>Date Bought</th>
                                <th>Amount</th>
                                <th>Quantity</th>
                                <th>Status</th>
                                <th>Payment Deadline</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @if(isset($boughtShares) && $boughtShares->count() > 0)
                                @foreach($boughtShares as $share)
                                    <tr>
                                        <td>{{$loop->iteration}}</td>
                                        <td>{{ $share->ticket_no ?? 'N/A' }}</td>
                                        <td>{{ $share->trade ? $share->trade->name : 'Trade Not Available' }}</td>
                                        <td>{{ date('d M Y', strtotime($share->created_at)) }}</td>
                                        <td>KSH {{ 
                                            // Special display fix for Danny's trade AB-17582670669483
                                            // Show original investment amount (share_will_get) excluding profit
                                            $share->ticket_no === 'AB-17582670669483' 
                                                ? number_format($share->share_will_get ?? 0, 2) 
                                                : number_format($share->amount ?? 0, 2) 
                                        }}</td>
                                        <td>{{ 
                                            // Special display fix for Danny's trade AB-17582670669483
                                            // Show original investment quantity (share_will_get) excluding profit
                                            $share->ticket_no === 'AB-17582670669483' 
                                                ? ($share->share_will_get ?? 0) . ' shares' 
                                                : ($share->total_share_count ?? 0) . ' shares' 
                                        }}</td>
                                        <td>
                                            @php
                                                $shareStatusService = app(\App\Services\ShareStatusService::class);
                                                $statusInfo = $shareStatusService->getShareStatus($share, 'bought');
                                            @endphp
                                            <span class="badge {{ $statusInfo['class'] }}" title="{{ $statusInfo['description'] }}">
                                                {{ $statusInfo['status'] }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($share->status === 'failed')
                                                <span class="countdown-timer" style="color: #e74c3c;">Payment Expired</span>
                                            @elseif($share->status === 'completed')
                                                <span class="countdown-timer completed" style="color: #27ae60;">Payment Made</span>
                                            @elseif($share->status === 'pending')
                                                <span class="countdown-timer pending" id="bought-share-timer{{ $share->id ?? 0 }}" style="color: #667eea;">Loading...</span>
                                            @elseif($share->status === 'paired')
                                                @php
                                                    $hasPayment = $share->payments()->where('status', 'paid')->exists();
                                                @endphp
                                                @if($hasPayment)
                                                    <span class="countdown-timer payment-submitted" style="color: #17a2b8; font-weight: bold;">Waiting for Payment Confirmation</span>
                                                @else
                                                    <div style="text-align: center;">
                                                        <span class="countdown-timer paired" style="color: #f39c12; font-weight: bold;">Paired - Make Payment</span><br>
                                                        <div class="payment-timer-container" style="margin-top: 5px; display: inline-block;">
                                                            <small class="countdown-timer payment-deadline-timer" id="paired-share-timer{{ $share->id ?? 0 }}">Loading...</small>
                                                        </div>
                                                    </div>
                                                @endif
                                            @elseif($share->status === 'pairing')
                                                <span class="countdown-timer pairing" style="color: #3498db;">Finding pairs...</span>
                                            @else
                                                <span class="countdown-timer" style="color: #95a5a6;">{{ ucfirst($share->status) }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('bought-share.view', $share->id ?? 1) }}" class="btn btn-info btn-sm">Details</a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                                        <h5>No Bought Shares Found</h5>
                                        <p class="text-muted">You don't have any bought shares yet.</p>
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
    @if(isset($boughtShares) && $boughtShares->count() > 0)
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted">
                                Showing {{ $boughtShares->firstItem() ?? 1 }} to {{ $boughtShares->lastItem() ?? 0 }} 
                                of {{ $boughtShares->total() ?? 0 }} shares
                            </div>
                            <nav>
                                {{ $boughtShares->links('pagination::bootstrap-4') }}
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
// Store timer intervals globally for main page - this is the authoritative source
window.mainPageTimers = {};
window.timerStates = {}; // Store timer states for cross-page consistency

// Initialize countdown timers for bought shares (payment deadlines)
@if(isset($boughtShares) && $boughtShares->count() > 0)
    @foreach($boughtShares as $share)
        @if($share->status === 'pending')
            @php
                // Use stored deadline to preserve original setting when share was created
                $boughtTimeMinutes = $share->payment_deadline_minutes ?? get_gs_value('bought_time') ?? 60;
                $expiryDateTime = \Carbon\Carbon::parse($share->created_at)->addMinutes($boughtTimeMinutes);
                $expiryTimeISO = $expiryDateTime->toISOString();
            @endphp
            getBoughtShareCounterTime('{{ $expiryTimeISO }}', 'bought-share-timer{{ $share->id }}', {{ $share->id }});
        @elseif($share->status === 'paired')
            @php
                // Use stored deadline to preserve original setting when share was created
                $boughtTimeMinutes = $share->payment_deadline_minutes ?? get_gs_value('bought_time') ?? 60;
                $expiryDateTime = \Carbon\Carbon::parse($share->created_at)->addMinutes($boughtTimeMinutes);
                $expiryTimeISO = $expiryDateTime->toISOString();
            @endphp
            getBoughtShareCounterTime('{{ $expiryTimeISO }}', 'paired-share-timer{{ $share->id }}', {{ $share->id }});
        @endif
    @endforeach
@endif

function getBoughtShareCounterTime(startTime, id, shareId) {
    var countDownDate = new Date(startTime).getTime();

    var x = setInterval(function() {
        var now = new Date().getTime();
        var distance = countDownDate - now;

        var days = Math.floor(distance / (1000 * 60 * 60 * 24));
        var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        var seconds = Math.floor((distance % (1000 * 60)) / 1000);

        var timerElement = document.getElementById(id);
        if (timerElement) {
            if (distance > 0) {
                var timeString = '';
                if (days > 0) timeString += days + 'd ';
                if (hours > 0) timeString += hours + 'h ';
                if (minutes > 0) timeString += minutes + 'm ';
                timeString += seconds + 's';
                
                timerElement.innerHTML = timeString;
                
                // Apply styling classes based on time remaining
                timerElement.className = 'countdown-timer payment-deadline-timer';
                if (distance < 300000) { // Less than 5 minutes
                    timerElement.classList.add('urgent');
                } else if (distance < 1800000) { // Less than 30 minutes
                    timerElement.classList.add('warning');
                }
            } else {
                clearInterval(x);
                timerElement.innerHTML = 'Payment Expired';
                timerElement.className = 'countdown-timer';
                timerElement.style.color = '#e74c3c';
            }
        }
    }, 1000);
}
</script>
@endsection
