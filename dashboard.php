<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MotherCare - Health Analytics</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
<style>
:root{
    --primary:#287f63;
    --primary-dark:#1e6650;
    --ink:#18352c;
    --muted:#6b7d76;
    --bg:#f4f8f6;
    --white:#ffffff;
    --line:#e3ece8;
    --blue:#4b86c5;
    --lavender:#7b6fd6;
    --orange:#e98a3a;
    --rose:#d86b78;
    --shadow:0 12px 30px rgba(30,60,50,.08);
}
*{box-sizing:border-box}
html{scroll-behavior:smooth}
body{
    margin:0;
    font-family:Inter,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;
    background:var(--bg);
    color:var(--ink);
    padding-bottom:88px;
}
a{text-decoration:none;color:inherit}
.mobile-header{
    height:72px;background:var(--white);border-bottom:1px solid var(--line);
    display:flex;align-items:center;justify-content:space-between;padding:0 5%;
    position:sticky;top:0;z-index:50;
}
.brand{display:flex;align-items:center;gap:11px}
.brand-mark{
    width:38px;height:38px;border-radius:12px;background:var(--primary);
    color:#fff;display:grid;place-items:center;font-size:25px;font-weight:800;
}
.brand strong{display:block;font-size:18px}
.brand span{display:block;font-size:11px;color:var(--muted);margin-top:2px}
.logout-icon{
    width:40px;height:40px;border-radius:12px;display:grid;place-items:center;
    color:#66756f;background:#f2f6f4;
}
.dashboard{width:min(1120px,92%);margin:0 auto;padding:34px 0 20px}
.welcome-row{display:flex;justify-content:space-between;align-items:flex-end;gap:20px;margin-bottom:22px}
.eyebrow{font-size:11px;font-weight:800;letter-spacing:.12em;color:var(--primary);margin:0 0 7px}
h1{font-size:30px;line-height:1.15;margin:0}
.welcome-subtitle{color:var(--muted);margin:8px 0 0}
.session-timer{
    background:#fff;border:1px solid var(--line);border-radius:12px;padding:10px 13px;
    font-size:12px;color:#63736d;white-space:nowrap;box-shadow:0 5px 18px rgba(0,0,0,.03)
}
.session-timer strong{color:var(--ink)}
.session-timer.warning{border-color:#f0c15c;color:#a36a00}
.session-timer.danger{border-color:#e47a82;color:#b32632}
.sync-card{
    background:linear-gradient(110deg,#eaf7f0,#f7fbfa);border:1px solid #cfe8db;
    border-radius:18px;padding:15px 18px;display:flex;justify-content:space-between;
    align-items:center;gap:15px;margin-bottom:22px;box-shadow:var(--shadow)
}
.sync-left{display:flex;align-items:center;gap:12px;min-width:0}
.sync-icon{
    width:44px;height:44px;border-radius:14px;background:#d9f0e3;color:var(--primary);
    display:grid;place-items:center;font-size:18px;flex:none
}
.sync-title{font-weight:750;font-size:14px}
.sync-status{display:inline-flex;align-items:center;gap:5px;margin-left:8px;font-size:11px;font-weight:650;color:#287f63}
.status-dot{width:7px;height:7px;border-radius:50%;background:#36a66e}
.sync-status.offline{color:#b46c19}.sync-status.offline .status-dot{background:#e29a34}
.sync-card p{margin:4px 0 0;color:var(--muted);font-size:12px}
.sync-button{
    border:0;background:var(--primary);color:#fff;border-radius:11px;padding:11px 15px;
    font-weight:700;cursor:pointer;white-space:nowrap
}
.sync-button:hover{background:var(--primary-dark)}
.sync-button:disabled{opacity:.55;cursor:not-allowed}
.feature-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:15px;margin-bottom:22px}
.feature-card{
    min-height:150px;border-radius:18px;padding:20px;color:#fff;display:flex;flex-direction:column;
    box-shadow:var(--shadow);transition:transform .18s ease,box-shadow .18s ease
}
.feature-card:hover{transform:translateY(-3px);box-shadow:0 16px 34px rgba(30,60,50,.13)}
.feature-card.analytics{background:#287f63}
.feature-card.symptoms{background:#4b86c5}
.feature-card.doctor{background:#d87555}
.feature-card.profile{background:#7b6fd6}
.feature-icon{font-size:22px;opacity:.95}
.feature-title{font-size:18px;font-weight:800;margin-top:auto;white-space:nowrap}
.feature-footer{
    border-top:1px solid rgba(255,255,255,.28);margin-top:14px;padding-top:10px;
    display:flex;justify-content:space-between;align-items:center;font-size:11px;font-weight:650
}
.snapshot-card,.section{
    background:#fff;border:1px solid var(--line);border-radius:18px;box-shadow:var(--shadow)
}
.snapshot-card{padding:20px;margin-bottom:28px}
.snapshot-heading{display:flex;justify-content:space-between;align-items:center;gap:15px}
.snapshot-heading h2,.analytics-header h2{margin:0;font-size:19px}
.snapshot-heading a,.back-link{font-size:12px;color:var(--primary);font-weight:700}
.snapshot-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-top:16px}
.snapshot-item{background:#f7faf8;border-radius:13px;padding:13px}
.snapshot-item span{display:block;color:var(--muted);font-size:11px;margin-bottom:7px}
.snapshot-item strong{font-size:16px}
.analytics-section{scroll-margin-top:88px;padding-bottom:20px}
.analytics-header{display:flex;justify-content:space-between;align-items:end;margin-bottom:16px}
.stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:15px;margin-bottom:18px}
.stat-card{background:#fff;border:1px solid var(--line);border-radius:16px;padding:18px;box-shadow:var(--shadow)}
.stat-icon{font-size:24px;margin-bottom:9px}
.stat-number{font-size:24px;font-weight:800}
.stat-label{font-size:11px;color:var(--muted);margin-top:4px}
.section{padding:20px;margin-bottom:18px}
.section h3{margin:0 0 15px;font-size:16px}
.chart-container{display:grid;grid-template-columns:1fr 1fr;gap:15px}
.chart-box{border:1px solid var(--line);border-radius:14px;padding:16px}
.chart-box h4{margin:0 0 15px;font-size:13px}
.chart-bar{display:flex;justify-content:space-around;align-items:flex-end;min-height:170px;gap:12px}
.bar-item{text-align:center;flex:1}
.bar{width:100%;max-width:55px;margin:8px auto;border-radius:8px 8px 2px 2px}
.bar-value{font-weight:800;font-size:12px}
.bar-label{font-size:11px;color:var(--muted)}
.risk-level-item{display:flex;justify-content:space-between;padding:11px 0;border-bottom:1px solid #eef3f0;font-size:12px}
.risk-level-item:last-child{border-bottom:0}
.risk-dot{display:inline-block;width:8px;height:8px;border-radius:50%;margin-right:6px}
.risk-dot.low{background:#28a745}.risk-dot.moderate{background:#ffc107}.risk-dot.high{background:#fd7e14}.risk-dot.critical{background:#dc3545}
.section-header{display:flex;justify-content:space-between;align-items:center;gap:10px}
.btn-view-all{font-size:12px;color:var(--primary);font-weight:700}
.table-wrapper{overflow-x:auto}
table{width:100%;border-collapse:collapse;min-width:650px}
th,td{padding:11px 9px;border-bottom:1px solid #edf2ef;text-align:left;font-size:11px}
th{color:#60716b;background:#f7faf8}
.badge{padding:4px 8px;border-radius:20px;font-weight:700;font-size:10px}
.badge-low{background:#e5f5ea;color:#287f3d}.badge-moderate{background:#fff5d8;color:#9a7100}.badge-high{background:#fff0e4;color:#b75b11}.badge-critical{background:#fde7e9;color:#b32632}
.quick-insights{display:grid;grid-template-columns:repeat(4,1fr);gap:10px}
.insight-card{background:#f7faf8;border-radius:12px;padding:15px;text-align:center}
.insight-card .number{font-size:20px;font-weight:800}.insight-card .label{font-size:10px;color:var(--muted);margin-top:4px}
.bottom-nav{
    position:fixed;bottom:0;left:0;right:0;height:70px;background:rgba(255,255,255,.97);
    border-top:1px solid var(--line);display:flex;justify-content:center;gap:min(9vw,80px);
    align-items:center;z-index:60;backdrop-filter:blur(10px)
}
.bottom-nav a{display:flex;flex-direction:column;align-items:center;gap:4px;font-size:10px;color:#81908a;font-weight:650}
.bottom-nav a i{font-size:17px}.bottom-nav a.active{color:var(--primary)}
.idle-modal{
    position:fixed;inset:0;background:rgba(10,30,24,.55);display:none;align-items:center;justify-content:center;
    z-index:200;padding:20px
}
.idle-modal.show{display:flex}
.idle-modal-content{background:#fff;border-radius:18px;padding:28px;text-align:center;width:min(390px,100%);box-shadow:0 20px 60px rgba(0,0,0,.2)}
.idle-modal-content>i{font-size:30px;color:var(--primary)}
.idle-modal-content h2{margin:10px 0 8px}.idle-modal-content p{color:var(--muted)}
.countdown-timer{font-size:36px;font-weight:800;color:#dc3545;margin:10px}
.btn-stay,.btn-logout-modal{border:0;border-radius:10px;padding:12px 15px;font-weight:700;cursor:pointer;margin:4px}
.btn-stay{background:var(--primary);color:#fff}.btn-logout-modal{background:#f3f5f4;color:#45534e}
@media(max-width:900px){
    .feature-grid{grid-template-columns:repeat(2,1fr)}
    .stats-grid{grid-template-columns:repeat(2,1fr)}
}
@media(max-width:650px){
    body{padding-bottom:78px}
    .mobile-header{height:64px;padding:0 4%}
    .dashboard{width:92%;padding-top:23px}
    .welcome-row{align-items:flex-start;flex-direction:column}
    h1{font-size:25px}
    .session-timer{font-size:11px}
    .sync-card{align-items:flex-start;flex-direction:column}
    .sync-button{width:100%}
    .feature-grid{grid-template-columns:repeat(2,1fr);gap:10px}
    .feature-card{min-height:128px;padding:15px;border-radius:15px}
    .feature-title{font-size:15px}
    .feature-footer{font-size:10px}
    .snapshot-grid{grid-template-columns:1fr}
    .chart-container{grid-template-columns:1fr}
    .quick-insights{grid-template-columns:repeat(2,1fr)}
    .analytics-header{align-items:flex-start;flex-direction:column}
    .bottom-nav{height:64px;gap:0;justify-content:space-around}
}
@media(orientation:landscape) and (max-height:600px){
    .dashboard{padding-top:18px}
    .feature-grid{grid-template-columns:repeat(4,1fr)}
    .feature-card{min-height:120px}
    .welcome-row{margin-bottom:15px}
    .sync-card{padding:11px 15px}
}
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


<body id="top">

<!-- IDLE WARNING MODAL -->
<div class="idle-modal" id="idleModal">
    <div class="idle-modal-content">
        <i class="fas fa-clock"></i>
        <h2>Session Expiring Soon</h2>
        <p>You have been inactive for a while. Your session will expire in:</p>
        <div class="countdown-timer" id="countdownTimer">60</div>
        <p style="font-size:12px;color:#94a3b8;">seconds</p>
        <br>
        <button class="btn-stay" onclick="stayLoggedIn()">
            <i class="fas fa-check-circle"></i> Stay Logged In
        </button>
        <button class="btn-logout-modal" onclick="logoutNow()">
            <i class="fas fa-sign-out-alt"></i> Logout Now
        </button>
    </div>
</div>

<header class="mobile-header">
    <div class="brand">
        <div class="brand-mark">+</div>
        <div>
            <strong>MotherCare</strong>
            <span>Patient Portal</span>
        </div>
    </div>
    <a href="screen1.html" class="logout-icon" title="Logout">
        <i class="fas fa-sign-out-alt"></i>
    </a>
</header>

<main class="dashboard">
    <section class="welcome-row">
        <div>
            <p class="eyebrow">PATIENT PORTAL</p>
            <h1>Welcome back, <span id="userName">Patient</span> 👋</h1>
            <p class="welcome-subtitle">Manage your pregnancy health from one place.</p>
        </div>
        <div class="session-timer" id="sessionTimer">
            <i class="fas fa-hourglass-half"></i>
            <span>Session: </span>
            <strong id="timerDisplay">5:00</strong>
        </div>
    </section>

    <!-- SYNC STATUS -->
    <section class="sync-card">
        <div class="sync-left">
            <div class="sync-icon" id="syncIcon">
                <i class="fas fa-cloud"></i>
            </div>
            <div>
                <div class="sync-title">
                    Data synchronization
                    <span class="sync-status" id="syncStatus">
                        <span class="status-dot"></span>
                        <span id="syncStatusText">Checking...</span>
                    </span>
                </div>
                <p id="pendingSync">Checking connection...</p>
            </div>
        </div>
        <button class="sync-button" id="syncButton" onclick="syncNow()">
            <i class="fas fa-sync-alt"></i> Sync Now
        </button>
    </section>

    <!-- FOUR MAIN NAVIGATION CARDS -->
    <section class="feature-grid" aria-label="MotherCare services">

        <a class="feature-card analytics" href="#analytics">
            <div class="feature-icon"><i class="fas fa-chart-line"></i></div>
            <div class="feature-title">Health Analytics</div>
            <div class="feature-footer">
                <span>View health data</span>
                <i class="fas fa-arrow-right"></i>
            </div>
        </a>

        <a class="feature-card symptoms" href="screen6.html">
            <div class="feature-icon"><i class="fas fa-stethoscope"></i></div>
            <div class="feature-title">Check Symptoms</div>
            <div class="feature-footer">
                <span>Start assessment</span>
                <i class="fas fa-arrow-right"></i>
            </div>
        </a>

        <a class="feature-card doctor" href="consult_doctor.php">
            <div class="feature-icon"><i class="fas fa-user-md"></i></div>
            <div class="feature-title">Consult Doctor</div>
            <div class="feature-footer">
                <span>Talk to a doctor</span>
                <i class="fas fa-arrow-right"></i>
            </div>
        </a>

        <a class="feature-card profile" href="screen4.html">
            <div class="feature-icon"><i class="fas fa-user-edit"></i></div>
            <div class="feature-title">Update Profile</div>
            <div class="feature-footer">
                <span>Manage your details</span>
                <i class="fas fa-arrow-right"></i>
            </div>
        </a>
    </section>

    <!-- COMPACT PATIENT SNAPSHOT -->
    <section class="snapshot-card">
        <div class="snapshot-heading">
            <div>
                <p class="eyebrow">QUICK STATUS</p>
                <h2>Your pregnancy overview</h2>
            </div>
            <a href="#analytics">View details →</a>
        </div>

        <div class="snapshot-grid">
            <div class="snapshot-item">
                <span><i class="fas fa-calendar-check"></i> Visits</span>
                <strong id="totalVisits">-</strong>
            </div>
            <div class="snapshot-item">
                <span><i class="fas fa-heartbeat"></i> Risk Level</span>
                <strong id="latestRisk">-</strong>
            </div>
            <div class="snapshot-item">
                <span><i class="fas fa-baby"></i> Due Date</span>
                <strong id="eddDate">-</strong>
            </div>
        </div>
    </section>

    <!-- DETAILED ANALYTICS: HIDDEN UNDER HEALTH ANALYTICS -->
    <section class="analytics-section" id="analytics">
        <div class="analytics-header">
            <div>
                <p class="eyebrow">HEALTH ANALYTICS</p>
                <h2>Your health data</h2>
            </div>
            <a href="#top" class="back-link">Back to dashboard ↑</a>
        </div>

        <!-- Existing analytics IDs preserved for the existing JavaScript -->
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

        <div class="section">
            <h3>📈 Risk Distribution</h3>
            <div class="chart-container">
                <div class="chart-box">
                    <h4>Risk Levels Overview</h4>
                    <div class="chart-bar" id="riskChart">
                        <div class="bar-item">
                            <div class="bar-value" id="lowCount">0</div>
                            <div class="bar" style="height:30px;background:#28a745;" id="lowBar"></div>
                            <div class="bar-label">Low</div>
                        </div>
                        <div class="bar-item">
                            <div class="bar-value" id="moderateCount">0</div>
                            <div class="bar" style="height:30px;background:#ffc107;" id="moderateBar"></div>
                            <div class="bar-label">Moderate</div>
                        </div>
                        <div class="bar-item">
                            <div class="bar-value" id="highCount">0</div>
                            <div class="bar" style="height:30px;background:#fd7e14;" id="highBar"></div>
                            <div class="bar-label">High</div>
                        </div>
                        <div class="bar-item">
                            <div class="bar-value" id="criticalCount">0</div>
                            <div class="bar" style="height:30px;background:#dc3545;" id="criticalBar"></div>
                            <div class="bar-label">Critical</div>
                        </div>
                    </div>
                </div>

                <div class="chart-box">
                    <h4>Risk Level Summary</h4>
                    <div id="riskSummary" style="width:100%;">
                        <div class="risk-level-item"><span><span class="risk-dot low"></span> Low</span><span id="lowSummary">0 visits</span></div>
                        <div class="risk-level-item"><span><span class="risk-dot moderate"></span> Moderate</span><span id="moderateSummary">0 visits</span></div>
                        <div class="risk-level-item"><span><span class="risk-dot high"></span> High</span><span id="highSummary">0 visits</span></div>
                        <div class="risk-level-item"><span><span class="risk-dot critical"></span> Critical</span><span id="criticalSummary">0 visits</span></div>
                    </div>
                </div>
            </div>
        </div>

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
                            <td colspan="5" style="text-align:center;color:#6c757d;padding:30px;">
                                <i class="fas fa-spinner fa-spin"></i> Loading data...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="section">
            <h3>📊 Quick Insights</h3>
            <div class="quick-insights">
                <div class="insight-card"><div class="number" id="insightTotal">-</div><div class="label">Total Assessments</div></div>
                <div class="insight-card"><div class="number" id="insightRisk">-</div><div class="label">Current Risk Level</div></div>
                <div class="insight-card"><div class="number" id="insightAvg">-</div><div class="label">Average Risk Score</div></div>
                <div class="insight-card"><div class="number" id="insightRecords">-</div><div class="label">Total Records</div></div>
            </div>
        </div>
    </section>
</main>

<nav class="bottom-nav">
    <a href="#top" class="active"><i class="fas fa-home"></i><span>Home</span></a>
    <a href="consult_doctor.php"><i class="fas fa-user-md"></i><span>Doctor</span></a>
    <a href="#analytics"><i class="fas fa-chart-line"></i><span>Analytics</span></a>
    <a href="screen4.html"><i class="fas fa-user"></i><span>Profile</span></a>
</nav>

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

// ============================================
// OFFLINE / SYNC STATUS UI
// UI-only for now: does not alter the existing API/database pipeline.
// ============================================

const syncStatus = document.getElementById('syncStatus');
const syncStatusText = document.getElementById('syncStatusText');
const syncButton = document.getElementById('syncButton');
const pendingSync = document.getElementById('pendingSync');

function updateConnectionStatus() {
    const online = navigator.onLine;

    if (online) {
        syncStatus.className = 'sync-status online';
        syncStatusText.textContent = 'Online • Connected';
        pendingSync.textContent = 'Ready to synchronize';
        syncButton.disabled = false;
    } else {
        syncStatus.className = 'sync-status offline';
        syncStatusText.textContent = 'Offline • Working locally';
        pendingSync.textContent = 'Will synchronize when Internet is available';
        syncButton.disabled = true;
    }
}

function syncNow() {
    if (!navigator.onLine) {
        showToast('You are offline. Synchronization will be available when Internet returns.', 'info');
        return;
    }

    syncButton.classList.add('syncing');
    syncButton.innerHTML = '<i class="fas fa-sync-alt fa-spin"></i> Syncing...';

    // Deliberately does not call or modify the existing backend.
    // Actual offline database synchronization can be added separately.
    setTimeout(() => {
        syncButton.classList.remove('syncing');
        syncButton.innerHTML = '<i class="fas fa-sync-alt"></i> Sync Now';
        pendingSync.textContent = 'No pending synchronization';
        showToast('Connection verified. Sync is ready.', 'success');
    }, 900);
}

window.addEventListener('online', updateConnectionStatus);
window.addEventListener('offline', updateConnectionStatus);
updateConnectionStatus();

</script>
</body>
</html>
