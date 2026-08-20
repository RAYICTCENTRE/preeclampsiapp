<?php
session_start();

// Secure access - only clients (patients) can view doctors
if(!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || strtolower(trim($_SESSION['user_type'])) !== 'client'){
    header("Location: screen2.html");
    exit();
}

require_once __DIR__ . '/db_connect.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch approved doctors with their profile information
$sql = "
SELECT 
    u.id,
    u.firstname,
    u.lastname,
    d.photo_path,
    d.specialty,
    d.facility,
    d.dcontact,
    d.qualifications
FROM users u
LEFT JOIN doctors d ON u.id = d.user_id
WHERE LOWER(u.user_type) = 'doctor' AND u.approved = 1
ORDER BY u.firstname ASC
";

$result = $conn->query($sql);

if(!$result){
    die("Query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consult a Doctor - MotherCare</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #fff5e8 0%, #ffe8d4 100%);
            min-height: 100vh;
            padding: 30px 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            background: white;
            border-radius: 20px;
            padding: 25px 30px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            text-align: center;
        }

        .header h1 {
            color: #e67e22;
            font-size: 28px;
            margin-bottom: 8px;
        }

        .header p {
            color: #b86f2c;
            font-size: 14px;
        }

        .back-btn {
            display: inline-block;
            margin-bottom: 20px;
            background: #e67e22;
            color: white;
            padding: 10px 20px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }

        .back-btn:hover {
            background: #d35400;
            transform: translateY(-2px);
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
        }

        .card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: all 0.3s;
            cursor: pointer;
            border: 1px solid rgba(230,126,34,0.2);
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            border-color: #e67e22;
        }

        .doctor-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin: 0 auto 15px auto;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background: linear-gradient(135deg, #e67e22 0%, #f39c12 100%);
        }

        .doctor-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .doctor-avatar .default-avatar {
            font-size: 48px;
            color: white;
        }

        .name {
            font-size: 20px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }

        .specialty {
            color: #e67e22;
            font-weight: 600;
            margin: 8px 0;
            font-size: 14px;
        }

        .facility {
            font-size: 13px;
            color: #666;
            margin: 5px 0;
        }

        .contact {
            font-size: 12px;
            color: #888;
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid #eee;
        }

        .consult-btn {
            margin-top: 15px;
            display: inline-block;
            padding: 10px 20px;
            background: linear-gradient(135deg, #e67e22 0%, #f39c12 100%);
            color: white;
            border-radius: 30px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            width: 100%;
        }

        .consult-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(230,126,34,0.3);
        }

        .no-data {
            text-align: center;
            padding: 60px;
            background: white;
            border-radius: 20px;
            color: #b86f2c;
            grid-column: 1 / -1;
        }

        @media (max-width: 768px) {
            .grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            .header {
                padding: 20px;
            }
            .header h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <a href="dashboard.html" class="back-btn">← Back to Dashboard</a>

    <div class="header">
        <h1>👨‍⚕️ Consult a Doctor</h1>
        <p>Choose a doctor to start a conversation about your health concerns</p>
    </div>

    <div class="grid">
        <?php if($result->num_rows > 0): ?>
            <?php while($doc = $result->fetch_assoc()): ?>
                <div class="card" onclick="selectDoctor(<?php echo $doc['id']; ?>)">
                    <div class="doctor-avatar">
                        <?php if(!empty($doc['photo_path']) && file_exists($doc['photo_path'])): ?>
                            <img src="<?php echo $doc['photo_path']; ?>" alt="Dr. <?php echo htmlspecialchars($doc['firstname']); ?>">
                        <?php else: ?>
                            <div class="default-avatar">👨‍⚕️</div>
                        <?php endif; ?>
                    </div>
                    <div class="name">
                        Dr. <?php echo htmlspecialchars($doc['firstname'] . " " . $doc['lastname']); ?>
                    </div>
                    <div class="specialty">
                        SPECIALTY:  <?php echo !empty($doc['specialty']) ? htmlspecialchars($doc['specialty']) : 'General Practitioner'; ?>
                    </div>
                    <div class="facility">
                    FACILITY: <?php echo !empty($doc['facility']) ? htmlspecialchars($doc['facility']) : 'Facility not specified'; ?>
                    </div>
                    <div class="contact">
                        📞 <?php echo !empty($doc['dcontact']) ? htmlspecialchars($doc['dcontact']) : 'Contact not available'; ?>
                    </div>
                    <button class="consult-btn">Start Consultation →</button>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-data">
                <p>📭 No approved doctors available at the moment</p>
                <p style="font-size: 14px;">Please check back later or contact the administrator</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function selectDoctor(doctorId) {
    window.location.href = "chat_patient.php?doctor_id=" + encodeURIComponent(doctorId);
}
</script>

</body>
</html>