<?php
require '../logic/returning/returning_logic.php'
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Returning - BRSMS</title>
        <link rel="icon" type="image/png" href="../uploads/BRSMS.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="../assets/css/returning.css">
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
            <a href="returning.php" class="active"><i class="fas fa-exchange-alt"></i> <span>Returning</span></a>
            <a href="report.php"><i class="fas fa-chart-bar"></i> Report</a>
            <a href="user_settings.php"><i class="fas fa-cog"></i> Settings</a>
            <div class="mt-auto">
                <a href="logout.php" class="logout-btn mt-4"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 col-lg-10 p-4 main-content">
            <?php if (isset($_SESSION['toast'])): ?>
                <div class="alert alert-<?= $_SESSION['toast']['type'] ?> alert-dismissible fade show">
                    <i class="fas <?= $_SESSION['toast']['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?> me-2"></i>
                    <?= htmlspecialchars($_SESSION['toast']['message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['toast']); ?>
            <?php endif; ?>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="page-header">Returned Resources</h1>
                </div>
                <!-- Removed Toggle View Button -->
            </div>

            <div class="table-wrapper">
                <!-- Search Bar and Condition Filter -->
                <div class="search-filter-row">
                    <div class="search-input-group">
                        <i class="fas fa-search"></i>
                        <input type="text" class="form-control" id="searchInput" placeholder="Search by Resource, Requester...">
                    </div>
                    <select class="form-select" id="conditionFilter">
                        <option value="all" <?= $condition_filter === 'all' ? 'selected' : '' ?>>All Conditions</option>
                        <option value="good" <?= $condition_filter === 'good' ? 'selected' : '' ?>>Good</option>
                        <option value="minor scratches" <?= $condition_filter === 'minor scratches' ? 'selected' : '' ?>>Minor Scratches</option>
                        <option value="damaged" <?= $condition_filter === 'damaged' ? 'selected' : '' ?>>Damaged</option>
                        <option value="lost" <?= $condition_filter === 'lost' ? 'selected' : '' ?>>Lost</option>
                        <option value="other" <?= $condition_filter === 'other' ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>

                <div id="tableView" class="table-view-container">
                    <!-- Table will be rendered here by JavaScript -->
                </div>
                <!-- No data message container, initially hidden -->
                <div class="no-data-message mt-4" id="noDataMessage" style="display: none;">
                    <i class="fas fa-info-circle mb-3"></i>
                    <h5>No Returned Resources Yet</h5>
                    <p class="mb-3">There are no records of returned resources in your barangay.</p>
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

    <!-- Return Details Modal -->
    <div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-white text-dark">
                    <h5 class="modal-title" id="detailsModalLabel"><i class="fas fa-info-circle me-2"></i> Return Details</h5>
                    <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="modalDetailsContent">
                    <!-- Details will be loaded here via JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Individual Items under a Condition Group -->
    <div class="modal fade" id="itemDetailsModal" tabindex="-1" aria-labelledby="itemDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header text-white" id="itemDetailsModalHeader">
                    <h5 class="modal-title" id="itemDetailsModalLabel">Items with Condition: <span id="itemDetailsCondition"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Serial Number</th>
                                <th>Condition</th>
                            </tr>
                        </thead>
                        <tbody id="itemDetailsTableBody">
                            <!-- Item details will be loaded here -->
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>


    <!-- Toast Notification -->
    <div class="toast-container">
        <div id="liveToast" class="toast align-items-center text-white bg-primary" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="toastMessage">
                    <!-- Message will be inserted here -->
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/returning.js"></script>
    <script>

        const returnsData = <?= json_encode($processed_returns_data) ?>;
        const currentConditionFilter = "<?= $condition_filter ?>";

        <?php if (isset($_SESSION['toast'])): ?>
        const toast = new bootstrap.Toast(document.getElementById('liveToast'));
        document.getElementById('toastMessage').innerHTML = `
            <i class="fas <?= $_SESSION['toast']['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?> me-2"></i>
            <?= addslashes($_SESSION['toast']['message']) ?>
        `;
        document.getElementById('liveToast').classList.add('bg-<?= $_SESSION['toast']['type'] ?>');
        toast.show();
        <?php unset($_SESSION['toast']); ?>
        
        <?php endif; ?>

    </script>
</body>
</html>
