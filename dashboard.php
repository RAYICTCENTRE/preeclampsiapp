<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MotherCare - Health Analytics</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* ... (keep all your existing styles) ... */
    </style>
</head>
<body>

<!-- IDLE WARNING MODAL -->
<div class="idle-modal" id="idleModal">
    <div class="idle-modal-content">
        <i class="fas fa-clock"></i>
        <h2>Session Expiring Soon</h2>
        <p>You have been inactive for a while. Your session will expire in:</p>
        <div class="countdown-timer" id="countdownTimer">60</div>
        <p style="font-size: 12px; color: #94a3b8;">seconds</p>
        <br>
        <button class="btn-stay" onclick="stayLoggedIn()">
            <i class="fas fa-check-circle"></i> Stay Logged In
        </button>
        <button class="btn-logout-modal" onclick="logoutNow()">
            <i class="fas fa-sign-out-alt"></i> Logout Now
        </button>
    </div>
</div>

<!-- LEFT SIDEBAR -->
<div class="sidebar">
    <div class="sidebar-header">
        <h2>🏥 MotherCare</h2>
        <p>Patient Portal</p>
    </div>
    
    <div class="sidebar-nav">
        <a href="dashboard.php" class="nav-item active">
            <i class="fas fa-chart-line"></i>
            <span>Health Analytics</span>
        </a>
        <a href="screen6.html" class="nav-item">
            <i class="fas fa-stethoscope"></i>
            <span>Check Symptoms</span>
        </a>
        <a href="consult_doctor.php" class="nav-item">
            <i class="fas fa-user-md"></i>
            <span>Consult Doctor</span>
        </a>
        <a href="screen4.html" class="nav-item">
            <i class="fas fa-edit"></i>
            <span>Update Profile</span>
        </a>
        <a href="screen1.html" class="nav-item" style="margin-top: 20px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 20px;">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>
    
    <div class="stats-mini">
        <div class="stat-mini-item">
            <span><i class="fas fa-calendar-check"></i> Visits</span>
            <strong id="totalVisits">-</strong>
        </div>
        <div class="stat-mini-item">
            <span><i class="fas fa-heartbeat"></i> Risk Level</span>
            <strong id="latestRisk">-</strong>
        </div>
        <div class="stat-mini-item">
            <span><i class="fas fa-baby"></i> Due Date</span>
            <strong id="eddDate">-</strong>
        </div>
    </div>
</div>

<!-- MAIN CONTENT -->
<div class="main-content" id="mainContent">
    <div class="top-bar">
        <div class="page-title">
            <h1>📊 Health Analytics</h1>
        </div>
        <div class="user-info">
            <div class="session-timer" id="sessionTimer">
                <i class="fas fa-hourglass-half"></i>
                <span>Session: </span>
                <strong id="timerDisplay">5:00</strong>
            </div>
            <span class="welcome-badge"><i class="fas fa-user-circle"></i> <span id="userName">Patient</span></span>
            <a href="screen1.html" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid" id="overview">
        <div class="stat-card">
            <div class="stat-icon">📋</div>
            <div class="stat-number" id="totalVisitsCard">-</div>
            <div class="stat-label">Total Health Visits</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">⚠️</div>
            <div class="stat-number" id="avgRiskCard">-</div>
            <div class="stat-label">Average Risk Score</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">❤️</div>
            <div class="stat-number" id="latestRiskCard">-</div>
            <div class="stat-label">Latest Risk Level</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">👶</div>
            <div class="stat-number" id="eddDateCard">-</div>
            <div class="stat-label">Expected Delivery</div>
        </div>
    </div>

    <!-- Risk Distribution Chart -->
    <div class="section">
        <h3>📈 Risk Distribution</h3>
        <div class="chart-container">
            <div class="chart-box">
                <h4>Risk Levels Overview</h4>
                <div class="chart-bar" id="riskChart">
                    <div class="bar-item">
                        <div class="bar-value" id="lowCount">0</div>
                        <div class="bar" style="height: 30px; background: #28a745;" id="lowBar"></div>
                        <div class="bar-label">Low</div>
                    </div>
                    <div class="bar-item">
                        <div class="bar-value" id="moderateCount">0</div>
                        <div class="bar" style="height: 30px; background: #ffc107;" id="moderateBar"></div>
                        <div class="bar-label">Moderate</div>
                    </div>
                    <div class="bar-item">
                        <div class="bar-value" id="highCount">0</div>
                        <div class="bar" style="height: 30px; background: #fd7e14;" id="highBar"></div>
                        <div class="bar-label">High</div>
                    </div>
                    <div class="bar-item">
                        <div class="bar-value" id="criticalCount">0</div>
                        <div class="bar" style="height: 30px; background: #dc3545;" id="criticalBar"></div>
                        <div class="bar-label">Critical</div>
                    </div>
                </div>
            </div>
            
            <div class="chart-box">
                <h4>Risk Level Summary</h4>
                <div id="riskSummary" style="width: 100%;">
                    <div class="risk-level-item">
                        <span><span class="risk-dot low"></span> Low</span>
                        <span id="lowSummary">0 visits</span>
                    </div>
                    <div class="risk-level-item">
                        <span><span class="risk-dot moderate"></span> Moderate</span>
                        <span id="moderateSummary">0 visits</span>
                    </div>
                    <div class="risk-level-item">
                        <span><span class="risk-dot high"></span> High</span>
                        <span id="highSummary">0 visits</span>
                    </div>
                    <div class="risk-level-item">
                        <span><span class="risk-dot critical"></span> Critical</span>
                        <span id="criticalSummary">0 visits</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Symptoms History Table -->
    <div class="section">
        <div class="section-header">
            <h3>📋 Recent Symptoms Records</h3>
            <a href="full_history.php" class="btn-view-all">View All →</a>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Symptoms</th>
                        <th>Blood Pressure</th>
                        <th>Risk Level</th>
                        <th>Risk Score</th>
                    </tr>
                </thead>
                <tbody id="symptomsTableBody">
                    <tr>
                        <td colspan="5" style="text-align: center; color: #6c757d; padding: 30px;">
                            <i class="fas fa-spinner fa-spin"></i> Loading data...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Quick Insights -->
    <div class="section">
        <h3>📊 Quick Insights</h3>
        <div class="quick-insights">
            <div class="insight-card">
                <div class="number" id="insightTotal">-</div>
                <div class="label">Total Assessments</div>
            </div>
            <div class="insight-card">
                <div class="number" id="insightRisk">-</div>
                <div class="label">Current Risk Level</div>
            </div>
            <div class="insight-card">
                <div class="number" id="insightAvg">-</div>
                <div class="label">Average Risk Score</div>
            </div>
            <div class="insight-card">
                <div class="number" id="insightRecords">-</div>
                <div class="label">Total Records</div>
            </div>
        </div>
    </div>
