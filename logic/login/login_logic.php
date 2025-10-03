<?php
session_start();
require __DIR__ . '/../database/db.php';
require __DIR__ . '/../logging.php';

if (isset($_SESSION['user_id'])) {
    if (strtolower($_SESSION['role']) === 'superadmin') {
        header("Location: super_dashboard.php");
    } else {
        header("Location: dashboard.php");
    }
    exit();
}
require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../EMAIL_SMS/otp_functions.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (!preg_match("/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/", $username)) {
        $_SESSION['toast_message'] = "Invalid username format.";
        $_SESSION['toast_type'] = "error";
    } else {
        $stmt = $conn->prepare("SELECT *, two_factor_enabled FROM users WHERE LOWER(username) = LOWER(?) LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {

                if ($user['two_factor_enabled']) {
                    send_otp($user, $conn);
                    $_SESSION['2fa_user_id'] = $user['user_id'];
                    header("Location: ../pages/otp_verify.php");
                    exit();
                } else {
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['user_full_name'] = $user['user_full_name'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['brgy_name'] = $user['brgy_name'];
                    $_SESSION['brgy_id'] = $user['brgy_id'];

                    logLogin($user['user_id']);

                    $_SESSION['toast_message'] = "Login successful!";
                    $_SESSION['toast_type'] = "success";

                    if (strtolower($user['role']) === 'superadmin') {
                        header("Location: ../admin_folder/super_dashboard.php");
                    } else {
                        header("Location: ../pages/dashboard.php");
                    }
                    exit();
                }
            } else {
                $_SESSION['toast_message'] = "Invalid credentials.";
                $_SESSION['toast_type'] = "error";
            }
        } else {
            $_SESSION['toast_message'] = "Invalid credentials.";
            $_SESSION['toast_type'] = "error";
        }
    }
}
?>