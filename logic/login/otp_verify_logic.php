<?php
session_start();
require __DIR__ . '/../database/db.php';
require __DIR__ . '/../logging.php';

if (!isset($_SESSION['2fa_user_id'])) {
    header("Location: ../pages/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['otp_submitted'])) {
    $entered_otp = trim($_POST['otp']);
    $user_id = $_SESSION['2fa_user_id'];
    $stmt = $conn->prepare("SELECT otp, otp_expires_at, user_full_name, role, brgy_name, brgy_id FROM users WHERE user_id = ? LIMIT 1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        $stored_otp = $user['otp'];
        $otp_expires_at = $user['otp_expires_at'];

        if (strtotime($otp_expires_at) < time()) {
            $_SESSION['toast_message'] = "Your OTP has expired.";
            $_SESSION['toast_type'] = "error";

            unset($_SESSION['2fa_user_id']);
            $clear_otp_stmt = $conn->prepare("UPDATE users SET otp = NULL, otp_expires_at = NULL WHERE user_id = ?");
            $clear_otp_stmt->bind_param("i", $user_id);
            $clear_otp_stmt->execute();
            header("Location: ../pages/login.php");
            exit();
        } elseif ($entered_otp === $stored_otp) {
            $clear_otp_stmt = $conn->prepare("UPDATE users SET otp = NULL, otp_expires_at = NULL WHERE user_id = ?");
            $clear_otp_stmt->bind_param("i", $user_id);
            $clear_otp_stmt->execute();

            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_full_name'] = $user['user_full_name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['brgy_name'] = $user['brgy_name'];
            $_SESSION['brgy_id'] = $user['brgy_id'];

            unset($_SESSION['2fa_user_id']);

            logLogin($user_id);

            $_SESSION['toast_message'] = "Login successful!";
            $_SESSION['toast_type'] = "success";

            if (strtolower($user['role']) === 'superadmin') {
                header("Location: ../admin_folder/super_dashboard.php");
            } else {
                header("Location: ../pages/dashboard.php");
            }
            exit();
        } else {
            $_SESSION['toast_message'] = "Invalid OTP. Please try again.";
            $_SESSION['toast_type'] = "error";
        }
    } else {
        $_SESSION['toast_message'] = "User not found.";
        $_SESSION['toast_type'] = "error";
        unset($_SESSION['2fa_user_id']);
        header("Location: ../pages/login.php");
        exit();
    }
}
?>
