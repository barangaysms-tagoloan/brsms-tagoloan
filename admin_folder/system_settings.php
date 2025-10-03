<?php
session_start();
require '../logic/database/db.php'; 
require '../logic/logging.php'; 


if (!isset($_SESSION['role']) || (!in_array(strtolower($_SESSION['role']), ['superadmin', 'admin']))) {
    header("Location: login.php");
    exit();
}

$errors = [];
$success_message = '';
$show_password_modal = false;
$show_2fa_modal = false;
$show_system_maintenance_modal = false; 
$show_database_backup_modal = false; 

$user_id = $_SESSION['user_id'];
$user_role = strtolower($_SESSION['role']);
$user_data = [];


$stmt_fetch_user = $conn->prepare("
    SELECT user_id, user_full_name, username, role, two_factor_enabled
    FROM users
    WHERE user_id = ?
");
if ($stmt_fetch_user) {
    $stmt_fetch_user->bind_param("i", $user_id);
    $stmt_fetch_user->execute();
    $result_fetch_user = $stmt_fetch_user->get_result();
    $user_data = $result_fetch_user->fetch_assoc();
    $stmt_fetch_user->close();

    if (!$user_data) {
        $_SESSION['error'] = "User data could not be retrieved.";
    }
} else {
    $_SESSION['error'] = "Failed to prepare statement to fetch user data: " . $conn->error;
}


$settings = [
    'site_name' => '',
    'admin_email' => '',
    'maintenance_mode' => 0,
];

$sql = "SELECT setting_key, setting_value FROM system_settings";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
}




if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($current_password)) {
        $errors[] = "Current password is required.";
    }
    if (empty($new_password)) {
        $errors[] = "New password is required.";
    } elseif (strlen($new_password) < 6) {
        $errors[] = "New password must be at least 6 characters long.";
    }
    if ($new_password !== $confirm_password) {
        $errors[] = "New passwords do not match.";
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();

            if ($user && password_verify($current_password, $user['password'])) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                if ($update_stmt) {
                    $update_stmt->bind_param("si", $hashed_password, $user_id);
                    if ($update_stmt->execute()) {
                        $_SESSION['success'] = "Password changed successfully!";
                        logActivity($user_id, ucfirst($user_role) . " Password Changed", ucfirst($user_role) . " changed their password.");
                    } else {
                        $errors[] = "Failed to update password. Please try again. Error: " . $update_stmt->error;
                        $show_password_modal = true;
                    }
                    $update_stmt->close();
                } else {
                    $errors[] = "Failed to prepare update statement. Error: " . $conn->error;
                    $show_password_modal = true;
                }
            } else {
                $errors[] = "Current password is incorrect.";
                $show_password_modal = true;
            }
        } else {
            $errors[] = "Failed to prepare select statement. Error: " . $conn->error;
            $show_password_modal = true;
        }
    } else {
        $show_password_modal = true;
    }
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
    }
    header("Location: system_settings.php");
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_2fa'])) {
    $new_2fa_status = isset($_POST['two_factor_enabled']) ? 1 : 0;
    $old_2fa_status = $user_data['two_factor_enabled'] ?? 0;

    $update_2fa_stmt = $conn->prepare("UPDATE users SET two_factor_enabled = ? WHERE user_id = ?");
    if ($update_2fa_stmt) {
        $update_2fa_stmt->bind_param("ii", $new_2fa_status, $user_id);
        if ($update_2fa_stmt->execute()) {
            $_SESSION['success'] = "Two-Factor Authentication status updated successfully!";
            if ($new_2fa_status == 1 && $old_2fa_status == 0) {
                logActivity($user_id, ucfirst($user_role) . " 2FA Enabled", ucfirst($user_role) . " enabled Two-Factor Authentication.");
            } elseif ($new_2fa_status == 0 && $old_2fa_status == 1) {
                logActivity($user_id, ucfirst($user_role) . " 2FA Disabled", ucfirst($user_role) . " disabled Two-Factor Authentication.");
            }
            $user_data['two_factor_enabled'] = $new_2fa_status; 
        } else {
            $errors[] = "Failed to update 2FA status. Error: " . $update_2fa_stmt->error;
            $show_2fa_modal = true;
        }
        $update_2fa_stmt->close();
    } else {
        $errors[] = "Failed to prepare 2FA update statement: " . $conn->error;
        $show_2fa_modal = true;
    }
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
    }
    header("Location: system_settings.php");
    exit();
}


