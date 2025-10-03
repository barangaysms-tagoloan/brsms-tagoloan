<?php
session_start();
require '../logic/database/db.php'; // Ensure this path is correct for your new domain structure

// --- IMPORTANT: Set the default timezone for PHP ---
// Replace 'Asia/Manila' with your actual timezone if different.
// A list of supported timezones can be found here: https://www.php.net/manual/en/timezones.php
date_default_timezone_set('Asia/Manila'); 

// Get item ID from URL, sanitize and validate
$item_id = isset($_GET['item_id']) ? (int)$_GET['item_id'] : 0;

if ($item_id === 0) {
    die("Invalid item ID provided.");
}

// --- Fetch Item Details ---
$query = "SELECT ri.item_id, ri.item_status, ri.qr_code, ri.serial_number, ri.current_req_id,
                 r.res_id, r.res_name, r.res_description, r.res_photo, r.is_bulk,
                 b.brgy_name
          FROM resource_items ri
          JOIN resources r ON ri.res_id = r.res_id
          JOIN barangays b ON r.brgy_id = b.brgy_id
          WHERE ri.item_id = ?";
          
$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
}
$stmt->bind_param("i", $item_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Item not found.");
}

$item = $result->fetch_assoc();
$stmt->close();

// --- Fetch Current Borrower Information (if item is borrowed) ---
$current_borrower = null;
if ($item['item_status'] === 'Borrowed' && $item['current_req_id']) {
    $borrower_query = "SELECT 
                        u.user_full_name, 
                        b.brgy_name as borrower_brgy,
                        req.return_date,
                        req.req_date,
                        req.borrow_timestamp,
                        req.return_timestamp
                      FROM requests req
                      JOIN users u ON req.req_user_id = u.user_id
                      JOIN barangays b ON req.req_brgy_id = b.brgy_id
                      WHERE req.req_id = ?";
    
    $stmt = $conn->prepare($borrower_query);
    if (!$stmt) {
        die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
    }
    $stmt->bind_param("i", $item['current_req_id']);
    $stmt->execute();
    $borrower_result = $stmt->get_result();
    
    if ($borrower_result->num_rows > 0) {
        $current_borrower = $borrower_result->fetch_assoc();
    }
    $stmt->close();
}

// --- Fetch Borrowing History for the specific resource item ---
// Modified to fetch individual item details for bulk resources
$history_query = "SELECT 
                    u.user_full_name, 
                    b.brgy_name as borrower_brgy,
                    req.req_date,
                    req.return_date,
                    req.borrow_timestamp, -- Added borrow_timestamp
                    req.return_timestamp, -- Added return_timestamp
                    ret.return_date as actual_return_date,
                    ret.return_condition,
                    ri.serial_number,
                    ri.item_id,
                    r.is_bulk
                 FROM requests req
                 JOIN users u ON req.req_user_id = u.user_id
                 JOIN barangays b ON req.req_brgy_id = b.brgy_id
                 JOIN resources r ON req.res_id = r.res_id
                 LEFT JOIN returns ret ON req.req_id = ret.req_id
                 LEFT JOIN resource_items ri ON req.req_id = ri.current_req_id AND ri.res_id = req.res_id
                 WHERE req.res_id = ? AND req.req_status = 'Completed'
                 ORDER BY req.req_date DESC";
                 
$stmt = $conn->prepare($history_query);
if (!$stmt) {
    die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
}
$stmt->bind_param("i", $item['res_id']); // Use res_id to get history for the resource type
$stmt->execute();
$history_result = $stmt->get_result();
$borrowing_history = [];
$lost_by_borrower = null; // Variable to store info of the borrower who lost the item

