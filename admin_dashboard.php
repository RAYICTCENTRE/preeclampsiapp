<?php
session_start();
require_once __DIR__ . '/db_connect.php';

// Check admin access
if(!isset($_SESSION['user_id']) || strtolower($_SESSION['user_type']) !== 'admin'){
    header("Location: screen1.html");
    exit();
}

// Handle actions
if(isset($_GET['action'])){
    $action = $_GET['action'];
    if($action=='logout'){
        session_destroy();
        header("Location: screen1.html");
        exit();
    } elseif(isset($_GET['id'])){
        $id = intval($_GET['id']);
        if($action=='approve'){
            $conn->query("UPDATE users SET approved=1 WHERE id=$id");
        } elseif($action=='delete'){
            $conn->query("DELETE FROM users WHERE id=$id");
        } elseif($action=='reject'){
            $conn->query("UPDATE users SET approved=2 WHERE id=$id");
        }
        header("Location: admin_dashboard.php");
        exit();
    }
}

// Export CSV
if(isset($_GET['export_excel'])){
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename=users.csv');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID','Firstname','Lastname','Email','User Type','Phone','Status','Approved','Specialty','Facility','Contact']);

    $res = $conn->query("
        SELECT u.*, d.specialty, d.facility, d.dcontact 
        FROM users u
        LEFT JOIN doctors d ON u.id = d.user_id
    ");

    if($res){
        while($row = $res->fetch_assoc()){
            fputcsv($out, [
                $row['id'] ?? '',
                $row['firstname'] ?? '',
                $row['lastname'] ?? '',
                $row['email'] ?? '',
                $row['user_type'] ?? '',
                $row['phone'] ?? '',
                $row['status'] ?? '',
                $row['approved'] ?? '',
                $row['specialty'] ?? '',
                $row['facility'] ?? '',
                $row['dcontact'] ?? ''
            ]);
        }
    }
    fclose($out);
    exit();
}

// Fetch all users (patients are those with user_type='client')
$all_users = $conn->query("
    SELECT u.*, d.specialty, d.facility, d.dcontact 
    FROM users u
    LEFT JOIN doctors d ON u.id = d.user_id
    ORDER BY u.id DESC
") ?: [];

// Patients = users with user_type='client'
$patients = $conn->query("SELECT * FROM users WHERE user_type='client'") ?: [];

// Pending doctors (approved = 0)
$pending_doctors = $conn->query("
    SELECT u.*, d.specialty, d.facility, d.dcontact
    FROM users u
    LEFT JOIN doctors d ON u.id = d.user_id
    WHERE u.user_type='doctor' AND u.approved=0
") ?: [];

// Approved doctors (approved = 1)
$approved_doctors = $conn->query("
    SELECT u.*, d.specialty, d.facility, d.dcontact
    FROM users u
    LEFT JOIN doctors d ON u.id = d.user_id
    WHERE u.user_type='doctor' AND u.approved=1
") ?: [];

// Statistics
$total_users = $all_users->num_rows ?? 0;
$total_patients = $patients->num_rows ?? 0;
$total_doctors = $approved_doctors->num_rows ?? 0;
$pending_count = $pending_doctors->num_rows ?? 0;

// Message statistics
$total_messages = $conn->query("SELECT COUNT(*) as cnt FROM messages")->fetch_assoc()['cnt'] ?? 0;
$unread_messages = $conn->query("SELECT COUNT(*) as cnt FROM messages WHERE status='sent'")->fetch_assoc()['cnt'] ?? 0;

// Recent activity (last 5 messages)
$recent_messages = $conn->query("
    SELECT m.*, u.firstname, u.lastname, u2.firstname as receiver_firstname, u2.lastname as receiver_lastname
    FROM messages m
    JOIN users u ON m.sender_id = u.id
    JOIN users u2 ON m.receiver_id = u2.id
    ORDER BY m.created_at DESC LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MotherCare Admin Dashboard</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Segoe UI', 'Poppins', sans-serif;
        background: #f5f7fb;
        display: flex;
        height: 100vh;
        overflow: hidden;
    }

    /* ========== LEFT PANEL ========== */
    .sidebar {
        width: 280px;
        background: linear-gradient(180deg, #2c3e50 0%, #1a2634 100%);
        color: white;
        display: flex;
        flex-direction: column;
        overflow-y: auto;
        box-shadow: 2px 0 10px rgba(0,0,0,0.1);
    }

    .sidebar-header {
        padding: 25px 20px;
        text-align: center;
        border-bottom: 1px solid rgba(255,255,255,0.1);
    }

    .sidebar-header h2 {
        font-size: 22px;
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
        background: #ff8c42;
        color: white;
        box-shadow: 0 4px 12px rgba(255,140,66,0.3);
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
        overflow-y: auto;
        padding: 25px 30px;
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
        color: #2c3e50;
    }

    .admin-info {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .btn-logout {
        background: #ff6b6b;
        color: white;
        padding: 8px 20px;
        border-radius: 8px;
        text-decoration: none;
        transition: all 0.3s;
    }

    .btn-logout:hover {
        background: #ff4757;
    }

    /* Stats Cards */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        padding: 20px;
        border-radius: 15px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        transition: transform 0.3s;
        border-left: 4px solid;
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
        color: #2c3e50;
    }

    .stat-card .stat-label {
        color: #7f8c8d;
        font-size: 14px;
        margin-top: 5px;
    }

    /* Tables */
    .section {
        background: white;
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 30px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .section h3 {
        margin-bottom: 20px;
        color: #2c3e50;
        font-size: 18px;
        border-left: 4px solid #ff8c42;
        padding-left: 12px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        overflow-x: auto;
        display: block;
    }

    th, td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #ecf0f1;
    }

    th {
        background: #f8f9fa;
        font-weight: 600;
        color: #2c3e50;
    }

    tr:hover {
        background: #f8f9fa;
    }

    .badge {
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .badge-approved {
        background: #d4edda;
        color: #155724;
    }

    .badge-pending {
        background: #fff3cd;
        color: #856404;
    }

    .badge-rejected {
        background: #f8d7da;
        color: #721c24;
    }

    .action-btn {
        padding: 4px 8px;
        border-radius: 5px;
        text-decoration: none;
        font-size: 12px;
        margin: 0 2px;
        display: inline-block;
    }

    .btn-approve {
        background: #28a745;
        color: white;
    }

    .btn-delete {
        background: #dc3545;
        color: white;
    }

    .btn-reject {
        background: #ffc107;
        color: #856404;
    }

    .btn-excel, .btn-print, .btn-db {
        background: #6c757d;
        color: white;
        padding: 8px 15px;
        border-radius: 6px;
        text-decoration: none;
        font-size: 13px;
        margin-left: 10px;
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
            padding: 15px;
        }
    }
</style>
</head>
<body>

<!-- LEFT PANEL -->
<div class="sidebar">
    <div class="sidebar-header">
        <h2>🏥 MotherCare</h2>
        <p>Admin Panel</p>
    </div>
    
    <div class="sidebar-nav">
        <div class="nav-item active" onclick="scrollToSection('overview')">
            <i class="fas fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </div>
        <div class="nav-item" onclick="scrollToSection('users')">
            <i class="fas fa-users"></i>
            <span>All Users</span>
        </div>
        <div class="nav-item" onclick="scrollToSection('patients')">
            <i class="fas fa-user-injured"></i>
            <span>Patients</span>
        </div>
        <div class="nav-item" onclick="scrollToSection('pending')">
            <i class="fas fa-clock"></i>
            <span>Pending Approvals</span>
        </div>
        <div class="nav-item" onclick="scrollToSection('doctors')">
            <i class="fas fa-user-md"></i>
            <span>Approved Doctors</span>
        </div>
        <div class="nav-item" onclick="scrollToSection('messages')">
            <i class="fas fa-envelope"></i>
            <span>Recent Activity</span>
        </div>
    </div>
    
    <div class="stats-mini">
        <div class="stat-mini-item">
            <span><i class="fas fa-users"></i> Total Users</span>
            <strong><?= $total_users ?></strong>
        </div>
        <div class="stat-mini-item">
            <span><i class="fas fa-user-md"></i> Doctors</span>
            <strong><?= $total_doctors ?></strong>
        </div>
        <div class="stat-mini-item">
            <span><i class="fas fa-envelope"></i> Messages</span>
            <strong><?= $total_messages ?></strong>
        </div>
    </div>
</div>

<!-- MAIN CONTENT -->
<div class="main-content">
    <div class="top-bar">
        <div class="page-title">
            <h1>Admin Dashboard</h1>
        </div>
        <div class="admin-info">
            <span><i class="fas fa-user-shield"></i> Admin</span>
            <a href="?action=logout" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <!-- STATS CARDS -->
    <div class="stats-grid" id="overview">
        <div class="stat-card" style="border-left-color: #ff8c42;">
            <div class="stat-icon"><i class="fas fa-users" style="color: #ff8c42;"></i></div>
            <div class="stat-number"><?= $total_users ?></div>
            <div class="stat-label">Total Registered Users</div>
        </div>
        <div class="stat-card" style="border-left-color: #28a745;">
            <div class="stat-icon"><i class="fas fa-user-injured" style="color: #28a745;"></i></div>
            <div class="stat-number"><?= $total_patients ?></div>
            <div class="stat-label">Total Patients</div>
        </div>
        <div class="stat-card" style="border-left-color: #17a2b8;">
            <div class="stat-icon"><i class="fas fa-user-md" style="color: #17a2b8;"></i></div>
            <div class="stat-number"><?= $total_doctors ?></div>
            <div class="stat-label">Approved Doctors</div>
        </div>
        <div class="stat-card" style="border-left-color: #ffc107;">
            <div class="stat-icon"><i class="fas fa-hourglass-half" style="color: #ffc107;"></i></div>
            <div class="stat-number"><?= $pending_count ?></div>
            <div class="stat-label">Pending Approvals</div>
        </div>
        <div class="stat-card" style="border-left-color: #6c5ce7;">
            <div class="stat-icon"><i class="fas fa-envelope" style="color: #6c5ce7;"></i></div>
            <div class="stat-number"><?= $total_messages ?></div>
            <div class="stat-label">Total Messages</div>
        </div>
        <div class="stat-card" style="border-left-color: #e84393;">
            <div class="stat-icon"><i class="fas fa-eye" style="color: #e84393;"></i></div>
            <div class="stat-number"><?= $unread_messages ?></div>
            <div class="stat-label">Unread Messages</div>
        </div>
    </div>

    <!-- Export Buttons -->
    <div style="margin-bottom: 20px; text-align: right;">
        <a href="?export_excel=1" class="btn-excel"><i class="fas fa-file-excel"></i> Export Excel</a>
        <button onclick="window.print()" class="btn-print"><i class="fas fa-print"></i> Print</button>
        <a href="https://railway.com/dashboard" target="_blank" rel="noopener noreferrer" class="btn-db"><i class="fas fa-database"></i> Railway Database</a>
    </div>

    <!-- ALL USERS -->
    <div class="section" id="users">
        <h3><i class="fas fa-users"></i> All Registered Users</h3>
        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>ID</th><th>Name</th><th>Email</th><th>Type</th><th>Phone</th><th>Status</th><th>Approved</th><th>Specialty</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if($all_users && $all_users->num_rows > 0): 
                    $all_users->data_seek(0);
                    while($u = $all_users->fetch_assoc()): ?>
                    <tr>
                        <td><?= $u['id'] ?></td>
                        <td><?= htmlspecialchars($u['firstname'] . ' ' . $u['lastname']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><?= ucfirst($u['user_type']) ?></td>
                        <td><?= htmlspecialchars($u['phone'] ?? 'N/A') ?></td>
                        <td><?= $u['status'] ?? 'Active' ?></td>
                        <td>
                            <?php if($u['user_type'] == 'doctor'): ?>
                                <span class="badge <?= $u['approved'] == 1 ? 'badge-approved' : ($u['approved'] == 2 ? 'badge-rejected' : 'badge-pending') ?>">
                                    <?= $u['approved'] == 1 ? 'Approved' : ($u['approved'] == 2 ? 'Rejected' : 'Pending') ?>
                                </span>
                            <?php else: ?>
                                <span class="badge badge-approved">Active</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($u['specialty'] ?? 'N/A') ?></td>
                        <td>
                            <a href="?action=delete&id=<?= $u['id'] ?>" class="action-btn btn-delete" onclick="return confirm('Delete user?')">Delete</a>
                            <?php if($u['user_type'] == 'doctor' && $u['approved'] == 0): ?>
                                <a href="?action=approve&id=<?= $u['id'] ?>" class="action-btn btn-approve">Approve</a>
                                <a href="?action=reject&id=<?= $u['id'] ?>" class="action-btn btn-reject" onclick="return confirm('Reject this doctor?')">Reject</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; else: ?>
                    <tr><td colspan="9">No users found</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- PATIENTS (from users table) -->
    <div class="section" id="patients">
        <h3><i class="fas fa-user-injured"></i> Patients</h3>
        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Registered</th>
                    </tr>
                </thead>
                <tbody>
                <?php if($patients && $patients->num_rows > 0): 
                    while($p = $patients->fetch_assoc()): ?>
                    <tr>
                        <td><?= $p['id'] ?></td>
                        <td><?= htmlspecialchars($p['firstname'] . ' ' . $p['lastname']) ?></td>
                        <td><?= htmlspecialchars($p['email']) ?></td>
                        <td><?= htmlspecialchars($p['phone'] ?? 'N/A') ?></td>
                        <td><?= date('M d, Y', strtotime($p['created_at'])) ?></td>
                    </tr>
                <?php endwhile; else: ?>
                    <tr><td colspan="5">No patients found</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- PENDING DOCTORS -->
    <div class="section" id="pending">
        <h3><i class="fas fa-clock"></i> Pending Doctor Approvals</h3>
        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Specialty</th><th>Facility</th><th>Contact</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if($pending_doctors && $pending_doctors->num_rows > 0): 
                    while($doc = $pending_doctors->fetch_assoc()): ?>
                    <tr>
                        <td><?= $doc['id'] ?></td>
                        <td><?= htmlspecialchars($doc['firstname'] . ' ' . $doc['lastname']) ?></td>
                        <td><?= htmlspecialchars($doc['email']) ?></td>
                        <td><?= htmlspecialchars($doc['phone'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($doc['specialty'] ?? 'Not specified') ?></td>
                        <td><?= htmlspecialchars($doc['facility'] ?? 'Not specified') ?></td>
                        <td><?= htmlspecialchars($doc['dcontact'] ?? 'N/A') ?></td>
                        <td>
                            <a href="?action=approve&id=<?= $doc['id'] ?>" class="action-btn btn-approve">Approve</a>
                            <a href="?action=delete&id=<?= $doc['id'] ?>" class="action-btn btn-delete" onclick="return confirm('Delete?')">Delete</a>
                            <a href="?action=reject&id=<?= $doc['id'] ?>" class="action-btn btn-reject" onclick="return confirm('Reject?')">Reject</a>
                        </td>
                    </tr>
                <?php endwhile; else: ?>
                    <tr><td colspan="8">No pending doctors</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- APPROVED DOCTORS -->
    <div class="section" id="doctors">
        <h3><i class="fas fa-user-md"></i> Approved Doctors</h3>
        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>ID</th><th>Name</th><th>Email</th><th>Specialty</th><th>Facility</th><th>Contact</th>
                    </tr>
                </thead>
                <tbody>
                <?php if($approved_doctors && $approved_doctors->num_rows > 0): 
                    while($doc = $approved_doctors->fetch_assoc()): ?>
                    <tr>
                        <td><?= $doc['id'] ?></td>
                        <td>Dr. <?= htmlspecialchars($doc['firstname'] . ' ' . $doc['lastname']) ?></td>
                        <td><?= htmlspecialchars($doc['email']) ?></td>
                        <td><?= htmlspecialchars($doc['specialty'] ?? 'General Practitioner') ?></td>
                        <td><?= htmlspecialchars($doc['facility'] ?? 'Not specified') ?></td>
                        <td><?= htmlspecialchars($doc['dcontact'] ?? 'N/A') ?></td>
                    </tr>
                <?php endwhile; else: ?>
                    <tr><td colspan="6">No approved doctors</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- RECENT ACTIVITY -->
    <div class="section" id="messages">
        <h3><i class="fas fa-history"></i> Recent Messages</h3>
        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>From</th><th>To</th><th>Message</th><th>Status</th><th>Time</th>
                    </tr>
                </thead>
                <tbody>
                <?php if($recent_messages && $recent_messages->num_rows > 0): 
                    while($msg = $recent_messages->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($msg['firstname'] . ' ' . $msg['lastname']) ?></td>
                        <td><?= htmlspecialchars($msg['receiver_firstname'] . ' ' . $msg['receiver_lastname']) ?></td>
                        <td><?= htmlspecialchars(substr($msg['message'], 0, 50)) ?>...</td>
                        <td><span class="badge <?= $msg['status'] == 'read' ? 'badge-approved' : 'badge-pending' ?>"><?= ucfirst($msg['status']) ?></span></td>
                        <td><?= date('M d, H:i', strtotime($msg['created_at'])) ?></td>
                    </tr>
                <?php endwhile; else: ?>
                    <tr><td colspan="5">No messages yet</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function scrollToSection(sectionId) {
    const element = document.getElementById(sectionId);
    if(element) {
        element.scrollIntoView({ behavior: 'smooth' });
    }
    // Update active nav item
    document.querySelectorAll('.nav-item').forEach(item => {
        item.classList.remove('active');
    });
    event.currentTarget.classList.add('active');
}
</script>

</body>
</html>
