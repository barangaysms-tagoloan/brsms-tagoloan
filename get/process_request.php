<?php
session_start();
require '../logic/database/db.php';
require '../logic/logging.php'; 


ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');


if (!isset($_SESSION['user_id'])) {
    die(json_encode(['status' => 'error', 'message' => 'Not logged in']));
}


$required_fields = ['res_id', 'req_quantity', 'req_purpose', 'req_date', 'return_date', 'requester_id', 'requester_brgy_id', 'req_contact_number'];
foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
        
        $user_id_for_log = $_SESSION['user_id'] ?? 0; 
        $res_id_for_log = (int)($_POST['res_id'] ?? 0);
        $req_quantity_for_log = (int)($_POST['req_quantity'] ?? 0);
        $resource_name_for_log = "Unknown Resource"; 

        
        if ($res_id_for_log > 0) {
            $stmt_res_name = $conn->prepare("SELECT res_name FROM resources WHERE res_id = ?");
            $stmt_res_name->bind_param("i", $res_id_for_log);
            $stmt_res_name->execute();
            $result_res_name = $stmt_res_name->get_result();
            if ($row_res_name = $result_res_name->fetch_assoc()) {
                $resource_name_for_log = $row_res_name['res_name'];
            }
            $stmt_res_name->close();
        }

        logRequestCreate(
            $user_id_for_log,
            $resource_name_for_log,
            $req_quantity_for_log,
            'failed',
            "Missing or empty required field: $field"
        );
        die(json_encode(['status' => 'error', 'message' => "Missing or empty required field: $field"]));
    }
}


$res_id = (int)$_POST['res_id'];
$req_quantity = (int)$_POST['req_quantity'];
$req_purpose = $conn->real_escape_string(trim($_POST['req_purpose']));
$req_date_str = $conn->real_escape_string(trim($_POST['req_date']));
$return_date_str = $conn->real_escape_string(trim($_POST['return_date']));
$requester_id = (int)$_POST['requester_id'];
$requester_brgy_id = (int)$_POST['requester_brgy_id'];
$req_contact_number = $conn->real_escape_string(trim($_POST['req_contact_number']));
$req_status = 'Pending'; 

$resource_name_for_log = "Unknown Resource"; 


$stmt_res_name = $conn->prepare("SELECT res_name FROM resources WHERE res_id = ?");
$stmt_res_name->bind_param("i", $res_id);
$stmt_res_name->execute();
$result_res_name = $stmt_res_name->get_result();
if ($row_res_name = $result_res_name->fetch_assoc()) {
    $resource_name_for_log = $row_res_name['res_name'];
}
$stmt_res_name->close();



if ($req_quantity <= 0) {
    logRequestCreate($requester_id, $resource_name_for_log, $req_quantity, 'failed', 'Quantity must be positive.');
    die(json_encode(['status' => 'error', 'message' => 'Quantity must be positive.']));
}


try {
    $req_date = new DateTime($req_date_str);
    $return_date = new DateTime($return_date_str);
    $today = new DateTime();
    $today->setTime(0, 0, 0); 

    if ($req_date < $today) {
        logRequestCreate($requester_id, $resource_name_for_log, $req_quantity, 'failed', 'Request date cannot be in the past.');
        die(json_encode(['status' => 'error', 'message' => 'Request date cannot be in the past.']));
    }
    if ($return_date < $req_date) {
        logRequestCreate($requester_id, $resource_name_for_log, $req_quantity, 'failed', 'Return date cannot be before request date.');
        die(json_encode(['status' => 'error', 'message' => 'Return date cannot be before request date.']));
    }
} catch (Exception $e) {
    logRequestCreate($requester_id, $resource_name_for_log, $req_quantity, 'failed', 'Invalid date format: ' . $e->getMessage());
    die(json_encode(['status' => 'error', 'message' => 'Invalid date format.']));
}


$conn->begin_transaction();

