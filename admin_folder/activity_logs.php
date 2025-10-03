<?php
session_start();
require '../logic/database/db.php';
require '../logic/logging.php';


if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'superadmin') {
    header("Location: login.php");
    exit();
}


$filter_user = isset($_GET['filter_user']) ? $_GET['filter_user'] : '';
$filter_action = isset($_GET['filter_action']) ? $_GET['filter_action'] : '';
$filter_date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$filter_date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';


$where_conditions = [];
$params = [];
$types = '';

if (!empty($filter_user)) {
    $where_conditions[] = "u.username LIKE ?";
    $params[] = "%$filter_user%";
    $types .= 's';
}

if (!empty($filter_action)) {
    $where_conditions[] = "al.action LIKE ?";
    $params[] = "%$filter_action%";
    $types .= 's';
}

if (!empty($filter_date_from)) {
    $where_conditions[] = "DATE(al.created_at) >= ?";
    $params[] = $filter_date_from;
    $types .= 's';
}

if (!empty($filter_date_to)) {
    $where_conditions[] = "DATE(al.created_at) <= ?";
    $params[] = $filter_date_to;
    $types .= 's';
}

$where_sql = '';
if (!empty($where_conditions)) {
    $where_sql = 'WHERE ' . implode(' AND ', $where_conditions);
}


$count_sql = "SELECT COUNT(*) as total FROM activity_logs al 
              LEFT JOIN users u ON al.user_id = u.user_id $where_sql";
$count_stmt = $conn->prepare($count_sql);

if (!empty($params)) {
    $count_stmt->bind_param($types, ...$params);
}

$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_logs = $count_result->fetch_assoc()['total'];


$sql = "SELECT al.*, u.username, u.user_full_name, u.role, b.brgy_name 
        FROM activity_logs al 
        LEFT JOIN users u ON al.user_id = u.user_id 
        LEFT JOIN barangays b ON u.brgy_id = b.brgy_id 
        $where_sql 
        ORDER BY al.created_at DESC";

$stmt = $conn->prepare($sql);


