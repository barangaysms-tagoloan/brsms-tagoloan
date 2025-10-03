<?php
session_start();
require '../logic/database/db.php'; 


if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'superadmin') {
    header("Location: login.php");
    exit();
}



$stmt_users = $conn->prepare("SELECT COUNT(*) as total_users FROM users WHERE role != 'superadmin'");
$stmt_users->execute();
$result_users = $stmt_users->get_result();
$total_users = $result_users->fetch_assoc()['total_users'];
$stmt_users->close();


$stmt_barangays = $conn->prepare("SELECT COUNT(*) as total_barangays FROM barangays");
$stmt_barangays->execute();
$result_barangays = $stmt_barangays->get_result();
$total_barangays = $result_barangays->fetch_assoc()['total_barangays'];
$stmt_barangays->close();


$stmt_activities = $conn->prepare("SELECT COUNT(*) as total_activities FROM activity_logs WHERE DATE(created_at) = CURDATE()");
$stmt_activities->execute();
$result_activities = $stmt_activities->get_result();
$total_activities = $result_activities->fetch_assoc()['total_activities'];
$stmt_activities->close();


$system_alerts = 5; 


$stmt_user_roles = $conn->prepare("SELECT role, COUNT(*) as count FROM users GROUP BY role");
$stmt_user_roles->execute();
$user_roles_result = $stmt_user_roles->get_result();
$user_roles_data = [];
while ($row = $user_roles_result->fetch_assoc()) {
    $user_roles_data[] = $row;
}
$stmt_user_roles->close();


$stmt_activity_trends = $conn->prepare("SELECT DATE(created_at) as activity_date, COUNT(*) as count FROM activity_logs WHERE created_at >= CURDATE() - INTERVAL 7 DAY GROUP BY activity_date ORDER BY activity_date ASC");
$stmt_activity_trends->execute();
$activity_trends_result = $stmt_activity_trends->get_result();
$activity_trends_data = [];
while ($row = $activity_trends_result->fetch_assoc()) {
    $activity_trends_data[] = $row;
}
$stmt_activity_trends->close();


