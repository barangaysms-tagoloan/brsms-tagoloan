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

    
    $stmt = $conn->prepare("
        SELECT
            COUNT(CASE WHEN item_status = 'Available' THEN 1 END) as available,
            COUNT(CASE WHEN item_status = 'Borrowed' THEN 1 END) as borrowed,
            COUNT(CASE WHEN item_status = 'Under Maintenance' THEN 1 END) as maintenance,
            COUNT(CASE WHEN item_status = 'Lost' THEN 1 END) as lost,
            COUNT(*) as total
        FROM resource_items
        WHERE res_id = ?
    ");
    $stmt->bind_param("i", $res_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $conn->close();

    echo json_encode($result);
?>