if ($user_role === 'superadmin' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_system_settings'])) {
    $site_name = trim($_POST['site_name'] ?? '');
    $admin_email = trim($_POST['admin_email'] ?? '');
    $maintenance_mode = isset($_POST['maintenance_mode']) ? 1 : 0;

    if (empty($site_name)) {
        $errors[] = "Site Name is required.";
    }
    if (empty($admin_email)) {
        $errors[] = "Admin Email is required.";
    } elseif (!filter_var($admin_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid Admin Email format.";
    }

    if (empty($errors)) {
        $conn->begin_transaction(); 

        try {
            $stmt1 = $conn->prepare("UPDATE system_settings SET setting_value = ? WHERE setting_key = 'site_name'");
            $stmt1->bind_param("s", $site_name);
            $stmt1->execute();
            $stmt1->close();

            $stmt2 = $conn->prepare("UPDATE system_settings SET setting_value = ? WHERE setting_key = 'admin_email'");
            $stmt2->bind_param("s", $admin_email);
            $stmt2->execute();
            $stmt2->close();

            $stmt3 = $conn->prepare("UPDATE system_settings SET setting_value = ? WHERE setting_key = 'maintenance_mode'");
            $stmt3->bind_param("i", $maintenance_mode);
            $stmt3->execute();
            $stmt3->close();

            $conn->commit(); 
            $_SESSION['success'] = "System settings updated successfully.";
            logActivity($user_id, "System Settings Updated", "Super Admin updated system settings (Site Name: '{$site_name}', Admin Email: '{$admin_email}', Maintenance Mode: '{$maintenance_mode}').");

            
            $settings['site_name'] = $site_name;
            $settings['admin_email'] = $admin_email;
            $settings['maintenance_mode'] = $maintenance_mode;

        } catch (mysqli_sql_exception $e) {
            $conn->rollback(); 
            $errors[] = "Failed to update system settings: " . $e->getMessage();
            $show_system_maintenance_modal = true; 
        }
    } else {
        $show_system_maintenance_modal = true;
    }
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
    }
    header("Location: system_settings.php");
    exit();
}


if ($user_role === 'superadmin' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['backup_database'])) {
    $backup_dir = 'backups/';
    if (!is_dir($backup_dir)) {
        mkdir($backup_dir, 0777, true);
    }

    $db_host = DB_SERVER; 
    $db_user = DB_USERNAME; 
    $db_pass = DB_PASSWORD; 
    $db_name = DB_NAME;     

    $backup_file = $backup_dir . $db_name . '_' . date('Y-m-d_H-i-s') . '.sql';

    
    
    $command = "mysqldump --opt -h{$db_host} -u{$db_user} -p{$db_pass} {$db_name} > {$backup_file}";

    
    exec($command, $output, $return_var);

    if ($return_var === 0) {
        $_SESSION['success'] = "Database backup created successfully: " . basename($backup_file);
        logActivity($user_id, "Database Backup", "Super Admin created a database backup: " . basename($backup_file));
    } else {
        $errors[] = "Failed to create database backup. Error code: {$return_var}. Output: " . implode("\n", $output);
        $show_database_backup_modal = true;
    }

    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
    }
    header("Location: system_settings.php");
    exit();
}



























$success_message = isset($_SESSION['success']) ? $_SESSION['success'] : '';
$errors = isset($_SESSION['errors']) ? $_SESSION['errors'] : [];
unset($_SESSION['success']);
unset($_SESSION['errors']);


