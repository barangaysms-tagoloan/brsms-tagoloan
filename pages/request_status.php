<?php
require '../logic/request_status/request_status_logic.php'
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Status - BRSMS</title>
    <link rel="icon" type="image/png" href="../uploads/BRSMS.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="../assets/css/request_status.css">
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
            <a href="request_status.php" class="active"><i class="fas fa-history"></i> Request Status</a>
            <a href="incoming.php"><i class="fas fa-inbox"></i> Incoming Request</a>
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
            <!-- Hidden inputs for PHP messages -->
            <input type="hidden" id="php_success_message" value="<?= htmlspecialchars($js_success_message) ?>">
            <input type="hidden" id="php_error_message" value="<?= htmlspecialchars($js_error_message) ?>">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="dashboard-title">Request Status</h1>
                </div>
            </div>

            <!-- Requests Table Wrapper (now contains search/filter and card rows) -->
            <div class="table-wrapper">
                <!-- Search Bar and Status Filter -->
                <div class="search-filter-row">
                    <div class="search-input-group">
                        <i class="fas fa-search"></i>
                        <input type="text" class="form-control" id="searchInput" placeholder="Search by Resource, Barangay, or ID...">
                    </div>
                    <select class="form-select" id="statusFilter">
                        <option value="all">All Statuses</option>
                        <option value="Pending">Pending</option>
                        <option value="Approved">Approved</option>
                        <option value="Rejected">Rejected</option>
                        <option value="Borrowed">Borrowed</option>
                        <option value="Cancelled">Cancelled</option>
                        <option value="Completed">Completed</option>
                    </select>
                </div>

                <div class="request-list-container">
                    <?php if ($requests->num_rows > 0) : ?>
                        <?php while ($request = $requests->fetch_assoc()) :
                            $photo_src = !empty($request['res_photo']) ? '../logic/inventory/uploads/' . htmlspecialchars($request['res_photo']) : 'images/default-item.jpg';
                        ?>
                            <div class="request-card-wrapper" data-reqid="<?= $request['req_id'] ?>">
                                <!-- Moved remove button inside the wrapper, positioned absolutely -->
                                <div class="remove-card-btn" title="Remove from view" data-reqid="<?= $request['req_id'] ?>">
                                    <i class="fas fa-times"></i>
                                </div>
                                <div class="request-card-row status-<?= strtolower($request['req_status']) ?> request-item"
                                     data-status="<?= strtolower($request['req_status']) ?>"
                                     data-search="<?= htmlspecialchars(strtolower("#{$request['req_id']} {$request['res_name']} {$request['owner_brgy_name']}")) ?>">
                                    <div class="resource-thumbnail view-details" data-details="<?= htmlspecialchars(json_encode($request)) ?>">
                                        <img src="<?= $photo_src ?>" alt="Resource Photo">
                                    </div>
                                    <div class="row-content">
                                        <!-- Removed the Request ID display from here -->
                                        <div class="info-group resource-col">
                                            <strong>Resource:</strong>
                                            <span><?= htmlspecialchars($request['res_name']) ?></span>
                                        </div>
                                        <div class="info-group barangay-col">
                                            <strong>From Barangay:</strong>
                                            <span><?= htmlspecialchars($request['owner_brgy_name']) ?></span>
                                        </div>
                                        <div class="info-group quantity-col">
                                            <strong>Quantity:</strong>
                                            <span><?= $request['req_quantity'] ?></span>
                                        </div>
                                        <div class="info-group status-col">
                                            <strong>Status:</strong>
                                            <span>
                                                <span class="status-badge badge-<?= strtolower($request['req_status']) ?>">
                                                    <?= htmlspecialchars($request['req_status']) ?>
                                                </span>
                                            </span>
                                        </div>
                                        <div class="info-group date-col">
                                            <strong>Date:</strong>
                                            <span><?= date('M d, Y', strtotime($request['req_date'])) ?></span>
                                        </div>
                                    </div>
                                    <div class="request-actions">
                                        <button class="btn btn-sm btn-outline-primary view-details"
                                                data-details="<?= htmlspecialchars(json_encode($request)) ?>"
                                                title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>

                                        <?php if ($request['req_status'] === 'Approved' || $request['req_status'] === 'Borrowed'): ?>
                                            <a href="../generate_pdf_receipt.php?request_id=<?= $request['req_id'] ?>&source=requester"
                                               class="btn btn-sm btn-success text-white"
                                               target="_blank"
                                               title="Generate PDF Receipt">
                                                <i class="fas fa-file-pdf"></i>
                                            </a>
                                        <?php endif; ?>

                                        <?php if ($request['req_status'] === 'Pending'): ?>
                                            <button class="btn btn-sm btn-outline-cancelled cancel-request"
                                                    data-reqid="<?= $request['req_id'] ?>"
                                                    title="Cancel Request">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else : ?>
                        <div class="no-requests mt-4">
                            <i class="fas fa-info-circle fa-3x mb-3 text-secondary"></i>
                            <h5>No Requests Found</h5>
                            <p class="mb-0">You haven't made any resource requests yet.</p>
                            <a href="request.php" class="btn btn-primary mt-3">
                                <i class="fas fa-hand-holding me-1"></i> Make a Request
                            </a>
                        </div>
                    <?php endif; ?>
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

    <!-- Request Details Modal (MODIFIED: Layout similar to issued_resources.php) -->
    <div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-white text-dark">
                    <h5 class="modal-title" id="detailsModalLabel"><i class="fas fa-info-circle me-2"></i> Request Details</h5>
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

    <!-- Custom Alert Modal Structure (for cancellation and removal) -->
    <div class="custom-modal-overlay" id="unifiedCustomAlertOverlay">
        <div class="custom-modal" id="unifiedCustomAlertModal">
            <button type="button" class="close-btn" aria-label="Close" onclick="hideCustomAlert()">
                <i class="fas fa-times"></i>
            </button>
            <div class="modal-icon"></div>
            <h4 class="modal-title"></h4>
            <p class="modal-message"></p>
            <div class="modal-actions">
                <!-- Buttons will be dynamically added/removed by JS -->
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/request_status.js"></script>
</body>
</html>
