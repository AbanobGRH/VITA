// Device Setup Page JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Setup device buttons
    const setupBtns = document.querySelectorAll('.setup-btn');
    setupBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const deviceCard = this.closest('.device-card');
            const deviceName = deviceCard.querySelector('h3').textContent;
            startDeviceSetup(deviceName);
        });
    });

    // Device type selection buttons
    const selectDeviceBtns = document.querySelectorAll('.select-device-btn');
    selectDeviceBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const deviceTypeCard = this.closest('.device-type-card');
            const deviceType = deviceTypeCard.querySelector('h4').textContent;
            selectDeviceType(deviceType);
        });
    });

    // Configuration controls
    const configSelects = document.querySelectorAll('.config-select');
    configSelects.forEach(select => {
        select.addEventListener('change', function() {
            const configName = this.closest('.config-item').querySelector('h3').textContent;
            handleConfigChange(configName, this.value);
        });
    });

    // Configuration buttons
    const configBtns = document.querySelectorAll('.config-btn');
    configBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const configName = this.closest('.config-item').querySelector('h3').textContent;
            handleConfigAction(configName);
        });
    });

    // Configuration toggles
    const configToggles = document.querySelectorAll('.config-item .toggle-switch input');
    configToggles.forEach(toggle => {
        toggle.addEventListener('change', function() {
            const configName = this.closest('.config-item').querySelector('h3').textContent;
            handleConfigToggle(configName, this.checked);
        });
    });

    // Troubleshooting buttons
    const troubleshootBtns = document.querySelectorAll('.troubleshoot-btn');
    troubleshootBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const action = this.textContent;
            const troubleshootItem = this.closest('.troubleshoot-item');
            const issue = troubleshootItem.querySelector('h3').textContent;
            handleTroubleshootAction(action, issue);
        });
    });

    // Support buttons
    const supportBtns = document.querySelectorAll('.support-btn');
    supportBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const action = this.textContent;
            handleSupportAction(action);
        });
    });

    function startDeviceSetup(deviceName) {
        if (window.VitaApp) {
            window.VitaApp.showNotification(`Starting setup for ${deviceName}...`, 'info');
        }
        
        // Simulate device setup process
        setTimeout(() => {
            if (window.VitaApp) {
                window.VitaApp.showNotification('Searching for device...', 'info');
            }
        }, 1000);
        
        setTimeout(() => {
            if (window.VitaApp) {
                window.VitaApp.showNotification('Device found! Pairing...', 'info');
            }
        }, 3000);
        
        setTimeout(() => {
            if (window.VitaApp) {
                window.VitaApp.showNotification(`${deviceName} setup completed successfully!`, 'success');
            }
            
            // Update device status
            const deviceCards = document.querySelectorAll('.device-card');
            deviceCards.forEach(card => {
                if (card.querySelector('h3').textContent === deviceName) {
                    card.classList.remove('disconnected');
                    card.classList.add('connected');
                    
                    const statusElement = card.querySelector('.device-status');
                    if (statusElement) {
                        statusElement.textContent = 'Connected';
                        statusElement.className = 'device-status connected';
                    }
                    
                    const setupBtn = card.querySelector('.setup-btn');
                    if (setupBtn) {
                        setupBtn.remove();
                        
                        // Add battery indicator
                        const batteryDiv = document.createElement('div');
                        batteryDiv.className = 'device-battery';
                        batteryDiv.innerHTML = `
                            <div class="battery-indicator">
                                <div class="battery-bar">
                                    <div class="battery-fill" style="width: 85%"></div>
                                </div>
                                <span>85%</span>
                            </div>
                        `;
                        card.appendChild(batteryDiv);
                    }
                }
            });
        }, 5000);
    }

    function selectDeviceType(deviceType) {
        // Update wizard steps
        const steps = document.querySelectorAll('.step');
        steps[0].classList.remove('active');
        steps[1].classList.add('active');
        
        if (window.VitaApp) {
            window.VitaApp.showNotification(`Selected ${deviceType}. Starting pairing process...`, 'info');
        }
        
        // Simulate moving to next step
        setTimeout(() => {
            steps[1].classList.remove('active');
            steps[2].classList.add('active');
            
            if (window.VitaApp) {
                window.VitaApp.showNotification('Device paired! Configuring settings...', 'info');
            }
        }, 3000);
        
        setTimeout(() => {
            steps[2].classList.remove('active');
            steps[3].classList.add('active');
            
            if (window.VitaApp) {
                window.VitaApp.showNotification('Running device tests...', 'info');
            }
        }, 5000);
        
        setTimeout(() => {
            if (window.VitaApp) {
                window.VitaApp.showNotification(`${deviceType} setup completed successfully!`, 'success');
            }
            
            // Reset wizard
            setTimeout(() => {
                steps.forEach(step => step.classList.remove('active'));
                steps[0].classList.add('active');
            }, 2000);
        }, 7000);
    }

    function handleConfigChange(configName, value) {
        if (window.VitaApp) {
            window.VitaApp.showNotification(`${configName} updated to: ${value}`, 'info');
        }
        
        // Simulate applying configuration
        if (configName === 'Measurement Frequency') {
            updateMeasurementFrequency(value);
        }
    }

    function handleConfigAction(configName) {
        if (configName === 'Alert Thresholds') {
            showAlertThresholdsModal();
        }
    }

    function handleConfigToggle(configName, enabled) {
        const status = enabled ? 'enabled' : 'disabled';
        if (window.VitaApp) {
            window.VitaApp.showNotification(`${configName} ${status}`, 'info');
        }
        
        if (configName === 'Power Saving Mode' && enabled) {
            // Simulate power saving effects
            const batteryFills = document.querySelectorAll('.battery-fill');
            batteryFills.forEach(fill => {
                const currentWidth = parseInt(fill.style.width);
                fill.style.width = `${Math.min(100, currentWidth + 5)}%`;
            });
        }
    }

    function handleTroubleshootAction(action, issue) {
        if (window.VitaApp) {
            window.VitaApp.showNotification(`${action} for ${issue}...`, 'info');
        }
        
        // Simulate troubleshooting actions
        setTimeout(() => {
            switch(action) {
                case 'Restart Device':
                    if (window.VitaApp) {
                        window.VitaApp.showNotification('Device restarted successfully', 'success');
                    }
                    break;
                case 'Reset Connection':
                    if (window.VitaApp) {
                        window.VitaApp.showNotification('Connection reset and re-established', 'success');
                    }
                    break;
                case 'Check Settings':
                    if (window.VitaApp) {
                        window.VitaApp.showNotification('Settings optimized for better battery life', 'success');
                    }
                    break;
                case 'Calibrate Battery':
                    if (window.VitaApp) {
                        window.VitaApp.showNotification('Battery calibration completed', 'success');
                    }
                    break;
                case 'Calibrate Sensors':
                    if (window.VitaApp) {
                        window.VitaApp.showNotification('Sensors calibrated successfully', 'success');
                    }
                    break;
                case 'Contact Support':
                    if (window.VitaApp) {
                        window.VitaApp.showNotification('Connecting to technical support...', 'info');
                    }
                    break;
            }
        }, 2000);
    }

    function handleSupportAction(action) {
        switch(action) {
            case 'Download PDF':
                if (window.VitaApp) {
                    window.VitaApp.showNotification('Downloading user manual...', 'info');
                }
                setTimeout(() => {
                    if (window.VitaApp) {
                        window.VitaApp.showNotification('User manual downloaded successfully', 'success');
                    }
                }, 2000);
                break;
            case 'Watch Videos':
                if (window.VitaApp) {
                    window.VitaApp.showNotification('Opening video tutorials...', 'info');
                }
                break;
            case 'Contact Support':
                if (window.VitaApp) {
                    window.VitaApp.showNotification('Connecting to technical support...', 'info');
                }
                break;
            case 'Visit Forum':
                if (window.VitaApp) {
                    window.VitaApp.showNotification('Opening community forum...', 'info');
                }
                break;
        }
    }

    function showAlertThresholdsModal() {
        const modalOverlay = document.createElement('div');
        modalOverlay.className = 'modal-overlay';
        modalOverlay.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Configure Alert Thresholds</h3>
                    <button class="modal-close">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="threshold-section">
                        <h4>Heart Rate</h4>
                        <div class="threshold-inputs">
                            <div class="form-group">
                                <label>Low Threshold (bpm)</label>
                                <input type="number" value="50" min="30" max="100" class="form-input">
                            </div>
                            <div class="form-group">
                                <label>High Threshold (bpm)</label>
                                <input type="number" value="120" min="80" max="200" class="form-input">
                            </div>
                        </div>
                    </div>
                    
                    <div class="threshold-section">
                        <h4>Blood Pressure</h4>
                        <div class="threshold-inputs">
                            <div class="form-group">
                                <label>Systolic High (mmHg)</label>
                                <input type="number" value="140" min="100" max="200" class="form-input">
                            </div>
                            <div class="form-group">
                                <label>Diastolic High (mmHg)</label>
                                <input type="number" value="90" min="60" max="120" class="form-input">
                            </div>
                        </div>
                    </div>
                    
                    <div class="threshold-section">
                        <h4>Blood Glucose</h4>
                        <div class="threshold-inputs">
                            <div class="form-group">
                                <label>Low Threshold (mg/dL)</label>
                                <input type="number" value="70" min="40" max="100" class="form-input">
                            </div>
                            <div class="form-group">
                                <label>High Threshold (mg/dL)</label>
                                <input type="number" value="180" min="120" max="300" class="form-input">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn-secondary modal-cancel">Cancel</button>
                    <button class="btn-primary modal-save">Save Thresholds</button>
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
            closeModal();
            if (window.VitaApp) {
                window.VitaApp.showNotification('Alert thresholds updated successfully', 'success');
            }
        });

        setTimeout(() => {
            modalOverlay.querySelector('.form-input').focus();
        }, 100);
    }

    function updateMeasurementFrequency(frequency) {
        // Simulate updating device measurement frequency
        const deviceCards = document.querySelectorAll('.device-card.connected');
        deviceCards.forEach(card => {
            const deviceName = card.querySelector('h3').textContent;
            
            // Add visual indicator of frequency change
            const indicator = document.createElement('div');
            indicator.className = 'frequency-indicator';
            indicator.textContent = frequency;
            indicator.style.cssText = `
                position: absolute;
                top: 0.5rem;
                right: 0.5rem;
                background: var(--vita-blue);
                color: white;
                font-size: 0.625rem;
                padding: 0.25rem 0.5rem;
                border-radius: 0.75rem;
                z-index: 1;
            `;
            
            card.style.position = 'relative';
            
            // Remove existing indicator
            const existingIndicator = card.querySelector('.frequency-indicator');
            if (existingIndicator) {
                existingIndicator.remove();
            }
            
            card.appendChild(indicator);
            
            // Remove indicator after 3 seconds
            setTimeout(() => {
                indicator.remove();
            }, 3000);
        });
    }

    // Simulate device status updates
    function simulateDeviceUpdates() {
        const batteryFills = document.querySelectorAll('.battery-fill');
        batteryFills.forEach(fill => {
            // Simulate slow battery drain
            if (Math.random() < 0.1) { // 10% chance
                const currentWidth = parseInt(fill.style.width);
                const newWidth = Math.max(10, currentWidth - 1);
                fill.style.width = `${newWidth}%`;
                
                const batteryText = fill.parentElement.nextElementSibling;
                if (batteryText) {
                    batteryText.textContent = `${newWidth}%`;
                }
                
                // Alert if battery is low
                if (newWidth <= 20 && Math.random() < 0.3) {
                    const deviceCard = fill.closest('.device-card');
                    const deviceName = deviceCard.querySelector('h3').textContent;
                    
                    if (window.VitaApp) {
                        window.VitaApp.showNotification(`${deviceName} battery low (${newWidth}%)`, 'error');
                    }
                }
            }
        });
    }

    // Update device status every 2 minutes
    setInterval(simulateDeviceUpdates, 120000);

    // Add smooth animations to device cards
    const deviceCards = document.querySelectorAll('.device-card');
    deviceCards.forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
        card.classList.add('fade-in');
    });

    // Add smooth animations to other elements
    const configItems = document.querySelectorAll('.config-item');
    configItems.forEach((item, index) => {
        item.style.animationDelay = `${index * 0.1}s`;
        item.classList.add('fade-in');
    });
});

