<?php
session_start();
require __DIR__ . '/../database/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$current_brgy_id = $_SESSION['brgy_id'];
$current_brgy_name = $_SESSION['brgy_name'];
$current_user_name = $_SESSION['user_full_name'];
$stmt = $conn->prepare("SELECT brgy_id, brgy_name FROM barangays WHERE brgy_id != ?");
$stmt->bind_param("i", $current_brgy_id);
$stmt->execute();
$partner_barangays_result = $stmt->get_result();

$selected_brgy_id = null;
$selected_brgy_name = "Choose a barangay...";

if (isset($_GET['selected_brgy_id']) && is_numeric($_GET['selected_brgy_id'])) {
    $temp_brgy_id = (int)$_GET['selected_brgy_id'];
    $stmt_check = $conn->prepare("SELECT brgy_name FROM barangays WHERE brgy_id = ? AND brgy_id != ?");
    $stmt_check->bind_param("ii", $temp_brgy_id, $current_brgy_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    if ($result_check->num_rows > 0) {
        $selected_brgy_id = $temp_brgy_id;
        $selected_brgy_name = $result_check->fetch_assoc()['brgy_name'];
    }
}
?>