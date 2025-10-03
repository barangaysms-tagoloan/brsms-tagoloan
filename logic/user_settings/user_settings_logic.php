<?php
session_start();
require __DIR__ . '/../database/db.php';
require __DIR__ . '/../logging.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$errors = [];
$success_message = '';
// Flags to control which section is visible
$show_password_section = false;
$show_profile_section = false;
$show_2fa_section = false;
$show_photo_modal = false; // Profile photo will still use a modal for simplicity of upload

$user_id = $_SESSION['user_id'];
$user_data = [];

// --- Fetch User Data for Profile Settings, 2FA Status, and Photo ---
// Fetch all user data including full name, role, barangay, and user_photo
$stmt_fetch_user = $conn->prepare("
    SELECT u.user_id, u.user_full_name, u.username, u.role, u.brgy_id, 
           u.two_factor_enabled, u.user_photo, b.brgy_name 
    FROM users u 
    LEFT JOIN barangays b ON u.brgy_id = b.brgy_id 
    WHERE u.user_id = ?
");
if ($stmt_fetch_user) {
    $stmt_fetch_user->bind_param("i", $user_id);
    $stmt_fetch_user->execute();
    $result_fetch_user = $stmt_fetch_user->get_result();
    $user_data = $result_fetch_user->fetch_assoc();
    $stmt_fetch_user->close();

    if (!$user_data) {
        $_SESSION['error'] = "User data could not be retrieved."; // Use session for errors
    }
} else {
    $_SESSION['error'] = "Failed to prepare statement to fetch user data: " . $conn->error; // Use session for errors
}

// --- Handle Password Change if form is submitted ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate inputs
    if (empty($current_password)) {
        $errors['current_password'] = "Current password is required.";
    }
    
    if (empty($new_password)) {
        $errors['new_password'] = "New password is required.";
    } elseif (strlen($new_password) < 6) {
        $errors['new_password'] = "New password must be at least 6 characters long.";
    }
    
    if ($new_password !== $confirm_password) {
        $errors['confirm_password'] = "New passwords do not match.";
    }
    
    // If no errors, verify current password and update
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
                        // Log the password change
                        logActivity($user_id, "Password Changed", "User changed their password.");
                    } else {
                        $errors['db_error'] = "Failed to update password. Please try again. Error: " . $update_stmt->error;
                        $show_password_section = true; // Keep section open on error
                    }
                    $update_stmt->close();
                } else {
                    $errors['db_error'] = "Failed to prepare update statement. Error: " . $conn->error;
                    $show_password_section = true; // Keep section open on error
                }
            } else {
                $errors['current_password'] = "Current password is incorrect.";
                $show_password_section = true; // Keep section open on error
            }
        } else {
            $errors['db_error'] = "Failed to prepare select statement. Error: " . $conn->error;
            $show_password_section = true; // Keep section open on error
        }
    } else {
        $show_password_section = true; // Keep section open on error
    }
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors; // Store all errors in session
    }
    header("Location: user_settings.php?section=password"); // Redirect to clear POST data and show section
    exit();
}

// --- Handle Profile Update if form is submitted ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $new_full_name = trim($_POST['full_name']);
    $new_username = trim($_POST['username']); 

    // Basic validation
    if (empty($new_full_name)) {
        $errors['full_name'] = "Full name is required.";
    }
    if (empty($new_username)) {
        $errors['username'] = "Username is required.";
    } elseif (!str_ends_with($new_username, '@gmail.com')) {
        $errors['username'] = "Username must end with '@gmail.com'.";
    }

    // Check if username already exists for another user
    if (empty($errors)) {
        $check_stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ? AND user_id != ?");
        if ($check_stmt) {
            $check_stmt->bind_param("si", $new_username, $user_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            if ($check_result->num_rows > 0) {
                $errors['username'] = "Username already taken.";
            }
            $check_stmt->close();
        } else {
            $errors['db_error'] = "Failed to prepare check statement: " . $conn->error;
        }
    }

    // If no errors, update profile
    if (empty($errors)) {
        $update_stmt = $conn->prepare("UPDATE users SET user_full_name = ?, username = ? WHERE user_id = ?");
        if ($update_stmt) {
            $update_stmt->bind_param("ssi", $new_full_name, $new_username, $user_id);
            if ($update_stmt->execute()) {
                $_SESSION['success'] = "Profile updated successfully!";
                // Log the profile update
                logActivity($user_id, "Profile Updated", "User updated their profile information (Full Name: '{$new_full_name}', Username: '{$new_username}').");
                // Update $user_data to reflect the new values immediately
                $user_data['user_full_name'] = $new_full_name;
                $user_data['username'] = $new_username;
            } else {
                $errors['db_error'] = "Failed to update profile: " . $update_stmt->error;
                $show_profile_section = true; // Keep section open on error
            }
            $update_stmt->close();
        } else {
            $errors['db_error'] = "Failed to prepare update statement: " . $conn->error;
            $show_profile_section = true; // Keep section open on error
        }
    } else {
        $show_profile_section = true; // Keep section open on error
    }
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors; // Store all errors in session
    }
    header("Location: user_settings.php?section=profile"); // Redirect to clear POST data and show section
    exit();
}

