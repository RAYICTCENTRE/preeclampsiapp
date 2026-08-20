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

$patient_id = $_SESSION['user_id'];
$doctor_id = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : 0;

if(!$doctor_id){
    die("Doctor not specified");
}

// Get doctor details
$sql = "SELECT firstname, lastname FROM users WHERE id = ? AND user_type = 'doctor'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$result = $stmt->get_result();
$doctor = $result->fetch_assoc();

if(!$doctor){
    die("Doctor not found");
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Chat with Dr. <?= htmlspecialchars($doctor['firstname'] . ' ' . $doctor['lastname']) ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #fff5e8 0%, #ffe8d4 100%);
            height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .chat-container {
            max-width: 800px;
            margin: 20px auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            height: calc(100vh - 40px);
            overflow: hidden;
        }
        .chat-header {
            background: linear-gradient(135deg, #e67e22 0%, #f39c12 100%);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        .chat-header h2 { font-size: 20px; }
        .back-btn, .logout-btn {
            padding: 8px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s;
        }
        .back-btn { background: rgba(255,255,255,0.2); color: white; }
        .logout-btn { background: #ff4757; color: white; }
        .back-btn:hover, .logout-btn:hover { transform: translateY(-2px); }
        .messages-area {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            background: #f8f9fa;
        }
        .message {
            margin-bottom: 20px;
            display: flex;
            animation: fadeIn 0.3s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .message.sent { justify-content: flex-end; }
        .message.received { justify-content: flex-start; }
        .message-content {
            max-width: 70%;
            padding: 12px 18px;
            border-radius: 20px;
            word-wrap: break-word;
        }
        .message.sent .message-content {
            background: linear-gradient(135deg, #e67e22 0%, #f39c12 100%);
            color: white;
            border-bottom-right-radius: 5px;
        }
        .message.received .message-content {
            background: white;
            color: #333;
            border-bottom-left-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .message-time {
            font-size: 10px;
            margin-top: 5px;
            opacity: 0.7;
        }
        .message.sent .message-time { text-align: right; }
        .input-area {
            padding: 20px;
            background: white;
            border-top: 1px solid #e0e0e0;
            display: flex;
            gap: 10px;
        }
        .input-area textarea {
            flex: 1;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            resize: none;
            font-family: inherit;
            font-size: 14px;
        }
        .input-area textarea:focus {
            outline: none;
            border-color: #e67e22;
        }
        .send-btn {
            background: linear-gradient(135deg, #e67e22 0%, #f39c12 100%);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 12px;
            cursor: pointer;
            font-weight: bold;
        }
        .send-btn:hover { transform: scale(1.05); }
        .no-messages { text-align: center; padding: 50px; color: #999; }
        @media (max-width: 768px) {
            .chat-container { margin: 10px; height: calc(100vh - 20px); }
            .message-content { max-width: 85%; }
            .chat-header { flex-direction: column; text-align: center; }
        }
    </style>
</head>
<body>

<div class="chat-container">
    <div class="chat-header">
        <h2>💬 Dr. <?= htmlspecialchars($doctor['firstname'] . ' ' . $doctor['lastname']) ?></h2>
        <div>
            <button class="back-btn" onclick="goBack()">← Back</button>
            <button class="logout-btn" onclick="logout()">🚪 Logout</button>
        </div>
    </div>
    
    <div class="messages-area" id="messagesArea">
        <div class="no-messages">💬 Loading messages...</div>
    </div>
    
    <div class="input-area">
        <textarea id="messageInput" rows="2" placeholder="Type your message here... (Press Enter to send)"></textarea>
        <button class="send-btn" onclick="sendMessage()">Send ✉️</button>
    </div>
</div>

<script>
const doctorId = <?= $doctor_id ?>;
let lastMessageCount = 0;

function loadMessages() {
    fetch(`fetch_messages.php?doctor_id=${doctorId}&t=${Date.now()}`)
        .then(response => response.json())
        .then(messages => {
            const messagesArea = document.getElementById('messagesArea');
            
            if(messages.error) {
                messagesArea.innerHTML = '<div class="no-messages">⚠️ Error loading messages</div>';
                return;
            }
            
            if(messages.length === 0) {
                messagesArea.innerHTML = '<div class="no-messages">💬 No messages yet. Send a message to the doctor!</div>';
                return;
            }
            
            let html = '';
            messages.forEach(msg => {
                const isSent = msg.sender === 'patient';
                html += `
                    <div class="message ${isSent ? 'sent' : 'received'}">
                        <div class="message-content">
                            ${escapeHtml(msg.message)}
                            <div class="message-time">
                                ${formatDate(msg.created_at)}
                                ${isSent ? '<span> ✓</span>' : ''}
                            </div>
                        </div>
                    </div>
                `;
            });
            
            messagesArea.innerHTML = html;
            scrollToBottom();
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('messagesArea').innerHTML = '<div class="no-messages">⚠️ Error loading messages</div>';
        });
}

function sendMessage() {
    const input = document.getElementById('messageInput');
    const message = input.value.trim();
    
    if(!message) {
        alert('Please enter a message');
        return;
    }
    
    const formData = new FormData();
    formData.append('doctor_id', doctorId);
    formData.append('message', message);
    
    fetch('send_message.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            input.value = '';
            loadMessages();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to send message');
    });
}

function formatDate(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diff = now - date;
    
    if(diff < 60000) return 'Just now';
    if(diff < 3600000) return `${Math.floor(diff/60000)} min ago`;
    if(diff < 86400000) return `${Math.floor(diff/3600000)} hours ago`;
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'});
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML.replace(/\n/g, '<br>');
}

function scrollToBottom() {
    const messagesArea = document.getElementById('messagesArea');
    messagesArea.scrollTop = messagesArea.scrollHeight;
}

function goBack() {
    window.location.href = 'dashboard.html';
}

function logout() {
    if(confirm('Are you sure you want to logout?')) {
        window.location.href = 'dashboard.html';
    }
}

// Handle Enter key
document.getElementById('messageInput').addEventListener('keypress', function(e) {
    if(e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
});

// Load messages on page load
loadMessages();

// Auto-refresh every 3 seconds
setInterval(loadMessages, 3000);
</script>

</body>
</html>