</div>

<script>
// ============================================
// AUTO SIGN-OUT AFTER 5 MINUTES INACTIVITY
// ============================================

const SESSION_TIMEOUT = 5;
const WARNING_TIME = 60;

let idleTimer, countdownTimer;
let secondsLeft = SESSION_TIMEOUT * 60;
let warningShown = false;

const timerDisplay = document.getElementById('timerDisplay');
const sessionTimer = document.getElementById('sessionTimer');
const idleModal = document.getElementById('idleModal');
const countdownDisplay = document.getElementById('countdownTimer');

function resetIdleTimer() {
    secondsLeft = SESSION_TIMEOUT * 60;
    warningShown = false;
    idleModal.classList.remove('show');
    clearInterval(idleTimer);
    clearInterval(countdownTimer);
    updateTimerDisplay();
    sessionTimer.className = 'session-timer';
    idleTimer = setInterval(checkIdleTime, 1000);
}

function checkIdleTime() {
    secondsLeft--;
    updateTimerDisplay();
    if (secondsLeft <= WARNING_TIME && !warningShown) {
        warningShown = true;
        showWarningModal();
    }
    if (secondsLeft <= 0) {
        logoutNow();
    }
}

function updateTimerDisplay() {
    const minutes = Math.floor(secondsLeft / 60);
    const seconds = secondsLeft % 60;
    timerDisplay.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
    if (secondsLeft <= 60) sessionTimer.className = 'session-timer danger';
    else if (secondsLeft <= 120) sessionTimer.className = 'session-timer warning';
}

function showWarningModal() {
    idleModal.classList.add('show');
    let countdown = WARNING_TIME;
    countdownDisplay.textContent = countdown;
    countdownTimer = setInterval(() => {
        countdown--;
        countdownDisplay.textContent = countdown;
        if (countdown <= 0) clearInterval(countdownTimer);
    }, 1000);
}

function stayLoggedIn() {
    resetIdleTimer();
    showToast('Session extended!', 'success');
}

function logoutNow() {
    clearInterval(idleTimer);
    clearInterval(countdownTimer);
    showToast('Logging out...', 'info');
    setTimeout(() => { window.location.href = 'screen1.html'; }, 1000);
}

function showToast(message, type = 'info') {
    const old = document.getElementById('toast');
    if (old) old.remove();
    const toast = document.createElement('div');
    toast.id = 'toast';
    const colors = { success: '#2e7d32', error: '#dc3545', info: '#17a2b8' };
    toast.style.cssText = `
        position: fixed; top: 20px; right: 20px; padding: 16px 24px;
        border-radius: 12px; color: white; font-weight: 500;
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        transform: translateX(400px); transition: transform 0.3s ease;
        z-index: 1000; max-width: 350px;
        background: ${colors[type] || colors.info};
    `;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => { toast.style.transform = 'translateX(0)'; }, 100);
    setTimeout(() => { toast.style.transform = 'translateX(400px)'; }, 4000);
}

document.addEventListener('mousemove', resetIdleTimer);
document.addEventListener('keypress', resetIdleTimer);
document.addEventListener('click', resetIdleTimer);
document.addEventListener('scroll', resetIdleTimer);

// ============================================
// FETCH DATA FROM API
// ============================================

