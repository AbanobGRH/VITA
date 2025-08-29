// VITA Health Platform JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu functionality
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const sidebar = document.getElementById('sidebar');
    const sidebarClose = document.getElementById('sidebarClose');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    function openSidebar() {
        sidebar.classList.add('open');
        sidebarOverlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeSidebar() {
        sidebar.classList.remove('open');
        sidebarOverlay.classList.remove('active');
        document.body.style.overflow = '';
    }

    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', openSidebar);
    }

    if (sidebarClose) {
        sidebarClose.addEventListener('click', closeSidebar);
    }

    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', closeSidebar);
    }

    // Close sidebar on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && sidebar.classList.contains('open')) {
            closeSidebar();
        }
    });

    // Active navigation link
    function setActiveNavLink() {
        const currentPage = window.location.pathname.split('/').pop() || 'index.html';
        const navLinks = document.querySelectorAll('.nav-link');
        
        navLinks.forEach(link => {
            link.classList.remove('active');
            const href = link.getAttribute('href');
            if (href === currentPage || (currentPage === '' && href === 'index.html')) {
                link.classList.add('active');
            }
        });
    }

    setActiveNavLink();

    // Simulate real-time data updates
    function updateMetrics() {
        const heartRate = document.querySelector('.metric-card:nth-child(1) .metric-value');
        const bloodPressure = document.querySelector('.metric-card:nth-child(2) .metric-value');
        const oxygen = document.querySelector('.metric-card:nth-child(3) .metric-value');
        const glucose = document.querySelector('.metric-card:nth-child(4) .metric-value');

        if (heartRate) {
            // Simulate heart rate variation (68-76 bpm)
            const newHeartRate = Math.floor(Math.random() * 9) + 68;
            heartRate.innerHTML = `${newHeartRate} <span class="metric-unit">bpm</span>`;
        }

        if (bloodPressure) {
            // Simulate blood pressure variation
            const systolic = Math.floor(Math.random() * 10) + 115;
            const diastolic = Math.floor(Math.random() * 8) + 75;
            bloodPressure.innerHTML = `${systolic}/${diastolic} <span class="metric-unit">mmHg</span>`;
        }

        if (oxygen) {
            // Simulate SpO2 variation (96-99%)
            const newOxygen = Math.floor(Math.random() * 4) + 96;
            oxygen.innerHTML = `${newOxygen} <span class="metric-unit">%</span>`;
        }

        if (glucose) {
            // Simulate glucose variation (90-110 mg/dL)
            const newGlucose = Math.floor(Math.random() * 21) + 90;
            glucose.innerHTML = `${newGlucose} <span class="metric-unit">mg/dL</span>`;
        }
    }

    // Update metrics every 30 seconds
    setInterval(updateMetrics, 30000);

    // Battery level animation
    function animateBattery() {
        const batteryFill = document.querySelector('.battery-fill');
        if (batteryFill) {
            let currentWidth = 87;
            setInterval(() => {
                // Simulate battery drain (very slow)
                if (Math.random() < 0.1) { // 10% chance every interval
                    currentWidth = Math.max(20, currentWidth - 1);
                    batteryFill.style.width = `${currentWidth}%`;
                    
                    // Update battery percentage text
                    const batteryText = batteryFill.parentElement.nextElementSibling;
                    if (batteryText) {
                        batteryText.textContent = `${currentWidth}%`;
                    }
                }
            }, 60000); // Check every minute
        }
    }

    animateBattery();

    // Quick action button handlers
    const actionButtons = document.querySelectorAll('.action-btn');
    actionButtons.forEach(button => {
        button.addEventListener('click', function() {
            const buttonType = this.className.split(' ')[1];
            handleQuickAction(buttonType);
        });
    });

    function handleQuickAction(actionType) {
        switch(actionType) {
            case 'emergency':
                showNotification('Emergency alert triggered! Contacting emergency services...', 'error');
                break;
            case 'medication':
                showNotification('Redirecting to medication manager...', 'info');
                setTimeout(() => {
                    window.location.href = 'medication.html';
                }, 1500);
                break;
            case 'location':
                showNotification('Opening location settings...', 'info');
                setTimeout(() => {
                    window.location.href = 'location.html';
                }, 1500);
                break;
            case 'health':
                showNotification('Generating health report...', 'success');
                setTimeout(() => {
                    window.location.href = 'health-metrics.html';
                }, 1500);
                break;
        }
    }

    // Notification system
    function showNotification(message, type = 'info') {
        // Remove existing notifications
        const existingNotification = document.querySelector('.notification');
        if (existingNotification) {
            existingNotification.remove();
        }

        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <span>${message}</span>
                <button class="notification-close">&times;</button>
            </div>
        `;

        // Add notification styles
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow-soft-lg);
            border: 1px solid var(--vita-grey-light);
            padding: 16px;
            z-index: 1000;
            max-width: 400px;
            transform: translateX(100%);
            transition: transform 0.3s ease;
        `;

        if (type === 'error') {
            notification.style.borderLeftColor = 'var(--vita-coral)';
            notification.style.borderLeftWidth = '4px';
        } else if (type === 'success') {
            notification.style.borderLeftColor = 'var(--vita-mint)';
            notification.style.borderLeftWidth = '4px';
        } else {
            notification.style.borderLeftColor = 'var(--vita-blue)';
            notification.style.borderLeftWidth = '4px';
        }

        document.body.appendChild(notification);

        // Animate in
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 100);

        // Close button functionality
        const closeBtn = notification.querySelector('.notification-close');
        closeBtn.addEventListener('click', () => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => notification.remove(), 300);
        });

        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (notification.parentElement) {
                notification.style.transform = 'translateX(100%)';
                setTimeout(() => notification.remove(), 300);
            }
        }, 5000);
    }

    // Add smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Add loading states for navigation
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            if (this.getAttribute('href') !== '#' && !this.classList.contains('active')) {
                this.style.opacity = '0.6';
                this.style.pointerEvents = 'none';
                
                // Reset after navigation
                setTimeout(() => {
                    this.style.opacity = '';
                    this.style.pointerEvents = '';
                }, 1000);
            }
        });
    });
});

// Utility functions
function formatTime(date) {
    return date.toLocaleTimeString('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    });
}

function formatDate(date) {
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

// Export functions for use in other pages
window.VitaApp = {
    showNotification: function(message, type) {
        // This will be available globally
        const event = new CustomEvent('showNotification', {
            detail: { message, type }
        });
        document.dispatchEvent(event);
    },
    formatTime,
    formatDate
};