$stmt_system_status = $conn->prepare("SELECT 'Database' as component, 'Operational' as status UNION SELECT 'API Services', 'Operational' UNION SELECT 'Storage', 'Normal'");
$stmt_system_status->execute();
$system_status_result = $stmt_system_status->get_result();
$system_status_data = [];
while ($row = $system_status_result->fetch_assoc()) {
    $system_status_data[] = $row;
}
$stmt_system_status->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Super Admin Dashboard - BRSMS</title>
    <link rel="icon" type="image/png" href="uploads/BRSMS.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-color: #5f2c82;
            --secondary-color: #6f2dbd;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --info-color: #0dcaf0;

            /* Custom Modal Colors based on incoming.php */
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

            /* Minimalist Table Colors (from returning.php) */
            --table-border-color: #e0e0e0;
            --table-header-bg: #f8f8f8;
            --table-header-text: #333;
            --table-row-hover-bg: #f5f5f5;

            /* Super Admin Specific Colors */
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
            min-height: 100vh; /* Ensure body takes full viewport height */
            display: flex;
            flex-direction: column;
        }
        .wrapper {
            display: flex;
            width: 100%;
            flex-grow: 1; /* Allow wrapper to grow and fill available space */
        }
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
            background-color: #f8f9fa; /* Lighter background for main content */
        }
        
        /* NEW: Dashboard header with improved styling */
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
        
        .page-header {
            font-size: 2rem;
            font-weight: normal;
            color: #343a40;
            margin-bottom: 20px;
            display: block;
            text-align: left;
            padding-left: 15px;
        }

        .page-header i {
            margin-right: 10px;
            font-size: 1.5rem;
        }

        /* Super Admin Specific Styles */
        .dashboard-header { /* This is the new dashboard-top-bar, renamed for clarity */
            background: linear-gradient(135deg, var(--violet), var(--secondary-color));
            border-radius: 12px;
            padding: 25px;
            color: white;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(95, 44, 130, 0.2);
        }
        
        .dashboard-header h1 {
            font-size: 2rem;
            font-weight: 600;
            margin: 0;
        }
        
        .dashboard-header p {
            margin: 10px 0 0;
            opacity: 0.9;
            font-size: 1.1rem;
        }

        /* Removed .feature-grid and .feature-card styles as they are no longer needed */

        .row.g-4 {
            --bs-gutter-x: 1.5rem;
            --bs-gutter-y: 1.5rem;
        }

        /* NEW: Stats overview section */
        .stats-grid { /* Renamed from stats-overview for consistency */
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            margin-right: 20px;
            color: white;
        }
        
        .stat-content {
            flex-grow: 1;
        }
        
        .stat-value {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--violet);
            line-height: 1;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 1rem;
            color: var(--text-medium);
            font-weight: 500;
        }

        /* Dashboard Sections */
        .dashboard-section {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 25px;
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--light-violet);
        }
        
        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--violet);
            margin: 0;
        }
        
        .section-action {
            color: var(--violet);
            font-weight: 500;
            text-decoration: none;
        }
        
        /* Charts */
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }
        
        /* System Status */
        .status-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .status-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid var(--border-light);
        }
        
        .status-item:last-child {
            border-bottom: none;
        }
        
        .status-info {
            display: flex;
            align-items: center;
        }
        
        .status-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            background-color: var(--light-violet);
            color: var(--violet);
        }
        
        .status-name {
            font-weight: 500;
            color: var(--text-dark);
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .action-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s, box-shadow 0.2s;
            text-decoration: none;
            color: inherit;
        }
        
        .action-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
            color: inherit;
        }
        
        .action-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 1.5rem;
            color: white;
            background-color: var(--violet);
        }
        
        .action-title {
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 5px;
        }
        
        .action-desc {
            font-size: 0.9rem;
            color: var(--text-medium);
        }

        /* Footer styling */
        .footer {
            padding: 15px;
            text-align: center;
            color: #6c757d;
            font-size: 0.85rem;
            margin-top: auto;
            background-color: #f8f9fa;
            border-top: 1px solid #e9ecef;
            position: sticky; /* Make footer sticky */
            bottom: 0; /* Stick to the bottom */
            width: 100%; /* Ensure it spans full width */
            z-index: 1000; /* Ensure it's above other content if needed */
        }

        /* Toast Notification Styles */
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
            font-size: 1.1rem;
            padding: 1rem 1.25rem;
            border-radius: 0.5rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        .toast-body {
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .toast-body i {
            font-size: 1.5rem;
        }

        /* Responsive adjustments */
        @media (max-width: 992px) {
            .dashboard-header h1 { /* Changed from h2 to h1 */
                font-size: 2rem;
            }
            .dashboard-header p {
                font-size: 1rem;
            }
            
            .stats-grid { /* Changed from stats-overview */
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                min-height: unset;
                padding: 20px;
                position: relative; /* Make sidebar relative on small screens */
            }
            .wrapper {
                flex-direction: column;
            }
            .main-content {
                padding: 15px;
            }
            
            .stats-grid { /* Changed from stats-overview */
                grid-template-columns: 1fr;
            }
            
            .dashboard-header { /* Changed from dashboard-top-bar */
                padding: 15px 20px;
            }
            
            .dashboard-header h1 { /* Changed from h2 to h1 */
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
<div class="wrapper">
    <!-- Sidebar - Consistent with original super_dashboard.php -->
    <div class="col-md-3 col-lg-2 sidebar d-flex flex-column">
        <div class="logo-container">
            <div class="logo-circle">
                <img src="../uploads/BRSMS.png" alt="BRSMS Logo">
            </div>
            <div class="logo-text">BRSMS</div>
        </div>
        <!-- Super Admin specific navigation links -->
        <a href="super_dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="user_management.php"><i class="fas fa-users"></i> User Management</a>
        <a href="activity_logs.php"><i class="fas fa-list-alt"></i> Activity Logs</a>
        <a href="system_settings.php"><i class="fas fa-cog"></i> System Settings</a>
        <!-- Add more super admin specific links here if needed -->
        <div class="mt-auto">
            <a href="../pages/logout.php" class="logout-btn mt-4"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
        </div>
    </div>

    <!-- Main Content - Updated with new layout -->
    <div class="col-md-9 col-lg-10 main-content">
        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <h1> Super Admin Dashboard</h1>
            <p>Welcome back! Here's an overview of your system administration tools.</p>
        </div>

        <!-- Stats Overview -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background-color: var(--primary-color);">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?= $total_users ?></div>
                    <div class="stat-label">Active Users</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background-color: var(--info-color);">
                    <i class="fas fa-home"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?= $total_barangays ?></div>
                    <div class="stat-label">Barangays</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background-color: var(--success-color);">
                    <i class="fas fa-calendar-day"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?= $total_activities ?></div>
                    <div class="stat-label">Today's Activities</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background-color: var(--warning-color);">
                    <i class="fas fa-bell"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value"><?= $system_alerts ?></div>
                    <div class="stat-label">System Alerts</div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Left Column -->
            <div class="col-lg-8">
                <!-- Activity Trends Analytics -->
                <div class="dashboard-section">
                    <div class="section-header">
                        <h3 class="section-title"><i class="fas fa-chart-line me-2"></i> Activity Trends</h3>
                        <a href="activity_logs.php" class="section-action">View All</a>
                    </div>
                    <div class="chart-container">
                        <canvas id="activityTrendsChart"></canvas>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="dashboard-section">
                    <div class="section-header">
                        <h3 class="section-title"><i class="fas fa-bolt me-2"></i> Quick Actions</h3>
                    </div>
                    <div class="quick-actions">
                        <a href="user_management.php?action=add" class="action-card">
                            <div class="action-icon">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <div class="action-title">Add User</div>
                            <div class="action-desc">Create a new user account</div>
                        </a>
                        
                        <a href="system_settings.php" class="action-card">
                            <div class="action-icon">
                                <i class="fas fa-cogs"></i>
                            </div>
                            <div class="action-title">System Settings</div>
                            <div class="action-desc">Configure system preferences</div>
                        </a>
                        
                        <a href="activity_logs.php" class="action-card">
                            <div class="action-icon">
                                <i class="fas fa-history"></i>
                            </div>
                            <div class="action-title">View Logs</div>
                            <div class="action-desc">Check system activity logs</div>
                        </a>
                        
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Right Column -->
            <div class="col-lg-4">
                <!-- User Roles Distribution Analytics -->
                <div class="dashboard-section">
                    <div class="section-header">
                        <h3 class="section-title"><i class="fas fa-chart-pie me-2"></i> User Distribution</h3>
                    </div>
                    <div class="chart-container">
                        <canvas id="userRolesChart"></canvas>
                    </div>
                </div>
                
                <!-- System Status -->
                <div class="dashboard-section">
                    <div class="section-header">
                        <h3 class="section-title"><i class="fas fa-server me-2"></i> System Status</h3>
                    </div>
                    <ul class="status-list">
                        <?php foreach ($system_status_data as $status): ?>
                        <li class="status-item">
                            <div class="status-info">
                                <div class="status-icon">
                                    <i class="fas fa-database"></i>
                                </div>
                                <div class="status-name"><?= $status['component'] ?></div>
                            </div>
                            <span class="status-badge bg-success"><?= $status['status'] ?></span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Footer - Consistent with user_management.php -->
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
<script>
    // Toast Notification Logic (copied and adapted from login.php)
    document.addEventListener('DOMContentLoaded', function() {
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

        // Check for toast messages from PHP session
        <?php if (isset($_SESSION['toast_message'])): ?>
            showToast("<?= htmlspecialchars($_SESSION['toast_message']) ?>", "<?= htmlspecialchars($_SESSION['toast_type']) ?>");
            <?php
            
            unset($_SESSION['toast_message']);
            unset($_SESSION['toast_type']);
            ?>
        <?php endif; ?>
    });

    // Initialize Activity Trends Chart
    const activityCtx = document.getElementById('activityTrendsChart').getContext('2d');
    const activityTrendsChart = new Chart(activityCtx, {
        type: 'line',
        data: {
            labels: [
                <?php 
                
                for ($i = 6; $i >= 0; $i--) {
                    $date = date('M j', strtotime("-$i days"));
                    echo "'$date',";
                }
                ?>
            ],
            datasets: [{
                label: 'Activities',
                data: [
                    <?php
                    
                    $activityCounts = [];
                    foreach ($activity_trends_data as $activity) {
                        $date = date('M j', strtotime($activity['activity_date']));
                        $activityCounts[$date] = $activity['count'];
                    }
                    
                    
                    for ($i = 6; $i >= 0; $i--) {
                        $date = date('M j', strtotime("-$i days"));
                        $count = isset($activityCounts[$date]) ? $activityCounts[$date] : 0;
                        echo "$count,";
                    }
                    ?>
                ],
                backgroundColor: 'rgba(95, 44, 130, 0.1)',
                borderColor: '#5f2c82',
                tension: 0.3,
                fill: true,
                pointBackgroundColor: '#5f2c82',
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        drawBorder: false
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    // Initialize User Roles Chart
    const rolesCtx = document.getElementById('userRolesChart').getContext('2d');
    const userRolesChart = new Chart(rolesCtx, {
        type: 'doughnut',
        data: {
            labels: [
                <?php 
                foreach ($user_roles_data as $role) {
                    echo "'" . ucfirst($role['role']) . "',";
                }
                ?>
            ],
            datasets: [{
                data: [
                    <?php 
                    foreach ($user_roles_data as $role) {
                        echo $role['count'] . ",";
                    }
                    ?>
                ],
                backgroundColor: [
                    '#5f2c82', 
                    '#6f2dbd', 
                    '#a663cc', 
                    '#b298dc', 
                    '#b8d0eb'
                ],
                borderWidth: 0,
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            cutout: '65%'
        }
    });
</script>
</body>
</html>
