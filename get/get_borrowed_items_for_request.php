<?php
session_start();
require '../logic/database/db.php'; 

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$req_id = isset($_GET['req_id']) ? (int)$_GET['req_id'] : 0;

if ($req_id === 0) {
    echo json_encode(['error' => 'Request ID is required.']);
    exit();
}



$verify_req_stmt = $conn->prepare("
    SELECT r.res_id, res.brgy_id, r.req_status
    FROM requests r
    JOIN resources res ON r.res_id = res.res_id
    WHERE r.req_id = ?
");
$verify_req_stmt->bind_param("i", $req_id);
$verify_req_stmt->execute();
$verify_req_result = $verify_req_stmt->get_result();
$request_info = $verify_req_result->fetch_assoc();

if (!$request_info || $request_info['brgy_id'] != $_SESSION['brgy_id'] || $request_info['req_status'] !== 'Borrowed') {
    echo json_encode(['error' => 'Request not found, not authorized, or not in "Borrowed" status.']);
    exit();
}
$verify_req_stmt->close();

$res_id = $request_info['res_id'];


$query = "SELECT ri.item_id, ri.serial_number, ri.qr_code, r.res_name
          FROM resource_items ri
          JOIN resources r ON ri.res_id = r.res_id
          WHERE ri.res_id = ? AND ri.current_req_id = ? AND ri.item_status = 'Borrowed'";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $res_id, $req_id);
$stmt->execute();
$result = $stmt->get_result();

$items = [];
while ($row = $result->fetch_assoc()) {
    $items[] = $row;
}

echo json_encode($items);

$stmt->close();
$conn->close();
        