<?php
// Database connection
require_once __DIR__ . '/db_connect.php';
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Fetch doctor profiles
$sql = "SELECT * FROM doctors ORDER BY created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Registered Doctors</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f9f9f9;
      margin: 0;
      padding: 20px;
    }

    h1 {
      text-align: center;
      color: #4B0082;
    }

    .doctor-container {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 20px;
      margin-top: 30px;
    }

    .doctor-card {
      background-color: #fff;
      border-radius: 10px;
      padding: 20px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      text-align: center;
    }

    .doctor-card img {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      object-fit: cover;
      margin-bottom: 10px;
      border: 3px solid #4CAF50;
    }

    .doctor-card h3 {
      margin: 10px 0 5px 0;
      color: #333;
    }

    .doctor-card p {
      margin: 5px 0;
      font-size: 0.95rem;
      color: #555;
    }

    .back-btn {
      display: inline-block;
      margin: 15px auto;
      background-color: #3949ab;
      color: white;
      padding: 10px 20px;
      text-align: center;
      text-decoration: none;
      border-radius: 6px;
      font-weight: bold;
    }

    .back-btn:hover {
      background-color: #303f9f;
    }

  </style>
</head>
<body>

<h1>Registered Doctors</h1>

<div class="doctor-container">
  <?php if ($result->num_rows > 0): ?>
    <?php while($row = $result->fetch_assoc()): ?>
      <div class="doctor-card">
        <img src="<?= htmlspecialchars($row['photo']) ?>" alt="Doctor Photo">
        <h3><?= htmlspecialchars($row['specialty']) ?></h3>
        <p><strong>Facility:</strong> <?= htmlspecialchars($row['facility']) ?></p>
        <p><strong>Qualifications:</strong> <?= nl2br(htmlspecialchars($row['qualifications'])) ?></p>
        <p style="color: gray; font-size: 0.85rem;">Added on <?= date('M d, Y', strtotime($row['created_at'])) ?></p>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <p style="text-align:center; color:red;">No doctors have registered yet.</p>
  <?php endif; ?>
</div>

<div style="text-align:center;">
  <a href="doctor_dashboard.html" class="back-btn">← Back to Dashboard</a>
</div>

</body>
</html>
<?php $conn->close(); ?>
