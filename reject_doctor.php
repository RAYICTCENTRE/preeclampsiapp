<?php
session_start();
include "db_connect.php";

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin'){
    header("Location: login.php");
    exit();
}

if(isset($_GET['id'])){
    $id = intval($_GET['id']);
    $conn->query("UPDATE users SET status='rejected' WHERE user_id=$id AND role='doctor'");
}

header("Location: admin_dashboard.php");
exit();
?>