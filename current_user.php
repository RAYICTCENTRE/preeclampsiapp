<?php
declare(strict_types=1);

session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

if ($user_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated.']);
    exit;
}

echo json_encode([
    'success' => true,
    'user_id' => $user_id,
    'firstname' => $_SESSION['firstname'] ?? ''
]);
?>