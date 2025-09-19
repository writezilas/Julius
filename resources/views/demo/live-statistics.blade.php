@extends('layouts.master')

@section('title') Live Statistics Demo @endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1') Demo @endslot
        @slot('title') Live Statistics @endslot
    @endcomponent

    <!-- Include the Live Statistics Component -->
    @include('components.live-statistics')

    <!-- Additional demo content -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Live Statistics Features</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <h6><i class="fas fa-chart-line text-primary me-2"></i>Top Traders</h6>
                            <ul class="text-muted">
                                <li>Shows top 10 traders by investment + profit</li>
                                <li>Gold, Silver, Bronze badges for top 3</li>
                                <li>Real-time data updates</li>
                            </ul>
                        </div>
                        <div class="col-md-4">
                            <h6><i class="fas fa-clock text-success me-2"></i>Recent Activity</h6>
                            <ul class="text-muted">
                                <li>Last 10 buy/sell transactions</li>
                                <li>Real-time activity feed</li>
                                <li>24-hour rolling window</li>
                            </ul>
                        </div>
                        <div class="col-md-4">
                            <h6><i class="fas fa-users text-info me-2"></i>Top Referrers</h6>
                            <ul class="text-muted">
                                <li>Top 10 users by referral count</li>
                                <li>Active referrals only</li>
                                <li>Ranked with badge system</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection