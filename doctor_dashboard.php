<?php
session_start();

// Check if doctor is logged in
if(!isset($_SESSION['user_id']) || strtolower($_SESSION['user_type']) !== 'doctor'){
    header("Location: screen2.html");
    exit();
}

require_once __DIR__ . '/db_connect.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$doctor_id = $_SESSION['user_id'];
$doctor_name = $_SESSION['firstname'] ?? 'Doctor';

// Check doctor status
$check_stmt = $conn->prepare("SELECT approved FROM users WHERE id = ?");
$check_stmt->bind_param("i", $doctor_id);
$check_stmt->execute();
$result = $check_stmt->get_result();
$doctor = $result->fetch_assoc();
$check_stmt->close();

$is_approved = ($doctor && $doctor['approved'] == 1);

// Check if doctor has completed profile
$profile_stmt = $conn->prepare("SELECT id, specialty, facility, dcontact FROM doctors WHERE user_id = ?");
$profile_stmt->bind_param("i", $doctor_id);
$profile_stmt->execute();
$profile_result = $profile_stmt->get_result();
$has_profile = $profile_result->num_rows > 0;
$profile_data = $profile_result->fetch_assoc();
$profile_stmt->close();

$is_profile_complete = false;
if ($has_profile && !empty($profile_data['specialty']) && !empty($profile_data['facility']) && !empty($profile_data['dcontact'])) {
    $is_profile_complete = true;
}

