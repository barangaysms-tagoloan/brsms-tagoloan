<?php
require '../logic/issued_resources/issued_resources_logic.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Issued Resources - BRSMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="icon" type="image/png" href="../uploads/BRSMS.png">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/issued_resources.css">
</head>
<body>
    <div class="wrapper">
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
            <a href="issued_resources.php" class="active"><i class="fas fa-hand-holding-heart"></i> <span>Issued Resources</span></a>
            <a href="returning.php"><i class="fas fa-exchange-alt"></i> Returning</a>
            <a href="report.php"><i class="fas fa-chart-bar"></i> Report</a>
            <a href="user_settings.php"><i class="fas fa-cog"></i> Settings</a>
            <div class="mt-auto">
                <a href="logout.php" class="logout-btn mt-4"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
            </div>
        </div>

        <div class="col-md-9 col-lg-10 p-4 main-content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="page-header">
                    Issued Resources
                </h2>
            </div>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?= htmlspecialchars($_SESSION['success']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <div class="table-wrapper">
                <div class="search-filter-row">
                    <div class="search-input-group">
                        <i class="fas fa-search"></i>
                        <input type="text" class="form-control" id="searchInput" placeholder="Search by Resource, Requester...">
                    </div>
                </div>

                <div id="tableView" class="table-view-container">
                </div>
                <div class="no-data-message mt-4" id="noDataMessage" style="display: none;">
                    <i class="fas fa-info-circle mb-3"></i>
                    <h5>No Issued Resources Yet</h5>
                    <p class="mb-3">There are no records of issued resources from your barangay.</p>
                </div>
            </div>

            <footer class="footer mt-auto py-1">
                <div class="container-fluid">
                    <span>&copy; <?= date('Y') ?> BRSMS. All rights reserved.</span>
                </div>
            </footer>
        </div>
    </div>

    <div class="modal fade" id="purposeModal" tabindex="-1" aria-labelledby="purposeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="purposeModalLabel">Request Purpose</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="modalPurposeText"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="returnConditionModal" tabindex="-1" aria-labelledby="returnConditionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="returnConditionModalLabel">Return Resource</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="returnModalIntroText">ADD RETURN CONDITION</p>
                    <div id="singleItemCondition" style="display: none;">
                        <div class="mb-3">
                            <label for="returnCondition" class="form-label">Condition:</label>
                            <select class="form-select" id="returnCondition">
                                <option value="Good">Good</option>
                                <option value="Minor Scratches">Minor Scratches</option>
                                <option value="Damaged">Damaged</option>
                                <option value="Lost">Lost</option>
                                <option value="Other">Other (Please specify)</option>
                            </select>
                        </div>
                        <div class="mb-3" id="otherConditionGroup" style="display: none;">
                            <label for="otherConditionText" class="form-label">Specify Other Condition:</label>
                            <input type="text" class="form-control" id="otherConditionText" placeholder="e.g., Broken handle">
                        </div>
                    </div>

                    <div id="bulkItemConditions" style="display: none;">
                        <div class="mb-3 d-flex align-items-center">
                            <label for="applyAllCondition" class="form-label me-2 mb-0">Apply to all:</label>
                            <select class="form-select w-auto me-2" id="applyAllCondition">
                                <option value="">Select Condition</option>
                                <option value="Good">Good</option>
                                <option value="Minor Scratches">Minor Scratches</option>
                                <option value="Damaged">Damaged</option>
                                <option value="Lost">Lost</option>
                                <option value="Other">Other (Specify)</option>
                            </select>
                            <input type="text" class="form-control w-auto" id="applyAllOtherConditionText" placeholder="Specify other condition" style="display:none;">
                            <button type="button" class="btn btn-primary ms-2" id="applyAllBtn">Apply</button>
                        </div>
                        <hr>
                        <div id="bulkItemsList">
                            <p class="text-muted">Loading borrowed items...</p>
                        </div>
                    </div>

                    <input type="hidden" id="modalRequestId">
                    <input type="hidden" id="modalIsBulk">
                    <input type="hidden" id="modalRequestedQuantity">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmReturnBtn">Confirm Return</button>
                </div>
            </div>
        </div>
    </div>

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

    <div class="toast-container">
        <div id="actionToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="3000">
            <div class="toast-body" id="toastMessage">

            </div>
        </div>
    </div>

    <div class="modal fade" id="requestDetailsModal" tabindex="-1" aria-labelledby="requestDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-white text-dark">
                    <h5 class="modal-title" id="requestDetailsModalLabel"><i class="fas fa-info-circle me-2"></i> Issued Request Details</h5>
                    <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="modalRequestDetailsContent">

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/issued_resources.js"></script>
    <script>
        const issuedResourcesData = <?= json_encode($issued_resources_data) ?>;
    </script>
</body>
</html>
