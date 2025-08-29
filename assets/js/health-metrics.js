// Health Metrics Page JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Period selector functionality
    const periodButtons = document.querySelectorAll('.period-btn');
    
    periodButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all buttons
            periodButtons.forEach(btn => btn.classList.remove('active'));
            
            // Add active class to clicked button
            this.classList.add('active');
            
            // Get selected period
            const selectedPeriod = this.dataset.period;
            
            // Update charts and data based on selected period
            updateHealthData(selectedPeriod);
        });
    });

    function updateHealthData(period) {
        // Simulate data update based on period
        console.log(`Updating health data for period: ${period}`);
        
        // Show loading state
        const chartPlaceholder = document.querySelector('.chart-placeholder');
        if (chartPlaceholder) {
            chartPlaceholder.style.opacity = '0.5';
            
            setTimeout(() => {
                chartPlaceholder.style.opacity = '1';
                // Here you would typically fetch new data from your backend
                updateMetricsForPeriod(period);
            }, 500);
        }
    }

    function updateMetricsForPeriod(period) {
        // Simulate different averages based on period
        const metrics = {
            day: {
                heartRate: { current: 72, average: 74 },
                bloodPressure: { current: '120/80', average: '122/81' },
                oxygen: { current: 98, average: 97 },
                glucose: { current: 95, average: 96 }
            },
            week: {
                heartRate: { current: 72, average: 75 },
                bloodPressure: { current: '120/80', average: '125/82' },
                oxygen: { current: 98, average: 97 },
                glucose: { current: 95, average: 98 }
            },
            month: {
                heartRate: { current: 72, average: 76 },
                bloodPressure: { current: '120/80', average: '128/84' },
                oxygen: { current: 98, average: 96 },
                glucose: { current: 95, average: 102 }
            },
            year: {
                heartRate: { current: 72, average: 78 },
                bloodPressure: { current: '120/80', average: '132/86' },
                oxygen: { current: 98, average: 96 },
                glucose: { current: 95, average: 105 }
            }
        };

        const data = metrics[period];
        
        // Update metric averages
        const averageElements = document.querySelectorAll('.metric-average');
        if (averageElements.length >= 4) {
            averageElements[0].textContent = `Avg: ${data.heartRate.average} bpm`;
            averageElements[1].textContent = `Avg: ${data.bloodPressure.average} mmHg`;
            averageElements[2].textContent = `Avg: ${data.oxygen.average}%`;
            averageElements[3].textContent = `Avg: ${data.glucose.average} mg/dL`;
        }
    }

    // Simulate real-time updates for health metrics
    function updateHealthMetrics() {
        const metrics = document.querySelectorAll('.metric-value');
        
        if (metrics.length >= 4) {
            // Heart rate (68-76 bpm)
            const heartRate = Math.floor(Math.random() * 9) + 68;
            metrics[0].innerHTML = `${heartRate} <span class="metric-unit">bpm</span>`;
            
            // Blood pressure
            const systolic = Math.floor(Math.random() * 10) + 115;
            const diastolic = Math.floor(Math.random() * 8) + 75;
            metrics[1].innerHTML = `${systolic}/${diastolic} <span class="metric-unit">mmHg</span>`;
            
            // SpO2 (96-99%)
            const oxygen = Math.floor(Math.random() * 4) + 96;
            metrics[2].innerHTML = `${oxygen} <span class="metric-unit">%</span>`;
            
            // Glucose (90-110 mg/dL)
            const glucose = Math.floor(Math.random() * 21) + 90;
            metrics[3].innerHTML = `${glucose} <span class="metric-unit">mg/dL</span>`;
        }
    }

    // Update metrics every 30 seconds
    setInterval(updateHealthMetrics, 30000);

    // Add trend indicators
    function updateTrendIndicators() {
        const statusElements = document.querySelectorAll('.metric-status');
        const trends = ['● Stable', '● Improving', '● Normal', '● Good'];
        const colors = ['normal', 'improving', 'normal', 'normal'];
        
        statusElements.forEach((element, index) => {
            if (index < trends.length) {
                element.textContent = trends[index];
                element.className = `metric-status ${colors[index]}`;
                
                // Add beta tags back for blood pressure and glucose
                if (index === 1 || index === 3) {
                    const betaTag = document.createElement('span');
                    betaTag.className = 'beta-tag';
                    betaTag.textContent = 'BETA';
                    element.appendChild(betaTag);
                }
            }
        });
    }

    // AI Analysis button functionality
    function handleAIAnalysis() {
        const button = document.querySelector('.ai-analysis-btn');
        if (!button) return;
        
        // Disable button and show loading state
        button.disabled = true;
        button.classList.add('analyzing');
        button.innerHTML = `
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 12l2 2 4-4"></path>
                <path d="M21 12c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1z"></path>
                <path d="M3 12c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1z"></path>
                <path d="M12 21c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1z"></path>
                <path d="M12 3c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1z"></path>
            </svg>
            Analyzing...
        `;
        
        // Simulate AI analysis
        if (window.VitaAI) {
            window.VitaAI.analyzeCurrentVitals().finally(() => {
                // Re-enable button after analysis
                button.disabled = false;
                button.classList.remove('analyzing');
                button.innerHTML = `
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 12l2 2 4-4"></path>
                        <path d="M21 12c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1z"></path>
                        <path d="M3 12c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1z"></path>
                        <path d="M12 21c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1z"></path>
                        <path d="M12 3c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1z"></path>
                    </svg>
                    AI Analysis
                `;
            });
        }
    }

    updateTrendIndicators();

    // Add interactive chart simulation
    const chartPlaceholder = document.querySelector('.chart-placeholder');
    if (chartPlaceholder) {
        chartPlaceholder.addEventListener('click', function() {
            this.style.transform = 'scale(0.98)';
            setTimeout(() => {
                this.style.transform = 'scale(1)';
            }, 150);
            
            // Simulate chart interaction
            if (window.VitaApp) {
                window.VitaApp.showNotification('Chart interaction coming soon!', 'info');
            }
        });
    }

    // Add reading item interactions
    const readingItems = document.querySelectorAll('.reading-item');
    readingItems.forEach(item => {
        item.addEventListener('click', function() {
            // Toggle expanded view
            this.classList.toggle('expanded');
            
            // Add detailed view if not exists
            if (!this.querySelector('.reading-details')) {
                const details = document.createElement('div');
                details.className = 'reading-details';
                details.innerHTML = `
                    <div class="detail-row">
                        <span>Location:</span>
                        <span>Home</span>
                    </div>
                    <div class="detail-row">
                        <span>Activity:</span>
                        <span>Resting</span>
                    </div>
                    <div class="detail-row">
                        <span>Notes:</span>
                        <span>All readings within normal range</span>
                    </div>
                `;
                this.appendChild(details);
            }
        });
    });
});

