<?php
require "../logic/requests/request_logic.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Request Resources - BRSMS</title>
    <link rel="icon" type="image/png" href="../uploads/BRSMS.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <!-- jQuery UI Datepicker CSS -->
    <link rel="stylesheet" href="//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="../assets/css/request.css">
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar - COPIED FROM DASHBOARD.PHP -->
        <div class="col-md-3 col-lg-2 sidebar d-flex flex-column">
            <div class="logo-container">
                <div class="logo-circle">
                    <img src="../uploads/BRSMS.png" alt="BRSMS Logo">
                </div>
                <div class="logo-text">BRSMS</div>
            </div>
            <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="inventory.php"><i class="fas fa-boxes"></i> Inventory</a>
            <a href="request.php" class="active"><i class="fas fa-hand-holding"></i> Request Resource</a>
            <a href="request_status.php"><i class="fas fa-history"></i> Request Status</a>
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
        <div class="col-md-9 col-lg-10 p-4 main-content" id="mainContentWrapper">
            <h1 class="dashboard-title">Request Resource</h1>

            <!-- Content Area below Top Bar -->
            <div class="content-area">
                <!-- Section for Partner Barangays -->
                <div id="partnerBarangaysSection" class="resources-main-container">
                    <div class="request-guide">
                        <h6><i class="fas fa-info-circle"></i> How to Request Resources</h6>
                        <ul>
                            <li>Select a partner barangay from the list below to view their available resources.</li>
                            <li>Use the search bar to quickly find a specific barangay.</li>
                            <li>Once you've selected a barangay, you'll see a list of their resources that you can request.</li>
                        </ul>
                    </div>
                    <div class="barangay-search-container">
                        <div class="search-input-group">
                            <i class="fas fa-search"></i>
                            <input type="text" class="form-control" id="partnerBrgySearchInput" placeholder="Search partner barangays...">
                        </div>
                    </div>
                    <div class="barangay-card-grid" id="partnerBarangaysGrid">
                        <?php if ($partner_barangays_result->num_rows > 0) : ?>
                            <?php while ($barangay = $partner_barangays_result->fetch_assoc()) : ?>
                                <div class="barangay-card" data-brgy-name="<?= htmlspecialchars($barangay['brgy_name']) ?>">
                                    <img src="../uploads/tagoloan.png" alt="Barangay Image" class="brgy-image">
                                    <h5><?= htmlspecialchars($barangay['brgy_name']) ?></h5>
                                    <button class="btn btn-view-resources" onclick="loadResources(<?= $barangay['brgy_id'] ?>, '<?= htmlspecialchars($barangay['brgy_name']) ?>')">
                                        View Resources
                                    </button>
                                </div>
                            <?php endwhile; ?>
                        <?php else : ?>
                            <div class="no-resources-message col-12">
                                <i class="fas fa-exclamation-circle fa-3x mb-3 text-secondary"></i>
                                <h5>No Partner Barangays Found</h5>
                                <p class="mb-0">There are no other barangays registered in the system to request resources from.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Section for Resources from Selected Barangay -->
                <div id="resourcesFromSelectedBarangaySection" class="resources-main-container">
                    <div class="resources-view-header revised">
                        <div class="header-left">
                            <h4 id="selectedBrgyHeader">RESOURCES FROM: <span id="selectedBrgyNameDisplay"></span></h4>
                            <div class="search-input-group">
                                <i class="fas fa-search"></i>
                                <input type="text" class="form-control" id="resourceSearchInput" placeholder="Search resources...">
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <div class="view-toggle-buttons">
                                <button class="btn btn-cards-view active" id="cardsViewBtn" onclick="toggleResourceView('cards')"><i class="fas fa-th-large"></i> Cards</button>
                                <button class="btn btn-table-view" id="tableViewBtn" onclick="toggleResourceView('table')"><i class="fas fa-list-ul"></i> Table</button>
                            </div>
                            <button class="btn btn-back" onclick="showPartnerBarangays()">
                                <i class="fas fa-home py-2"></i> Back to Barangay
                            </button>
                        </div>
                    </div>
                    <div id="resourcesContainer" class="resources-grid-container">
                        <!-- Resources will be loaded here by JavaScript -->
                        <div class="no-resources-message">
                            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <h5 class="mt-3 text-muted">Loading resources...</h5>
                        </div>
                    </div>
                </div>

                <!-- Request Form Container (formerly a modal) -->
                <div id="requestFormContainer" class="resources-main-container">
                    <form id="requestForm" method="POST">
                        <div class="form-header">
                            <h5 class="form-title" id="requestFormTitle">Request Resource Form</h5>
                            <button type="button" class="btn-close btn-close-white" aria-label="Close" onclick="hideRequestForm()"></button>
                        </div>
                        <div class="form-body">
                            <input type="hidden" name="res_id" id="modalResourceId">
                            <input type="hidden" name="brgy_id" id="modalBrgyId">
                            <input type="hidden" name="requester_id" value="<?= $_SESSION['user_id'] ?>">
                            <input type="hidden" name="requester_brgy_id" value="<?= $current_brgy_id ?>">

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Requester Name:</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($current_user_name) ?>" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">From Barangay:</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($current_brgy_name) ?>" readonly>
                                </div>
                            </div>

                            <hr class="my-3">

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Resource Owner Barangay:</label>
                                    <input type="text" class="form-control" id="modalResourceOwner" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Resource Name:</label>
                                    <input type="text" class="form-control" id="modalResourceName" readonly>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Available Quantity:</label>
                                    <input type="text" class="form-control" id="modalAvailableQtyDisplay" readonly>
                                    <input type="hidden" id="modalOriginalTotalQty"> <!-- Store original total quantity -->
                                </div>
                                <div class="col-md-6">
                                    <label for="requestQuantity" class="form-label fw-semibold">Quantity to Request:<span class="required-asterisk">*</span></label>
                                    <input type="number" class="form-control" id="requestQuantity" name="req_quantity" min="1" value="1" required>
                                    <div class="invalid-feedback" id="quantityFeedback"></div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="requestDate" class="form-label fw-semibold">Request Date:<span class="required-asterisk">*</span></label>
                                    <input type="text" class="form-control datepicker" id="requestDate" name="req_date_display" required>
                                    <div class="invalid-feedback" id="requestDateFeedback">Please select a date for borrowing.</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="returnDate" class="form-label fw-semibold">Expected Return Date:<span class="required-asterisk">*</span></label>
                                    <input type="text" class="form-control datepicker" id="returnDate" name="return_date_display" required>
                                    <div class="invalid-feedback" id="returnDateFeedback">Please select a date for returning.</div>
                                </div>
                            </div>
                            <div class="alert alert-danger d-none" id="dateRangeError"></div>

                            <div class="mb-3">
                                <label for="requestContactNumber" class="form-label fw-semibold">Contact Number:<span class="required-asterisk">*</span></label>
                                <input type="text" class="form-control" id="requestContactNumber" name="req_contact_number" required placeholder="e.g., 09123456789">
                                <div class="invalid-feedback" id="contactNumberFeedback"></div>
                            </div>

                            <div class="mb-3">
                                <label for="requestPurpose" class="form-label fw-semibold">Purpose of Request:<span class="required-asterisk">*</span></label>
                                <textarea class="form-control" id="requestPurpose" name="req_purpose" rows="4" required></textarea>
                                <div class="invalid-feedback" id="purposeFeedback"></div>
                            </div>

                            <div class="form-check mt-3">
                                <input class="form-check-input" type="checkbox" id="acceptTermsCheckbox" disabled>
                                <label class="form-check-label text-success-custom" for="acceptTermsCheckbox">
                                    I accept the <a href="#" data-bs-toggle="modal" data-bs-target="#termsAndConditionsModal">terms and conditions</a>
                                </label>
                            </div>
                        </div>
                        <div class="form-footer">
                            <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                                <span id="submitText">Submit Request</span>
                                <span id="submitSpinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <!-- Footer - COPIED FROM DASHBOARD.PHP -->
            <footer class="footer mt-auto py-1">
                <div class="container-fluid">
                    <span>&copy; <?= date('Y') ?> BRSMS. All rights reserved.</span>
                </div>
            </footer>
        </div>
    </div>

    <!-- Notice Modal - Enhanced Version -->
    <div class="modal fade" id="noticeModal" tabindex="-1" aria-labelledby="noticeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="noticeModalLabel">OFFICIAL ADVISORY: RESOURCE REQUEST GUIDELINES</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="notice-intro-box">
                        <p class="mb-0">This advisory outlines the essential guidelines for submitting resource requests through the Barangay Resource Sharing Management System (BRSMS). Adherence to these principles ensures efficient and responsible resource allocation.</p>
                    </div>

                    <h6>Key Considerations for Requesting Barangays:</h6>
                    <ul>
                        <li><span class="important-text">Inter-Barangay Collaboration:</span> All requests are directed to <span class="important-text">partner barangays</span>, not your own. This system facilitates mutual aid and resource sharing across the municipality.</li>
                        <li><span class="important-text">Approval Discretion:</span> Submission of a request does not constitute automatic approval. All requests are subject to the <span class="important-text">review and final approval</span> of the resource-owning barangay, based on their internal policies and resource availability.</li>
                        <li><span class="important-text">Stewardship and Accountability:</span> The requesting barangay assumes full responsibility for the <span class="important-text">proper care, security, and timely return</span> of all borrowed resources. Any damage, loss, or deviation from the agreed-upon terms must be promptly reported.</li>
                        <li><span class="important-text">Accuracy of Information:</span> It is imperative that all details provided in your request, including requested quantity, specific dates, and the stated purpose, are <span class="important-text">accurate, complete, and truthful</span>. Inaccurate submissions may lead to delays or rejection.</li>
                    </ul>
                    <p class="mb-0">By proceeding, you acknowledge your understanding and acceptance of these guidelines.</p>

                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" id="confirmNotice">
                        <label class="form-check-label" for="confirmNotice">
                            I have read and understood the above guidelines.
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary btn-continue" id="continueToRequestBtn" disabled>Proceed to Request Form</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Terms and Conditions Modal -->
    <div class="modal fade" id="termsAndConditionsModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="termsAndConditionsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="termsAndConditionsModalLabel">Terms and Conditions</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="terms-intro-box">
                        <p class="mb-0">This document outlines the official terms and conditions governing the utilization of the Barangay Resource Sharing Management System (BRSMS) for inter-barangay resource allocation within the Municipality of Tagoloan. Adherence to these provisions is mandatory for all participating entities.</p>
                    </div>

                    <h6>1. Purpose and Mandate</h6>
                    <ul>
                        <li><span class="important-text">Objective:</span> The BRSMS is established to facilitate the efficient and equitable sharing of municipal resources (e.g., equipment, tools, facilities, personnel) among barangays to support public service initiatives, community development projects, and emergency response operations.</li>
                        <li><span class="important-text">Applicability:</span> These terms and conditions apply to all barangays, their authorized representatives, and personnel involved in requesting or providing resources through this system.</li>
                    </ul>

                    <h6>2. Eligibility and Authorization for Resource Requests</h6>
                    <ul>
                        <li><span class="important-text">Authorized Requesters:</span> Only duly authorized officials or designated personnel of registered barangays, as recognized by the Municipal Government of Tagoloan, are permitted to submit resource requests.</li>
                        <li><span class="important-text">Legitimate Purpose:</span> All requests must be for official barangay-related functions, aligned with public welfare, and shall not be utilized for personal gain or unauthorized activities.</li>
                    </ul>

                    <h6>3. Resource Availability, Allocation, and Approval Process</h6>
                    <ul>
                        <li><span class="important-text">Discretion of Owning Barangay:</span> The availability and allocation of resources are subject to the sole discretion of the resource-owning barangay, considering their operational needs, existing commitments, and inventory status.</li>
                        <li><span class="important-text">Review and Approval:</span> All resource requests undergo a rigorous review process by the authorized personnel of the owning barangay. Approval is not automatic and is contingent upon compliance with established policies and resource capacity.</li>
                        <li><span class="important-text">Right to Decline:</span> The resource-owning barangay reserves the unequivocal right to decline any request without obligation to provide specific justification.</li>
                    </ul>

                    <h6>4. Accuracy of Information and Accountability</h6>
                    <ul>
                        <li><span class="important-text">Veracity of Data:</span> It is incumbent upon the requesting barangay to ensure that all information provided in the request form, including but not limited to resource type, quantity, specific dates, and stated purpose, is <span class="important-text">accurate, complete, and truthful</span>.</li>
                        <li><span class="important-text">Consequences of Misrepresentation:</span> Any deliberate misrepresentation or submission of false information shall result in the immediate rejection of the current request, potential suspension of future borrowing privileges, and may lead to administrative or legal action.</li>
                    </ul>

                    <h6>5. Stewardship, Usage, and Reporting Obligations</h6>
                    <ul>
                        <li><span class="important-text">Exclusive Use:</span> Requested resources shall be utilized strictly for the purpose explicitly stated in the approved request.</li>
                        <li><span class="important-text">Custodial Responsibility:</span> The requesting barangay assumes full and unequivocal responsibility for the proper care, maintenance, security, and safekeeping of the borrowed resource from the moment of its acquisition until its verified return.</li>
                        <li><span class="important-text">Incident Reporting:</span> Any damage, loss, malfunction, or deviation from the agreed-upon terms concerning the resource while under the requesting barangay's custody must be reported immediately and comprehensively to the resource-owning barangay and the BRSMS administration.</li>
                    </ul>

                    <h6>6. Resource Return Protocol and Condition Assessment</h6>
                    <ul>
                        <li><span class="important-text">Timely Return:</span> Resources must be returned precisely on or before the stipulated "Expected Return Date" as agreed upon in the approved request.</li>
                        <li><span class="important-text">Condition upon Return:</span> Resources shall be returned in the identical condition as received, accounting for reasonable wear and tear attributable to normal and intended use.</li>
                        <li><span class="important-text">Inspection and Liability:</span> The resource-owning barangay shall conduct a thorough inspection upon return. Any damage exceeding reasonable wear and tear, or instances of loss, shall be assessed, and the requesting barangay shall be held liable for the full cost of repair or replacement.</li>
                        <li><span class="important-text">Sanctions for Non-Compliance:</span> Failure to return resources punctually without prior, documented arrangement, or returning resources in an unacceptable condition, may result in the imposition of penalties, including but not limited to, suspension of borrowing privileges and formal notification to the Municipal Government.</li>
                    </ul>

                    <h6>7. Dispute Resolution and Compliance Monitoring</h6>
                    <ul>
                        <li><span class="important-text">Record Keeping:</span> Both the requesting and resource-owning barangays are mandated to maintain meticulous records of all resource sharing transactions.</li>
                        <li><span class="important-text">Conflict Resolution:</span> In the event of disputes pertaining to resource condition, return, or usage, both parties are obligated to engage in good-faith discussions to achieve an amicable resolution. Should a resolution not be reached, the matter may be escalated to the relevant department of the Municipal Government of Tagoloan for mediation and final decision.</li>
                    </ul>

                    <h6>8. Data Privacy and System Integrity</h6>
                    <ul>
                        <li><span class="important-text">Confidentiality:</span> All personal and barangay-specific information collected through the BRSMS shall be used exclusively for the purpose of facilitating resource sharing and system administration, in strict adherence to applicable data privacy laws and regulations.</li>
                        <li><span class="important-text">System Security:</span> Users are prohibited from attempting to compromise the integrity, security, or functionality of the BRSMS.</li>
                    </ul>

                    <h6>9. Amendments and Revisions</h6>
                    <p>These Official Terms and Conditions may be subject to review, update, or amendment by the BRSMS administrators and the Municipal Government of Tagoloan at any time. Significant revisions shall be communicated to all registered users, and continued utilization of the system shall constitute implicit acceptance of the revised terms.</p>

                    <p class="mb-0">By clicking "I Agree," you formally acknowledge your comprehensive understanding and unconditional acceptance of all the Official Terms and Conditions stipulated herein.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-cancel" onclick="redirectToDashboard()">Cancel</button>
                    <button type="button" class="btn btn-primary btn-agree" id="agreeButton" disabled>I Agree</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Custom Alert Modal Structure (for success/error messages) -->
    <div class="custom-modal-overlay" id="customAlertModalOverlay">
        <div class="custom-modal" id="customAlertModal">
            <button type="button" class="close-btn" aria-label="Close" onclick="hideCustomAlert()">
                <i class="fas fa-times"></i>
            </button>
            <div class="modal-icon" id="customAlertIcon"></div>
            <h4 class="modal-title" id="customAlertTitle"></h4>
            <p class="modal-message" id="customAlertMessage"></p>
            <div class="modal-actions">
                <button class="btn-primary-action" id="customAlertPrimaryBtn" onclick="hideCustomAlert()">OK</button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
    <script src="../assets/js/request.js"></script>
    <script> 
            let currentSelectedBrgyId = <?= json_encode($selected_brgy_id) ?>;
            let currentSelectedBrgyName = <?= json_encode($selected_brgy_name) ?>;
    </script>
</body>
</html>
