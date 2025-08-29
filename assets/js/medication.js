// Medication Page JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Add medication button functionality
    const addMedicationBtn = document.querySelector('.add-medication-btn');
    if (addMedicationBtn) {
        addMedicationBtn.addEventListener('click', function() {
            showAddMedicationModal();
        });
    }

    // Medication action buttons
    const medActionBtns = document.querySelectorAll('.med-action-btn');
    medActionBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const action = this.classList.contains('edit') ? 'edit' : 'refill';
            const medName = this.closest('.medication-item').querySelector('h3').textContent;
            handleMedicationAction(action, medName);
        });
    });

    // Schedule status buttons
    const scheduleItems = document.querySelectorAll('.schedule-item');
    scheduleItems.forEach(item => {
        const statusBtn = item.querySelector('.schedule-status');
        if (statusBtn && statusBtn.classList.contains('pending')) {
            statusBtn.addEventListener('click', function() {
                markAsTaken(item);
            });
        }
    });

    // Refill buttons
    const refillBtns = document.querySelectorAll('.refill-btn');
    refillBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const medName = this.closest('.refill-item').querySelector('h3').textContent;
            handleRefillOrder(medName);
        });
    });

    function showAddMedicationModal() {
        const modalOverlay = document.createElement('div');
        modalOverlay.className = 'modal-overlay';
        modalOverlay.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Add New Medication</h3>
                    <button class="modal-close">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Medication Name</label>
                        <input type="text" placeholder="e.g., Lisinopril" class="form-input">
                    </div>
                    <div class="form-group">
                        <label>Dosage</label>
                        <input type="text" placeholder="e.g., 10mg" class="form-input">
                    </div>
                    <div class="form-group">
                        <label>Frequency</label>
                        <select class="form-input">
                            <option>Once daily</option>
                            <option>Twice daily</option>
                            <option>Three times daily</option>
                            <option>As needed</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Time(s)</label>
                        <input type="time" value="08:00" class="form-input">
                    </div>
                    <div class="form-group">
                        <label>Condition</label>
                        <input type="text" placeholder="e.g., Blood Pressure" class="form-input">
                    </div>
                    <div class="form-group">
                        <label>Instructions</label>
                        <textarea placeholder="Take with food..." class="form-input"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn-secondary modal-cancel">Cancel</button>
                    <button class="btn-primary modal-save">Add Medication</button>
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
            const name = modalOverlay.querySelector('input[placeholder*="Lisinopril"]').value;
            const dosage = modalOverlay.querySelector('input[placeholder*="10mg"]').value;
            const frequency = modalOverlay.querySelector('select').value;
            const condition = modalOverlay.querySelector('input[placeholder*="Blood Pressure"]').value;

            if (name && dosage) {
                addNewMedication(name, dosage, frequency, condition);
                closeModal();
                if (window.VitaApp) {
                    window.VitaApp.showNotification(`${name} added to medication list`, 'success');
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

    function addNewMedication(name, dosage, frequency, condition) {
        const medicationsList = document.querySelector('.medications-list');
        const newMedication = document.createElement('div');
        newMedication.className = 'medication-item';
        
        const conditionClass = condition.toLowerCase().includes('blood') ? 'blood-pressure' : 
                              condition.toLowerCase().includes('diabetes') ? 'glucose' : 'cholesterol';
        
        newMedication.innerHTML = `
            <div class="medication-icon ${conditionClass}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4.5 16.5c-1.5 1.5-1.5 3.5 0 5s3.5 1.5 5 0l12-12c1.5-1.5 1.5-3.5 0-5s-3.5-1.5-5 0l-12 12z"></path>
                </svg>
            </div>
            <div class="medication-details">
                <h3>${name}</h3>
                <p>${dosage} • ${frequency}</p>
                <span class="medication-condition">${condition}</span>
            </div>
            <div class="medication-actions">
                <button class="med-action-btn edit">Edit</button>
                <button class="med-action-btn refill">Refill</button>
            </div>
        `;

        medicationsList.appendChild(newMedication);

        // Add event listeners to new medication
        const actionBtns = newMedication.querySelectorAll('.med-action-btn');
        actionBtns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const action = this.classList.contains('edit') ? 'edit' : 'refill';
                const medName = this.closest('.medication-item').querySelector('h3').textContent;
                handleMedicationAction(action, medName);
            });
        });
    }

    function handleMedicationAction(action, medName) {
        if (action === 'edit') {
            if (window.VitaApp) {
                window.VitaApp.showNotification(`Editing ${medName}`, 'info');
            }
        } else if (action === 'refill') {
            handleRefillOrder(medName);
        }
    }

    function handleRefillOrder(medName) {
        if (window.VitaApp) {
            window.VitaApp.showNotification(`Refill order placed for ${medName}`, 'success');
        }
        
        // Simulate removing from refill reminders
        setTimeout(() => {
            const refillItems = document.querySelectorAll('.refill-item');
            refillItems.forEach(item => {
                if (item.querySelector('h3').textContent === medName) {
                    item.style.opacity = '0.5';
                    item.querySelector('.refill-btn').textContent = 'Ordered';
                    item.querySelector('.refill-btn').disabled = true;
                }
            });
        }, 1000);
    }

    function markAsTaken(scheduleItem) {
        const medName = scheduleItem.querySelector('h3').textContent;
        
        // Update visual state
        scheduleItem.classList.remove('upcoming');
        scheduleItem.classList.add('completed');
        
        const statusElement = scheduleItem.querySelector('.schedule-status');
        statusElement.classList.remove('pending');
        statusElement.classList.add('taken');
        statusElement.innerHTML = `
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="20,6 9,17 4,12"></polyline>
            </svg>
            <span>Taken</span>
        `;
        
        if (window.VitaApp) {
            window.VitaApp.showNotification(`${medName} marked as taken`, 'success');
        }
        
        // Update adherence score
        updateAdherenceScore();
    }

    function updateAdherenceScore() {
        const scoreElement = document.querySelector('.score-value');
        if (scoreElement) {
            let currentScore = parseInt(scoreElement.textContent);
            const newScore = Math.min(100, currentScore + 2);
            scoreElement.textContent = `${newScore}%`;
        }
    }

    // Simulate medication reminders
    function simulateMedicationReminder() {
        const upcomingItems = document.querySelectorAll('.schedule-item.upcoming');
        if (upcomingItems.length > 0) {
            const randomItem = upcomingItems[Math.floor(Math.random() * upcomingItems.length)];
            const medName = randomItem.querySelector('h3').textContent;
            const time = randomItem.querySelector('.schedule-time').textContent;
            
            if (window.VitaApp) {
                window.VitaApp.showNotification(`Reminder: Time to take ${medName} (${time})`, 'info');
            }
        }
    }

    // Set up periodic reminders (every 5 minutes for demo)
    setInterval(simulateMedicationReminder, 300000);

    // Add smooth animations to medication items
    const medicationItems = document.querySelectorAll('.medication-item');
    medicationItems.forEach((item, index) => {
        item.style.animationDelay = `${index * 0.1}s`;
        item.classList.add('fade-in');
    });
});

