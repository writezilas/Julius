@extends('layouts.master')
@section('title') @lang('translation.dashboards') @endsection
@section('css')
<link href="{{ URL::asset('assets/libs/jsvectormap/jsvectormap.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('assets/libs/swiper/swiper.min.css')}}" rel="stylesheet" type="text/css" />
<style>
    .dataTables_filter label{
        width: 200px;
        float: right;
    }
    
    /* Enhanced Dashboard Styling */
    .dashboard-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 15px;
        color: white;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.15);
    }
    
    .welcome-text {
        font-size: 1.8rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
        text-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .welcome-subtitle {
        opacity: 0.9;
        font-size: 1.1rem;
        margin-bottom: 0;
    }
    
    .stat-card {
        background: white;
        border-radius: 15px;
        padding: 1.2rem;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        border-left: 4px solid transparent;
        position: relative;
        overflow: hidden;
        min-height: 120px;
        display: flex;
        align-items: center;
    }
    
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #667eea, #764ba2);
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    
    .stat-card.success { border-left-color: #28a745; }
    .stat-card.primary { border-left-color: #007bff; }
    .stat-card.warning { border-left-color: #ffc107; }
    .stat-card.danger { border-left-color: #dc3545; }
    .stat-card.info { border-left-color: #17a2b8; }
    
    .stat-number {
        font-size: 1.5rem; /* Reduced from 2.2rem */
        font-weight: 700;
        color: #2d3436;
        line-height: 1.2;
        word-break: break-all;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    /* Responsive font sizes for very large numbers */
    .stat-number.large-number {
        font-size: 1.2rem;
    }
    
    .stat-number.very-large-number {
        font-size: 1rem;
    }
    
    .stat-label {
        font-size: 0.9rem;
        color: #636e72;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-top: 0.5rem;
    }
    
    .section-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        border: none;
        overflow: hidden;
    }
    
    .section-card .card-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-bottom: 1px solid #dee2e6;
        padding: 1.5rem;
    }
    
    .section-title {
        font-size: 1.3rem;
        font-weight: 600;
        color: #2d3436;
        margin-bottom: 0;
    }
    
    .metric-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-right: 1rem;
    }
    
    .icon-success { background: rgba(40, 167, 69, 0.1); color: #28a745; }
    .icon-primary { background: rgba(0, 123, 255, 0.1); color: #007bff; }
    .icon-warning { background: rgba(255, 193, 7, 0.1); color: #ffc107; }
    .icon-danger { background: rgba(220, 53, 69, 0.1); color: #dc3545; }
    .icon-info { background: rgba(23, 162, 184, 0.1); color: #17a2b8; }
    
    .trade-category-card {
        background: white;
        border-radius: 12px;
        padding: 1rem;
        margin-bottom: 1rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        border-left: 3px solid #e9ecef;
    }
    
    .trade-category-card:hover {
        box-shadow: 0 4px 15px rgba(0,0,0,0.12);
        border-left-color: #667eea;
        transform: translateX(5px);
    }
    
    .category-name {
        font-weight: 600;
        color: #2d3436;
        font-size: 1rem;
    }
    
    .category-date {
        font-size: 0.85rem;
        color: #74b9ff;
    }
    
    .category-stat {
        font-weight: 600;
        color: #2d3436;
    }
    
    .category-stat-label {
        font-size: 0.8rem;
        color: #636e72;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .trader-avatar {
        width: 45px;
        height: 45px;
        border-radius: 12px;
        object-fit: cover;
        margin-right: 1rem;
    }
    
    .trader-name {
        font-weight: 600;
        color: #2d3436;
        font-size: 1rem;
        text-decoration: none;
    }
    
    .trader-name:hover {
        color: #667eea;
    }
    
    .recent-activity-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 1.5rem;
        border-radius: 15px 15px 0 0;
        margin: -1.5rem -1.5rem 1.5rem -1.5rem;
    }
    
    /* Admin Notifications Styles */
    .notification-item {
        background: white;
        border-radius: 10px;
        padding: 1rem;
        margin-bottom: 0.8rem;
        box-shadow: 0 2px 6px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
        position: relative;
        border-left: 3px solid #e9ecef;
    }
    
    .notification-item.unread {
        border-left-color: #28a745;
        background: #f8fff8;
    }
    
    .notification-item.success {
        border-left-color: #28a745;
    }
    
    .notification-item.info {
        border-left-color: #17a2b8;
    }
    
    .notification-item.warning {
        border-left-color: #ffc107;
    }
    
    .notification-item.error {
        border-left-color: #dc3545;
    }
    
    .notification-item:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }
    
    .notification-title {
        font-size: 0.9rem;
        font-weight: 600;
        color: #2d3436;
        margin-bottom: 0.3rem;
    }
    
    .notification-message {
        font-size: 0.8rem;
        color: #636e72;
        line-height: 1.4;
        margin-bottom: 0.5rem;
    }
    
    .notification-time {
        font-size: 0.75rem;
        color: #74b9ff;
        text-align: right;
    }
    
    .notification-icon {
        width: 35px;
        height: 35px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        margin-right: 0.8rem;
        flex-shrink: 0;
    }
    
    .notification-icon.success {
        background: rgba(40, 167, 69, 0.1);
        color: #28a745;
    }
    
    .notification-icon.info {
        background: rgba(23, 162, 184, 0.1);
        color: #17a2b8;
    }
    
    .notification-icon.warning {
        background: rgba(255, 193, 7, 0.1);
        color: #ffc107;
    }
    
    .notification-icon.error {
        background: rgba(220, 53, 69, 0.1);
        color: #dc3545;
    }
    
    .notification-empty {
        text-align: center;
        padding: 2rem 1rem;
        color: #636e72;
    }
    
    .notification-empty i {
        font-size: 2rem;
        color: #ddd;
        margin-bottom: 1rem;
    }
    
    /* Spinner animation */
    .spinner-border-sm {
        animation: spinner-border 0.75s linear infinite;
    }
    
    @keyframes spinner-border {
        to {
            transform: rotate(360deg);
        }
    }
    .activity-item {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 1rem;
        margin-bottom: 0.5rem;
        transition: all 0.3s ease;
    }
    
    .activity-item:hover {
        background: #e9ecef;
        transform: translateX(5px);
    }
    
    .earning-stats {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 0;
        border: none;
    }
    
    .earning-stats .border-dashed {
        border-color: rgba(255,255,255,0.2) !important;
    }
    
    .earning-stats h5, .earning-stats p {
        color: white;
    }
    
    .btn-enhanced {
        border-radius: 8px;
        font-weight: 500;
        padding: 0.6rem 1.2rem;
        transition: all 0.3s ease;
    }
    
    .btn-enhanced:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    }
    
    .status-badge {
        font-size: 0.75rem;
        padding: 0.4rem 0.8rem;
        border-radius: 15px;
        font-weight: 500;
    }
    
    .progress-enhanced {
        height: 8px;
        border-radius: 4px;
        background: rgba(0,0,0,0.1);
    }
    
    .progress-enhanced .progress-bar {
        border-radius: 4px;
    }
    
    .fade-in {
        animation: fadeIn 0.6s ease-in;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .pulse {
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }
    
    .floating {
        animation: floating 3s ease-in-out infinite;
    }
    
    @keyframes floating {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-10px); }
    }
    
    /* User Activity Card Styles */
    .user-activity-item {
        border-radius: 8px;
        transition: all 0.3s ease;
        margin-bottom: 0.5rem;
    }
    
    .user-activity-item:hover {
        background: rgba(102, 126, 234, 0.05);
        transform: translateX(5px);
        padding-left: 1rem !important;
    }
    
    .user-activity-item .avatar-sm {
        width: 40px;
        height: 40px;
    }
    
    .user-activity-item .avatar-title {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 1rem;
    }
    
    .activity-section-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 8px;
        padding: 0.5rem 1rem;
        margin-bottom: 1rem;
    }
    
    .activity-badge {
        font-size: 0.7rem;
        padding: 0.25rem 0.5rem;
        border-radius: 12px;
    }
    
    /* Badge animations */
    .badge-pulse {
        animation: badgePulse 2s infinite;
    }
    
    @keyframes badgePulse {
        0% { box-shadow: 0 0 0 0 rgba(102, 126, 234, 0.7); }
        70% { box-shadow: 0 0 0 10px rgba(102, 126, 234, 0); }
        100% { box-shadow: 0 0 0 0 rgba(102, 126, 234, 0); }
    }
    
    /* Ranking indicators */
    .rank-indicator {
        position: absolute;
        top: -5px;
        right: -5px;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.7rem;
        font-weight: 600;
        color: white;
    }
    
    /* User Activity Card Fixed Width and Full Height */
    .user-activity-column {
        position: fixed !important;
        right: 0;
        top: 0;
        bottom: 0;
        width: 300px !important;
        max-width: 300px !important;
        min-width: 300px !important;
        flex: none !important;
        z-index: 1000;
        padding: 20px 15px;
        background: #f8f9fa;
        border-left: 1px solid #dee2e6;
        overflow: hidden;
    }
    
    .user-activity-card {
        height: 100% !important;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    
    .user-activity-card .card-body {
        flex: 1;
        overflow-y: auto;
        overflow-x: hidden;
        display: flex;
        flex-direction: column;
        padding: 0;
    }
    
    .user-activity-sections {
        flex: 1;
        overflow-y: auto;
        overflow-x: hidden;
        display: flex;
        flex-direction: column;
    }
    
    .user-activity-section {
        flex: 0 0 auto;
        display: flex;
        flex-direction: column;
        margin-bottom: 0.5rem;
    }
    
    .user-activity-section .section-content {
        max-height: 200px;
        overflow-y: auto;
        overflow-x: hidden;
    }
    
    .user-activity-section .user-activity-item {
        padding: 0.5rem;
        margin-bottom: 0.25rem;
        font-size: 0.8rem;
    }
    
    .user-activity-section h6 {
        font-size: 0.75rem;
        margin-bottom: 0.5rem;
        padding: 0.25rem 0.5rem;
        background: rgba(102, 126, 234, 0.1);
        border-radius: 4px;
        position: sticky;
        top: 0;
        z-index: 10;
    }
    
    /* Custom scrollbar for User Activity sections */
    .user-activity-sections::-webkit-scrollbar,
    .user-activity-section .section-content::-webkit-scrollbar {
        width: 4px;
    }
    
    .user-activity-sections::-webkit-scrollbar-track,
    .user-activity-section .section-content::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 2px;
    }
    
    .user-activity-sections::-webkit-scrollbar-thumb,
    .user-activity-section .section-content::-webkit-scrollbar-thumb {
        background: rgba(102, 126, 234, 0.3);
        border-radius: 2px;
    }
    
    .user-activity-sections::-webkit-scrollbar-thumb:hover,
    .user-activity-section .section-content::-webkit-scrollbar-thumb:hover {
        background: rgba(102, 126, 234, 0.5);
    }
    
    /* Section divider */
    .user-activity-section:not(:last-child) {
        border-bottom: 1px solid rgba(0,0,0,0.05);
        padding-bottom: 0.5rem;
    }
    
    /* Adjust main content to accommodate fixed sidebar */
    .main-content-with-sidebar {
        margin-right: 315px !important;
    }
    
    /* Mobile responsive adjustments */
    @media (max-width: 768px) {
        .stat-number {
            font-size: 1.2rem !important;
        }
        
        .stat-number.large-number {
            font-size: 1rem !important;
        }
        
        .stat-number.very-large-number {
            font-size: 0.9rem !important;
        }
        
        .stat-card {
            padding: 1rem;
            min-height: 100px;
        }
        
        .metric-icon {
            width: 40px;
            height: 40px;
            font-size: 1.2rem;
        }
        
        .user-activity-column {
            display: none !important;
        }
        
        .main-content-with-sidebar {
            margin-right: 0 !important;
        }
    }
    
    @media (max-width: 576px) {
        .stat-number {
            font-size: 1rem !important;
            line-height: 1.1;
        }
        
        .stat-number.large-number {
            font-size: 0.9rem !important;
        }
        
        .stat-number.very-large-number {
            font-size: 0.8rem !important;
        }
    }
</style>
@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1') Dashboards @endslot
@slot('title') Dashboard @endslot
@endcomponent
<div class="row">
    <div class="col-12 main-content-with-sidebar">

        <div class="h-100">
            @can('view-analytic')
                
                <!-- Enhanced Welcome Header -->
                <div class="dashboard-header fade-in mb-4">
                    <div class="d-flex align-items-lg-center flex-lg-row flex-column">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center mb-3">
                                <div class="metric-icon icon-primary floating me-3">
                                    <i class="ri-dashboard-3-line"></i>
                                </div>
                                <div>
                                    <h2 class="welcome-text mb-0">Welcome back, {{auth()->user()->name}}!</h2>
                                    <p class="welcome-subtitle mb-0">Here's your trading overview and platform insights</p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 mt-lg-0">
                            <div class="row g-3 mb-0 align-items-center">
                                <div class="col-sm-auto">
                                    <div class="input-group">
                                        <input type="text" class="form-control border-0 dash-filter-picker" data-provider="flatpickr" data-range-date="true" data-date-format="d M, Y" data-deafult-date="01 Jan 2022 to 31 Jan 2022" style="background: rgba(255,255,255,0.1); color: white; border-radius: 8px;">
                                        <div class="input-group-text" style="background: rgba(255,255,255,0.2); border: none; color: white;">
                                            <i class="ri-calendar-2-line"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <button type="button" class="btn btn-light btn-icon waves-effect waves-light layout-rightside-btn" style="border-radius: 8px;">
                                        <i class="ri-pulse-line"></i>
                                    </button>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Admin Notifications Widget - Hidden --}}
    {{-- <div class="col-xl-4">
        <div class="section-card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="section-title mb-0">
                    <i class="ri-notification-3-line me-2 text-primary"></i>
                    Recent Notifications
                </h5>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge badge-soft-primary" id="notification-count">0</span>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="mark-all-read" title="Mark all as read">
                        <i class="ri-check-double-line"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="refresh-notifications" title="Refresh">
                        <i class="ri-refresh-line"></i>
                    </button>
                </div>
            </div>
            <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                <div id="admin-notifications-container">
                    <div class="text-center text-muted py-4" id="notifications-loading">
                        <i class="ri-loader-2-line fs-18 spinner-border-sm"></i>
                        <p class="mt-2 mb-0">Loading notifications...</p>
                    </div>
                </div>
            </div>
        </div>
    </div> --}}
                </div>
                <!--end row-->

                <!-- Enhanced Trade Statistics Cards -->
                <div class="row fade-in">
                    @foreach($trades as $trade)
                    @php
                    $totalShares = $trade->userShares->where('status', 'completed')->sum('share_will_get');
                    $totalProfit = $trade->userShares->where('status', 'completed')->sum('profit_share');
                    $totalRemainShares = $trade->userShares->where('status', 'completed')->sum('total_share_count');
                    @endphp
                    <div class="col-xl-6 mb-4">
                        <div class="section-card">
                            <div class="card-header">
                                <div class="d-flex align-items-center">
                                    <div class="metric-icon icon-primary me-3">
                                        <i class="ri-line-chart-line"></i>
                                    </div>
                                    <div>
                                        <h4 class="section-title mb-0">{{ $trade->name }}</h4>
                                        <small class="text-muted">Trading Overview</small>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body p-3">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <div class="stat-card success">
                                            <div class="d-flex align-items-center">
                                                <div class="metric-icon icon-success me-3">
                                                    <i class="ri-shopping-bag-line"></i>
                                                </div>
                                                <div>
                                                    <div class="stat-number pulse" id="bought-shares-{{ $trade->id }}">
                                                        <span class="counter-value" data-target="{{ $totalShares + $totalProfit }}">0</span>
                                                    </div>
                                                    <div class="stat-label">Bought Shares</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="stat-card info">
                                            <div class="d-flex align-items-center">
                                                <div class="metric-icon icon-info me-3">
                                                    <i class="ri-exchange-line"></i>
                                                </div>
                                                <div>
                                                    <div class="stat-number pulse" id="sold-shares-{{ $trade->id }}">
                                                        <span class="counter-value" data-target="{{ ($totalShares + $totalProfit) - $totalRemainShares }}">0</span>
                                                    </div>
                                                    <div class="stat-label">Sold Shares</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="stat-card warning">
                                            <div class="d-flex align-items-center">
                                                <div class="metric-icon icon-warning me-3">
                                                    <i class="ri-stack-line"></i>
                                                </div>
                                                <div>
                                                    <div class="stat-number pulse" id="remaining-shares-{{ $trade->id }}">
                                                        <span class="counter-value" data-target="{{ $totalRemainShares }}">0</span>
                                                    </div>
                                                    <div class="stat-label">Remaining</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="row">
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-header border-0 align-items-center d-flex">
                                <h4 class="card-title mb-0 flex-grow-1">Earning</h4>
                                {{-- <div>
                                    <button type="button" class="btn btn-soft-secondary btn-sm">
                                        ALL
                                    </button>
                                    <button type="button" class="btn btn-soft-secondary btn-sm">
                                        1M
                                    </button>
                                    <button type="button" class="btn btn-soft-secondary btn-sm">
                                        6M
                                    </button>
                                    <button type="button" class="btn btn-soft-primary btn-sm">
                                        1Y
                                    </button>
                                </div> --}}
                            </div><!-- end card header -->

                            <div class="card-header p-0 border-0 bg-soft-light">
                                <div class="row g-0 text-center">
                                    <div class="col-6 col-sm-3">
                                        <div class="p-3 border border-dashed border-start-0">
                                            <h5 class="mb-1"><span class="counter-value" data-target="7585">0</span></h5>
                                            <p class="text-muted mb-0">Orders</p>
                                        </div>
                                    </div>
                                    <!--end col-->
                                    <div class="col-6 col-sm-3">
                                        <div class="p-3 border border-dashed border-start-0">
                                            <h5 class="mb-1">KSH <span class="counter-value" data-target="22.89">0</span>k</h5>
                                            <p class="text-muted mb-0">Earnings</p>
                                        </div>
                                    </div>
                                    <!--end col-->
                                    <div class="col-6 col-sm-3">
                                        <div class="p-3 border border-dashed border-start-0">
                                            <h5 class="mb-1"><span class="counter-value" data-target="367">0</span></h5>
                                            <p class="text-muted mb-0">Refunds</p>
                                        </div>
                                    </div>
                                    <!--end col-->
                                    <div class="col-6 col-sm-3">
                                        <div class="p-3 border border-dashed border-start-0 border-end-0">
                                            <h5 class="mb-1 text-success"><span class="counter-value" data-target="18.92">0</span>%</h5>
                                            <p class="text-muted mb-0">Conversation Ratio</p>
                                        </div>
                                    </div>
                                    <!--end col-->
                                </div>
                            </div><!-- end card header -->

                            <div class="card-body p-0 pb-2">
                                <div class="w-100">
                                    <div id="customer_impression_charts" data-colors='["--vz-primary", "--vz-success", "--vz-danger"]' class="apex-charts" dir="ltr"></div>
                                </div>
                            </div><!-- end card body -->
                        </div><!-- end card -->
                    </div><!-- end col -->
                </div>

                <div class="row">
                    <div class="col-xl-6">
                        <div class="card">
                            <div class="card-header align-items-center d-flex">
                                <h4 class="card-title mb-0 flex-grow-1">Best trading categories</h4>
                                {{-- <div class="flex-shrink-0">--}}
                                {{-- <div class="dropdown card-header-dropdown">--}}
                                {{-- <a class="text-reset dropdown-btn" href="#"--}}
                                {{-- data-bs-toggle="dropdown" aria-haspopup="true"--}}
                                {{-- aria-expanded="false">--}}
                                {{-- <span class="fw-semibold text-uppercase fs-12">Sort by:--}}
                                {{-- </span><span class="text-muted">Today<i--}}
                                {{-- class="mdi mdi-chevron-down ms-1"></i></span>--}}
                                {{-- </a>--}}
                                {{-- <div class="dropdown-menu dropdown-menu-end">--}}
                                {{-- <a class="dropdown-item" href="#">Today</a>--}}
                                {{-- <a class="dropdown-item" href="#">Yesterday</a>--}}
                                {{-- <a class="dropdown-item" href="#">Last 7 Days</a>--}}
                                {{-- <a class="dropdown-item" href="#">Last 30 Days</a>--}}
                                {{-- <a class="dropdown-item" href="#">This Month</a>--}}
                                {{-- <a class="dropdown-item" href="#">Last Month</a>--}}
                                {{-- </div>--}}
                                {{-- </div>--}}
                                {{-- </div>--}}
                            </div><!-- end card header -->

                            <div class="card-body">
                                <div class="table-responsive table-card">
                                    <table class="table table-hover table-centered align-middle table-nowrap mb-0">
                                        <tbody>
                                            @foreach($topCategory as $key => $top)
                                            @php
                                            $totalRemainShares = $top->userShares->where('status', 'completed')->sum('total_share_count');
                                            @endphp
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-sm bg-light rounded p-1 me-2">
                                                            <img src="{{ URL::asset('assets/images/products/img-1.png') }}" alt="" class="img-fluid d-block" />
                                                        </div>
                                                        <div>
                                                            <h5 class="fs-14 my-1"><a href="apps-ecommerce-product-details" class="text-reset">{{$top->name}}</a></h5>
                                                            <span class="text-muted">{{\Carbon\Carbon::parse($top->created_at)->format('d M Y')}}</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <h5 class="fs-14 my-1 fw-normal">KSH {{number_format($top->price, 2)}}</h5>
                                                    <span class="text-muted">Price</span>
                                                </td>
                                                <td>
                                                    <h5 class="fs-14 my-1 fw-normal">{{$top->user_shares_count}}</h5>
                                                    <span class="text-muted">Orders</span>
                                                </td>
                                                <td>
                                                    <h5 class="fs-14 my-1 fw-normal">{{$totalRemainShares}}</h5>
                                                    <span class="text-muted">Stock</span>
                                                </td>
                                                <td>
                                                    <h5 class="fs-14 my-1 fw-normal">KSH {{number_format($totalRemainShares * $top->price, 2)}}</h5>
                                                    <span class="text-muted">Amount</span>
                                                </td>
                                            </tr>
                                            @endforeach

                                        </tbody>
                                    </table>
                                </div>

                                {{-- <div--}}
                                {{-- class="align-items-center mt-4 pt-2 justify-content-between d-flex">--}}
                                {{-- <div class="flex-shrink-0">--}}
                                {{-- <div class="text-muted">Showing <span--}}
                                {{-- class="fw-semibold">5</span> of <span--}}
                                {{-- class="fw-semibold">25</span> Results--}}
                                {{-- </div>--}}
                                {{-- </div>--}}
                                {{-- <ul class="pagination pagination-separated pagination-sm mb-0">--}}
                                {{-- <li class="page-item disabled">--}}
                                {{-- <a href="#" class="page-link">←</a>--}}
                                {{-- </li>--}}
                                {{-- <li class="page-item">--}}
                                {{-- <a href="#" class="page-link">1</a>--}}
                                {{-- </li>--}}
                                {{-- <li class="page-item active">--}}
                                {{-- <a href="#" class="page-link">2</a>--}}
                                {{-- </li>--}}
                                {{-- <li class="page-item">--}}
                                {{-- <a href="#" class="page-link">3</a>--}}
                                {{-- </li>--}}
                                {{-- <li class="page-item">--}}
                                {{-- <a href="#" class="page-link">→</a>--}}
                                {{-- </li>--}}
                                {{-- </ul>--}}
                                {{-- </div>--}}

                            </div>
                        </div>
                    </div>

                    <div class="col-xl-6">
                        <div class="card card-height-100">
                            <div class="card-header align-items-center d-flex">
                                <h4 class="card-title mb-0 flex-grow-1">Top Traders</h4>
                                <div class="flex-shrink-0">
                                    <div class="dropdown card-header-dropdown">
                                        <a class="text-reset dropdown-btn" href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <span class="text-muted">Report<i class="mdi mdi-chevron-down ms-1"></i></span>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <a class="dropdown-item" href="#">Download Report</a>
                                            <a class="dropdown-item" href="#">Export</a>
                                            <a class="dropdown-item" href="#">Import</a>
                                        </div>
                                    </div>
                                </div>
                            </div><!-- end card header -->

                            <div class="card-body">
                                <div class="table-responsive table-card">
                                    <table class="table table-centered table-hover align-middle table-nowrap mb-0">
                                        <tbody>
                                            @foreach($topTraders as $key => $topTrader)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="flex-shrink-0 me-2">
                                                            <img src="{{ URL::asset('assets/images/companies/img-1.png') }}" alt="" class="avatar-sm p-2" />
                                                        </div>
                                                        <div>
                                                            <h5 class="fs-14 my-1 fw-medium"><a href="apps-ecommerce-seller-details" class="text-reset">{{$topTrader->name}}</a>
                                                            </h5>
                                                            {{-- <span class="text-muted">Oliver Tyler</span> --}}
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="text-muted">{{@$topTrader->trade->name}}</span>
                                                </td>
                                                <td>
                                                    <p class="mb-0">{{$topTrader->balance}}</p>
                                                    <span class="text-muted">Stock</span>
                                                </td>
                                                <td>
                                                    <span class="text-muted">KSH {{number_format($topTrader->balance, 2)}}</span>
                                                </td>
                                                <td>
                                                    <h5 class="fs-14 mb-0">32%<i class="ri-bar-chart-fill text-success fs-16 align-middle ms-2"></i>
                                                    </h5>
                                                </td>
                                            </tr><!-- end -->
                                            @endforeach
                                        </tbody>
                                    </table><!-- end table -->
                                </div>

                                {{-- <div--}}
                                {{-- class="align-items-center mt-4 pt-2 justify-content-between d-flex">--}}
                                {{-- <div class="flex-shrink-0">--}}
                                {{-- <div class="text-muted">Showing <span--}}
                                {{-- class="fw-semibold">5</span> of <span--}}
                                {{-- class="fw-semibold">25</span> Results--}}
                                {{-- </div>--}}
                                {{-- </div>--}}
                                {{-- <ul class="pagination pagination-separated pagination-sm mb-0">--}}
                                {{-- <li class="page-item disabled">--}}
                                {{-- <a href="#" class="page-link">←</a>--}}
                                {{-- </li>--}}
                                {{-- <li class="page-item">--}}
                                {{-- <a href="#" class="page-link">1</a>--}}
                                {{-- </li>--}}
                                {{-- <li class="page-item active">--}}
                                {{-- <a href="#" class="page-link">2</a>--}}
                                {{-- </li>--}}
                                {{-- <li class="page-item">--}}
                                {{-- <a href="#" class="page-link">3</a>--}}
                                {{-- </li>--}}
                                {{-- <li class="page-item">--}}
                                {{-- <a href="#" class="page-link">→</a>--}}
                                {{-- </li>--}}
                                {{-- </ul>--}}
                                {{-- </div>--}}

                            </div> <!-- .card-body-->
                        </div> <!-- .card-->
                    </div> <!-- .col-->
                </div> <!-- end row-->
                
                <div class="row">
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-header align-items-center d-flex">
                                <h4 class="card-title mb-0 flex-grow-1">Recent share bought</h4>
                                <div class="flex-shrink-0">
                                    <button type="button" class="btn btn-soft-info btn-sm">
                                        <i class="ri-file-list-3-line align-middle"></i> Generate Report
                                    </button>
                                </div>
                            </div><!-- end card header -->

                            <div class="card-body">
                                <div class="table-responsive table-card">
                                    <table class="table table-borderless table-centered align-middle table-nowrap mb-0">
                                        <thead class="text-muted table-light">
                                            <tr>
                                                <th scope="col">Ticket no</th>
                                                <th scope="col">Customer</th>
                                                <th scope="col">Share type</th>
                                                <th scope="col">Share bought</th>
                                                <th scope="col">Payment status</th>
                                                {{-- <th scope="col">Action</th>--}}
                                            </tr>
                                        </thead>
                                        <tbody>

                                            @foreach($recentShares as $recentShare)
                                            <tr>
                                                <td>
                                                    <a href="#" class="fw-medium link-primary">{{ $recentShare->ticket_no }}</a>
                                                </td>
                                                <td>
                                                    <a href="#" class="text-decoration-underline text-blue">
                                                        {{ $recentShare->user->name }}
                                                    </a>
                                                </td>
                                                <td> {{ $recentShare->trade->name }} </td>
                                                <td>
                                                    <span class="text-success">
                                                        {{ $recentShare->share_will_get }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if( $recentShare->start_date)
                                                    <span class="badge badge-soft-success">paid</span>
                                                    @else
                                                    <span class="badge badge-soft-danger">Unpaid</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            <!-- end tr -->
                                            @endforeach
                                        </tbody><!-- end tbody -->
                                    </table><!-- end table -->
                                </div>
                            </div>
                        </div> <!-- .card-->
                    </div> <!-- .col-->
                </div> <!-- end row-->

                <!-- Live Statistics Section -->
                {{-- @include('components.live-statistics') --}}

            @endcan
            {{-- Share pending confirmation card has been moved to a dedicated page under Share Management --}}
            {{-- View at: /admin/pending-payment-confirmations --}}

        </div> <!-- end .h-100-->

    </div> <!-- end col -->

    <!-- User Activity Right Sidebar - Fixed position, full height -->
    <div class="user-activity-column d-none d-xl-block">
        <div class="section-card user-activity-card">
            <div class="card-header">
                <h5 class="section-title mb-0">
                    <i class="ri-user-line me-2 text-primary"></i>
                    User Activity
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="user-activity-sections">
                
                <!-- New Users Section -->
                <div class="user-activity-section p-2 border-bottom">
                    <h6 class="text-muted mb-2 text-uppercase fw-semibold d-flex align-items-center">
                        <i class="ri-user-add-line me-2 text-success"></i>
                        New Users (Top 5)
                        <span class="badge badge-soft-success ms-2">{{ $newUsers->count() }}</span>
                    </h6>
                    
                    <div class="section-content">
                    @forelse($newUsers as $user)
                    <div class="user-activity-item d-flex align-items-center py-2">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm">
                                @if($user->avatar)
                                    <img src="{{ URL::asset($user->avatar) }}" alt="{{ $user->name }}" class="avatar-sm rounded-circle">
                                @else
                                    <div class="avatar-title bg-soft-primary text-primary rounded-circle">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1 fs-14">{{ $user->name }}</h6>
                            <p class="text-muted mb-0 fs-12">Joined {{ $user->created_at->diffForHumans() }}</p>
                        </div>
                        <div class="flex-shrink-0">
                            <span class="badge badge-soft-success">New</span>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-muted py-2">
                        <i class="ri-user-line fs-18 mb-1 d-block"></i>
                        <p class="mb-0 small">No new users this week</p>
                    </div>
                    @endforelse
                    </div>
                </div>

                <!-- New Traders Section -->
                <div class="user-activity-section p-2 border-bottom">
                    <h6 class="text-muted mb-2 text-uppercase fw-semibold d-flex align-items-center">
                        <i class="ri-exchange-line me-2 text-info"></i>
                        New Traders (Top 5)
                        <span class="badge badge-soft-info ms-2">{{ $newTraders->count() }}</span>
                    </h6>
                    
                    <div class="section-content">
                    @forelse($newTraders as $trader)
                    <div class="user-activity-item d-flex align-items-center py-2">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm">
                                @if($trader->avatar)
                                    <img src="{{ URL::asset($trader->avatar) }}" alt="{{ $trader->name }}" class="avatar-sm rounded-circle">
                                @else
                                    <div class="avatar-title bg-soft-info text-info rounded-circle">
                                        {{ substr($trader->name, 0, 1) }}
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1 fs-14">{{ $trader->name }}</h6>
                            <p class="text-muted mb-0 fs-12">First investment made</p>
                        </div>
                        <div class="flex-shrink-0">
                            <span class="badge badge-soft-info">{{ $trader->shares_count }} shares</span>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-muted py-2">
                        <i class="ri-exchange-line fs-18 mb-1 d-block"></i>
                        <p class="mb-0 small">No new traders yet</p>
                    </div>
                    @endforelse
                    </div>
                </div>

                <!-- Top Investors Section -->
                <div class="user-activity-section p-2 border-bottom">
                    <h6 class="text-muted mb-2 text-uppercase fw-semibold d-flex align-items-center">
                        <i class="ri-trophy-line me-2 text-warning"></i>
                        Top 5 Investors
                        <span class="badge badge-soft-warning ms-2">{{ $topInvestors->count() }}</span>
                    </h6>
                    
                    <div class="section-content">
                    @forelse($topInvestors as $index => $investor)
                    <div class="user-activity-item d-flex align-items-center py-2">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm position-relative">
                                @if($investor->avatar)
                                    <img src="{{ URL::asset($investor->avatar) }}" alt="{{ $investor->name }}" class="avatar-sm rounded-circle">
                                @else
                                    <div class="avatar-title bg-soft-warning text-warning rounded-circle">
                                        {{ substr($investor->name, 0, 1) }}
                                    </div>
                                @endif
                                @if($index < 3)
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning">
                                    {{ $index + 1 }}
                                </span>
                                @endif
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1 fs-14">{{ $investor->name }}</h6>
                            <p class="text-muted mb-0 fs-12">Balance: KSH {{ number_format($investor->balance, 2) }}</p>
                        </div>
                        <div class="flex-shrink-0">
                            @if($index == 0)
                                <i class="ri-trophy-fill text-warning fs-18"></i>
                            @elseif($index == 1)
                                <i class="ri-medal-fill text-secondary fs-18"></i>
                            @elseif($index == 2)
                                <i class="ri-award-fill text-danger fs-18"></i>
                            @else
                                <span class="badge badge-soft-warning">#{{ $index + 1 }}</span>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-muted py-2">
                        <i class="ri-trophy-line fs-18 mb-1 d-block"></i>
                        <p class="mb-0 small">No investors yet</p>
                    </div>
                    @endforelse
                    </div>
                </div>

                <!-- Top Referral Users Section -->
                <div class="user-activity-section p-2">
                    <h6 class="text-muted mb-2 text-uppercase fw-semibold d-flex align-items-center">
                        <i class="ri-share-line me-2 text-danger"></i>
                        Top 5 Referrals
                        <span class="badge badge-soft-danger ms-2">{{ $topReferralUsers->count() }}</span>
                    </h6>
                    
                    <div class="section-content">
                    @forelse($topReferralUsers as $index => $referrer)
                    <div class="user-activity-item d-flex align-items-center py-2">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm position-relative">
                                @if($referrer->avatar)
                                    <img src="{{ URL::asset($referrer->avatar) }}" alt="{{ $referrer->name }}" class="avatar-sm rounded-circle">
                                @else
                                    <div class="avatar-title bg-soft-danger text-danger rounded-circle">
                                        {{ substr($referrer->name, 0, 1) }}
                                    </div>
                                @endif
                                @if($index < 3)
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    {{ $index + 1 }}
                                </span>
                                @endif
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1 fs-14">{{ $referrer->name }}</h6>
                            <p class="text-muted mb-0 fs-12">{{ $referrer->referral_count }} referrals</p>
                        </div>
                        <div class="flex-shrink-0">
                            @if($index == 0)
                                <i class="ri-crown-fill text-warning fs-18"></i>
                            @else
                                <span class="badge badge-soft-danger">{{ $referrer->referral_count }}</span>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-muted py-2">
                        <i class="ri-share-line fs-18 mb-1 d-block"></i>
                        <p class="mb-0 small">No referrals yet</p>
                    </div>
                    @endforelse
                    </div>
                </div>
                
                </div> <!-- end user-activity-sections -->
            </div>
        </div>
    </div> <!-- end user-activity-column -->
</div>

@endsection
@section('script')
<!-- apexcharts -->
<script src="{{ URL::asset('/assets/libs/apexcharts/apexcharts.min.js') }}"></script>
<script src="{{ URL::asset('/assets/libs/jsvectormap/jsvectormap.min.js') }}"></script>
<script src="{{ URL::asset('assets/libs/swiper/swiper.min.js')}}"></script>
<!-- dashboard init -->
<script src="{{ URL::asset('/assets/js/pages/dashboard-ecommerce.init.js') }}"></script>

<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="{{ URL::asset('assets/js/pages/datatables.init.js') }}"></script>

<script>
    function handlePaymentConformSubmit(paymentId) {
        $('.subBtn-' + paymentId).prop('disabled', true);
        $('#paymentApproveForm' + paymentId).submit();
    }

    function handlePaymentDeclineSubmit(paymentId) {
        $('.declineBtn-' + paymentId).prop('disabled', true);
        $('#paymentDeclineForm' + paymentId).submit();
    }

    function toggleForms(paymentId, formType) {
        if (formType === 'decline') {
            $('#confirmForm' + paymentId).hide();
            $('#declineForm' + paymentId).show();
        } else {
            $('#declineForm' + paymentId).hide();
            $('#confirmForm' + paymentId).show();
        }
    }
    
    // Function to format large numbers and add responsive classes
    function formatStatsNumbers() {
        $('.stat-number').each(function() {
            const $this = $(this);
            const $counterValue = $this.find('.counter-value');
            const targetValue = parseInt($counterValue.attr('data-target'));
            
            // Add responsive classes based on number size
            if (targetValue >= 1000000) {
                $this.addClass('very-large-number');
            } else if (targetValue >= 100000) {
                $this.addClass('large-number');
            }
        });
    }
    
    // Override the counter animation to format numbers with commas
    function formatCounterNumbers() {
        // Wait for counter animation to complete, then format
        setTimeout(function() {
            $('.counter-value').each(function() {
                const $this = $(this);
                const currentValue = parseInt($this.text());
                if (!isNaN(currentValue)) {
                    const formattedNumber = new Intl.NumberFormat().format(currentValue);
                    $this.text(formattedNumber);
                }
            });
        }, 3000); // Wait 3 seconds for counter animation
    }
    
    // Run formatting when page loads
    $(document).ready(function() {
        formatStatsNumbers();
        formatCounterNumbers();
    });
    
    /*
    // Admin Notifications System - Disabled
    class AdminNotifications {
        constructor() {
            this.init();
        }
        
        init() {
            this.loadNotifications();
            this.bindEvents();
            
            // Auto-refresh every 30 seconds
            setInterval(() => {
                this.loadNotifications(true);
            }, 30000);
        }
        
        bindEvents() {
            // Refresh notifications button
            $('#refresh-notifications').click(() => {
                this.loadNotifications();
            });
            
            // Mark all as read button
            $('#mark-all-read').click(() => {
                this.markAllAsRead();
            });
        }
        
        async loadNotifications(silent = false) {
            try {
                if (!silent) {
                    this.showLoading();
                }
                
                const response = await fetch('/admin/notifications/recent?limit=10');
                const data = await response.json();
                
                if (data.success) {
                    this.renderNotifications(data.notifications);
                    this.updateNotificationCount(data.unread_count);
                } else {
                    this.showError('Failed to load notifications');
                }
            } catch (error) {
                console.error('Error loading notifications:', error);
                this.showError('Network error occurred');
            }
        }
        
        renderNotifications(notifications) {
            const container = $('#admin-notifications-container');
            
            if (notifications.length === 0) {
                container.html(`
                    <div class="notification-empty">
                        <i class="ri-notification-off-line d-block"></i>
                        <p class="mb-0">No notifications yet</p>
                    </div>
                `);
                return;
            }
            
            let html = '';
            notifications.forEach(notification => {
                const timeAgo = this.getTimeAgo(notification.created_at);
                const isUnread = !notification.is_read;
                
                html += `
                    <div class="notification-item ${notification.type} ${isUnread ? 'unread' : ''}" 
                         data-id="${notification.id}" 
                         style="cursor: pointer;">
                        <div class="d-flex align-items-start">
                            <div class="notification-icon ${notification.type}">
                                ${this.getNotificationIcon(notification.type)}
                            </div>
                            <div class="flex-grow-1">
                                <div class="notification-title">${notification.title}</div>
                                <div class="notification-message">${notification.message}</div>
                                <div class="notification-time">${timeAgo}</div>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            container.html(html);
            
            // Bind click events to mark individual notifications as read
            $('.notification-item.unread').click((e) => {
                const notificationId = $(e.currentTarget).data('id');
                this.markAsRead(notificationId);
            });
        }
        
        getNotificationIcon(type) {
            const icons = {
                'success': '<i class="ri-user-add-line"></i>',
                'info': '<i class="ri-information-line"></i>',
                'warning': '<i class="ri-alert-line"></i>',
                'error': '<i class="ri-error-warning-line"></i>'
            };
            return icons[type] || icons['info'];
        }
        
        getTimeAgo(dateString) {
            const now = new Date();
            const notificationDate = new Date(dateString);
            const diffInSeconds = Math.floor((now - notificationDate) / 1000);
            
            if (diffInSeconds < 60) return 'Just now';
            if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)} minutes ago`;
            if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)} hours ago`;
            if (diffInSeconds < 604800) return `${Math.floor(diffInSeconds / 86400)} days ago`;
            
            return notificationDate.toLocaleDateString();
        }
        
        updateNotificationCount(count) {
            const badge = $('#notification-count');
            if (count > 0) {
                badge.text(count).show();
                badge.removeClass('badge-soft-secondary').addClass('badge-soft-primary');
            } else {
                badge.text('0').hide();
            }
        }
        
        async markAsRead(notificationId) {
            try {
                const response = await fetch(`/admin/notifications/${notificationId}/mark-as-read`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Content-Type': 'application/json'
                    }
                });
                
                const data = await response.json();
                if (data.success) {
                    // Remove unread class and update UI
                    $(`.notification-item[data-id="${notificationId}"]`).removeClass('unread');
                    this.loadNotifications(true); // Refresh to update count
                }
            } catch (error) {
                console.error('Error marking notification as read:', error);
            }
        }
        
        async markAllAsRead() {
            try {
                const response = await fetch('/admin/notifications/mark-all-as-read', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Content-Type': 'application/json'
                    }
                });
                
                const data = await response.json();
                if (data.success) {
                    // Remove all unread classes
                    $('.notification-item.unread').removeClass('unread');
                    this.updateNotificationCount(0);
                    this.showSuccess('All notifications marked as read');
                }
            } catch (error) {
                console.error('Error marking all notifications as read:', error);
            }
        }
        
        showLoading() {
            $('#admin-notifications-container').html(`
                <div class="text-center text-muted py-4" id="notifications-loading">
                    <i class="ri-loader-2-line fs-18 spinner-border-sm"></i>
                    <p class="mt-2 mb-0">Loading notifications...</p>
                </div>
            `);
        }
        
        showError(message) {
            $('#admin-notifications-container').html(`
                <div class="text-center text-danger py-4">
                    <i class="ri-error-warning-line fs-18 mb-2"></i>
                    <p class="mb-0">${message}</p>
                </div>
            `);
        }
        
        showSuccess(message) {
            // You can use toast or other notification systems here
            console.log('Success:', message);
        }
    }
    
    // Initialize Admin Notifications when document is ready - Disabled
    $(document).ready(function() {
        @if(auth()->check() && auth()->user()->role_id != 2)
        // window.adminNotifications = new AdminNotifications();
        @endif
    });
    */

</script>
@endsection