function fetchData() {
    fetch('VisitSummary.php')
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error('Error:', data.error);
                return;
            }
            
            // Update user name
            document.getElementById('userName').textContent = data.name?.split(' ')[0] || 'Patient';
            
            // Update stats
            const totalVisits = data.visits ? data.visits.length : 0;
            document.getElementById('totalVisits').textContent = totalVisits;
            document.getElementById('totalVisitsCard').textContent = totalVisits;
            document.getElementById('insightTotal').textContent = totalVisits;
            document.getElementById('insightRecords').textContent = totalVisits;
            
            const latestRisk = data.visits && data.visits.length > 0 ? data.visits[0].risk_level : 'No Data';
            document.getElementById('latestRisk').textContent = latestRisk;
            document.getElementById('latestRiskCard').textContent = latestRisk;
            document.getElementById('insightRisk').textContent = latestRisk;
            
            // Color the risk
            const riskElement = document.getElementById('latestRiskCard');
            const riskColors = { 'Low': '#2e7d32', 'Moderate': '#ffc107', 'High': '#fd7e14', 'Critical': '#dc3545' };
            riskElement.style.color = riskColors[latestRisk] || 'gray';
            
            const edd = data.expected_delivery && data.expected_delivery !== 'N/A' ? 
                data.expected_delivery.substring(0, 10) : 'Not Set';
            document.getElementById('eddDate').textContent = edd;
            document.getElementById('eddDateCard').textContent = edd;
            
            // Calculate average risk
            if (data.visits && data.visits.length > 0) {
                let totalRisk = 0;
                const riskMap = {'Low': 1, 'Moderate': 2, 'High': 3, 'Critical': 4};
                data.visits.forEach(visit => {
                    totalRisk += riskMap[visit.risk_level] || 0;
                });
                const avgRisk = (totalRisk / data.visits.length).toFixed(1);
                document.getElementById('avgRiskCard').textContent = avgRisk;
                document.getElementById('insightAvg').textContent = avgRisk + '%';
            }
            
            // Update symptoms table
            updateTable(data.visits || []);
            
            // Update risk distribution
            updateRiskDistribution(data.visits || []);
        })
        .catch(error => {
            console.error('Error fetching data:', error);
            document.getElementById('symptomsTableBody').innerHTML = `
                <tr><td colspan="5" style="text-align:center;color:#dc3545;padding:30px;">
                    <i class="fas fa-exclamation-circle"></i> Failed to load data
                </td></tr>
            `;
        });
}

function updateTable(visits) {
    const tbody = document.getElementById('symptomsTableBody');
    if (!visits || visits.length === 0) {
        tbody.innerHTML = `
            <tr><td colspan="5" style="text-align:center;color:#6c757d;padding:30px;">
                <i class="fas fa-clipboard-list"></i> No symptoms records found
            </td></tr>
        `;
        return;
    }
    
    let html = '';
    visits.slice(0, 10).forEach(visit => {
        const riskClass = (visit.risk_level || 'low').toLowerCase();
        
        // ✅ FIX: Check for risk_score first, then fallback to risk
        const riskScore = visit.risk_score || visit.risk || 'N/A';
        
        html += `
            <tr>
                <td>${visit.created_at ? new Date(visit.created_at).toLocaleDateString('en-US', { 
                    month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' 
                }) : 'N/A'}</td>
                <td>${visit.symptoms ? (visit.symptoms.substring(0, 50) + (visit.symptoms.length > 50 ? '...' : '')) : 'N/A'}</td>
                <td>${visit.blood_pressure || 'N/A'}</td>
                <td><span class="badge badge-${riskClass}">${visit.risk_level || 'N/A'}</span></td>
                <td>${riskScore}%</td>
            </tr>
        `;
    });
    tbody.innerHTML = html;
}

function updateRiskDistribution(visits) {
    const riskCounts = { 'Low': 0, 'Moderate': 0, 'High': 0, 'Critical': 0 };
    visits.forEach(visit => {
        if (visit.risk_level && riskCounts.hasOwnProperty(visit.risk_level)) {
            riskCounts[visit.risk_level]++;
        }
    });
    
    const maxCount = Math.max(...Object.values(riskCounts), 1);
    
    // Update bars
    const colors = { 'Low': '#28a745', 'Moderate': '#ffc107', 'High': '#fd7e14', 'Critical': '#dc3545' };
    Object.keys(riskCounts).forEach(level => {
        const count = riskCounts[level];
        const height = (count / maxCount) * 120 + 20;
        document.getElementById(level.toLowerCase() + 'Bar').style.height = height + 'px';
        document.getElementById(level.toLowerCase() + 'Count').textContent = count;
        document.getElementById(level.toLowerCase() + 'Summary').textContent = count + ' visit' + (count !== 1 ? 's' : '');
    });
}

// ============================================
// INITIALIZE
// ============================================
resetIdleTimer();
fetchData();

console.log('Health Analytics Dashboard loaded. Session timeout: ' + SESSION_TIMEOUT + ' minutes');
</script>

</body>
</html>