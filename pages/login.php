<?php
require_once '../logic/login/login_logic.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BRSMS Login</title>
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

                <div id="loginFormSection">
                    <p class="welcome-text">Please login to your account</p>
                    <form method="POST" action="" id="loginForm" novalidate> <!-- Added novalidate to control validation via JS -->
                        <div class="form-outline mb-4">
                            <label class="form-label" for="usernameInput"><i class="fas fa-user me-2"></i> Email Username</label>
                            <input
                                type="text"
                                name="username"
                                id="usernameInput"
                                class="form-control"
                                placeholder="e.g., brsms@gmail.com"
                                required
                                value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                            />
                            <div id="usernameError" class="validation-error">
                                <i class="fas fa-exclamation-circle"></i> Please enter a valid email address (e.g., example@gmail.com).
                            </div>
                        </div>

                        <div class="form-outline mb-4">
                            <label class="form-label" for="passwordInput"><i class="fas fa-lock me-2"></i> Password</label>
                            <input type="password" name="password" id="passwordInput" class="form-control" placeholder="Enter your password" required />
                            <div id="passwordError" class="validation-error">
                                <i class="fas fa-exclamation-circle"></i> This field is required.
                            </div>
                        </div>

                        <div class="form-check d-flex align-items-center mb-4">
                            <input class="form-check-input me-2" type="checkbox" id="remember" name="remember" />
                            <label class="form-check-label" for="remember">Remember me</label>
                        </div>

                        <div class="text-center pt-1 mb-5 pb-1">
                            <button class="btn btn-primary btn-block fa-lg gradient-custom-2 mb-3 w-100" type="submit">
                                <i class="fas fa-sign-in-alt me-2"></i> Login
                            </button>
                        </div>
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
        // Toast Notification Logic (copied and adapted from issued_resources.php)
    const actionToast = new bootstrap.Toast(document.getElementById('actionToast'), {
        delay: 3000 // Set delay to 3000 milliseconds (3 seconds)
    });
    
        function showToast(message, type = 'success') {
        const toastBody = document.getElementById('toastMessage');
        let iconClass = '';
        let bgColorClass = '';

        // Determine icon and background color based on type
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
            iconClass = 'fas fa-info-circle'; // Default icon
            bgColorClass = 'text-bg-secondary'; // Default background
        }

        // Set the content of the toast body
        toastBody.innerHTML = `<i class="${iconClass}"></i> <span>${message}</span>`;

        const toastEl = document.getElementById('actionToast');
        // Remove all existing text-bg-* classes
        toastEl.classList.remove('text-bg-success', 'text-bg-danger', 'text-bg-warning', 'text-bg-info', 'text-bg-primary', 'text-bg-secondary');

        // Add the appropriate class based on type
        toastEl.classList.add(bgColorClass);

        actionToast.show();
    }

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
