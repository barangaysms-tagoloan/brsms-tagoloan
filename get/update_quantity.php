<?php
session_start();
header('Content-Type: application/json');

ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');
error_reporting(E_ALL);

require '../logic/database/db.php'; 
require '../logic/logging.php'; 


function sendJsonResponse($status, $message, $http_code = 200) {
    http_response_code($http_code);
    echo json_encode(['status' => $status, 'message' => $message]);
    exit;
}


if (!isset($_SESSION['user_id'])) {
    sendJsonResponse('error', 'User not logged in.', 401);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = filter_input(INPUT_POST, 'request_id', FILTER_VALIDATE_INT);
    $new_quantity = filter_input(INPUT_POST, 'new_quantity', FILTER_VALIDATE_INT);

    
    if ($request_id === false || $request_id <= 0) {
        sendJsonResponse('error', 'Invalid Request ID provided.', 400);
    }
    if ($new_quantity === false || $new_quantity < 1) {
        sendJsonResponse('error', 'New quantity must be a positive number.', 400);
    }

    $conn->begin_transaction();

    try {
        
        $check_stmt = $conn->prepare("
            SELECT r.req_status, res.is_bulk, res.res_id, res.res_quantity AS available_stock
            FROM requests r
            JOIN resources res ON r.res_id = res.res_id
            WHERE r.req_id = ? FOR UPDATE
        ");
        $check_stmt->bind_param("i", $request_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        $request_info = $result->fetch_assoc();
        $check_stmt->close();

        if (!$request_info) {
            throw new Exception("Request not found.");
        }

        if ($request_info['req_status'] !== 'Approved') {
            throw new Exception("Quantity can only be edited for 'Approved' requests.");
        }

        $resource_id = $request_info['res_id'];
        $is_bulk = (bool)$request_info['is_bulk'];
        $available_stock = $request_info['available_stock'];

        
        if (!$is_bulk && $new_quantity !== 1) {
            throw new Exception("Non-bulk resources can only have a quantity of 1.");
        }

        
        if ($is_bulk && $new_quantity > $available_stock) {
            throw new Exception("New quantity ({$new_quantity}) exceeds available stock ({$available_stock}).");
        }

        
        $update_stmt = $conn->prepare("UPDATE requests SET req_quantity = ? WHERE req_id = ?");
        $update_stmt->bind_param("ii", $new_quantity, $request_id);

        if (!$update_stmt->execute()) {
            throw new Exception("Failed to update request quantity: " . $conn->error);
        }
        $update_stmt->close();

        
        logActivity($_SESSION['user_id'], "Updated Request Quantity", "Request ID: {$request_id} quantity updated to {$new_quantity}.");

        $conn->commit();
        sendJsonResponse('success', 'Request quantity updated successfully.');

    } catch (Exception $e) {
        $conn->rollback();
        error_log("Update Quantity Error (Request ID: {$request_id}): " . $e->getMessage());
        sendJsonResponse('error', $e->getMessage(), 400);
    }

} else {
    sendJsonResponse('error', 'Invalid request method.', 405); 
}
?>