$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>System Settings - BRSMS</title>
    <link rel="icon" type="image/png" href="uploads/BRSMS.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <style>
        :root {
            --primary-color: #5f2c82;
            --secondary-color: #6f2dbd;
            --text-dark: #333;
            --text-medium: #666;
            --border-light: #e0e0e0;
            --light-violet: rgba(138, 43, 226, 0.1);
            --violet: #5f2c82;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --info-color: #0dcaf0;
            --table-border-color: #e0e0e0;
            --table-header-bg: #f8f8f8;
            --table-header-text: #333;
            --table-row-hover-bg: #f5f5f5;
            --footer-text-color: #6c757d;
            --footer-bg: #f8f9fa;
            --footer-border-color: #e9ecef;
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
            width: 100%;
            flex-grow: 1;
        }
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
            background-color: #f8f9fa;
        }
        .page-header {
            font-size: 2rem;
            font-weight: normal;
            color: var(--text-dark);
            margin-bottom: 20px;
            display: block;
            text-align: left;
            padding-left: 15px;
        }
        .page-header i {
            margin-right: 10px;
            font-size: 1.5rem;
        }
        /* Settings Card Styles */
        .settings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); /* Adjusted minmax for better spacing */
            gap: 25px; /* Increased gap for more professional spacing */
            margin-bottom: 30px;
        }
        .settings-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.07);
            transition: all 0.3s ease;
            cursor: pointer;
            height: 100%;
            background: white;
            overflow: hidden;
            position: relative;
            padding: 25px; /* Adjusted padding */
            display: flex; /* Use flexbox for card content */
            flex-direction: column; /* Stack items vertically */
        }
        .settings-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.12);
        }
        .settings-card .card-body {
            padding: 0;
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            flex-grow: 1; /* Allow card body to grow */
        }
        .settings-icon-container {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            box-shadow: 0 4px 10px rgba(95, 44, 130, 0.2);
        }
        .settings-icon {
            font-size: 24px;
            color: white;
        }
        .settings-card .card-title {
            font-size: 1.2rem; /* Slightly larger title */
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        .settings-card .card-text {
            color: #7f8c8d;
            font-size: 0.95rem; /* Slightly larger text */
            line-height: 1.5;
            margin-bottom: auto; /* Pushes the "Update now" link to the bottom */
        }
        .card-hover-indicator {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }
        .settings-card:hover .card-hover-indicator {
            transform: scaleX(1);
        }
        /* User Info Panel */
        .user-info-panel {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 16px;
            padding: 30px;
            color: white;
            margin-bottom: 30px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
        }
        .user-avatar {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
            font-size: 30px;
        }
        .user-name {
            font-size: 1.4rem;
            font-weight: 600;
            margin-bottom: 5px;
        }
        .role-badge {
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            margin-top: 5px;
            display: inline-block;
        }
        .user-details {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }
        .user-detail-item {
            background-color: rgba(255, 255, 255, 0.1);
            padding: 6px 12px;
            border-radius: 10px;
            font-size: 0.85rem;
        }
        /* Modal Styles */
        .modal-content {
            border-radius: 16px;
            border: none;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
            background-color: #fff;
            color: #2c3e50;
        }
        .modal-header {
            background-color: #f8f9fa;
            color: #2c3e50;
            border-bottom: 1px solid #e9ecef;
            border-top-left-radius: 16px;
            border-top-right-radius: 16px;
            padding: 20px 25px;
        }
        .modal-title {
            font-weight: 600;
        }
        .modal-body {
            padding: 25px;
        }
        .modal-footer {
            border-top: 1px solid #e9ecef;
            background-color: #f8f9fa;
            border-bottom-left-radius: 16px;
            border-bottom-right-radius: 16px;
            padding: 15px 25px;
        }
        /* Form Styles */
        .form-label {
            font-weight: 500;
            color: #2c3e50;
            margin-bottom: 8px;
        }
        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            border: 1px solid #ddd;
            transition: all 0.3s;
        }
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(95, 44, 130, 0.15);
        }
        /* Button Styles */
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            border-radius: 10px;
            padding: 10px 20px;
            font-weight: 500;
            transition: all 0.3s;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(95, 44, 130, 0.3);
        }
        .btn-secondary {
            background-color: #95a5a6;
            border: none;
            border-radius: 10px;
            padding: 10px 20px;
            font-weight: 500;
        }
        .btn-secondary:hover {
            background-color: #7f8c8d;
        }
        /* Footer styling */
        .footer {
            padding: 15px;
            text-align: center;
            color: #6c757d;
            font-size: 0.85rem;
            margin-top: auto;
            background-color: #f8f9fa;
            border-top: 1px solid #e9ecef;
            position: sticky; /* Make footer sticky */
            bottom: 0; /* Stick to the bottom */
            width: 100%; /* Ensure it spans full width */
            z-index: 1000; /* Ensure it's above other content if needed */
        }
        /* Toast Notification Styles */
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
        /* Password Toggle Icon */
        .password-input-group {
            position: relative;
        }
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 42px;
            cursor: pointer;
            color: #95a5a6;
            transition: color 0.3s;
        }
        .password-toggle:hover {
            color: var(--primary-color);
        }
        /* Custom switch styling for 2FA toggle */
        .form-check-input.form-switch {
            width: 3.5em;
            height: 2em;
            cursor: pointer;
        }
        .form-check-input.form-switch:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        .form-check-input.form-switch:focus {
            box-shadow: 0 0 0 0.25rem rgba(95, 44, 130, 0.25);
        }
        /* 2FA Toggle Styles */
        .two-factor-toggle {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
        }
        .two-factor-info h5 {
            margin-bottom: 5px;
            color: #2c3e50;
            font-weight: 600;
        }
        .two-factor-info p {
            color: #7f8c8d;
            margin-bottom: 0;
            font-size: 0.95rem;
        }
        .two-factor-status {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .status-indicator {
            font-weight: 600;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.9em;
        }
        .status-on {
            background-color: var(--success-color);
            color: white;
        }
        .status-off {
            background-color: #95a5a6;
            color: white;
        }
        /* Removed Recent Activity Table Styles */
        /* .activity-table-container {
            background-color: white;
            border-radius: 16px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.07);
            padding: 20px;
            margin-top: 30px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        .activity-table-container h5 {
            font-size: 1.4rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 20px;
        }
        .activity-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 8px;
        }
        .activity-table th,
        .activity-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        .activity-table th {
            background-color: var(--table-header-bg);
            color: var(--table-header-text);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            border-top: 1px solid #e0e0e0;
        }
        .activity-table tbody tr:last-child td {
            border-bottom: none;
        }
        .activity-table tbody tr:hover {
            background-color: var(--table-row-hover-bg);
        }
        .activity-table .activity-type {
            font-weight: 500;
            color: var(--primary-color);
        }
        .activity-table .activity-timestamp {
            font-size: 0.85rem;
            color: #7f8c8d;
        } */
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
            .settings-grid {
                grid-template-columns: 1fr; /* Stack cards on small screens */
            }
        }
    </style>
