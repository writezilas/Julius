/**
 * Enhanced Announcement Card JavaScript
 * Handles inline expansion/collapse of announcement content without popups
 */

class AnnouncementCard {
    constructor() {
        this.init();
    }

    init() {
        // Initialize all announcement toggles
        this.bindToggleEvents();
        
        // Auto-expand first announcement if there's only one
        this.autoExpandSingle();
        
        // Add smooth scrolling to expanded announcements
        this.addSmoothScrolling();
    }

    bindToggleEvents() {
        // Bind click events to all toggle buttons
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('announcement-toggle') || 
                e.target.closest('.announcement-toggle')) {
                
                e.preventDefault();
                e.stopPropagation();
                
                const toggleBtn = e.target.classList.contains('announcement-toggle') 
                    ? e.target 
                    : e.target.closest('.announcement-toggle');
                
                this.toggleAnnouncement(toggleBtn);
            }
        });
    }

    toggleAnnouncement(toggleBtn) {
        const announcementItem = toggleBtn.closest('.announcement-item-enhanced');
        const content = announcementItem.querySelector('.announcement-content');
        const icon = toggleBtn.querySelector('.announcement-toggle-icon');
        const toggleText = toggleBtn.querySelector('.toggle-text');
        
        if (!content) return;

        const isExpanded = content.classList.contains('expanded');
        
        if (isExpanded) {
            this.collapseContent(content, toggleBtn, icon, toggleText);
        } else {
            this.expandContent(content, toggleBtn, icon, toggleText);
        }
    }

    expandContent(content, toggleBtn, icon, toggleText) {
        // Update button state
        toggleBtn.classList.remove('collapsed');
        toggleBtn.classList.add('expanded');
        
        if (icon) {
            icon.classList.add('expanded');
        }
        
        if (toggleText) {
            toggleText.textContent = 'Hide Details';
        }

        // Expand content with animation
        content.style.maxHeight = '0px';
        content.style.opacity = '0';
        content.classList.add('expanded');
        
        // Force reflow
        content.offsetHeight;
        
        // Calculate the actual height needed
        const scrollHeight = content.scrollHeight;
        content.style.maxHeight = scrollHeight + 'px';
        content.style.opacity = '1';
        
        // Clean up after animation
        setTimeout(() => {
            content.style.maxHeight = 'none';
        }, 300);

        // Smooth scroll to keep the announcement in view
        setTimeout(() => {
            this.scrollToAnnouncement(content.closest('.announcement-item-enhanced'));
        }, 100);

        // Track expansion event
        this.trackEvent('announcement_expanded', {
            announcement_id: content.closest('.announcement-item-enhanced').dataset.announcementId
        });
    }

    collapseContent(content, toggleBtn, icon, toggleText) {
        // Update button state
        toggleBtn.classList.remove('expanded');
        toggleBtn.classList.add('collapsed');
        
        if (icon) {
            icon.classList.remove('expanded');
        }
        
        if (toggleText) {
            toggleText.textContent = 'View Details';
        }

        // Get current height for smooth collapse
        const currentHeight = content.scrollHeight;
        content.style.maxHeight = currentHeight + 'px';
        
        // Force reflow
        content.offsetHeight;
        
        // Collapse
        content.style.maxHeight = '0px';
        content.style.opacity = '0';
        
        // Remove expanded class after animation
        setTimeout(() => {
            content.classList.remove('expanded');
        }, 300);

        // Track collapse event
        this.trackEvent('announcement_collapsed', {
            announcement_id: content.closest('.announcement-item-enhanced').dataset.announcementId
        });
    }

    autoExpandSingle() {
        const announcements = document.querySelectorAll('.announcement-item-enhanced');
        
        // If there's only one announcement, auto-expand it
        if (announcements.length === 1) {
            const toggleBtn = announcements[0].querySelector('.announcement-toggle');
            if (toggleBtn) {
                setTimeout(() => {
                    this.toggleAnnouncement(toggleBtn);
                }, 500);
            }
        }
    }

    scrollToAnnouncement(announcementElement) {
        if (!announcementElement) return;

        const rect = announcementElement.getBoundingClientRect();
        const viewportHeight = window.innerHeight;
        
        // Only scroll if the announcement is not fully visible
        if (rect.bottom > viewportHeight || rect.top < 100) {
            announcementElement.scrollIntoView({
                behavior: 'smooth',
                block: 'center',
                inline: 'nearest'
            });
        }
    }

    addSmoothScrolling() {
        // Add smooth scrolling for any anchor links within announcements
        document.addEventListener('click', (e) => {
            const link = e.target.closest('a[href^="#"]');
            if (link && link.closest('.announcement-content')) {
                e.preventDefault();
                const targetId = link.getAttribute('href').substring(1);
                const targetElement = document.getElementById(targetId);
                
                if (targetElement) {
                    targetElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    }

    trackEvent(eventName, data = {}) {
        // Simple event tracking - can be extended to use analytics
        if (typeof gtag !== 'undefined') {
            gtag('event', eventName, data);
        }
        
        // Console log for debugging
        console.log('Announcement Event:', eventName, data);
    }

    // Utility method to expand all announcements
    expandAll() {
        const toggleButtons = document.querySelectorAll('.announcement-toggle.collapsed');
        toggleButtons.forEach(btn => {
            setTimeout(() => {
                this.toggleAnnouncement(btn);
            }, Math.random() * 500); // Stagger the expansions
        });
    }

    // Utility method to collapse all announcements
    collapseAll() {
        const toggleButtons = document.querySelectorAll('.announcement-toggle.expanded');
        toggleButtons.forEach(btn => {
            setTimeout(() => {
                this.toggleAnnouncement(btn);
            }, Math.random() * 300);
        });
    }

    // Method to handle image loading errors in announcements
    handleImageErrors() {
        const images = document.querySelectorAll('.announcement-content img');
        images.forEach(img => {
            img.addEventListener('error', function() {
                this.style.display = 'none';
                
                // Create a placeholder
                const placeholder = document.createElement('div');
                placeholder.className = 'announcement-image-placeholder';
                placeholder.innerHTML = `
                    <div class="text-center p-3 bg-light rounded">
                        <i class="mdi mdi-image-broken text-muted" style="font-size: 2rem;"></i>
                        <p class="text-muted mb-0 mt-2">Image could not be loaded</p>
                    </div>
                `;
                
                this.parentNode.replaceChild(placeholder, this);
            });
        });
    }

    // Method to handle video loading for announcements
    setupVideoHandling() {
        const videos = document.querySelectorAll('.announcement-content iframe');
        videos.forEach(video => {
            video.addEventListener('load', function() {
                // Add loaded class for any additional styling
                this.classList.add('video-loaded');
            });
        });
    }

    // Method to setup keyboard navigation
    setupKeyboardNavigation() {
        document.addEventListener('keydown', (e) => {
            // Toggle announcement on Enter or Space when focused
            if ((e.key === 'Enter' || e.key === ' ') && 
                e.target.classList.contains('announcement-toggle')) {
                e.preventDefault();
                this.toggleAnnouncement(e.target);
            }
        });
    }
}

// Initialize the announcement card functionality when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    const announcementCard = new AnnouncementCard();
    
    // Setup additional features
    announcementCard.handleImageErrors();
    announcementCard.setupVideoHandling();
    announcementCard.setupKeyboardNavigation();
    
    // Make it globally accessible for debugging
    window.announcementCard = announcementCard;
    
    console.log('Enhanced Announcement Card initialized successfully!');
});

// Export for use in other scripts if needed
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AnnouncementCard;
}