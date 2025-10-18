@extends('layouts.master')
@php($pageTitle = $pageTitle ?? 'Referrals')
@section('title') {{ $pageTitle }} @endsection

@section('content')
    <!-- Referral Statistics -->
    <div class="row">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-users text-primary mb-3" style="font-size: 2.5rem;"></i>
                    <h5 class="card-title">Total Referrals</h5>
                    <h2 class="text-primary mb-0">{{ $totalReferrals ?? 0 }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-dollar-sign text-success mb-3" style="font-size: 2.5rem;"></i>
                    <h5 class="card-title">Total Earnings</h5>
                    <h2 class="text-success mb-0">KSH {{ number_format($totalEarnings ?? 0, 2) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-clock text-warning mb-3" style="font-size: 2.5rem;"></i>
                    <h5 class="card-title">Pending Payments</h5>
                    <h2 class="text-warning mb-0">{{ $pendingPayments ?? 0 }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-calendar-week text-info mb-3" style="font-size: 2.5rem;"></i>
                    <h5 class="card-title">Recent (7 days)</h5>
                    <h2 class="text-info mb-0">{{ $recentReferrals ?? 0 }}</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Referral Link Section -->
    <div class="row mt-4">
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0 text-white"><i class="fas fa-link me-2"></i>Your Referral Link</h5>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <p class="text-muted mb-3">Share this link with friends to earn referral bonuses!</p>
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" id="referralCode" 
                                   value="{{ url('/register?refferal_code=' . auth()->user()->username) }}" readonly>
                            <button type="button" class="btn btn-primary" onclick="handleCopyLink()">
                                <i class="fas fa-copy me-1"></i> Copy Link
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Referral List -->
    <div class="row mt-4">
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0 text-white"><i class="fas fa-list me-2"></i>Your Referrals</h5>
                </div>
                <div class="card-body">
                    @if(isset($refferals) && $refferals->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Username</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Joined Date</th>
                                        <th>Referral Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($refferals as $index => $referral)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <strong>{{ $referral->username }}</strong>
                                            </td>
                                            <td>{{ $referral->name }}</td>
                                            <td>{{ $referral->email }}</td>
                                            <td>
                                                <small class="text-muted">
                                                    {{ $referral->created_at->format('M d, Y') }}
                                                </small>
                                            </td>
                                            <td>
                                                <span class="badge {{ $referral->ref_amount > 0 ? 'bg-success' : 'bg-warning' }}">
                                                    KSH {{ number_format($referral->ref_amount, 2) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if(isset($referral->payment_status) && $referral->payment_status === 'paid')
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-check me-1"></i>Completed
                                                    </span>
                                                    <br><small class="text-success mt-1">Bonus shares sold & paid</small>
                                                @else
                                                    <span class="badge bg-warning">
                                                        <i class="fas fa-clock me-1"></i>Pending
                                                    </span>
                                                    <br><small class="text-muted mt-1">Awaiting bonus shares sale</small>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-users-slash text-muted mb-3" style="font-size: 3rem;"></i>
                            <h5 class="text-muted">No Referrals Yet</h5>
                            <p class="text-muted">Start sharing your referral link to earn bonuses!</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        function handleCopyLink() {
            let text = document.getElementById("referralCode");
            text.select();
            text.setSelectionRange(0, 99999); // For mobile devices
            navigator.clipboard.writeText(text.value).then(function() {
                // Success feedback
                let button = text.nextElementSibling;
                let originalText = button.innerHTML;
                button.innerHTML = '<i class="fas fa-check me-1"></i> Copied!';
                button.classList.remove('btn-primary');
                button.classList.add('btn-success');
                
                setTimeout(function() {
                    button.innerHTML = originalText;
                    button.classList.remove('btn-success');
                    button.classList.add('btn-primary');
                }, 2000);
            }).catch(function() {
                // Fallback for older browsers
                document.execCommand('copy');
                alert("Referral link copied to clipboard!");
            });
        }
    </script>
@endsection
