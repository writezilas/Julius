<!doctype html >

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-layout="{{ auth()->check() && auth()->user()->role_id != 2 ? 'vertical' : 'horizontal'}}" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none" data-preloader="disable" data-layout-mode="{{auth()->check() && auth()->user()->mode ? auth()->user()->mode : 'light' }}">

<head>
    <meta charset="utf-8" />
    <title>@yield('title')| {{env('APP_NAME', 'AUTO BIDDER')}}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Autobidder.live is peer-to-peer investment platform that simulates the stock exchange market." name="description" />
    <meta content="Autobidder" name="author" />
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ URL::asset('assets/images/favicon.ico')}}">
    @include('layouts.head-css')
    
    <!-- iOS Payment Modal Compatibility Fixes -->
    <link href="{{ asset('assets/css/ios-payment-modal-fix.css') }}?v={{ time() }}" rel="stylesheet">
    
    <!-- Payment Form Light Mode Fix -->
    <link href="{{ asset('assets/css/payment-form-light-mode-fix.css') }}?v={{ time() }}" rel="stylesheet">
    
    <!-- CRITICAL: Payment Modal Light Mode Fix - Forces ALL payment modals to light mode -->
    @include('layouts.payment-light-mode-fix')
    
    <!-- Enhanced Payment Confirmation Modal Fix - Light Mode + Mobile Scrolling -->
    <link href="{{ asset('assets/css/payment-confirmation-modal-enhanced-fix.css') }}?v={{ time() }}" rel="stylesheet">
    
    <!-- Success Notification Text Color Fix -->
    <link href="{{ asset('assets/css/success-notification-fix.css') }}?v={{ time() }}" rel="stylesheet">

</head>

@section('body')
    @include('layouts.body')
