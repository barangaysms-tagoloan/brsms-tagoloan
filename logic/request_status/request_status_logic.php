<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_SESSION['role']) && strtolower($_SESSION['role']) === 'superadmin') {
}

require __DIR__ . '/../database/db.php';
require __DIR__ . '/../logging.php';

// Initialize message variables for JavaScript
$js_success_message = '';
$js_error_message = '';

if (isset($_SESSION['success_message'])) {
    $js_success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

if (isset($_SESSION['error_message'])) {
    $js_error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}


// Handle cancel request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_request'])) {
    $req_id = filter_var($_POST['req_id'], FILTER_SANITIZE_NUMBER_INT);
    $user_id = $_SESSION['user_id'];

    if (!empty($req_id)) {
        // Start transaction
        $conn->begin_transaction();
        try {
            // Verify the request belongs to the user and is pending
            // Added error checking for prepare statement
            $stmt = $conn->prepare("
                SELECT req_id FROM requests
                WHERE req_id = ? AND req_user_id = ? AND req_status = 'Pending' FOR UPDATE
            ");
            if ($stmt === false) {
                throw new Exception("Failed to prepare select statement: " . $conn->error);
            }
            $stmt->bind_param("ii", $req_id, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // Update the request status to Cancelled
                // Removed 'reject_reason' as it's not applicable for user cancellations
                $update_stmt = $conn->prepare("
                    UPDATE requests
                    SET req_status = 'Cancelled'
                    WHERE req_id = ?
                ");
                if ($update_stmt === false) {
                    throw new Exception("Failed to prepare update statement: " . $conn->error);
                }
                $update_stmt->bind_param("i", $req_id);

                if ($update_stmt->execute()) {
                    // Log the cancellation
                    logRequestCancel($user_id, $req_id);

                    // MODIFIED: Changed success message to be more general
                    $_SESSION['success_message'] = "Your request has been cancelled successfully.";
                    $conn->commit(); // Commit transaction on success
                } else {
                    throw new Exception("Failed to update the request status: " . $conn->error);
                }
            } else {
                throw new Exception("Request not found, already processed, or cannot be cancelled."); // MODIFIED: General message
            }
        } catch (Exception $e) {
            $conn->rollback(); // Rollback transaction on error
            $_SESSION['error_message'] = "Error cancelling request: " . $e->getMessage();
        }
    } else {
        $_SESSION['error_message'] = "Invalid request provided."; // MODIFIED: General message
    }

    header("Location: request_status.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$brgy_id = $_SESSION['brgy_id'];

// Fetch all requests made by this user/barangay with additional details
// ORDER BY r.req_id DESC to show newest requests first
$stmt = $conn->prepare("
    SELECT
        r.*,
        res.res_name,
        res.res_description,
        res.is_bulk,
        res.res_photo,
        b.brgy_name as owner_brgy_name,
        u.user_full_name as requester_name,
        rb.brgy_name as requester_brgy_name
    FROM requests r
    JOIN resources res ON r.res_id = res.res_id
    JOIN barangays b ON r.res_brgy_id = b.brgy_id
    JOIN barangays rb ON r.req_brgy_id = rb.brgy_id
    JOIN users u ON r.req_user_id = u.user_id
    WHERE r.req_brgy_id = ? AND r.req_user_id = ?
    ORDER BY r.req_id DESC
");
$stmt->bind_param("ii", $brgy_id, $user_id);
$stmt->execute();
$requests = $stmt->get_result();
?>