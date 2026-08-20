<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Forgot / Reset Password</title>
<style>
body {
    background-color: #F0E68C;
    font-family: Arial;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    margin: 0;
}
.container {
    background: #FFFACD;
    padding: 20px;
    border-radius: 10px;
    width: 350px;
    text-align: center;
}
input, button { width:100%; padding:10px; margin:10px 0; border-radius:5px; border:1px solid #ccc; }
button { background:#4CAF50; color:white; cursor:pointer; border:none; }
button:hover { background:#45a049; }
</style>
</head>
<body>

<div class="container">
<h2>Reset Password</h2>
<p>Enter your registered Email or Phone</p>

<input type="text" id="identifier" placeholder="Email or Phone" required>
<input type="password" id="new_password" placeholder="New Password" required>

<button onclick="resetPassword()">Reset Password</button>
<p id="msg"></p>
</div>

<script>
async function resetPassword() {
    const identifier = document.getElementById("identifier").value.trim();
    const password = document.getElementById("new_password").value.trim();

    if (!identifier || !password) {
        document.getElementById("msg").innerText = "All fields are required";
        return;
    }

    try {
        const res = await fetch("forgot_password.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: new URLSearchParams({ identifier, password })
        });

        const data = await res.json();
        document.getElementById("msg").innerText = data.message;
    } catch(err) {
        document.getElementById("msg").innerText = "Network error: " + err.message;
    }
}
</script>

</body>
</html>