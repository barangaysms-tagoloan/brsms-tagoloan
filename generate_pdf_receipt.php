<?php
ob_start();
session_start();
require __DIR__ . '/logic/database/db.php';
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/tecnickcom/tcpdf/tcpdf.php';
require __DIR__ . '/logic/logging.php';

if (!isset($_GET['request_id'])) {
    ob_end_clean();
    die("Request ID is required");
}

$request_id = (int) $_GET['request_id'];
$source = isset($_GET['source']) ? $_GET['source'] : '';
$stmt = $conn->prepare("
    SELECT
        r.*,
        res.res_name, res.res_description,
        owner.brgy_name AS owner_brgy_name,
        requester.brgy_name AS requester_brgy_name,
        u.user_full_name AS requester_name
    FROM requests r
    JOIN resources res ON r.res_id = res.res_id
    JOIN barangays owner ON r.res_brgy_id = owner.brgy_id
    JOIN barangays requester ON r.req_brgy_id = requester.brgy_id
    JOIN users u ON r.req_user_id = u.user_id
    WHERE r.req_id = ? AND (r.req_status = 'Approved' OR r.req_status = 'Borrowed')
");
$stmt->bind_param("i", $request_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    ob_end_clean(); // Clean buffer before dying
    die("Request not found or not approved/borrowed.");
}

$request = $result->fetch_assoc();

// TCPDF Custom Header
class MYPDF extends TCPDF {
    public function Header() {
        // Header content for the first page
        if ($this->getPage() == 1) {
            $leftLogo = 'uploads/BRSMS.jpg';
            $rightLogo = 'uploads/tagoloan.jpg';

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

            // Watermark based on status and source for the first page
            $this->SetAlpha(0.08);
            $this->SetFont('helvetica', 'B', 50);
            $this->StartTransform();
            $this->Rotate(45, 105, 140);

            $watermark_text = '';
            if ($GLOBALS['source'] === 'owner') {
                $watermark_text = 'FOR VALIDATION';
            } elseif ($GLOBALS['source'] === 'requester') {
                if ($GLOBALS['request']['req_status'] === 'Approved' || $GLOBALS['request']['req_status'] === 'Borrowed') {
                    $watermark_text = 'APPROVED';
                } else {
                    $watermark_text = strtoupper($GLOBALS['request']['req_status']);
                }
            } else {
                $watermark_text = strtoupper($GLOBALS['request']['req_status']);
            }

            $this->Text(30, 160, $watermark_text);
            $this->StopTransform();
            $this->SetAlpha(1);
        }
    }

    // Override Footer to remove page numbers if desired, or keep default
    public function Footer() {
        // Default footer behavior (e.g., page number)
        // $this->SetY(-15);
        // $this->SetFont('helvetica', 'I', 8);
        // $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }

    // Custom method to add watermark
    public function AddWatermark($imagePath) {
        if (file_exists($imagePath)) {
            $this->SetAlpha(0.08); // Low opacity
            $this->Image($imagePath, $this->GetPageWidth() / 2 - 60, $this->GetPageHeight() / 2 - 60, 120, 120, '', '', '', false, 300, '', false, false, 0, false, false, false);
            $this->SetAlpha(1); // Reset alpha
        }
    }
}

// Pass the $request and $source variables to the global scope for TCPDF Header to access
$GLOBALS['request'] = $request;
$GLOBALS['source'] = $source;

$pdf = new MYPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator('BRMS');
$pdf->SetAuthor('BRMS System');
$pdf->SetTitle('Request Receipt');
$pdf->SetMargins(15, 40, 15); // Margins for the first page (with header)
$pdf->AddPage();

// Determine status color for HTML
$status_color = 'green';
if ($request['req_status'] === 'Borrowed') {
    $status_color = 'orange';
} elseif ($request['req_status'] === 'Rejected' || $request['req_status'] === 'Cancelled') {
    $status_color = 'red';
} elseif ($request['req_status'] === 'Pending') {
    $status_color = 'darkgoldenrod';
} elseif ($request['req_status'] === 'Completed') {
    $status_color = 'blue';
}


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

<p><strong>Transaction No.:</strong> ' . $request['req_id'] . '</p>

<div class="section-title">Barangay Resource Owner</div>
<p><strong>Barangay:</strong> ' . htmlspecialchars($request['owner_brgy_name']) . '</p>

<div class="section-title">Requester Information</div>
<p>
<strong>Name:</strong> ' . htmlspecialchars($request['requester_name']) . '<br />
<strong>Barangay:</strong> ' . htmlspecialchars($request['requester_brgy_name']) . '
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
            <td>' . htmlspecialchars($request['res_name']) . '</td>
            <td>' . (htmlspecialchars($request['res_description']) ?: 'N/A') . '</td>
            <td>' . $request['req_quantity'] . '</td>
        </tr>
    </tbody>
</table>

<div class="section-title">Request Summary</div>
<p>
<strong>Request Date:</strong> ' . date('F d, Y', strtotime($request['req_date'])) . '<br />
<strong>Expected Return:</strong> ' . date('F d, Y', strtotime($request['return_date'])) . '<br />
<strong>Purpose:</strong> ' . htmlspecialchars($request['req_purpose']) . '<br />
<strong>Status:</strong> <span style="color:' . $status_color . ';"><strong>' . htmlspecialchars($request['req_status']) . '</strong></span>
</p>

<div class="section-title">Approval & Certification</div>
<p>
This certifies that the above request for resource sharing has been duly reviewed and ' . htmlspecialchars(strtolower($request['req_status'])) . ' by the authorized Barangay official.
</p>
<p>
<strong>Approved By:</strong> Barangay ' . htmlspecialchars($request['owner_brgy_name']) . '<br />
<strong>Date Issued:</strong> ' . date('F d, Y') . '
</p>

<div class="signature-line">
    <div class="line"></div>
    <div>' . htmlspecialchars("Hon. Captain of Barangay " . $request['owner_brgy_name']) . '</div>
</div>
';

$pdf->writeHTML($html, true, false, true, false, '');

// Add a new page for instructions
$pdf->AddPage();

// Add BRSMS logo watermark to the second page
$pdf->AddWatermark('uploads/BRSMS.jpg');


$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'BRSMS REQUEST PROCESS GUIDE', 0, 1, 'C'); // Capitalized title
$pdf->Ln(5);

$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 7, 'STEP 1 – VERIFICATION', 0, 1, 'L'); // Capitalized step title
$pdf->SetFont('helvetica', '', 11);
$pdf->MultiCell(0, 6, 'Proceed to the Barangay Resource Desk to have your request verified. Present your printed request receipt. Make sure the verifier signs or stamps your receipt before going to the next step.', 0, 'L', 0, 1, '', '', true, 0, false, true, 0, 'T', false);
$pdf->Ln(3);

