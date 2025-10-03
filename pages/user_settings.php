<?php
require '../logic/user_settings/user_settings_logic.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Settings - BRSMS</title>
    <link rel="icon" type="image/png" href="../uploads/BRSMS.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="../assets/css/user_settings.css">
</head>
<body>
<div class="wrapper">
    <!-- Sidebar -->
    <div class="col-md-3 col-lg-2 sidebar d-flex flex-column">
        <div class="logo-container">
            <div class="logo-circle">
                <img src="../uploads/BRSMS.png" alt="BRSMS Logo">
            </div>
            <div class="logo-text">BRSMS</div>
        </div>
        <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="inventory.php"><i class="fas fa-boxes"></i> Inventory</a>
        <a href="request.php"><i class="fas fa-hand-holding"></i> Request Resource</a>
        <a href="request_status.php"><i class="fas fa-history"></i> Request Status</a>
        <a href="incoming.php"><i class="fas fa-inbox"></i> Incoming Request</a>
        <a href="issued_resources.php"><i class="fas fa-hand-holding-heart"></i> <span>Issued Resources</span></a>
        <a href="returning.php"><i class="fas fa-exchange-alt"></i> Returning</a>
        <a href="report.php"><i class="fas fa-chart-bar"></i> Report</a>
        <a href="user_settings.php" class="active"><i class="fas fa-cog"></i> Settings</a>
        <div class="mt-auto">
            <a href="#" class="logout-btn mt-4" id="logoutButton"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="col-md-9 col-lg-10 p-4 main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="page-header">
                Settings
            </h2>
        </div>

        <!-- User Info Panel -->
        <div class="user-info-panel">
            <div class="d-flex align-items-center">
                <div class="user-avatar" data-bs-toggle="modal" data-bs-target="#changeProfilePictureModal">
                    <?php 
                    $profile_photo_path = !empty($user_data['user_photo']) ? $user_data['user_photo'] : 'uploads/profile_pictures/default_profile.png';
                    
                    if (!file_exists($profile_photo_path) || is_dir($profile_photo_path)) {
                        $profile_photo_path = 'uploads/profile_pictures/default_profile.png';
                    }
                    ?>
                    <img src="<?php echo htmlspecialchars($profile_photo_path); ?>" alt="Profile Picture">
                    <div class="camera-icon-overlay">
                        <i class="fas fa-camera"></i>
                    </div>
                </div>
                <div class="ms-3">
                    <div class="user-name"><?php echo htmlspecialchars($user_data['user_full_name'] ?? 'User'); ?></div>
                    <div class="role-badge">
                        <?php 
                        $role = $user_data['role'] ?? '';
                        $role_display = '';
                        switch($role) {
                            case 'superadmin':
                                $role_display = 'Super Administrator';
                                break;
                            case 'barangay_official':
                                $role_display = 'Barangay Official';
                                break;
                            case 'secretary':
                                $role_display = 'Secretary';
                                break;
                            case 'captain':
                                $role_display = 'Barangay Captain';
                                break;
                            default:
                                $role_display = 'User';
                        }
                        echo $role_display;
                        ?>
                    </div>
                </div>
            </div>
            
            <!-- Additional user details -->
            <div class="user-details mt-3">
                <?php if (!empty($user_data['brgy_name'])): ?>
                    <div class="user-detail-item">
                        <i class="fas fa-map-marker-alt me-2"></i>
                        Barangay: <?php echo htmlspecialchars($user_data['brgy_name']); ?>
                    </div>
                <?php endif; ?>
                
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
                <div class="card settings-card" data-target-section="password-section">
                    <div class="card-body">
                        <div class="settings-icon-container">
                            <i class="fas fa-key settings-icon"></i>
                        </div>
                        <h5 class="card-title">Change Password</h5>
                        <p class="card-text">Update your account password to keep your account secure.</p>
                        <div class="text-primary mt-2">
                            <small><i class="fas fa-arrow-right me-1"></i> Update now</small>
                        </div>
                    </div>
                    <div class="card-hover-indicator"></div>
                </div>
                
                <!-- Profile Settings Card -->
                <div class="card settings-card" data-target-section="profile-section">
                    <div class="card-body">
                        <div class="settings-icon-container">
                            <i class="fas fa-user settings-icon"></i>
                        </div>
                        <h5 class="card-title">Profile Settings</h5>
                        <p class="card-text">Manage your full name and username.</p>
                        <div class="text-primary mt-2">
                            <small><i class="fas fa-arrow-right me-1"></i> Update now</small>
                        </div>
                    </div>
                    <div class="card-hover-indicator"></div>
                </div>
                
                <!-- 2FA Card -->
                <div class="card settings-card" data-target-section="2fa-section">
                    <div class="card-body">
                        <div class="settings-icon-container">
                            <i class="fas fa-shield-alt settings-icon"></i>
                        </div>
                        <h5 class="card-title">Two-Factor Authentication</h5>
                        <p class="card-text">Add an extra layer of security to your account.</p>
                        <div class="text-primary mt-2">
                            <small><i class="fas fa-arrow-right me-1"></i> Configure</small>
                        </div>
                    </div>
                    <div class="card-hover-indicator"></div>
                </div>
            </div>

            <!-- Password Change Section -->
            <div id="password-section" class="settings-form-container <?php echo $show_password_section ? 'active' : ''; ?>">
                <div class="form-header">
                    <h5 class="modal-title">
                        <i class="fas fa-key me-2"></i>Change Password
                    </h5>
                    <button type="button" class="btn-close-form" data-close-section="password-section" aria-label="Close">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form method="POST" action="user_settings.php?section=password" id="passwordChangeForm">
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                        <div class="inline-error" id="current_password_error"></div>
                    </div>
                    <div class="mb-3 password-input-group">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                        <span class="password-toggle" onclick="togglePasswordVisibility('new_password')">
                            <i class="fas fa-eye"></i>
                        </span>
                        <div class="form-text">Password must be at least 6 characters long.</div>
                        <div class="inline-error" id="new_password_error"></div>
                    </div>
                    <div class="mb-3 password-input-group">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        <span class="password-toggle" onclick="togglePasswordVisibility('confirm_password')">
                            <i class="fas fa-eye"></i>
                        </span>
                        <div class="inline-error" id="confirm_password_error"></div>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="submit" name="change_password" class="btn btn-primary">Update Password</button>
                    </div>
                </form>
            </div>

            <!-- Profile Settings Section -->
            <div id="profile-section" class="settings-form-container <?php echo $show_profile_section ? 'active' : ''; ?>">
                <div class="form-header">
                    <h5 class="modal-title">
                        <i class="fas fa-user me-2"></i>Profile Settings
                    </h5>
                    <button type="button" class="btn-close-form" data-close-section="profile-section" aria-label="Close">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form method="POST" action="user_settings.php?section=profile" id="profileUpdateForm">
                    <div class="mb-3">
                        <label for="full_name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user_data['user_full_name'] ?? ''); ?>" required>
                        <div class="inline-error" id="full_name_error"></div>
                    </div>
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user_data['username'] ?? ''); ?>" placeholder="e.g., yourname@gmail.com" required>
                        <div class="form-text">Your username must end with '@gmail.com'.</div>
                        <div class="inline-error" id="username_error"></div>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="submit" name="update_profile" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>

            <!-- Two-Factor Authentication Section -->
            <div id="2fa-section" class="settings-form-container <?php echo $show_2fa_section ? 'active' : ''; ?>">
                <div class="form-header">
                    <h5 class="modal-title">
                        <i class="fas fa-shield-alt me-2"></i>Two-Factor Authentication
                    </h5>
                    <button type="button" class="btn-close-form" data-close-section="2fa-section" aria-label="Close">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form method="POST" action="user_settings.php?section=2fa" id="twoFactorForm">
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
                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" name="toggle_2fa" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Change Profile Picture Modal (still a modal as requested) -->
        <div class="modal fade" id="changeProfilePictureModal" tabindex="-1" aria-labelledby="changeProfilePictureModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="changeProfilePictureModalLabel">
                            <i class="fas fa-camera me-2"></i>Change Profile Picture
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" action="" enctype="multipart/form-data" id="profilePictureForm">
                        <div class="modal-body text-center">
                            <img id="profilePicturePreview" src="<?php echo htmlspecialchars($profile_photo_path); ?>" alt="Profile Picture Preview">
                            <div class="mb-3">
                                <label for="profile_picture_input" class="form-label">Upload New Picture</label>
                                <input class="form-control" type="file" id="profile_picture_input" name="profile_picture" accept="image/jpeg, image/png, image/gif">
                                <div class="form-text">Max file size: 5MB. Allowed formats: JPG and PNG.</div>
                                <div class="inline-error" id="profile_picture_error"></div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" name="upload_photo" class="btn btn-primary">Upload Photo</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Footer -->
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
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="../assets/js/user_settings.js"></script>
<script>
        const successMessage = "<?php echo $success_message; ?>";
        const errorMessages = <?php echo json_encode($errors_from_session); ?>;
</script>
</body>     
</html>
