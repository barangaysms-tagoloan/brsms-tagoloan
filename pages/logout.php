<?php
session_start();
require_once '../logic/logging.php';

if (isset($_SESSION['user_id'])) {
    logLogout($_SESSION['user_id']);
}

session_unset();
session_destroy();
header("Location: login.php");
exit();
?>
