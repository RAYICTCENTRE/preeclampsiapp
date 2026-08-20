<?php
session_start();
session_unset();
session_destroy();
header("Location: screen2.php");
exit();
?>