// Add custom styles for device setup page
const deviceSetupStyles = `
    .device-overview {
        margin-bottom: 2rem;
    }

    .device-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
    }

    .device-card {
        padding: 1.5rem;
        border-radius: 1.5rem;
        border: 1px solid var(--vita-grey-light);
        transition: all 0.3s ease;
        position: relative;
    }

    .device-card.connected {
        background: rgba(126, 214, 165, 0.05);
        border-color: rgba(126, 214, 165, 0.3);
    }

    .device-card.disconnected {
        background: rgba(231, 76, 60, 0.05);
        border-color: rgba(231, 76, 60, 0.3);
    }

    .device-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-soft-lg);
    }

    .device-icon {
        width: 3rem;
        height: 3rem;
        background: var(--vita-blue);
        border-radius: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem;
    }

    .device-icon svg {
        width: 1.5rem;
        height: 1.5rem;
        color: white;
    }

    .device-info h3 {
        font-size: 1rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 0.25rem;
    }

    .device-info p {
        font-size: 0.875rem;
        color: #6b7280;
        margin-bottom: 0.75rem;
    }

    .device-status {
        font-size: 0.75rem;
        font-weight: 500;
        padding: 0.25rem 0.75rem;
        border-radius: 1rem;
        display: inline-block;
    }

    .device-status.connected {
        background: rgba(126, 214, 165, 0.2);
        color: var(--vita-mint-dark);
    }

    .device-status.disconnected {
        background: rgba(231, 76, 60, 0.2);
        color: var(--vita-coral);
    }

    .device-battery {
        margin-top: 1rem;
    }

    .setup-btn {
        margin-top: 1rem;
        padding: 0.5rem 1rem;
        background: var(--vita-blue);
        color: white;
        border: none;
        border-radius: 1rem;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .setup-btn:hover {
        background: var(--vita-blue-dark);
    }

    .config-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .config-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem;
        background: var(--vita-white);
        border-radius: 1rem;
        border: 1px solid var(--vita-grey-light);
    }

    .config-info {
        flex: 1;
    }

    .config-info h3 {
        font-size: 0.875rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 0.25rem;
    }

    .config-info p {
        font-size: 0.75rem;
        color: #6b7280;
        margin: 0;
    }

    .config-select {
        padding: 0.5rem 0.75rem;
        border: 1px solid var(--vita-grey-light);
        border-radius: 1rem;
        font-size: 0.875rem;
        background: white;
        cursor: pointer;
    }

    .config-btn {
        padding: 0.5rem 1rem;
        background: var(--vita-blue);
        color: white;
        border: none;
        border-radius: 1rem;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .config-btn:hover {
        background: var(--vita-blue-dark);
    }

    .troubleshoot-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .troubleshoot-item {
        padding: 1rem;
        background: var(--vita-white);
        border-radius: 1rem;
        border: 1px solid var(--vita-grey-light);
    }

    .troubleshoot-item h3 {
        font-size: 0.875rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 0.5rem;
    }

    .troubleshoot-item p {
        font-size: 0.75rem;
        color: #6b7280;
        margin-bottom: 0.75rem;
    }

    .troubleshoot-actions {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .troubleshoot-btn {
        padding: 0.5rem 0.75rem;
        background: var(--vita-grey-light);
        color: #374151;
        border: none;
        border-radius: 1rem;
        font-size: 0.75rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .troubleshoot-btn:hover {
        background: var(--vita-grey);
    }

    .setup-wizard {
        display: flex;
        flex-direction: column;
        gap: 2rem;
    }

    .wizard-steps {
        display: flex;
        justify-content: center;
        gap: 2rem;
        margin-bottom: 2rem;
    }

    .step {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
        opacity: 0.5;
        transition: opacity 0.3s ease;
    }

    .step.active {
        opacity: 1;
    }

    .step-number {
        width: 2rem;
        height: 2rem;
        background: var(--vita-grey-light);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.875rem;
        font-weight: 600;
        color: #6b7280;
        transition: all 0.3s ease;
    }

    .step.active .step-number {
        background: var(--vita-blue);
        color: white;
    }

    .step span {
        font-size: 0.75rem;
        color: #6b7280;
        text-align: center;
    }

    .step.active span {
        color: #1f2937;
        font-weight: 500;
    }

    .device-selection h3 {
        font-size: 1rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 1.5rem;
        text-align: center;
    }

    .device-types {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }

    .device-type-card {
        padding: 1.5rem;
        background: var(--vita-white);
        border: 1px solid var(--vita-grey-light);
        border-radius: 1.5rem;
        text-align: center;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .device-type-card:hover {
        background: var(--vita-grey-light);
        transform: translateY(-2px);
    }

    .device-type-icon {
        width: 3rem;
        height: 3rem;
        background: var(--vita-blue);
        border-radius: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
    }

    .device-type-icon svg {
        width: 1.5rem;
        height: 1.5rem;
        color: white;
    }

    .device-type-card h4 {
        font-size: 0.875rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 0.5rem;
    }

    .device-type-card p {
        font-size: 0.75rem;
        color: #6b7280;
        margin-bottom: 1rem;
    }

    .select-device-btn {
        padding: 0.5rem 1rem;
        background: var(--vita-blue);
        color: white;
        border: none;
        border-radius: 1rem;
        font-size: 0.75rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .select-device-btn:hover {
        background: var(--vita-blue-dark);
    }

    .support-options {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1rem;
    }

    .support-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        background: var(--vita-white);
        border: 1px solid var(--vita-grey-light);
        border-radius: 1rem;
        transition: all 0.2s ease;
    }

    .support-item:hover {
        background: var(--vita-grey-light);
    }

    .support-icon {
        width: 2.5rem;
        height: 2.5rem;
        background: var(--vita-blue);
        border-radius: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .support-icon svg {
        width: 1.25rem;
        height: 1.25rem;
        color: white;
    }

    .support-info {
        flex: 1;
    }

    .support-info h3 {
        font-size: 0.875rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 0.25rem;
    }

    .support-info p {
        font-size: 0.75rem;
        color: #6b7280;
        margin: 0;
    }

    .support-btn {
        padding: 0.5rem 0.75rem;
        background: var(--vita-grey-light);
        color: #374151;
        border: none;
        border-radius: 1rem;
        font-size: 0.75rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
    }

    .support-btn:hover {
        background: var(--vita-grey);
    }

    .threshold-section {
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid var(--vita-grey-light);
    }

    .threshold-section:last-child {
        border-bottom: none;
        margin-bottom: 0;
    }

    .threshold-section h4 {
        font-size: 0.875rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 1rem;
    }

    .threshold-inputs {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
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
        .device-grid {
            grid-template-columns: 1fr;
        }
        
        .wizard-steps {
            gap: 1rem;
        }
        
        .device-types {
            grid-template-columns: 1fr;
        }
        
        .support-options {
            grid-template-columns: 1fr;
        }
        
        .threshold-inputs {
            grid-template-columns: 1fr;
        }
        
        .troubleshoot-actions {
            flex-direction: column;
        }
    }
`;

// Inject styles
const styleSheet = document.createElement('style');
styleSheet.textContent = deviceSetupStyles;
document.head.appendChild(styleSheet);