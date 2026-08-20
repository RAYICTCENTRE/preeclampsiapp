<?php
session_start();

// Check if patient is logged in
if(!isset($_SESSION['user_id']) || strtolower($_SESSION['user_type']) !== 'client'){
    header("Location: screen1.html");
    exit();
}

require_once __DIR__ . '/db_connect.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

// Fetch user info (including phone from users table)
$user_stmt = $conn->prepare("SELECT id, firstname, lastname, email, phone FROM users WHERE id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();

// Fetch profile info
$profile_stmt = $conn->prepare("
    SELECT age, nationality, district, sub_county, parish, village, nearest_health, 
           kin_name, kin_relationship, kin_contact, last_period, expected_delivery, created_at 
    FROM user_profiles 
    WHERE user_id = ?
");
if ($profile_stmt) {
    $profile_stmt->bind_param("i", $user_id);
    $profile_stmt->execute();
    $profile_result = $profile_stmt->get_result();
    $profile = $profile_result->fetch_assoc() ?? [];
    $profile_stmt->close();
}

// Fetch all symptoms records for history
$records_stmt = $conn->prepare("
    SELECT id, input_type, symptoms, blood_pressure, systolic_bp, diastolic_bp, 
           proteinuria, risk, risk_level, message, created_at 
    FROM symptoms_records 
    WHERE user_id = ? 
    ORDER BY created_at DESC
");
if ($records_stmt) {
    $records_stmt->bind_param("i", $user_id);
    $records_stmt->execute();
    $records_result = $records_stmt->get_result();
    $records = $records_result->fetch_all(MYSQLI_ASSOC);
    $records_stmt->close();
} else {
    $records = [];
}

// Calculate statistics
$total_visits = count($records);
$latest_risk = !empty($records) ? $records[0]['risk_level'] : 'No Data';
$avg_risk = 0;
$risk_counts = ['Low' => 0, 'Moderate' => 0, 'High' => 0, 'Critical' => 0];

foreach ($records as $record) {
    $risk_level = $record['risk_level'];
    if (isset($risk_counts[$risk_level])) {
        $risk_counts[$risk_level]++;
    }
    
    $risk_map = ['Low' => 1, 'Moderate' => 2, 'High' => 3, 'Critical' => 4];
    $avg_risk += $risk_map[$risk_level] ?? 0;
}
$avg_risk = $total_visits > 0 ? round($avg_risk / $total_visits, 1) : 0;

// Prepare data for charts
$risk_labels = json_encode(array_keys($risk_counts));
$risk_values = json_encode(array_values($risk_counts));

$trend_labels = [];
$trend_risks = [];
$risk_numeric = ['Low' => 1, 'Moderate' => 2, 'High' => 3, 'Critical' => 4];

$trend_records = array_reverse($records);
foreach ($trend_records as $record) {
    $trend_labels[] = date('M d', strtotime($record['created_at']));
    $trend_risks[] = $risk_numeric[$record['risk_level']] ?? 0;
}
$trend_labels_json = json_encode($trend_labels);
$trend_risks_json = json_encode($trend_risks);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MotherCare - Patient Health Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f5f7fb;
            display: flex;
            min-height: 100vh;
        }

        /* ========== LEFT SIDEBAR ========== */
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, #1a472a 0%, #0e2a1a 100%);
            color: white;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }

        .sidebar-header {
            padding: 30px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-header h2 {
            font-size: 24px;
            margin-bottom: 5px;
        }

        .sidebar-header p {
            font-size: 12px;
            opacity: 0.7;
        }

        .sidebar-nav {
            flex: 1;
            padding: 20px 0;
        }

        .nav-item {
            padding: 12px 25px;
            margin: 5px 15px;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .nav-item i {
            width: 24px;
            font-size: 18px;
        }

        .nav-item:hover {
            background: rgba(255,255,255,0.1);
            transform: translateX(5px);
        }

        .nav-item.active {
            background: #2e7d32;
            color: white;
            box-shadow: 0 4px 12px rgba(46,125,50,0.3);
        }

        .stats-mini {
            padding: 20px;
            margin-top: auto;
            border-top: 1px solid rgba(255,255,255,0.1);
        }

        .stat-mini-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 13px;
        }

        /* ========== MAIN CONTENT ========== */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 25px 30px;
            overflow-y: auto;
        }

        /* Header */
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            background: white;
            padding: 15px 25px;
            border-radius: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .page-title h1 {
            font-size: 24px;
            color: #1a472a;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .btn-logout {
            background: #dc3545;
            color: white;
            padding: 8px 20px;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.3s;
        }

        .btn-logout:hover {
            background: #c82333;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: transform 0.3s;
            border-left: 4px solid #2e7d32;
        }

        .stat-card:hover {
            transform: translateY(-3px);
        }

        .stat-card .stat-icon {
            font-size: 32px;
            margin-bottom: 10px;
        }

        .stat-card .stat-number {
            font-size: 28px;
            font-weight: bold;
            color: #1a472a;
        }

        .stat-card .stat-label {
            color: #6c757d;
            font-size: 14px;
            margin-top: 5px;
        }

        /* Charts Row */
        .charts-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .chart-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .chart-card h3 {
            margin-bottom: 20px;
            color: #1a472a;
            font-size: 16px;
        }

        canvas {
            max-height: 280px;
        }

        /* Sections */
        .section {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .section h3 {
            margin-bottom: 20px;
            color: #1a472a;
            font-size: 18px;
            border-left: 4px solid #2e7d32;
            padding-left: 12px;
        }

        .profile-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 15px;
        }

        .profile-item {
            padding: 12px;
            background: #f8f9fa;
            border-radius: 10px;
        }

        .profile-item strong {
            color: #2e7d32;
            display: block;
            font-size: 12px;
            margin-bottom: 5px;
        }

        .profile-item span {
            font-size: 14px;
            color: #333;
        }

        /* Table */
        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ecf0f1;
        }

        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #1a472a;
        }

        .risk-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .risk-low { background: #d4edda; color: #155724; }
        .risk-moderate { background: #fff3cd; color: #856404; }
        .risk-high { background: #f8d7da; color: #721c24; }
        .risk-critical { background: #dc3545; color: white; }

        .btn-excel, .btn-print {
            background: #2e7d32;
            color: white;
            padding: 8px 15px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
            margin-left: 10px;
            border: none;
            cursor: pointer;
        }

        .btn-print {
            background: #6c757d;
        }

        .search-box {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            width: 250px;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 80px;
            }
            .sidebar-header h2, .sidebar-header p, .nav-item span, .stats-mini {
                display: none;
            }
            .nav-item {
                justify-content: center;
            }
            .main-content {
                margin-left: 80px;
                padding: 15px;
            }
            .charts-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<!-- LEFT SIDEBAR -->
<div class="sidebar">
    <div class="sidebar-header">
        <h2>🏥 MotherCare</h2>
        <p>Patient Portal</p>
    </div>
    
    <div class="sidebar-nav">
        <div class="nav-item active" onclick="scrollToSection('overview')">
            <i class="fas fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </div>
        <div class="nav-item" onclick="scrollToSection('profile')">
            <i class="fas fa-user-circle"></i>
            <span>My Profile</span>
        </div>
        <div class="nav-item" onclick="scrollToSection('visits')">
            <i class="fas fa-history"></i>
            <span>Visit History</span>
        </div>
        <div class="nav-item" onclick="scrollToSection('analytics')">
            <i class="fas fa-chart-line"></i>
            <span>Health Analytics</span>
        </div>
    </div>
    
    <div class="stats-mini">
        <div class="stat-mini-item">
            <span><i class="fas fa-calendar-check"></i> Visits</span>
            <strong><?= $total_visits ?></strong>
        </div>
        <div class="stat-mini-item">
            <span><i class="fas fa-heartbeat"></i> Risk Level</span>
            <strong><?= $latest_risk ?></strong>
        </div>
        <div class="stat-mini-item">
            <span><i class="fas fa-baby"></i> Due Date</span>
            <strong><?= $profile['expected_delivery'] ? date('M d', strtotime($profile['expected_delivery'])) : 'N/A' ?></strong>
        </div>
    </div>
</div>

<!-- MAIN CONTENT -->
<div class="main-content">
    <div class="top-bar">
        <div class="page-title">
            <h1>Welcome, <?= htmlspecialchars($user['firstname'] ?? 'Patient') ?>! 👋</h1>
        </div>
        <div class="user-info">
            <span><i class="fas fa-user-circle"></i> <?= htmlspecialchars($user['firstname'] ?? 'Patient') ?></span>
            <a href="screen2.html" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <!-- STATS CARDS -->
    <div class="stats-grid" id="overview">
        <div class="stat-card">
            <div class="stat-icon">📋</div>
            <div class="stat-number"><?= $total_visits ?></div>
            <div class="stat-label">Total Health Visits</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">⚠️</div>
            <div class="stat-number"><?= $avg_risk ?></div>
            <div class="stat-label">Average Risk Score (1-4)</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">❤️</div>
            <div class="stat-number"><?= $latest_risk ?></div>
            <div class="stat-label">Latest Risk Level</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">👶</div>
            <div class="stat-number"><?= $profile['expected_delivery'] ? date('M d, Y', strtotime($profile['expected_delivery'])) : 'N/A' ?></div>
            <div class="stat-label">Expected Delivery</div>
        </div>
    </div>

    <!-- CHARTS SECTION -->
    <div class="charts-row" id="analytics">
        <div class="chart-card">
            <h3><i class="fas fa-chart-pie"></i> Risk Level Distribution</h3>
            <canvas id="riskPieChart"></canvas>
        </div>
        <div class="chart-card">
            <h3><i class="fas fa-chart-line"></i> Health Trend Over Time</h3>
            <canvas id="riskTrendChart"></canvas>
        </div>
    </div>

    <!-- PROFILE SECTION -->
    <div class="section" id="profile">
        <h3><i class="fas fa-user-circle"></i> Personal Information</h3>
        <div class="profile-grid">
            <div class="profile-item"><strong>Full Name</strong><span><?= htmlspecialchars($user['firstname'] . ' ' . $user['lastname']) ?></span></div>
            <div class="profile-item"><strong>Email</strong><span><?= htmlspecialchars($user['email'] ?? 'N/A') ?></span></div>
            <div class="profile-item"><strong>Phone</strong><span><?= htmlspecialchars($user['phone'] ?? 'N/A') ?></span></div>
            <div class="profile-item"><strong>Age</strong><span><?= $profile['age'] ?? 'N/A' ?></span></div>
            <div class="profile-item"><strong>Nationality</strong><span><?= htmlspecialchars($profile['nationality'] ?? 'N/A') ?></span></div>
            <div class="profile-item"><strong>District</strong><span><?= htmlspecialchars($profile['district'] ?? 'N/A') ?></span></div>
            <div class="profile-item"><strong>Sub County</strong><span><?= htmlspecialchars($profile['sub_county'] ?? 'N/A') ?></span></div>
            <div class="profile-item"><strong>Parish</strong><span><?= htmlspecialchars($profile['parish'] ?? 'N/A') ?></span></div>
            <div class="profile-item"><strong>Village</strong><span><?= htmlspecialchars($profile['village'] ?? 'N/A') ?></span></div>
            <div class="profile-item"><strong>Nearest Clinic</strong><span><?= htmlspecialchars($profile['nearest_health'] ?? 'N/A') ?></span></div>
            <div class="profile-item"><strong>Next of Kin</strong><span><?= htmlspecialchars($profile['kin_name'] ?? 'N/A') ?></span></div>
            <div class="profile-item"><strong>Kin Relationship</strong><span><?= htmlspecialchars($profile['kin_relationship'] ?? 'N/A') ?></span></div>
            <div class="profile-item"><strong>Kin Contact</strong><span><?= htmlspecialchars($profile['kin_contact'] ?? 'N/A') ?></span></div>
            <div class="profile-item"><strong>Last Period</strong><span><?= $profile['last_period'] ?? 'N/A' ?></span></div>
        </div>
    </div>

    <!-- VISIT HISTORY -->
    <div class="section" id="visits">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
            <h3 style="margin: 0;"><i class="fas fa-history"></i> Health Visit History</h3>
            <div>
                <input type="text" class="search-box" id="searchInput" placeholder="🔍 Search by symptoms...">
                <button class="btn-excel" onclick="exportData()"><i class="fas fa-file-excel"></i> Export</button>
                <button class="btn-print" onclick="window.print()"><i class="fas fa-print"></i> Print</button>
            </div>
        </div>
        <div class="table-container">
            <table id="visitsTable">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Symptoms</th>
                        <th>BP</th>
                        <th>Protein</th>
                        <th>Risk Level</th>
                        <th>Message</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($records)): ?>
                        <?php foreach ($records as $record): ?>
                            <tr>
                                <td><?= date('M d, Y H:i', strtotime($record['created_at'])) ?></td>
                                <td><?= htmlspecialchars($record['input_type'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars(substr($record['symptoms'] ?? '', 0, 50)) ?>...</td>
                                <td><?= htmlspecialchars($record['blood_pressure'] ?? $record['systolic_bp'] . '/' . $record['diastolic_bp']) ?></td>
                                <td><?= htmlspecialchars($record['proteinuria'] ?? 'N/A') ?></td>
                                <td>
                                    <span class="risk-badge risk-<?= strtolower($record['risk_level'] ?? 'low') ?>">
                                        <?= htmlspecialchars($record['risk_level'] ?? 'N/A') ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars(substr($record['message'] ?? '', 0, 40)) ?>...</td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center;">No health records found. Complete a health assessment to see your history.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Scroll to section function
function scrollToSection(sectionId) {
    const element = document.getElementById(sectionId);
    if(element) {
        element.scrollIntoView({ behavior: 'smooth' });
    }
    document.querySelectorAll('.nav-item').forEach(item => {
        item.classList.remove('active');
    });
    event.currentTarget.classList.add('active');
}

// Chart data from PHP
const riskLabels = <?= $risk_labels ?>;
const riskValues = <?= $risk_values ?>;
const trendLabels = <?= $trend_labels_json ?>;
const trendRisks = <?= $trend_risks_json ?>;

// Risk Distribution Pie Chart
if (riskLabels.length > 0 && riskValues.some(v => v > 0)) {
    const ctxPie = document.getElementById('riskPieChart').getContext('2d');
    new Chart(ctxPie, {
        type: 'pie',
        data: {
            labels: riskLabels,
            datasets: [{
                data: riskValues,
                backgroundColor: ['#28a745', '#ffc107', '#fd7e14', '#dc3545'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom', labels: { font: { size: 12 } } }
            }
        }
    });
} else {
    document.getElementById('riskPieChart').parentElement.innerHTML += '<p style="text-align:center; color:#999; margin-top:20px;">No data available yet</p>';
}

// Health Trend Line Chart
if (trendLabels.length > 0 && trendRisks.length > 0) {
    const ctxLine = document.getElementById('riskTrendChart').getContext('2d');
    new Chart(ctxLine, {
        type: 'line',
        data: {
            labels: trendLabels,
            datasets: [{
                label: 'Risk Level (1=Low, 2=Moderate, 3=High, 4=Critical)',
                data: trendRisks,
                borderColor: '#2e7d32',
                backgroundColor: 'rgba(46,125,50,0.1)',
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#2e7d32',
                pointBorderColor: 'white',
                pointRadius: 5
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    min: 0,
                    max: 5,
                    ticks: {
                        stepSize: 1,
                        callback: function(value) {
                            const levels = ['', 'Low', 'Moderate', 'High', 'Critical'];
                            return levels[value] || value;
                        }
                    }
                }
            }
        }
    });
} else {
    document.getElementById('riskTrendChart').parentElement.innerHTML += '<p style="text-align:center; color:#999; margin-top:20px;">No data available yet</p>';
}

// Search functionality
document.getElementById('searchInput')?.addEventListener('keyup', function() {
    const searchValue = this.value.toLowerCase();
    const rows = document.querySelectorAll('#visitsTable tbody tr');
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchValue) ? '' : 'none';
    });
});

// Export data to CSV
function exportData() {
    const rows = document.querySelectorAll('#visitsTable tbody tr');
    let csv = 'Date,Type,Symptoms,BP,Protein,Risk Level,Message\n';
    
    rows.forEach(row => {
        if (row.style.display !== 'none') {
            const cells = row.querySelectorAll('td');
            const rowData = Array.from(cells).map(cell => {
                let text = cell.textContent.trim();
                if (cell.querySelector('.risk-badge')) {
                    text = cell.querySelector('.risk-badge').textContent;
                }
                return `"${text.replace(/"/g, '""')}"`;
            }).join(',');
            csv += rowData + '\n';
        }
    });
    
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'health_history.csv';
    a.click();
    URL.revokeObjectURL(url);
}
</script>

</body>
</html>