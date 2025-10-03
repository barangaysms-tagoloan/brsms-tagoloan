<?php
session_start();
require __DIR__ . '/../database/db.php';
require_once __DIR__ . '/../logging.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_SESSION['role']) && strtolower($_SESSION['role']) === 'superadmin') {
    header("Location: super_dashboard.php");
    exit();
}

$current_brgy_id = $_SESSION['brgy_id'];

$stats = [];
$queries = [
    'total_resources_count' => "SELECT COUNT(*) as count FROM resources WHERE brgy_id = ?", // Count of distinct resources
    'total_pending_requests' => "SELECT COUNT(*) as count FROM requests WHERE res_brgy_id = ? AND req_status = 'Pending'",
    'total_issued_resources' => "SELECT COUNT(*) as count FROM requests WHERE res_brgy_id = ? AND req_status = 'Borrowed'", // This is the count for 'Issued'
    'total_returned_resources' => "SELECT COUNT(*) as count FROM returns WHERE brgy_id = ?",
    // Queries for breakdown of resource item statuses
    'total_available_items' => "SELECT COUNT(ri.item_id) as count FROM resource_items ri JOIN resources r ON ri.res_id = r.res_id WHERE r.brgy_id = ? AND ri.item_status = 'Available'",
    'total_borrowed_items' => "SELECT COUNT(ri.item_id) as count FROM resource_items ri JOIN resources r ON ri.res_id = r.res_id WHERE r.brgy_id = ? AND ri.item_status = 'Borrowed'",
    'total_maintenance_items' => "SELECT COUNT(ri.item_id) as count FROM resource_items ri JOIN resources r ON ri.res_id = r.res_id WHERE r.brgy_id = ? AND ri.item_status = 'Under Maintenance'",
    'total_lost_items' => "SELECT COUNT(ri.item_id) as count FROM resource_items ri JOIN resources r ON ri.res_id = r.res_id WHERE r.brgy_id = ? AND ri.item_status = 'Lost'", // Query for lost items
];

foreach ($queries as $key => $query) {
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $current_brgy_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stats[$key] = $row['count'] ?? $row['total'] ?? 0;
}

// Calculate total quantity by summing up all item statuses
$stats['total_resources_quantity'] = $stats['total_available_items'] + $stats['total_borrowed_items'] + $stats['total_maintenance_items'] + $stats['total_lost_items'];


// Fetch latest borrowed items (which are effectively 'Issued' items)
$latest_borrowed_items = [];
$stmt = $conn->prepare("
    SELECT r.req_id, r.req_date, res.res_name, u.user_full_name, b.brgy_name, r.req_quantity
    FROM requests r
    JOIN resources res ON r.res_id = res.res_id
    JOIN users u ON r.req_user_id = u.user_id
    JOIN barangays b ON r.req_brgy_id = b.brgy_id
    WHERE r.res_brgy_id = ? AND r.req_status = 'Borrowed'
    ORDER BY r.req_timestamp DESC
    LIMIT 5
");
$stmt->bind_param("i", $current_brgy_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $latest_borrowed_items[] = $row;
}

// Fetch latest returned resources
$latest_returned_resources = [];
$stmt = $conn->prepare("
    SELECT ret.return_id, ret.return_date, req.req_quantity, res.res_name, u.user_full_name, b.brgy_name
    FROM returns ret
    JOIN requests req ON ret.req_id = req.req_id
    JOIN resources res ON req.res_id = res.res_id
    JOIN users u ON req.req_user_id = u.user_id
    JOIN barangays b ON req.req_brgy_id = b.brgy_id
    WHERE ret.brgy_id = ?
    ORDER BY ret.return_date DESC
    LIMIT 5
");
$stmt->bind_param("i", $current_brgy_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $latest_returned_resources[] = $row;
}

?>