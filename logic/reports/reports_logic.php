<?php
session_start();
require __DIR__ . '/../database/db.php';
require_once(__DIR__ . '/../../vendor/autoload.php');

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$current_brgy_id = $_SESSION['brgy_id'];
$current_user_role = $_SESSION['role'];

// Default filter values for Month and Year
$selected_month = isset($_GET['month']) ? $_GET['month'] : '';
$selected_year = isset($_GET['year']) ? $_GET['year'] : '';

// Changed default report_type to empty string to allow "Select" option to be default
$report_type = isset($_GET['report_type']) ? $_GET['report_type'] : '';
// Changed default status_filter to empty string to allow "Select" option to be default
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Helper function for status colors (for requests and single items)
function getStatusColorClass($status) {
    switch(strtolower($status)) {
        case 'pending': return 'badge-pending';
        case 'approved': return 'badge-approved';
        case 'rejected': return 'badge-rejected';
        case 'completed': return 'badge-completed';
        case 'borrowed': return 'badge-borrowed';
        case 'cancelled': return 'badge-cancelled';
        default: return 'badge-secondary'; // Fallback for unknown status
    }
}

// Helper function for resource status colors (for single items in inventory)
function getResourceStatusColorClass($status) {
    switch(strtolower($status)) {
        case 'available': return 'badge-available';
        case 'borrowed': return 'badge-borrowed';
        case 'under maintenance': return 'badge-under-maintenance';
        default: return 'badge-secondary'; // Fallback for unknown status
    }
}

// Helper function for condition colors (copied from returning.php)
function getConditionColorClass($condition) {
    $condition_lower = strtolower($condition);
    if (strpos($condition_lower, 'good') !== false) {
        return 'badge-success'; // Green for good
    } elseif (strpos($condition_lower, 'damaged') !== false) { // Modified line
        return 'badge-danger'; // Red for damaged
    } elseif (strpos($condition_lower, 'scratch') !== false || strpos($condition_lower, 'minor') !== false) { // Modified line
        return 'badge-warning'; // Yellow for minor/scratch
    } elseif (strpos($condition_lower, 'lost') !== false) {
        return 'badge-danger'; // Red for lost
    } else {
        return 'badge-secondary'; // Grey for other/bad
    }
}

