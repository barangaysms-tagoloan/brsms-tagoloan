<?php
session_start();
require '../logic/database/db.php'; 
require '../logic/logging.php'; 


if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'superadmin') {
    header("Location: login.php");
    exit();
}


function uploadUserPhoto($file) {
    $target_dir = "uploads/user_photos/"; 
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true); 
    }

    $file_name = basename($file["name"]);
    $target_file = $target_dir . uniqid() . "_" . $file_name; 
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    
    $check = getimagesize($file["tmp_name"]);
    if($check !== false) {
        $uploadOk = 1;
    } else {
        $_SESSION['error_message'] = "File is not an image.";
        $uploadOk = 0;
    }

    
    if ($file["size"] > 5000000) {
        $_SESSION['error_message'] = "Sorry, your file is too large (max 5MB).";
        $uploadOk = 0;
    }

    
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
    && $imageFileType != "gif" ) {
        $_SESSION['error_message'] = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }

    
    if ($uploadOk == 0) {
        return false;
    } else {
        if (move_uploaded_file($file["tmp_name"], $target_file)) {
            return $target_file;
        } else {
            $_SESSION['error_message'] = "Sorry, there was an error uploading your file.";
            return false;
        }
    }
}



if (isset($_GET['delete_user'])) {
    $user_id = filter_var($_GET['delete_user'], FILTER_SANITIZE_NUMBER_INT);

    
    if ($user_id == $_SESSION['user_id']) {
        $_SESSION['error_message'] = 'You cannot delete your own account.'; 
        header("Location: user_management.php");
        exit();
    }

    
    $stmt_select_user = $conn->prepare("SELECT username, user_full_name, user_photo FROM users WHERE user_id = ?");
    $stmt_select_user->bind_param("i", $user_id);
    $stmt_select_user->execute();
    $result_user = $stmt_select_user->get_result();
    $row_user = $result_user->fetch_assoc();
    $deleted_username = $row_user['username'];
    $deleted_full_name = $row_user['user_full_name'];
    $user_photo_path = $row_user['user_photo'];
    $stmt_select_user->close();

    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    if ($stmt->execute()) {
        
        if ($user_photo_path && $user_photo_path != 'uploads/default_profile.png' && file_exists($user_photo_path)) {
            unlink($user_photo_path);
        }
        $_SESSION['success_message'] = 'User deleted successfully.'; 
        logUserDelete($_SESSION['user_id'], $deleted_full_name . " (" . $deleted_username . ")");
    } else {
        $_SESSION['error_message'] = 'Error deleting user.';
        logActivity($_SESSION['user_id'], "User Deletion Failed", "Failed to delete user: " . $deleted_full_name . " (" . $deleted_username . "). Error: " . $conn->error);
    }
    header("Location: user_management.php");
    exit();
}



if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_user'])) {
        
        $full_name = trim($_POST['full_name']);
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);
        $role = trim($_POST['role']);
        $brgy_name = trim($_POST['brgy_name']); 
        $brgy_id = (int)trim($_POST['brgy_id']); 
        $user_photo_path = 'uploads/default_profile.png'; 

        
        if (isset($_FILES['user_photo']) && $_FILES['user_photo']['error'] == UPLOAD_ERR_OK) {
            $uploaded_path = uploadUserPhoto($_FILES['user_photo']);
            if ($uploaded_path) {
                $user_photo_path = $uploaded_path;
            } else {
                
                logActivity($_SESSION['user_id'], "User Add Failed", "Failed to upload photo for new user: " . $full_name . " (" . $username . ").");
                header("Location: user_management.php");
                exit();
            }
        }

        
        if (empty($full_name) || empty($username) || empty($password) || empty($role) || empty($brgy_name) || empty($brgy_id)) {
            $_SESSION['error_message'] = "All fields are required.";
            logActivity($_SESSION['user_id'], "User Add Failed", "Attempted to add user with incomplete fields.");
        } elseif (!filter_var($username, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error_message'] = "Username must be a valid email address.";
            logActivity($_SESSION['user_id'], "User Add Failed", "Attempted to add user with invalid email format: " . $username);
        } else {
            
            $stmt = $conn->prepare("SELECT * FROM users WHERE LOWER(username) = LOWER(?)");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $_SESSION['error_message'] = "Username already exists.";
                logActivity($_SESSION['user_id'], "User Add Failed", "Attempted to add user with existing username: " . $username);
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (user_full_name, username, password, role, brgy_name, brgy_id, user_photo) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssssis", $full_name, $username, $hashed_password, $role, $brgy_name, $brgy_id, $user_photo_path);

                if ($stmt->execute()) {
                    $_SESSION['success_message'] = "User added successfully!";
                    logUserCreate($_SESSION['user_id'], $full_name . " (" . $username . ")");
                } else {
                    $_SESSION['error_message'] = "Error adding user: " . $conn->error;
                    logActivity($_SESSION['user_id'], "User Add Failed", "Database error adding user: " . $full_name . " (" . $username . "). Error: " . $conn->error);
                }
            }
        }
        header("Location: user_management.php");
        exit();
    } elseif (isset($_POST['edit_user'])) {
        
        $user_id = filter_var($_POST['user_id'], FILTER_SANITIZE_NUMBER_INT);
        $full_name = trim($_POST['full_name']);
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);
        $role = trim($_POST['role']);
        $brgy_name = trim($_POST['brgy_name']); 
        $brgy_id = (int)trim($_POST['brgy_id']); 

        
        $current_user_photo_path = 'uploads/default_profile.png';
        $stmt_current_photo = $conn->prepare("SELECT user_photo FROM users WHERE user_id = ?");
        $stmt_current_photo->bind_param("i", $user_id);
        $stmt_current_photo->execute();
        $result_current_photo = $stmt_current_photo->get_result();
        if ($row_current_photo = $result_current_photo->fetch_assoc()) {
            $current_user_photo_path = $row_current_photo['user_photo'];
        }
        $stmt_current_photo->close();

        $user_photo_path = $current_user_photo_path; 

        
        if (isset($_FILES['user_photo']) && $_FILES['user_photo']['error'] == UPLOAD_ERR_OK) {
            $uploaded_path = uploadUserPhoto($_FILES['user_photo']);
            if ($uploaded_path) {
                
                if ($current_user_photo_path && $current_user_photo_path != 'uploads/default_profile.png' && file_exists($current_user_photo_path)) {
                    unlink($current_user_photo_path);
                }
                $user_photo_path = $uploaded_path;
            } else {
                
                logActivity($_SESSION['user_id'], "User Edit Failed", "Failed to upload new photo for user: " . $full_name . " (" . $username . ").");
                header("Location: user_management.php");
                exit();
            }
        }

        
        if (empty($full_name) || empty($username) || empty($role) || empty($brgy_name) || empty($brgy_id)) {
            $_SESSION['error_message'] = "All fields except password are required.";
            logActivity($_SESSION['user_id'], "User Edit Failed", "Attempted to edit user " . $user_id . " with incomplete fields.");
        } elseif (!filter_var($username, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error_message'] = "Username must be a valid email address.";
            logActivity($_SESSION['user_id'], "User Edit Failed", "Attempted to edit user " . $user_id . " with invalid email format: " . $username);
        } else {
            
            $stmt = $conn->prepare("SELECT * FROM users WHERE LOWER(username) = LOWER(?) AND user_id != ?");
            $stmt->bind_param("si", $username, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $_SESSION['error_message'] = "Username already exists.";
                logActivity($_SESSION['user_id'], "User Edit Failed", "Attempted to change user " . $user_id . " username to existing: " . $username);
            } else {
                if (!empty($password)) {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE users SET user_full_name = ?, username = ?, password = ?, role = ?, brgy_name = ?, brgy_id = ?, user_photo = ? WHERE user_id = ?");
                    $stmt->bind_param("sssssisi", $full_name, $username, $hashed_password, $role, $brgy_name, $brgy_id, $user_photo_path, $user_id);
                } else {
                    $stmt = $conn->prepare("UPDATE users SET user_full_name = ?, username = ?, role = ?, brgy_name = ?, brgy_id = ?, user_photo = ? WHERE user_id = ?");
                    $stmt->bind_param("ssssisi", $full_name, $username, $role, $brgy_name, $brgy_id, $user_photo_path, $user_id);
                }

                if ($stmt->execute()) {
                    $_SESSION['success_message'] = "User updated successfully!";
                    logUserEdit($_SESSION['user_id'], $full_name . " (" . $username . ")");
                } else {
                    $_SESSION['error_message'] = "Error updating user: " . $conn->error;
                    logActivity($_SESSION['user_id'], "User Edit Failed", "Database error updating user: " . $full_name . " (" . $username . "). Error: " . $conn->error);
                }
            }
        }
        header("Location: user_management.php");
        exit();
    }
    
    
    elseif (isset($_POST['add_barangay_ajax'])) {
        header('Content-Type: application/json');
        $brgy_name = trim($_POST['brgy_name']);
        if (!empty($brgy_name)) {
            
            $stmt_check = $conn->prepare("SELECT brgy_id FROM barangays WHERE LOWER(brgy_name) = LOWER(?)");
            $stmt_check->bind_param("s", $brgy_name);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();

            if ($result_check->num_rows > 0) {
                echo json_encode(['status' => 'error', 'message' => "Barangay '{$brgy_name}' already exists."]);
                logActivity($_SESSION['user_id'], "Barangay Add Failed", "Attempted to add existing barangay: " . $brgy_name);
            } else {
                
                
                $result_max_id = $conn->query("SELECT MAX(brgy_id) AS max_id FROM barangays");
                $row_max_id = $result_max_id->fetch_assoc();
                $new_brgy_id = ($row_max_id['max_id'] ?? 0) + 1; 

                $stmt = $conn->prepare("INSERT INTO barangays (brgy_id, brgy_name) VALUES (?, ?)");
                $stmt->bind_param("is", $new_brgy_id, $brgy_name);
                if ($stmt->execute()) {
                    echo json_encode(['status' => 'success', 'message' => "Barangay '{$brgy_name}' added successfully!", 'new_barangay' => ['brgy_id' => $new_brgy_id, 'brgy_name' => $brgy_name]]);
                    logActivity($_SESSION['user_id'], "Barangay Added", "Added new barangay: " . $brgy_name . " (ID: " . $new_brgy_id . ")");
                } else {
                    echo json_encode(['status' => 'error', 'message' => "Error adding barangay: " . $stmt->error]);
                    logActivity($_SESSION['user_id'], "Barangay Add Failed", "Database error adding barangay: " . $brgy_name . ". Error: " . $stmt->error);
                }
                $stmt->close();
            }
            $stmt_check->close();
        } else {
            echo json_encode(['status' => 'error', 'message' => "Barangay name cannot be empty."]);
            logActivity($_SESSION['user_id'], "Barangay Add Failed", "Attempted to add barangay with empty name.");
        }
        exit(); 
    } elseif (isset($_POST['edit_barangay_ajax'])) {
        header('Content-Type: application/json');
        $brgy_id = filter_var($_POST['brgy_id'], FILTER_SANITIZE_NUMBER_INT);
        $brgy_name = trim($_POST['brgy_name']);
        if (!empty($brgy_name) && !empty($brgy_id)) {
            
            $stmt_check = $conn->prepare("SELECT brgy_id FROM barangays WHERE LOWER(brgy_name) = LOWER(?) AND brgy_id != ?");
            $stmt_check->bind_param("si", $brgy_name, $brgy_id);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();

            if ($result_check->num_rows > 0) {
                echo json_encode(['status' => 'error', 'message' => "Barangay name '{$brgy_name}' already exists for another barangay."]);
                logActivity($_SESSION['user_id'], "Barangay Edit Failed", "Attempted to change barangay ID " . $brgy_id . " name to existing: " . $brgy_name);
            } else {
                
                $stmt_old_name = $conn->prepare("SELECT brgy_name FROM barangays WHERE brgy_id = ?");
                $stmt_old_name->bind_param("i", $brgy_id);
                $stmt_old_name->execute();
                $result_old_name = $stmt_old_name->get_result();
                $old_brgy_name = $result_old_name->fetch_assoc()['brgy_name'];
                $stmt_old_name->close();

                $stmt = $conn->prepare("UPDATE barangays SET brgy_name = ? WHERE brgy_id = ?");
                $stmt->bind_param("si", $brgy_name, $brgy_id);
                if ($stmt->execute()) {
                    echo json_encode(['status' => 'success', 'message' => "Barangay updated successfully.", 'updated_barangay' => ['brgy_id' => $brgy_id, 'brgy_name' => $brgy_name]]);
                    logActivity($_SESSION['user_id'], "Barangay Edited", "Edited barangay ID " . $brgy_id . " from '" . $old_brgy_name . "' to '" . $brgy_name . "'");
                } else {
                    echo json_encode(['status' => 'error', 'message' => "Error updating barangay: " . $stmt->error]);
                    logActivity($_SESSION['user_id'], "Barangay Edit Failed", "Database error updating barangay ID " . $brgy_id . " to " . $brgy_name . ". Error: " . $stmt->error);
                }
                $stmt->close();
            }
            $stmt_check->close();
        } else {
            echo json_encode(['status' => 'error', 'message' => "Barangay name or ID cannot be empty."]);
            logActivity($_SESSION['user_id'], "Barangay Edit Failed", "Attempted to edit barangay with empty name or ID.");
        }
        exit(); 
    } elseif (isset($_POST['delete_barangay_ajax'])) {
        header('Content-Type: application/json');
        $brgy_id = filter_var($_POST['brgy_id'], FILTER_SANITIZE_NUMBER_INT);
        if (!empty($brgy_id)) {
            
            $stmt_select_brgy = $conn->prepare("SELECT brgy_name FROM barangays WHERE brgy_id = ?");
            $stmt_select_brgy->bind_param("i", $brgy_id);
            $stmt_select_brgy->execute();
            $result_brgy = $stmt_select_brgy->get_result();
            $deleted_brgy_name = $result_brgy->fetch_assoc()['brgy_name'];
            $stmt_select_brgy->close();

            
            $stmt_check_users = $conn->prepare("SELECT COUNT(*) FROM users WHERE brgy_id = ?");
            $stmt_check_users->bind_param("i", $brgy_id);
            $stmt_check_users->execute();
            $result_check_users = $stmt_check_users->get_result();
            $row_check_users = $result_check_users->fetch_row();
            $user_count = $row_check_users[0];
            $stmt_check_users->close();

            if ($user_count > 0) {
                echo json_encode(['status' => 'error', 'message' => "Cannot delete barangay. There are {$user_count} users assigned to it. Please reassign or delete them first."]);
                logActivity($_SESSION['user_id'], "Barangay Delete Failed", "Attempted to delete barangay " . $deleted_brgy_name . " (ID: " . $brgy_id . ") with " . $user_count . " assigned users.");
            } else {
                $stmt = $conn->prepare("DELETE FROM barangays WHERE brgy_id = ?");
                $stmt->bind_param("i", $brgy_id);
                if ($stmt->execute()) {
                    echo json_encode(['status' => 'success', 'message' => "Barangay deleted successfully."]);
                    logActivity($_SESSION['user_id'], "Barangay Deleted", "Deleted barangay: " . $deleted_brgy_name . " (ID: " . $brgy_id . ")");
                } else {
                    echo json_encode(['status' => 'error', 'message' => "Error deleting barangay: " . $stmt->error]);
                    logActivity($_SESSION['user_id'], "Barangay Delete Failed", "Database error deleting barangay " . $deleted_brgy_name . " (ID: " . $brgy_id . "). Error: " . $stmt->error);
                }
                $stmt->close();
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => "Barangay ID cannot be empty."]);
            logActivity($_SESSION['user_id'], "Barangay Delete Failed", "Attempted to delete barangay with empty ID.");
        }
        exit(); 
    } elseif (isset($_POST['fetch_user_requests'])) {
        header('Content-Type: application/json');
        $user_id = filter_var($_POST['user_id'], FILTER_SANITIZE_NUMBER_INT);

        $requests = [];
        if (!empty($user_id)) {
            
            $stmt = $conn->prepare("
                SELECT
                    r.req_id,
                    res.res_name,
                    r.req_quantity,
                    r.req_date,
                    r.return_date,
                    r.req_purpose,
                    r.req_status,
                    res.res_photo,
                    u_req.user_full_name as requester_name,
                    b_req.brgy_name as requester_brgy_name,
                    u_owner.user_full_name as owner_name,
                    b_owner.brgy_name as owner_brgy_name
                FROM requests r
                JOIN resources res ON r.res_id = res.res_id
                JOIN users u_req ON r.req_user_id = u_req.user_id
                JOIN barangays b_req ON r.req_brgy_id = b_req.brgy_id
                LEFT JOIN users u_owner ON res.brgy_id = u_owner.brgy_id AND u_owner.role = 'captain'
                LEFT JOIN barangays b_owner ON res.brgy_id = b_owner.brgy_id
                WHERE r.req_user_id = ?
                ORDER BY r.req_date DESC
            ");
            $stmt->bind_param("i", $user_id); 
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $requests[] = $row;
            }
            $stmt->close();
        }
        echo json_encode(['status' => 'success', 'requests' => $requests]);
        exit();
    }
}



