/**
 * TrackTraster - Dashboard JavaScript
 */

// Dashboard initialization
document.addEventListener('DOMContentLoaded', function() {
    initializeDashboard();
});

/**
 * Initialize dashboard functionality
 */
function initializeDashboard() {
    console.log('Dashboard initialized');
    
    // Initialize sidebar functionality
    initializeSidebar();
    
    // Initialize responsive behavior
    initializeResponsive();
    
    // Initialize tooltips
    initializeTooltips();
    
    // Initialize auto-refresh for stats (if needed)
    initializeAutoRefresh();
}

/**
 * Sidebar functionality
 */
function initializeSidebar() {
    // Handle active menu item
    updateActiveMenuItem();
    
    // Handle sidebar collapse on mobile
    const sidebarToggle = document.querySelectorAll('.sidebar-toggle');
    sidebarToggle.forEach(toggle => {
        toggle.addEventListener('click', toggleSidebar);
    });
    
    // Handle sidebar overlay click
    const overlay = document.querySelector('.sidebar-overlay');
    if (overlay) {
        overlay.addEventListener('click', closeSidebar);
    }
}

/**
 * Toggle sidebar on mobile
 */
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.querySelector('.sidebar-overlay');
    
    if (sidebar && overlay) {
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
        document.body.style.overflow = sidebar.classList.contains('active') ? 'hidden' : '';
    }
}

/**
 * Close sidebar
 */
function closeSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.querySelector('.sidebar-overlay');
    
    if (sidebar && overlay) {
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
        document.body.style.overflow = '';
    }
}

/**
 * Update active menu item based on current URL
 */
function updateActiveMenuItem() {
    const currentPath = window.location.pathname;
    const menuItems = document.querySelectorAll('.menu-item');
    
    menuItems.forEach(item => {
        const href = item.getAttribute('href');
        if (href && currentPath.includes(href.replace(/^.*\/tracktraster/, ''))) {
            item.classList.add('active');
        }
    });
}

/**
 * Initialize responsive behavior
 */
function initializeResponsive() {
    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 992) {
            closeSidebar();
        }
    });
    
    // Handle escape key to close sidebar
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeSidebar();
        }
    });
}

/**
 * Initialize tooltips
 */
function initializeTooltips() {
    // Initialize Bootstrap tooltips if available
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
}

/**
 * Initialize auto-refresh for stats
 */
function initializeAutoRefresh() {
    // This could be used to periodically refresh stats
    // For now, we'll just set up the structure
    
    const refreshInterval = 5 * 60 * 1000; // 5 minutes
    
    // setInterval(() => {
    //     refreshDashboardStats();
    // }, refreshInterval);
}

/**
 * Refresh dashboard stats (placeholder for future implementation)
 */
function refreshDashboardStats() {
    // This will be implemented when we add AJAX functionality
    console.log('Refreshing dashboard stats...');
}

/**
 * Utility functions for dashboard
 */
const Dashboard = {
    // Show loading state
    showLoading: function(element) {
        if (element) {
            element.classList.add('loading');
            element.style.opacity = '0.6';
            element.style.pointerEvents = 'none';
        }
    },
    
    // Hide loading state
    hideLoading: function(element) {
        if (element) {
            element.classList.remove('loading');
            element.style.opacity = '';
            element.style.pointerEvents = '';
        }
    },
    
    // Show notification
    showNotification: function(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = `
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        `;
        
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 5000);
    },
    
    // Format number with commas
    formatNumber: function(num) {
        return new Intl.NumberFormat().format(num);
    },
    
    // Format percentage
    formatPercentage: function(num, decimals = 1) {
        return `${num.toFixed(decimals)}%`;
    },
    
    // Format date
    formatDate: function(date) {
        return new Intl.DateTimeFormat('es-ES', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        }).format(new Date(date));
    },
    
    // Animate counter
    animateCounter: function(element, target, duration = 1000) {
        const start = parseInt(element.textContent) || 0;
        const increment = (target - start) / (duration / 16);
        let current = start;
        
        const timer = setInterval(() => {
            current += increment;
            if ((increment > 0 && current >= target) || (increment < 0 && current <= target)) {
                current = target;
                clearInterval(timer);
            }
            element.textContent = Math.floor(current);
        }, 16);
    }
};

/**
 * Chart utilities (for future use with Chart.js)
 */
const ChartUtils = {
    // Default chart colors
    colors: {
        primary: '#6366f1',
        secondary: '#8b5cf6',
        success: '#10b981',
        warning: '#f59e0b',
        danger: '#ef4444',
        info: '#06b6d4'
    },
    
    // Create gradient
    createGradient: function(ctx, color1, color2) {
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, color1);
        gradient.addColorStop(1, color2);
        return gradient;
    },
    
    // Default chart options
    getDefaultOptions: function() {
        return {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: '#e2e8f0'
                    }
                },
                x: {
                    grid: {
                        color: '#e2e8f0'
                    }
                }
            }
        };
    }
};

// Make functions available globally
window.toggleSidebar = toggleSidebar;
window.Dashboard = Dashboard;
window.ChartUtils = ChartUtils;
