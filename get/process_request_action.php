<?php
set_exception_handler(function($e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
    exit;
});

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => "$errstr in $errfile on line $errline"
    ]);
    exit;
});
ob_start();

session_start();
header('Content-Type: application/json');

ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');
error_reporting(E_ALL);


ob_start();

require __DIR__ . '/../logic/database/db.php';
require '../logic/logging.php'; 

require_once '../vendor/tecnickcom/tcpdf/tcpdf.php';
require __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

function sendJsonResponse($status, $message, $new_status = null, $http_code = 200) {
    
    ob_clean();
    http_response_code($http_code);
    echo json_encode(['status' => $status, 'message' => $message, 'new_status' => $new_status]);
    exit;
}

try {
    $action = '';
    $request_id = 0;
    $condition_raw = 'No condition specified'; 
    $condition_processed = 'No condition specified'; 
    $item_ids = []; 

    
    $action_performer_user_id = $_SESSION['user_id'] ?? null;
    if (!$action_performer_user_id) {
        sendJsonResponse('error', 'User not logged in.', null, 401);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (!isset($_GET['action'], $_GET['id'])) {
            sendJsonResponse('error', 'Missing action or ID.', null, 400);
        }
        $action = strtolower($_GET['action']);
        $request_id = (int)$_GET['id'];
        $condition_raw = $_GET['condition'] ?? 'No condition specified';
        
        $condition_processed = htmlspecialchars(trim($condition_raw));
        if (empty($condition_processed)) {
            $condition_processed = 'No condition specified';
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['action'], $_POST['id'])) {
            sendJsonResponse('error', 'Missing action or ID in POST data.', null, 400);
        }
        $action = strtolower($_POST['action']);
        $request_id = (int)$_POST['id'];
        $condition_raw = $_POST['condition'] ?? 'No condition specified';

        
        $decoded_condition = json_decode($condition_raw, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded_condition)) {
            
            $condition_processed = $condition_raw; 
        } else {
            
            $condition_processed = htmlspecialchars(trim($condition_raw));
            if (empty($condition_processed)) {
                $condition_processed = 'No condition specified';
            }
        }

        
        if ($action === 'borrow' && isset($_POST['item_ids'])) {
            $item_ids = $_POST['item_ids'];
            if (!is_array($item_ids)) {
                sendJsonResponse('error', 'Invalid item_ids format.', null, 400);
            }
            
            $item_ids = array_map('intval', $item_ids);
            $item_ids = array_filter($item_ids, function($id) { return $id > 0; }); 
            if (empty($item_ids)) {
                sendJsonResponse('error', 'No valid item IDs provided for borrowing.', null, 400);
            }
        }
    } else {
        sendJsonResponse('error', 'Unsupported request method.', null, 405); 
    }

    $status_map = [
        'approve' => 'Approved',
        'reject' => 'Rejected',
        'complete' => 'Completed',
        'borrow' => 'Borrowed',
        'cancel' => 'Cancelled'
    ];

    if (!isset($status_map[$action])) {
        sendJsonResponse('error', 'Invalid action.', null, 400);
    }

    $new_request_status = $status_map[$action];
    $conn->begin_transaction();

    try {
        
        $request_details_query = $conn->prepare("
            SELECT r.req_id, r.req_quantity, r.res_id, r.res_brgy_id, r.req_status, r.req_user_id,
                   res.is_bulk, res.res_name, res.res_description,
                   owner.brgy_name AS owner_brgy_name,
                   requester_brgy.brgy_name AS requester_brgy_name,
                   req_user.user_full_name AS requester_name,
                   req_user.username AS requester_email,
                   r.req_date, r.return_date, r.req_purpose, r.borrow_timestamp, r.return_timestamp
            FROM requests r
            JOIN resources res ON r.res_id = res.res_id
            JOIN barangays owner ON r.res_brgy_id = owner.brgy_id
            JOIN barangays requester_brgy ON r.req_brgy_id = requester_brgy.brgy_id
            JOIN users req_user ON r.req_user_id = req_user.user_id
            WHERE r.req_id = ? FOR UPDATE
        ");
        $request_details_query->bind_param("i", $request_id);
        $request_details_query->execute();
        $request_data = $request_details_query->get_result()->fetch_assoc();

        if (!$request_data) {
            throw new Exception("Request not found.");
        }

        $res_id = $request_data['res_id'];
        $req_quantity = $request_data['req_quantity'];
        $resource_owner_brgy_id = $request_data['res_brgy_id'];
        $current_req_status = $request_data['req_status'];
        $is_bulk = (bool)$request_data['is_bulk'];
        $res_name = $request_data['res_name'];
        $requester_email = $request_data['requester_email']; 

        

        if ($action === 'approve') {
            if ($current_req_status !== 'Pending') {
                throw new Exception("Request is not pending and cannot be approved.");
            }

            $update_request_stmt = $conn->prepare("UPDATE requests SET req_status = ? WHERE req_id = ?");
            $update_request_stmt->bind_param("si", $new_request_status, $request_id);
            if (!$update_request_stmt->execute()) {
                throw new Exception("Failed to update request status: " . $conn->error);
            }

            
            logRequestApprove($action_performer_user_id, $request_id);
            $message = "Request marked as Approved.";

            
            
            class MYPDF extends TCPDF {
                public function Header() {
                    
                    if ($this->getPage() == 1) {
                        $leftLogo = '/uploads/BRSMS.png'; 
                        $rightLogo = '/uploads/tagoloan.jpg';

                        if (file_exists($leftLogo)) {
                            $this->Image($leftLogo, 15, 10, 20);
                        }
                        if (file_exists($rightLogo)) {
                            $this->Image($rightLogo, 175, 10, 20);
                        }

                        $this->SetY(10);
                        $this->SetFont('helvetica', '', 10);
                        $this->Cell(0, 5, 'Republic of the Philippines', 0, 1, 'C');
                        $this->SetFont('helvetica', 'B', 11);
                        $this->Cell(0, 5, 'Municipality of Tagoloan', 0, 1, 'C');
                        $this->SetFont('helvetica', '', 10);
                        $this->Cell(0, 5, 'Province of Misamis Oriental', 0, 1, 'C');
                        $this->Line(15, 30, 195, 30);

                        
                        $this->SetAlpha(0.08);
                        $this->SetFont('helvetica', 'B', 50);
                        $this->StartTransform();
                        $this->Rotate(45, 105, 140);
                        $this->Text(30, 160, 'APPROVED');
                        $this->StopTransform();
                        $this->SetAlpha(1);
                    }
                }

                
                public function AddWatermark($imagePath) {
                    if (file_exists($imagePath)) {
                        $this->SetAlpha(0.08); 
                        $this->Image($imagePath, $this->GetPageWidth() / 2 - 60, $this->GetPageHeight() / 2 - 60, 120, 120, '', '', '', false, 300, '', false, false, 0, false, false, false);
                        $this->SetAlpha(1); 
                    }
                }
            }

            
            
            $GLOBALS['request'] = $request_data;
            $GLOBALS['source'] = 'system_approved'; 

            $pdf = new MYPDF('P', 'mm', 'A4', true, 'UTF-8', false);
            $pdf->SetCreator('BRMS');
            $pdf->SetAuthor('BRMS System');
            $pdf->SetTitle('Request Receipt');
            $pdf->SetMargins(15, 40, 15);
            $pdf->AddPage();

            
            $status_color = 'green'; 

            $html = '
            <style>
                .title { text-align: center; font-size: 16pt; font-weight: bold; margin-top: 10px; }
                .subtitle { text-align: center; font-size: 12pt; margin-bottom: 10px; }
                .section-title { font-weight: bold; font-size: 12pt; margin-top: 20px; border-bottom: 1px solid #888; padding-bottom: 4px; }
                table { border-collapse: collapse; width: 100%; margin-top: 10px; }
                th, td { border: 1px solid #333; padding: 6px; font-size: 10pt; }
                th { background-color: #f2f2f2; }
                p { font-size: 10.5pt; line-height: 1.5; }
                .signature-line {
                    margin-top: 60px;
                    text-align: center;
                    font-size: 10pt;
                }
                .line {
                    width: 200px;
                    border-bottom: 1px solid #000;
                    margin: 0 auto 5px auto;
                }
                .label {
                    font-weight: bold;
                }
            </style>

            <div class="title">BARANGAY RESOURCE SHARING MANAGEMENT SYSTEM</div>
            <div class="subtitle">OFFICIAL REQUEST RECEIPT</div>

            <p><strong>Transaction No.:</strong> ' . $request_data['req_id'] . '</p>

            <div class="section-title">Barangay Resource Owner</div>
            <p><strong>Barangay:</strong> ' . htmlspecialchars($request_data['owner_brgy_name']) . '</p>

            <div class="section-title">Requester Information</div>
            <p>
            <strong>Name:</strong> ' . htmlspecialchars($request_data['requester_name']) . '<br />
            <strong>Barangay:</strong> ' . htmlspecialchars($request_data['requester_brgy_name']) . '
            </p>

            <div class="section-title">Resource Details</div>
            <table>
                <thead>
                    <tr>
                        <th>Resource</th>
                        <th>Description</th>
                        <th>Quantity</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>' . htmlspecialchars($request_data['res_name']) . '</td>
                        <td>' . (htmlspecialchars($request_data['res_description']) ?: 'N/A') . '</td>
                        <td>' . $request_data['req_quantity'] . '</td>
                    </tr>
                </tbody>
            </table>

            <div class="section-title">Request Summary</div>
            <p>
            <strong>Request Date:</strong> ' . date('F d, Y', strtotime($request_data['req_date'])) . '<br />
            <strong>Expected Return:</strong> ' . date('F d, Y', strtotime($request_data['return_date'])) . '<br />
            <strong>Purpose:</strong> ' . htmlspecialchars($request_data['req_purpose']) . '<br />
            <strong>Status:</strong> <span style="color:' . $status_color . ';"><strong>' . htmlspecialchars($new_request_status) . '</strong></span>
            </p>

            <div class="section-title">Approval & Certification</div>
            <p>
            This certifies that the above request for resource sharing has been duly reviewed and ' . htmlspecialchars(strtolower($new_request_status)) . ' by the authorized Barangay official.
            </p>
            <p>
            <strong>Approved By:</strong> Barangay ' . htmlspecialchars($request_data['owner_brgy_name']) . '<br />
            <strong>Date Issued:</strong> ' . date('F d, Y') . '
            </p>

            <div class="signature-line">
                <div class="line"></div>
                <div>' . htmlspecialchars("Hon. Captain of Barangay " . $request_data['owner_brgy_name']) . '</div>
            </div>
            ';

            $pdf->writeHTML($html, true, false, true, false, '');

            
            $pdf->AddPage();

            
            $pdf->AddWatermark('uploads/BRSMS.jpg');


            $pdf->SetFont('helvetica', 'B', 16);
            $pdf->Cell(0, 10, 'BRSMS REQUEST PROCESS GUIDE', 0, 1, 'C'); 
            $pdf->Ln(5);

            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 7, 'STEP 1 – VERIFICATION', 0, 1, 'L'); 
            $pdf->SetFont('helvetica', '', 11);
            $pdf->MultiCell(0, 6, 'Proceed to the Barangay Resource Desk to have your request verified. Present your printed request receipt. Make sure the verifier signs or stamps your receipt before going to the next step.', 0, 'L', 0, 1, '', '', true, 0, false, true, 0, 'T', false);
            $pdf->Ln(3);

            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 7, 'STEP 2 – APPROVAL', 0, 1, 'L'); 
            $pdf->SetFont('helvetica', '', 11);
            $pdf->MultiCell(0, 6, 'Submit your verified request receipt to the Approving Officer. The officer will check your request and confirm item availability. If approved, your request will be updated in the system and your receipt will be signed or stamped.', 0, 'L', 0, 1, '', '', true, 0, false, true, 0, 'T', false);
            $pdf->Ln(3);

            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 7, 'STEP 3 – ISSUANCE (BORROWED)', 0, 1, 'L'); 
            $pdf->SetFont('helvetica', '', 11);
            $pdf->MultiCell(0, 6, 'Go to the Issuance Section to claim your requested item/s. The system will automatically record the exact borrowed day and time together with the quantity. Confirm the details before leaving the desk.', 0, 'L', 0, 1, '', '', true, 0, false, true, 0, 'T', false);
            $pdf->Ln(3);

            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 7, 'STEP 4 – RETURN', 0, 1, 'L'); 
            $pdf->SetFont('helvetica', '', 11);
            $pdf->MultiCell(0, 6, 'Return the borrowed item/s on the approved return day and time. The system will log the returned timestamp once the item is received.', 0, 'L', 0, 1, '', '', true, 0, false, true, 0, 'T', false);
            $pdf->Ln(7);

            
            $pdf->SetFont('helvetica', 'B', 14);
            $pdf->Cell(0, 7, 'Important Reminders', 0, 1, 'L');
            $pdf->SetFont('helvetica', '', 11);
            $pdf->MultiCell(0, 6, '• Your request is scheduled on ' . date('F d, Y', strtotime($request_data['req_date'])) . ' ', 0, 'L', 0, 1, '', '', true, 0, false, true, 0, 'T', false);
            $pdf->MultiCell(0, 6, '• Be at the Barangay Hall 15–30 minutes before your borrowing time.', 0, 'L', 0, 1, '', '', true, 0, false, true, 0, 'T', false);
            $pdf->MultiCell(0, 6, '• Print your verified request receipt (downloadable from BRSMS or sent to your registered email).', 0, 'L', 0, 1, '', '', true, 0, false, true, 0, 'T', false);
            $pdf->MultiCell(0, 6, '• Requests not claimed within the scheduled day will be automatically cancelled.', 0, 'L', 0, 1, '', '', true, 0, false, true, 0, 'T', false);
            $pdf->MultiCell(0, 6, '• All items must be picked up at the Barangay Resource Desk. Courier or delivery services are not available.', 0, 'L', 0, 1, '', '', true, 0, false, true, 0, 'T', false);
            $pdf->Ln(5);

            
            $pdf->SetFont('helvetica', 'B', 14);
            $pdf->Cell(0, 7, 'Additional Notes', 0, 1, 'L');
            $pdf->SetFont('helvetica', '', 11);

            $pdf->MultiCell(0, 6, '• Requests are subject to barangay approval before release.', 0, 'L', 0, 1, '', '', true);
            $pdf->MultiCell(0, 6, '• Only available items can be requested.', 0, 'L', 0, 1, '', '', true);
            $pdf->MultiCell(0, 6, '• Borrowed items can only be requested again within the same day once the same items are returned, to prevent double booking and overlapping requests. However, advance requests for future dates are allowed if the items are still available.', 0, 'L', 0, 1, '', '', true);
            $pdf->MultiCell(0, 6, '• Handle all borrowed items with care and use them only for the stated purpose.', 0, 'L', 0, 1, '', '', true);
            $pdf->MultiCell(0, 6, '• Lost or damaged items must be reported immediately to the barangay office.', 0, 'L', 0, 1, '', '', true);
            $pdf->MultiCell(0, 6, '• Coordinate with barangay staff before claiming or returning requested items.', 0, 'L', 0, 1, '', '', true);
            $pdf->MultiCell(0, 6, '• Ensure timely return of borrowed items as delays may affect future requests.', 0, 'L', 0, 1, '', '', true);

            $pdf->Ln(7);


            
            $pdf->SetFont('helvetica', 'I', 10); 
            $pdf->Cell(0, 5, '"Sharing resources, building communities."', 0, 1, 'C'); 

            
            $pdf_output = $pdf->Output('request_receipt_' . $request_id . '.pdf', 'S');
            $filename = 'request_receipt_' . $request_id . '.pdf';

            
            $mail = new PHPMailer(true);
            try {
                
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'barangaysms@gmail.com';
                $mail->Password   = 'zwfhvcmbebampern';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                
                $mail->setFrom('no-reply@brms.com', 'BRSMS');
                $mail->addAddress($requester_email, $request_data['requester_name']);

                
                $mail->addStringAttachment($pdf_output, $filename, 'base64', 'application/pdf');

                
                $mail->isHTML(true);
                $mail->Subject = 'Your Resource Request #' . $request_id . ' Has Been Approved!';
                $mail->Body    = 'Dear ' . htmlspecialchars($request_data['requester_name']) . ',<br><br>'
                               . 'We are pleased to inform you that your request for the resource <strong>' . htmlspecialchars($request_data['res_name']) . '</strong> (Quantity: ' . $request_data['req_quantity'] . ') has been <strong>APPROVED</strong>.<br><br>'
                               . 'Please find your official receipt attached to this email. You may proceed to Barangay ' . htmlspecialchars($request_data['owner_brgy_name']) . ' to claim the resource.<br><br>'
                               . '<strong>Request Details:</strong><br>'
                               . 'Transaction No.: ' . $request_data['req_id'] . '<br>'
                               . 'Resource: ' . htmlspecialchars($request_data['res_name']) . '<br>'
                               . 'Quantity: ' . $request_data['req_quantity'] . '<br>'
                               . 'Request Date: ' . date('F d, Y', strtotime($request_data['req_date'])) . '<br>'
                               . 'Expected Return: ' . date('F d, Y', strtotime($request_data['return_date'])) . '<br>'
                               . 'Purpose: ' . htmlspecialchars($request_data['req_purpose']) . '<br><br>'
                               . 'Thank you for using the Barangay Resource Sharing Management System.';
                $mail->AltBody = 'Your resource request #' . $request_id . ' has been APPROVED. Please find the official receipt attached.';

                $mail->send();
                $message .= " PDF receipt sent to requester's email.";
            } catch (Exception $e) {
                error_log("Mailer Error (Request ID: {$request_id}): {$mail->ErrorInfo}");
                $message .= " However, failed to send PDF receipt to requester's email. Mailer Error: {$mail->ErrorInfo}";
            }

        } elseif ($action === 'borrow') {
            if ($current_req_status !== 'Approved') {
                throw new Exception("Request must be 'Approved' before marking as 'Borrowed'.");
            }

            if ($is_bulk) {
                
                if (count($item_ids) !== $req_quantity) {
                    throw new Exception("Selected item quantity (" . count($item_ids) . ") does not match requested quantity (" . $req_quantity . ").");
                }

                
                $placeholders = implode(',', array_fill(0, count($item_ids), '?'));
                $types = str_repeat('i', count($item_ids));

                $verify_items_stmt = $conn->prepare("
                    SELECT item_id FROM resource_items
                    WHERE res_id = ? AND item_status = 'Available' AND item_id IN ($placeholders) FOR UPDATE
                ");
                $verify_items_stmt->bind_param("i" . $types, $res_id, ...$item_ids);
                $verify_items_stmt->execute();
                $verified_items = $verify_items_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

                if (count($verified_items) !== $req_quantity) {
                    throw new Exception("Some selected items are not available or do not belong to this resource.");
                }

                
                $update_items_stmt = $conn->prepare("
                    UPDATE resource_items
                    SET item_status = 'Borrowed', current_req_id = ?
                    WHERE item_id IN ($placeholders)
                ");
                $update_items_stmt->bind_param("i" . $types, $request_id, ...$item_ids);
                if (!$update_items_stmt->execute()) {
                    throw new Exception("Failed to update individual item statuses to Borrowed: " . $conn->error);
                }
            } else {
                
                $update_item_stmt = $conn->prepare("
                    UPDATE resource_items
                    SET item_status = 'Borrowed', current_req_id = ?
                    WHERE res_id = ? AND item_status = 'Available'
                    LIMIT 1
                ");
                $update_item_stmt->bind_param("ii", $request_id, $res_id);
                if (!$update_item_stmt->execute()) {
                    throw new Exception("Failed to update single item status to Borrowed: " . $conn->error);
                }
            }

            
            $update_request_stmt = $conn->prepare("UPDATE requests SET req_status = ?, borrow_timestamp = NOW() WHERE req_id = ?");
            $update_request_stmt->bind_param("si", $new_request_status, $request_id);
            if (!$update_request_stmt->execute()) {
                throw new Exception("Failed to update request status: " . $conn->error);
            }

            
            logActivity($action_performer_user_id, "Request Borrowed", "Request ID: {$request_id} for '{$res_name}' marked as Borrowed.");
            $message = "Request marked as Borrowed.";

        } elseif ($action === 'reject') {
            if ($current_req_status !== 'Pending') {
                throw new Exception("Request is not pending and cannot be rejected.");
            }

            $update_request_stmt = $conn->prepare("UPDATE requests SET req_status = ? WHERE req_id = ?");
            $update_request_stmt->bind_param("si", $new_request_status, $request_id);
            if (!$update_request_stmt->execute()) {
                throw new Exception("Failed to update request status: " . $conn->error);
            }

            
            logRequestReject($action_performer_user_id, $request_id);
            $message = "Request marked as Rejected.";

        } elseif ($action === 'complete') {
            if ($current_req_status !== 'Borrowed') {
                throw new Exception("Request must be 'Borrowed' before marking as 'Completed'.");
            }

            
            $new_item_status = 'Available'; 
            $return_condition_for_db = $condition_processed; 

            if ($is_bulk) {
                $decoded_conditions = json_decode($condition_raw, true); 
                if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded_conditions)) {
                    throw new Exception("Invalid condition data for bulk return.");
                }

                $final_conditions_for_db = [];
                foreach ($decoded_conditions as $cond_item) {
                    $item_id_to_update = (int)$cond_item['item_id'];
                    $condition_text = htmlspecialchars(trim($cond_item['condition']));

                    $item_new_status = 'Available';
                    if (strcasecmp($condition_text, 'Damaged') === 0) {
                        $item_new_status = 'Under Maintenance';
                    } elseif (strcasecmp($condition_text, 'Lost') === 0) {
                        $item_new_status = 'Lost';
                    }

                    
                    $get_serial_stmt = $conn->prepare("SELECT serial_number FROM resource_items WHERE item_id = ?");
                    $get_serial_stmt->bind_param("i", $item_id_to_update);
                    $get_serial_stmt->execute();
                    $serial_result = $get_serial_stmt->get_result()->fetch_assoc();
                    $serial_number = $serial_result['serial_number'] ?? 'N/A';
                    $get_serial_stmt->close();

                    $update_item_stmt = $conn->prepare("
                        UPDATE resource_items
                        SET item_status = ?, current_req_id = NULL
                        WHERE item_id = ? AND current_req_id = ? AND item_status = 'Borrowed'
                    ");
                    $update_item_stmt->bind_param("sii", $item_new_status, $item_id_to_update, $request_id);
                    if (!$update_item_stmt->execute()) {
                        throw new Exception("Failed to update item {$item_id_to_update} status to {$item_new_status}: " . $conn->error);
                    }

                    $final_conditions_for_db[] = [
                        'item_id' => $item_id_to_update,
                        'serial_number' => $serial_number,
                        'condition' => $condition_text,
                        'new_status' => $item_new_status 
                    ];
                }
                $return_condition_for_db = json_encode($final_conditions_for_db); 
            } else {
                
                $condition_text = htmlspecialchars(trim($condition_raw));
                if (strcasecmp($condition_text, 'Damaged') === 0) {
                    $new_item_status = 'Under Maintenance';
                } elseif (strcasecmp($condition_text, 'Lost') === 0) {
                    $new_item_status = 'Lost';
                }

                $update_item_stmt = $conn->prepare("
                    UPDATE resource_items
                    SET item_status = ?, current_req_id = NULL
                    WHERE res_id = ? AND current_req_id = ? AND item_status = 'Borrowed'
                    LIMIT 1
                ");
                $update_item_stmt->bind_param("sii", $new_item_status, $res_id, $request_id);
                if (!$update_item_stmt->execute()) {
                    throw new Exception("Failed to update single item status to {$new_item_status}: " . $conn->error);
                }
                $return_condition_for_db = $condition_text; 
            }

            
            $return_date = date('Y-m-d');
            $insert_return_stmt = $conn->prepare("
                INSERT INTO returns
                (req_id, return_date, return_condition, brgy_id)
                VALUES (?, ?, ?, ?)
            ");
            $insert_return_stmt->bind_param("issi",
                $request_id,
                $return_date,
                $return_condition_for_db, 
                $resource_owner_brgy_id
            );
            if (!$insert_return_stmt->execute()) {
                throw new Exception("Failed to record return: " . $conn->error);
            }

            
            $update_request_stmt = $conn->prepare("UPDATE requests SET req_status = ?, return_timestamp = NOW() WHERE req_id = ?");
            $update_request_stmt->bind_param("si", $new_request_status, $request_id);
            if (!$update_request_stmt->execute()) {
                throw new Exception("Failed to update request status: " . $conn->error);
            }

            
            logRequestComplete($action_performer_user_id, $request_id);
            $message = "Request marked as Completed and items returned.";
        } elseif ($action === 'cancel') {
            if ($current_req_status !== 'Approved' && $current_req_status !== 'Pending') {
                throw new Exception("Request must be 'Approved' or 'Pending' to be cancelled.");
            }

            
            $borrow_timestamp_update = "";
            if ($current_req_status === 'Borrowed') {
                $borrow_timestamp_update = ", borrow_timestamp = NULL";
            }

            $update_request_stmt = $conn->prepare("UPDATE requests SET req_status = ? {$borrow_timestamp_update} WHERE req_id = ?");
            $update_request_stmt->bind_param("si", $new_request_status, $request_id);
            if (!$update_request_stmt->execute()) {
                throw new Exception("Failed to update request status to Cancelled: " . $conn->error);
            }

            
            if ($current_req_status === 'Borrowed') {
                if ($is_bulk) {
                    $borrowed_items_query = $conn->prepare("
                        SELECT item_id FROM resource_items
                        WHERE res_id = ? AND current_req_id = ? AND item_status = 'Borrowed'
                        LIMIT ? FOR UPDATE
                    ");
                    $borrowed_items_query->bind_param("iii", $res_id, $request_id, $req_quantity);
                    $borrowed_items_query->execute();
                    $items_to_return = $borrowed_items_query->get_result()->fetch_all(MYSQLI_ASSOC);

                    if (!empty($items_to_return)) {
                        $item_ids_to_return = array_column($items_to_return, 'item_id');
                        $placeholders_return = implode(',', array_fill(0, count($item_ids_to_return), '?'));
                        $types_return = str_repeat('i', count($item_ids_to_return));

                        $update_items_return_stmt = $conn->prepare("
                            UPDATE resource_items
                            SET item_status = 'Available', current_req_id = NULL
                            WHERE item_id IN ($placeholders_return)
                        ");
                        $update_items_return_stmt->bind_param($types_return, ...$item_ids_to_return);
                        if (!$update_items_return_stmt->execute()) {
                            throw new Exception("Failed to update individual item statuses to Available after cancellation: " . $conn->error);
                        }
                    }
                } else {
                    $update_item_stmt = $conn->prepare("
                        UPDATE resource_items
                        SET item_status = 'Available', current_req_id = NULL
                        WHERE res_id = ? AND current_req_id = ?
                        LIMIT 1
                    ");
                    $update_item_stmt->bind_param("ii", $res_id, $request_id);
                    if (!$update_item_stmt->execute()) {
                        throw new Exception("Failed to update single item status to Available after cancellation: " . $conn->error);
                    }
                }
            }

            
            logRequestCancel($action_performer_user_id, $request_id);
            $message = "Request marked as Cancelled.";
        }

        $conn->commit();
        sendJsonResponse('success', $message, $new_request_status);

    } catch (Exception $e) {
        $conn->rollback();
        error_log("Request Action Error (ID: {$request_id}, Action: {$action}): " . $e->getMessage());
        sendJsonResponse('error', $e->getMessage(), null, 400);
    }
} catch (Exception $e) {
    error_log("Global Error in process_request_action.php: " . $e->getMessage());
    sendJsonResponse('error', 'An unexpected error occurred: ' . $e->getMessage(), null, 500);
}
ob_clean();
?>
?>