// --- PDF Export Logic ---
if (isset($_GET['export']) && $_GET['export'] == 'pdf') {
    // Turn off error reporting temporarily for PDF output to prevent "headers already sent" error
    error_reporting(0);
    ini_set('display_errors', 0);

    require __DIR__ . '/../../vendor/tecnickcom/tcpdf/tcpdf.php';

    // Custom PDF class with header
    class REPORT_PDF extends TCPDF {
        public function Header() {
            $leftLogo = '../uploads/BRSMS.jpg';
            $rightLogo = '../uploads/tagoloan.jpg';

            // Left logo
            if (file_exists($leftLogo)) {
                $this->Image($leftLogo, 15, 10, 20);
            }

            // Right logo
            if (file_exists($rightLogo)) {
                $this->Image($rightLogo, $this->getPageWidth() - 35, 10, 20);
            }

            // Header text
            $this->SetY(10);
            $this->SetFont('helvetica', '', 10);
            $this->Cell(0, 5, 'Republic of the Philippines', 0, 1, 'C');
            $this->SetFont('helvetica', 'B', 11);
            $this->Cell(0, 5, 'Municipality of Tagoloan', 0, 1, 'C');
            $this->SetFont('helvetica', '', 10);
            $this->Cell(0, 5, 'Province of Misamis Oriental', 0, 1, 'C');

            // Line separator
            $this->Line(15, 30, $this->getPageWidth() - 15, 30);
        }

        // Watermark on every page
        public function AddPage($orientation='', $format='', $keepmargins=false, $tocpage=false) {
            parent::AddPage($orientation, $format, $keepmargins, $tocpage);

            // Add watermark to this new page
            $this->SetAlpha(0.05); // Decreased opacity
            $watermarkPath = 'uploads/BRSMS.jpg';
            if (file_exists($watermarkPath)) {
                $x = ($this->getPageWidth() - 150) / 2; // Increased size
                $y = ($this->getPageHeight() - 150) / 2; // Increased size
                $this->Image($watermarkPath, $x, $y, 150, 150, '', '', '', false, 300, '', false, false, 0);
            }
            $this->SetAlpha(1);
        }

        public function Footer() {
            $this->SetY(-15);
            $this->SetFont('helvetica', 'I', 8);
            $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, 0, 'C');
        }
    }

    // Create new PDF document in landscape
    $pdf = new REPORT_PDF('L', 'mm', 'A4', true, 'UTF-8', false);

    // Set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('BRSMS');
    $pdf->SetTitle('BRSMS Report - ' . ucfirst($report_type));
    $pdf->SetSubject('BRSMS Report');

    // Set margins
    $pdf->SetMargins(15, 40, 15);
    $pdf->SetHeaderMargin(10);
    $pdf->SetFooterMargin(10);

    // Set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, 15);

    // Add a page
    $pdf->AddPage();

    // Set font
    $pdf->SetFont('helvetica', '', 10);

    // Report title
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'BARANGAY RESOURCE SHARING MANAGEMENT SYSTEM', 0, 1, 'C');
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, strtoupper($report_type) . ' REPORT', 0, 1, 'C');
    $pdf->Ln(5);

    // Report details in a single line
    $pdf->SetFont('helvetica', '', 10);
    $details = '<table border="0" cellpadding="2" cellspacing="0" width="100%"><tr>';
    $details .= '<td width="25%"><strong>Barangay:</strong> ' . $_SESSION['brgy_name'] . '</td>';
    $details .= '<td width="25%"><strong>Generated On:</strong> ' . date('F j, Y') . '</td>';

    // Display period only if month and year are selected AND report type is not inventory
    if (!empty($selected_month) && !empty($selected_year) && $report_type !== 'inventory') {
        $details .= '<td width="30%"><strong>Period:</strong> ' . date('F', mktime(0, 0, 0, $selected_month, 10)) . ' ' . $selected_year . '</td>';
    } else {
        $details .= '<td width="30%"><strong>Period:</strong> N/A</td>'; // Or leave empty
    }

    if ($status_filter !== '' && $status_filter !== 'all') { // Check for empty string and 'all'
        $details .= '<td width="20%"><strong>Status:</strong> ' . ucfirst($status_filter) . '</td>';
    }
    $details .= '</tr></table>';
    $pdf->writeHTML($details, true, false, false, false, '');
    $pdf->Ln(10);

    // --- Generate the appropriate table based on report type for PDF ---
    if ($report_type === 'requests') {
        $query = "SELECT r.*, res.res_name, b.brgy_name as owner_brgy_name,
                         u.user_full_name as requester_name, rb.brgy_name as requester_brgy_name
                  FROM requests r
                  JOIN resources res ON r.res_id = res.res_id
                  JOIN barangays b ON r.res_brgy_id = b.brgy_id
                  JOIN barangays rb ON r.req_brgy_id = rb.brgy_id
                  JOIN users u ON r.req_user_id = u.user_id
                  WHERE r.res_brgy_id = ?";

        $params = [$current_brgy_id];
        $types = "i";

        if ($status_filter !== '' && $status_filter !== 'all') { // Check for empty string and 'all'
            $query .= " AND r.req_status = ?";
            $params[] = $status_filter;
            $types .= "s";
        }

        // Filter by month and year only if selected
        if (!empty($selected_month) && !empty($selected_year)) {
            $query .= " AND MONTH(r.req_date) = ? AND YEAR(r.req_date) = ?";
            $params[] = $selected_month;
            $params[] = $selected_year;
            $types .= "ii";
        }

        $query .= " ORDER BY r.req_date DESC";

        $stmt = $conn->prepare($query);
        if (!empty($params)) {
            $bind_params = [$types];
            foreach ($params as $key => $value) {
                $bind_params[] = &$params[$key];
            }
            call_user_func_array([$stmt, 'bind_param'], $bind_params);
        }
        $stmt->execute();
        $requests_pdf = $stmt->get_result(); // Renamed to avoid conflict

        // Define column widths for requests table (total 100%)
        $col_widths = [8, 20, 18, 18, 6, 12, 12, 6]; // Adjusted last column to 6% for better fit

        $html = '<table border="1" cellpadding="5" style="border-collapse: collapse; width: 100%;">';
        $html .= '<thead>';
        $html .= '<tr style="background-color: #5f2c82; color: white;">';
        $html .= '<th width="' . $col_widths[0] . '%" style="text-align: center;">ID</th>';
        $html .= '<th width="' . $col_widths[1] . '%" style="text-align: center;">Resource</th>';
        $html .= '<th width="' . $col_widths[2] . '%" style="text-align: center;">Requester</th>';
        $html .= '<th width="' . $col_widths[3] . '%" style="text-align: center;">From Barangay</th>';
        $html .= '<th width="' . $col_widths[4] . '%" style="text-align: center;">Qty</th>';
        $html .= '<th width="' . $col_widths[5] . '%" style="text-align: center;">Request Date</th>';
        $html .= '<th width="' . $col_widths[6] . '%" style="text-align: center;">Return Date</th>';
        $html .= '<th width="' . $col_widths[7] . '%" style="text-align: center;">Status</th>';
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';

        if ($requests_pdf->num_rows > 0) {
            while ($request = $requests_pdf->fetch_assoc()) {
                $html .= '<tr>';
                $html .= '<td width="' . $col_widths[0] . '%" style="text-align: center;">' . $request['req_id'] . '</td>';
                $html .= '<td width="' . $col_widths[1] . '%">' . htmlspecialchars($request['res_name']) . '</td>';
                $html .= '<td width="' . $col_widths[2] . '%">' . htmlspecialchars($request['requester_name']) . '</td>';
                $html .= '<td width="' . $col_widths[3] . '%">' . htmlspecialchars($request['requester_brgy_name']) . '</td>';
                $html .= '<td width="' . $col_widths[4] . '%" style="text-align: center;">' . $request['req_quantity'] . '</td>';
                $html .= '<td width="' . $col_widths[5] . '%" style="text-align: center;">' . date('M d, Y', strtotime($request['req_date'])) . '</td>';
                $html .= '<td width="' . $col_widths[6] . '%" style="text-align: center;">' . ($request['return_date'] ? date('M d, Y', strtotime($request['return_date'])) : 'N/A') . '</td>';
                $html .= '<td width="' . $col_widths[7] . '%" style="text-align: center;">' . $request['req_status'] . '</td>';
                $html .= '</tr>';
            }
        } else {
            $html .= '<tr><td colspan="8" style="text-align: center;">No requests found for the selected filters</td></tr>';
        }

        $html .= '</tbody></table>';
        $pdf->writeHTML($html, true, false, false, false, '');
    } elseif ($report_type === 'inventory') {
        $inventory_data_pdf = [];

        // Fetch all resources for the current barangay
        $query_resources = "SELECT res_id, res_name, res_description, res_quantity, res_status, is_bulk FROM resources WHERE brgy_id = ?";
        $stmt_resources = $conn->prepare($query_resources);
        $stmt_resources->bind_param("i", $current_brgy_id);
        $stmt_resources->execute();
        $all_resources = $stmt_resources->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt_resources->close();

        foreach ($all_resources as $resource) {
            if ($resource['is_bulk']) {
                // For bulk items, get counts for each status
                $query_items = "
                    SELECT item_status, COUNT(*) as count
                    FROM resource_items
                    WHERE res_id = ?
                ";
                // Inventory status is always current, no month/year filter here
                $stmt_items = $conn->prepare($query_items . " GROUP BY item_status");
                $stmt_items->bind_param("i", $resource['res_id']);
                $stmt_items->execute();
                $item_counts = $stmt_items->get_result()->fetch_all(MYSQLI_ASSOC);
                $stmt_items->close();

                foreach ($item_counts as $item_count) {
                    $status = $item_count['item_status'];
                    $count = $item_count['count'];

                    if ($count > 0 && ($status_filter === '' || $status_filter === 'all' || strtolower($status_filter) === strtolower($status))) {
                        $inventory_data_pdf[] = [
                            'res_id' => $resource['res_id'],
                            'res_name' => $resource['res_name'],
                            'res_description' => $resource['res_description'],
                            'is_bulk' => true,
                            'display_quantity' => $count,
                            'display_status' => $status,
                            'item_id' => 'N/A' // Not applicable for grouped items
                        ];
                    }
                }
            } else {
                // For non-bulk items, add if status matches filter or filter is 'all'
                if ($status_filter === '' || $status_filter === 'all' || strtolower($status_filter) === strtolower($resource['res_status'])) {
                    $inventory_data_pdf[] = [
                        'res_id' => $resource['res_id'],
                        'res_name' => $resource['res_name'],
                        'res_description' => $resource['res_description'],
                        'is_bulk' => false,
                        'display_quantity' => $resource['res_quantity'],
                        'display_status' => $resource['res_status'],
                        'item_id' => 'N/A'
                    ];
                }
            }
        }

        // Define column widths for inventory table (total 100%)
        $col_widths = [10, 25, 35, 10, 20];

        $html = '<table border="1" cellpadding="5" style="border-collapse: collapse; width: 100%;">';
        $html .= '<thead>';
        $html .= '<tr style="background-color: #5f2c82; color: white;">';
        $html .= '<th width="' . $col_widths[0] . '%" style="text-align: center;">Resource ID</th>';
        $html .= '<th width="' . $col_widths[1] . '%" style="text-align: center;">Name</th>';
        $html .= '<th width="' . $col_widths[2] . '%" style="text-align: center;">Description</th>';
        $html .= '<th width="' . $col_widths[3] . '%" style="text-align: center;">Quantity</th>';
        $html .= '<th width="' . $col_widths[4] . '%" style="text-align: center;">Status</th>';
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';

        if (!empty($inventory_data_pdf)) {
            foreach ($inventory_data_pdf as $item) {
                $html .= '<tr>';
                $html .= '<td width="' . $col_widths[0] . '%" style="text-align: center;">' . $item['res_id'] . '</td>';
                $html .= '<td width="' . $col_widths[1] . '%">' . htmlspecialchars($item['res_name']) . '</td>';
                $html .= '<td width="' . $col_widths[2] . '%">' . htmlspecialchars($item['res_description']) . '</td>';
                $html .= '<td width="' . $col_widths[3] . '%" style="text-align: center;">' . $item['display_quantity'] . '</td>';
                $html .= '<td width="' . $col_widths[4] . '%" style="text-align: center;">' . $item['display_status'] . '</td>';
                $html .= '</tr>';
            }
        } else {
            $html .= '<tr><td colspan="5" style="text-align: center;">No inventory items found for the selected filters</td></tr>';
        }

        $html .= '</tbody></table>';
        $pdf->writeHTML($html, true, false, false, false, '');
    } elseif ($report_type === 'returns') {
        // --- Data processing for Returns PDF ---
        $processed_returns_data_pdf = [];
        $query = "
            SELECT r.*, req.req_date, req.req_quantity, res.res_name, res.res_description, res.is_bulk,
                   b.brgy_name as requester_brgy_name, u.user_full_name as requester_name
            FROM returns r
            JOIN requests req ON r.req_id = req.req_id
            JOIN resources res ON req.res_id = res.res_id
            JOIN barangays b ON req.req_brgy_id = b.brgy_id
            JOIN users u ON req.req_user_id = u.user_id
            WHERE r.brgy_id = ?
        ";

        $params = [$current_brgy_id];
        $types = "i";

        // Filter by month and year
        if (!empty($selected_month) && !empty($selected_year)) {
            $query .= " AND MONTH(r.return_date) = ? AND YEAR(r.return_date) = ?";
            $params[] = $selected_month;
            $params[] = $selected_year;
            $types .= "ii";
        }

        $query .= " ORDER BY r.return_date DESC";

        $stmt = $conn->prepare($query);
        if (!empty($params)) {
            $bind_params = [$types];
            foreach ($params as $key => $value) {
                $bind_params[] = &$params[$key];
            }
            call_user_func_array([$stmt, 'bind_param'], $bind_params);
        }
        $stmt->execute();
        $returns_result_pdf = $stmt->get_result();

        while ($row = $returns_result_pdf->fetch_assoc()) {
            $return_condition_raw = $row['return_condition'];
            $is_bulk = (bool)$row['is_bulk'];

            if ($is_bulk) {
                $item_conditions = json_decode($return_condition_raw, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($item_conditions)) {
                    foreach ($item_conditions as $item_condition) {
                        // Apply status_filter here for individual items
                        $condition_lower = strtolower($item_condition['condition']);
                        $match = false;
                        if ($status_filter === '' || $status_filter === 'all') {
                            $match = true;
                        } elseif ($status_filter === 'good' && strpos($condition_lower, 'good') !== false) {
                            $match = true;
                        } elseif ($status_filter === 'minor scratches' && strpos($condition_lower, 'minor scratches') !== false) {
                            $match = true;
                        } elseif ($status_filter === 'damaged' && strpos($condition_lower, 'damaged') !== false) {
                            $match = true;
                        } elseif ($status_filter === 'lost' && strpos($condition_lower, 'lost') !== false) {
                            $match = true;
                        } elseif ($status_filter === 'other') {
                            // Check if the condition is NOT one of the predefined ones
                            $match = !(strpos($condition_lower, 'good') !== false ||
                                       strpos($condition_lower, 'minor scratches') !== false ||
                                       strpos($condition_lower, 'damaged') !== false ||
                                       strpos($condition_lower, 'lost') !== false);
                        }

                        if ($match) {
                            $new_row = $row; // Copy original row data
                            $new_row['req_quantity'] = 1; // Each item is 1 quantity
                            $new_row['display_condition'] = $item_condition['condition'];
                            $new_row['serial_number'] = $item_condition['serial_number'] ?? 'N/A'; // Add serial number
                            $processed_returns_data_pdf[] = $new_row;
                        }
                    }
                } else {
                    // Fallback for bulk items if JSON decoding fails or is not an array
                    $condition_lower = strtolower($return_condition_raw);
                    $match = false;
                    if ($status_filter === '' || $status_filter === 'all') {
                        $match = true;
                    } elseif ($status_filter === 'good' && strpos($condition_lower, 'good') !== false) {
                        $match = true;
                    } elseif ($status_filter === 'minor scratches' && strpos($condition_lower, 'minor scratches') !== false) {
                        $match = true;
                    } elseif ($status_filter === 'damaged' && strpos($condition_lower, 'damaged') !== false) {
                        $match = true;
                    } elseif ($status_filter === 'lost' && strpos($condition_lower, 'lost') !== false) {
                        $match = true;
                    } elseif ($status_filter === 'other') {
                        // Check if the condition is NOT one of the predefined ones
                        $match = !(strpos($condition_lower, 'good') !== false ||
                                   strpos($condition_lower, 'minor scratches') !== false ||
                                   strpos($condition_lower, 'damaged') !== false ||
                                   strpos($condition_lower, 'lost') !== false);
                    }

                    if ($match) {
                        $row['display_condition'] = $return_condition_raw;
                        $row['serial_number'] = 'N/A';
                        $processed_returns_data_pdf[] = $row;
                    }
                }
            } else {
                // For non-bulk items, the condition is a simple string
                $condition_lower = strtolower($return_condition_raw);
                $match = false;
                if ($status_filter === '' || $status_filter === 'all') {
                    $match = true;
                } elseif ($status_filter === 'good' && strpos($condition_lower, 'good') !== false) {
                    $match = true;
                } elseif ($status_filter === 'minor scratches' && strpos($condition_lower, 'minor scratches') !== false) {
                    $match = true;
                } elseif ($status_filter === 'damaged' && strpos($condition_lower, 'damaged') !== false) {
                    $match = true;
                } elseif ($status_filter === 'lost' && strpos($condition_lower, 'lost') !== false) {
                    $match = true;
                } elseif ($status_filter === 'other') {
                    // Check if the condition is NOT one of the predefined ones
                    $match = !(strpos($condition_lower, 'good') !== false ||
                               strpos($condition_lower, 'minor scratches') !== false ||
                               strpos($condition_lower, 'damaged') !== false ||
                               strpos($condition_lower, 'lost') !== false);
                }

                if ($match) {
                    $row['display_condition'] = $return_condition_raw;
                    $row['serial_number'] = 'N/A'; // Not applicable for single items
                    $processed_returns_data_pdf[] = $row;
                }
            }
        }

        // Define column widths for returns table (total 100%)
        // Added Serial Number column
        $col_widths = [8, 15, 15, 15, 8, 10, 10, 10, 9]; // Adjusted widths for better fit

        $html = '<table border="1" cellpadding="5" style="border-collapse: collapse; width: 100%;">';
        $html .= '<thead>';
        $html .= '<tr style="background-color: #5f2c82; color: white;">';
        $html .= '<th width="' . $col_widths[0] . '%" style="text-align: center;">Return ID</th>';
        $html .= '<th width="' . $col_widths[1] . '%" style="text-align: center;">Resource</th>';
        $html .= '<th width="' . $col_widths[2] . '%" style="text-align: center;">Requester</th>';
        $html .= '<th width="' . $col_widths[3] . '%" style="text-align: center;">From Barangay</th>';
        $html .= '<th width="' . $col_widths[4] . '%" style="text-align: center;">Qty</th>';
        $html .= '<th width="' . $col_widths[5] . '%" style="text-align: center;">Serial Number</th>'; // New column
        $html .= '<th width="' . $col_widths[6] . '%" style="text-align: center;">Request Date</th>';
        $html .= '<th width="' . $col_widths[7] . '%" style="text-align: center;">Return Date</th>';
        $html .= '<th width="' . $col_widths[8] . '%" style="text-align: center;">Condition</th>';
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';

        if (!empty($processed_returns_data_pdf)) {
            foreach ($processed_returns_data_pdf as $return_row) {
                $html .= '<tr>';
                $html .= '<td width="' . $col_widths[0] . '%" style="text-align: center;">' . $return_row['return_id'] . '</td>';
                $html .= '<td width="' . $col_widths[1] . '%">' . htmlspecialchars($return_row['res_name']) . '</td>';
                $html .= '<td width="' . $col_widths[2] . '%">' . htmlspecialchars($return_row['requester_name']) . '</td>';
                $html .= '<td width="' . $col_widths[3] . '%">' . htmlspecialchars($return_row['requester_brgy_name']) . '</td>';
                $html .= '<td width="' . $col_widths[4] . '%" style="text-align: center;">' . $return_row['req_quantity'] . '</td>';
                $html .= '<td width="' . $col_widths[5] . '%" style="text-align: center;">' . htmlspecialchars($return_row['serial_number']) . '</td>'; // Display serial number
                $html .= '<td width="' . $col_widths[6] . '%" style="text-align: center;">' . date('M d, Y', strtotime($return_row['req_date'])) . '</td>';
                $html .= '<td width="' . $col_widths[7] . '%" style="text-align: center;">' . ($return_row['return_date'] ? date('M d, Y', strtotime($return_row['return_date'])) : 'N/A') . '</td>';
                $html .= '<td width="' . $col_widths[8] . '%" style="text-align: center;">' . $return_row['display_condition'] . '</td>';
                $html .= '</tr>';
            }
        } else {
            $html .= '<tr><td colspan="9" style="text-align: center;">No returned resources found for the selected filters</td></tr>';
        }

        $html .= '</tbody></table>';
        $pdf->writeHTML($html, true, false, false, false, '');
    }

    // Close and output PDF document
    $pdf->Output('BRSMS_Report_' . $report_type . '_' . date('Ymd') . '.pdf', 'I');
    exit();
}

