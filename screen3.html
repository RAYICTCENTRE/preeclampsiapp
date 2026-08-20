<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Sign Up - MotherCare</title>
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
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
}

.signup-container {
    max-width: 450px;
    width: 100%;
    background: white;
    border-radius: 32px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.08);
    padding: 40px 32px;
    animation: fadeInUp 0.5s ease;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.logo {
    text-align: center;
    margin-bottom: 28px;
}

.logo h1 {
    font-size: 28px;
    font-weight: 800;
    background: linear-gradient(135deg, #e67e22 0%, #f39c12 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.logo p {
    font-size: 13px;
    color: #b86f2c;
    margin-top: 4px;
}

h2 {
    text-align: center;
    color: #1e3a8a;
    font-size: 24px;
    margin-bottom: 24px;
}

.form-group {
    margin-bottom: 16px;
}

input, select {
    width: 100%;
    padding: 12px 14px;
    border: 2px solid #e2e8f0;
    border-radius: 14px;
    font-size: 14px;
    font-family: inherit;
    transition: all 0.3s ease;
    background: #fefcf8;
}

input:focus, select:focus {
    outline: none;
    border-color: #e67e22;
    box-shadow: 0 0 0 3px rgba(230,126,34,0.1);
    background: white;
}

.btn {
    width: 100%;
    padding: 12px;
    border: none;
    border-radius: 14px;
    font-size: 16px;
    font-weight: 600;
    font-family: inherit;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-top: 8px;
}

.btn-primary {
    background: linear-gradient(135deg, #e67e22 0%, #f39c12 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(230,126,34,0.3);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(230,126,34,0.4);
}

.btn-secondary {
    background: #f1f5f9;
    color: #64748b;
}

.btn-secondary:hover {
    background: #e2e8f0;
    transform: translateY(-2px);
}

.message {
    text-align: center;
    padding: 10px;
    border-radius: 10px;
    margin-top: 12px;
    font-size: 13px;
    display: none;
}

.message.error {
    background: #fee2e2;
    color: #dc2626;
    display: block;
}

.message.success {
    background: #dcfce7;
    color: #16a34a;
    display: block;
}

.divider {
    text-align: center;
    margin: 20px 0;
    position: relative;
}

.divider::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 1px;
    background: #e2e8f0;
}

.divider span {
    background: white;
    padding: 0 12px;
    position: relative;
    z-index: 1;
    color: #94a3b8;
    font-size: 12px;
}

@media (max-width: 480px) {
    .signup-container {
        padding: 28px 24px;
    }
    
    h2 {
        font-size: 22px;
    }
}
</style>
</head>
<body>

<div class="signup-container">
    <div class="logo">
        <h1>MotherCare</h1>
        <p>Pre-eclampsia Guardian</p>
    </div>
    
    <h2>Create Account</h2>
    
    <form id="signupForm">
        <div class="form-group">
            <input type="text" name="firstname" placeholder="First Name" required>
        </div>
        
        <div class="form-group">
            <input type="text" name="lastname" placeholder="Last Name" required>
        </div>
        
        <div class="form-group">
            <input type="email" name="email" placeholder="Email Address" required>
        </div>
        
        <div class="form-group">
            <input type="tel" name="phone" placeholder="Phone Number (optional)">
        </div>
        
        <div class="form-group">
            <select name="user_type" required>
                <option value="">Select User Type</option>
                <option value="client">Patient</option>
                <option value="doctor">Doctor</option>
            </select>
        </div>
        
        <div class="form-group">
            <input type="password" id="password" name="password" placeholder="Create Password (min 6 characters)" required>
        </div>
        
        <div class="form-group">
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
        </div>
        
        <button type="submit" class="btn btn-primary">Sign Up</button>
    </form>
    
    <div class="divider">
        <span>OR</span>
    </div>
    
    <button class="btn btn-secondary" onclick="window.location.href='screen2.html'">Already have an account? Login</button>
    
    <div id="message" class="message"></div>
</div>

<script>
document.getElementById('signupForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const messageDiv = document.getElementById('message');
    
    // Validate passwords match
    if (password !== confirmPassword) {
        messageDiv.className = "message error";
        messageDiv.innerText = "❌ Passwords do not match!";
        return;
    }
    
    // Validate password length
    if (password.length < 6) {
        messageDiv.className = "message error";
        messageDiv.innerText = "❌ Password must be at least 6 characters";
        return;
    }
    
    // Get form data
    const formData = new FormData(this);
    
    try {
        const response = await fetch('signup.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            messageDiv.className = "message success";
            messageDiv.innerText = "✅ " + result.message;
            setTimeout(() => {
                window.location.href = result.redirect;
            }, 1500);
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