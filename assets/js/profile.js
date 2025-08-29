// Profile Page JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Edit profile button
    const editProfileBtn = document.querySelector('.edit-profile-btn');
    if (editProfileBtn) {
        editProfileBtn.addEventListener('click', function() {
            toggleProfileEdit();
        });
    }

    // Edit buttons for sections
    const editBtns = document.querySelectorAll('.edit-btn');
    editBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const section = this.closest('.card');
            toggleSectionEdit(section);
        });
    });

    // Toggle switches for preferences
    const toggleSwitches = document.querySelectorAll('.toggle-switch input');
    toggleSwitches.forEach(toggle => {
        toggle.addEventListener('change', function() {
            const settingName = this.closest('.preference-item, .privacy-item').querySelector('span').textContent;
            handlePreferenceToggle(settingName, this.checked);
        });
    });

    // Security buttons
    const securityBtns = document.querySelectorAll('.security-btn');
    securityBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const action = this.textContent;
            handleSecurityAction(action);
        });
    });

    // Data management buttons
    const dataBtns = document.querySelectorAll('.data-btn');
    dataBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const action = this.textContent;
            handleDataAction(action);
        });
    });

    // Add family member button
    const addFamilyBtn = document.querySelector('.add-family-btn');
    if (addFamilyBtn) {
        addFamilyBtn.addEventListener('click', function() {
            showAddFamilyModal();
        });
    }

    // Family member action buttons
    const memberActionBtns = document.querySelectorAll('.member-action-btn');
    memberActionBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const action = this.textContent.toLowerCase();
            const memberItem = this.closest('.family-member');
            handleFamilyMemberAction(action, memberItem);
        });
    });

    function toggleProfileEdit() {
        const profileInfo = document.querySelector('.profile-info');
        const editBtn = document.querySelector('.edit-profile-btn');
        
        if (editBtn.textContent === 'Edit Profile') {
            // Enter edit mode
            editBtn.textContent = 'Save Changes';
            editBtn.style.background = 'var(--vita-mint)';
            
            if (window.VitaApp) {
                window.VitaApp.showNotification('Profile editing enabled', 'info');
            }
        } else {
            // Save changes
            editBtn.textContent = 'Edit Profile';
            editBtn.style.background = '';
            
            if (window.VitaApp) {
                window.VitaApp.showNotification('Profile changes saved', 'success');
            }
        }
    }

    function toggleSectionEdit(section) {
        const editBtn = section.querySelector('.edit-btn');
        const inputs = section.querySelectorAll('input, textarea, select');
        
        if (editBtn.textContent === 'Edit') {
            // Enter edit mode
            editBtn.textContent = 'Save';
            inputs.forEach(input => {
                input.removeAttribute('readonly');
                input.style.background = 'white';
                input.style.border = '1px solid var(--vita-blue)';
            });
            
            if (window.VitaApp) {
                window.VitaApp.showNotification('Section editing enabled', 'info');
            }
        } else {
            // Save changes
            editBtn.textContent = 'Edit';
            inputs.forEach(input => {
                input.setAttribute('readonly', true);
                input.style.background = '';
                input.style.border = '';
            });
            
            if (window.VitaApp) {
                window.VitaApp.showNotification('Changes saved successfully', 'success');
            }
        }
    }

    function handlePreferenceToggle(settingName, enabled) {
        const status = enabled ? 'enabled' : 'disabled';
        
        // Apply visual changes based on setting
        if (settingName === 'Dark Mode' && enabled) {
            document.body.style.filter = 'invert(0.1)';
            setTimeout(() => {
                document.body.style.filter = '';
            }, 2000);
        }
        
        if (settingName === 'Large Text' && enabled) {
            document.body.style.fontSize = '1.1em';
            setTimeout(() => {
                document.body.style.fontSize = '';
            }, 2000);
        }
        
        if (window.VitaApp) {
            window.VitaApp.showNotification(`${settingName} ${status}`, 'info');
        }
    }

    function handleSecurityAction(action) {
        switch(action) {
            case 'Change Password':
                showPasswordChangeModal();
                break;
            case 'Two-Factor Authentication':
                if (window.VitaApp) {
                    window.VitaApp.showNotification('Two-factor authentication setup initiated', 'info');
                }
                break;
        }
    }

    function handleDataAction(action) {
        switch(action) {
            case 'Export My Data':
                if (window.VitaApp) {
                    window.VitaApp.showNotification('Preparing data export...', 'info');
                }
                setTimeout(() => {
                    if (window.VitaApp) {
                        window.VitaApp.showNotification('Data export ready for download', 'success');
                    }
                }, 3000);
                break;
            case 'Delete Account':
                const confirmed = confirm('Are you sure you want to delete your account? This action cannot be undone.');
                if (confirmed) {
                    if (window.VitaApp) {
                        window.VitaApp.showNotification('Account deletion process initiated', 'error');
                    }
                }
                break;
        }
    }

    function showPasswordChangeModal() {
        const modalOverlay = document.createElement('div');
        modalOverlay.className = 'modal-overlay';
        modalOverlay.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Change Password</h3>
                    <button class="modal-close">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Current Password</label>
                        <input type="password" placeholder="Enter current password" class="form-input">
                    </div>
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" placeholder="Enter new password" class="form-input">
                    </div>
                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="password" placeholder="Confirm new password" class="form-input">
                    </div>
                    <div class="password-requirements">
                        <p>Password must contain:</p>
                        <ul>
                            <li>At least 8 characters</li>
                            <li>One uppercase letter</li>
                            <li>One lowercase letter</li>
                            <li>One number</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn-secondary modal-cancel">Cancel</button>
                    <button class="btn-primary modal-save">Change Password</button>
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
            const currentPassword = modalOverlay.querySelector('input[placeholder*="current"]').value;
            const newPassword = modalOverlay.querySelector('input[placeholder*="new password"]').value;
            const confirmPassword = modalOverlay.querySelector('input[placeholder*="Confirm"]').value;

            if (currentPassword && newPassword && confirmPassword) {
                if (newPassword === confirmPassword) {
                    closeModal();
                    if (window.VitaApp) {
                        window.VitaApp.showNotification('Password changed successfully', 'success');
                    }
                } else {
                    if (window.VitaApp) {
                        window.VitaApp.showNotification('Passwords do not match', 'error');
                    }
                }
            } else {
                if (window.VitaApp) {
                    window.VitaApp.showNotification('Please fill in all fields', 'error');
                }
            }
        });

        setTimeout(() => {
            modalOverlay.querySelector('.form-input').focus();
        }, 100);
    }

    function showAddFamilyModal() {
        const modalOverlay = document.createElement('div');
        modalOverlay.className = 'modal-overlay';
        modalOverlay.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Add Family Member</h3>
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
                            <option>Son</option>
                            <option>Daughter</option>
                            <option>Spouse</option>
                            <option>Sibling</option>
                            <option>Grandchild</option>
                            <option>Other Family</option>
                            <option>Healthcare Provider</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" placeholder="Enter email address" class="form-input">
                    </div>
                    <div class="form-group">
                        <label>Access Level</label>
                        <select class="form-input">
                            <option>Full Access</option>
                            <option>Health Data Only</option>
                            <option>Emergency Only</option>
                            <option>Medical Access</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Permissions</label>
                        <div class="permissions-list">
                            <label class="permission-item">
                                <input type="checkbox" checked>
                                <span>Health Data</span>
                            </label>
                            <label class="permission-item">
                                <input type="checkbox" checked>
                                <span>Location</span>
                            </label>
                            <label class="permission-item">
                                <input type="checkbox" checked>
                                <span>Alerts</span>
                            </label>
                            <label class="permission-item">
                                <input type="checkbox">
                                <span>Medical Records</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn-secondary modal-cancel">Cancel</button>
                    <button class="btn-primary modal-save">Add Family Member</button>
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
            const email = modalOverlay.querySelector('input[type="email"]').value;
            const accessLevel = modalOverlay.querySelectorAll('select')[1].value;

            if (name && email) {
                addNewFamilyMember(name, relationship, email, accessLevel);
                closeModal();
                if (window.VitaApp) {
                    window.VitaApp.showNotification(`${name} added to family access`, 'success');
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

    function addNewFamilyMember(name, relationship, email, accessLevel) {
        const familyList = document.querySelector('.family-list');
        const newMember = document.createElement('div');
        newMember.className = 'family-member';
        newMember.innerHTML = `
            <div class="member-avatar">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
            </div>
            <div class="member-info">
                <h3>${name}</h3>
                <p>${relationship} • ${accessLevel}</p>
                <span class="member-email">${email}</span>
            </div>
            <div class="member-permissions">
                <span class="permission-tag">Health Data</span>
                <span class="permission-tag">Location</span>
                <span class="permission-tag">Alerts</span>
            </div>
            <div class="member-actions">
                <button class="member-action-btn">Edit</button>
                <button class="member-action-btn">Remove</button>
            </div>
        `;

        familyList.appendChild(newMember);

        // Add event listeners to new member
        const actionBtns = newMember.querySelectorAll('.member-action-btn');
        actionBtns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const action = this.textContent.toLowerCase();
                const memberItem = this.closest('.family-member');
                handleFamilyMemberAction(action, memberItem);
            });
        });
    }

    function handleFamilyMemberAction(action, memberItem) {
        const memberName = memberItem.querySelector('h3').textContent;
        
        if (action === 'edit') {
            if (window.VitaApp) {
                window.VitaApp.showNotification(`Editing access for ${memberName}`, 'info');
            }
        } else if (action === 'remove') {
            const confirmed = confirm(`Are you sure you want to remove ${memberName} from family access?`);
            if (confirmed) {
                memberItem.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    memberItem.remove();
                }, 300);
                
                if (window.VitaApp) {
                    window.VitaApp.showNotification(`${memberName} removed from family access`, 'success');
                }
            }
        }
    }

    // Add smooth animations to profile elements
    const profileElements = document.querySelectorAll('.profile-overview, .card');
    profileElements.forEach((element, index) => {
        element.style.animationDelay = `${index * 0.1}s`;
        element.classList.add('fade-in');
    });
});

