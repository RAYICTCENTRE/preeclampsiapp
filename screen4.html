<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>My Profile - MotherCare</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #fff5e8 0%, #ffe8d4 100%);
            font-family: 'Segoe UI', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            min-height: 100vh;
            padding: 30px 20px;
        }

        .profile-container {
            max-width: 500px;
            width: 100%;
            margin: 0 auto;
            background: white;
            border-radius: 24px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #e67e22 0%, #f39c12 100%);
            padding: 20px 24px;
            text-align: center;
        }

        .header h1 {
            font-size: 24px;
            font-weight: 700;
            color: white;
            margin-bottom: 4px;
        }

        .header p {
            font-size: 12px;
            color: rgba(255,255,255,0.9);
        }

        .progress-section {
            padding: 16px 24px 12px 24px;
            background: white;
            border-bottom: 1px solid #f0f0f0;
        }

        .progress-label {
            display: flex;
            justify-content: space-between;
            margin-bottom: 6px;
            font-size: 11px;
            color: #64748b;
            font-weight: 500;
        }

        .progress {
            background: #e2e8f0;
            height: 6px;
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #e67e22 0%, #f39c12 100%);
            border-radius: 10px;
            width: 0%;
            transition: width 0.3s ease;
        }

        .form-content {
            padding: 0 24px 20px 24px;
        }

        .section-title {
            font-size: 15px;
            font-weight: 700;
            color: #e67e22;
            margin: 20px 0 12px 0;
            padding-bottom: 6px;
            border-bottom: 2px solid #ffe0b5;
        }

        .section-title:first-of-type {
            margin-top: 0;
        }

        .form-row {
            display: flex;
            gap: 12px;
            margin-bottom: 10px;
            align-items: center;
        }

        .form-row label {
            width: 32%;
            font-weight: 600;
            color: #334155;
            font-size: 13px;
        }

        .form-row input,
        .form-row select {
            width: 68%;
            padding: 8px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            font-size: 13px;
            font-family: inherit;
            background: #fefcf8;
        }

        .form-row input:focus,
        .form-row select:focus {
            outline: none;
            border-color: #e67e22;
            box-shadow: 0 0 0 2px rgba(230,126,34,0.1);
        }

        .form-row input[readonly] {
            background: #f1f5f9;
            color: #64748b;
            cursor: not-allowed;
        }

        .phone-row {
            display: flex;
            gap: 10px;
            width: 68%;
        }

        .phone-row select {
            width: 40%;
            padding: 8px 10px;
        }

        .phone-row input {
            width: 60%;
            padding: 8px 12px;
        }

        .readonly-value {
            width: 68%;
            padding: 8px 12px;
            background: #f1f5f9;
            border-radius: 10px;
            font-size: 13px;
            color: #1e293b;
            font-weight: 500;
        }

        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 24px;
            flex-wrap: wrap;
        }

        .btn {
            flex: 1;
            padding: 10px 16px;
            border: none;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            text-align: center;
        }

        .btn-primary {
            background: linear-gradient(135deg, #e67e22 0%, #f39c12 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(230,126,34,0.35);
        }

        .btn-warning {
            background: #f59e0b;
            color: white;
        }

        .btn-warning:hover {
            background: #d97706;
        }

        .btn-danger {
            background: #ef4444;
            color: white;
        }

        .btn-danger:hover {
            background: #dc2626;
        }

        small {
            font-size: 10px;
            color: #b86f2c;
            margin-left: 8px;
        }

        @media (max-width: 640px) {
            .form-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }
            
            .form-row label {
                width: 100%;
            }
            
            .form-row input,
            .form-row select,
            .readonly-value,
            .phone-row {
                width: 100%;
            }
            
            .phone-row {
                flex-direction: row;
            }
            
            .button-group {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>

<div class="profile-container">
    <div class="header">
        <h1>My Profile</h1>
        <p>Tell us about yourself for better care</p>
    </div>

    <div class="progress-section">
        <div class="progress-label">
            <span>Profile Complete</span>
            <span id="progressPercent">0%</span>
        </div>
        <div class="progress">
            <div class="progress-bar" id="progressBar"></div>
        </div>
    </div>

    <form id="profileForm" action="save_profile.php" method="POST">
        <div class="form-content">
            <!-- Basic Info -->
            <div class="section-title">About You</div>
            
            <div class="form-row">
                <label>First Name</label>
                <div class="readonly-value" id="displayFirstname">Loading...</div>
            </div>
            
            <div class="form-row">
                <label>Last Name</label>
                <div class="readonly-value" id="displayLastname">Loading...</div>
            </div>
            
            <div class="form-row">
                <label>Phone Number</label>
                <div class="phone-row">
                    <select name="phoneCountryCode" id="phoneCountryCode">
                        <option value="+256">🇺🇬 Uganda (+256)</option>
                        <option value="+254">🇰🇪 Kenya (+254)</option>
                        <option value="+255">🇹🇿 Tanzania (+255)</option>
                        <option value="+211">🇸🇸 South Sudan (+211)</option>
                        <option value="+250">🇷🇼 Rwanda (+250)</option>
                        <option value="+243">🇨🇩 DR Congo (+243)</option>
                        <option value="+252">🇸🇴 Somalia (+252)</option>
                        <option value="+234">🇳🇬 Nigeria (+234)</option>
                        <option value="+251">🇪🇹 Ethiopia (+251)</option>
                        <option value="+1">🇺🇸 United States (+1)</option>
                        <option value="+44">🇬🇧 United Kingdom (+44)</option>
                        <option value="+27">🇿🇦 South Africa (+27)</option>
                    </select>
                    <input type="text" name="phone" id="phone" placeholder="Phone number">
                </div>
            </div>
            
            <div class="form-row">
                <label>Email</label>
                <div class="readonly-value" id="displayEmail">Loading...</div>
            </div>
            
            <div class="form-row">
                <label>Your Age</label>
                <input type="number" name="age" id="age" placeholder="e.g., 28" min="12" max="60">
            </div>

            <!-- Address -->
            <div class="section-title">Where You Live</div>
            
            <div class="form-row">
                <label>Nationality</label>
                <input type="text" name="nationality" id="nationality" placeholder="Your nationality">
            </div>
            
            <div class="form-row">
                <label>District</label>
                <input type="text" name="district" id="district" placeholder="Your district">
            </div>
            
            <div class="form-row">
                <label>Sub County</label>
                <input type="text" name="subCounty" id="subCounty" placeholder="Your sub county">
            </div>
            
            <div class="form-row">
                <label>Parish</label>
                <input type="text" name="parish" id="parish" placeholder="Your parish">
            </div>
            
            <div class="form-row">
                <label>Village</label>
                <input type="text" name="village" id="village" placeholder="Your village">
            </div>
            
            <div class="form-row">
                <label>Nearest Clinic</label>
                <input type="text" name="nearestHealth" id="nearestHealth" placeholder="Nearest health facility">
            </div>

            <!-- Emergency Contact -->
            <div class="section-title">Emergency Contact</div>
            
            <div class="form-row">
                <label>Full Name</label>
                <input type="text" name="kinName" id="kinName" placeholder="Emergency contact name">
            </div>
            
            <div class="form-row">
                <label>Relationship</label>
                <input type="text" name="kinRelationship" id="kinRelationship" placeholder="e.g., Husband, Mother">
            </div>
            
            <div class="form-row">
                <label>Phone Number</label>
                <div class="phone-row">
                    <select name="kinCountryCode" id="kinCountryCode">
                        <option value="+256">🇺🇬 Uganda (+256)</option>
                        <option value="+254">🇰🇪 Kenya (+254)</option>
                        <option value="+255">🇹🇿 Tanzania (+255)</option>
                        <option value="+211">🇸🇸 South Sudan (+211)</option>
                        <option value="+250">🇷🇼 Rwanda (+250)</option>
                        <option value="+243">🇨🇩 DR Congo (+243)</option>
                        <option value="+252">🇸🇴 Somalia (+252)</option>
                        <option value="+234">🇳🇬 Nigeria (+234)</option>
                        <option value="+251">🇪🇹 Ethiopia (+251)</option>
                        <option value="+1">🇺🇸 United States (+1)</option>
                        <option value="+44">🇬🇧 United Kingdom (+44)</option>
                        <option value="+27">🇿🇦 South Africa (+27)</option>
                    </select>
                    <input type="text" name="kinContact" id="kinContact" placeholder="Phone number">
                </div>
            </div>

            <!-- Pregnancy -->
            <div class="section-title">Pregnancy Details</div>
            
            <div class="form-row">
                <label>First day of last period</label>
                <input type="date" id="lastPeriod" name="lastPeriod">
                <small>Helps calculate due date</small>
            </div>
            
            <div class="form-row">
                <label>Your due date</label>
                <input type="date" id="expectedDelivery" name="expectedDelivery" readonly>
            </div>
            
            <div class="form-row">
                <label>Weeks pregnant</label>
                <input type="text" id="weeksPregnant" name="weeks_pregnant" readonly style="background:#f1f5f9;">
            </div>

            <!-- Buttons -->
            <div class="button-group">
                <button type="submit" class="btn btn-primary">Save Profile</button>
                <button type="button" id="clear-btn" class="btn btn-warning">Clear</button>
                <button type="button" id="cancel-btn" class="btn btn-danger">Cancel</button>
            </div>
        </div>
    </form>
</div>

<script>
// Fetch user data from server
async function loadUserData() {
    try {
        const response = await fetch('get_user_profile.php');
        const data = await response.json();
        
        console.log("Profile data received:", data); // Check console
        
        if (data.success) {
            // Fill readonly fields
            document.getElementById('displayFirstname').innerHTML = data.firstname || '';
            document.getElementById('displayLastname').innerHTML = data.lastname || '';
            document.getElementById('displayEmail').innerHTML = data.email || '';
            
            // Handle phone number
            if (data.phone) {
                const phoneMatch = data.phone.match(/^(\+[0-9]+)(.*)$/);
                if (phoneMatch) {
                    document.getElementById('phoneCountryCode').value = phoneMatch[1];
                    document.getElementById('phone').value = phoneMatch[2];
                } else {
                    document.getElementById('phone').value = data.phone;
                }
            }
            
            // Fill profile data if exists
            if (data.profile) {
                document.getElementById('age').value = data.profile.age || '';
                document.getElementById('nationality').value = data.profile.nationality || '';
                document.getElementById('district').value = data.profile.district || '';
                document.getElementById('subCounty').value = data.profile.sub_county || '';
                document.getElementById('parish').value = data.profile.parish || '';
                document.getElementById('village').value = data.profile.village || '';
                document.getElementById('nearestHealth').value = data.profile.nearest_health || '';
                document.getElementById('kinName').value = data.profile.kin_name || '';
                document.getElementById('kinRelationship').value = data.profile.kin_relationship || '';
                
                // Handle kin phone
                if (data.profile.kin_contact) {
                    const kinMatch = data.profile.kin_contact.match(/^(\+[0-9]+)(.*)$/);
                    if (kinMatch) {
                        document.getElementById('kinCountryCode').value = kinMatch[1];
                        document.getElementById('kinContact').value = kinMatch[2];
                    } else {
                        document.getElementById('kinContact').value = data.profile.kin_contact;
                    }
                }
                
                // Handle pregnancy dates
                if (data.profile.last_period) {
                    document.getElementById('lastPeriod').value = data.profile.last_period;
                    
                    // Calculate due date
                    const lastPeriod = new Date(data.profile.last_period);
                    const dueDate = new Date(lastPeriod);
                    dueDate.setDate(dueDate.getDate() + 280);
                    document.getElementById('expectedDelivery').value = dueDate.toISOString().split('T')[0];
                    
                    // Calculate weeks
                    const today = new Date();
                    const diffDays = Math.floor((today - lastPeriod) / (1000 * 60 * 60 * 24));
                    const weeks = Math.floor(diffDays / 7);
                    if (weeks >= 0 && weeks <= 42) {
                        document.getElementById('weeksPregnant').value = weeks + " weeks";
                    }
                }
            }
        }
        updateProgress();
    } catch (error) {
        console.error('Error loading profile:', error);
        document.getElementById('displayFirstname').innerHTML = 'Error loading';
        document.getElementById('displayLastname').innerHTML = 'Error loading';
        document.getElementById('displayEmail').innerHTML = 'Error loading';
    }
}

const form = document.getElementById('profileForm');
const progressBar = document.getElementById('progressBar');
const progressPercent = document.getElementById('progressPercent');

// Calculate weeks pregnant and due date
function calculateWeeksAndDueDate(lastPeriodDate) {
    const lastPeriod = new Date(lastPeriodDate);
    const today = new Date();
    const diffTime = today - lastPeriod;
    const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
    const weeks = Math.floor(diffDays / 7);
    return weeks;
}

// Update due date and weeks pregnant
document.getElementById("lastPeriod").addEventListener("change", function () {
    if (this.value) {
        const d = new Date(this.value);
        d.setDate(d.getDate() + 280);
        document.getElementById("expectedDelivery").value = d.toISOString().split('T')[0];
        
        const weeks = calculateWeeksAndDueDate(this.value);
        if (weeks >= 0 && weeks <= 42) {
            document.getElementById("weeksPregnant").value = weeks + " weeks";
        } else if (weeks < 0) {
            document.getElementById("weeksPregnant").value = "Please enter a valid date";
        } else {
            document.getElementById("weeksPregnant").value = "Overdue - please consult doctor";
        }
        updateProgress();
    }
});

// Clear form
document.getElementById('clear-btn').onclick = () => {
    form.reset();
    document.getElementById("expectedDelivery").value = "";
    document.getElementById("weeksPregnant").value = "";
    updateProgress();
};

// Cancel → dashboard
document.getElementById('cancel-btn').onclick = () => {
    window.location.href = 'dashboard.html';
};

// Update progress percentage
function updateProgress() {
    const inputs = form.querySelectorAll('input, select');
    let filled = 0;
    let total = 0;
    
    inputs.forEach(input => {
        if (!input.hasAttribute('readonly')) {
            total++;
            if (input.value && input.value.trim() !== '') {
                filled++;
            }
        }
    });
    
    const percent = Math.round((filled / total) * 100);
    progressBar.style.width = percent + '%';
    progressPercent.textContent = percent + '%';
}

// Add event listeners
form.querySelectorAll('input, select').forEach(input => {
    if (!input.hasAttribute('readonly')) {
        input.addEventListener('input', updateProgress);
        input.addEventListener('change', updateProgress);
    }
});

// Load user data when page loads
loadUserData();
</script>

</body>
</html>