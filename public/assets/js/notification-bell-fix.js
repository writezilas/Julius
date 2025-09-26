/**
 * Notification Bell Fix
 * Ensures proper functionality of the notification dropdown
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('Notification Bell Fix: Initializing...');
    
    // Initialize Bootstrap dropdowns if not already initialized
    var dropdownElementList = [].slice.call(document.querySelectorAll('[data-bs-toggle="dropdown"]'));
    var dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
        return new bootstrap.Dropdown(dropdownToggleEl);
    });
    
    // Specific notification dropdown functionality
    const notificationButton = document.getElementById('page-header-notifications-dropdown');
    const notificationDropdown = document.querySelector('[aria-labelledby="page-header-notifications-dropdown"]');
    
    if (notificationButton && notificationDropdown) {
        console.log('Notification Bell Fix: Found notification elements');
        
        // Ensure the dropdown is properly initialized
        if (!bootstrap.Dropdown.getInstance(notificationButton)) {
            new bootstrap.Dropdown(notificationButton);
            console.log('Notification Bell Fix: Initialized Bootstrap dropdown');
        }
        
        // Add click event debugging
        notificationButton.addEventListener('click', function(e) {
            console.log('Notification Bell Fix: Bell clicked');
            
            // Check if badge exists and has count
            const badge = this.querySelector('.topbar-badge');
            if (badge) {
                console.log('Notification Bell Fix: Badge found with count:', badge.textContent.trim());
            } else {
                console.log('Notification Bell Fix: No badge found - no unread notifications');
            }
        });
        
        // Handle dropdown show/hide events
        notificationButton.addEventListener('show.bs.dropdown', function () {
            console.log('Notification Bell Fix: Dropdown showing');
        });
        
        notificationButton.addEventListener('hide.bs.dropdown', function () {
            console.log('Notification Bell Fix: Dropdown hiding');
        });
        
        // Check notification count on load
        const badge = notificationButton.querySelector('.topbar-badge');
        if (badge) {
            const count = badge.textContent.trim();
            console.log(`Notification Bell Fix: Found ${count} unread notifications`);
        } else {
            console.log('Notification Bell Fix: No unread notifications');
        }
    } else {
        console.error('Notification Bell Fix: Could not find notification elements');
    }
    
    // Auto-refresh notification count every 30 seconds
    setInterval(function() {
        // Only refresh if user is authenticated and on a user page
        if (window.location.pathname.includes('dashboard') || window.location.pathname === '/') {
            console.log('Notification Bell Fix: Auto-refresh triggered');
            // You could add AJAX call here to update notification count dynamically
        }
    }, 30000);
    
    console.log('Notification Bell Fix: Initialization complete');
});

// Helper function to manually trigger notification count refresh
window.refreshNotificationCount = function() {
    console.log('Notification Bell Fix: Manual refresh triggered');
    // Reload the page for now - could be enhanced with AJAX
    window.location.reload();
};
