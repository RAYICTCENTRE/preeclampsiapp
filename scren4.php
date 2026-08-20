<?php
session_start();

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: screen2.html");
    exit();
}

require_once __DIR__ . '/db_connect.php';
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

// =====================
// FETCH USER
$stmt = $conn->prepare("SELECT firstname, lastname, email FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc() ?? [];

// =====================
// FETCH PROFILE
$stmt2 = $conn->prepare("SELECT * FROM user_profiles WHERE id=?");
$stmt2->bind_param("i", $user_id);
$stmt2->execute();
$result2 = $stmt2->get_result();
$profile = $result2->fetch_assoc() ?? [];

// =====================
// SAFE VALUES (NO ERRORS)
$firstname = $user['firstname'] ?? '';
$lastname  = $user['lastname'] ?? '';
$email     = $user['email'] ?? '';

$phone = $profile['phone'] ?? '';
$nationality = $profile['nationality'] ?? '';
$district = $profile['district'] ?? '';
$subCounty = $profile['sub_county'] ?? '';
$parish = $profile['parish'] ?? '';
$village = $profile['village'] ?? '';
$nearestHealth = $profile['nearest_health'] ?? '';
$kinName = $profile['kin_name'] ?? '';
$kinRelationship = $profile['kin_relationship'] ?? '';
$kinContact = $profile['kin_contact'] ?? '';
$dob = $profile['dob'] ?? '';
$lastPeriod = $profile['last_period'] ?? '';
$expectedDelivery = $profile['expected_delivery'] ?? '';

// Split country code
$countryCode = "+256";
$kinNumber = "";

if (!empty($kinContact)) {
    if (preg_match('/^(\+\d+)(.*)$/', $kinContact, $matches)) {
        $countryCode = $matches[1];
        $kinNumber = $matches[2];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Profile Setup</title>

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
body { background:#E0FFFF; font-family:Arial; padding:15px; }
.profile-container { background:#FFFACD; padding:20px; border-radius:10px; max-width:700px; margin:auto; }
h1 { text-align:center; color:#4B0082; }

.form-row { display:flex; flex-wrap:wrap; gap:10px; margin-bottom:12px; align-items:center; }

label { width:35%; font-weight:bold; }

input, select {
    width:65%;
    max-width:400px;
    padding:8px;
    border-radius:5px;
    border:1px solid #ccc;
}

.contact-row { display:flex; gap:8px; width:65%; }
.contact-row select { width:35%; }
.contact-row input { width:65%; }

.progress { background:#ddd; height:20px; border-radius:10px; margin-bottom:15px; }
.progress-bar { height:100%; background:#4CAF50; border-radius:10px; text-align:center; color:white; line-height:20px; font-size:12px; }

.button-group { display:flex; flex-wrap:wrap; gap:10px; margin-top:15px; }

button {
    flex:1;
    padding:12px;
    border:none;
    border-radius:6px;
    background:#4CAF50;
    color:white;
    cursor:pointer;
}

#clear-btn { background:orange; }
#cancel-btn { background:red; }

@media (max-width:600px){
    .form-row { flex-direction:column; align-items:flex-start; }
    label { width:100%; }
    input, select { width:100%; }
    .contact-row { width:100%; }
}
</style>
</head>

<body>

<div class="profile-container">

<h1>Profile Setup</h1>

<div class="progress">
<div class="progress-bar" style="width:0%">0%</div>
</div>

<form id="profileForm" action="save_profile.php" method="POST">

<h3>Basic Info</h3>

<div class="form-row">
<label>First Name:</label>
<input type="text" value="<?php echo htmlspecialchars($firstname); ?>" readonly>
</div>

<div class="form-row">
<label>Last Name:</label>
<input type="text" value="<?php echo htmlspecialchars($lastname); ?>" readonly>
</div>

<div class="form-row">
<label>Phone:</label>
<input type="text" name="phone" value="<?php echo htmlspecialchars($phone); ?>">
</div>

<div class="form-row">
<label>Email:</label>
<input type="text" value="<?php echo htmlspecialchars($email); ?>" readonly>
</div>

<h3>Address</h3>

<div class="form-row"><label>Nationality:</label><input type="text" name="nationality" value="<?php echo $nationality; ?>"></div>
<div class="form-row"><label>District:</label><input type="text" name="district" value="<?php echo $district; ?>"></div>
<div class="form-row"><label>Sub County:</label><input type="text" name="subCounty" value="<?php echo $subCounty; ?>"></div>
<div class="form-row"><label>Parish:</label><input type="text" name="parish" value="<?php echo $parish; ?>"></div>
<div class="form-row"><label>Village:</label><input type="text" name="village" value="<?php echo $village; ?>"></div>
<div class="form-row"><label>Nearest Health:</label><input type="text" name="nearestHealth" value="<?php echo $nearestHealth; ?>"></div>

<h3>Next of Kin</h3>

<div class="form-row"><label>Name:</label><input type="text" name="kinName" value="<?php echo $kinName; ?>"></div>
<div class="form-row"><label>Relationship:</label><input type="text" name="kinRelationship" value="<?php echo $kinRelationship; ?>"></div>

<div class="form-row">
<label>Contact:</label>
<div class="contact-row">
<select name="countryCode">
<option value="+256" <?= $countryCode=="+256"?"selected":"" ?>>+256</option>
<option value="+254" <?= $countryCode=="+254"?"selected":"" ?>>+254</option>
<option value="+255" <?= $countryCode=="+255"?"selected":"" ?>>+255</option>
<option value="+1" <?= $countryCode=="+1"?"selected":"" ?>>+1</option>
</select>
<input type="text" name="kinContact" value="<?php echo $kinNumber; ?>">
</div>
</div>

<h3>Other</h3>

<div class="form-row"><label>DOB:</label><input type="date" name="dob" value="<?php echo $dob; ?>"></div>
<div class="form-row"><label>Last Period:</label><input type="date" id="lastPeriod" name="lastPeriod" value="<?php echo $lastPeriod; ?>"></div>
<div class="form-row"><label>Expected Delivery:</label><input type="date" id="expectedDelivery" name="expectedDelivery" value="<?php echo $expectedDelivery; ?>" readonly></div>

<div class="button-group">
<button type="submit">Save Profile</button>
<button type="button" id="clear-btn">Clear</button>
<button type="button" id="cancel-btn">Cancel</button>
</div>

</form>
</div>

<script>
document.getElementById('clear-btn').onclick = () => document.getElementById('profileForm').reset();
document.getElementById('cancel-btn').onclick = () => window.location.href='dashboard.php';
</script>

</body>
</html>