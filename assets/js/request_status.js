function showCustomAlert(type, title, message, actions) {
    const overlay = document.getElementById('unifiedCustomAlertOverlay');
    const modal = document.getElementById('unifiedCustomAlertModal');
    const iconDiv = modal.querySelector('.modal-icon');
    const titleDiv = modal.querySelector('.modal-title');
    const messageDiv = modal.querySelector('.modal-message');
    const actionsDiv = modal.querySelector('.modal-actions');

    // Reset classes and content
    modal.classList.remove('success', 'error', 'warning', 'info', 'borrowed', 'cancelled', 'confirm-remove');
    iconDiv.className = 'modal-icon'; // Reset icon class
    actionsDiv.innerHTML = ''; // Clear previous buttons

    // Set type-specific styles and icon
    if (type === 'success') {
        modal.classList.add('success');
        iconDiv.innerHTML = '<i class="fas fa-check-circle"></i>';
    } else if (type === 'error') {
        modal.classList.add('error');
        iconDiv.innerHTML = '<i class="fas fa-times-circle"></i>';
    } else if (type === 'warning') {
        modal.classList.add('warning');
        iconDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i>';
    } else if (type === 'info') {
        modal.classList.add('info');
        iconDiv.innerHTML = '<i class="fas fa-info-circle"></i>';
    } else if (type === 'borrowed') {
        modal.classList.add('borrowed');
        iconDiv.innerHTML = '<i class="fas fa-hand-holding"></i>';
    } else if (type === 'cancelled') {
        modal.classList.add('cancelled');
        iconDiv.innerHTML = '<i class="fas fa-ban"></i>';
    } else if (type === 'confirm-remove') { // New type for remove confirmation
        modal.classList.add('error'); // Using error style for removal confirmation
        iconDiv.innerHTML = '<i class="fas fa-trash-alt"></i>';
    }

    titleDiv.textContent = title;
    messageDiv.innerHTML = message; // Use innerHTML for message to allow strong tag

    // Add action buttons
    actions.forEach(action => {
        const button = document.createElement('button');
        button.textContent = action.text;
        button.classList.add(action.class);
        button.addEventListener('click', () => {
            if (action.callback) {
                action.callback();
            }
            hideCustomAlert();
        });
        actionsDiv.appendChild(button);
    });

    overlay.classList.add('show');
}

function hideCustomAlert() {
    document.getElementById('unifiedCustomAlertOverlay').classList.remove('show');
}

// Close modal on overlay click
document.getElementById('unifiedCustomAlertOverlay').addEventListener('click', (e) => {
    if (e.target === document.getElementById('unifiedCustomAlertOverlay')) {
        hideCustomAlert();
    }
});

