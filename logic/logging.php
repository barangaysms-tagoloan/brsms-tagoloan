<?php

if (!isset($conn)) {
    require_once 'database/db.php';
}

function logActivity($user_id, $action, $details = null) {
    global $conn;
    
    
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'N/A';
    
    
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'N/A';
    
    $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
    
    $stmt->bind_param("issss", $user_id, $action, $details, $ip_address, $user_agent);
    
    return $stmt->execute();
}


function logLogin($user_id) {
    logActivity($user_id, "User Login", "User logged into the system.");
}

function logLogout($user_id) {
    logActivity($user_id, "User Logout", "User logged out of the system.");
}

function logResourceAdd($user_id, $resource_name) {
    logActivity($user_id, "Resource Added", "Added: " . $resource_name);
}

function logResourceEdit($user_id, $resource_name) {
    logActivity($user_id, "Resource Edited", "Edited: " . $resource_name);
}

function logResourceDelete($user_id, $resource_name) {
    logActivity($user_id, "Resource Deleted", "Deleted: " . $resource_name);
}

function logResourceItemStatusUpdate($user_id, $item_id, $old_status, $new_status, $resource_name) {
    logActivity($user_id, "Item Status Update", "Item ID: {$item_id} ({$resource_name}) from '{$old_status}' to '{$new_status}'.");
}

function logResourceBulkItemStatusUpdate($user_id, $item_ids, $new_status, $resource_name) {
    $item_count = count($item_ids);
    $item_list = implode(', ', array_slice($item_ids, 0, 5)); 
    if ($item_count > 5) $item_list .= '...';
    logActivity($user_id, "Bulk Item Status", "Updated {$item_count} items ({$resource_name}) to '{$new_status}'. IDs: {$item_list}.");
}

function logCategoryAdd($user_id, $category_name) {
    logActivity($user_id, "Category Added", "Added category: " . $category_name);
}

function logCategoryDelete($user_id, $category_name) {
    logActivity($user_id, "Category Deleted", "Deleted category: " . $category_name);
}


function logRequestCreate($user_id, $resource_name, $quantity, $status, $details = null) {
    $action = "Request " . ucfirst($status); 
    $log_details = "Requested " . $quantity . " of " . $resource_name . ". Status: " . ucfirst($status);
    if ($details) {
        $log_details .= ". " . $details;
    }
    logActivity($user_id, $action, $log_details);
}

function logRequestApprove($user_id, $request_id) {
    logActivity($user_id, "Request Approved", "Approved request ID: " . $request_id);
}

function logRequestReject($user_id, $request_id) {
    logActivity($user_id, "Request Rejected", "Rejected request ID: " . $request_id);
}

function logRequestComplete($user_id, $request_id) {
    logActivity($user_id, "Request Completed", "Completed request ID: " . $request_id);
}


function logRequestCancel($user_id, $request_id) {
    logActivity($user_id, "Request Cancelled", "Requester cancelled request ID: " . $request_id);
}

function logUserCreate($user_id, $new_user_name) {
    logActivity($user_id, "User Created", "Created user: " . $new_user_name);
}

function logUserEdit($user_id, $edited_user_name) {
    logActivity($user_id, "User Edited", "Edited user: " . $edited_user_name);
}

function logUserDelete($user_id, $deleted_user_name) {
    logActivity($user_id, "User Deleted", "Deleted user: " . $deleted_user_name);
}


function logViewActivityLogs($user_id) {
    logActivity($user_id, "View Logs", "Super Admin viewed activity logs page.");
}