@show
    <!-- Begin page -->
    <div id="layout-wrapper">
        @if(request()->get('md'))
            @include('layouts.sidebar2')
        @else
            @if(auth()->check() && auth()->user()->role_id == 2)
                @include('user-panel.partials.topbar')
                @include('user-panel.partials.sidebar')
            @else
                @include('layouts.topbar')
                @include('layouts.sidebar')
            @endif
        @endif
        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    @yield('content')
                </div>
                <!-- container-fluid -->
            </div>
            <!-- End Page-content -->
            @include('layouts.footer')
        </div>
        <!-- end main content-->
    </div>
    <!-- END layout-wrapper -->

    {{-- @include('layouts.customizer') --}}

    <!-- JAVASCRIPT -->
    @include('layouts.vendor-scripts')
    
    <!-- iOS Payment Modal JavaScript Fixes -->
    <script src="{{ asset('assets/js/ios-payment-modal-fix.js') }}?v={{ time() }}"></script>
    
    <!-- Enhanced Payment Confirmation Modal JavaScript Fixes -->
    <script src="{{ asset('assets/js/payment-confirmation-modal-fix.js') }}?v={{ time() }}"></script>
    
    <!-- New Payment Forms JavaScript -->
    <script src="{{ asset('assets/js/new-payment-forms.js') }}?v={{ time() }}"></script>
    
    @auth
    <!-- User Status Monitor -->
    <script>
        window.Laravel = {
            user: {
                id: {{ auth()->user()->id }},
                username: '{{ auth()->user()->username }}',
                status: '{{ auth()->user()->status }}'
            }
        };
    </script>
    <script src="{{ asset('assets/js/suspension-monitor.js') }}"></script>
    <script src="{{ asset('assets/js/user-status-monitor.js') }}"></script>
    
    @if(auth()->user()->role_id === 2)
    <!-- Global chat unread count functionality -->
    <script>
    class GlobalChatNotification {
        constructor() {
            this.init();
        }

        init() {
            this.updateUnreadCount();
            // Update every 30 seconds
            setInterval(() => {
                this.updateUnreadCount();
            }, 30000);
        }

        async updateUnreadCount() {
            try {
                const response = await fetch('/chat/unread-count');
                if (response.ok) {
                    const data = await response.json();
                    if (data.success) {
                        const count = data.unread_count;
                        const badge = document.getElementById('sidebarChatBadge');
                        
                        if (badge) {
                            if (count > 0) {
                                badge.textContent = count;
                                badge.style.display = 'inline-block';
                            } else {
                                badge.style.display = 'none';
                            }
                        }
                    }
                }
            } catch (error) {
                console.error('Error fetching unread count:', error);
            }
        }
    }

    // Initialize global chat notifications
    document.addEventListener('DOMContentLoaded', function() {
        window.globalChatNotification = new GlobalChatNotification();
    });
    </script>
    @endif
    
    @if(auth()->check() && auth()->user()->role_id != 2)
    <!-- Admin Notifications Bell JavaScript -->
    <script>
    class AdminNotificationBell {
        constructor() {
            this.init();
        }
        
        init() {
            console.log('AdminNotificationBell: Initializing...');
            this.loadNotifications();
            this.bindEvents();
            this.bindDropdownEvents();
            
            // Auto-refresh every 30 seconds
            setInterval(() => {
                console.log('AdminNotificationBell: Auto-refresh triggered');
                this.loadNotifications(true);
            }, 30000);
            
            console.log('AdminNotificationBell: Initialization complete');
        }
        
        bindEvents() {
            // Mark all as read button
            const markAllBtn = document.getElementById('topbar-mark-all-read');
            if (markAllBtn) {
                markAllBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.markAllAsRead();
                });
            }
        }
        
        bindDropdownEvents() {
            // Load notifications when dropdown is opened
            const notificationDropdown = document.getElementById('page-header-notifications-dropdown');
            if (notificationDropdown) {
                notificationDropdown.addEventListener('shown.bs.dropdown', () => {
                    console.log('AdminNotificationBell: Dropdown opened, loading fresh notifications');
                    this.loadNotifications();
                });
            }
        }
        
        async loadNotifications(silent = false) {
            try {
                console.log('AdminNotificationBell: Loading notifications, silent=' + silent);
                
                if (!silent) {
                    this.showLoading();
                }
                
                const response = await fetch('/admin/notifications/unread?limit=10');
                const data = await response.json();
                
                console.log('AdminNotificationBell: API Response:', data);
                
                if (data.success) {
                    console.log('AdminNotificationBell: Rendering', data.notifications.length, 'notifications with', data.unread_count, 'unread');
                    this.renderNotifications(data.notifications);
                    this.updateNotificationCount(data.unread_count);
                } else {
                    console.error('AdminNotificationBell: API returned success=false');
                    this.showError('Failed to load notifications');
                }
            } catch (error) {
                console.error('AdminNotificationBell: Error loading notifications:', error);
                if (!silent) {
                    this.showError('Network error occurred');
                }
            }
        }
        
        renderNotifications(notifications) {
            const container = document.getElementById('topbar-admin-notifications-container');
            if (!container) return;
            
            if (notifications.length === 0) {
                container.innerHTML = `
                    <div class="text-center text-muted py-4">
                        <i class="mdi mdi-bell-check fs-24 mb-2"></i>
                        <p class="mb-0">All caught up!</p>
                        <p class="mb-0 fs-12">No new notifications</p>
                    </div>
                `;
                return;
            }
            
            let html = '';
            notifications.forEach(notification => {
                const timeAgo = this.getTimeAgo(notification.created_at);
                const iconClass = this.getNotificationIcon(notification.type);
                
                html += `
                    <div class="text-reset notification-item d-block dropdown-item position-relative bg-light" 
                         data-id="${notification.id}" 
                         style="cursor: pointer;">
                        <div class="d-flex">
                            <div class="avatar-xs me-3">
                                <span class="avatar-title bg-soft-${notification.type} text-${notification.type} rounded-circle fs-16">
                                    <i class="${iconClass}"></i>
                                </span>
                            </div>
                            <div class="flex-1">
                                <h6 class="mt-0 mb-1 fs-13 fw-semibold">
                                    ${notification.title}
                                    <span class="badge bg-danger ms-1" style="font-size: 8px;">•</span>
                                </h6>
                                <p class="mb-1 fs-12 text-muted">
                                    ${notification.message}
                                </p>
                                <p class="mb-0 fs-11 fw-medium text-uppercase text-muted">
                                    <span><i class="mdi mdi-clock-outline"></i> ${timeAgo}</span>
                                </p>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            container.innerHTML = html;
            
            // Bind click events to mark individual notifications as read
            container.querySelectorAll('.notification-item[data-id]').forEach(item => {
                item.addEventListener('click', (e) => {
                    e.preventDefault();
                    const notificationId = item.dataset.id;
                    const notificationData = notifications.find(n => n.id == notificationId);
                    
                    // Mark as read
                    this.markAsRead(notificationId, item);
                    
                    // Navigate to URL if available
                    if (notificationData && notificationData.data && notificationData.data.view_url) {
                        setTimeout(() => {
                            console.log('AdminNotificationBell: Navigating to:', notificationData.data.view_url);
                            window.location.href = notificationData.data.view_url;
                        }, 500); // Small delay to allow mark as read to complete
                    }
                });
            });
        }
        
        getNotificationIcon(type) {
            const icons = {
                'success': 'mdi mdi-account-plus',
                'info': 'mdi mdi-information',
                'warning': 'mdi mdi-alert',
                'error': 'mdi mdi-alert-circle'
            };
            return icons[type] || icons['info'];
        }
        
        getTimeAgo(dateString) {
            const now = new Date();
            const notificationDate = new Date(dateString);
            const diffInSeconds = Math.floor((now - notificationDate) / 1000);
            
            if (diffInSeconds < 60) return 'Just now';
            if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}m ago`;
            if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h ago`;
            if (diffInSeconds < 604800) return `${Math.floor(diffInSeconds / 86400)}d ago`;
            
            return notificationDate.toLocaleDateString();
        }
        
        updateNotificationCount(count) {
            console.log('AdminNotificationBell: Updating notification count to', count);
            
            const badge = document.getElementById('admin-notification-badge');
            const countSpan = document.getElementById('topbar-notification-count');
            
            console.log('AdminNotificationBell: Badge element found:', !!badge);
            console.log('AdminNotificationBell: Count span found:', !!countSpan);
            
            if (badge) {
                if (count > 0) {
                    console.log('AdminNotificationBell: Showing badge with count', count);
                    badge.textContent = count;
                    badge.style.display = 'block';
                } else {
                    console.log('AdminNotificationBell: Hiding badge (count is 0)');
                    badge.style.display = 'none';
                }
            } else {
                console.error('AdminNotificationBell: Could not find admin-notification-badge element');
            }
            
            if (countSpan) {
                countSpan.textContent = `${count} New`;
                console.log('AdminNotificationBell: Updated count span to "' + count + ' New"');
            } else {
                console.error('AdminNotificationBell: Could not find topbar-notification-count element');
            }
            
            // Show/hide Mark all read button based on unread count
            const markAllBtn = document.getElementById('topbar-mark-all-read');
            if (markAllBtn) {
                markAllBtn.style.display = count > 0 ? 'inline-block' : 'none';
            }
        }
        
        async markAsRead(notificationId, element = null) {
            try {
                const response = await fetch(`/admin/notifications/${notificationId}/mark-as-read`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json'
                    }
                });
                
                const data = await response.json();
                if (data.success && element) {
                    // Remove the notification from display with fade effect
                    element.style.opacity = '0.5';
                    element.style.transition = 'opacity 0.3s ease';
                    
                    setTimeout(() => {
                        // Refresh notifications to get accurate count from server
                        this.loadNotifications(true);
                    }, 300);
                } else if (!data.success) {
                    this.showErrorMessage('Failed to mark notification as read');
                }
            } catch (error) {
                console.error('Error marking notification as read:', error);
                this.showErrorMessage('Network error occurred');
            }
        }
        
        async markAllAsRead() {
            const markAllBtn = document.getElementById('topbar-mark-all-read');
            
            try {
                console.log('AdminNotificationBell: Mark all as read triggered');
                
                // Show loading state
                if (markAllBtn) {
                    markAllBtn.disabled = true;
                    markAllBtn.innerHTML = '<i class="mdi mdi-loading mdi-spin me-1"></i>Marking...';
                }
                
                const response = await fetch('/admin/notifications/mark-all-as-read', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                console.log('AdminNotificationBell: Mark all as read response:', data);
                
                if (data.success) {
                    console.log('AdminNotificationBell: Successfully marked all as read, updating UI');
                    
                    // Update notification count immediately
                    this.updateNotificationCount(0);
                    
                    // Show empty state immediately
                    const container = document.getElementById('topbar-admin-notifications-container');
                    if (container) {
                        container.innerHTML = `
                            <div class="text-center text-muted py-4">
                                <i class="mdi mdi-bell-check fs-24 mb-2"></i>
                                <p class="mb-0">All caught up!</p>
                                <p class="mb-0 fs-12">No new notifications</p>
                            </div>
                        `;
                    }
                    
                    // Show success message
                    this.showSuccessMessage('All notifications marked as read');
                } else {
                    console.error('AdminNotificationBell: Mark all as read failed:', data.message || 'Unknown error');
                    this.showErrorMessage(data.message || 'Failed to mark notifications as read');
                }
            } catch (error) {
                console.error('AdminNotificationBell: Error marking all notifications as read:', error);
                this.showErrorMessage('Network error occurred: ' + error.message);
            } finally {
                // Reset button state
                if (markAllBtn) {
                    markAllBtn.disabled = false;
                    markAllBtn.innerHTML = 'Mark all read';
                }
            }
        }
        
        showLoading() {
            const container = document.getElementById('topbar-admin-notifications-container');
            if (container) {
                container.innerHTML = `
                    <div class="text-center text-muted py-4">
                        <i class="mdi mdi-loading mdi-spin fs-18 mb-2"></i>
                        <p class="mb-0">Loading notifications...</p>
                    </div>
                `;
            }
        }
        
        showError(message) {
            const container = document.getElementById('topbar-admin-notifications-container');
            if (container) {
                container.innerHTML = `
                    <div class="text-center text-danger py-4">
                        <i class="mdi mdi-alert-circle fs-18 mb-2"></i>
                        <p class="mb-0">${message}</p>
                    </div>
                `;
            }
        }
        
        showSuccessMessage(message) {
            // Use toastr if available, otherwise show in console
            if (typeof toastr !== 'undefined') {
                toastr.success(message);
            } else {
                console.log('Success: ' + message);
            }
        }
        
        showErrorMessage(message) {
            // Use toastr if available, otherwise show in console
            if (typeof toastr !== 'undefined') {
                toastr.error(message);
            } else {
                console.error('Error: ' + message);
            }
        }
    }
    
    // Initialize Admin Notification Bell when document is ready
    document.addEventListener('DOMContentLoaded', function() {
        console.log('AdminNotificationBell: DOM Content Loaded, initializing...');
        
        // Check if we're on an admin page (not user role_id 2)
        const isAdmin = @json(auth()->check() && auth()->user()->role_id != 2);
        console.log('AdminNotificationBell: Is admin user?', isAdmin);
        
        if (isAdmin) {
            window.adminNotificationBell = new AdminNotificationBell();
            
            // Fallback: If initial load fails, try to get count from backend
            setTimeout(() => {
                const badge = document.getElementById('admin-notification-badge');
                const countSpan = document.getElementById('topbar-notification-count');
                
                if (badge && badge.style.display === 'none' && countSpan && countSpan.textContent === '0 New') {
                    console.log('AdminNotificationBell: Fallback check - trying to get initial count');
                    window.adminNotificationBell.loadNotifications(true);
                }
            }, 2000);
        }
    });
    </script>
    @endif
    @endauth

</body>
<script>
  
</script>
</html>
