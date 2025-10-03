<?php
session_start();
require '../logic/database/db.php'; 

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$res_id = isset($_GET['res_id']) ? (int)$_GET['res_id'] : 0;

if ($res_id === 0) {
    echo json_encode(['error' => 'Resource ID is required.']);
    exit();
}


$verify_res_stmt = $conn->prepare("SELECT brgy_id FROM resources WHERE res_id = ?");
$verify_res_stmt->bind_param("i", $res_id);
$verify_res_stmt->execute();
$verify_res_result = $verify_res_stmt->get_result();
if ($verify_res_result->num_rows === 0 || $verify_res_result->fetch_assoc()['brgy_id'] != $_SESSION['brgy_id']) {
    echo json_encode(['error' => 'Resource not found or not authorized for this barangay.']);
    exit();
}
$verify_res_stmt->close();


$query = "SELECT ri.item_id, ri.serial_number, ri.qr_code, r.res_name
          FROM resource_items ri
          JOIN resources r ON ri.res_id = r.res_id
          WHERE ri.res_id = ? AND ri.item_status = 'Available'";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $res_id);
$stmt->execute();
$result = $stmt->get_result();

$items = [];
while ($row = $result->fetch_assoc()) {
    $items[] = $row;
}

echo json_encode($items);

$stmt->close();
$conn->close();
?>
