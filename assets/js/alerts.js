// Alerts Page JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Emergency button functionality
    const emergencyBtn = document.querySelector('.emergency-btn');
    if (emergencyBtn) {
        emergencyBtn.addEventListener('click', function() {
            triggerEmergencyAlert();
        });
    }

    // Test system button
    const testBtn = document.querySelector('.test-btn');
    if (testBtn) {
        testBtn.addEventListener('click', function() {
            testEmergencySystem();
        });
    }

    // Alert action buttons
    const alertActionBtns = document.querySelectorAll('.alert-action-btn');
    alertActionBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const action = this.textContent.toLowerCase();
            const alertItem = this.closest('.alert-item');
            handleAlertAction(action, alertItem);
        });
    });

    // Contact action buttons
    const contactActionBtns = document.querySelectorAll('.contact-action-btn');
    contactActionBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const action = this.classList.contains('call') ? 'call' : 'edit';
            const contactItem = this.closest('.contact-item');
            handleContactAction(action, contactItem);
        });
    });

    // Add contact button
    const addContactBtn = document.querySelector('.add-contact-btn');
    if (addContactBtn) {
        addContactBtn.addEventListener('click', function() {
            showAddContactModal();
        });
    }

    // Clear all alerts button
    const clearAlertsBtn = document.querySelector('.clear-alerts-btn');
    if (clearAlertsBtn) {
        clearAlertsBtn.addEventListener('click', function() {
            clearAllAlerts();
        });
    }

    // Toggle switches for notification settings
    const toggleSwitches = document.querySelectorAll('.toggle-switch input');
    toggleSwitches.forEach(toggle => {
        toggle.addEventListener('change', function() {
            const settingName = this.closest('.setting-item').querySelector('span').textContent;
            handleNotificationToggle(settingName, this.checked);
        });
    });

    function triggerEmergencyAlert() {
        // Show confirmation dialog
        const confirmed = confirm('Are you sure you want to trigger an emergency alert? This will notify all emergency contacts and emergency services.');
        
        if (confirmed) {
            // Simulate emergency alert process
            if (window.VitaApp) {
                window.VitaApp.showNotification('EMERGENCY ALERT ACTIVATED', 'error');
            }
            
            // Update emergency status
            const statusIndicator = document.querySelector('.emergency-status .status-indicator');
            if (statusIndicator) {
                statusIndicator.innerHTML = `
                    <div class="status-dot emergency"></div>
                    <span>Emergency Alert Active</span>
                `;
                statusIndicator.className = 'status-indicator emergency';
            }
            
            // Simulate calling emergency contacts
            setTimeout(() => {
                if (window.VitaApp) {
                    window.VitaApp.showNotification('Contacting emergency contacts...', 'info');
                }
            }, 2000);
            
            setTimeout(() => {
                if (window.VitaApp) {
                    window.VitaApp.showNotification('Emergency services have been notified', 'info');
                }
            }, 4000);
        }
    }

    function testEmergencySystem() {
        if (window.VitaApp) {
            window.VitaApp.showNotification('Testing emergency system...', 'info');
        }
        
        // Simulate system test
        setTimeout(() => {
            if (window.VitaApp) {
                window.VitaApp.showNotification('✓ All emergency systems operational', 'success');
            }
        }, 2000);
    }

    function handleAlertAction(action, alertItem) {
        const alertMessage = alertItem.querySelector('.alert-message').textContent;
        
        switch(action) {
            case 'mark as taken':
                alertItem.style.opacity = '0.5';
                alertItem.querySelector('.alert-actions').innerHTML = '<span style="color: var(--vita-mint-dark); font-size: 0.75rem;">✓ Marked as taken</span>';
                if (window.VitaApp) {
                    window.VitaApp.showNotification('Medication marked as taken', 'success');
                }
                break;
            case 'snooze':
                alertItem.style.opacity = '0.7';
                if (window.VitaApp) {
                    window.VitaApp.showNotification('Alert snoozed for 15 minutes', 'info');
                }
                break;
            case 'view details':
                if (window.VitaApp) {
                    window.VitaApp.showNotification('Redirecting to health metrics...', 'info');
                }
                setTimeout(() => {
                    window.location.href = 'health-metrics.html';
                }, 1500);
                break;
            case 'dismiss':
                alertItem.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    alertItem.remove();
                }, 300);
                if (window.VitaApp) {
                    window.VitaApp.showNotification('Alert dismissed', 'info');
                }
                break;
        }
    }

    function handleContactAction(action, contactItem) {
        const contactName = contactItem.querySelector('h3').textContent;
        const contactPhone = contactItem.querySelector('.contact-phone').textContent;
        
        if (action === 'call') {
            if (contactPhone === '911') {
                const confirmed = confirm('Are you sure you want to call Emergency Services (911)?');
                if (confirmed) {
                    if (window.VitaApp) {
                        window.VitaApp.showNotification('Calling Emergency Services...', 'error');
                    }
                }
            } else {
                if (window.VitaApp) {
                    window.VitaApp.showNotification(`Calling ${contactName} at ${contactPhone}`, 'info');
                }
            }
        } else if (action === 'edit') {
            if (window.VitaApp) {
                window.VitaApp.showNotification(`Editing contact: ${contactName}`, 'info');
            }
        }
    }

    function showAddContactModal() {
        const modalOverlay = document.createElement('div');
        modalOverlay.className = 'modal-overlay';
        modalOverlay.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Add Emergency Contact</h3>
                    <button class="modal-close">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" placeholder="Enter full name" class="form-input">
                    </div>
                    <div class="form-group">
                        <label>Relationship</label>
                        <select class="form-input">
                            <option>Family Member</option>
                            <option>Friend</option>
                            <option>Neighbor</option>
                            <option>Healthcare Provider</option>
                            <option>Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="tel" placeholder="(555) 123-4567" class="form-input">
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" placeholder="contact@email.com" class="form-input">
                    </div>
                    <div class="form-group">
                        <label>Priority Level</label>
                        <select class="form-input">
                            <option>Primary Contact</option>
                            <option>Secondary Contact</option>
                            <option>Emergency Only</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn-secondary modal-cancel">Cancel</button>
                    <button class="btn-primary modal-save">Add Contact</button>
                </div>
            </div>
        `;

        document.body.appendChild(modalOverlay);
        
        const closeBtn = modalOverlay.querySelector('.modal-close');
        const cancelBtn = modalOverlay.querySelector('.modal-cancel');
        const saveBtn = modalOverlay.querySelector('.modal-save');

        function closeModal() {
            modalOverlay.remove();
        }

        closeBtn.addEventListener('click', closeModal);
        cancelBtn.addEventListener('click', closeModal);
        modalOverlay.addEventListener('click', function(e) {
            if (e.target === modalOverlay) closeModal();
        });

        saveBtn.addEventListener('click', function() {
            const name = modalOverlay.querySelector('input[placeholder*="name"]').value;
            const relationship = modalOverlay.querySelector('select').value;
            const phone = modalOverlay.querySelector('input[type="tel"]').value;
            const email = modalOverlay.querySelector('input[type="email"]').value;

            if (name && phone) {
                addNewContact(name, relationship, phone, email);
                closeModal();
                if (window.VitaApp) {
                    window.VitaApp.showNotification(`${name} added to emergency contacts`, 'success');
                }
            } else {
                if (window.VitaApp) {
                    window.VitaApp.showNotification('Please fill in required fields', 'error');
                }
            }
        });

        setTimeout(() => {
            modalOverlay.querySelector('.form-input').focus();
        }, 100);
    }

    function addNewContact(name, relationship, phone, email) {
        const contactsList = document.querySelector('.contacts-list');
        const newContact = document.createElement('div');
        newContact.className = 'contact-item';
        newContact.innerHTML = `
            <div class="contact-avatar">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
            </div>
            <div class="contact-info">
                <h3>${name}</h3>
                <p>${relationship}</p>
                <span class="contact-phone">${phone}</span>
            </div>
            <div class="contact-actions">
                <button class="contact-action-btn call">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                    </svg>
                </button>
                <button class="contact-action-btn edit">Edit</button>
            </div>
        `;

        contactsList.appendChild(newContact);

        // Add event listeners to new contact
        const actionBtns = newContact.querySelectorAll('.contact-action-btn');
        actionBtns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const action = this.classList.contains('call') ? 'call' : 'edit';
                const contactItem = this.closest('.contact-item');
                handleContactAction(action, contactItem);
            });
        });
    }

    function clearAllAlerts() {
        const confirmed = confirm('Are you sure you want to clear all alerts?');
        if (confirmed) {
            const alertItems = document.querySelectorAll('.alert-item');
            alertItems.forEach((item, index) => {
                setTimeout(() => {
                    item.style.transform = 'translateX(100%)';
                    setTimeout(() => {
                        item.remove();
                    }, 300);
                }, index * 100);
            });
            
            if (window.VitaApp) {
                window.VitaApp.showNotification('All alerts cleared', 'success');
            }
        }
    }

    function handleNotificationToggle(settingName, enabled) {
        const status = enabled ? 'enabled' : 'disabled';
        if (window.VitaApp) {
            window.VitaApp.showNotification(`${settingName} ${status}`, 'info');
        }
    }

    // Simulate new alerts
    function simulateNewAlert() {
        const alertTypes = [
            { type: 'Medication', message: 'Evening medication reminder', priority: 'medium' },
            { type: 'Health', message: 'Blood pressure reading recommended', priority: 'low' },
            { type: 'Device', message: 'Device sync completed', priority: 'low' }
        ];
        
        const randomAlert = alertTypes[Math.floor(Math.random() * alertTypes.length)];
        const now = new Date();
        const timeString = now.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
        
        addNewAlert(randomAlert.type, randomAlert.message, randomAlert.priority, timeString);
    }

    function addNewAlert(type, message, priority, time) {
        const alertsList = document.querySelector('.alerts-list');
        const newAlert = document.createElement('div');
        newAlert.className = `alert-item ${priority}`;
        newAlert.innerHTML = `
            <div class="alert-indicator"></div>
            <div class="alert-content">
                <div class="alert-header">
                    <span class="alert-type">${type}</span>
                    <span class="alert-time">${time}</span>
                </div>
                <p class="alert-message">${message}</p>
                <div class="alert-actions">
                    <button class="alert-action-btn">Dismiss</button>
                </div>
            </div>
        `;
        
        // Add to top of list
        alertsList.insertBefore(newAlert, alertsList.firstChild);
        
        // Add event listeners
        const actionBtns = newAlert.querySelectorAll('.alert-action-btn');
        actionBtns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const action = this.textContent.toLowerCase();
                const alertItem = this.closest('.alert-item');
                handleAlertAction(action, alertItem);
            });
        });
        
        // Animate in
        newAlert.style.transform = 'translateX(-100%)';
        setTimeout(() => {
            newAlert.style.transform = 'translateX(0)';
        }, 100);
    }

    // Add new alert every 2 minutes for demo
    setInterval(simulateNewAlert, 120000);

    // Add smooth animations to existing items
    const alertItems = document.querySelectorAll('.alert-item');
    alertItems.forEach((item, index) => {
        item.style.animationDelay = `${index * 0.1}s`;
        item.classList.add('fade-in');
    });

    const contactItems = document.querySelectorAll('.contact-item');
    contactItems.forEach((item, index) => {
        item.style.animationDelay = `${index * 0.1}s`;
        item.classList.add('fade-in');
    });
});

// Add custom styles for alerts page
const alertsStyles = `
    .emergency-status {
        margin-bottom: 2rem;
    }

    .emergency-actions {
        display: flex;
        gap: 1rem;
        margin-top: 1rem;
    }

    .emergency-btn,
    .test-btn {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 1rem 1.5rem;
        border-radius: 1.5rem;
        font-size: 0.875rem;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .emergency-btn {
        background: var(--vita-coral);
        color: white;
        flex: 1;
    }

    .emergency-btn:hover {
        background: #dc2626;
        transform: translateY(-2px);
    }

    .test-btn {
        background: rgba(74, 144, 226, 0.1);
        color: var(--vita-blue);
        border: 1px solid rgba(74, 144, 226, 0.3);
    }

    .test-btn:hover {
        background: rgba(74, 144, 226, 0.2);
    }

    .emergency-btn svg,
    .test-btn svg {
        width: 1.25rem;
        height: 1.25rem;
    }

    .status-indicator.emergency {
        color: var(--vita-coral);
    }

    .status-indicator.emergency .status-dot {
        background: var(--vita-coral);
        animation: pulse-emergency 1s infinite;
    }

    @keyframes pulse-emergency {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.3; }
    }

    .clear-alerts-btn {
        font-size: 0.875rem;
        color: #6b7280;
        background: none;
        border: none;
        cursor: pointer;
        transition: color 0.2s ease;
    }

    .clear-alerts-btn:hover {
        color: var(--vita-coral);
    }

    .alert-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.5rem;
    }

    .alert-type {
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--vita-blue);
        background: rgba(74, 144, 226, 0.1);
        padding: 0.25rem 0.5rem;
        border-radius: 0.75rem;
    }

    .alert-time {
        font-size: 0.75rem;
        color: #6b7280;
    }

    .alert-actions {
        display: flex;
        gap: 0.75rem;
        margin-top: 0.75rem;
    }

    .alert-action-btn {
        font-size: 0.75rem;
        color: var(--vita-blue);
        background: none;
        border: none;
        cursor: pointer;
        transition: color 0.2s ease;
        padding: 0.25rem 0.5rem;
        border-radius: 0.5rem;
    }

    .alert-action-btn:hover {
        background: rgba(74, 144, 226, 0.1);
        color: var(--vita-blue-dark);
    }

    .add-contact-btn {
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--vita-blue);
        background: none;
        border: none;
        cursor: pointer;
        transition: color 0.2s ease;
    }

    .add-contact-btn:hover {
        color: var(--vita-blue-dark);
    }

    .contacts-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .contact-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        border: 1px solid var(--vita-grey-light);
        border-radius: 1.5rem;
        transition: all 0.2s ease;
    }

    .contact-item:hover {
        background: var(--vita-grey-light);
    }

    .contact-item.primary {
        background: rgba(74, 144, 226, 0.05);
        border-color: rgba(74, 144, 226, 0.2);
    }

    .contact-avatar {
        width: 3rem;
        height: 3rem;
        background: var(--vita-grey-light);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .contact-avatar svg {
        width: 1.5rem;
        height: 1.5rem;
        color: #6b7280;
    }

    .contact-info {
        flex: 1;
    }

    .contact-info h3 {
        font-size: 1rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 0.25rem;
    }

    .contact-info p {
        font-size: 0.875rem;
        color: #6b7280;
        margin-bottom: 0.25rem;
    }

    .contact-phone {
        font-size: 0.875rem;
        color: var(--vita-blue);
        font-weight: 500;
    }

    .contact-actions {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .contact-action-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 50%;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .contact-action-btn.call {
        background: var(--vita-mint);
        color: white;
    }

    .contact-action-btn.call:hover {
        background: var(--vita-mint-dark);
    }

    .contact-action-btn.call.emergency {
        background: var(--vita-coral);
    }

    .contact-action-btn.call.emergency:hover {
        background: #dc2626;
    }

    .contact-action-btn.edit {
        background: var(--vita-grey-light);
        color: #6b7280;
        font-size: 0.75rem;
        width: auto;
        height: auto;
        padding: 0.5rem 0.75rem;
        border-radius: 1rem;
    }

    .contact-action-btn.edit:hover {
        background: var(--vita-grey);
        color: #374151;
    }

    .contact-action-btn svg {
        width: 1.25rem;
        height: 1.25rem;
    }

    .notification-settings {
        display: flex;
        flex-direction: column;
        gap: 2rem;
    }

    .setting-group {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .setting-group h3 {
        font-size: 1rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 0.5rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid var(--vita-grey-light);
    }

    .setting-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem;
        background: var(--vita-white);
        border-radius: 1rem;
        border: 1px solid var(--vita-grey-light);
    }

    .setting-info {
        flex: 1;
    }

    .setting-info span {
        font-size: 0.875rem;
        font-weight: 500;
        color: #1f2937;
        display: block;
        margin-bottom: 0.25rem;
    }

    .setting-info p {
        font-size: 0.75rem;
        color: #6b7280;
        margin: 0;
    }

    .fade-in {
        animation: fadeIn 0.5s ease forwards;
        opacity: 0;
    }

    @keyframes fadeIn {
        to {
            opacity: 1;
        }
    }

    @media (max-width: 768px) {
        .emergency-actions {
            flex-direction: column;
        }
        
        .contact-item {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }
        
        .contact-actions {
            align-self: flex-end;
        }
        
        .alert-actions {
            flex-wrap: wrap;
        }
    }
`;

// Inject styles
const styleSheet = document.createElement('style');
styleSheet.textContent = alertsStyles;
document.head.appendChild(styleSheet);