</head>
<body>
<div class="wrapper">
    <!-- Sidebar - Mirrored from super_dashboard.php -->
    <div class="col-md-3 col-lg-2 sidebar d-flex flex-column">
        <div class="logo-container">
            <div class="logo-circle">
                <img src="uploads/BRSMS.jpg" alt="BRSMS Logo">
            </div>
            <div class="logo-text">BRSMS</div>
        </div>
        <?php if ($user_role === 'superadmin'): ?>
            <a href="super_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="user_management.php"><i class="fas fa-users"></i> User Management</a>
            <a href="activity_logs.php"><i class="fas fa-list-alt"></i> Activity Logs</a>
        <?php elseif ($user_role === 'admin'): ?>
            <a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <!-- Add other admin specific links here if any -->
        <?php endif; ?>
        <a href="system_settings.php" class="active"><i class="fas fa-cog"></i> System Settings</a>
        <div class="mt-auto">
            <a href="logout.php" class="logout-btn mt-4"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="col-md-9 col-lg-10 main-content">
        <div class="page-header">
            <!-- This header can be customized for System Settings if needed, or removed if the user info panel is sufficient -->
        </div>

        <!-- User Info Panel -->
        <div class="user-info-panel">
            <div class="d-flex align-items-center">
                <div class="user-avatar">
                    <?php if ($user_role === 'superadmin'): ?>
                        <i class="fas fa-user-shield"></i>
                    <?php else: ?>
                        <i class="fas fa-user-cog"></i>
                    <?php endif; ?>
                </div>
                <div class="ms-3">
                    <div class="user-name"><?php echo htmlspecialchars($user_data['user_full_name'] ?? 'User'); ?></div>
                    <div class="role-badge"><?php echo ucfirst($user_role); ?></div>
                </div>
            </div>

            <!-- Additional User details -->
            <div class="user-details mt-3">
                <div class="user-detail-item">
                    <i class="fas fa-user me-2"></i>
                    Username: <?php echo htmlspecialchars($user_data['username'] ?? 'N/A'); ?>
                </div>
                <div class="user-detail-item">
                    <i class="fas fa-shield-alt me-2"></i>
                    2FA Status: <?php echo ($user_data['two_factor_enabled'] ?? 0) ? 'Enabled' : 'Disabled'; ?>
                </div>
            </div>
        </div>

        <!-- Content for Settings page -->
        <div class="container-fluid p-0">
            <div class="settings-grid">
                <!-- Password Card -->
                <div class="card settings-card" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                    <div class="card-body">
                        <div class="settings-icon-container">
                            <i class="fas fa-key settings-icon"></i>
                        </div>
                        <h5 class="card-title">Change My Password</h5>
                        <p class="card-text">Update your account password to keep it secure.</p>
                        <div class="text-primary mt-2">
                            <small><i class="fas fa-arrow-right me-1"></i> Update now</small>
                        </div>
                    </div>
                    <div class="card-hover-indicator"></div>
                </div>

                <!-- 2FA Card -->
                <div class="card settings-card" data-bs-toggle="modal" data-bs-target="#twoFactorAuthModal">
                    <div class="card-body">
                        <div class="settings-icon-container">
                            <i class="fas fa-shield-alt settings-icon"></i>
                        </div>
                        <h5 class="card-title">Two-Factor Authentication</h5>
                        <p class="card-text">Add an extra layer of security to your account by enabling 2FA.</p>
                        <div class="text-primary mt-2">
                            <small><i class="fas fa-arrow-right me-1"></i> Configure</small>
                        </div>
                    </div>
                    <div class="card-hover-indicator"></div>
                </div>

                <?php if ($user_role === 'superadmin'): ?>
                    <!-- System Configuration Card (Only for Super Admin) -->
                    <div class="card settings-card" data-bs-toggle="modal" data-bs-target="#systemMaintenanceModal">
                        <div class="card-body">
                            <div class="settings-icon-container">
                                <i class="fas fa-cogs settings-icon"></i>
                            </div>
                            <h5 class="card-title">System Configuration</h5>
                            <p class="card-text">Manage site name, admin email, and maintenance mode settings.</p>
                            <div class="text-primary mt-2">
                                <small><i class="fas fa-arrow-right me-1"></i> Configure</small>
                            </div>
                        </div>
                        <div class="card-hover-indicator"></div>
                    </div>

                    <!-- Database Backup Card (Only for Super Admin) -->
                    <div class="card settings-card" data-bs-toggle="modal" data-bs-target="#databaseBackupModal">
                        <div class="card-body">
                            <div class="settings-icon-container">
                                <i class="fas fa-database settings-icon"></i>
                            </div>
                            <h5 class="card-title">Database Backup</h5>
                            <p class="card-text">Create a backup of the entire system database for recovery.</p>
                            <div class="text-primary mt-2">
                                <small><i class="fas fa-arrow-right me-1"></i> Backup now</small>
                            </div>
                        </div>
                        <div class="card-hover-indicator"></div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Removed Recent Activity Table -->
            <?php
            
            ?>
        </div>

        <!-- Change Password Modal -->
        <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="changePasswordModalLabel">
                            <i class="fas fa-key me-2"></i>Change My Password
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" action="">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Current Password</label>
                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                            </div>
                            <div class="mb-3 password-input-group">
                                <label for="new_password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" required>
                                <span class="password-toggle" onclick="togglePasswordVisibility('new_password')">
                                    <i class="fas fa-eye"></i>
                                </span>
                                <div class="form-text">Password must be at least 6 characters long.</div>
                            </div>
                            <div class="mb-3 password-input-group">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                <span class="password-toggle" onclick="togglePasswordVisibility('confirm_password')">
                                    <i class="fas fa-eye"></i>
                                </span>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" name="change_password" class="btn btn-primary">Update Password</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Two-Factor Authentication Modal -->
        <div class="modal fade" id="twoFactorAuthModal" tabindex="-1" aria-labelledby="twoFactorAuthModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="twoFactorAuthModalLabel">
                            <i class="fas fa-shield-alt me-2"></i>Two-Factor Authentication
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" action="">
                        <div class="modal-body">
                            <div class="mb-4">
                                <p>Two-factor authentication (2FA) adds an extra layer of security to your account by requiring more than just a password to sign in.</p>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6>Enable Two-Factor Authentication</h6>
                                    <p class="text-muted mb-0">Current status:
                                        <span class="status-indicator <?php echo ($user_data['two_factor_enabled'] ?? 0) ? 'status-on' : 'status-off'; ?>">
                                            <?php echo ($user_data['two_factor_enabled'] ?? 0) ? 'ON' : 'OFF'; ?>
                                        </span>
                                    </p>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="twoFactorToggle" name="two_factor_enabled" <?php echo ($user_data['two_factor_enabled'] ?? 0) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="twoFactorToggle"></label>
                                </div>
                            </div>
                            <?php if ($user_data['two_factor_enabled'] ?? 0): ?>
                                <div class="alert alert-info mt-3">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Two-factor authentication is currently enabled for your account.
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning mt-3">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    Two-factor authentication is currently disabled. We recommend enabling it for better security.
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" name="toggle_2fa" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <?php if ($user_role === 'superadmin'): ?>
            <!-- System Configuration Modal (Only for Super Admin) -->
            <div class="modal fade" id="systemMaintenanceModal" tabindex="-1" aria-labelledby="systemMaintenanceModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="systemMaintenanceModalLabel">
                                <i class="fas fa-cogs me-2"></i>System Configuration
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form method="POST" action="">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="site_name" class="form-label">Site Name</label>
                                    <input type="text" class="form-control" id="site_name" name="site_name" value="<?= htmlspecialchars($settings['site_name']) ?>" required />
                                </div>
                                <div class="mb-3">
                                    <label for="admin_email" class="form-label">Admin Email</label>
                                    <input type="email" class="form-control" id="admin_email" name="admin_email" value="<?= htmlspecialchars($settings['admin_email']) ?>" required />
                                </div>
                                <div class="form-check mb-4">
                                    <input class="form-check-input" type="checkbox" id="maintenance_mode" name="maintenance_mode" <?= $settings['maintenance_mode'] ? 'checked' : '' ?> />
                                    <label class="form-check-label" for="maintenance_mode">
                                        Enable Maintenance Mode
                                    </label>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" name="update_system_settings" class="btn btn-primary">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Database Backup Modal (Only for Super Admin) -->
            <div class="modal fade" id="databaseBackupModal" tabindex="-1" aria-labelledby="databaseBackupModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="databaseBackupModalLabel">
                                <i class="fas fa-database me-2"></i>Database Backup
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form method="POST" action="">
                            <div class="modal-body">
                                <p>Click the button below to create a full backup of your database. This will download a `.sql` file containing all your data.</p>
                                <div class="alert alert-warning" role="alert">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Important:</strong> Ensure you have `mysqldump` installed and configured on your server for this feature to work.
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" name="backup_database" class="btn btn-primary"><i class="fas fa-download me-2"></i>Create Backup</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Footer - Mirrored from super_dashboard.php -->
        <footer class="footer mt-auto py-1">
            <div class="container-fluid">
                <span>&copy; <?= date('Y') ?> BRSMS. All rights reserved.</span>
            </div>
        </footer>
    </div>
