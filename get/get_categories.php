<?php
session_start();
require '../logic/database/db.php';

header('Content-Type: application/json');


if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized access.']);
    exit();
}

$current_brgy_id = $_SESSION['brgy_id'];

try {
    $categories = [];
    $stmt = $conn->prepare("SELECT category_id, category_name FROM resource_categories WHERE brgy_id = ? ORDER BY category_name ASC");
    $stmt->bind_param("i", $current_brgy_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
    $stmt->close();
    echo json_encode($categories);
} catch (Exception $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}

$conn->close();
?>
