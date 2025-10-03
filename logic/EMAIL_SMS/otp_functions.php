<?php
require __DIR__ . '/../../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;


function send_otp($user, $conn) {
    $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $otp_expires_at = date('Y-m-d H:i:s', strtotime('+5 minutes'));

    $update_stmt = $conn->prepare("UPDATE users SET otp = ?, otp_expires_at = ? WHERE user_id = ?");
    $update_stmt->bind_param("ssi", $otp, $otp_expires_at, $user['user_id']);
    $update_stmt->execute();

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'barangaysms@gmail.com';
        $mail->Password   = 'zwfhvcmbebampern';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->setFrom('no-reply@yourdomain.com', 'BRSMS 2FA');
        $mail->addAddress($user['username'], $user['user_full_name']);
        $mail->isHTML(true);
        $mail->Subject = 'Your BRSMS Two-Factor Authentication Code';
        $mail->Body    = 'Hello ' . htmlspecialchars($user['user_full_name']) . ',<br><br>'
                       . 'Your One-Time Password (OTP) for BRSMS login is: <strong>' . $otp . '</strong><br>'
                       . 'This code is valid for 5 minutes. Do not share this code with anyone.<br><br>'
                       . 'If you did not request this, please ignore this email.';
        $mail->AltBody = 'Your One-Time Password (OTP) for BRSMS login is: ' . $otp . '. This code is valid for 5 minutes. Do not share this code with anyone.';

        $mail->send();
        $_SESSION['2fa_user_id'] = $user['user_id'];
        $_SESSION['toast_message'] = "An OTP has been sent to your email address.";
        $_SESSION['toast_type'] = "success";
        return true;
    } catch (Exception $e) {
        $_SESSION['toast_message'] = "Failed to send OTP. Mailer Error: {$mail->ErrorInfo}";
        $_SESSION['toast_type'] = "error";
        return false;
    }
}
?>
