<?php
require_once '../logic/incoming/incoming_logic.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Incoming Request - BRSMS</title>
    <link rel="icon" type="image/png" href="../uploads/BRSMS.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/incoming.css">
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar - MIRRORED FROM DASHBOARD.PHP -->
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
            <a href="incoming.php" class="active"><i class="fas fa-inbox"></i> Incoming Request</a>
            <a href="issued_resources.php"><i class="fas fa-hand-holding-heart"></i> <span>Issued Resources</span></a>
            <a href="returning.php"><i class="fas fa-exchange-alt"></i> Returning</a>
            <a href="report.php"><i class="fas fa-chart-bar"></i> Report</a>
            <a href="user_settings.php"><i class="fas fa-cog"></i> Settings</a>
            <div class="mt-auto">
                <a href="logout.php" class="logout-btn mt-4"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 col-lg-10 p-4 main-content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="page-header">
                    Incoming Requests
                </h2>
            </div>

            <?php
            
            if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show text-center" role="alert">
                    <?= htmlspecialchars($_SESSION['success_message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>

            <?php
            
            if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show text-center" role="alert">
                    <?= htmlspecialchars($_SESSION['error_message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>

            <div class="table-wrapper">
                <!-- Search Bar and Status Filter -->
                <div class="search-filter-row">
                    <div class="search-input-group">
                        <i class="fas fa-search"></i>
                        <input type="text" class="form-control" id="searchInput" placeholder="Search by Resource, Requester, Contact #...">
                    </div>
                    <select class="form-select" id="statusFilter">
                        <option value="all" <?= $status_filter === 'all' ? 'selected' : '' ?>>All Statuses</option>
                        <option value="Pending" <?= $status_filter === 'Pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="Approved" <?= $status_filter === 'Approved' ? 'selected' : '' ?>>Approved</option>
                        <option value="Rejected" <?= $status_filter === 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                        <option value="Borrowed" <?= $status_filter === 'Borrowed' ? 'selected' : '' ?>>Borrowed</option>
                        <option value="Cancelled" <?= $status_filter === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        <option value="Completed" <?= $status_filter === 'Completed' ? 'selected' : '' ?>>Completed</option>
                    </select>
                </div>

                <div id="tableView" class="table-view-container">
                    <!-- Table will be rendered here by JavaScript -->
                    <!-- No data message will be handled by JavaScript based on filtered data -->
                </div>
                <!-- No data message container, initially hidden -->
                <div class="no-data-message mt-4" id="noDataMessage" style="display: none;">
                    <i class="fas fa-info-circle mb-3"></i>
                    <h5>No Incoming Requests Yet</h5>
                    <p class="mb-3">There are no records of incoming requests for your barangay.</p>
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

    <!-- Request Details Modal (NEW MODAL) -->
    <div class="modal fade" id="requestDetailsModal" tabindex="-1" aria-labelledby="requestDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="requestDetailsModalLabel">Request Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-5 text-center">
                            <div class="resource-image-container">
                                <img id="modalResourcePhoto" src="" alt="Resource Photo" class="img-fluid">
                            </div>
                            <div class="resource-details">
                                <h6>Resource Information</h6>
                                <div class="detail-item">
                                    <strong>Resource Name:</strong>
                                    <span id="modalResourceName"></span>
                                </div>
                                <div class="detail-item">
                                    <strong>Quantity Requested:</strong>
                                    <span id="modalQuantityRequested"></span>
                                </div>
                                <div class="detail-item">
                                    <strong>Is Bulk:</strong>
                                    <span id="modalIsBulk"></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <div class="request-details">
                                <h6>Requester & Request Information</h6>
                                <div class="detail-item">
                                    <strong>Requester Name:</strong>
                                    <span id="modalRequesterName"></span>
                                </div>
                                <div class="detail-item">
                                    <strong>Requester Barangay:</strong>
                                    <span id="modalRequesterBrgy"></span>
                                </div>
                                <div class="detail-item">
                                    <strong>Contact Number:</strong>
                                    <span id="modalContactNumber"></span>
                                </div>
                                <div class="detail-item">
                                    <strong>Request Date:</strong>
                                    <span id="modalRequestDate"></span>
                                </div>
                                <div class="detail-item">
                                    <strong>Expected Return:</strong>
                                    <span id="modalReturnDate"></span>
                                </div>
                                <div class="detail-item">
                                    <strong>Borrowed On:</strong>
                                    <span id="modalBorrowedTimestamp"></span>
                                </div>
                                <div class="detail-item">
                                    <strong>Returned On:</strong>
                                    <span id="modalReturnTimestamp"></span>
                                </div>
                                <div class="detail-item status-info">
                                    <strong>Status:</strong>
                                    <span id="modalStatus" class="badge"></span>
                                </div>
                                <div class="detail-item">
                                    <strong>Purpose:</strong>
                                    <p id="modalPurpose" class="purpose-text"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Select Items Modal -->
    <div class="modal fade" id="selectItemsModal" tabindex="-1" aria-labelledby="selectItemsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="selectItemsModalLabel">Select Items for <span id="selectItemsResourceName"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="info-row">
                        <span class="label">Requested Quantity:</span>
                        <span id="requiredQuantity" class="value"></span>
                    </div>
                    <div class="info-row">
                        <span class="label">Selected Items:</span>
                        <span class="value"><span id="selectedCount">0</span> / <span id="totalAvailableItems">0</span></span>
                    </div>
                    <div id="selectionError" class="alert alert-danger d-none"></div>
                    <table class="table table-hover item-selection-table">
                        <thead>
                            <tr>
                                <th>Select</th>
                                <!-- Removed Item Name column header -->
                                <th>Serial Number</th>
                                <th>QR Code</th>
                            </tr>
                        </thead>
                        <tbody id="availableItemsTableBody">
                            <!-- Items will be loaded here by JavaScript -->
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="confirmSelectionBtn">Confirm Selection</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Quantity Modal -->
    <div class="modal fade" id="editQuantityModal" tabindex="-1" aria-labelledby="editQuantityModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editQuantityModalLabel">Edit Quantity</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editQuantityForm">
                        <div class="mb-3">
                            <label for="editQuantityInput" class="form-label">New Quantity:</label>
                            <input type="number" class="form-control" id="editQuantityInput" min="1" required>
                            <div class="invalid-feedback" id="quantityError">
                                Please enter a valid quantity (numeric, greater than 0).
                            </div>
                        </div>
                        <input type="hidden" id="editRequestId">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveQuantityBtn">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Custom Alert Modal Structure -->
    <div class="custom-modal-overlay" id="customAlertModalOverlay">
        <div class="custom-modal" id="customAlertModal">
            <button type="button" class="close-btn" aria-label="Close" id="customAlertCloseBtn">
                <i class="fas fa-times"></i>
            </button>
            <div class="modal-icon" id="customAlertIcon"></div>
            <h4 class="modal-title" id="customAlertTitle"></h4>
            <p class="modal-message" id="customAlertMessage"></p>
            <div class="modal-actions">
                <button class="btn-primary-action" id="customAlertPrimaryBtn"></button>
                <button class="btn-secondary-action" id="customAlertSecondaryBtn">Cancel</button>
            </div>
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
    <script src="../assets/js/incoming.js"></script>
    <script>

        let incomingRequestsData = <?= json_encode($incoming_requests_data) ?>;
        const initialStatusFilter = "<?= htmlspecialchars($status_filter) ?>";

    </script>
</body>
</html>