document.addEventListener('DOMContentLoaded', function() {
    // Display PHP messages on page load using the custom alert
    const phpSuccessMessage = document.getElementById('php_success_message').value;
    const phpErrorMessage = document.getElementById('php_error_message').value;

    if (phpSuccessMessage) {
        showCustomAlert('success', 'Success!', phpSuccessMessage, [{ text: 'OK', class: 'btn-primary-action' }]);
    } else if (phpErrorMessage) {
        showCustomAlert('error', 'Error!', phpErrorMessage, [{ text: 'OK', class: 'btn-primary-action' }]);
    }

    // Helper function for status colors
    function getStatusColorJS(status) {
        const status_lower = status.toLowerCase();
        switch(status_lower) {
            case 'pending': return 'pending';
            case 'approved': return 'approved';
            case 'rejected': return 'rejected';
            case 'completed': return 'completed';
            case 'borrowed': return 'borrowed';
            case 'cancelled': return 'cancelled';
            default: return 'secondary';
        }
    }

    // View details button handler
    document.querySelectorAll('.view-details').forEach(button => {
        button.addEventListener('click', function() {
            const request = JSON.parse(this.getAttribute('data-details'));
            const modalContent = document.getElementById('modalDetailsContent');
            const photo_src = request.res_photo ? '../logic/inventory/uploads/' + request.res_photo : 'images/default-item.jpg';
            const status_class = getStatusColorJS(request.req_status); // Use req_status for consistency

            modalContent.innerHTML = `
                <div class="row">
                    <div class="col-md-5">
                        <div class="resource-image-container">
                            <img src="${photo_src}" alt="Resource Photo">
                        </div>
                        <div class="resource-details">
                            <h6><i class="fas fa-box me-2"></i> Resource Details</h6>
                            <div class="detail-item">
                                <strong>Resource Name:</strong>
                                <span>${request.res_name}</span>
                            </div>
                            <div class="detail-item">
                                <strong>Resource Type:</strong>
                                <span>${request.is_bulk == 1 ? 'Bulk (Multiple Items)' : 'Single Item'}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div class="request-details">
                            <h6><i class="fas fa-hand-holding me-2"></i> Request Information</h6>
                            <div class="detail-item">
                                <strong>Request ID:</strong>
                                <span>#${request.req_id}</span>
                            </div>
                            <div class="detail-item">
                                <strong>Requester Name:</strong>
                                <span>${request.requester_name}</span>
                            </div>
                            <div class="detail-item">
                                <strong>Requester Barangay:</strong>
                                <span>${request.requester_brgy_name}</span>
                            </div>
                            <div class="detail-item">
                                <strong>Quantity Requested:</strong>
                                <span>${request.req_quantity}</span>
                            </div>
                            <div class="detail-item">
                                <strong>Request Date:</strong>
                                <span>${formatDate(request.req_date)}</span>
                            </div>
                            <div class="detail-item">
                                <strong>Expected Return Date:</strong>
                                <span>${formatDate(request.return_date)}</span>
                            </div>
                            <div class="detail-item">
                                <strong>Contact Number:</strong>
                                <span>${request.req_contact_number || 'N/A'}</span>
                            </div>
                            <div class="detail-item status-info">
                                <strong>Status:</strong>
                                <span class="badge rounded-pill badge-${status_class}">
                                    ${request.req_status}
                                </span>
                            </div>
                            <div class="detail-item">
                                <strong>Purpose:</strong>
                                <span class="purpose-text">${request.req_purpose || 'N/A'}</span>
                            </div>
                            ${(request.req_status === 'Rejected' || request.req_status === 'Cancelled') && request.reject_reason ? `
                            <div class="detail-item text-danger">
                                <strong>Reason for ${request.req_status}:</strong>
                                <span class="purpose-text">${request.reject_reason}</span>
                            </div>` : ''}
                            ${request.req_status === 'Completed' && request.return_condition ? `
                            <div class="detail-item">
                                <strong>Return Condition:</strong>
                                <span class="purpose-text">${request.return_condition}</span>
                            </div>` : ''}
                            ${request.req_status === 'Approved' || request.req_status === 'Borrowed' ? `
                            <div class="text-center mt-4">
                                <a href="../generate_pdf_receipt.php?request_id=${request.req_id}&source=requester" class="btn btn-success text-white">
                                    <i class="fas fa-file-pdf me-2"></i> Generate PDF Receipt
                                </a>
                            </div>
                            ` : ''}
                        </div>
                    </div>
                </div>
            `;
            // Manually show the Bootstrap modal
            const detailsModal = new bootstrap.Modal(document.getElementById('detailsModal'));
            detailsModal.show();
        });
    });

    // Cancel request button handler
    document.querySelectorAll('.cancel-request').forEach(button => {
        button.addEventListener('click', function() {
            const reqId = this.getAttribute('data-reqid');
            showCustomAlert(
                'cancelled',
                'Confirm Cancellation',
                `Are you sure you want to cancel this request? This action cannot be undone.`,
                [
                    { text: 'Yes, Cancel Request', class: 'btn-primary-action', callback: () => {
                        // Submit the form when confirmed
                        const cancelForm = document.createElement('form');
                        cancelForm.method = 'POST';
                        cancelForm.action = 'request_status.php';
                        
                        const reqIdInput = document.createElement('input');
                        reqIdInput.type = 'hidden';
                        reqIdInput.name = 'req_id';
                        reqIdInput.value = reqId;
                        cancelForm.appendChild(reqIdInput);

                        const cancelRequestInput = document.createElement('input');
                        cancelRequestInput.type = 'hidden';
                        cancelRequestInput.name = 'cancel_request';
                        cancelRequestInput.value = '1';
                        cancelForm.appendChild(cancelRequestInput);

                        document.body.appendChild(cancelForm);
                        cancelForm.submit();
                    }},
                    { text: 'No, Keep It', class: 'btn-secondary-action' }
                ]
            );
        });
    });

    // --- Persistence Logic for Hiding Cards ---
    const HIDDEN_REQUESTS_KEY = 'hiddenRequestIds';
    let hiddenRequestIds = JSON.parse(localStorage.getItem(HIDDEN_REQUESTS_KEY)) || [];

    function applyHiddenState() {
        document.querySelectorAll('.request-card-wrapper').forEach(wrapper => {
            const reqId = wrapper.getAttribute('data-reqid');
            if (hiddenRequestIds.includes(reqId)) {
                wrapper.style.display = 'none';
            } else {
                wrapper.style.display = 'block'; // Ensure it's visible if not hidden (changed from flex to block for wrapper)
            }
        });
        filterRequests(); // Re-apply filters after showing/hiding based on persistence
    }

    function saveHiddenState() {
        localStorage.setItem(HIDDEN_REQUESTS_KEY, JSON.stringify(hiddenRequestIds));
    }

    // Handle removing card from interface and saving state
    document.addEventListener('click', function(event) {
        const removeBtn = event.target.closest('.remove-card-btn');
        if (removeBtn) {
            const reqIdToRemove = removeBtn.getAttribute('data-reqid');
            
            showCustomAlert(
                'confirm-remove', // New type for removal confirmation
                'Confirm Removal',
                `Are you sure you want to remove this request from your view? This will only hide it.`,
                [
                    { text: 'Yes, Remove It', class: 'btn-primary-action', callback: () => {
                        const cardWrapper = document.querySelector(`.request-card-wrapper[data-reqid="${reqIdToRemove}"]`);
                        if (cardWrapper) {
                            cardWrapper.style.display = 'none'; // Hide the wrapper

                            // Add to hidden IDs if not already there
                            if (!hiddenRequestIds.includes(reqIdToRemove)) {
                                hiddenRequestIds.push(reqIdToRemove);
                                saveHiddenState();
                            }
                            checkAndToggleNoRequestsMessage();
                        }
                    }},
                    { text: 'Cancel', class: 'btn-secondary-action' }
                ]
            );
        }
    });

    // Filter functionality
    document.getElementById('statusFilter').addEventListener('change', filterRequests);
    document.getElementById('searchInput').addEventListener('keyup', filterRequests);

    function filterRequests() {
        const status = document.getElementById('statusFilter').value.toLowerCase();
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        let anyVisible = false;

        document.querySelectorAll('.request-card-wrapper').forEach(wrapper => {
            const reqId = wrapper.getAttribute('data-reqid');
            // If the card is permanently hidden, skip it
            if (hiddenRequestIds.includes(reqId)) {
                wrapper.style.display = 'none';
                return; 
            }

            const cardRow = wrapper.querySelector('.request-item'); // Get the actual card row inside the wrapper
            const rowStatus = cardRow.getAttribute('data-status');
            const rowSearch = cardRow.getAttribute('data-search');

            const statusMatch = status === 'all' || rowStatus === status;
            const searchMatch = rowSearch.includes(searchTerm);

            if (statusMatch && searchMatch) {
                wrapper.style.display = 'block'; // Show the wrapper (changed from flex to block)
                anyVisible = true;
            } else {
                wrapper.style.display = 'none'; // Hide the wrapper
            }
        });
        checkAndToggleNoRequestsMessage(); // Call after filtering
    }

    function checkAndToggleNoRequestsMessage() {
        // Count visible wrappers, excluding those hidden by persistence
        const visibleRequests = Array.from(document.querySelectorAll('.request-card-wrapper')).filter(wrapper => {
            return wrapper.style.display !== 'none' && !hiddenRequestIds.includes(wrapper.getAttribute('data-reqid'));
        });

        const noRequestsMessage = document.querySelector('.no-requests');
        if (noRequestsMessage) {
            if (visibleRequests.length > 0) {
                noRequestsMessage.style.display = 'none';
            } else {
                noRequestsMessage.style.display = 'block';
            }
        }
    }

    // Helper functions
    function formatDate(dateString) {
        if (!dateString) return 'Not specified';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
        });
    }

    // Initial application of hidden state and filter on page load
    applyHiddenState();
});