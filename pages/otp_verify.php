<?php
require_once '../logic/login/otp_verify_logic.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BRSMS OTP Verification</title>
                <!-- Favicon (browser tab logo) -->
    <link rel="icon" type="image/png" href="../uploads/BRSMS.png">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/login.css">
</head>
<body>

<section class="h-100 gradient-form" style="background-color: #eee;">
  <div class="container py-5 h-100">
    <div class="row d-flex justify-content-center align-items-center h-100">
      <div class="col-xl-10">
        <div class="card rounded-3 text-black shadow">
          <div class="row g-0">
            <div class="col-lg-6">
              <div class="card-body p-md-5 mx-md-4">

                <div class="text-center mb-4">
                  <img src="../uploads/BRSMS.png" class="logo-img" alt="BRSMS Logo" id="spinLogo">
                  <h4 class="mt-3 mb-4 login-title">Barangay Resource Sharing Management System</h4>
                </div>

                <div id="otpFormSection">
                    <h2 class="title">Two-Factor Authentication</h2>
                    <p class="subtitle">Please enter the 6-digit code sent to your email address.</p>

                    <form method="POST" action="" id="otpVerificationForm">
                        <input type="hidden" name="otp_submitted" value="1">
                        <div class="otp-input-container">
                            <label for="otpInput" class="otp-label">One-Time Password</label>
                            <div class="otp-inputs">
                            <input type="text" class="otp-input" id="otp1" maxlength="1" pattern="[0-9]*" inputmode="numeric" autocomplete="one-time-code" required autofocus>
                            <input type="text" class="otp-input" id="otp2" maxlength="1" pattern="[0-9]*" inputmode="numeric" autocomplete="off" required>
                            <input type="text" class="otp-input" id="otp3" maxlength="1" pattern="[0-9]*" inputmode="numeric" autocomplete="off" required>
                            <input type="text" class="otp-input" id="otp4" maxlength="1" pattern="[0-9]*" inputmode="numeric" autocomplete="off" required>
                            <input type="text" class="otp-input" id="otp5" maxlength="1" pattern="[0-9]*" inputmode="numeric" autocomplete="off" required>
                            <input type="text" class="otp-input" id="otp6" maxlength="1" pattern="[0-9]*" inputmode="numeric" autocomplete="off" required>
                            </div>
                            <input type="hidden" name="otp" id="hiddenOtpInput">
                        </div>

                        <button type="submit" class="btn btn-primary btn-block fa-lg gradient-custom-2 mb-3 w-100">
                            <i class="fas fa-check-circle me-2"></i> Verify Code
                        </button>
                    </form>
                </div>

              </div>
            </div>

            <div class="col-lg-6 d-flex align-items-center gradient-custom-2 text-white">
              <div class="right-side-content">
                <div class="text-content">
                  <h4 class="mb-3 fw-bold">Empowering Barangay Communities</h4>
                  <p class="mb-4">The Barangay Resource Sharing Management System helps local governments:</p>
                  <ul class="feature-list">
                    <li>Track community resources in real-time</li>
                    <li>Manage inventory efficiently</li>
                    <li>Streamline resource allocation</li>
                    <li>Generate comprehensive reports</li>
                  </ul>
                </div>
                <img src="../uploads/nadya.png" class="model-image" alt="Community Illustration">
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<div class="toast-container">
    <div id="actionToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="3000">
        <div class="toast-body" id="toastMessage">
            </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/login.js"></script>

<script>
    <?php if (isset($_SESSION['toast_message'])): ?>
        showToast("<?= htmlspecialchars($_SESSION['toast_message']) ?>", "<?= htmlspecialchars($_SESSION['toast_type']) ?>");
        <?php
        unset($_SESSION['toast_message']);
        unset($_SESSION['toast_type']);
        ?>
    <?php endif; ?>
</script>

</body>
</html>
