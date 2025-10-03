<?php
require '../logic/reports/reports_logic.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reports - BRSMS</title>
    <link rel="icon" type="image/png" href="../uploads/BRSMS.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/report.css">
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
        <a href="incoming.php"><i class="fas fa-inbox"></i> Incoming Request</a>
        <a href="issued_resources.php"><i class="fas fa-hand-holding-heart"></i> <span>Issued Resources</span></a>
        <a href="returning.php"><i class="fas fa-exchange-alt"></i> Returning</a>
        <a href="report.php" class="active"><i class="fas fa-chart-bar"></i> Report</a>
        <a href="user_settings.php"><i class="fas fa-cog"></i> Settings</a>
        <div class="mt-auto">
            <a href="logout.php" class="logout-btn mt-4"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="col-md-9 col-lg-10 p-4 main-content">
        <!-- Removed the mainContentToggle button as per dashboard.php -->

        <div class="d-flex justify-content-between align-items-center mb-4 no-print">
            <div>
                <h2 class="page-header">
                    Reports
                </h2>
            </div>
            <div>
            </div>
        </div>

        <!-- Print Header -->
        <div class="print-only mb-4">
            <div class="text-center">
                <h3>Barangay Resource Sharing Management System</h3>
                <h4>Report - <?= ucfirst($report_type) ?></h4>
                <div class="details-row justify-content-center">
                    <div class="detail-item">
                        <span class="detail-label">Barangay:</span>
                        <span><?= $_SESSION['brgy_name'] ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Generated On:</span>
                        <span><?= date('F j, Y') ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Period:</span>
                        <?php if (!empty($selected_month) && !empty($selected_year) && $report_type !== 'inventory'): ?>
                            <span><?= date('F', mktime(0, 0, 0, $selected_month, 10)) . ' ' . $selected_year ?></span>
                        <?php else: ?>
                            <span>N/A</span>
                        <?php endif; ?>
                    </div>
                    <?php if ($status_filter !== '' && $status_filter !== 'all'): ?>
                    <div class="detail-item">
                        <span class="detail-label">Status:</span>
                        <span><?= ucfirst($status_filter) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Filter Form -->
        <div class="card shadow-sm mb-4 no-print">
            <div class="card-header" style="background-color: var(--dark-violet); color: white;">
                <i class="fas fa-filter me-2"></i> Report Filters
            </div>
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Report Type</label>
                        <select name="report_type" class="form-select" onchange="updateStatusOptions(this.value)">
                            <option value="" disabled <?= $report_type === '' ? 'selected' : '' ?>>Select Report Type</option>
                            <option value="requests" <?= $report_type === 'requests' ? 'selected' : '' ?>>Requests</option>
                            <option value="inventory" <?= $report_type === 'inventory' ? 'selected' : '' ?>>Resources</option>
                            <option value="returns" <?= $report_type === 'returns' ? 'selected' : '' ?>>Returns</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select name="status" id="statusSelect" class="form-select">
                            <option value="" disabled <?= $status_filter === '' ? 'selected' : '' ?>>Select Status</option>
                            <?php if ($report_type === 'requests'): ?>
                                <option value="all" <?= $status_filter === 'all' ? 'selected' : '' ?>>All Statuses</option>
                                <option value="Pending" <?= $status_filter === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="Approved" <?= $status_filter === 'Approved' ? 'selected' : '' ?>>Approved</option>
                                <option value="Rejected" <?= $status_filter === 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                                <option value="Borrowed" <?= $status_filter === 'Borrowed' ? 'selected' : '' ?>>Borrowed</option>
                                <option value="Cancelled" <?= $status_filter === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                <option value="Completed" <?= $status_filter === 'Completed' ? 'selected' : '' ?>>Completed</option>
                            <?php elseif ($report_type === 'inventory'): ?>
                                <option value="all" <?= $status_filter === 'all' ? 'selected' : '' ?>>All Statuses</option>
                                <option value="Available" <?= $status_filter === 'Available' ? 'selected' : '' ?>>Available</option>
                                <option value="Borrowed" <?= $status_filter === 'Borrowed' ? 'selected' : '' ?>>Borrowed</option>
                                <option value="Under Maintenance" <?= $status_filter === 'Under Maintenance' ? 'selected' : '' ?>>Under Maintenance</option>
                            <?php else: ?>
                                <option value="all" <?= $status_filter === 'all' ? 'selected' : '' ?>>All Conditions</option>
                                <option value="good" <?= $status_filter === 'good' ? 'selected' : '' ?>>Good</option>
                                <option value="minor scratches" <?= $status_filter === 'minor scratches' ? 'selected' : '' ?>>Minor Scratches</option>
                                <option value="damaged" <?= $status_filter === 'damaged' ? 'selected' : '' ?>>Damaged</option>
                                <option value="lost" <?= $status_filter === 'lost' ? 'selected' : '' ?>>Lost</option>
                                <option value="other" <?= $status_filter === 'other' ? 'selected' : '' ?>>Other</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-2" id="monthFilter">
                        <label class="form-label">Month</label>
                        <select name="month" class="form-select">
                            <option value="" <?= empty($selected_month) ? 'selected' : '' ?>>Select Month</option>
                            <?php for ($m = 1; $m <= 12; $m++): ?>
                                <option value="<?= sprintf('%02d', $m) ?>" <?= ($selected_month == sprintf('%02d', $m)) ? 'selected' : '' ?>>
                                    <?= date('F', mktime(0, 0, 0, $m, 10)) ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-2" id="yearFilter">
                        <label class="form-label">Year</label>
                        <select name="year" class="form-select">
                            <option value="" <?= empty($selected_year) ? 'selected' : '' ?>>Select Year</option>
                            <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                                <option value="<?= $y ?>" <?= ($selected_year == $y) ? 'selected' : '' ?>>
                                    <?= $y ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn" style="background-color: var(--dark-violet); color: white;">
                            <i class="fas fa-filter me-2"></i> Apply Filters
                        </button>
                        <a href="report.php" class="btn btn-secondary ms-2">
                            <i class="fas fa-sync me-2"></i> Reset
                        </a>
                        <button type="submit" name="export" value="pdf" class="btn btn-danger ms-2">
                            <i class="fas fa-file-pdf me-2"></i> Export PDF
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <?php if ($report_type === ''): ?>
            <div class="alert alert-info text-center" role="alert">
                Please select a report type from the dropdown above to view the report.
            </div>
        <?php elseif (($report_type === 'requests' || $report_type === 'returns') && (empty($selected_month) || empty($selected_year))): ?>
            <div class="alert alert-warning text-center" role="alert">
                Please select both a Month and a Year to view the report.
            </div>
        <?php else: ?>
            <!-- Requests Report -->
            <?php if ($report_type === 'requests'): ?>
                <div class="table-container">
                    <table class="table table-minimalist mb-0">
                        <thead>
                            <tr>
                                <th class="col-req-id hide-id">ID</th>
                                <th class="col-resource">Resource</th>
                                <th class="col-requester">Requester</th>
                                <th class="col-brgy">From Barangay</th>
                                <th class="col-quantity">Qty</th>
                                <th class="col-req-date">Request Date</th>
                                <th class="col-ret-date">Return Date</th>
                                <th class="col-status">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($requests->num_rows > 0): ?>
                                <?php while ($request = $requests->fetch_assoc()): ?>
                                    <tr>
                                        <td class="col-req-id hide-id" data-label="ID"><?= $request['req_id'] ?></td>
                                        <td class="col-resource" data-label="Resource"><?= htmlspecialchars($request['res_name']) ?></td>
                                        <td class="col-requester" data-label="Requester"><?= htmlspecialchars($request['requester_name']) ?></td>
                                        <td class="col-brgy" data-label="From Barangay"><?= htmlspecialchars($request['requester_brgy_name']) ?></td>
                                        <td class="col-quantity text-center" data-label="Qty"><?= $request['req_quantity'] ?></td>
                                        <td class="col-req-date" data-label="Request Date"><?= date('M d, Y', strtotime($request['req_date'])) ?></td>
                                        <td class="col-ret-date" data-label="Return Date"><?= $request['return_date'] ? date('M d, Y', strtotime($request['return_date'])) : 'N/A' ?></td>
                                        <td class="col-status text-center" data-label="Status">
                                            <span class="badge rounded-pill <?= getStatusColorClass($request['req_status']) ?>">
                                                <?= $request['req_status'] ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        No requests found for the selected filters
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <!-- Inventory Report -->
            <?php if ($report_type === 'inventory'): ?>
                <div class="table-container">
                    <table class="table table-minimalist mb-0">
                        <thead>
                            <tr>
                                <th class="col-req-id hide-id">Resource ID</th>
                                <th class="col-resource">Resource Name</th>
                                <th class="col-purpose">Description</th>
                                <th class="col-quantity">Quantity</th>
                                <th class="col-status">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($inventory)): ?>
                                <?php foreach ($inventory as $item): ?>
                                    <tr>
                                        <td class="col-req-id hide-id" data-label="Resource ID"><?= $item['res_id'] ?></td>
                                        <td class="col-resource" data-label="Name"><?= htmlspecialchars($item['res_name']) ?></td>
                                        <td class="col-purpose" data-label="Description"><?= htmlspecialchars($item['res_description']) ?></td>
                                        <td class="col-quantity text-center" data-label="Quantity"><?= $item['display_quantity'] ?></td>
                                        <td class="col-status text-center" data-label="Status">
                                            <span class="badge rounded-pill <?= getResourceStatusColorClass($item['display_status']) ?>">
                                                <?= $item['display_status'] ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        No inventory items found for the selected filters
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <!-- Returns Report -->
            <?php if ($report_type === 'returns'): ?>
                <div class="table-container">
                    <table class="table table-minimalist mb-0">
                        <thead>
                            <tr>
                                <th class="col-req-id hide-id">Return ID</th>
                                <th class="col-resource">Resource</th>
                                <th class="col-requester">Requester</th>
                                <th class="col-brgy">Barangay</th>
                                <th class="col-quantity">Qty</th>
                                <th class="col-ret-date">Serial Number</th> <!-- New column header -->
                                <th class="col-req-date">Request Date</th>
                                <th class="col-ret-date">Return Date</th>
                                <th class="col-status">Condition</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($returns_html)): ?>
                                <?php foreach ($returns_html as $return_row): ?>
                                    <tr>
                                        <td class="col-req-id hide-id" data-label="Return ID"><?= $return_row['return_id'] ?></td>
                                        <td class="col-resource" data-label="Resource"><?= htmlspecialchars($return_row['res_name']) ?></td>
                                        <td class="col-requester" data-label="Requester"><?= htmlspecialchars($return_row['requester_name']) ?></td>
                                        <td class="col-brgy" data-label="Barangay"><?= htmlspecialchars($return_row['requester_brgy_name']) ?></td>
                                        <td class="col-quantity text-center" data-label="Qty"><?= $return_row['req_quantity'] ?></td>
                                        <td class="col-ret-date" data-label="Serial Number"><?= htmlspecialchars($return_row['serial_number']) ?></td> <!-- Display serial number -->
                                        <td class="col-req-date" data-label="Request Date"><?= date('M d, Y', strtotime($return_row['req_date'])) ?></td>
                                        <td class="col-ret-date" data-label="Return Date"><?= date('M d, Y', strtotime($return_row['return_date'])) ?></td>
                                        <td class="col-status text-center" data-label="Condition">
                                            <span class="badge rounded-pill <?= getConditionColorClass($return_row['display_condition']) ?>">
                                                <?= htmlspecialchars($return_row['display_condition']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        No returned resources found for the selected filters
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        <!-- Footer -->
        <footer class="footer mt-auto py-1 no-print">
            <div class="container-fluid">
                <span>&copy; <?= date('Y') ?> BRSMS. All rights reserved.</span>
            </div>
        </footer>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/report.js"></script>

</body>
</html>