if (!empty($params)) {
    $main_query_types = '';
    foreach ($params as $param) {
        if (is_int($param)) {
            $main_query_types .= 'i';
        } elseif (is_float($param)) {
            $main_query_types .= 'd';
        } else {
            $main_query_types .= 's';
        }
    }
    $stmt->bind_param($main_query_types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
$logs = $result->fetch_all(MYSQLI_ASSOC);


$actions_sql = "SELECT DISTINCT action FROM activity_logs ORDER BY action";
$actions_result = $conn->query($actions_sql);
$actions = $actions_result->fetch_all(MYSQLI_ASSOC);


$users_sql = "SELECT DISTINCT u.user_id, u.username, u.user_full_name 
              FROM activity_logs al 
              JOIN users u ON al.user_id = u.user_id 
              ORDER BY u.user_full_name";
$users_result = $conn->query($users_sql);
$users = $users_result->fetch_all(MYSQLI_ASSOC);


$js_success_message = '';
$js_error_message = '';

if (isset($_SESSION['success'])) {
    $js_success_message = $_SESSION['success'];
    unset($_SESSION['success']);
}

if (isset($_SESSION['error'])) {
    $js_error_message = $_SESSION['error'];
    unset($_SESSION['error']);
}





?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Activity Logs - BRSMS</title>
    <!-- Favicon (browser tab logo) -->
    <link rel="icon" type="image/png" href="../uploads/BRSMS.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        :root {
            --primary-color: #5f2c82;
            --secondary-color: #6f2dbd;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --info-color: #0dcaf0;
            --modal-success-bg: #28a745;
            --modal-success-text: white;
            --modal-error-bg: #dc3545;
            --modal-error-text: white;
            --modal-warning-bg: #ffc107;
            --modal-warning-text: #000;
            --modal-info-bg: #0dcaf0;
            --modal-info-text: #212529;
            --modal-borrowed-bg: #fd7e14;
            --modal-borrowed-text: white;
            --table-border-color: #e0e0e0;
            --table-header-bg: #f8f8f8;
            --table-header-text: #333;
            --table-row-hover-bg: #f5f5f5;
            --violet: #5f2c82;
            --light-violet: rgba(138, 43, 226, 0.1);
            --dark-violet: #5f2c82;
            --card-bg: #ffffff;
            --card-shadow: rgba(0, 0, 0, 0.08);
            --card-hover-shadow: rgba(0, 0, 0, 0.15);
            --text-dark: #333;
            --text-medium: #666;
            --border-light: #e0e0e0;
        }

        body {
            overflow-x: hidden;
            background-color: #F0F8FF;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .wrapper {
            display: flex;
            width: 100%;
            flex-grow: 1;
        }

        /* Sidebar */
        .sidebar {
            background-color: var(--primary-color); /* Use primary color for sidebar */
            min-height: 100vh; /* Changed from 100vh to 100% to prevent stretching beyond content */
            padding: 30px 20px;
            color: white;
            display: flex; /* Use flexbox for sidebar content */
            flex-direction: column; /* Stack items vertically */
            position: sticky; /* Make sidebar sticky */
            top: 0; /* Stick to the top */
            align-self: flex-start; /* Align to the start of the flex container */
        }
        .sidebar .logo-container {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 40px;
        }
        .sidebar .logo-circle {
            background-color: white;
            border-radius: 50%;
            padding: 5px;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .sidebar .logo-circle img {
            width: 40px;
            height: 40px;
            object-fit: contain;
        }
        .sidebar .logo-text {
            font-size: 22px;
            font-weight: bold;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            padding: 15px 18px;
            display: block;
            border-radius: 8px;
            font-size: 18px;
            margin-bottom: 12px;
            transition: background 0.2s ease;
            font-weight: normal; /* Ensure sidebar text is not bold */
        }
        .sidebar a i {
            margin-right: 12px;
            width: 24px; /* Fixed width for consistent icon spacing */
            text-align: center; /* Center icons within their fixed width */
        }
        .sidebar a:hover,
        .sidebar a.active {
            background-color: rgba(255, 255, 255, 0.15);
        }
        .logout-btn {
            background-color: #e74c3c;
            color: white;
            padding: 14px 18px;
            display: block;
            width: 100%;
            text-align: left;
            border: none;
            border-radius: 8px;
            font-size: 18px;
        }
        .logout-btn:hover {
            background-color: #c0392b;
        }

        .main-content {
            flex-grow: 1;
            padding: 20px;
            display: flex;
            flex-direction: column;
            background-color: #f8f9fa;
        }
        
        /* Dashboard Header */
        .dashboard-top-bar {
            background: linear-gradient(135deg, var(--violet), var(--secondary-color));
            border-radius: 12px;
            padding: 20px 25px;
            color: white;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(95, 44, 130, 0.2);
        }
        
        .dashboard-top-bar h2 {
            font-size: 1.8rem;
            font-weight: 600;
            margin: 0;
        }
        
        .dashboard-top-bar p {
            margin: 5px 0 0;
            opacity: 0.9;
            font-size: 1rem;
        }

        /* Filter Section */
        .filter-section {
            background-color: white;
            padding: 15px 20px; /* Reduced padding */
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .filter-section h5 {
            color: var(--violet);
            margin-bottom: 15px;
            font-weight: 600;
            font-size: 1.1rem; /* Slightly smaller heading */
        }

        .filter-section .form-label {
            font-size: 0.85rem; /* Smaller label font */
            margin-bottom: 5px;
        }

        .filter-section .form-control,
        .filter-section .form-select {
            font-size: 0.9rem; /* Smaller input/select font */
            padding: 0.375rem 0.75rem; /* Compact padding */
            height: calc(1.5em + 0.75rem + 2px); /* Adjust height */
        }

        /* Logs Table */
        .logs-table-container {
            background-color: white;
            padding: 20px; /* Reduced padding */
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            flex-grow: 1;
        }

        .logs-table-container h5 {
            font-size: 1.1rem; /* Smaller heading */
        }

        .table th {
            background-color: var(--table-header-bg);
            color: var(--table-header-text);
            font-weight: 600;
            border-top: none;
            padding: 6px 8px; /* Further reduced padding */
            font-size: 0.85rem; /* Slightly smaller font */
        }

        .table td {
            padding: 6px 8px; /* Further reduced padding */
            vertical-align: middle;
            font-size: 0.8rem; /* Smaller font for table data */
        }

        .table-hover tbody tr:hover {
            background-color: var(--table-row-hover-bg);
        }

        .action-text {
            font-weight: bold;
        }

        /* Toast Notification */
        .toast-container {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1100;
            width: 100%;
            max-width: 400px;
            display: flex;
            justify-content: center;
        }

        .toast {
            font-size: 1rem; /* Slightly smaller toast font */
            padding: 0.75rem 1rem; /* Compact toast padding */
            border-radius: 0.5rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        .toast-body {
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px; /* Reduced gap */
        }

        .toast-body i {
            font-size: 1.2rem; /* Slightly smaller icon */
        }

        /* Footer */
        .footer {
            padding: 15px;
            text-align: center;
            color: #6c757d;
            font-size: 0.85rem;
            margin-top: auto;
            background-color: #f8f9fa;
            border-top: 1px solid #e9ecef;
            position: sticky;
            bottom: 0;
            width: 100%;
            z-index: 1000;
        }

        /* Table cell spacing adjustments */
        .table-responsive {
            overflow-x: auto;
        }
        
        .table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }
        
        .table th, .table td {
            white-space: nowrap;
        }
        
        .table th:nth-child(1), 
        .table td:nth-child(1) {
            width: 15%;
        }
        
        .table th:nth-child(2), 
        .table td:nth-child(2) {
            width: 20%;
        }
        
        .table th:nth-child(3), 
        .table td:nth-child(3) {
            width: 15%;
        }
        
        .table th:nth-child(4), 
        .table td:nth-child(4) {
            width: 35%;
            white-space: normal; /* Allow details to wrap */
        }
        
        .table th:nth-child(5), 
        .table td:nth-child(5) {
            width: 15%;
        }

        /* Specific adjustments for user column text */
        .table td small {
            font-size: 0.7rem; /* Even smaller font for user details */
        }

        /* NEW CSS FOR SCROLLABLE TABLE (Adjusted for full height) */
        .scrollable-table-body {
            max-height: 70vh; /* Set a percentage of viewport height for a larger scrollable area */
            overflow-y: auto;
            border: 1px solid var(--table-border-color); /* Add a border for visual separation */
            border-radius: 8px; /* Match container border-radius */
        }

        .scrollable-table-body table {
            margin-bottom: 0; /* Remove default table margin */
        }

        .scrollable-table-body thead th {
            position: sticky;
            top: 0;
            background-color: var(--table-header-bg);
            z-index: 10; /* Ensure header stays on top when scrolling */
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                min-height: unset;
                padding: 20px;
                position: relative;
            }
            .wrapper {
                flex-direction: column;
            }
            .main-content {
                padding: 15px;
            }
            .dashboard-top-bar {
                padding: 15px 20px;
            }
            .dashboard-top-bar h2 {
                font-size: 1.5rem;
            }
            
            .table th, .table td {
                padding: 4px 6px; /* Further reduced padding for small screens */
                font-size: 0.75rem; /* Smaller font for small screens */
            }
            .table td small {
                font-size: 0.65rem; /* Even smaller font for user details on small screens */
            }
            .scrollable-table-body {
                max-height: 60vh; /* Adjust for smaller screens if needed */
            }
            .filter-section .form-control,
            .filter-section .form-select {
                font-size: 0.8rem; /* Even smaller input/select font on small screens */
                padding: 0.25rem 0.5rem; /* More compact padding */
                height: calc(1.5em + 0.5rem + 2px); /* Adjust height */
            }
            .filter-section .form-label {
                font-size: 0.75rem;
            }
            .filter-section h5 {
                font-size: 1rem;
            }
        }
    </style>
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
            <!-- Super Admin specific navigation links -->
            <a href="super_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="user_management.php"><i class="fas fa-users"></i> User Management</a>
            <a href="activity_logs.php" class="active"><i class="fas fa-list-alt"></i> Activity Logs</a>
            <a href="system_settings.php"><i class="fas fa-cog"></i> System Settings</a>
            <div class="mt-auto">
                <a href="../pages/logout.php" class="logout-btn mt-4"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 col-lg-10 main-content">
            <!-- Dashboard Header -->
            <div class="dashboard-top-bar">
                <h2>Activity Logs</h2>
                <p>View a record of all significant actions performed within the system.</p>
            </div>

            <!-- Hidden inputs for PHP messages -->
            <input type="hidden" id="php_success_message" value="<?= htmlspecialchars($js_success_message) ?>">
            <input type="hidden" id="php_error_message" value="<?= htmlspecialchars($js_error_message) ?>">

            <!-- Filter Section -->
            <div class="filter-section">
                <h5><i class="fas fa-filter me-2"></i> Filter Logs</h5>
                <form method="GET" action="">
                    <div class="row g-2"> <!-- Use g-2 for smaller gutter -->
                        <div class="col-md-3 col-sm-6"> <!-- Adjusted column sizes for better responsiveness -->
                            <label for="filter_user" class="form-label">User</label>
                            <select class="form-select form-select-sm" id="filter_user" name="filter_user"> <!-- Added form-select-sm -->
                                <option value="">All Users</option>
                                <?php foreach ($users as $user): ?>
                                    <option value="<?= htmlspecialchars($user['username']) ?>" <?= $filter_user === $user['username'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($user['user_full_name']) ?> (<?= htmlspecialchars($user['username']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 col-sm-6"> <!-- Adjusted column sizes -->
                            <label for="filter_action" class="form-label">Action</label>
                            <select class="form-select form-select-sm" id="filter_action" name="filter_action"> <!-- Added form-select-sm -->
                                <option value="">All Actions</option>
                                <?php foreach ($actions as $action): ?>
                                    <option value="<?= htmlspecialchars($action['action']) ?>" <?= $filter_action === $action['action'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($action['action']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 col-sm-6"> <!-- Adjusted column sizes -->
                            <label for="date_from" class="form-label">Date From</label>
                            <input type="date" class="form-control form-control-sm" id="date_from" name="date_from" value="<?= htmlspecialchars($filter_date_from) ?>"> <!-- Added form-control-sm -->
                        </div>
                        <div class="col-md-3 col-sm-6"> <!-- Adjusted column sizes -->
                            <label for="date_to" class="form-label">Date To</label>
                            <input type="date" class="form-control form-control-sm" id="date_to" name="date_to" value="<?= htmlspecialchars($filter_date_to) ?>"> <!-- Added form-control-sm -->
                        </div>
                    </div>
                    <div class="row mt-3"> <!-- Increased margin-top for buttons -->
                        <div class="col-md-12 d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary btn-sm me-2"><i class="fas fa-filter me-1"></i> Apply Filters</button> <!-- Added btn-sm -->
                            <a href="activity_logs.php" class="btn btn-outline-secondary btn-sm"><i class="fas fa-times me-1"></i> Clear Filters</a> <!-- Added btn-sm -->
                        </div>
                    </div>
                </form>
            </div>

            <!-- Logs Table -->
            <div class="logs-table-container">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5>System Activity Logs</h5>
                    <span class="badge bg-primary">Total: <?= $total_logs ?> logs</span>
                </div>

                <?php if (count($logs) > 0): ?>
                    <div class="table-responsive scrollable-table-body">
                        <table class="table table-hover table-sm"> <!-- Added table-sm for compact table -->
                            <thead>
                                <tr>
                                    <th>Date & Time</th>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Activity Details</th>
                                    <th>IP Address</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($logs as $log): ?>
                                    <tr>
                                        <td><?= date('M j, Y h:i A', strtotime($log['created_at'])) ?></td>
                                        <td>
                                            <?php if ($log['user_full_name']): ?>
                                                <div class="fw-bold"><?= htmlspecialchars($log['user_full_name']) ?></div>
                                                <small class="text-muted"><?= htmlspecialchars($log['username']) ?></small><br>
                                                <small class="text-muted">(<?= htmlspecialchars($log['role']) ?>)</small>
                                                <?php if ($log['brgy_name']): ?>
                                                    <br><small class="text-muted"><?= htmlspecialchars($log['brgy_name']) ?></small>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-muted">System</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="action-text"><?= htmlspecialchars($log['action']) ?></span>
                                        </td>
                                        <td><?= htmlspecialchars($log['details'] ?? 'No details') ?></td>
                                        <td><?= htmlspecialchars($log['ip_address']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-history fa-5x text-muted mb-4"></i>
                        <h4 class="text-muted">No activity logs found</h4>
                        <p class="text-muted">No activity has been recorded yet or no logs match your filters.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Footer -->
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
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize date pickers
            flatpickr("#date_from", {
                dateFormat: "Y-m-d",
                allowInput: true
            });
            
            flatpickr("#date_to", {
                dateFormat: "Y-m-d",
                allowInput: true
            });

            // --- Toast Notification ---
            const actionToast = new bootstrap.Toast(document.getElementById('actionToast'), {
                delay: 3000
            });

            function showToast(message, type = 'success') {
                const toastBody = document.getElementById('toastMessage');
                let iconClass = '';
                let bgColorClass = '';

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
                    iconClass = 'fas fa-info-circle';
                    bgColorClass = 'text-bg-secondary';
                }

                toastBody.innerHTML = `<i class="${iconClass}"></i> <span>${message}</span>`;
                const toastEl = document.getElementById('actionToast');
                toastEl.classList.remove('text-bg-success', 'text-bg-danger', 'text-bg-warning', 'text-bg-info', 'text-bg-primary', 'text-bg-secondary');
                toastEl.classList.add(bgColorClass);
                actionToast.show();
            }

            // Display PHP messages on page load using the toast
            const phpSuccessMessage = document.getElementById('php_success_message').value;
            const phpErrorMessage = document.getElementById('php_error_message').value;

            if (phpSuccessMessage) {
                showToast(phpSuccessMessage, 'success');
            } else if (phpErrorMessage) {
                showToast(phpErrorMessage, 'error');
            }
        });
    </script>
</body>
</html>