// --- Handle 2FA Toggle if form is submitted ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_2fa'])) {
    $new_2fa_status = isset($_POST['two_factor_enabled']) ? 1 : 0;
    $old_2fa_status = $user_data['two_factor_enabled'] ?? 0; // Get current status before update

    // Update the two_factor_enabled status in the database
    $update_2fa_stmt = $conn->prepare("UPDATE users SET two_factor_enabled = ? WHERE user_id = ?");
    if ($update_2fa_stmt) {
        $update_2fa_stmt->bind_param("ii", $new_2fa_status, $user_id);
        if ($update_2fa_stmt->execute()) {
            $_SESSION['success'] = "Two-Factor Authentication status updated successfully!";
            // Log the 2FA toggle
            if ($new_2fa_status == 1 && $old_2fa_status == 0) {
                logActivity($user_id, "2FA Enabled", "User enabled Two-Factor Authentication.");
            } elseif ($new_2fa_status == 0 && $old_2fa_status == 1) {
                logActivity($user_id, "2FA Disabled", "User disabled Two-Factor Authentication.");
            }
            // Update $user_data to reflect the new value
            $user_data['two_factor_enabled'] = $new_2fa_status;
        } else {
            $errors['db_error'] = "Failed to update 2FA status. Error: " . $update_2fa_stmt->error;
            $show_2fa_section = true; // Keep section open on error
        }
        $update_2fa_stmt->close();
    } else {
        $errors['db_error'] = "Failed to prepare 2FA update statement: " . $conn->error;
        $show_2fa_section = true; // Keep section open on error
    }
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors; // Store all errors in session
    }
    header("Location: user_settings.php?section=2fa"); // Redirect to clear POST data and show section
    exit();
}

// --- Handle Profile Photo Upload ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_photo'])) {
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $file_name = $_FILES['profile_picture']['name'];
        $file_tmp_name = $_FILES['profile_picture']['tmp_name'];
        $file_size = $_FILES['profile_picture']['size'];
        $file_type = $_FILES['profile_picture']['type'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $max_file_size = 5 * 1024 * 1024; // 5MB

        if (!in_array($file_ext, $allowed_extensions)) {
            $errors['profile_picture'] = "Invalid file type. Only JPG, JPEG and PNG are allowed.";
        }
        if ($file_size > $max_file_size) {
            $errors['profile_picture'] = "File size exceeds 5MB limit.";
        }

        if (empty($errors)) {
            $upload_dir = 'uploads/profile_pictures/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true); // Create directory if it doesn't exist
            }
            
            $new_file_name = uniqid('profile_') . '.' . $file_ext;
            $destination = $upload_dir . $new_file_name;

            if (move_uploaded_file($file_tmp_name, $destination)) {
                // Delete old profile picture if it exists and is not the default
                if (!empty($user_data['user_photo']) && file_exists($user_data['user_photo']) && basename($user_data['user_photo']) !== 'default_profile.png') {
                    unlink($user_data['user_photo']);
                }

                $update_photo_stmt = $conn->prepare("UPDATE users SET user_photo = ? WHERE user_id = ?");
                if ($update_photo_stmt) {
                    $update_photo_stmt->bind_param("si", $destination, $user_id);
                    if ($update_photo_stmt->execute()) {
                        $_SESSION['success'] = "Profile picture updated successfully!";
                        logActivity($user_id, "Profile Picture Updated", "User uploaded a new profile picture.");
                        $user_data['user_photo'] = $destination; // Update immediately
                    } else {
                        $errors['db_error'] = "Failed to update profile picture in database: " . $update_photo_stmt->error;
                        $show_photo_modal = true;
                    }
                    $update_photo_stmt->close();
                } else {
                    $errors['db_error'] = "Failed to prepare photo update statement. Error: " . $conn->error;
                    $show_photo_modal = true;
                }
            } else {
                $errors['upload_error'] = "Failed to upload file. Check directory permissions.";
                $show_photo_modal = true;
            }
        } else {
            $show_photo_modal = true;
        }
    } else {
        $errors['profile_picture'] = "No file uploaded or an upload error occurred.";
        $show_photo_modal = true;
    }

    if (!empty($errors)) {
        $_SESSION['errors'] = $errors; // Store all errors in session
    }
    header("Location: user_settings.php");
    exit();
}


// --- Fetch Recent Activity Data ---
$recent_activities = [];
// This section is commented out as the 'user_activities' table might not exist yet.
// If you have this table, uncomment and ensure it's correctly populated.
/*
$stmt_fetch_activity = $conn->prepare("
    SELECT activity_type, activity_description, timestamp 
    FROM user_activities 
    WHERE user_id = ? 
    ORDER BY timestamp DESC 
    LIMIT 5
");
if ($stmt_fetch_activity) {
    $stmt_fetch_activity->bind_param("i", $user_id);
    $stmt_fetch_activity->execute();
    $result_fetch_activity = $stmt_fetch_activity->get_result();
    while ($row = $result_fetch_activity->fetch_assoc()) {
        $recent_activities[] = $row;
    }
    $stmt_fetch_activity->close();
} else {
    // Handle error if statement preparation fails
    // $_SESSION['error'] = "Failed to prepare statement to fetch recent activities: " . $conn->error;
}
*/

$success_message = isset($_SESSION['success']) ? $_SESSION['success'] : '';
$errors_from_session = isset($_SESSION['errors']) ? $_SESSION['errors'] : [];
unset($_SESSION['success']);
unset($_SESSION['errors']);

if (isset($_GET['section'])) {
    if ($_GET['section'] === 'password') {
        $show_password_section = true;
    } elseif ($_GET['section'] === 'profile') {
        $show_profile_section = true;
    } elseif ($_GET['section'] === '2fa') {
        $show_2fa_section = true;
    }
}

$conn->close();
?>