</div>

<!-- Bootstrap JS and dependencies -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>

<script>
    // Function to toggle password visibility
    function togglePasswordVisibility(inputId) {
        const passwordInput = document.getElementById(inputId);
        const passwordToggle = passwordInput.nextElementSibling;
        const eyeIcon = passwordToggle.querySelector('i');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeIcon.classList.remove('fa-eye');
            eyeIcon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            eyeIcon.classList.remove('fa-eye-slash');
            eyeIcon.classList.add('fa-eye');
        }
    }

    // Toast Notification Logic
    function showToast(message, type = 'success') {
        let toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container';
            document.body.appendChild(toastContainer);
        }

        const toastEl = document.createElement('div');
        toastEl.className = 'toast';
        toastEl.setAttribute('role', 'alert');
        toastEl.setAttribute('aria-live', 'assertive');
        toastEl.setAttribute('aria-atomic', 'true');
        toastEl.setAttribute('data-bs-delay', '3000');

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

        toastEl.classList.add(bgColorClass);

        toastEl.innerHTML = `
            <div class="toast-body">
                <i class="${iconClass}"></i> <span>${message}</span>
            </div>
        `;

        toastContainer.appendChild(toastEl);
        const newToast = new bootstrap.Toast(toastEl);
        newToast.show();

        toastEl.addEventListener('hidden.bs.toast', function () {
            toastEl.remove();
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const successMessage = "<?php echo $success_message; ?>";
        const errorMessages = <?php echo json_encode($errors); ?>;

        if (successMessage) {
            showToast(successMessage, 'success');
        }

        if (errorMessages.length > 0) {
            errorMessages.forEach(msg => {
                showToast(msg, 'error');
            });
        }

        <?php if ($show_password_modal): ?>
            var passwordModal = new bootstrap.Modal(document.getElementById('changePasswordModal'));
            passwordModal.show();
        <?php endif; ?>

        <?php if ($show_2fa_modal): ?>
            var twoFactorModal = new bootstrap.Modal(document.getElementById('twoFactorAuthModal'));
            twoFactorModal.show();
        <?php endif; ?>

        <?php if ($show_system_maintenance_modal): ?>
            var systemMaintenanceModal = new bootstrap.Modal(document.getElementById('systemMaintenanceModal'));
            systemMaintenanceModal.show();
        <?php endif; ?>

        <?php if ($show_database_backup_modal): ?>
            var databaseBackupModal = new bootstrap.Modal(document.getElementById('databaseBackupModal'));
            databaseBackupModal.show();
        <?php endif; ?>
    });
</script>
</body>
</html>
