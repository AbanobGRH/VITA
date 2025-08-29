// AI Analytics Integration for VITA Health Platform

class VitaAIAnalytics {
    constructor() {
        this.apiEndpoint = 'https://ai.hackclub.com/api/analyze';
        this.apiKey = 'vita_api_key_2024';
        this.userId = '550e8400-e29b-41d4-a716-446655440000';
        this.isAnalyzing = false;
    }

    async sendVitalsToAI(vitals) {
        if (this.isAnalyzing) {
            if (window.VitaApp) {
                window.VitaApp.showNotification('AI analysis already in progress...', 'info');
            }
            return null;
        }
        
        this.isAnalyzing = true;
        
        try {
            const payload = {
                user_id: this.userId,
                timestamp: new Date().toISOString(),
                vitals: {
                    heart_rate: vitals.heartRate,
                    spo2: vitals.spo2,
                    glucose_level: vitals.glucoseLevel
                },
                context: {
                    age: 72,
                    conditions: ['Type 2 Diabetes', 'High Cholesterol'],
                    medications: ['Metformin', 'Atorvastatin'],
                    activity_level: 'moderate'
                }
            };

            const response = await fetch(this.apiEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-API-Key': this.apiKey,
                    'Authorization': `Bearer ${this.apiKey}`
                },
                body: JSON.stringify(payload)
            });

            if (response.ok) {
                const result = await response.json();
                this.processAIResponse(result);
                this.isAnalyzing = false;
                return result;
            } else {
                console.error('AI API Error:', response.status, response.statusText);
                this.isAnalyzing = false;
                return null;
            }
        } catch (error) {
            console.error('AI Analytics Error:', error);
            this.isAnalyzing = false;
            return null;
        }
    }

    processAIResponse(response) {
        if (response.alerts && response.alerts.length > 0) {
            response.alerts.forEach(alert => {
                this.createHealthAlert(alert);
            });
        }

        if (response.recommendations) {
            this.updateRecommendations(response.recommendations);
        }

        if (response.risk_assessment) {
            this.updateRiskAssessment(response.risk_assessment);
        }
    }

    createHealthAlert(alert) {
        const alertsContainer = document.querySelector('.alerts-list');
        if (!alertsContainer) return;

        const alertElement = document.createElement('div');
        alertElement.className = `alert-item ${alert.severity || 'medium'}`;
        alertElement.innerHTML = `
            <div class="alert-indicator"></div>
            <div class="alert-content">
                <div class="alert-header">
                    <span class="alert-type">AI Analysis</span>
                    <span class="alert-time">${new Date().toLocaleTimeString()}</span>
                </div>
                <p class="alert-message">${alert.message}</p>
                <div class="alert-actions">
                    <button class="alert-action-btn">View Details</button>
                    <button class="alert-action-btn">Dismiss</button>
                </div>
            </div>
        `;

        alertsContainer.insertBefore(alertElement, alertsContainer.firstChild);

        // Add event listeners
        const actionBtns = alertElement.querySelectorAll('.alert-action-btn');
        actionBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                if (btn.textContent === 'Dismiss') {
                    alertElement.remove();
                } else {
                    this.showAlertDetails(alert);
                }
            });
        });

        // Show notification
        if (window.VitaApp) {
            window.VitaApp.showNotification(alert.message, alert.severity === 'high' ? 'error' : 'info');
        }
    }

    updateRecommendations(recommendations) {
        const recommendationsCard = document.querySelector('.insight-card.recommendations ul');
        if (!recommendationsCard) return;

        recommendationsCard.innerHTML = '';
        recommendations.forEach(rec => {
            const li = document.createElement('li');
            li.textContent = `• ${rec}`;
            recommendationsCard.appendChild(li);
        });
    }

    updateRiskAssessment(riskData) {
        // Update risk indicators in the UI
        const riskIndicators = document.querySelectorAll('.risk-indicator');
        riskIndicators.forEach(indicator => {
            const riskType = indicator.dataset.riskType;
            if (riskData[riskType]) {
                indicator.textContent = riskData[riskType].level;
                indicator.className = `risk-indicator ${riskData[riskType].level.toLowerCase()}`;
            }
        });
    }

    showAlertDetails(alert) {
        const modal = document.createElement('div');
        modal.className = 'modal-overlay';
        modal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h3>AI Health Alert Details</h3>
                    <button class="modal-close">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="alert-detail-section">
                        <h4>Alert Type</h4>
                        <p>${alert.type || 'Health Analysis'}</p>
                    </div>
                    <div class="alert-detail-section">
                        <h4>Severity</h4>
                        <p class="severity-${alert.severity}">${alert.severity?.toUpperCase() || 'MEDIUM'}</p>
                    </div>
                    <div class="alert-detail-section">
                        <h4>Message</h4>
                        <p>${alert.message}</p>
                    </div>
                    ${alert.details ? `
                        <div class="alert-detail-section">
                            <h4>Details</h4>
                            <p>${alert.details}</p>
                        </div>
                    ` : ''}
                    ${alert.recommendations ? `
                        <div class="alert-detail-section">
                            <h4>Recommendations</h4>
                            <ul>
                                ${alert.recommendations.map(rec => `<li>${rec}</li>`).join('')}
                            </ul>
                        </div>
                    ` : ''}
                </div>
                <div class="modal-footer">
                    <button class="btn-secondary modal-close-btn">Close</button>
                    <button class="btn-primary contact-doctor-btn">Contact Doctor</button>
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        // Add event listeners
        const closeButtons = modal.querySelectorAll('.modal-close, .modal-close-btn');
        closeButtons.forEach(btn => {
            btn.addEventListener('click', () => modal.remove());
        });

        const contactBtn = modal.querySelector('.contact-doctor-btn');
        contactBtn.addEventListener('click', () => {
            this.contactDoctor(alert);
            modal.remove();
        });

        modal.addEventListener('click', (e) => {
            if (e.target === modal) modal.remove();
        });
    }

    contactDoctor(alert) {
        // Simulate contacting doctor
        if (window.VitaApp) {
            window.VitaApp.showNotification('Contacting Dr. Michael Chen...', 'info');
        }

        setTimeout(() => {
            if (window.VitaApp) {
                window.VitaApp.showNotification('Doctor has been notified about the alert', 'success');
            }
        }, 2000);
    }

    async analyzeCurrentVitals() {        
        if (window.VitaApp) {
            window.VitaApp.showNotification('Starting AI analysis of current vitals...', 'info');
        }
        
        // Get current vitals from the page
        const vitals = this.getCurrentVitals();
        if (vitals) {
            const result = await this.sendVitalsToAI(vitals);
            if (result) {
                if (window.VitaApp) {
                    window.VitaApp.showNotification('AI analysis completed successfully!', 'success');
                }
            } else {
                if (window.VitaApp) {
                    window.VitaApp.showNotification('AI analysis failed. Please try again.', 'error');
                }
            }
        } else {
            if (window.VitaApp) {
                window.VitaApp.showNotification('Unable to read current vitals. Please refresh the page.', 'error');
            }
        }
    }

    getCurrentVitals() {
        // Updated selectors for the new 3-metric layout (removed blood pressure)
        const heartRateElement = document.querySelector('.metric-card:first-child .metric-value');
        const glucoseElement = document.querySelector('.metric-card:nth-child(2) .metric-value');
        const spo2Element = document.querySelector('.metric-card:nth-child(3) .metric-value');

        if (!heartRateElement || !glucoseElement || !spo2Element) {
            console.error('Could not find vital signs elements');
            return null;
        }

        return {
            heartRate: parseInt(heartRateElement.textContent),
            glucoseLevel: parseInt(glucoseElement.textContent),
            spo2: parseInt(spo2Element.textContent)
        };
    }
}

