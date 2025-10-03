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

// Base SQL query to fetch requests with 'Borrowed' status
$sql = "SELECT
        r.req_id as request_id,
        res.res_name as resource_name,
        u.user_full_name,
        b.brgy_name as requester_brgy_name,
        r.req_quantity as quantity_requested,
        r.return_date,
        r.req_purpose as purpose,
        r.req_status as status,
        res.res_id as resource_id,
        res.brgy_id as resource_owner_id,
        res.is_bulk,
        res.res_photo,
        r.req_contact_number,
        r.req_date as request_date,
        r.borrow_timestamp, -- Added borrow_timestamp
        r.return_timestamp  -- Added return_timestamp
    FROM requests r
    JOIN users u ON r.req_user_id = u.user_id
    JOIN barangays b ON r.req_brgy_id = b.brgy_id
    JOIN resources res ON r.res_id = res.res_id
    WHERE res.brgy_id = ?
    AND r.req_brgy_id != res.brgy_id
    AND r.req_status = 'Borrowed'
    ORDER BY r.req_timestamp DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $current_brgy_id);
$stmt->execute();
$requests_result = $stmt->get_result();
$issued_resources_data = [];
while ($row = $requests_result->fetch_assoc()) {
    $issued_resources_data[] = $row;
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