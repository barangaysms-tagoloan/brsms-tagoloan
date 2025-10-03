<?php
    session_start();
    require '../logic/database/db.php'; 

    header('Content-Type: application/json');

    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Unauthorized']);
        exit();
    }

    $res_id = isset($_GET['res_id']) ? (int)$_GET['res_id'] : 0;
    $item_status_filter = isset($_GET['item_status']) ? $_GET['item_status'] : 'All';

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

    
    $query = "SELECT ri.item_id, ri.item_status, ri.qr_code, ri.serial_number, r.res_name
              FROM resource_items ri
              JOIN resources r ON ri.res_id = r.res_id
              WHERE ri.res_id = ?";
    $params = [$res_id];
    $types = "i";

    if ($item_status_filter !== 'All') {
        $query .= " AND ri.item_status = ?";
        $params[] = $item_status_filter;
        $types .= "s";
    }

    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $bind_params = [$types];
        foreach ($params as $key => $value) {
            $bind_params[] = &$params[$key];
        }
        call_user_func_array([$stmt, 'bind_param'], $bind_params);
    }
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
