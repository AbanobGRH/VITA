// Location Page JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Add zone button functionality
    const addZoneBtn = document.querySelector('.add-zone-btn');
    if (addZoneBtn) {
        addZoneBtn.addEventListener('click', function() {
            showAddZoneModal();
        });
    }

    // Zone action buttons
    const zoneActionBtns = document.querySelectorAll('.zone-action-btn');
    zoneActionBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const action = this.classList.contains('edit') ? 'edit' : 'remove';
            const zoneName = this.closest('.zone-item').querySelector('.zone-name').textContent;
            handleZoneAction(action, zoneName);
        });
    });

    // Toggle switches
    const toggleSwitches = document.querySelectorAll('.toggle-switch input');
    toggleSwitches.forEach(toggle => {
        toggle.addEventListener('change', function() {
            const settingName = this.closest('.setting-item').querySelector('h3').textContent;
            handleSettingToggle(settingName, this.checked);
        });
    });

    // Setting buttons
    const settingBtns = document.querySelectorAll('.setting-btn');
    settingBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const settingName = this.closest('.setting-item').querySelector('h3').textContent;
            handleSettingAction(settingName);
        });
    });

    // Map placeholder interaction
    const mapPlaceholder = document.querySelector('.map-placeholder');
    if (mapPlaceholder) {
        mapPlaceholder.addEventListener('click', function() {
            this.style.transform = 'scale(0.98)';
            setTimeout(() => {
                this.style.transform = 'scale(1)';
            }, 150);
            
            if (window.VitaApp) {
                window.VitaApp.showNotification('Interactive map coming soon!', 'info');
            }
        });
    }

    // History item interactions
    const historyItems = document.querySelectorAll('.history-item');
    historyItems.forEach(item => {
        item.addEventListener('click', function() {
            const location = this.querySelector('.history-location').textContent;
            const time = this.querySelector('.history-time').textContent;
            
            if (window.VitaApp) {
                window.VitaApp.showNotification(`Viewing details for ${location} at ${time}`, 'info');
            }
        });
    });

    function showAddZoneModal() {
        // Create modal overlay
        const modalOverlay = document.createElement('div');
        modalOverlay.className = 'modal-overlay';
        modalOverlay.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Add Safe Zone</h3>
                    <button class="modal-close">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Zone Name</label>
                        <input type="text" placeholder="e.g., Doctor's Office" class="form-input">
                    </div>
                    <div class="form-group">
                        <label>Address</label>
                        <input type="text" placeholder="Enter address" class="form-input">
                    </div>
                    <div class="form-group">
                        <label>Radius (meters)</label>
                        <input type="number" value="50" min="10" max="500" class="form-input">
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn-secondary modal-cancel">Cancel</button>
                    <button class="btn-primary modal-save">Add Zone</button>
                </div>
            </div>
        `;

        document.body.appendChild(modalOverlay);
        
        // Modal event listeners
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
            const name = modalOverlay.querySelector('input[placeholder*="Office"]').value;
            const address = modalOverlay.querySelector('input[placeholder*="address"]').value;
            const radius = modalOverlay.querySelector('input[type="number"]').value;

            if (name && address) {
                addNewZone(name, address, radius);
                closeModal();
                if (window.VitaApp) {
                    window.VitaApp.showNotification(`Safe zone "${name}" added successfully!`, 'success');
                }
            } else {
                if (window.VitaApp) {
                    window.VitaApp.showNotification('Please fill in all required fields', 'error');
                }
            }
        });

        // Focus first input
        setTimeout(() => {
            modalOverlay.querySelector('.form-input').focus();
        }, 100);
    }

    function addNewZone(name, address, radius) {
        const zonesList = document.querySelector('.zones-list');
        const newZone = document.createElement('div');
        newZone.className = 'zone-item active';
        newZone.innerHTML = `
            <div class="zone-status"></div>
            <div class="zone-info">
                <div class="zone-header">
                    <span class="zone-name">${name}</span>
                    <span class="zone-radius">${radius} meters</span>
                </div>
                <p class="zone-address">${address}</p>
                <div class="zone-actions">
                    <button class="zone-action-btn edit">Edit</button>
                    <button class="zone-action-btn remove">Remove</button>
                </div>
            </div>
        `;

        zonesList.appendChild(newZone);

        // Add event listeners to new zone
        const actionBtns = newZone.querySelectorAll('.zone-action-btn');
        actionBtns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const action = this.classList.contains('edit') ? 'edit' : 'remove';
                const zoneName = this.closest('.zone-item').querySelector('.zone-name').textContent;
                handleZoneAction(action, zoneName);
            });
        });
    }

    function handleZoneAction(action, zoneName) {
        if (action === 'edit') {
            if (window.VitaApp) {
                window.VitaApp.showNotification(`Editing zone: ${zoneName}`, 'info');
            }
        } else if (action === 'remove') {
            if (confirm(`Are you sure you want to remove the "${zoneName}" safe zone?`)) {
                // Find and remove the zone
                const zoneItems = document.querySelectorAll('.zone-item');
                zoneItems.forEach(item => {
                    if (item.querySelector('.zone-name').textContent === zoneName) {
                        item.remove();
                    }
                });
                
                if (window.VitaApp) {
                    window.VitaApp.showNotification(`Safe zone "${zoneName}" removed`, 'success');
                }
            }
        }
    }

    function handleSettingToggle(settingName, enabled) {
        const status = enabled ? 'enabled' : 'disabled';
        if (window.VitaApp) {
            window.VitaApp.showNotification(`${settingName} ${status}`, 'info');
        }
    }

    function handleSettingAction(settingName) {
        if (settingName === 'Emergency Contacts') {
            if (window.VitaApp) {
                window.VitaApp.showNotification('Redirecting to emergency contacts...', 'info');
            }
            setTimeout(() => {
                window.location.href = 'alerts.html';
            }, 1500);
        }
    }

    // Simulate location updates
    function updateLocationStatus() {
        const locationTime = document.querySelector('.location-time');
        if (locationTime) {
            const now = new Date();
            const timeAgo = Math.floor(Math.random() * 5) + 1; // 1-5 minutes ago
            locationTime.textContent = `Safe Zone • Last updated ${timeAgo} min ago`;
        }
    }

    // Update location every 2 minutes
    setInterval(updateLocationStatus, 120000);

    // Add smooth animations to zone items
    const zoneItems = document.querySelectorAll('.zone-item');
    zoneItems.forEach((item, index) => {
        item.style.animationDelay = `${index * 0.1}s`;
        item.classList.add('fade-in');
    });
});

// Add custom styles for location page
const locationStyles = `
    .location-card {
        margin-bottom: 2rem;
    }

    .location-content {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
    }

    .map-placeholder {
        height: 16rem;
        background: linear-gradient(135deg, rgba(74, 144, 226, 0.05), rgba(126, 214, 165, 0.1));
        border-radius: 1.5rem;
        border: 1px solid var(--vita-grey-light);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: transform 0.2s ease;
    }

    .map-content {
        text-align: center;
    }

    .map-icon {
        width: 3rem;
        height: 3rem;
        color: var(--vita-blue);
        margin: 0 auto 0.5rem;
    }

    .map-content p {
        font-size: 0.875rem;
        font-weight: 500;
        color: #374151;
        margin-bottom: 0.25rem;
    }

    .map-address {
        font-size: 0.75rem;
        color: #6b7280;
    }

    .location-details {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .current-location-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem;
        background: rgba(126, 214, 165, 0.1);
        border-radius: 1.5rem;
        border: 1px solid rgba(126, 214, 165, 0.3);
    }

    .location-icon {
        width: 1.25rem;
        height: 1.25rem;
        color: var(--vita-mint-dark);
    }

    .location-status {
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--vita-mint-dark);
        margin-bottom: 0.125rem;
    }

    .location-time {
        font-size: 0.75rem;
        color: var(--vita-mint);
    }

    .location-stats {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }

    .stat-item {
        padding: 0.75rem;
        background: var(--vita-white);
        border-radius: 1rem;
        border: 1px solid var(--vita-grey-light);
    }

    .stat-label {
        font-size: 0.75rem;
        color: #6b7280;
        margin-bottom: 0.25rem;
    }

    .stat-value {
        font-size: 0.875rem;
        font-weight: 500;
        color: #1f2937;
    }

    .safety-status {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem;
        background: rgba(74, 144, 226, 0.1);
        border-radius: 1.5rem;
        border: 1px solid rgba(74, 144, 226, 0.3);
    }

    .safety-icon {
        width: 1rem;
        height: 1rem;
        color: var(--vita-blue);
    }

    .safety-status p {
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--vita-blue-dark);
        margin: 0;
    }

    .add-zone-btn {
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--vita-blue);
        background: none;
        border: none;
        cursor: pointer;
        transition: color 0.2s ease;
    }

    .add-zone-btn:hover {
        color: var(--vita-blue-dark);
    }

    .zones-list,
    .history-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .zone-item,
    .history-item {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        padding: 1rem;
        border: 1px solid var(--vita-grey-light);
        border-radius: 1.5rem;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .zone-item:hover,
    .history-item:hover {
        background: var(--vita-grey-light);
    }

    .zone-status,
    .history-status {
        width: 0.75rem;
        height: 0.75rem;
        border-radius: 50%;
        margin-top: 0.125rem;
        flex-shrink: 0;
    }

    .zone-item.active .zone-status,
    .history-item.safe .history-status {
        background: var(--vita-mint);
    }

    .zone-info,
    .history-info {
        flex: 1;
    }

    .zone-header,
    .history-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.5rem;
    }

    .zone-name,
    .history-location {
        font-weight: 500;
        color: #1f2937;
    }

    .zone-radius,
    .history-time {
        font-size: 0.75rem;
        color: #6b7280;
    }

    .zone-address,
    .history-duration {
        font-size: 0.875rem;
        color: #6b7280;
        margin-bottom: 0.5rem;
    }

    .zone-actions {
        display: flex;
        gap: 1rem;
    }

    .zone-action-btn {
        font-size: 0.75rem;
        color: var(--vita-blue);
        background: none;
        border: none;
        cursor: pointer;
        transition: color 0.2s ease;
    }

    .zone-action-btn:hover {
        color: var(--vita-blue-dark);
    }

    .zone-action-btn.remove {
        color: var(--vita-coral);
    }

    .zone-action-btn.remove:hover {
        color: #dc2626;
    }

    .safety-settings {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
    }

    .setting-item {
        padding: 1rem;
        border: 1px solid var(--vita-grey-light);
        border-radius: 1.5rem;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .setting-item h3 {
        font-size: 0.875rem;
        font-weight: 500;
        color: #1f2937;
        margin: 0;
    }

    .setting-item p {
        font-size: 0.75rem;
        color: #6b7280;
        margin: 0;
        flex: 1;
    }

    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 3rem;
        height: 1.5rem;
        align-self: flex-start;
    }

    .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .toggle-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: var(--vita-grey);
        transition: 0.3s;
        border-radius: 1.5rem;
    }

    .toggle-slider:before {
        position: absolute;
        content: "";
        height: 1.125rem;
        width: 1.125rem;
        left: 0.1875rem;
        bottom: 0.1875rem;
        background-color: white;
        transition: 0.3s;
        border-radius: 50%;
    }

    input:checked + .toggle-slider {
        background-color: var(--vita-blue);
    }

    input:checked + .toggle-slider:before {
        transform: translateX(1.5rem);
    }

    .setting-btn {
        font-size: 0.875rem;
        color: var(--vita-blue);
        background: none;
        border: none;
        cursor: pointer;
        transition: color 0.2s ease;
        align-self: flex-start;
        padding: 0;
    }

    .setting-btn:hover {
        color: var(--vita-blue-dark);
    }

    /* Modal Styles */
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        padding: 1rem;
    }

    .modal-content {
        background: white;
        border-radius: 1.5rem;
        box-shadow: var(--shadow-soft-lg);
        width: 100%;
        max-width: 500px;
        max-height: 90vh;
        overflow-y: auto;
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.5rem 1.5rem 0;
    }

    .modal-header h3 {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1f2937;
        margin: 0;
    }

    .modal-close {
        background: none;
        border: none;
        font-size: 1.5rem;
        color: #6b7280;
        cursor: pointer;
        padding: 0.25rem;
        line-height: 1;
    }

    .modal-body {
        padding: 1.5rem;
    }

    .form-group {
        margin-bottom: 1rem;
    }

    .form-group label {
        display: block;
        font-size: 0.875rem;
        font-weight: 500;
        color: #374151;
        margin-bottom: 0.5rem;
    }

    .form-input {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid var(--vita-grey-light);
        border-radius: 1rem;
        font-size: 0.875rem;
        transition: border-color 0.2s ease;
    }

    .form-input:focus {
        outline: none;
        border-color: var(--vita-blue);
        box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.1);
    }

    .modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
        padding: 0 1.5rem 1.5rem;
    }

    .btn-secondary,
    .btn-primary {
        padding: 0.75rem 1.5rem;
        border-radius: 1rem;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        border: none;
    }

    .btn-secondary {
        background: var(--vita-grey-light);
        color: #374151;
    }

    .btn-secondary:hover {
        background: var(--vita-grey);
    }

    .btn-primary {
        background: var(--vita-blue);
        color: white;
    }

    .btn-primary:hover {
        background: var(--vita-blue-dark);
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
        .location-content {
            grid-template-columns: 1fr;
        }
        
        .location-stats {
            grid-template-columns: 1fr;
        }
        
        .safety-settings {
            grid-template-columns: 1fr;
        }
    }
`;

// Inject styles
const styleSheet = document.createElement('style');
styleSheet.textContent = locationStyles;
document.head.appendChild(styleSheet);