$users_result = $conn->query("
    SELECT u.user_id, u.user_full_name, u.username, u.role, u.brgy_name, COALESCE(b.brgy_id, u.brgy_id) as barangay_id_display, u.user_photo
    FROM users u
    LEFT JOIN barangays b ON u.brgy_name = b.brgy_name
    WHERE u.role != 'superadmin'
    ORDER BY u.user_id ASC
");
$users_data = [];
while ($row = $users_result->fetch_assoc()) {
    $users_data[] = $row;
}


$barangays_result = $conn->query("SELECT brgy_id, brgy_name FROM barangays ORDER BY brgy_name ASC");
$barangays_data = [];
while ($row = $barangays_result->fetch_assoc()) {
    $barangays_data[] = $row;
}


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

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Management - BRSMS</title>
            <!-- Favicon (browser tab logo) -->
    <link rel="icon" type="image/png" href="uploads/BRSMS.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #5f2c82; /* From inventory.php sidebar */
            --secondary-color: #6f2dbd; /* From inventory.php sidebar hover */
            --success-color: #28a745; /* From inventory.php */
            --warning-color: #ffc107; /* From inventory.php */
            --danger-color: #dc3545; /* From inventory.php */
            --info-color: #0dcaf0; /* From inventory.php */

            /* Custom Modal Colors based on inventory.php */
            --modal-success-bg: #28a745;
            --modal-success-text: white;
            --modal-error-bg: #dc3545;
            --modal-error-text: white;
            --modal-warning-bg: #ffc107;
            --modal-warning-text: #000;
            --modal-info-bg: #0dcaf0;
            --modal-info-text: #212529; /* Changed to match inventory.php */
            --modal-borrowed-bg: #fd7e14; /* From inventory.php */
            --modal-borrowed-text: white;
            --modal-cancelled-bg: #dc3545; /* Using danger color for delete confirmation */
            --modal-cancelled-text: white;

            /* Minimalist Table Colors (from inventory.php) */
            --table-border-color: #e0e0e0;
            --table-header-bg: #f8f8f8; /* From inventory.php */
            --table-header-text: #333; /* From inventory.php */
            --table-row-hover-bg: #f5f5f5; /* From inventory.php */

            /* New color for hover effect on settings dropdown */
            --settings-hover-color: #ff8c00; /* A clean orange */

            /* Super Admin Specific Colors (from super_dashboard.php) */
            --violet: #5f2c82;
            --light-violet: rgba(138, 43, 226, 0.1);
            --dark-violet: #5f2c82;
            --card-bg: #ffffff;
            --card-shadow: rgba(0, 0, 0, 0.08);
            --card-hover-shadow: rgba(0, 0, 0, 0.15);
            --text-dark: #333;
            --text-medium: #666;
            --border-light: #e0e0e0;
        }

        body {
            overflow-x: hidden;
            background-color: #F0F8FF;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .wrapper {
            display: flex;
            width: 100%; /* Changed to 100% for sidebar layout */
            flex-grow: 1;
        }

        /* Sidebar - Copied from super_dashboard.php */
        .sidebar {
            background-color: var(--primary-color); /* Use primary color for sidebar */
            min-height: 100vh; /* Changed from 100vh to 100% to prevent stretching beyond content */
            padding: 30px 20px;
            color: white;
            display: flex; /* Use flexbox for sidebar content */
            flex-direction: column; /* Stack items vertically */
            position: sticky; /* Make sidebar sticky */
            top: 0; /* Stick to the top */
            align-self: flex-start; /* Align to the start of the flex container */
        }
        .sidebar .logo-container {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 40px;
        }
        .sidebar .logo-circle {
            background-color: white;
            border-radius: 50%;
            padding: 5px;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .sidebar .logo-circle img {
            width: 40px;
            height: 40px;
            object-fit: contain;
        }
        .sidebar .logo-text {
            font-size: 22px;
            font-weight: bold;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            padding: 15px 18px;
            display: block;
            border-radius: 8px;
            font-size: 18px;
            margin-bottom: 12px;
            transition: background 0.2s ease;
            font-weight: normal; /* Ensure sidebar text is not bold */
        }
        .sidebar a i {
            margin-right: 12px;
            width: 24px; /* Fixed width for consistent icon spacing */
            text-align: center; /* Center icons within their fixed width */
        }
        .sidebar a:hover,
        .sidebar a.active {
            background-color: rgba(255, 255, 255, 0.15);
        }
        .logout-btn {
            background-color: #e74c3c;
            color: white;
            padding: 14px 18px;
            display: block;
            width: 100%;
            text-align: left;
            border: none;
            border-radius: 8px;
            font-size: 18px;
        }
        .logout-btn:hover {
            background-color: #c0392b;
        }

        .main-content {
            flex-grow: 1;
            padding: 20px;
            display: flex;
            flex-direction: column;
            background-color: #f8f9fa; /* Lighter background for main content */
        }
        
        /* Dashboard Header (from super_dashboard.php) */
        .dashboard-top-bar {
            background: linear-gradient(135deg, var(--violet), var(--secondary-color));
            border-radius: 12px;
            padding: 20px 25px;
            color: white;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(95, 44, 130, 0.2);
        }
        
        .dashboard-top-bar h2 {
            font-size: 1.8rem;
            font-weight: 600;
            margin: 0;
        }
        
        .dashboard-top-bar p {
            margin: 5px 0 0;
            opacity: 0.9;
            font-size: 1rem;
        }

        .page-header {
            font-size: 2rem;
            font-weight: normal;
            color: #343a40;
            margin-bottom: 20px;
            display: block;
            text-align: left;
            padding-left: 15px; /* Added for consistency with super_dashboard.php */
        }

        .page-header i {
            margin-right: 10px;
            font-size: 1.5rem;
        }

        /* Table-like wrapper for cards */
        .card-wrapper {
            max-height: calc(100vh - 200px); /* Adjusted height: viewport - topbar - header - footer */
            overflow-y: auto;
            margin-bottom: 20px;
            border: 1px solid var(--table-border-color); /* Consistent with inventory.php */
            border-radius: 10px;
            background-color: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            flex-grow: 1;
        }

        .search-filter-row {
            background-color: var(--table-header-bg); /* Consistent with inventory.php */
            padding: 15px 20px;
            border-bottom: 1px solid var(--table-border-color); /* Consistent with inventory.php */
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 15px;
            position: sticky;
            top: 0;
            z-index: 11;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            border-radius: 8px 8px 0 0;
        }

        .search-filter-row .search-input-group {
            position: relative;
            flex-grow: 1;
            max-width: 400px;
        }

        .search-filter-row .search-input-group input {
            padding-left: 15px; /* Removed icon padding */
            border-radius: 25px;
            border: 1px solid var(--table-border-color); /* Consistent with inventory.php */
            width: 100%;
            font-size: 1rem;
            padding-top: 8px;
            padding-bottom: 8px;
        }

        .search-filter-row .search-input-group i {
            display: none; /* Hide search icon */
        }

        /* Card View Specific Styles */
        .card-view-container {
            padding: 25px;
        }

        /* UPDATED USER CARD STYLES */
        .user-card {
            background-color: #ffffff;
            border: none; /* Remove default border */
            border-radius: 12px; /* More rounded corners */
            margin-bottom: 20px; /* Increased margin for better separation */
            padding: 20px; /* Increased padding */
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08); /* Softer, more prominent shadow */
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
            overflow: hidden; /* Ensures content stays within rounded corners */
        }

        .user-card:hover {
            transform: translateY(-8px); /* More pronounced lift effect */
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15); /* Stronger shadow on hover */
        }

        .user-card-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px; /* Increased margin */
            padding-bottom: 15px; /* Increased padding */
            border-bottom: 1px solid #f0f0f0; /* Lighter border */
        }

        .user-card-header .user-profile-img {
            width: 60px; /* Larger profile image */
            height: 60px; /* Larger profile image */
            border-radius: 50%;
            object-fit: cover;
            margin-right: 15px; /* Increased margin */
            border: 3px solid var(--primary-color); /* Thicker border */
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); /* Shadow for profile image */
        }

        .user-card-header .user-info h5 {
            margin: 0;
            font-size: 1.25rem; /* Larger font size for name */
            color: #333;
            font-weight: 700; /* Bolder font weight */
        }

        .user-card-header .user-info p {
            margin: 0;
            font-size: 0.95rem; /* Slightly larger font size for username */
            color: #6c757d;
        }

        .user-card-body {
            flex-grow: 1;
            margin-bottom: 15px; /* Increased margin */
        }

        .user-card-body .detail-item {
            margin-bottom: 8px; /* Increased margin */
            font-size: 0.95rem; /* Slightly larger font size */
            color: #555;
        }

        .user-card-body .detail-item strong {
            color: #333; /* Darker color for labels */
            margin-right: 8px; /* Increased margin */
            font-weight: 600;
        }

        .user-card-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px; /* Increased gap */
            padding-top: 15px; /* Increased padding */
            border-top: 1px solid #f0f0f0; /* Lighter border */
        }

        .action-btn-group .btn {
            padding: 8px 15px; /* Larger buttons */
            font-size: 0.9rem; /* Larger font size */
            border-radius: 8px; /* More rounded buttons */
            min-width: 90px; /* Ensure consistent button width */
        }
        /* END UPDATED USER CARD STYLES */

        .btn-success {
            background-color: var(--success-color); /* Consistent with inventory.php */
            border-color: var(--success-color);
        }
        .btn-success:hover {
            background-color: #218838;
            border-color: #1e7e34;
        }

        .btn-danger {
            background-color: var(--danger-color); /* Consistent with inventory.php */
            border-color: var(--danger-color);
        }
        .btn-danger:hover {
            background-color: #c82333;
            border-color: #bd2130;
        }

        .btn-warning {
            background-color: var(--warning-color) !important; /* Consistent with inventory.php */
            border-color: var(--warning-color) !important;
            color: black !important; /* Consistent with inventory.php */
        }
        .btn-warning:hover {
            background-color: #e0a800 !important;
            border-color: #e0a800 !important;
        }

        /* No Data Message */
        .no-data-message {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            text-align: center;
            color: #6c757d;
            border: 1px solid var(--table-border-color); /* Consistent with inventory.php */
        }
        .no-data-message i {
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: #6c757d;
        }
        .no-data-message h5 {
            font-weight: 600;
            color: #333;
        }

        /* Custom Alert Modal Styles (from inventory.php) */
        .custom-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1060; /* Higher than Bootstrap modals (1050) */
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }

        .custom-modal-overlay.show {
            opacity: 1;
            visibility: visible;
        }

        .custom-modal {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            width: 90%;
            max-width: 400px; /* Consistent with inventory.php */
            padding: 30px; /* Consistent with inventory.php */
            text-align: center;
            position: relative;
            transform: translateY(-20px);
            transition: transform 0.3s ease;
        }

        .custom-modal-overlay.show .custom-modal {
            transform: translateY(0);
        }

        .custom-modal .close-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #aaa;
            cursor: pointer;
            z-index: 10;
        }

        .custom-modal .modal-icon {
            font-size: 3rem; /* Consistent with inventory.php */
            margin-bottom: 15px; /* Consistent with inventory.php */
            width: 60px; /* Consistent with inventory.php */
            height: 60px; /* Consistent with inventory.php */
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-left: auto;
            margin-right: auto;
            color: white;
        }

        .custom-modal .modal-title {
            font-size: 1.5rem; /* Consistent with inventory.php */
            font-weight: bold;
            margin-bottom: 10px; /* Consistent with inventory.php */
            color: #333;
        }

        .custom-modal .modal-message {
            font-size: 1rem; /* Consistent with inventory.php */
            color: #555;
            margin-bottom: 25px; /* Consistent with inventory.php */
        }

        .custom-modal .modal-actions {
            display: flex;
            justify-content: center;
            gap: 10px; /* Consistent with inventory.php */
        }

        .custom-modal .modal-actions button {
            padding: 10px 20px; /* Consistent with inventory.php */
            border-radius: 5px; /* Consistent with inventory.php */
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        /* Modal Type Styles (from inventory.php) */
        .custom-modal.success .modal-icon { background-color: var(--modal-success-bg); }
        .custom-modal.success .modal-title { color: var(--modal-success-bg); }
        .custom-modal.success .btn-primary-action { background-color: var(--modal-success-bg); color: var(--modal-success-text); border: 1px solid var(--modal-success-bg); }
        .custom-modal.success .btn-primary-action:hover { background-color: #218838; border-color: #218838; }

        .custom-modal.error .modal-icon { background-color: var(--modal-error-bg); }
        .custom-modal.error .modal-title { color: var(--modal-error-bg); }
        .custom-modal.error .btn-primary-action { background-color: var(--modal-error-bg); color: var(--modal-error-text); border: 1px solid var(--modal-error-bg); }
        .custom-modal.error .btn-primary-action:hover { background-color: #c82333; border-color: #c82333; }

        .custom-modal.warning .modal-icon { background-color: var(--modal-warning-bg); color: var(--modal-warning-text); }
        .custom-modal.warning .modal-title { color: var(--modal-warning-bg); }
        .custom-modal.warning .btn-primary-action { background-color: var(--modal-warning-bg); color: var(--modal-warning-text); border: 1px solid var(--modal-warning-bg); }
        .custom-modal.warning .btn-primary-action:hover { background-color: #e0a800; border-color: #e0a800; }

        .custom-modal.info .modal-icon { background-color: var(--modal-info-bg); color: var(--modal-info-text); }
        .custom-modal.info .modal-title { color: var(--modal-info-bg); }
        .custom-modal.info .btn-primary-action { background-color: var(--modal-info-bg); color: var(--modal-info-text); border: 1px solid var(--modal-info-bg); }
        .custom-modal.info .btn-primary-action:hover { background-color: #31d2f2; border-color: #31d2f2; }

        .custom-modal.borrowed .modal-icon { background-color: var(--modal-borrowed-bg); color: var(--modal-borrowed-text); }
        .custom-modal.borrowed .modal-title { color: var(--modal-borrowed-bg); }
        .custom-modal.borrowed .btn-primary-action { background-color: var(--modal-borrowed-bg); color: var(--modal-borrowed-text); border: 1px solid var(--modal-borrowed-bg); }
        .custom-modal.borrowed .btn-primary-action:hover { background-color: #e06e04; border-color: #e06e04; }

        /* Specific styles for delete confirmation (using 'cancelled' type) */
        .custom-modal.cancelled .modal-icon { background-color: var(--modal-cancelled-bg); }
        .custom-modal.cancelled .modal-title { color: var(--modal-cancelled-bg); }
        .custom-modal.cancelled .btn-primary-action { background-color: var(--modal-cancelled-bg); color: white; border: 1px solid var(--modal-cancelled-bg); }
        .custom-modal.cancelled .btn-primary-action:hover { background-color: #c82333; border-color: #c82333; }
        .custom-modal.cancelled .btn-secondary-action { background-color: #f0f0f0; color: #555; border: 1px solid #ccc; } /* Consistent with inventory.php */
        .custom-modal.cancelled .btn-secondary-action:hover { background-color: #e0e0e0; border-color: #adb5bd; }


        /* Specific style for delete button in barangay table to be red */
        .barangay-table-delete-btn {
            background-color: var(--danger-color) !important; /* Consistent with inventory.php */
            border-color: var(--danger-color) !important;
            color: white !important;
        }
        .barangay-table-delete-btn:hover {
            background-color: #c82333 !important;
            border-color: #c82333 !important;
        }


        /* Toast Notification */
        .toast-container {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1100;
            width: 100%;
            max-width: 400px;
            display: flex;
            justify-content: center;
        }

        .toast {
            font-size: 1.1rem;
            padding: 1rem 1.25rem;
            border-radius: 0.5rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        .toast-body {
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .toast-body i {
            font-size: 1.5rem;
        }

        /* Footer styling - Copied from super_dashboard.php */
        .footer {
            padding: 15px;
            text-align: center;
            color: #6c757d;
            font-size: 0.85rem;
            margin-top: auto;
            background-color: #f8f9fa;
            border-top: 1px solid #e9ecef;
            position: sticky;
            bottom: 0;
            width: 100%;
            z-index: 1000;
        }

        /* Form validation */
        .is-invalid {
            border-color: #dc3545 !important;
        }

        .invalid-feedback {
            display: none;
            width: 100%;
            margin-top: 0.25rem;
            font-size: 0.875em;
            color: #dc3545;
        }

        .is-invalid ~ .invalid-feedback {
            display: block;
        }

        /* Responsive adjustments for sidebar */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                min-height: unset;
                padding: 20px;
                position: relative;
            }
            .wrapper {
                flex-direction: column;
            }
            .main-content {
                padding: 15px;
            }
            .dashboard-top-bar {
                padding: 15px 20px;
            }
            .dashboard-top-bar h2 {
                font-size: 1.5rem;
            }
        }

        /* Modal Header Font */
        .modal-header .modal-title {
            font-size: 1.25rem; /* Consistent with inventory.php */
            font-weight: 500; /* Consistent with inventory.php */
            color: #333; /* Consistent with inventory.php */
            display: flex; /* Consistent with inventory.php */
            align-items: center; /* Consistent with inventory.php */
            gap: 10px; /* Consistent with inventory.php */
        }
        /* Specific modal header styling for user management modals */
        #addUserModal .modal-header,
        #editUserModal .modal-header,
        #manageBarangaysModal .modal-header,
        #editBarangayModal .modal-header,
        #viewUserRequestsModal .modal-header { /* Added for new modal */
            background-color: white; /* Changed to white */
            color: black; /* Changed to black */
            border-bottom: 1px solid var(--table-border-color); /* Consistent with inventory.php */
            padding: 1rem; /* Consistent with inventory.php */
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
        }
        #addUserModal .modal-header .modal-title,
        #editUserModal .modal-header .modal-title,
        #manageBarangaysModal .modal-header .modal-title,
        #editBarangayModal .modal-header .modal-title,
        #viewUserRequestsModal .modal-header .modal-title { /* Added for new modal */
            color: black; /* Ensure title is black on white background */
        }
        #addUserModal .modal-header .btn-close,
        #editUserModal .modal-header .btn-close,
        #manageBarangaysModal .modal-header .btn-close,
        #editBarangayModal .modal-header .btn-close,
        #viewUserRequestsModal .modal-header .btn-close { /* Added for new modal */
            filter: none; /* Remove invert filter to make close button black */
            color: #000; /* Ensure close button is black */
            opacity: 1; /* Ensure it's fully visible */
            position: static; /* Consistent with inventory.php */
        }

        /* Style for file input */
        .form-control-file {
            display: block;
            width: 100%;
            padding: 0.375rem 0.75rem;
            font-size: 1rem;
            font-weight: 400;
            line-height: 1.5;
            color: #212529;
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid #ced4da;
            appearance: none;
            border-radius: 0.375rem;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        .form-control-file:focus {
            border-color: #86b7fe;
            outline: 0;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }

        /* Styles for Manage Barangays Modal (Mirrored from Manage Categories Modal in inventory.php) */
        #manageBarangaysModal .modal-header {
            background-color: white;
            color: #333;
            border-bottom: 1px solid #dee2e6;
            padding: 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
        }
        #manageBarangaysModal .modal-header .modal-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.25rem;
            font-weight: 500;
            color: #333;
        }
        #manageBarangaysModal .modal-header .btn-close {
            filter: none;
            position: static;
        }
        #manageBarangaysModal .modal-body {
            padding: 30px;
        }
        /* Simplified Add Barangay Form Layout */
        #manageBarangaysModal #addBarangayFormAjax .input-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        #manageBarangaysModal #addBarangayFormAjax .form-control {
            width: 100%;
            border-radius: 0.375rem;
        }
        #manageBarangaysModal #addBarangayFormAjax .btn {
            width: 100%;
            border-radius: 0.375rem;
        }

        #manageBarangaysModal .barangay-list-table { /* Changed class name */
            margin-top: 20px;
            border: 1px solid #e9ecef;
            border-radius: 0.25rem;
            max-height: 250px;
            overflow-y: auto;
        }
        #manageBarangaysModal .barangay-list-table thead th {
            position: sticky;
            top: 0;
            background-color: #f8f9fa;
            z-index: 1;
            box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.1);
        }
        #manageBarangaysModal .barangay-list-table tbody td {
            vertical-align: middle;
        }
        #manageBarangaysModal .barangay-list-table .btn-danger {
            padding: 5px 10px;
            font-size: 0.85rem;
        }
        #manageBarangaysModal .modal-footer {
            border-top: none;
            padding: 20px 30px;
            background-color: #f8f9fa;
            justify-content: center;
            gap: 15px;
        }


        /* New styles for combined action button */
        .action-dropdown .dropdown-toggle {
            background-color: var(--primary-color); /* Consistent with inventory.php */
            border-color: var(--primary-color);
            color: white;
            padding: 10px 20px;
            font-size: 1rem;
            border-radius: 8px; /* Consistent with inventory.php */
            transition: background-color 0.2s ease, border-color 0.2s ease; /* Consistent with inventory.php */
        }
        .action-dropdown .dropdown-toggle:hover {
            background-color: var(--secondary-color); /* Consistent with inventory.php */
            border-color: var(--secondary-color);
        }
        .action-dropdown .dropdown-menu {
            background-color: white;
            border: 1px solid var(--table-border-color); /* Consistent with inventory.php */
            box-shadow: 0 4px 12px rgba(0,0,0,0.1); /* Consistent with inventory.php */
            padding: 10px 0; /* Consistent with inventory.php */
        }
        .action-dropdown .dropdown-item {
            color: #333;
            padding: 10px 20px; /* Consistent with inventory.php */
            font-size: 0.95rem; /* Consistent with inventory.php */
            transition: background-color 0.2s ease, color 0.2s ease; /* Consistent with inventory.php */
        }
        .action-dropdown .dropdown-item:hover {
            background-color: #f8f9fa; /* Consistent with inventory.php */
            color: var(--primary-color); /* Consistent with inventory.php */
        }
        .action-dropdown .dropdown-item i {
            margin-right: 8px; /* Space between icon and text */
        }

        /* Style for the new "Back to Dashboard" icon in main content */
        .back-to-dashboard-icon {
            font-size: 1.8rem; /* Larger icon */
            color: var(--primary-color); /* Match primary color */
            transition: color 0.2s ease;
            margin-right: 15px; /* Space from other elements */
        }

        .back-to-dashboard-icon:hover {
            color: var(--secondary-color); /* Darker on hover */
            cursor: pointer;
        }

        /* Custom styles for modal footer buttons */
        .modal-footer {
            justify-content: center; /* Center buttons */
            padding: 15px;
            border-top: 1px solid #e9ecef;
        }

        .modal-footer .btn {
            flex: 1; /* Make buttons take equal width */
            max-width: 100%; /* Ensure they don't exceed container */
            margin: 0 5px; /* Add some spacing between buttons */
        }

        /* Remove icons from form labels and modal titles */
        .modal-title i,
        .form-label i {
            display: none;
        }

        /* New styles for user list container (similar to request_status.php) */
        .user-list-container {
            padding: 15px;
        }

        /* New styles for user card row (similar to request_status.php) */
        .user-card-row {
            background-color: white;
            border-radius: 8px;
            padding: 15px 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            display: flex; /* Use flexbox for the main row */
            align-items: center; /* Vertically align items */
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            flex-grow: 1; /* Allow the card to take available space */
            margin-bottom: 15px; /* Space between cards */
        }

        .user-card-row:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .user-card-row .user-thumbnail {
            width: 60px; /* Smaller thumbnail for list view */
            height: 60px;
            border-radius: 50%; /* Circular thumbnail */
            overflow: hidden;
            flex-shrink: 0;
            margin-right: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .user-card-row .user-thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .user-card-row .row-content {
            display: flex;
            flex-wrap: wrap; /* Allow wrapping for smaller screens */
            flex-grow: 1; /* Allow content to take available space */
            align-items: center;
        }

        .user-card-row .info-group {
            padding-right: 15px; /* Space between columns */
            margin-bottom: 5px; /* Small margin for wrapped items */
            flex-shrink: 0; /* Prevent shrinking */
        }

        .user-card-row .info-group strong {
            display: block;
            font-size: 0.85rem;
            color: #6c757d;
            margin-bottom: 3px;
        }

        .user-card-row .info-group span {
            font-size: 1rem;
            color: #343a40;
            font-weight: 500;
            white-space: nowrap; /* Prevent wrapping of values */
            overflow: hidden;
            text-overflow: ellipsis; /* Add ellipsis for long values */
            display: block; /* Ensure span takes full width for ellipsis */
        }

        /* Specific width adjustments for better alignment in row cards */
        .user-card-row .info-group.name-col {
            flex-basis: 200px; /* Increased base width for name */
            flex-grow: 1;
            min-width: 150px;
        }
        .user-card-row .info-group.username-col {
            flex-basis: 200px; /* Increased base width for username */
            flex-grow: 1;
            min-width: 150px;
        }
        .user-card-row .info-group.role-col {
            flex-basis: 150px; /* Fixed width for role */
            width: 150px;
        }
        .user-card-row .info-group.barangay-col {
            flex-basis: 180px; /* Increased base width for barangay name */
            flex-grow: 1;
            min-width: 150px;
        }

        .user-card-row .user-actions {
            flex-shrink: 0;
            margin-left: auto;
            display: flex;
            gap: 5px;
            align-items: center;
            min-width: 100px; /* Adjust based on max number of buttons */
            justify-content: flex-end; /* Align buttons to the right */
        }

        /* Responsive adjustments for user row cards */
        @media (max-width: 1200px) {
            .user-card-row .info-group.name-col,
            .user-card-row .info-group.username-col,
            .user-card-row .info-group.barangay-col {
                flex-basis: calc(50% - 15px); /* Two columns per row */
            }
        }

        @media (max-width: 992px) {
            .user-card-row .info-group {
                padding-right: 10px;
            }
            .user-card-row .info-group.role-col {
                width: auto; /* Allow to shrink/grow */
                flex-basis: auto;
            }
            .user-card-row .info-group.name-col,
            .user-card-row .info-group.username-col,
            .user-card-row .info-group.barangay-col {
                flex-basis: calc(50% - 10px); /* Two columns per row */
                min-width: unset;
            }
            .user-card-row .user-actions {
                width: 100%;
                justify-content: flex-end;
                margin-top: 10px;
                min-width: unset;
            }
        }

        @media (max-width: 768px) {
            .user-card-row {
                flex-direction: column; /* Stack items vertically */
                align-items: flex-start; /* Align items to start when stacked */
            }
            .user-card-row .row-content {
                flex-direction: column; /* Stack content vertically */
                width: 100%;
            }
            .user-card-row .info-group {
                width: 100%; /* Full width for info groups */
                padding-right: 0;
                margin-bottom: 10px;
            }
            .user-card-row .user-thumbnail {
                margin-bottom: 15px; /* Add space below thumbnail when stacked */
                margin-right: 0; /* Remove right margin when stacked */
            }
        }
        /* Styles for View User Requests Modal */
        #viewUserRequestsModal .modal-body {
            max-height: 70vh; /* Limit height for scrollability */
            overflow-y: auto;
        }
        #userRequestsTable {
            width: 100%; /* Ensure table takes full width of its container */
            border-collapse: separate;
            border-spacing: 0 8px;
            table-layout: fixed; /* Important: This makes column widths behave predictably */
        }
        #userRequestsTable thead th {
            background-color: var(--table-header-bg);
            color: var(--table-header-text);
            padding: 12px 15px;
            border: none;
            font-weight: 600;
            position: sticky;
            top: 0;
            z-index: 10;
            white-space: nowrap; /* Prevent header text from wrapping */
            overflow: hidden; /* Hide overflow for headers */
            text-overflow: ellipsis; /* Add ellipsis for overflowing headers */
        }
        #userRequestsTable tbody tr {
            background-color: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        #userRequestsTable tbody tr:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        #userRequestsTable tbody td {
            padding: 10px 12px; /* Slightly reduced padding */
            vertical-align: middle;
            border-top: none;
            border-bottom: none;
            font-size: 0.9rem; /* Slightly reduced font size */
            white-space: nowrap; /* Keep content on one line */
            overflow: hidden;
            text-overflow: ellipsis; /* Add ellipsis for overflow */
        }
        #userRequestsTable tbody tr td:first-child {
            border-top-left-radius: 8px;
            border-bottom-left-radius: 8px;
        }
        #userRequestsTable tbody tr td:last-child {
            border-top-right-radius: 8px;
            border-bottom-right-radius: 8px;
        }
        #userRequestsTable .resource-img-sm {
            width: 40px; /* Slightly smaller image */
            height: 40px;
            border-radius: 5px;
            object-fit: cover;
            margin-right: 8px; /* Slightly reduced margin */
        }
        #userRequestsTable .resource-name-cell {
            display: flex;
            align-items: center;
            white-space: nowrap;
            min-width: 150px; /* Ensure enough space for resource name */
            width: 15%; /* Distribute width */
        }
        #userRequestsTable .quantity-col {
            min-width: 60px;
            width: 8%;
        }
        #userRequestsTable .date-col {
            min-width: 120px;
            width: 12%;
        }
        #userRequestsTable .purpose-col {
            min-width: 150px;
            width: 15%;
        }
        #userRequestsTable .status-col {
            min-width: 90px;
            width: 10%;
        }
        #userRequestsTable .requester-col,
        #userRequestsTable .requester-brgy-col,
        #userRequestsTable .owner-brgy-col {
            min-width: 100px;
            width: 12%; /* Distribute remaining width */
        }
        .modal-dialog.modal-xxl {
            max-width: 90vw; /* 90% of viewport width */
            width: 90vw;
        }

        .badge {
            padding: 0.4em 0.6em;
            font-size: 0.8em;
            font-weight: 600;
            border-radius: 0.375rem;
            display: inline-block;
            min-width: 70px;
            text-align: center;
        }
        .badge-pending { background-color: #ffc107; color: #000; }
        .badge-approved { background-color: #28a745; color: white; }
        .badge-rejected { background-color: #dc3545; color: white; }
        .badge-completed { background-color: #17a2b8; color: white; }
        .badge-borrowed { background-color: #fd7e14; color: white; }
        .badge-cancelled { background-color: #9E9E9E; color: white; }

    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar - Consistent with super_dashboard.php -->
        <div class="col-md-3 col-lg-2 sidebar d-flex flex-column">
            <div class="logo-container">
                <div class="logo-circle">
                    <img src="../uploads/BRSMS.png" alt="BRSMS Logo">
                </div>
                <div class="logo-text">BRSMS</div>
            </div>
            <!-- Super Admin specific navigation links -->
            <a href="super_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="user_management.php" class="active"><i class="fas fa-users"></i> User Management</a>
            <a href="activity_logs.php"><i class="fas fa-list-alt"></i> Activity Logs</a>
            <a href="system_settings.php"><i class="fas fa-cog"></i> System Settings</a>
            <!-- Add more super admin specific links here if needed -->
            <div class="mt-auto">
                <a href="../pages/logout.php" class="logout-btn mt-4"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 col-lg-10 main-content">
            <!-- Dashboard Header - Consistent with super_dashboard.php -->
            <div class="dashboard-top-bar">
                <h2> User Management</h2>
                <p>Manage user accounts and their roles within the system.</p>
            </div>

            <!-- Hidden inputs for PHP messages -->
            <input type="hidden" id="php_success_message" value="<?= htmlspecialchars($js_success_message) ?>">
            <input type="hidden" id="php_error_message" value="<?= htmlspecialchars($js_error_message) ?>">

            <div class="card-wrapper">
                <!-- Search Bar and Actions Dropdown -->
                <div class="search-filter-row">
                    <div class="search-input-group">
                        <input type="text" class="form-control" id="searchInput" placeholder="Search by Name, Username, Barangay...">
                    </div>
                    <div class="dropdown action-dropdown">
                        <button class="btn dropdown-toggle" type="button" id="mainActionsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-cogs me-2"></i> Actions
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="mainActionsDropdown">
                            <li>
                                <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#addUserModal">
                                    <i class="fas fa-user-plus me-2"></i> Add New User
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#manageBarangaysModal">
                                    <i class="fas fa-home"></i> Manage Barangays
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

                <div id="userListContainer" class="user-list-container">
                    <!-- User row cards will be rendered here by JavaScript -->
                </div>
                <!-- No data message container, initially hidden -->
                <div class="no-data-message mt-4" id="noDataMessage" style="display: none;">
                    <i class="fas fa-info-circle mb-3"></i>
                    <h5>No Users Found</h5>
                    <p class="mb-3">There are no user accounts in the system.</p>
                </div>
            </div>

            <!-- Footer - Consistent with super_dashboard.php -->
            <footer class="footer mt-auto py-1">
                <div class="container-fluid">
                    <span>&copy; <?= date('Y') ?> BRSMS. All rights reserved.</span>
                </div>
            </footer>
        </div>
    </div>

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="" id="addUserForm" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addUserModalLabel">Add New User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="user_photo" class="form-label">Profile Photo (Optional)</label>
                            <input type="file" class="form-control form-control-file" id="user_photo" name="user_photo" accept="image/*">
                            <div class="invalid-feedback">Please select a valid image file (JPG, JPEG, PNG, GIF).</div>
                        </div>
                        <div class="mb-3">
                            <label for="full_name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" required>
                            <div class="invalid-feedback">Full Name is required.</div>
                        </div>
                        <div class="mb-3">
                            <label for="username" class="form-label">Username (Email)</label>
                            <input type="email" class="form-control" id="username" name="username" required>
                            <div class="invalid-feedback">Please enter a valid email address.</div>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required minlength="6">
                            <div class="invalid-feedback">Password must be at least 6 characters.</div>
                        </div>
                        <div class="mb-3">
                            <label for="role" class="form-label">Role</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="">Select Role</option>
                                <option value="barangay_official">Barangay Official</option>
                                <option value="secretary">Secretary</option>
                                <option value="captain">Captain</option>
                                <!-- Super Admin role should generally not be assignable via this form -->
                            </select>
                            <div class="invalid-feedback">Role is required.</div>
                        </div>
                        <div class="mb-3">
                            <label for="brgy_name_select" class="form-label">Barangay</label>
                            <select class="form-select" id="brgy_name_select" name="brgy_name" required>
                                <option value="">Select Barangay</option>
                                <?php foreach ($barangays_data as $brgy): ?>
                                    <option value="<?= htmlspecialchars($brgy['brgy_name']) ?>" data-brgy-id="<?= htmlspecialchars($brgy['brgy_id']) ?>">
                                        <?= htmlspecialchars($brgy['brgy_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <input type="hidden" id="brgy_id_hidden" name="brgy_id">
                            <div class="invalid-feedback">Barangay is required.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="add_user" class="btn btn-primary">Add User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="" id="editUserForm" enctype="multipart/form-data">
                    <input type="hidden" name="user_id" id="edit_user_id">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_user_photo" class="form-label">Profile Photo (Optional)</label>
                            <input type="file" class="form-control form-control-file" id="edit_user_photo" name="user_photo" accept="image/*">
                            <div class="invalid-feedback">Please select a valid image file (JPG, JPEG, PNG, GIF).</div>
                            <small class="form-text text-muted">Leave blank to keep current photo.</small>
                        </div>
                        <div class="mb-3">
                            <label for="edit_full_name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="edit_full_name" name="full_name" required>
                            <div class="invalid-feedback">Full Name is required.</div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_username" class="form-label">Username (Email)</label>
                            <input type="email" class="form-control" id="edit_username" name="username" required>
                            <div class="invalid-feedback">Please enter a valid email address.</div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_password" class="form-label">Password (leave blank to keep current)</label>
                            <input type="password" class="form-control" id="edit_password" name="password" minlength="6">
                            <div class="invalid-feedback">Password must be at least 6 characters if provided.</div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_role" class="form-label">Role</label>
                            <select class="form-select" id="edit_role" name="role" required>
                                <option value="">Select Role</option>
                                <option value="barangay_official">Barangay Official</option>
                                <option value="secretary">Secretary</option>
                                <option value="captain">Captain</option>
                                <option value="superadmin">Super Admin</option>
                            </select>
                            <div class="invalid-feedback">Role is required.</div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_brgy_name_select" class="form-label">Barangay</label>
                            <select class="form-select" id="edit_brgy_name_select" name="brgy_name" required>
                                <option value="">Select Barangay</option>
                                <?php foreach ($barangays_data as $brgy): ?>
                                    <option value="<?= htmlspecialchars($brgy['brgy_name']) ?>" data-brgy-id="<?= htmlspecialchars($brgy['brgy_id']) ?>">
                                        <?= htmlspecialchars($brgy['brgy_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <input type="hidden" id="edit_brgy_id_hidden" name="brgy_id">
                            <div class="invalid-feedback">Barangay is required.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="edit_user" class="btn btn-warning">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Manage Barangays Modal (Updated Layout) -->
    <div class="modal fade" id="manageBarangaysModal" tabindex="-1" aria-labelledby="manageBarangaysModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="manageBarangaysModalLabel">Manage Barangays</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6 class="mb-3">Add New Barangay</h6>
                    <form id="addBarangayFormAjax" class="mb-4">
                        <div class="mb-3">
                            <label for="new_barangay_name" class="form-label visually-hidden">Barangay Name</label>
                            <input type="text" class="form-control" id="new_barangay_name" name="brgy_name" placeholder="Enter new barangay name" required>
                            <div class="invalid-feedback">Barangay name cannot be empty.</div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-plus me-1"></i> Add Barangay</button>
                    </form>

                    <h6 class="mb-3">Existing Barangays</h6>
                    <div class="table-responsive barangay-list-table">
                        <table class="table table-striped table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th style="display: none;">ID</th> <!-- Hidden ID column -->
                                    <th>Barangay Name</th>
                                    <th style="width: 120px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="barangayTableBody">
                                <!-- Barangays will be rendered here by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <!-- No buttons here, consistent with inventory.php category modal -->
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Barangay Modal (nested within user_management.php) -->
    <div class="modal fade" id="editBarangayModal" tabindex="-1" aria-labelledby="editBarangayModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editBarangayModalLabel">Edit Barangay</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editBarangayFormAjax">
                    <div class="modal-body">
                        <input type="hidden" name="brgy_id" id="edit_brgy_id">
                        <div class="mb-3">
                            <label for="edit_brgy_name" class="form-label">Barangay Name</label>
                            <input type="text" class="form-control" id="edit_brgy_name" name="brgy_name" required>
                            <div class="invalid-feedback">Barangay name cannot be empty.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="edit_barangay_ajax" class="btn btn-warning w-100">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View User Requests Modal -->
    <div class="modal fade" id="viewUserRequestsModal" tabindex="-1" aria-labelledby="viewUserRequestsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xxl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewUserRequestsModalLabel">Requests for <span id="userRequestsFullName"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table" id="userRequestsTable"> <!-- Removed table-hover class -->
                            <thead>
                                <tr>
                                    <th class="resource-col">Resource</th>
                                    <th class="purpose-col">Quantity</th>
                                    <th class="date-col">Request Date</th>
                                    <th class="date-col">Return Date</th>
                                    <th class="status-col">Status</th>
                                    <th class="requester-col">Requester</th>
                                    <th class="requester-brgy-col">Requester Brgy</th>
                                    <th class="owner-brgy-col">Owner Brgy</th>
                                </tr>
                            </thead>
                            <tbody id="userRequestsTableBody">
                                <!-- User requests will be loaded here by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                    <div class="no-data-message mt-4" id="noUserRequestsMessage" style="display: none;">
                        <i class="fas fa-info-circle mb-3"></i>
                        <h5>No Requests Found</h5>
                        <p class="mb-3">This user has no associated requests.</p>
                    </div>
                </div>
                <!-- Removed the "Close" button from the modal-footer -->
                <div class="modal-footer" style="display: none;">
                    <!-- This footer is now empty as per request -->
                </div>
            </div>
        </div>
    </div>

    <!-- Custom Alert Modal Structure (Unified for all custom alerts) -->
    <div class="custom-modal-overlay" id="unifiedCustomAlertOverlay">
        <div class="custom-modal" id="unifiedCustomAlertModal">
            <!-- Added data-bs-dismiss for Bootstrap's close button functionality -->
            <button type="button" class="close-btn" aria-label="Close">
                <i class="fas fa-times"></i>
            </button>
            <div class="modal-icon"></div>
            <h4 class="modal-title"></h4>
            <p class="modal-message"></p>
            <div class="modal-actions">
                <!-- Buttons will be dynamically added/removed by JS -->
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div class="toast-container">
        <div id="actionToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="3000">
            <div class="toast-body" id="toastMessage">
                <!-- Icon and message will be inserted here by JavaScript -->
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Added jQuery for spinner functionality -->
    <script>
        // Pass PHP data to JavaScript
        const usersData = <?= json_encode($users_data) ?>;
        const currentUserId = <?= json_encode($_SESSION['user_id']) ?>; // Pass current user ID for delete check
        let barangaysData = <?= json_encode($barangays_data) ?>; // Make it 'let' so it can be updated

        document.addEventListener('DOMContentLoaded', function() {
            const userListContainer = document.getElementById('userListContainer'); // Changed from cardView
            const searchInput = document.getElementById('searchInput');
            const noDataMessageDiv = document.getElementById('noDataMessage');
            const barangayTableBody = document.getElementById('barangayTableBody');
            const manageBarangaysModalElement = document.getElementById('manageBarangaysModal');
            const manageBarangaysModal = new bootstrap.Modal(manageBarangaysModalElement); // Initialize Bootstrap Modal object
            const editBarangayModalElement = document.getElementById('editBarangayModal');
            const editBarangayModal = new bootstrap.Modal(editBarangayModalElement);
            const viewUserRequestsModalElement = document.getElementById('viewUserRequestsModal'); // New modal element
            const viewUserRequestsModal = new bootstrap.Modal(viewUserRequestsModalElement); // New modal object

            // --- Custom Alert Modal Functions (Unified) ---
            const unifiedCustomAlertOverlay = document.getElementById('unifiedCustomAlertOverlay');
            const unifiedCustomAlertModal = document.getElementById('unifiedCustomAlertModal');
            const unifiedCustomAlertCloseBtn = unifiedCustomAlertModal.querySelector('.close-btn');

            function showCustomAlert(type, title, message, actions) {
                const iconDiv = unifiedCustomAlertModal.querySelector('.modal-icon');
                const titleDiv = unifiedCustomAlertModal.querySelector('.modal-title');
                const messageDiv = unifiedCustomAlertModal.querySelector('.modal-message');
                const actionsDiv = unifiedCustomAlertModal.querySelector('.modal-actions');

                // Reset classes and content
                unifiedCustomAlertModal.classList.remove('success', 'error', 'warning', 'info', 'borrowed', 'cancelled');
                iconDiv.className = 'modal-icon'; // Reset icon class
                actionsDiv.innerHTML = ''; // Clear previous buttons

                // Set type-specific styles and icon
                if (type === 'success') {
                    unifiedCustomAlertModal.classList.add('success');
                    iconDiv.innerHTML = '<i class="fas fa-check-circle"></i>';
                } else if (type === 'error') {
                    unifiedCustomAlertModal.classList.add('error');
                    iconDiv.innerHTML = '<i class="fas fa-times-circle"></i>';
                } else if (type === 'warning') {
                    unifiedCustomAlertModal.classList.add('warning');
                    iconDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i>';
                } else if (type === 'info') {
                    unifiedCustomAlertModal.classList.add('info');
                    iconDiv.innerHTML = '<i class="fas fa-info-circle"></i>';
                } else if (type === 'borrowed') { // Using for general info/action confirmation
                    unifiedCustomAlertModal.classList.add('borrowed');
                    iconDiv.innerHTML = '<i class="fas fa-question-circle"></i>';
                } else if (type === 'cancelled') { // Using for delete confirmation
                    unifiedCustomAlertModal.classList.add('cancelled');
                    iconDiv.innerHTML = '<i class="fas fa-trash-alt"></i>';
                }

                titleDiv.textContent = title;
                messageDiv.innerHTML = message; // Use innerHTML for message to allow strong tag

                // Add action buttons
                actions.forEach(action => {
                    const button = document.createElement('button');
                    button.textContent = action.text;
                    button.classList.add(action.class);
                    button.addEventListener('click', () => {
                        if (action.callback) {
                            action.callback();
                        }
                        hideCustomAlert();
                    });
                    actionsDiv.appendChild(button);
                });

                unifiedCustomAlertOverlay.classList.add('show');
            }

            function hideCustomAlert() {
                unifiedCustomAlertOverlay.classList.remove('show');
            }

            // Event listener for the close button in the custom alert modal
            unifiedCustomAlertCloseBtn.addEventListener('click', hideCustomAlert);

            // Close modal on overlay click
            unifiedCustomAlertOverlay.addEventListener('click', (e) => {
                if (e.target === unifiedCustomAlertOverlay) {
                    hideCustomAlert();
                }
            });

            // --- Toast Notification ---
            const actionToast = new bootstrap.Toast(document.getElementById('actionToast'), {
                delay: 3000
            });

            function showToast(message, type = 'success') {
                const toastBody = document.getElementById('toastMessage');
                let iconClass = '';
                let bgColorClass = '';

                if (type === 'success') {
                    iconClass = 'fas fa-check-circle';
                    bgColorClass = 'text-bg-success';
                } else if (type === 'error') {
                    iconClass = 'fas fa-times-circle';
                    bgColorClass = 'text-bg-danger';
                } else if (type === 'warning') {
                    iconClass = 'fas fa-exclamation-triangle';
                    bgColorClass = 'text-bg-warning';
                } else if (type === 'info') {
                    iconClass = 'fas fa-info-circle';
                    bgColorClass = 'text-bg-info';
                } else {
                    iconClass = 'fas fa-info-circle';
                    bgColorClass = 'text-bg-secondary';
                }

                toastBody.innerHTML = `<i class="${iconClass}"></i> <span>${message}</span>`;
                const toastEl = document.getElementById('actionToast');
                toastEl.classList.remove('text-bg-success', 'text-bg-danger', 'text-bg-warning', 'text-bg-info', 'text-bg-primary', 'text-bg-secondary');
                toastEl.classList.add(bgColorClass);
                actionToast.show();
            }

            // Display PHP messages on page load using the custom alert/toast
            const phpSuccessMessage = document.getElementById('php_success_message').value;
            const phpErrorMessage = document.getElementById('php_error_message').value;

            if (phpSuccessMessage) {
                showToast(phpSuccessMessage, 'success');
            } else if (phpErrorMessage) {
                showToast(phpErrorMessage, 'error');
            }

            // Initial rendering of the cards
            filterAndRenderUserCards(); // Changed function name
            renderBarangayTable(); // Render barangay table initially

            // Event listener for search input
            searchInput.addEventListener('keyup', filterAndRenderUserCards); // Changed function name

            // Function to render the user row cards
            function filterAndRenderUserCards() { // Changed function name
                const searchTerm = searchInput.value.toLowerCase();
                const filteredData = usersData.filter(user => {
                    const searchString = `${user.user_full_name} ${user.username} ${user.role} ${user.brgy_name} ${user.barangay_id_display}`.toLowerCase();
                    return searchString.includes(searchTerm);
                });

                let cardsHtml = '';

                if (filteredData.length > 0) {
                    filteredData.forEach(user => {
                        const userPhotoSrc = user.user_photo && user.user_photo !== 'uploads/default_profile.png' ? user.user_photo : 'uploads/default_profile.png';
                        cardsHtml += `
                            <div class="user-card-row" data-search="${(user.user_full_name + " " + user.username + " " + user.role + " " + user.brgy_name + " " + user.barangay_id_display).toLowerCase()}" data-user-id="${user.user_id}">
                                <div class="user-thumbnail">
                                    <img src="${userPhotoSrc}" alt="Profile Photo">
                                </div>
                                <div class="row-content">
                                    <div class="info-group name-col">
                                        <strong>Full Name:</strong>
                                        <span>${htmlspecialchars(user.user_full_name)}</span>
                                    </div>
                                    <div class="info-group username-col">
                                        <strong>Username:</strong>
                                        <span>${htmlspecialchars(user.username)}</span>
                                    </div>
                                    <div class="info-group role-col">
                                        <strong>Role:</strong>
                                        <span>${htmlspecialchars(user.role.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()))}</span>
                                    </div>
                                    <div class="info-group barangay-col">
                                        <strong>Barangay:</strong>
                                        <span>${htmlspecialchars(user.brgy_name || 'N/A')}</span>
                                    </div>
                                </div>
                                <div class="user-actions">
                                    <button class="btn btn-sm btn-info view-user-requests-btn"
                                            data-user-id="${user.user_id}"
                                            data-user-fullname="${htmlspecialchars(user.user_full_name)}"
                                            title="View Requests">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-warning edit-user-btn"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editUserModal"
                                            data-id="${user.user_id}"
                                            data-fullname="${htmlspecialchars(user.user_full_name)}"
                                            data-username="${htmlspecialchars(user.username)}"
                                            data-role="${htmlspecialchars(user.role)}"
                                            data-brgy="${htmlspecialchars(user.brgy_name)}"
                                            data-brgyid="${htmlspecialchars(user.barangay_id_display)}"
                                            data-userphoto="${htmlspecialchars(user.user_photo)}"
                                            title="Edit User">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-user-btn"
                                            data-id="${user.user_id}"
                                            data-fullname="${htmlspecialchars(user.user_full_name)}"
                                            title="Delete User">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        `;
                    });
                    userListContainer.innerHTML = cardsHtml;
                    noDataMessageDiv.style.display = 'none'; // Hide no data message if there's data
                } else {
                    userListContainer.innerHTML = ''; // Clear cards if no data
                    if (usersData.length === 0) {
                        noDataMessageDiv.querySelector('h5').textContent = 'No Users Found';
                        noDataMessageDiv.querySelector('p').textContent = 'There are no user accounts in the system.';
                    } else {
                        noDataMessageDiv.querySelector('h5').textContent = 'No Matching Users Found';
                        noDataMessageDiv.querySelector('p').textContent = 'Your search did not return any user accounts.';
                    }
                    noDataMessageDiv.style.display = 'block'; // Show no data message
                }
                attachActionButtonsHandlers(); // Re-attach listeners for new elements
            }

            // Function to render the Barangay table
            function renderBarangayTable() {
                let tableHtml = '';
                if (barangaysData.length > 0) {
                    barangaysData.sort((a, b) => a.brgy_name.localeCompare(b.brgy_name)); // Sort by name
                    barangaysData.forEach(barangay => {
                        tableHtml += `
                            <tr>
                                <td style="display: none;">${htmlspecialchars(barangay.brgy_id)}</td> <!-- Hidden ID column -->
                                <td>${htmlspecialchars(barangay.brgy_name)}</td>
                                <td>
                                    <button class="btn btn-sm btn-danger btn-action barangay-table-delete-btn"
                                            data-brgy-id="${htmlspecialchars(barangay.brgy_id)}"
                                            data-brgy-name="${htmlspecialchars(barangay.brgy_name)}">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                } else {
                    tableHtml = `
                        <tr>
                            <td colspan="2" class="text-center text-muted py-3">No barangays found.</td> <!-- Adjusted colspan -->
                        </tr>
                    `;
                }
                barangayTableBody.innerHTML = tableHtml;
                attachBarangayActionButtonsHandlers(); // Re-attach listeners for new elements
                updateBarangayDropdowns(); // Update dropdowns after table render
            }

            // Helper function for HTML escaping
            function htmlspecialchars(str) {
                if (typeof str !== 'string') {
                    str = String(str);
                }
                const map = {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                };
                return str.replace(/[&<>"']/g, function(m) { return map[m]; });
            }

            // Helper function for status colors
            function getStatusColorJS(status) {
                const status_lower = status.toLowerCase();
                switch(status_lower) {
                    case 'pending': return 'pending';
                    case 'approved': return 'approved';
                    case 'rejected': return 'rejected';
                    case 'completed': return 'completed';
                    case 'borrowed': return 'borrowed';
                    case 'cancelled': return 'cancelled';
                    default: return 'secondary';
                }
            }

            // Helper function to format date
            function formatDate(dateString) {
                if (!dateString || dateString === '0000-00-00') return 'N/A';
                const date = new Date(dateString);
                return date.toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                });
            }

            // --- User Action Button Handlers ---
            function attachActionButtonsHandlers() {
                // Handle edit user button clicks to populate the edit modal
                document.querySelectorAll('.edit-user-btn').forEach(button => {
                    button.onclick = function() {
                        const userId = this.getAttribute('data-id');
                        const fullName = this.getAttribute('data-fullname');
                        const username = this.getAttribute('data-username');
                        const role = this.getAttribute('data-role');
                        const brgyName = this.getAttribute('data-brgy');
                        const brgyId = this.getAttribute('data-brgyid');
                        const userPhoto = this.getAttribute('data-userphoto'); // Get user photo path

                        document.getElementById('edit_user_id').value = userId;
                        document.getElementById('edit_full_name').value = fullName;
                        document.getElementById('edit_username').value = username;
                        document.getElementById('edit_role').value = role;

                        // Set selected barangay in dropdown and hidden ID
                        const editBrgySelect = document.getElementById('edit_brgy_name_select');
                        editBrgySelect.value = brgyName; // Set the selected option by value (barangay name)
                        document.getElementById('edit_brgy_id_hidden').value = brgyId; // Set the hidden ID

                        // Clear password field and validation on modal open
                        document.getElementById('edit_password').value = '';
                        document.getElementById('edit_password').classList.remove('is-invalid');
                        document.getElementById('edit_full_name').classList.remove('is-invalid');
                        document.getElementById('edit_username').classList.remove('is-invalid');
                        document.getElementById('edit_role').classList.remove('is-invalid');
                        document.getElementById('edit_brgy_name_select').classList.remove('is-invalid');
                        document.getElementById('edit_user_photo').classList.remove('is-invalid'); // Clear photo validation
                        document.getElementById('edit_user_photo').value = ''; // Clear file input
                    };
                });

                // Handle delete user button clicks with custom alert
                document.querySelectorAll('.delete-user-btn').forEach(button => {
                    button.onclick = function() {
                        const userId = this.getAttribute('data-id');
                        const userName = this.getAttribute('data-fullname');

                        if (parseInt(userId) === parseInt(currentUserId)) {
                            showToast('You cannot delete your own account.', 'error');
                            return;
                        }

                        showCustomAlert(
                            'cancelled', // Using 'cancelled' type for delete confirmation
                            'Confirm Deletion',
                            `Are you sure you want to delete user "<strong>${userName}</strong>"? This action cannot be undone.`,
                            [
                                { text: 'Cancel', class: 'btn-secondary-action' },
                                { text: 'Delete', class: 'btn-primary-action', callback: () => {
                                    deleteUser(userId);
                                }}
                            ]
                        );
                    };
                });

                // Handle view user requests button clicks
                document.querySelectorAll('.view-user-requests-btn').forEach(button => {
                    button.onclick = function() {
                        const userId = this.getAttribute('data-user-id');
                        const userFullName = this.getAttribute('data-user-fullname');
                        document.getElementById('userRequestsFullName').textContent = userFullName;
                        fetchUserRequests(userId);
                    };
                });
            }

            function deleteUser(userId) {
                // This will trigger a full page reload after PHP processes the deletion
                window.location.href = `user_management.php?delete_user=${userId}`;
            }

            // Function to fetch and display user requests
            async function fetchUserRequests(userId) {
                const userRequestsTableBody = document.getElementById('userRequestsTableBody');
                const noUserRequestsMessage = document.getElementById('noUserRequestsMessage');
                userRequestsTableBody.innerHTML = '<tr><td colspan="9" class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div> Loading requests...</td></tr>';
                noUserRequestsMessage.style.display = 'none';

                try {
                    const formData = new FormData();
                    formData.append('fetch_user_requests', '1');
                    formData.append('user_id', userId);

                    const response = await fetch('user_management.php', {
                        method: 'POST',
                        body: formData
                    });
                    const result = await response.json();

                    if (result.status === 'success') {
                        if (result.requests.length > 0) {
                            let requestsHtml = '';
                            result.requests.forEach(request => {
                                const photo_src = request.res_photo ? 'uploads/' + request.res_photo : 'images/default-item.jpg';
                                const status_class = getStatusColorJS(request.req_status);
                                requestsHtml += `
                                    <tr>
                                        <td class="resource-name-cell">${htmlspecialchars(request.res_name)}</td>
                                        <td class="quantity-col">${htmlspecialchars(request.req_quantity)}</td>
                                        <td class="date-col">${formatDate(request.req_date)}</td>
                                        <td class="date-col">${formatDate(request.return_date)}</td>
                                        <td class="status-col"><span class="badge rounded-pill badge-${status_class}">${htmlspecialchars(request.req_status)}</span></td>
                                        <td class="requester-col">${htmlspecialchars(request.requester_name || 'N/A')}</td>
                                        <td class="requester-brgy-col">${htmlspecialchars(request.requester_brgy_name || 'N/A')}</td>
                                        <td class="owner-brgy-col">${htmlspecialchars(request.owner_brgy_name || 'N/A')}</td>
                                    </tr>
                                `;
                            });
                            userRequestsTableBody.innerHTML = requestsHtml;
                        } else {
                            userRequestsTableBody.innerHTML = '';
                            noUserRequestsMessage.style.display = 'block';
                        }
                    } else {
                        userRequestsTableBody.innerHTML = `<tr><td colspan="9" class="text-center text-danger">Error loading requests: ${htmlspecialchars(result.message || 'Unknown error')}</td></tr>`;
                        noUserRequestsMessage.style.display = 'none';
                        showToast('Error fetching user requests.', 'error');
                    }
                } catch (error) {
                    console.error('Error fetching user requests:', error);
                    userRequestsTableBody.innerHTML = `<tr><td colspan="9" class="text-center text-danger">An error occurred while fetching requests.</td></tr>`;
                    noUserRequestsMessage.style.display = 'none';
                    showToast('An error occurred while fetching user requests.', 'error');
                } finally {
                    viewUserRequestsModal.show();
                }
            }


            // --- Barangay Action Button Handlers ---
            function attachBarangayActionButtonsHandlers() {
                // Populate Edit Barangay Modal
                document.querySelectorAll('.edit-barangay-btn').forEach(button => {
                    button.onclick = function() {
                        const brgyId = this.getAttribute('data-brgy-id');
                        const brgyName = this.getAttribute('data-brgy-name');

                        document.getElementById('edit_brgy_id').value = brgyId;
                        document.getElementById('edit_brgy_name').value = brgyName;

                        // Show the edit barangay modal
                        editBarangayModal.show();
                    };
                });

                // Handle delete barangay button clicks with custom alert
                document.querySelectorAll('.barangay-table-delete-btn').forEach(button => {
                    button.onclick = function() {
                        const brgyId = this.getAttribute('data-brgy-id');
                        const brgyName = this.getAttribute('data-brgy-name');

                        // Hide the manageBarangaysModal first
                        manageBarangaysModal.hide();

                        // Wait for the manageBarangaysModal to be fully hidden before showing the custom alert
                        manageBarangaysModalElement.addEventListener('hidden.bs.modal', function handler() {
                            manageBarangaysModalElement.removeEventListener('hidden.bs.modal', handler); // Remove listener after it fires once
                            showCustomAlert(
                                'cancelled', // Using 'cancelled' type for delete confirmation
                                'Confirm Deletion',
                                `Are you sure you want to delete barangay "<strong>${brgyName}</strong>"?<br><span class="text-danger small">This action cannot be undone. You can only delete barangays that have no users assigned to them.</span>`,
                                [
                                    { text: 'Cancel', class: 'btn-secondary-action', callback: () => {
                                        // If cancelled, optionally re-show the manageBarangaysModal
                                        manageBarangaysModal.show();
                                    }},
                                    { text: 'Delete', class: 'btn-primary-action', callback: () => {
                                        deleteBarangayAjax(brgyId);
                                    }}
                                ]
                            );
                        });
                    };
                });
            }

            // --- AJAX Functions for Barangay Management ---

            // Add Barangay
            document.getElementById('addBarangayFormAjax').addEventListener('submit', async function(e) {
                e.preventDefault();
                const form = e.target;
                const newBarangayNameInput = document.getElementById('new_barangay_name');

                // Client-side validation for empty field
                if (!newBarangayNameInput.value.trim()) {
                    newBarangayNameInput.classList.add('is-invalid');
                    return; // Stop submission
                } else {
                    newBarangayNameInput.classList.remove('is-invalid');
                }

                const $button = $(form).find('button[type="submit"]');
                $button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Adding...');

                const formData = new FormData(form);
                formData.append('add_barangay_ajax', '1'); // Indicate AJAX request

                try {
                    const response = await fetch('user_management.php', {
                        method: 'POST',
                        body: formData
                    });
                    const result = await response.json();

                    if (result.status === 'success') {
                        showToast(result.message, 'success');
                        form.reset(); // Clear form
                        barangaysData.push(result.new_barangay); // Add new barangay to JS array
                        renderBarangayTable(); // Re-render table
                    } else {
                        showToast(result.message, 'error');
                        // If there's an error, re-add the invalid class if the field is still empty
                        if (!newBarangayNameInput.value.trim()) {
                             newBarangayNameInput.classList.add('is-invalid');
                        }
                    }
                } catch (error) {
                    console.error('Error adding barangay:', error);
                    showToast('An error occurred while adding barangay.', 'error');
                } finally {
                    $button.prop('disabled', false).html('<i class="fas fa-plus me-1"></i> Add Barangay');
                }
            });

            // Edit Barangay
            document.getElementById('editBarangayFormAjax').addEventListener('submit', async function(e) {
                e.preventDefault();
                const form = e.target;
                const editBrgyNameInput = document.getElementById('edit_brgy_name');

                // Client-side validation for empty field
                if (!editBrgyNameInput.value.trim()) {
                    editBrgyNameInput.classList.add('is-invalid');
                    return; // Stop submission
                } else {
                    editBrgyNameInput.classList.remove('is-invalid');
                }

                const $button = $(form).find('button[type="submit"]');
                $button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...');

                const formData = new FormData(form);
                formData.append('edit_barangay_ajax', '1'); // Indicate AJAX request

                try {
                    const response = await fetch('user_management.php', {
                        method: 'POST',
                        body: formData
                    });
                    const result = await response.json();

                    if (result.status === 'success') {
                        showToast(result.message, 'success');
                        editBarangayModal.hide(); // Hide modal on success

                        // Update barangaysData array
                        const index = barangaysData.findIndex(b => b.brgy_id == result.updated_barangay.brgy_id);
                        if (index !== -1) {
                            barangaysData[index].brgy_name = result.updated_barangay.brgy_name;
                        }
                        renderBarangayTable(); // Re-render table
                    } else {
                        showToast(result.message, 'error');
                        // If there's an error, re-add the invalid class if the field is still empty
                        if (!editBrgyNameInput.value.trim()) {
                            editBrgyNameInput.classList.add('is-invalid');
                        }
                    }
                } catch (error) {
                    console.error('Error editing barangay:', error);
                    showToast('An error occurred while editing barangay.', 'error');
                } finally {
                    $button.prop('disabled', false).html('Save Changes');
                }
            });

            // Delete Barangay
            async function deleteBarangayAjax(brgyId) {
                const formData = new FormData();
                formData.append('brgy_id', brgyId);
                formData.append('delete_barangay_ajax', '1'); // Indicate AJAX request

                try {
                    const response = await fetch('user_management.php', {
                        method: 'POST',
                        body: formData
                    });
                    const result = await response.json();

                    if (result.status === 'success') {
                        showToast(result.message, 'success');
                        // Remove from barangaysData array
                        barangaysData = barangaysData.filter(b => b.brgy_id != brgyId);
                        renderBarangayTable(); // Re-render table
                    } else {
                        showToast(result.message, 'error');
                    }
                } catch (error) {
                    console.error('Error deleting barangay:', error);
                    showToast('An error occurred while deleting barangay.', 'error');
                } finally {
                    // Always re-show the manageBarangaysModal after delete attempt (success or failure)
                    manageBarangaysModal.show();
                }
            }

            // --- Form Validation ---
            function validateForm(formId) {
                const form = document.getElementById(formId);
                let isValid = true;

                // Reset validation states
                form.querySelectorAll('.form-control, .form-select').forEach(input => {
                    input.classList.remove('is-invalid');
                });

                // Full Name (for user forms)
                const fullNameInput = form.querySelector('[name="full_name"]');
                if (fullNameInput && !fullNameInput.value.trim()) {
                    fullNameInput.classList.add('is-invalid');
                    isValid = false;
                }

                // Username (Email) (for user forms)
                const usernameInput = form.querySelector('[name="username"]');
                if (usernameInput && (!usernameInput.value.trim() || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(usernameInput.value.trim()))) {
                    usernameInput.classList.add('is-invalid');
                    isValid = false;
                }

                // Password (only for add user, or if provided for edit user)
                const passwordInput = form.querySelector('[name="password"]');
                if (passwordInput) { // Check if password field exists
                    if (formId === 'addUserForm' && passwordInput.value.length < 6) {
                        passwordInput.classList.add('is-invalid');
                        isValid = false;
                    } else if (formId === 'editUserForm' && passwordInput.value !== '' && passwordInput.value.length < 6) {
                        passwordInput.classList.add('is-invalid');
                        isValid = false;
                    }
                }

                // Role (for user forms)
                const roleInput = form.querySelector('[name="role"]');
                if (roleInput && !roleInput.value) {
                    roleInput.classList.add('is-invalid');
                    isValid = false;
                }

                // Barangay Name Select (for user forms and add barangay form)
                const brgyNameSelect = form.querySelector('[name="brgy_name"]');
                if (brgyNameSelect && !brgyNameSelect.value) {
                    brgyNameSelect.classList.add('is-invalid');
                    isValid = false;
                }

                // User Photo (optional, but validate if provided)
                const userPhotoInput = form.querySelector('[name="user_photo"]');
                if (userPhotoInput && userPhotoInput.files.length > 0) {
                    const file = userPhotoInput.files[0];
                    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                    const maxSize = 5 * 1024 * 1024; // 5MB

                    if (!allowedTypes.includes(file.type)) {
                        userPhotoInput.classList.add('is-invalid');
                        userPhotoInput.nextElementSibling.textContent = 'Only JPG, JPEG, PNG, GIF files are allowed.';
                        isValid = false;
                    } else if (file.size > maxSize) {
                        userPhotoInput.classList.add('is-invalid');
                        userPhotoInput.nextElementSibling.textContent = 'File is too large (max 5MB).';
                        isValid = false;
                    }
                }

                return isValid;
            }

            document.getElementById('addUserForm').addEventListener('submit', function(e) {
                if (!validateForm('addUserForm')) {
                    e.preventDefault();
                }
            });

            document.getElementById('editUserForm').addEventListener('submit', function(e) {
                if (!validateForm('editUserForm')) {
                    e.preventDefault();
                }
            });

            // --- Barangay Dropdown Logic ---
            function setupBarangayDropdown(selectElementId, hiddenIdElementId) {
                const selectElement = document.getElementById(selectElementId);
                const hiddenIdElement = document.getElementById(hiddenIdElementId);

                if (!selectElement || !hiddenIdElement) return; // Exit if elements don't exist

                selectElement.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    if (selectedOption) {
                        hiddenIdElement.value = selectedOption.getAttribute('data-brgy-id');
                    } else {
                        hiddenIdElement.value = '';
                    }
                });

                // Initialize hidden ID on page load if an option is already selected (e.g., after form submission error)
                const initialSelectedOption = selectElement.options[selectElement.selectedIndex];
                if (initialSelectedOption) {
                    hiddenIdElement.value = initialSelectedOption.getAttribute('data-brgy-id');
                }
            }

            // Function to update barangay dropdowns in user add/edit modals
            function updateBarangayDropdowns() {
                const addBrgySelect = document.getElementById('brgy_name_select');
                const editBrgySelect = document.getElementById('edit_brgy_name_select');

                [addBrgySelect, editBrgySelect].forEach(selectElement => {
                    if (selectElement) {
                        const currentValue = selectElement.value; // Store current selected value
                        selectElement.innerHTML = '<option value="">Select Barangay</option>'; // Clear existing options
                        barangaysData.forEach(brgy => {
                            const option = document.createElement('option');
                            option.value = brgy.brgy_name;
                            option.textContent = brgy.brgy_name;
                            option.setAttribute('data-brgy-id', brgy.brgy_id);
                            selectElement.appendChild(option);
                        });
                        selectElement.value = currentValue; // Restore previous selection
                        // Manually trigger change to update hidden ID if needed
                        const event = new Event('change');
                        selectElement.dispatchEvent(event);
                    }
                });
            }

            // Setup for Add User Modal
            setupBarangayDropdown('brgy_name_select', 'brgy_id_hidden');

            // Setup for Edit User Modal
            setupBarangayDropdown('edit_brgy_name_select', 'edit_brgy_id_hidden');

            // No longer need to reload the page when manageBarangaysModal is hidden
            // manageBarangaysModalElement.addEventListener('hidden.bs.modal', function () {
            //     location.reload();
            // });
        });
    </script>
</body>
</html>
