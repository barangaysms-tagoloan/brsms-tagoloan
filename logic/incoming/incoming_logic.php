<?php
session_start();
require __DIR__ . '/../database/db.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$current_brgy_id = $_SESSION['brgy_id'];

// Get status filter from GET request, default to 'all'
$status_filter = isset($_GET['status_filter']) ? $_GET['status_filter'] : 'all';

// Base SQL query
$sql = "SELECT
        r.req_id as request_id,
        res.res_name as resource_name,
        u.user_full_name,
        b.brgy_name as requester_brgy_name,
        r.req_quantity as quantity_requested,
        r.req_date as request_date,
        r.return_date,
        r.req_purpose as purpose,
        r.req_status as status,
        res.res_id as resource_id,
        res.brgy_id as resource_owner_id,
        res.is_bulk,
        res.res_photo,
        r.req_contact_number as contact_number_requested,
        r.req_timestamp as request_timestamp,
        r.borrow_timestamp, -- Added borrow_timestamp
        r.return_timestamp  -- Added return_timestamp
    FROM requests r
    JOIN users u ON r.req_user_id = u.user_id
    JOIN barangays b ON r.req_brgy_id = b.brgy_id
    JOIN resources res ON r.res_id = res.res_id
    WHERE res.brgy_id = ?
    AND r.req_brgy_id != res.brgy_id";

// Add status filter condition if not 'all'
if ($status_filter !== 'all') {
    $sql .= " AND r.req_status = ?";
}

$sql .= " ORDER BY r.req_timestamp DESC";

$stmt = $conn->prepare($sql);

if ($status_filter !== 'all') {
    $stmt->bind_param("is", $current_brgy_id, $status_filter);
} else {
    $stmt->bind_param("i", $current_brgy_id);
}


$stmt->execute();
$requests_result = $stmt->get_result();
$incoming_requests_data = [];
while ($row = $requests_result->fetch_assoc()) {
    $incoming_requests_data[] = $row;
}

// Helper function for status colors
function getStatusColor($status) {
    switch(strtolower($status)) {
        case 'pending': return 'pending';
        case 'approved': return 'approved';
        case 'rejected': return 'rejected';
        case 'completed': return 'completed';
        case 'borrowed': return 'borrowed';
        case 'cancelled': return 'cancelled';
        default: return 'secondary';
    }
}
?>