// Add custom styles for health metrics page
const healthMetricsStyles = `
    .period-selector {
        display: flex;
        gap: 0.25rem;
        background: var(--vita-grey-light);
        padding: 0.25rem;
        border-radius: 1.5rem;
        width: fit-content;
        margin-bottom: 2rem;
    }

    .period-btn {
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
        font-weight: 500;
        border: none;
        border-radius: 1rem;
        background: transparent;
        color: #6b7280;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .period-btn:hover {
        color: #1f2937;
    }

    .period-btn.active {
        background: white;
        color: #1f2937;
        box-shadow: var(--shadow-soft);
    }

    .metric-average {
        font-size: 0.875rem;
        color: #6b7280;
        margin-top: 0.25rem;
    }

    .metric-status.improving {
        color: var(--vita-mint-dark);
    }

    .chart-placeholder {
        height: 16rem;
        background: linear-gradient(135deg, rgba(231, 76, 60, 0.05), rgba(231, 76, 60, 0.1));
        border-radius: 1.5rem;
        border: 1px solid var(--vita-grey-light);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: transform 0.2s ease;
        margin-bottom: 1rem;
    }

    .chart-content {
        text-align: center;
    }

    .chart-icon {
        width: 3rem;
        height: 3rem;
        color: var(--vita-coral);
        margin: 0 auto 0.5rem;
    }

    .chart-content p {
        font-size: 0.875rem;
        font-weight: 500;
        color: #374151;
        margin-bottom: 0.25rem;
    }

    .chart-subtitle {
        font-size: 0.75rem;
        color: #6b7280;
    }

    .chart-stats {
        display: flex;
        justify-content: space-between;
        font-size: 0.875rem;
        color: #6b7280;
    }

    .readings-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .reading-item {
        padding: 1rem;
        background: var(--vita-white);
        border-radius: 1rem;
        border: 1px solid var(--vita-grey-light);
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .reading-item:hover {
        background: var(--vita-grey-light);
    }

    .reading-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.5rem;
    }

    .reading-time {
        font-size: 0.875rem;
        font-weight: 500;
        color: #1f2937;
    }

    .reading-date {
        font-size: 0.75rem;
        color: #6b7280;
    }

    .reading-metrics {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }

    .reading-metric {
        font-size: 0.875rem;
    }

    .metric-name {
        color: #6b7280;
    }

    .metric-val {
        font-weight: 500;
        color: #1f2937;
        margin-left: 0.25rem;
    }

    .reading-details {
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid var(--vita-grey-light);
        display: none;
    }

    .reading-item.expanded .reading-details {
        display: block;
    }

    .detail-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
    }

    .detail-row span:first-child {
        color: #6b7280;
    }

    .detail-row span:last-child {
        font-weight: 500;
        color: #1f2937;
    }

    .insights-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
    }

    .insight-card {
        padding: 1rem;
        border-radius: 1.5rem;
        border: 1px solid;
    }

    .insight-card.positive {
        background: rgba(126, 214, 165, 0.1);
        border-color: rgba(126, 214, 165, 0.3);
    }

    .insight-card.recommendations {
        background: rgba(74, 144, 226, 0.1);
        border-color: rgba(74, 144, 226, 0.3);
    }

    .insight-card h3 {
        font-size: 0.875rem;
        font-weight: 500;
        margin-bottom: 0.75rem;
    }

    .insight-card.positive h3 {
        color: var(--vita-mint-dark);
    }

    .insight-card.recommendations h3 {
        color: var(--vita-blue-dark);
    }

    .insight-card ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .insight-card li {
        font-size: 0.875rem;
        margin-bottom: 0.25rem;
    }

    .insight-card.positive li {
        color: var(--vita-mint-dark);
    }

    .insight-card.recommendations li {
        color: var(--vita-blue-dark);
    }

    @media (max-width: 768px) {
        .reading-metrics {
            grid-template-columns: 1fr;
            gap: 0.5rem;
        }
        
        .insights-grid {
            grid-template-columns: 1fr;
        }
    }
`;

// Inject styles
const styleSheet = document.createElement('style');
styleSheet.textContent = healthMetricsStyles;
document.head.appendChild(styleSheet);