// If not approved, show pending message
if (!$is_approved) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Doctor Dashboard - MotherCare</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background: linear-gradient(135deg, #fff5e8 0%, #ffe8d4 100%);
                min-height: 100vh;
                display: flex;
                justify-content: center;
                align-items: center;
                padding: 20px;
            }
            .pending-container {
                max-width: 500px;
                width: 100%;
                background: white;
                border-radius: 24px;
                padding: 40px 32px;
                text-align: center;
                box-shadow: 0 20px 40px rgba(0,0,0,0.08);
            }
            .icon { font-size: 64px; margin-bottom: 20px; }
            h2 { color: #e67e22; margin-bottom: 16px; }
            p { color: #64748b; margin-bottom: 24px; line-height: 1.6; }
            .btn {
                display: inline-block;
                padding: 12px 24px;
                background: linear-gradient(135deg, #e67e22 0%, #f39c12 100%);
                color: white;
                text-decoration: none;
                border-radius: 12px;
                font-weight: 600;
            }
            .logout-btn {
                display: inline-block;
                margin-top: 16px;
                padding: 10px 20px;
                background: #ef4444;
                color: white;
                text-decoration: none;
                border-radius: 10px;
            }
        </style>
    </head>
    <body>
        <div class="pending-container">
            <div class="icon">⏳</div>
            <h2>Account Pending Approval</h2>
            <p>Your account is waiting for admin approval.<br>You will be notified once your account is activated.</p>
            <a href="doctor_profile_setup.html" class="btn">Complete Profile</a><br>
            <a href="screen2.html" class="logout-btn">Logout</a>
        </div>
    </body>
    </html>
    <?php
    $conn->close();
    exit();
}

// If approved but profile incomplete, show profile setup required
if ($is_approved && !$is_profile_complete) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Complete Your Profile - MotherCare</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background: linear-gradient(135deg, #fff5e8 0%, #ffe8d4 100%);
                min-height: 100vh;
                padding: 40px 20px;
            }
            .container { max-width: 600px; width: 100%; margin: 0 auto; }
            .card {
                background: white;
                border-radius: 24px;
                padding: 32px;
                box-shadow: 0 20px 40px rgba(0,0,0,0.08);
            }
            .header { text-align: center; margin-bottom: 28px; }
            .icon { font-size: 48px; margin-bottom: 12px; }
            h2 { color: #e67e22; margin-bottom: 8px; }
            .header p { color: #64748b; font-size: 14px; }
            .form-group { margin-bottom: 20px; }
            label { display: block; font-weight: 600; color: #334155; margin-bottom: 6px; font-size: 14px; }
            input, select, textarea {
                width: 100%;
                padding: 12px 14px;
                border: 1px solid #e2e8f0;
                border-radius: 12px;
                font-size: 14px;
                font-family: inherit;
                background: #fefcf8;
            }
            textarea { resize: vertical; min-height: 80px; }
            .btn {
                width: 100%;
                padding: 12px;
                background: linear-gradient(135deg, #e67e22 0%, #f39c12 100%);
                color: white;
                border: none;
                border-radius: 12px;
                font-size: 16px;
                font-weight: 600;
                cursor: pointer;
                margin-top: 8px;
            }
            .logout-btn {
                display: block;
                text-align: center;
                margin-top: 16px;
                padding: 10px;
                background: #ef4444;
                color: white;
                text-decoration: none;
                border-radius: 10px;
            }
            .message {
                margin-top: 16px;
                padding: 10px;
                border-radius: 10px;
                display: none;
            }
            .message.error { background: #fee2e2; color: #dc2626; display: block; }
            .message.success { background: #dcfce7; color: #16a34a; display: block; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="card">
                <div class="header">
                    <div class="icon">👨‍⚕️</div>
                    <h2>Complete Your Profile</h2>
                    <p>Please provide your professional details to start using the platform</p>
                </div>
                <form id="profileForm">
                    <div class="form-group">
                        <label>Specialty</label>
                        <select name="specialty" required>
                            <option value="">Select your specialty</option>
                            <option value="Obstetrician">Obstetrician</option>
                            <option value="Gynecologist">Gynecologist</option>
                            <option value="General Practitioner">General Practitioner</option>
                            <option value="Midwife">Midwife</option>
                            <option value="Maternal Health Specialist">Maternal Health Specialist</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Facility / Hospital</label>
                        <input type="text" name="facility" placeholder="e.g., Mulago Hospital" required>
                    </div>
                    <div class="form-group">
                        <label>Contact Number</label>
                        <div style="display: flex; gap: 10px;">
                            <select name="countryCode" style="width: 30%;">
                                <option value="+256">🇺🇬 +256</option>
                                <option value="+254">🇰🇪 +254</option>
                                <option value="+255">🇹🇿 +255</option>
                                <option value="+211">🇸🇸 +211</option>
                            </select>
                            <input type="text" name="dcontact" placeholder="Phone number" style="width: 70%;" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Qualifications</label>
                        <textarea name="qualifications" placeholder="e.g., MBChB, Master in Obstetrics and Gynecology"></textarea>
                    </div>
                    <button type="submit" class="btn">Save Profile</button>
                </form>
                <a href="screen2.html" class="logout-btn">Logout</a>
                <div id="message" class="message"></div>
            </div>
        </div>
        <script>
            document.getElementById('profileForm').addEventListener('submit', async function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                const messageDiv = document.getElementById('message');
                try {
                    const response = await fetch('save_doctor_profile.php', { method: 'POST', body: formData });
                    const result = await response.json();
                    if (result.success) {
                        messageDiv.className = "message success";
                        messageDiv.innerText = "✅ Profile saved! Redirecting...";
                        setTimeout(() => { window.location.href = 'doctor_dashboard.php'; }, 1500);
                    } else {
                        messageDiv.className = "message error";
                        messageDiv.innerText = "❌ " + result.message;
                    }
                } catch (error) {
                    messageDiv.className = "message error";
                    messageDiv.innerText = "❌ Network error. Please try again.";
                }
            });
        </script>
    </body>
    </html>
    <?php
    $conn->close();
    exit();
}

// ========== FETCH MESSAGES - FIXED ==========
// Fetch all messages from patients to this doctor
$sql = "
SELECT m.id as message_id, m.message, m.created_at, m.status,
u.id as patient_id, u.firstname, u.lastname, u.phone,
p.district, p.village, age
FROM messages m
JOIN users u ON m.sender_id = u.id
LEFT JOIN user_profiles p ON u.id = p.user_id
WHERE m.receiver_id = ? AND m.sender_type = 'patient'
ORDER BY m.created_at DESC
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if query was successful
if (!$result) {
    die("Query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Doctor Dashboard - MotherCare</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(135deg, #fff5e8 0%, #ffe8d4 100%);
    min-height: 100vh;
    padding: 20px;
}
.container { max-width: 1200px; margin: 0 auto; }
.header {
    background: white;
    border-radius: 20px;
    padding: 20px 30px;
    margin-bottom: 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}
.header h1 { color: #e67e22; font-size: 24px; }
.doctor-info { display: flex; align-items: center; gap: 20px; }
.doctor-name { font-weight: bold; color: #e67e22; font-size: 16px; }
.logout-btn {
    background: #dc3545;
    color: white;
    border: none;
    padding: 8px 20px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
    font-weight: bold;
    text-decoration: none;
}
.logout-btn:hover { background: #c82333; transform: translateY(-2px); }
.messages-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
    gap: 20px;
}
.message-card {
    background: white;
    border-radius: 20px;
    padding: 20px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    transition: all 0.3s;
}
.message-card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.15); }
.message-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 15px;
    flex-wrap: wrap;
    gap: 10px;
}
.patient-name { font-size: 18px; font-weight: bold; color: #333; }
.message-date { font-size: 12px; color: #999; background: #f5f5f5; padding: 4px 10px; border-radius: 20px; }
.status-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: bold;
    margin-left: 10px;
}
.status-sent { background: #ffc107; color: #856404; }
.status-read { background: #28a745; color: white; }
.patient-details {
    background: #f8f9fa;
    padding: 12px;
    border-radius: 12px;
    margin: 15px 0;
    font-size: 13px;
    color: #666;
}
.message-content {
    background: #f0f2f5;
    padding: 15px;
    border-radius: 12px;
    margin: 15px 0;
    color: #333;
    line-height: 1.5;
    border-left: 4px solid #e67e22;
}
.reply-area { margin-top: 15px; padding-top: 15px; border-top: 1px solid #e0e0e0; }
.reply-textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 12px;
    resize: vertical;
    font-family: inherit;
    font-size: 14px;
}
.reply-buttons { display: flex; gap: 10px; margin-top: 10px; }
.send-reply-btn {
    background: linear-gradient(135deg, #e67e22 0%, #f39c12 100%);
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: bold;
}
.send-reply-btn:hover { transform: translateY(-2px); }
.no-messages {
    text-align: center;
    padding: 60px;
    background: white;
    border-radius: 20px;
    color: #999;
    grid-column: 1 / -1;
}
@media (max-width: 768px) {
    .header { flex-direction: column; text-align: center; }
    .messages-grid { grid-template-columns: 1fr; }
}
</style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>📋 Patient Messages</h1>
        <div class="doctor-info">
            <span class="doctor-name">Dr. <?= htmlspecialchars($doctor_name) ?></span>
            <a href="screen2.html" class="logout-btn">🚪 Logout</a>
        </div>
    </div>
    
    <div class="messages-grid">
        <?php if($result->num_rows == 0): ?>
            <div class="no-messages">
                <p>📭 No messages from patients yet</p>
                <p style="font-size: 14px;">When patients send you messages, they'll appear here</p>
            </div>
        <?php else: 
            while($r = $result->fetch_assoc()): 
        ?>
            <div class="message-card" id="message-<?= $r['message_id'] ?>">
                <div class="message-header">
                    <div>
                        <span class="patient-name">👤 <?= htmlspecialchars($r['firstname']." ".$r['lastname']) ?></span>
                        <span class="status-badge status-<?= $r['status'] ?>">
                            <?= strtoupper($r['status']) ?>
                        </span>
                    </div>
                    <span class="message-date"><?= date('M d, Y H:i', strtotime($r['created_at'])) ?></span>
                </div>
                
                <div class="patient-details">
    <div> Phone: <?= htmlspecialchars($r['phone'] ?? 'N/A') ?></div>
    <div>Location: <?= htmlspecialchars($r['district']." - ".$r['village']) ?></div>
    <div> Age: <?= $r['age'] ?? 'N/A' ?></div>
</div>
                
                <div class="message-content">
                    <strong>Message:</strong><br>
                    <?= nl2br(htmlspecialchars($r['message'])) ?>
                </div>
                
                <div class="reply-area">
                    <textarea 
                        class="reply-textarea" 
                        id="reply-<?= $r['message_id'] ?>" 
                        rows="3" 
                        placeholder="Type your reply here..."></textarea>
                    <div class="reply-buttons">
                        <button class="send-reply-btn" onclick="sendReply(<?= $r['patient_id'] ?>, <?= $r['message_id'] ?>, '<?= htmlspecialchars($r['firstname']." ".$r['lastname']) ?>')">
                            ✉️ Send Reply
                        </button>
                    </div>
                </div>
            </div>
        <?php 
            endwhile; 
        endif; 
        ?>
    </div>
</div>

<script>
function sendReply(patientId, messageId, patientName) {
    const replyText = document.getElementById(`reply-${messageId}`).value.trim();
    
    if(!replyText) {
        alert('Please enter a reply');
        return;
    }
    
    if(!confirm(`Send reply to ${patientName}?`)) {
        return;
    }
    
    const formData = new FormData();
    formData.append('receiver_id', patientId);
    formData.append('message', replyText);
    
    fetch('send_doctor_reply.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            alert('✅ Reply sent to ' + patientName);
            document.getElementById(`reply-${messageId}`).value = '';
            location.reload();
        } else {
            alert('❌ Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('❌ Failed to send: ' + error);
    });
}
</script>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>