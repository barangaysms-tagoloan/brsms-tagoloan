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
$condition_filter = isset($_GET['condition']) ? $_GET['condition'] : 'all';
$sql = "
    SELECT r.*, req.req_date, req.req_quantity, res.res_name, res.res_description, res.is_bulk, res.res_photo,
           b.brgy_name as requester_brgy, u.user_full_name as requester_name, req.req_contact_number,
           req.borrow_timestamp, req.return_timestamp -- ADDED: Borrow and Return Timestamps
    FROM returns r
    JOIN requests req ON r.req_id = req.req_id
    JOIN resources res ON req.res_id = res.res_id
    JOIN barangays b ON req.req_brgy_id = b.brgy_id
    JOIN users u ON req.req_user_id = u.user_id
    WHERE r.brgy_id = ?
    ORDER BY r.return_date DESC, r.return_id DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $current_brgy_id);
$stmt->execute();
$returns_result = $stmt->get_result();

$processed_returns_data = [];

while ($row = $returns_result->fetch_assoc()) {
    $return_condition_raw = $row['return_condition'];
    $is_bulk = (bool)$row['is_bulk'];

    if ($is_bulk) {
        // Attempt to decode the JSON string for bulk items
        $item_conditions = json_decode($return_condition_raw, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($item_conditions)) {
            // Group items by their condition
            $grouped_conditions = [];
            foreach ($item_conditions as $item_condition) {
                $condition_name = $item_condition['condition'];
                if (!isset($grouped_conditions[$condition_name])) {
                    $grouped_conditions[$condition_name] = [
                        'count' => 0,
                        'items' => []
                    ];
                }
                $grouped_conditions[$condition_name]['count']++;
                $grouped_conditions[$condition_name]['items'][] = $item_condition; // Store full item details (now includes serial_number)
            }

            foreach ($grouped_conditions as $condition_name => $data) {
                $new_row = $row; // Copy original row data
                $new_row['return_condition'] = $condition_name; // Set the grouped condition
                $new_row['req_quantity'] = $data['count']; // Set the count for this condition
                $new_row['grouped_items'] = $data['items']; // Store the individual items for this group
                $processed_returns_data[] = $new_row;
            }
        } else {
            // Fallback for bulk items if JSON decoding fails (shouldn't happen if process_request_action is correct)
            $row['grouped_items'] = []; // No individual items to show
            $processed_returns_data[] = $row;
        }
    } else {
        // For non-bulk items, the condition is a simple string
        $row['grouped_items'] = []; // No individual items for single resources
        $processed_returns_data[] = $row;
    }
}

// --- NEW ADDITION: Sort the processed data by return_date (descending) and then by return_id (descending) ---
usort($processed_returns_data, function($a, $b) {
    // Compare by return_date first
    $date_a = strtotime($a['return_date']);
    $date_b = strtotime($b['return_date']);

    if ($date_a == $date_b) {
        // If dates are the same, compare by return_id (newer ID first)
        return $b['return_id'] - $a['return_id'];
    }
    // Otherwise, compare by date (newer date first)
    return $date_b - $date_a;
});
// --- END NEW ADDITION ---

// Helper function for condition colors (remains the same)
function getConditionColor($condition) {
    $condition_lower = strtolower($condition);
    if (strpos($condition_lower, 'good') !== false) {
        return 'success'; // Green for good
    } elseif (strpos($condition_lower, 'scratch') !== false || strpos(
        $condition_lower, 'minor') !== false) {
        return 'warning'; // Yellow for minor/scratches
    } elseif (strpos($condition_lower, 'damaged') !== false || strpos($condition_lower, 'lost') !== false) {
        return 'danger'; // Red for damaged or lost
    } else {
        return 'secondary'; // Grey for other/bad
    }
}
?>