$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 7, 'STEP 2 – APPROVAL', 0, 1, 'L'); // Capitalized step title
$pdf->SetFont('helvetica', '', 11);
$pdf->MultiCell(0, 6, 'Submit your verified request receipt to the Approving Officer. The officer will check your request and confirm item availability. If approved, your request will be updated in the system and your receipt will be signed or stamped.', 0, 'L', 0, 1, '', '', true, 0, false, true, 0, 'T', false);
$pdf->Ln(3);

$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 7, 'STEP 3 – ISSUANCE (BORROWED)', 0, 1, 'L'); // Capitalized step title
$pdf->SetFont('helvetica', '', 11);
$pdf->MultiCell(0, 6, 'Go to the Issuance Section to claim your requested item/s. The system will automatically record the exact borrowed day and time together with the quantity. Confirm the details before leaving the desk.', 0, 'L', 0, 1, '', '', true, 0, false, true, 0, 'T', false);
$pdf->Ln(3);

$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 7, 'STEP 4 – RETURN', 0, 1, 'L'); // Capitalized step title
$pdf->SetFont('helvetica', '', 11);
$pdf->MultiCell(0, 6, 'Return the borrowed item/s on the approved return day and time. The system will log the returned timestamp once the item is received.', 0, 'L', 0, 1, '', '', true, 0, false, true, 0, 'T', false);
$pdf->Ln(7);

// Important Reminders
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 7, 'Important Reminders', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 11);
$pdf->MultiCell(0, 6, '• Your request is scheduled on ' . date('F d, Y', strtotime($request['req_date'])) . ' ', 0, 'L', 0, 1, '', '', true, 0, false, true, 0, 'T', false);
$pdf->MultiCell(0, 6, '• Be at the Barangay Hall 15–30 minutes before your borrowing time.', 0, 'L', 0, 1, '', '', true, 0, false, true, 0, 'T', false);
$pdf->MultiCell(0, 6, '• Print your verified request receipt (downloadable from BRSMS or sent to your registered email).', 0, 'L', 0, 1, '', '', true, 0, false, true, 0, 'T', false);
$pdf->MultiCell(0, 6, '• Requests not claimed within the scheduled day will be automatically cancelled.', 0, 'L', 0, 1, '', '', true, 0, false, true, 0, 'T', false);
$pdf->MultiCell(0, 6, '• All items must be picked up at the Barangay Resource Desk. Courier or delivery services are not available.', 0, 'L', 0, 1, '', '', true, 0, false, true, 0, 'T', false);
$pdf->Ln(5);

// Additional Notes
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 7, 'Additional Notes', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 11);

$pdf->MultiCell(0, 6, '• Requests are subject to barangay approval before release.', 0, 'L', 0, 1, '', '', true);
$pdf->MultiCell(0, 6, '• Only available items can be requested.', 0, 'L', 0, 1, '', '', true);
$pdf->MultiCell(0, 6, '• Borrowed resource can only be requested again within the same day once the same resource are returned, to prevent double booking and overlapping requests. However, advance requests for future dates are allowed if the resource are still available.', 0, 'L', 0, 1, '', '', true);
$pdf->MultiCell(0, 6, '• Handle all borrowed items with care and use them only for the stated purpose.', 0, 'L', 0, 1, '', '', true);
$pdf->MultiCell(0, 6, '• Lost or damaged items must be reported immediately to the barangay office.', 0, 'L', 0, 1, '', '', true);
$pdf->MultiCell(0, 6, '• Coordinate with barangay staff before claiming or returning requested items.', 0, 'L', 0, 1, '', '', true);
$pdf->MultiCell(0, 6, '• Ensure timely return of borrowed items as delays may affect future requests.', 0, 'L', 0, 1, '', '', true);

$pdf->Ln(7);


// Add the motto
$pdf->SetFont('helvetica', 'I', 10); // Italic font for the motto
$pdf->Cell(0, 5, '"Sharing resources, building communities."', 0, 1, 'C'); // Centered motto

ob_end_clean(); // Clean any buffered output before sending PDF
$pdf->Output('request_receipt_' . $request_id . '.pdf', 'I');
?>