while ($row = $history_result->fetch_assoc()) {
    // If it's a bulk resource and return_condition is a JSON string
    if ($row['is_bulk'] && $row['return_condition'] && json_decode($row['return_condition'], true) !== null) {
        $item_conditions = json_decode($row['return_condition'], true);
        foreach ($item_conditions as $item_cond) {
            // Create a new history entry for each individual item within the bulk return
            $new_entry = $row;
            $new_entry['serial_number'] = $item_cond['serial_number'] ?? 'N/A'; // Use individual serial number
            $new_entry['return_condition'] = $item_cond['condition'] ?? 'N/A'; // Use individual condition
            $borrowing_history[] = $new_entry;

            // Check if this specific item was lost
            if (strtolower($item_cond['condition']) === 'lost' && (int)$item_cond['item_id'] === $item_id) {
                $lost_by_borrower = [
                    'name' => $row['user_full_name'],
                    'brgy' => $row['borrower_brgy'],
                    'date' => $row['actual_return_date'] // This should contain date and time if available in DB
                ];
            }
        }
    } else {
        // For single items or bulk items where condition is not JSON (e.g., not yet returned or old data)
        $borrowing_history[] = $row;
        // Check if this single item was lost
        if (strtolower($row['return_condition']) === 'lost' && (int)$row['item_id'] === $item_id) {
             $lost_by_borrower = [
                'name' => $row['user_full_name'],
                'brgy' => $row['borrower_brgy'],
                'date' => $row['actual_return_date'] // This should contain date and time if available in DB
            ];
        }
    }
}
$stmt->close();