try {
    
    $resource_query = $conn->prepare("SELECT brgy_id, res_quantity, is_bulk FROM resources WHERE res_id = ? FOR UPDATE");
    $resource_query->bind_param("i", $res_id);
    $resource_query->execute();
    $resource_result = $resource_query->get_result();

    if ($resource_result->num_rows == 0) {
        throw new Exception("Resource not found.");
    }

    $resource_data = $resource_result->fetch_assoc();
    $res_brgy_id = $resource_data['brgy_id'];
    $total_resource_quantity = $resource_data['res_quantity'];
    $is_bulk = (bool)$resource_data['is_bulk'];
    $resource_query->close();

    
    if ($res_brgy_id == $requester_brgy_id) {
        throw new Exception("Cannot request from your own barangay.");
    }

    
    $interval = new DateInterval('P1D');
    
    $period = new DatePeriod($req_date, $interval, $return_date->modify('+1 day'));

    foreach ($period as $date) {
        $current_day_str = $date->format('Y-m-d');

        
        $booked_quantity_stmt = $conn->prepare("
            SELECT SUM(req_quantity) as booked_sum
            FROM requests
            WHERE res_id = ?
            AND req_status IN ('Approved', 'Borrowed')
            AND req_date <= ? AND return_date >= ?
        ");
        $booked_quantity_stmt->bind_param("iss", $res_id, $current_day_str, $current_day_str);
        $booked_quantity_stmt->execute();
        $booked_quantity_result = $booked_quantity_stmt->get_result()->fetch_assoc();
        $booked_quantity_for_day = (int)$booked_quantity_result['booked_sum'];
        $booked_quantity_stmt->close();

        $available_quantity_for_day = $total_resource_quantity - $booked_quantity_for_day;

        
        
        $user_existing_quantity_stmt = $conn->prepare("
            SELECT SUM(req_quantity) as user_sum
            FROM requests
            WHERE res_id = ? AND req_user_id = ?
            AND req_status IN ('Approved', 'Borrowed', 'Pending')
            AND req_date <= ? AND return_date >= ?
        ");
        $user_existing_quantity_stmt->bind_param("iiss", $res_id, $requester_id, $current_day_str, $current_day_str);
        $user_existing_quantity_stmt->execute();
        $user_existing_quantity_result = $user_existing_quantity_stmt->get_result()->fetch_assoc();
        $user_existing_quantity_for_day = (int)$user_existing_quantity_result['user_sum'];
        $user_existing_quantity_stmt->close();

        
        if (($req_quantity + $user_existing_quantity_for_day) > $total_resource_quantity) {
            throw new Exception("Your total requested quantity for this resource on " . $date->format('M d, Y') . " (including existing requests) would exceed the total available quantity of " . $total_resource_quantity . ".");
        }

        
        
        if ($req_quantity > $available_quantity_for_day) {
            throw new Exception("Not enough quantity available on " . $date->format('M d, Y') . ". Only " . $available_quantity_for_day . " units left.");
        }
    }

    
    $insert_query = $conn->prepare("INSERT INTO requests (
        req_user_id,
        req_brgy_id,
        res_id,
        res_brgy_id,
        req_quantity,
        req_date,
        return_date,
        req_status,
        req_purpose,
        req_contact_number
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $insert_query->bind_param(
        "iiiissssss",
        $requester_id,
        $requester_brgy_id,
        $res_id,
        $res_brgy_id,
        $req_quantity,
        $req_date_str,
        $return_date_str,
        $req_status,
        $req_purpose,
        $req_contact_number
    );

    if (!$insert_query->execute()) {
        throw new Exception("Failed to create request: " . $conn->error);
    }

    
    $conn->commit();

    
    logRequestCreate($requester_id, $resource_name_for_log, $req_quantity, 'success', 'Request submitted successfully and is pending approval.');

    echo json_encode([
        'status' => 'success',
        'message' => 'Request submitted successfully! Awaiting approval.',
        'req_user_id' => $requester_id
    ]);

} catch (Exception $e) {
    $conn->rollback();
    
    logRequestCreate($requester_id, $resource_name_for_log, $req_quantity, 'failed', $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