// Add custom styles for profile page
const profileStyles = `
    .profile-overview {
        margin-bottom: 2rem;
    }

    .profile-header {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .profile-avatar {
        width: 5rem;
        height: 5rem;
        background: var(--vita-blue);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .profile-avatar svg {
        width: 2.5rem;
        height: 2.5rem;
        color: white;
    }

    .profile-info {
        flex: 1;
    }

    .profile-info h2 {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 0.25rem;
    }

    .profile-info p {
        font-size: 0.875rem;
        color: #6b7280;
        margin-bottom: 0.5rem;
    }

    .profile-status {
        font-size: 0.75rem;
        font-weight: 500;
        color: var(--vita-mint-dark);
        background: rgba(126, 214, 165, 0.1);
        padding: 0.25rem 0.75rem;
        border-radius: 1rem;
        display: inline-block;
    }

    .profile-status.active {
        color: var(--vita-mint-dark);
        background: rgba(126, 214, 165, 0.1);
    }

    .edit-profile-btn {
        padding: 0.75rem 1.5rem;
        background: var(--vita-blue);
        color: white;
        border: none;
        border-radius: 1rem;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .edit-profile-btn:hover {
        background: var(--vita-blue-dark);
    }

    .profile-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }

    .stat-item {
        padding: 1rem;
        background: var(--vita-white);
        border-radius: 1rem;
        border: 1px solid var(--vita-grey-light);
        text-align: center;
    }

    .stat-label {
        font-size: 0.75rem;
        color: #6b7280;
        margin-bottom: 0.25rem;
        display: block;
    }

    .stat-value {
        font-size: 0.875rem;
        font-weight: 600;
        color: #1f2937;
    }

    .edit-btn {
        font-size: 0.875rem;
        color: var(--vita-blue);
        background: none;
        border: none;
        cursor: pointer;
        transition: color 0.2s ease;
    }

    .edit-btn:hover {
        color: var(--vita-blue-dark);
    }

    .info-form {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .form-group label {
        font-size: 0.875rem;
        font-weight: 500;
        color: #374151;
    }

    .form-group input,
    .form-group textarea,
    .form-group select {
        padding: 0.75rem;
        border: 1px solid var(--vita-grey-light);
        border-radius: 1rem;
        font-size: 0.875rem;
        background: var(--vita-grey-light);
        transition: all 0.2s ease;
    }

    .form-group input:focus,
    .form-group textarea:focus,
    .form-group select:focus {
        outline: none;
        border-color: var(--vita-blue);
        box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.1);
    }

    .form-group textarea {
        resize: vertical;
        min-height: 4rem;
    }

    .health-info {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .health-section h3 {
        font-size: 0.875rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 0.75rem;
    }

    .condition-list,
    .allergy-list {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .condition-tag,
    .allergy-tag {
        font-size: 0.75rem;
        font-weight: 500;
        padding: 0.25rem 0.75rem;
        border-radius: 1rem;
        display: inline-block;
    }

    .condition-tag {
        background: rgba(74, 144, 226, 0.1);
        color: var(--vita-blue);
    }

    .allergy-tag {
        background: rgba(231, 76, 60, 0.1);
        color: var(--vita-coral);
    }

    .emergency-info p {
        font-size: 0.875rem;
        color: #374151;
        margin-bottom: 0.5rem;
    }

    .emergency-info strong {
        color: #1f2937;
    }

    .preferences-list,
    .privacy-settings {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .preference-item,
    .privacy-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem;
        background: var(--vita-white);
        border-radius: 1rem;
        border: 1px solid var(--vita-grey-light);
    }

    .preference-info,
    .privacy-info {
        flex: 1;
    }

    .preference-info span,
    .privacy-info span {
        font-size: 0.875rem;
        font-weight: 500;
        color: #1f2937;
        display: block;
        margin-bottom: 0.25rem;
    }

    .preference-info p,
    .privacy-info p {
        font-size: 0.75rem;
        color: #6b7280;
        margin: 0;
    }

    .privacy-action {
        margin-top: 1rem;
    }

    .privacy-action h3 {
        font-size: 0.875rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 0.75rem;
    }

    .security-btn,
    .data-btn {
        margin-right: 0.75rem;
        margin-bottom: 0.5rem;
        padding: 0.5rem 1rem;
        background: var(--vita-grey-light);
        color: #374151;
        border: none;
        border-radius: 1rem;
        font-size: 0.75rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .security-btn:hover,
    .data-btn:hover {
        background: var(--vita-grey);
    }

    .data-btn:last-child {
        background: rgba(231, 76, 60, 0.1);
        color: var(--vita-coral);
    }

    .data-btn:last-child:hover {
        background: rgba(231, 76, 60, 0.2);
    }

    .add-family-btn {
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--vita-blue);
        background: none;
        border: none;
        cursor: pointer;
        transition: color 0.2s ease;
    }

    .add-family-btn:hover {
        color: var(--vita-blue-dark);
    }

    .family-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .family-member {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        border: 1px solid var(--vita-grey-light);
        border-radius: 1.5rem;
        transition: all 0.2s ease;
    }

    .family-member:hover {
        background: var(--vita-grey-light);
    }

    .member-avatar {
        width: 3rem;
        height: 3rem;
        background: var(--vita-grey-light);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .member-avatar svg {
        width: 1.5rem;
        height: 1.5rem;
        color: #6b7280;
    }

    .member-info {
        flex: 1;
    }

    .member-info h3 {
        font-size: 1rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 0.25rem;
    }

    .member-info p {
        font-size: 0.875rem;
        color: #6b7280;
        margin-bottom: 0.25rem;
    }

    .member-email {
        font-size: 0.75rem;
        color: var(--vita-blue);
    }

    .member-permissions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-right: 1rem;
    }

    .permission-tag {
        font-size: 0.625rem;
        font-weight: 500;
        color: var(--vita-mint-dark);
        background: rgba(126, 214, 165, 0.1);
        padding: 0.25rem 0.5rem;
        border-radius: 0.75rem;
    }

    .member-actions {
        display: flex;
        gap: 0.75rem;
    }

    .member-action-btn {
        font-size: 0.75rem;
        color: var(--vita-blue);
        background: none;
        border: none;
        cursor: pointer;
        transition: color 0.2s ease;
    }

    .member-action-btn:hover {
        color: var(--vita-blue-dark);
    }

    .member-action-btn:last-child {
        color: var(--vita-coral);
    }

    .member-action-btn:last-child:hover {
        color: #dc2626;
    }

    .password-requirements {
        margin-top: 1rem;
        padding: 1rem;
        background: var(--vita-grey-light);
        border-radius: 1rem;
    }

    .password-requirements p {
        font-size: 0.875rem;
        font-weight: 500;
        color: #374151;
        margin-bottom: 0.5rem;
    }

    .password-requirements ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .password-requirements li {
        font-size: 0.75rem;
        color: #6b7280;
        margin-bottom: 0.25rem;
        padding-left: 1rem;
        position: relative;
    }

    .password-requirements li::before {
        content: '•';
        position: absolute;
        left: 0;
        color: var(--vita-blue);
    }

    .permissions-list {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        margin-top: 0.5rem;
    }

    .permission-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
        color: #374151;
    }

    .permission-item input[type="checkbox"] {
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
        .profile-header {
            flex-direction: column;
            text-align: center;
        }
        
        .profile-stats {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .family-member {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }
        
        .member-permissions {
            margin-right: 0;
        }
        
        .member-actions {
            align-self: flex-end;
        }
    }
`;

// Inject styles
const styleSheet = document.createElement('style');
styleSheet.textContent = profileStyles;
document.head.appendChild(styleSheet);