// --- Get data based on filters for HTML display ---
$inventory = []; // Initialize for inventory report
$requests = []; // Initialize for requests report
$returns_html = []; // Initialize for returns report (renamed to avoid conflict with PDF var)

// Only fetch data if a report type is selected
if ($report_type !== '') {
    if ($report_type === 'requests') {
        // Only fetch requests if month and year are selected
        if (!empty($selected_month) && !empty($selected_year)) {
            $query = "SELECT r.*, res.res_name, b.brgy_name as owner_brgy_name,
                             u.user_full_name as requester_name, rb.brgy_name as requester_brgy_name
                      FROM requests r
                      JOIN resources res ON r.res_id = res.res_id
                      JOIN barangays b ON r.res_brgy_id = b.brgy_id
                      JOIN barangays rb ON r.req_brgy_id = rb.brgy_id
                      JOIN users u ON r.req_user_id = u.user_id
                      WHERE r.res_brgy_id = ?";

            $params = [$current_brgy_id];
            $types = "i";

            if ($status_filter !== '' && $status_filter !== 'all') {
                $query .= " AND r.req_status = ?";
                $params[] = $status_filter;
                $types .= "s";
            }

            // Filter by month and year
            $query .= " AND MONTH(r.req_date) = ? AND YEAR(r.req_date) = ?";
            $params[] = $selected_month;
            $params[] = $selected_year;
            $types .= "ii";

            $query .= " ORDER BY r.req_date DESC";

            $stmt = $conn->prepare($query);
            if (!empty($params)) {
                $bind_params = [$types];
                foreach ($params as $key => $value) {
                    $bind_params[] = &$params[$key];
                }
                call_user_func_array([$stmt, 'bind_param'], $bind_params);
            }
            $stmt->execute();
            $requests = $stmt->get_result();
        }
    }

    if ($report_type === 'inventory') {
        $inventory = []; // Reset inventory array

        // Fetch all resources for the current barangay (no month/year filter for inventory)
        $query_resources = "SELECT res_id, res_name, res_description, res_quantity, res_status, is_bulk FROM resources WHERE brgy_id = ?";
        $stmt_resources = $conn->prepare($query_resources);
        $stmt_resources->bind_param("i", $current_brgy_id);
        $stmt_resources->execute();
        $all_resources = $stmt_resources->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt_resources->close();

        foreach ($all_resources as $resource) {
            if ($resource['is_bulk']) {
                // For bulk items, get counts for each status
                $query_items = "
                    SELECT item_status, COUNT(*) as count
                    FROM resource_items
                    WHERE res_id = ?
                    GROUP BY item_status
                ";
                $stmt_items = $conn->prepare($query_items);
                $stmt_items->bind_param("i", $resource['res_id']);
                $stmt_items->execute();
                $item_counts = $stmt_items->get_result()->fetch_all(MYSQLI_ASSOC);
                $stmt_items->close();

                foreach ($item_counts as $item_count) {
                    $status = $item_count['item_status'];
                    $count = $item_count['count'];

                    if ($count > 0 && ($status_filter === '' || $status_filter === 'all' || strtolower($status_filter) === strtolower($status))) {
                        $inventory[] = [
                            'res_id' => $resource['res_id'],
                            'res_name' => $resource['res_name'],
                            'res_description' => $resource['res_description'],
                            'is_bulk' => true,
                            'display_quantity' => $count,
                            'display_status' => $status,
                            'item_id' => 'N/A' // Not applicable for grouped items
                        ];
                    }
                }
            } else {
                // For non-bulk items, add if status matches filter or filter is 'all'
                if ($status_filter === '' || $status_filter === 'all' || strtolower($status_filter) === strtolower($resource['res_status'])) {
                    $inventory[] = [
                        'res_id' => $resource['res_id'],
                        'res_name' => $resource['res_name'],
                        'res_description' => $resource['res_description'],
                        'is_bulk' => false,
                        'display_quantity' => $resource['res_quantity'],
                        'display_status' => $resource['res_status'],
                        'item_id' => 'N/A'
                    ];
                }
            }
        }
    }

    if ($report_type === 'returns') {
        // Only fetch returns if month and year are selected
        if (!empty($selected_month) && !empty($selected_year)) {
            // --- Data processing for Returns HTML display ---
            $query = "
                SELECT r.*, req.req_date, req.req_quantity, res.res_name, res.res_description, res.is_bulk,
                       b.brgy_name as requester_brgy_name, u.user_full_name as requester_name
                FROM returns r
                JOIN requests req ON r.req_id = req.req_id
                JOIN resources res ON req.res_id = res.res_id
                JOIN barangays b ON req.req_brgy_id = b.brgy_id
                JOIN users u ON req.req_user_id = u.user_id
                WHERE r.brgy_id = ?
            ";

            $params = [$current_brgy_id];
            $types = "i";

            // Filter by month and year
            $query .= " AND MONTH(r.return_date) = ? AND YEAR(r.return_date) = ?";
            $params[] = $selected_month;
            $params[] = $selected_year;
            $types .= "ii";

            $query .= " ORDER BY r.return_date DESC";

            $stmt = $conn->prepare($query);
            if (!empty($params)) {
                $bind_params = [$types];
                foreach ($params as $key => $value) {
                    $bind_params[] = &$params[$key];
                }
                call_user_func_array([$stmt, 'bind_param'], $bind_params);
            }
            $stmt->execute();
            $returns_result_html = $stmt->get_result(); // Renamed to avoid conflict

            while ($row = $returns_result_html->fetch_assoc()) {
                $return_condition_raw = $row['return_condition'];
                $is_bulk = (bool)$row['is_bulk'];

                if ($is_bulk) {
                    $item_conditions = json_decode($return_condition_raw, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($item_conditions)) {
                        foreach ($item_conditions as $item_condition) {
                            // Apply status_filter here for individual items
                            $condition_lower = strtolower($item_condition['condition']);
                            $match = false;
                            if ($status_filter === '' || $status_filter === 'all') {
                                $match = true;
                            } elseif ($status_filter === 'good' && strpos($condition_lower, 'good') !== false) {
                                $match = true;
                            } elseif ($status_filter === 'minor scratches' && strpos($condition_lower, 'minor scratches') !== false) {
                                $match = true;
                            } elseif ($status_filter === 'damaged' && strpos($condition_lower, 'damaged') !== false) {
                                $match = true;
                            } elseif ($status_filter === 'lost' && strpos($condition_lower, 'lost') !== false) {
                                $match = true;
                            } elseif ($status_filter === 'other') {
                                // Check if the condition is NOT one of the predefined ones
                                $match = !(strpos($condition_lower, 'good') !== false ||
                                           strpos($condition_lower, 'minor scratches') !== false ||
                                           strpos($condition_lower, 'damaged') !== false ||
                                           strpos($condition_lower, 'lost') !== false);
                            }

                            if ($match) {
                                $new_row = $row; // Copy original row data
                                $new_row['req_quantity'] = 1; // Each item is 1 quantity
                                $new_row['display_condition'] = $item_condition['condition'];
                                $new_row['serial_number'] = $item_condition['serial_number'] ?? 'N/A'; // Add serial number
                                $returns_html[] = $new_row;
                            }
                        }
                    } else {
                        // Fallback for bulk items if JSON decoding fails or is not an array
                        $condition_lower = strtolower($return_condition_raw);
                        $match = false;
                        if ($status_filter === '' || $status_filter === 'all') {
                            $match = true;
                        } elseif ($status_filter === 'good' && strpos($condition_lower, 'good') !== false) {
                            $match = true;
                        } elseif ($status_filter === 'minor scratches' && strpos($condition_lower, 'minor scratches') !== false) {
                            $match = true;
                        } elseif ($status_filter === 'damaged' && strpos($condition_lower, 'damaged') !== false) {
                            $match = true;
                        } elseif ($status_filter === 'lost' && strpos($condition_lower, 'lost') !== false) {
                            $match = true;
                        } elseif ($status_filter === 'other') {
                            // Check if the condition is NOT one of the predefined ones
                            $match = !(strpos($condition_lower, 'good') !== false ||
                                       strpos($condition_lower, 'minor scratches') !== false ||
                                       strpos($condition_lower, 'damaged') !== false ||
                                       strpos($condition_lower, 'lost') !== false);
                        }

                        if ($match) {
                            $row['display_condition'] = $return_condition_raw;
                            $row['serial_number'] = 'N/A';
                            $returns_html[] = $row;
                        }
                    }
                } else {
                    // For non-bulk items, the condition is a simple string
                    $condition_lower = strtolower($return_condition_raw);
                    $match = false;
                    if ($status_filter === '' || $status_filter === 'all') {
                        $match = true;
                    } elseif ($status_filter === 'good' && strpos($condition_lower, 'good') !== false) {
                        $match = true;
                    } elseif ($status_filter === 'minor scratches' && strpos($condition_lower, 'minor scratches') !== false) {
                        $match = true;
                    } elseif ($status_filter === 'damaged' && strpos($condition_lower, 'damaged') !== false) {
                        $match = true;
                    } elseif ($status_filter === 'lost' && strpos($condition_lower, 'lost') !== false) {
                        $match = true;
                    } elseif ($status_filter === 'other') {
                        // Check if the condition is NOT one of the predefined ones
                        $match = !(strpos($condition_lower, 'good') !== false ||
                                   strpos($condition_lower, 'minor scratches') !== false ||
                                   strpos($condition_lower, 'damaged') !== false ||
                                   strpos($condition_lower, 'lost') !== false);
                    }

                    if ($match) {
                        $row['display_condition'] = $return_condition_raw;
                        $row['serial_number'] = 'N/A'; // Not applicable for single items
                        $returns_html[] = $row;
                    }
                }
            }
        }
    }
}
?>