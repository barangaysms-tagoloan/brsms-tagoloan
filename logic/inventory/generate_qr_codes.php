<?php
// generate_qr_codes.php
session_start();
require __DIR__ . '/../database/db.php';
require_once __DIR__ . '/../phpqrcode/qrlib.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if (!isset($_POST['res_id'])) {
    echo json_encode(['success' => false, 'message' => 'Resource ID required']);
    exit();
}

$res_id = (int)$_POST['res_id'];

// Verify that the resource belongs to the current barangay
$verify_res_stmt = $conn->prepare("SELECT brgy_id, res_name FROM resources WHERE res_id = ?");
$verify_res_stmt->bind_param("i", $res_id);
$verify_res_stmt->execute();
$verify_res_result = $verify_res_stmt->get_result();

if ($verify_res_result->num_rows === 0 || $verify_res_result->fetch_assoc()['brgy_id'] != $_SESSION['brgy_id']) {
    echo json_encode(['success' => false, 'message' => 'Resource not found or not authorized']);
    exit();
}

// Get resource name
$verify_res_stmt->data_seek(0); // Reset pointer after fetch_assoc
$resource = $verify_res_result->fetch_assoc();
$res_name = $resource['res_name'];

// Get items without QR codes
$items_stmt = $conn->prepare("SELECT item_id FROM resource_items WHERE res_id = ? AND (qr_code IS NULL OR qr_code = '')");
$items_stmt->bind_param("i", $res_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();

$generated_count = 0;

// Create directory for QR codes if it doesn't exist
if (!is_dir('qrcodes')) {
    mkdir('qrcodes', 0755, true);
}

while ($item = $items_result->fetch_assoc()) {
    $item_id = $item['item_id'];
    
    // Generate QR code content with link to item details
    $qrContent = "https://brsms.infinityfree.me/brsms/item_details.php?item_id=$item_id"; // Updated to include link
    
    // Generate filename
    $filename = "qr_item_{$item_id}.png";
    $filepath = "qrcodes/" . $filename;
    
    // Generate QR code
    QRcode::png($qrContent, $filepath, QR_ECLEVEL_L, 10, 2);
    
    // Update database
    $update_stmt = $conn->prepare("UPDATE resource_items SET qr_code = ? WHERE item_id = ?");
    $update_stmt->bind_param("si", $filename, $item_id);
    $update_stmt->execute();
    $update_stmt->close();
    
    $generated_count++;
}

echo json_encode(['success' => true, 'message' => "Generated $generated_count QR codes"]);
?>