// --- Helper function for condition colors (consistent with returning.php) ---
function getConditionColorClass($condition) {
    $condition_lower = strtolower($condition);
    if (strpos($condition_lower, 'good') !== false || strpos($condition_lower, 'excellent') !== false) {
        return 'bg-success'; // Green for good
    } elseif (strpos($condition_lower, 'scratch') !== false || strpos($condition_lower, 'minor') !== false || strpos($condition_lower, 'damaged') !== false) {
        return 'bg-warning text-dark'; // Yellow for minor/damaged
    } elseif (strpos($condition_lower, 'lost') !== false || strpos($condition_lower, 'broken') !== false) {
        return 'bg-danger'; // Red for lost/broken
    } else {
        return 'bg-secondary'; // Grey for other/bad
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?= htmlspecialchars($item['res_name']) ?> - BRSMS Item Details</title>
    <link rel="icon" type="image/png" href="https://brsms-tagoloan.com/uploads/BRSMS.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            /* Official BRSMS Purple Gradient */
            --brsms-purple-start: #8A2BE2; /* Blue Violet */
            --brsms-purple-end: #4B0082;   /* Indigo */
            --main-purple: #5f2c82;
            --light-bg: #f8f9fa;
        }
        
        body {
            background-color: var(--light-bg); 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--primary-color);
            padding-bottom: 70px; /* Space for the fixed action button */
            position: relative;
        }
        
        /* Sticky Header */
        .header-section {
            background: linear-gradient(90deg, var(--brsms-purple-start), var(--brsms-purple-end));
            color: white;
            padding: 10px 0; /* Reduced padding */
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            position: sticky;
            top: 0;
            z-index: 1020;
        }

        .header-section h1 {
            font-size: 1.3rem; /* Reduced font size */
            font-weight: 700;
        }
        
        .header-section p {
            font-size: 0.75rem; /* Reduced font size */
            opacity: 0.9;
        }

        /* Main Item Card (Full-width on mobile) */
        .card-main {
            border-radius: 15px; /* Slightly smaller border-radius */
            box-shadow: 0 8px 30px rgba(0,0,0,0.1); /* Slightly smaller shadow */
            border: none;
            overflow: hidden;
            margin-top: 15px; /* Reduced margin-top */
            background-color: #ffffff;
        }

        /* Image Styling */
        .item-image-container {
            height: 250px; /* Reduced height for mobile */
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #ffffff;
            border-bottom: 1px solid #e9ecef;
            position: relative;
        }
        
        .item-image {
            width: 100%;
            height: 100%;
            object-fit: cover; /* Ensure image covers the area */
        }
        
        .item-icon-placeholder {
            font-size: 3.5rem; /* Reduced icon size */
            color: #adb5bd;
        }
        
        /* Details Section */
        .detail-section {
            padding: 20px; /* Reduced padding */
        }
        
        .detail-section h2 {
            font-size: 1.8rem; /* Reduced font size */
            font-weight: 800;
            color: var(--main-purple);
            margin-bottom: 3px; /* Reduced margin */
        }
        
        .detail-section .item-name-subtitle {
             font-size: 0.9rem; /* Reduced font size */
             color: #6c757d;
             margin-bottom: 20px; /* Reduced margin */
        }

        /* Status Block - Distinct and Color-Coded */
        .status-block {
            padding: 12px; /* Reduced padding */
            border-radius: 12px; /* Slightly smaller border-radius */
            margin-bottom: 20px; /* Reduced margin */
            text-align: center;
            font-weight: 700;
            box-shadow: 0 1px 8px rgba(0,0,0,0.1); /* Slightly smaller shadow */
        }

        .status-block.bg-success { background-color: var(--success-color); color: white; }
        .status-block.bg-warning { background-color: var(--warning-color); color: var(--primary-color); }
        .status-block.bg-danger { background-color: var(--danger-color); color: white; }
        
        .status-block-label {
            display: block;
            font-size: 0.75rem; /* Reduced font size */
            opacity: 0.8;
            margin-bottom: 3px; /* Reduced margin */
        }

        .status-block-value {
            font-size: 1.3rem; /* Reduced font size */
            line-height: 1;
        }
        
        /* Information Grid Layout */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr; /* Single column on mobile */
            gap: 10px; /* Reduced gap */
            margin-top: 5px; /* Reduced margin */
        }

        .info-item {
            padding: 12px; /* Reduced padding */
            border: 1px solid #e9ecef;
            border-radius: 8px; /* Slightly smaller border-radius */
            background-color: var(--light-bg);
        }

        .info-label {
            font-weight: 500;
            color: #6c757d;
            margin-bottom: 3px; /* Reduced margin */
            font-size: 0.75rem; /* Reduced font size */
            display: block;
        }
        
        .info-value {
            font-size: 0.95rem; /* Reduced font size */
            font-weight: 600;
            color: var(--primary-color);
        }
        
        /* Fixed Footer/Action Button Area (Mobile Only) */
        .fixed-action-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            background-color: white;
            box-shadow: 0 -4px 10px rgba(0,0,0,0.1);
            padding: 8px 12px; /* Reduced padding */
            z-index: 1010;
        }

        .action-btn {
            border-radius: 40px; /* Slightly smaller border-radius */
            padding: 10px 20px; /* Reduced padding */
            font-weight: 700;
            font-size: 0.95rem; /* Reduced font size */
            background-color: var(--main-purple);
            border-color: var(--main-purple);
            transition: background-color 0.3s, transform 0.2s;
        }

        /* Current Borrower Info - Visual Alert */
        .borrower-info {
            background-color: #fff3cd; /* Light warning yellow */
            border-left: 5px solid var(--warning-color);
            border-radius: 8px; /* Slightly smaller border-radius */
            padding: 15px; /* Reduced padding */
            margin-top: 20px; /* Reduced margin */
            box-shadow: 0 3px 8px rgba(0,0,0,0.1); /* Slightly smaller shadow */
        }

        .borrower-info h5 {
            font-size: 1.1rem; /* Reduced font size */
            font-weight: 700;
            color: var(--warning-color);
            margin-bottom: 10px; /* Reduced margin */
        }
        
        .borrower-info .info-value {
            font-weight: 600;
            color: var(--primary-color);
            font-size: 0.9rem; /* Reduced font size */
        }

        /* Lost Item Info - Distinct Alert */
        .lost-item-info {
            background-color: #f8d7da; /* Light red for danger */
            border-left: 5px solid var(--danger-color);
            border-radius: 8px; /* Slightly smaller border-radius */
            padding: 15px; /* Reduced padding */
            margin-top: 20px; /* Reduced margin */
            box-shadow: 0 3px 8px rgba(0,0,0,0.1); /* Slightly smaller shadow */
        }

        .lost-item-info h5 {
            font-size: 1.1rem; /* Reduced font size */
            font-weight: 700;
            color: var(--danger-color);
            margin-bottom: 10px; /* Reduced margin */
        }
        
        .lost-item-info .info-value {
            font-weight: 600;
            color: var(--primary-color);
            font-size: 0.9rem; /* Reduced font size */
        }

        /* Responsive table for modal - CARD-LIKE VIEW (Improved) */
        .modal-history-table {
            border-collapse: separate;
            border-spacing: 0 8px; /* Reduced spacing */
            width: 100%;
        }
        .modal-history-table thead {
            display: none;
        }
        .modal-history-table tr {
            margin-bottom: 10px; /* Reduced margin */
            display: block;
            border: 1px solid #e9ecef;
            border-radius: 10px; /* Slightly smaller border-radius */
            overflow: hidden;
            box-shadow: 0 3px 6px rgba(0,0,0,0.07); /* Slightly smaller shadow */
            background-color: white;
        }
        .modal-history-table td {
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            padding: 10px 12px; /* Reduced padding */
            border-bottom: 1px solid #f0f2f5;
            font-size: 0.85rem; /* Reduced font size */
        }
        .modal-history-table td:last-child {
            border-bottom: none;
        }
        .modal-history-table td::before {
            content: attr(data-label);
            font-weight: 700;
            color: var(--main-purple);
            flex-shrink: 0;
            margin-right: 10px; /* Reduced margin */
        }
        
        /* Desktop/Tablet View Adjustments */
        @media (min-width: 768px) {
            body {
                padding-bottom: 0; /* Remove padding when fixed footer is hidden */
            }
            .fixed-action-bar {
                display: none; /* Hide fixed footer on desktop */
            }
            .header-section {
                position: static;
                border-radius: 0 0 30px 30px;
                padding: 15px 0; /* Restore desktop padding */
            }
            .header-section h1 {
                font-size: 1.5rem; /* Restore desktop font size */
            }
            .header-section p {
                font-size: 0.85rem; /* Restore desktop font size */
            }
            .item-image-container {
                border-radius: 15px;
                height: 400px; /* Restore desktop height */
                border: 1px solid #e9ecef;
            }
            .detail-section {
                padding: 0 0 0 30px; /* Restore desktop padding */
            }
            .card-main {
                padding: 40px; /* Restore desktop padding */
                margin-top: 20px; /* Restore desktop margin */
            }
            .info-grid {
                grid-template-columns: repeat(2, 1fr); /* Two columns on desktop */
                gap: 15px; /* Restore desktop gap */
                margin-top: 10px; /* Restore desktop margin */
            }
            .info-item {
                padding: 15px; /* Restore desktop padding */
            }
            .info-label {
                font-size: 0.8rem; /* Restore desktop font size */
            }
            .info-value {
                font-size: 1rem; /* Restore desktop font size */
            }
            .action-btn-desktop {
                display: block !important;
            }
            .modal-history-table thead {
                display: table-header-group;
            }
            .modal-history-table tr {
                display: table-row;
                margin-bottom: 0;
                border: none;
                box-shadow: none;
            }
            .modal-history-table td {
                display: table-cell;
                border-bottom: 1px solid #e9ecef;
                font-size: 1rem; /* Restore desktop font size */
                padding: 10px;
            }
            .modal-history-table td::before {
                content: none;
            }
            .borrower-info, .lost-item-info {
                padding: 20px; /* Restore desktop padding */
                margin-top: 30px; /* Restore desktop margin */
            }
            .borrower-info h5, .lost-item-info h5 {
                font-size: 1.2rem; /* Restore desktop font size */
            }
            .borrower-info .info-value, .lost-item-info .info-value {
                font-size: 1rem; /* Restore desktop font size */
            }
        }
    </style>