// Initialize AI Analytics
document.addEventListener('DOMContentLoaded', function() {
    const aiAnalytics = new VitaAIAnalytics();
    
    // Make available globally
    window.VitaAI = aiAnalytics;
});

// Add custom styles for AI features
const aiStyles = `
    .ai-analysis-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }

    .ai-analysis-btn svg {
        width: 1rem;
        height: 1rem;
    }

    .alert-detail-section {
        margin-bottom: 1rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid var(--vita-grey-light);
    }

    .alert-detail-section:last-child {
        border-bottom: none;
        margin-bottom: 0;
    }

    .alert-detail-section h4 {
        font-size: 0.875rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 0.5rem;
    }

    .severity-high {
        color: var(--vita-coral);
        font-weight: 600;
    }

    .severity-medium {
        color: var(--orange-500);
        font-weight: 600;
    }

    .severity-low {
        color: var(--vita-blue);
        font-weight: 600;
    }

    .risk-indicator {
        padding: 0.25rem 0.5rem;
        border-radius: 0.5rem;
        font-size: 0.75rem;
        font-weight: 500;
    }

    .risk-indicator.low {
        background: rgba(126, 214, 165, 0.2);
        color: var(--vita-mint-dark);
    }

    .risk-indicator.medium {
        background: var(--orange-50);
        color: var(--orange-500);
    }

    .risk-indicator.high {
        background: rgba(231, 76, 60, 0.2);
        color: var(--vita-coral);
    }
`;

// Inject AI styles
const aiStyleSheet = document.createElement('style');
aiStyleSheet.textContent = aiStyles;
document.head.appendChild(aiStyleSheet);