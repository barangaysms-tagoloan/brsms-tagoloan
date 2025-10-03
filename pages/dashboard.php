<?php
require '../logic/dashboard/dashboard_logic.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - BRSMS</title>
    <link rel="icon" type="image/png" href="../uploads/BRSMS.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
<div class="wrapper">
    <!-- Sidebar - Mirrored from inventory.php -->
    <div class="col-md-3 col-lg-2 sidebar d-flex flex-column">
        <div class="logo-container">
            <div class="logo-circle">
                <img src="../uploads/BRSMS.png" alt="BRSMS Logo">
            </div>
            <div class="logo-text">BRSMS</div>
        </div>
        <a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="inventory.php"><i class="fas fa-boxes"></i> Inventory</a>
        <a href="request.php"><i class="fas fa-hand-holding"></i> Request Resource</a>
        <a href="request_status.php"><i class="fas fa-history"></i> Request Status</a>
        <a href="incoming.php"><i class="fas fa-inbox"></i> Incoming Request</a>
        <a href="issued_resources.php"><i class="fas fa-hand-holding-heart"></i> <span>Issued Resources</span></a>
        <a href="returning.php"><i class="fas fa-exchange-alt"></i> Returning</a>
        <a href="report.php"><i class="fas fa-chart-bar"></i> Report</a>
        <a href="user_settings.php"><i class="fas fa-cog"></i> Settings</a>
        <div class="mt-auto">
            <a href="#" class="logout-btn mt-4" id="logoutButton"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="col-md-9 col-lg-10 p-4 main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="page-header">
             Dashboard
            </h2>
        </div>

        <div class="welcome-message">
            <div class="welcome-text">
                <h2>Welcome, <?= htmlspecialchars($_SESSION['user_full_name']) ?>!</h2>
                <p class="mb-0">Here is a quick overview of the current resource activities in your barangay.</p>
            </div>
            <div class="user-info">
              
            </div>
        </div>

        <!-- Statistics -->
        <div class="row mb-4">
            <div class="col-md-6 col-lg-3 mb-4 d-flex">
                <div class="stat-card resources h-100 w-100" onclick="window.location.href='inventory.php'">
                    <div class="content">
                        <div class="card-title">Total Resources</div>
                        <div class="card-value"><?= $stats['total_resources_count'] ?></div>
                        <div class="breakdown">
                            <div class="breakdown-item">
                                <span><i class="fas fa-check-circle status-available"></i> Available:</span>
                                <strong><?= $stats['total_available_items'] ?></strong>
                            </div>
                            <div class="breakdown-item">
                                <span><i class="fas fa-hand-holding status-borrowed"></i> Borrowed:</span>
                                <strong><?= $stats['total_borrowed_items'] ?></strong>
                            </div>
                            <div class="breakdown-item">
                                <span><i class="fas fa-tools status-maintenance"></i> Under Maintenance:</span>
                                <strong><?= $stats['total_maintenance_items'] ?></strong>
                            </div>
                            <div class="breakdown-item">
                                <span><i class="fas fa-times-circle status-lost"></i> Lost:</span>
                                <strong><?= $stats['total_lost_items'] ?></strong>
                            </div>
                            <div class="breakdown-item" style="border-top: 1px solid rgba(0,0,0,0.05); padding-top: 8px; margin-top: 8px;">
                                <span><i class="fas fa-sum"></i> Total Quantity:</span> <!-- Changed icon here -->
                                <strong><?= $stats['total_resources_quantity'] ?></strong>
                            </div>
                        </div>
                    </div>
                    <i class="fas fa-boxes card-icon"></i>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3 mb-4 d-flex">
                <div class="stat-card pending h-100 w-100" onclick="window.location.href='incoming.php'">
                    <div class="content">
                        <div class="card-title">Pending Requests</div>
                        <div class="card-value"><?= $stats['total_pending_requests'] ?></div>
                        <div class="card-details">
                            <small>Requests awaiting approval.</small>
                        </div>
                    </div>
                    <i class="fas fa-clock card-icon"></i>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3 mb-4 d-flex">
                <div class="stat-card issued h-100 w-100" onclick="window.location.href='issued_resources.php'">
                    <div class="content">
                        <div class="card-title">Issued Resources</div>
                        <div class="card-value"><?= $stats['total_issued_resources'] ?></div>
                        <div class="card-details">
                            <small>Resources currently borrowed.</small>
                        </div>
                    </div>
                    <i class="fas fa-hand-holding-heart card-icon"></i>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3 mb-4 d-flex">
                <div class="stat-card returned h-100 w-100" onclick="window.location.href='returning.php'">
                    <div class="content">
                        <div class="card-title">Returned Resources</div>
                        <div class="card-value"><?= $stats['total_returned_resources'] ?></div>
                        <div class="card-details">
                            <small>Resources successfully returned.</small>
                        </div>
                    </div>
                    <i class="fas fa-exchange-alt card-icon"></i>
                </div>
            </div>
        </div>

        <div class="row flex-grow-1"> <!-- Allow this row to grow -->
            <!-- Latest Borrowed Items (This is effectively 'Issued' items based on the query) -->
            <div class="col-lg-6 mb-4 d-flex">
                <div class="card h-100 w-100">
                    <div class="card-header borrowed">
                        <i class="fas fa-hand-holding-heart"></i> Latest Borrowed Resources (Last 5)
                    </div>
                    <div class="card-body">
                        <?php if (!empty($latest_borrowed_items)): ?>
                            <div class="table-responsive">
                                <table class="table table-minimalist">
                                    <thead>
                                    <tr>
                                        <th>Resource Name</th>
                                        <th>Requester</th>
                                        <th>Barangay</th>
                                        <th>Qty</th>
                                        <th>Date Borrowed</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($latest_borrowed_items as $item): ?>
                                        <tr>
                                            <td data-label="Resource Name"><?= htmlspecialchars($item['res_name']) ?></td>
                                            <td data-label="Requester"><?= htmlspecialchars($item['user_full_name']) ?></td>
                                            <td data-label="Barangay"><?= htmlspecialchars($item['brgy_name']) ?></td>
                                            <td data-label="Quantity"><?= $item['req_quantity'] ?></td>
                                            <td data-label="Date Borrowed"><?= date('M d, Y', strtotime($item['req_date'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted mb-0 text-center py-3">No borrowed items found recently.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Latest Returned Resources -->
            <div class="col-lg-6 mb-4 d-flex">
                <div class="card h-100 w-100">
                    <div class="card-header returned">
                        <i class="fas fa-exchange-alt"></i> Latest Returned Resources (Last 5)
                    </div>
                    <div class="card-body">
                        <?php if (!empty($latest_returned_resources)): ?>
                            <div class="table-responsive">
                                <table class="table table-minimalist">
                                    <thead>
                                    <tr>
                                        <th>Resource Name</th>
                                        <th>Requester</th>
                                        <th>Barangay</th>
                                        <th>Qty</th>
                                        <th>Date Returned</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($latest_returned_resources as $item): ?>
                                        <tr>
                                            <td data-label="Resource Name"><?= htmlspecialchars($item['res_name']) ?></td>
                                            <td data-label="Requester"><?= htmlspecialchars($item['user_full_name']) ?></td>
                                            <td data-label="Barangay"><?= htmlspecialchars($item['brgy_name']) ?></td>
                                            <td data-label="Quantity"><?= $item['req_quantity'] ?></td>
                                            <td data-label="Date Returned"><?= date('M d, Y', strtotime($item['return_date'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted mb-0 text-center py-3">No returned resources found recently.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <!-- Example Footer -->
        <footer class="footer mt-auto py-1">
            <div class="container-fluid">
                <span>&copy; <?= date('Y') ?> BRSMS. All rights reserved.</span>
            </div>
        </footer>
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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../assets/js/dashboard.js"></script>
<script>
    <?php if (isset($_SESSION['toast_message'])): ?>
        showToast("<?= htmlspecialchars($_SESSION['toast_message']) ?>", "<?= htmlspecialchars($_SESSION['toast_type']) ?>");
        <?php
        unset($_SESSION['toast_message']);
        unset($_SESSION['toast_type']);
        ?>
    <?php endif; ?>
    document.getElementById('logoutButton').addEventListener('click', function(event) {
        event.preventDefault();
        confirmLogout();
    });
</script>
</body>
</html>