</head>
<body>
    <div class="header-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-12 text-center">
                    <h1 class="mb-0"><i class="fas fa-barcode me-2"></i>BRSMS Inventory</h1>
                    <p class="mb-0">Item Details & Tracking</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="container py-3"> <!-- Reduced vertical padding -->
        <div class="row justify-content-center">
            <div class="col-12 col-lg-10">
                <div class="card card-main p-0">
                    <div class="row g-0">
                        
                        <div class="col-md-5">
                            <div class="item-image-container">
                                <?php if (!empty($item['res_photo'])): ?>
                                    <!-- CORRECTED IMAGE PATH HERE -->
                                    <img src="https://brsms-tagoloan.com/logic/inventory/uploads/<?= htmlspecialchars($item['res_photo']) ?>" 
                                         class="item-image" 
                                         alt="<?= htmlspecialchars($item['res_name']) ?>">
                                <?php else: ?>
                                    <div class="text-center">
                                        <i class="fas fa-box-open item-icon-placeholder"></i>
                                        <p class="text-muted mt-2">No asset photo available</p> <!-- Reduced margin -->
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="col-md-7">
                            <div class="detail-section">
                                <h2 class="mb-0"><?= htmlspecialchars($item['res_name']) ?></h2>
                                <p class="item-name-subtitle"><i class="fas fa-tag me-1"></i> Resource Name</p>
                                
                                <?php
                                $status_class = '';
                                $status_icon = '';
                                switch ($item['item_status']) {
                                    case 'Available':
                                        $status_class = 'bg-success';
                                        $status_icon = 'fa-check-circle';
                                        break;
                                    case 'Borrowed':
                                        $status_class = 'bg-warning text-dark';
                                        $status_icon = 'fa-user-tag';
                                        break;
                                    case 'Under Maintenance':
                                        $status_class = 'bg-danger';
                                        $status_icon = 'fa-tools';
                                        break;
                                    case 'Lost': // Added 'Lost' status
                                        $status_class = 'bg-danger';
                                        $status_icon = 'fa-times-circle';
                                        break;
                                    default:
                                        $status_class = 'bg-secondary';
                                        $status_icon = 'fa-question-circle';
                                }
                                ?>
                                <div class="status-block <?= $status_class ?>">
                                    <span class="status-block-label">ITEM STATUS</span>
                                    <div class="status-block-value">
                                        <i class="fas <?= $status_icon ?> me-2"></i>
                                        <?= strtoupper($item['item_status']) ?>
                                    </div>
                                </div>

                                <div class="info-grid">
                                    <div class="info-item">
                                        <span class="info-label"><i class="fas fa-fingerprint me-1"></i> Serial Number</span>
                                        <span class="info-value"><?= htmlspecialchars($item['serial_number']) ?></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label"><i class="fas fa-map-marker-alt me-1"></i>Owner Barangay</span>
                                        <span class="info-value"><?= htmlspecialchars($item['brgy_name']) ?></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label"><i class="fas fa-clipboard-list me-1"></i> Description </span>
                                        <span class="info-value"><?= htmlspecialchars($item['res_description']) ?></span>
                                    </div>
                                </div>
                                
                                <?php if ($item['item_status'] === 'Borrowed' && $current_borrower): ?>
                                <div class="borrower-info">
                                    <h5 class="mb-2"><i class="fas fa-exclamation-circle me-2"></i>CURRENT BORROWER</h5> <!-- Reduced margin -->
                                    
                                    <div class="row">
                                        <div class="col-12 mb-1"> <!-- Reduced margin -->
                                            <span class="info-label">Borrower</span>
                                            <div class="info-value text-primary">
                                                <i class="fas fa-user me-2"></i>
                                                <?= htmlspecialchars($current_borrower['user_full_name']) ?> (<?= htmlspecialchars($current_borrower['borrower_brgy']) ?>)
                                            </div>
                                        </div>
                                        <div class="col-sm-6 mb-1"> <!-- Reduced margin -->
                                            <span class="info-label">Borrowed Date</span>
                                            <div class="info-value text-success">
                                                <i class="fas fa-calendar-day me-2"></i>
                                                <?= date('M j, Y', strtotime($current_borrower['req_date'])) ?>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <span class="info-label">Expected Return</span>
                                            <div class="info-value text-danger">
                                                <i class="fas fa-undo-alt me-2"></i>
                                                <?= date('M j, Y', strtotime($current_borrower['return_date'])) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php if ($item['item_status'] === 'Lost' && $lost_by_borrower): ?>
                                <div class="lost-item-info">
                                    <h5 class="mb-2"><i class="fas fa-exclamation-triangle me-2"></i>ITEM REPORTED LOST</h5> <!-- Reduced margin -->
                                    <div class="row">
                                        <div class="col-12 mb-1"> <!-- Reduced margin -->
                                            <span class="info-label">Last Borrower</span>
                                            <div class="info-value text-danger">
                                                <i class="fas fa-user-times me-2"></i>
                                                <?= htmlspecialchars($lost_by_borrower['name']) ?> (<?= htmlspecialchars($lost_by_borrower['brgy']) ?>)
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <span class="info-label">Reported Lost On</span>
                                            <div class="info-value text-danger">
                                                <i class="fas fa-calendar-times me-2"></i>
                                                <!-- Updated format to include time -->
                                                <?= date('M j, Y g:i A', strtotime($lost_by_borrower['date'])) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <div class="d-none d-md-block action-btn-desktop d-grid gap-2 mt-3"> <!-- Reduced margin-top -->
                                    <button type="button" class="btn btn-primary action-btn" data-bs-toggle="modal" data-bs-target="#historyModal">
                                        <i class="fas fa-history me-2"></i> View Full Borrowing History
                                    </button>
                                    <button type="button" class="btn btn-outline-danger action-btn" onclick="alert('Functionality to report an issue will be added here.')">
                                        <i class="fas fa-exclamation-triangle me-2"></i> Report Issue
                                    </button>
                                </div>

                                <div class="mt-3 pt-2 border-top"> <!-- Reduced margin-top and padding-top -->
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-1"></i>
                                        Last data refresh: <?= date('F j, Y \a\t g:i A') ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="fixed-action-bar d-md-none">
        <div class="d-grid gap-2">
            <button type="button" class="btn btn-primary action-btn" data-bs-toggle="modal" data-bs-target="#historyModal">
                <i class="fas fa-history me-2"></i> View Borrowing History
            </button>
        </div>
    </div>

    <div class="modal fade" id="historyModal" tabindex="-1" aria-labelledby="historyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="historyModalLabel">
                        <i class="fas fa-history me-2"></i>Borrowing History for <?= htmlspecialchars($item['res_name']) ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php if (!empty($borrowing_history)): ?>
                    <div class="table-responsive">
                        <table class="table modal-history-table">
                            <thead>
                                <tr>
                                    <th>Borrower Name</th>
                                    <th>Barangay</th>
                                    <th>Borrowed On:</th> <!-- NEW COLUMN -->
                                    <th>Returned On:</th> <!-- NEW COLUMN -->
                                    <th>Serial Number</th>
                                    <th>Condition</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($borrowing_history as $history): ?>
                                <tr>
                                    <td data-label="Borrower Name"><?= htmlspecialchars($history['user_full_name']) ?></td>
                                    <td data-label="Barangay"><?= htmlspecialchars($history['borrower_brgy']) ?></td>
                                    <td data-label="Borrowed On:">
                                        <?php if ($history['borrow_timestamp']): ?>
                                            <?= date('M j, Y g:i A', strtotime($history['borrow_timestamp'])) ?>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td data-label="Returned On:">
                                        <?php if ($history['return_timestamp']): ?>
                                            <?= date('M j, Y g:i A', strtotime($history['return_timestamp'])) ?>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td data-label="Serial Number">
                                        <?= htmlspecialchars($history['serial_number'] ?? 'N/A') ?>
                                    </td>
                                    <td data-label="Condition">
                                        <?php if ($history['return_condition']): ?>
                                            <span class="badge rounded-pill condition-badge <?= getConditionColorClass($history['return_condition']) ?>">
                                                <?= htmlspecialchars($history['return_condition']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info text-center py-4">
                        <i class="fas fa-info-circle me-2 fa-2x"></i>
                        <h5 class="mt-2">No Borrowing History</h5>
                        <p class="mb-0">This item has not been borrowed yet.</p>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Close
                    </button>
                    <?php if (!empty($borrowing_history)): ?>
                    <button type="button" class="btn btn-danger" onclick="exportHistoryToPDF()">
                        <i class="fas fa-file-pdf me-2"></i> Export as PDF
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
    
    <script>
        // Function to export history to PDF
        async function exportHistoryToPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();

            // Function to load image and return a Promise
            const loadImage = (src) => {
                return new Promise((resolve, reject) => {
                    const img = new Image();
                    img.onload = () => resolve(img);
                    img.onerror = reject;
                    img.src = src;
                });
            };

            // Load logos
            // NOTE: Ensure these paths ('https://brsms-tagoloan.com/uploads/BRSMS.png', 'https://brsms-tagoloan.com/uploads/tagoloan.jpg') are correct in your project.
            const brsmsLogo = await loadImage('https://brsms-tagoloan.com/public_html/uploads/BRSMS.png');
            const tagoloanLogo = await loadImage('https://brsms-tagoloan.com/public_html/uploads/tagoloan.jpg');

            // Add header content
            const margin = 15; 
            let currentY = 10; // Starting Y position for header content

            // BRSMS Logo (left) 
            doc.addImage(brsmsLogo, 'PNG', margin, currentY, 20, 20); 

            // Tagoloan Logo (right) 
            doc.addImage(tagoloanLogo, 'JPEG', doc.internal.pageSize.width - margin - 20, currentY, 20, 20);

            // Header Text 
            doc.setFont('helvetica', 'normal');
            doc.setFontSize(10);
            doc.text('Republic of the Philippines', doc.internal.pageSize.width / 2, currentY + 2, { align: 'center' });
            doc.setFont('helvetica', 'bold');
            doc.setFontSize(11);
            doc.text('Municipality of Tagoloan', doc.internal.pageSize.width / 2, currentY + 7, { align: 'center' });
            doc.setFont('helvetica', 'normal');
            doc.setFontSize(10);
            doc.text('Province of Misamis Oriental', doc.internal.pageSize.width / 2, currentY + 12, { align: 'center' });

            currentY += 25; // Move Y position after header text

            // Line separator
            doc.setDrawColor(200, 200, 200);
            doc.line(margin, currentY, doc.internal.pageSize.width - margin, currentY);

            currentY += 7; // Space after line

            doc.setFont('helvetica', 'bold');
            doc.setFontSize(14);
            doc.text('BARANGAY RESOURCE SHARING MANAGEMENT SYSTEM', doc.internal.pageSize.width / 2, currentY, { align: 'center' });
            currentY += 5; // Space after title
            doc.setFont('helvetica', 'normal');
            doc.setFontSize(12);
            doc.text('Borrowing History Report', doc.internal.pageSize.width / 2, currentY, { align: 'center' });
            currentY += 10; // Space after subtitle

            // Add item details below the main header
            doc.setTextColor(0, 0, 0);
            doc.setFontSize(12);
            doc.text('Item: <?= addslashes($item['res_name']) ?>', margin, currentY);
            currentY += 7;
            doc.text('Base Barangay: <?= addslashes($item['brgy_name']) ?>', margin, currentY);
            currentY += 7;
            doc.text('Generated on: <?= date('F j, Y') ?>', margin, currentY);
            currentY += 7;

            // Add a line separator before the table
            doc.setDrawColor(200, 200, 200);
            doc.line(margin, currentY + 3, doc.internal.pageSize.width - margin, currentY + 3);
            currentY += 5; 

            // Prepare table data for PDF
            const tableRows = [];
            <?php foreach ($borrowing_history as $history): ?>
                tableRows.push([
                    '<?= addslashes($history['user_full_name']) ?>',
                    '<?= addslashes($history['borrower_brgy']) ?>',
                    '<?= $history['borrow_timestamp'] ? date('M j, Y g:i A', strtotime($history['borrow_timestamp'])) : 'N/A' ?>', // NEW
                    '<?= $history['return_timestamp'] ? date('M j, Y g:i A', strtotime($history['return_timestamp'])) : 'N/A' ?>', // NEW
                    '<?= addslashes($history['serial_number'] ?? 'N/A') ?>',
                    '<?= addslashes($history['return_condition'] ?? 'N/A') ?>'
                ]);
            <?php endforeach; ?>

            // Create table
            doc.autoTable({
                startY: currentY + 5, 
                head: [['Borrower Name', 'Barangay', 'Borrowed On:', 'Returned On:', 'Serial Number', 'Condition']], // UPDATED HEADER
                body: tableRows,
                theme: 'grid',
                headStyles: {
                    fillColor: [95, 44, 130], 
                    textColor: [255, 255, 255],
                    fontStyle: 'bold'
                },
                alternateRowStyles: {
                    fillColor: [240, 240, 240]
                },
                styles: {
                    fontSize: 9,
                    cellPadding: 3,
                    overflow: 'linebreak'
                },
                margin: { left: margin, right: margin }
            });
            
            // Add page numbers
            const pageCount = doc.internal.getNumberOfPages();
            for (let i = 1; i <= pageCount; i++) {
                doc.setPage(i);
                doc.setFontSize(10);
                doc.setTextColor(100, 100, 100);
                doc.text('Page ' + i + ' of ' + pageCount, doc.internal.pageSize.width / 2, doc.internal.pageSize.height - 10, { align: 'center' });
            }
            
            // Trigger a download
            doc.save('Borrowing_History_<?= addslashes($item['res_name']) ?>_<?= date('Ymd') ?>.pdf');
        }
    </script>
</body>
</html>
