<?php
session_start();
require '../logic/database/db.php';

header('Content-Type: application/json');


if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized access.']);
    exit();
}

$current_brgy_id = $_SESSION['brgy_id'];
$current_user_id = $_SESSION['user_id'];

$res_id = isset($_GET['res_id']) ? (int)$_GET['res_id'] : 0;
$start_date_str = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date_str = isset($_GET['end_date']) ? $_GET['end_date'] : '';

if ($res_id === 0 || empty($start_date_str) || empty(trim($end_date_str))) {
    echo json_encode(['error' => 'Missing resource ID or date range.']);
    exit();
}

try {
    $start_date = new DateTime($start_date_str);
    $end_date = new DateTime($end_date_str);
    $end_date->setTime(23, 59, 59); 

    
    $resource_stmt = $conn->prepare("SELECT res_quantity, is_bulk FROM resources WHERE res_id = ?");
    $resource_stmt->bind_param("i", $res_id);
    $resource_stmt->execute();
    $resource_result = $resource_stmt->get_result();
    if ($resource_result->num_rows === 0) {
        echo json_encode(['error' => 'Resource not found.']);
        exit();
    }
    $resource_data = $resource_result->fetch_assoc();
    $total_resource_quantity = $resource_data['res_quantity'];
    $is_bulk = (bool)$resource_data['is_bulk'];
    $resource_stmt->close();

    
    $unavailable_items_stmt = $conn->prepare("
        SELECT COUNT(*) as unavailable_count
        FROM resource_items
        WHERE res_id = ? AND item_status IN ('Under Maintenance', 'Lost')
    ");
    $unavailable_items_stmt->bind_param("i", $res_id);
    $unavailable_items_stmt->execute();
    $unavailable_items_result = $unavailable_items_stmt->get_result();
    $unavailable_count = 0;
    if ($row = $unavailable_items_result->fetch_assoc()) {
        $unavailable_count = (int)$row['unavailable_count'];
    }
    $unavailable_items_stmt->close();

    
    $adjusted_total_quantity = $total_resource_quantity - $unavailable_count;
    if ($adjusted_total_quantity < 0) {
        $adjusted_total_quantity = 0; 
    }

    $availability_data = [];

    
    
    $booked_requests_stmt = $conn->prepare("
        SELECT req_id, req_quantity, req_date, return_date, req_user_id
        FROM requests
        WHERE res_id = ?
        AND req_status IN ('Approved', 'Borrowed')
        AND (
            (req_date <= ? AND return_date >= ?) OR -- Request spans or starts before and ends after
            (req_date >= ? AND req_date <= ?) OR   -- Request starts within
            (return_date >= ? AND return_date <= ?) -- Request ends within
        )
    ");
    $booked_requests_stmt->bind_param(
        "issssss",
        $res_id,
        $end_date_str, $start_date_str, 
        $start_date_str, $end_date_str, 
        $start_date_str, $end_date_str  
    );
    $booked_requests_stmt->execute();
    $booked_requests_result = $booked_requests_stmt->get_result();
    $booked_requests = [];
    while ($row = $booked_requests_result->fetch_assoc()) {
        $booked_requests[] = $row;
    }
    $booked_requests_stmt->close();

    $interval = new DateInterval('P1D');
    $period = new DatePeriod($start_date, $interval, $end_date);

    foreach ($period as $date) {
        $current_date_str = $date->format('Y-m-d');
        $booked_quantity_for_day = 0;
        $user_has_booking_on_day = false;

        foreach ($booked_requests as $req) {
            $req_start = new DateTime($req['req_date']);
            $req_end = new DateTime($req['return_date']);
            $req_end->setTime(23, 59, 59); 

            if ($date >= $req_start && $date <= $req_end) {
                $booked_quantity_for_day += $req['req_quantity'];
                
                
                if ($req['req_user_id'] == $current_user_id) {
                    $user_has_booking_on_day = true;
                }
            }
        }

        
        $available_quantity_for_day = $adjusted_total_quantity - $booked_quantity_for_day;
        if ($available_quantity_for_day < 0) $available_quantity_for_day = 0; 

        $availability_data[$current_date_str] = [
            'available_quantity' => $available_quantity_for_day,
            'fully_booked' => ($available_quantity_for_day <= 0),
            'user_has_booking' => $user_has_booking_on_day 
        ];
    }

    echo json_encode($availability_data);

} catch (Exception $e) {
    error_log("Error in get_resource_availability.php: " . $e->getMessage());
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}

$conn->close();
?>