// Add custom styles for medication page
const medicationStyles = `
    .add-medication-btn {
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--vita-blue);
        background: none;
        border: none;
        cursor: pointer;
        transition: color 0.2s ease;
    }

    .add-medication-btn:hover {
        color: var(--vita-blue-dark);
    }

    .schedule-timeline {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .schedule-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        border-radius: 1.5rem;
        border: 1px solid var(--vita-grey-light);
        transition: all 0.2s ease;
    }

    .schedule-item.completed {
        background: rgba(126, 214, 165, 0.1);
        border-color: rgba(126, 214, 165, 0.3);
    }

    .schedule-item.upcoming {
        background: rgba(74, 144, 226, 0.05);
        border-color: rgba(74, 144, 226, 0.2);
    }

    .schedule-time {
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--vita-blue-dark);
        min-width: 4rem;
    }

    .schedule-content {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex: 1;
    }

    .medication-info h3 {
        font-size: 1rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 0.25rem;
    }

    .medication-info p {
        font-size: 0.875rem;
        color: #6b7280;
        margin: 0;
    }

    .schedule-status {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
        font-weight: 500;
        padding: 0.5rem 1rem;
        border-radius: 1rem;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .schedule-status.taken {
        background: rgba(126, 214, 165, 0.2);
        color: var(--vita-mint-dark);
    }

    .schedule-status.pending {
        background: rgba(74, 144, 226, 0.1);
        color: var(--vita-blue);
    }

    .schedule-status.pending:hover {
        background: rgba(74, 144, 226, 0.2);
    }

    .schedule-status svg {
        width: 1rem;
        height: 1rem;
    }

    .medications-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .medication-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        border: 1px solid var(--vita-grey-light);
        border-radius: 1.5rem;
        transition: all 0.2s ease;
    }

    .medication-item:hover {
        background: var(--vita-grey-light);
    }

    .medication-icon {
        width: 3rem;
        height: 3rem;
        border-radius: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .medication-icon.blood-pressure {
        background: rgba(74, 144, 226, 0.1);
        color: var(--vita-blue);
    }

    .medication-icon.glucose {
        background: var(--orange-50);
        color: var(--orange-500);
    }

    .medication-icon.cholesterol {
        background: rgba(231, 76, 60, 0.1);
        color: var(--vita-coral);
    }

    .medication-icon svg {
        width: 1.5rem;
        height: 1.5rem;
    }

    .medication-details {
        flex: 1;
    }

    .medication-details h3 {
        font-size: 1rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 0.25rem;
    }

    .medication-details p {
        font-size: 0.875rem;
        color: #6b7280;
        margin-bottom: 0.5rem;
    }

    .medication-condition {
        font-size: 0.75rem;
        font-weight: 500;
        color: var(--vita-blue);
        background: rgba(74, 144, 226, 0.1);
        padding: 0.25rem 0.5rem;
        border-radius: 0.75rem;
    }

    .medication-actions {
        display: flex;
        gap: 1rem;
    }

    .med-action-btn {
        font-size: 0.75rem;
        color: var(--vita-blue);
        background: none;
        border: none;
        cursor: pointer;
        transition: color 0.2s ease;
    }

    .med-action-btn:hover {
        color: var(--vita-blue-dark);
    }

    .adherence-overview {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 1.5rem;
    }

    .adherence-score {
        text-align: center;
    }

    .score-circle {
        width: 6rem;
        height: 6rem;
        border-radius: 50%;
        background: conic-gradient(var(--vita-mint) 0deg 338deg, var(--vita-grey-light) 338deg 360deg);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 0.5rem;
        position: relative;
    }

    .score-circle::before {
        content: '';
        position: absolute;
        width: 4rem;
        height: 4rem;
        background: white;
        border-radius: 50%;
    }

    .score-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--vita-mint-dark);
        position: relative;
        z-index: 1;
    }

    .adherence-score p {
        font-size: 0.875rem;
        color: #6b7280;
        margin: 0;
    }

    .adherence-stats {
        width: 100%;
    }

    .stat-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.5rem 0;
        border-bottom: 1px solid var(--vita-grey-light);
        font-size: 0.875rem;
    }

    .stat-row:last-child {
        border-bottom: none;
    }

    .stat-value {
        font-weight: 600;
        color: #1f2937;
    }

    .stat-value.good {
        color: var(--vita-mint-dark);
    }

    .refill-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .refill-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        border-radius: 1.5rem;
        border: 1px solid;
    }

    .refill-item.urgent {
        background: rgba(231, 76, 60, 0.1);
        border-color: rgba(231, 76, 60, 0.3);
    }

    .refill-item.warning {
        background: var(--orange-50);
        border-color: rgba(249, 115, 22, 0.3);
    }

    .refill-indicator {
        width: 0.75rem;
        height: 0.75rem;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .refill-item.urgent .refill-indicator {
        background: var(--vita-coral);
    }

    .refill-item.warning .refill-indicator {
        background: var(--orange-500);
    }

    .refill-info {
        flex: 1;
    }

    .refill-info h3 {
        font-size: 0.875rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 0.25rem;
    }

    .refill-info p {
        font-size: 0.75rem;
        color: #6b7280;
        margin: 0;
    }

    .refill-btn {
        padding: 0.5rem 1rem;
        border-radius: 1rem;
        font-size: 0.75rem;
        font-weight: 500;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .refill-btn {
        background: var(--vita-blue);
        color: white;
    }

    .refill-btn:hover {
        background: var(--vita-blue-dark);
    }

    .refill-btn.urgent {
        background: var(--vita-coral);
        color: white;
    }

    .refill-btn.urgent:hover {
        background: #dc2626;
    }

    .refill-btn:disabled {
        background: var(--vita-grey);
        color: #6b7280;
        cursor: not-allowed;
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
        .schedule-content {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }
        
        .medication-item {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }
        
        .medication-actions {
            align-self: flex-end;
        }
        
        .adherence-overview {
            flex-direction: column;
        }
    }
`;

// Inject styles
const styleSheet = document.createElement('style');
styleSheet.textContent = medicationStyles;
document.head.appendChild(styleSheet);