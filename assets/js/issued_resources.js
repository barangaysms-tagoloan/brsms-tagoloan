document.addEventListener('DOMContentLoaded', function() {
    const tableView = document.getElementById('tableView');
    const searchInput = document.getElementById('searchInput');
    const noDataMessageDiv = document.getElementById('noDataMessage');

    filterAndRenderTable();

    searchInput.addEventListener('keyup', filterAndRenderTable);

    // Helper functions
    function formatDateTime(dateTimeString) {
        if (!dateTimeString || dateTimeString === '0000-00-00 00:00:00' || dateTimeString === '0000-00-00') return 'N/A';
        const date = new Date(dateTimeString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: 'numeric',
            minute: 'numeric',
            hour12: true // This will format as 3:36 PM
        });
    }

    function formatDate(dateString) {
        if (!dateString || dateString === '0000-00-00') return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
        });
    }

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

    // Function to render the table view
    function renderTable(dataToRender) {
        let tableHtml = '';

        if (dataToRender.length > 0) {
            tableHtml = `
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Resource</th>
                            <th>Requester</th>
                            <th>Barangay</th>
                            <th>Quantity</th>
                            <th>Request Date</th>
                            <th>Return Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            dataToRender.forEach(request => {
                const photo_src = request.res_photo ? '../logic/inventory/uploads/' + request.res_photo : 'images/default-item.jpg';
                const status_class = getStatusColorJS(request.status);
                tableHtml += `
                    <tr class="issued-item"
                        data-search="${(request.resource_name + " " + request.user_full_name + " " + request.requester_brgy_name).toLowerCase()}"
                        data-request-id="${request.request_id}">
                        <td data-label="Resource" class="resource-name-cell">
                            <img src="${photo_src}" alt="Resource Photo" class="resource-img-sm">
                            ${request.resource_name}
                        </td>
                        <td data-label="Requester">${request.user_full_name}</td>
                        <td data-label="Barangay">${request.requester_brgy_name}</td>
                        <td data-label="Quantity">${request.quantity_requested}</td>
                        <td data-label="Request Date">${formatDate(request.request_date)}</td>
                        <td data-label="Return Date">${formatDate(request.return_date)}</td>
                        <td data-label="Status">
                            <span class="badge rounded-pill badge-${status_class}">
                                ${request.status}
                            </span>
                        </td>
                        <td data-label="Actions" class="actions-cell">
                            <div class="action-btn-group">
                                <button class="btn btn-outline-primary view-details-btn"
                                        data-details='${JSON.stringify(request)}'
                                        title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>
                                ${request.status.toLowerCase() === 'borrowed' ? `
                                    <button class="btn btn-return complete-btn"
                                            data-id="${request.request_id}"
                                            data-is-bulk="${request.is_bulk}"
                                            data-quantity="${request.quantity_requested}"
                                            title="Mark as Returned">
                                        <i class="fas fa-undo-alt"></i> Returned
                                    </button>
                                ` : ''}
                            </div>
                        </td>
                    </tr>
                `;
            });

            tableHtml += `
                    </tbody>
                </table>
            `;
            tableView.innerHTML = tableHtml;
            noDataMessageDiv.style.display = 'none'; // Hide no data message if there's data
        } else {
            tableView.innerHTML = ''; // Clear table if no data
            noDataMessageDiv.style.display = 'block'; // Show no data message
        }
        attachActionButtonsHandlers(); // Re-attach listeners for new elements
        attachViewDetailsListeners(); // Attach listeners for new view details buttons
    }

    // Filter and Render functionality
    function filterAndRenderTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const filteredData = issuedResourcesData.filter(request => {
            const searchString = `${request.resource_name} ${request.user_full_name} ${request.requester_brgy_name}`.toLowerCase();
            return searchString.includes(searchTerm);
        });

        renderTable(filteredData);

        // Update no data message content based on filter
        if (filteredData.length === 0) {
            if (issuedResourcesData.length === 0) {
                noDataMessageDiv.querySelector('h5').textContent = 'No Issued Resources Yet';
                noDataMessageDiv.querySelector('p').textContent = 'There are no records of issued resources from your barangay.';
            } else {
                noDataMessageDiv.querySelector('h5').textContent = 'No Matching Resources Found';
                noDataMessageDiv.querySelector('p').textContent = 'Your search did not return any issued resources.';
            }
            noDataMessageDiv.style.display = 'block';
        } else {
            noDataMessageDiv.style.display = 'none';
        }
    }

    const actionToast = new bootstrap.Toast(document.getElementById('actionToast'), {
        delay: 3000 // Set delay to 3000 milliseconds (3 seconds)
    });
    const returnConditionModal = new bootstrap.Modal(document.getElementById('returnConditionModal'));
    const requestDetailsModal = new bootstrap.Modal(document.getElementById('requestDetailsModal'));

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
        } else if (type === 'borrowed') {
            iconClass = 'fas fa-hand-holding';
            bgColorClass = 'text-bg-primary'; // Using primary for borrowed, adjust as needed
        } else if (type === 'cancelled') {
            iconClass = 'fas fa-ban';
            bgColorClass = 'text-bg-secondary'; // Using secondary for cancelled, adjust as needed
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

    function setButtonLoading(button, isLoading) {
        const originalHtml = button.innerHTML;
        const originalDisabled = button.disabled;
        const originalTitle = button.title;

        if (isLoading) {
            button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
            button.disabled = true;
            button.title = 'Processing...';
            button.setAttribute('data-original-html', originalHtml);
            button.setAttribute('data-original-disabled', originalDisabled);
            button.setAttribute('data-original-title', originalTitle);
        } else {
            // Check if the attributes exist before trying to restore
            if (button.hasAttribute('data-original-html')) {
                button.innerHTML = button.getAttribute('data-original-html');
            }
            if (button.hasAttribute('data-original-disabled')) {
                button.disabled = (button.getAttribute('data-original-disabled') === 'true');
            }
            if (button.hasAttribute('data-original-title')) {
                button.title = button.getAttribute('data-original-title');
            }

            button.removeAttribute('data-original-html');
            button.removeAttribute('data-original-disabled');
            button.removeAttribute('data-original-title');
        }
    }

    // Custom Alert Modal Functions
    const customAlertModalOverlay = document.getElementById('customAlertModalOverlay');
    const customAlertModal = document.getElementById('customAlertModal');
    const customAlertIcon = document.getElementById('customAlertIcon');
    const customAlertTitle = document.getElementById('customAlertTitle');
    const customAlertMessage = document.getElementById('customAlertMessage');
    const customAlertPrimaryBtn = document.getElementById('customAlertPrimaryBtn');
    const customAlertSecondaryBtn = document.getElementById('customAlertSecondaryBtn');
    const customAlertCloseBtn = document.getElementById('customAlertCloseBtn');

    function showCustomAlert(type, title, message, primaryBtnText, primaryBtnCallback, secondaryBtnText = 'Cancel') {
        // Reset classes
        customAlertModal.classList.remove('success', 'error', 'warning', 'info', 'borrowed', 'cancelled');
        customAlertIcon.className = 'modal-icon'; // Reset icon class

        // Set type-specific styles and icon
        if (type === 'success') {
            customAlertModal.classList.add('success');
            customAlertIcon.innerHTML = '<i class="fas fa-check-circle"></i>';
        } else if (type === 'error') {
            customAlertModal.classList.add('error');
            customAlertIcon.innerHTML = '<i class="fas fa-times-circle"></i>';
        } else if (type === 'warning') {
            customAlertModal.classList.add('warning');
            customAlertIcon.innerHTML = '<i class="fas fa-exclamation-triangle"></i>';
        } else if (type === 'info') {
            customAlertModal.classList.add('info');
            customAlertIcon.innerHTML = '<i class="fas fa-info-circle"></i>';
        } else if (type === 'borrowed') {
            customAlertModal.classList.add('borrowed');
            customAlertIcon.innerHTML = '<i class="fas fa-hand-holding"></i>';
        } else if (type === 'cancelled') {
            customAlertModal.classList.add('cancelled');
            customAlertIcon.innerHTML = '<i class="fas fa-ban"></i>';
        }

        customAlertTitle.textContent = title;
        customAlertMessage.textContent = message;
        customAlertPrimaryBtn.textContent = primaryBtnText;
        customAlertSecondaryBtn.textContent = secondaryBtnText;

        // Clear previous event listeners for primary and secondary buttons
        customAlertPrimaryBtn.onclick = null;
        customAlertSecondaryBtn.onclick = null;
        customAlertCloseBtn.onclick = null;

        customAlertPrimaryBtn.addEventListener('click', () => {
            primaryBtnCallback();
            hideCustomAlert();
        }, { once: true }); // Use { once: true } to ensure it only fires once

        customAlertSecondaryBtn.addEventListener('click', () => {
            hideCustomAlert();
        }, { once: true }); // Use { once: true }

        customAlertCloseBtn.addEventListener('click', () => { // Attach to the 'x' button
            hideCustomAlert();
        }, { once: true }); // Use { once: true }

        customAlertModalOverlay.classList.add('show');
    }

    function hideCustomAlert() {
        customAlertModalOverlay.classList.remove('show');
    }

    // Close modal on overlay click
    customAlertModalOverlay.addEventListener('click', (e) => {
        if (e.target === customAlertModalOverlay) {
            hideCustomAlert();
        }
    });

    // MODIFIED: Return Condition Modal Logic
    const singleItemConditionDiv = document.getElementById('singleItemCondition');
    const bulkItemConditionsDiv = document.getElementById('bulkItemConditions');
    const bulkItemsList = document.getElementById('bulkItemsList'); // New element
    const returnConditionSelect = document.getElementById('returnCondition');
    const otherConditionGroup = document.getElementById('otherConditionGroup');
    const otherConditionText = document.getElementById('otherConditionText');

    // New elements for "Apply to all"
    const applyAllConditionSelect = document.getElementById('applyAllCondition');
    const applyAllOtherConditionText = document.getElementById('applyAllOtherConditionText');
    const applyAllBtn = document.getElementById('applyAllBtn');

    let currentRequestedQuantity = 0;
    let borrowedItemsData = []; // To store fetched borrowed items

    // Function to fetch borrowed items for a specific request
    async function fetchBorrowedItems(reqId) {
        bulkItemsList.innerHTML = '<p class="text-muted">Loading borrowed items...</p>';
        try {
            const response = await fetch(`../get/get_borrowed_items_for_request.php?req_id=${reqId}`);
            const data = await response.json();
            if (data.error) {
                throw new Error(data.error);
            }
            borrowedItemsData = data;
            renderBorrowedItemsForConditioning(borrowedItemsData);
        } catch (error) {
            bulkItemsList.innerHTML = `<p class="text-danger">Error loading items: ${error.message}</p>`;
            showToast(`Error loading borrowed items: ${error.message}`, 'error');
            borrowedItemsData = [];
        }
    }

    function renderBorrowedItemsForConditioning(items) {
        let html = '';
        if (items.length === 0) {
            html = '<p class="text-muted">No specific items found for this request. This might be a non-bulk resource or an issue with item tracking.</p>';
        } else {
            html = '<p>Specify condition for each item:</p>';
            items.forEach(item => {
                html += `
                    <div class="item-condition-entry" data-item-id="${item.item_id}">
                        <div class="item-details">
                            <strong>Serial Number: ${item.serial_number || 'N/A'}</strong>
                            <span style="display:none;">Item ID: ${item.item_id} | QR: ${item.qr_code || 'N/A'}</span>
                        </div>
                        <select class="form-select item-condition-select">
                            <option value="">Select Condition</option>
                            <option value="Good">Good</option>
                            <option value="Minor Scratches">Minor Scratches</option>
                            <option value="Damaged">Damaged</option>
                            <option value="Lost">Lost</option>
                            <option value="Other">Other (Specify)</option>
                        </select>
                        <input type="text" class="form-control item-other-condition-text" placeholder="Specify other condition" style="display:none;">
                    </div>
                `;
            });
        }
        bulkItemsList.innerHTML = html;

        // Attach event listeners to newly created selects
        bulkItemsList.querySelectorAll('.item-condition-select').forEach(select => {
            select.addEventListener('change', function() {
                const otherTextInput = this.closest('.item-condition-entry').querySelector('.item-other-condition-text');
                if (this.value === 'Other') {
                    otherTextInput.style.display = 'block';
                    otherTextInput.setAttribute('required', 'required');
                } else {
                    otherTextInput.style.display = 'none';
                    otherTextInput.removeAttribute('required');
                    otherTextInput.value = ''; // Clear text if not 'Other'
                }
            });
        });
    }


    returnConditionModal._element.addEventListener('show.bs.modal', event => {
        const button = event.relatedTarget;
        const requestId = button.getAttribute('data-id');
        const isBulk = button.getAttribute('data-is-bulk') === '1';
        const quantity = parseInt(button.getAttribute('data-quantity'));

        document.getElementById('modalRequestId').value = requestId;
        document.getElementById('modalIsBulk').value = isBulk ? '1' : '0';
        document.getElementById('modalRequestedQuantity').value = quantity;
        currentRequestedQuantity = quantity;

        // Reset fields
        returnConditionSelect.value = 'Good';
        otherConditionGroup.style.display = 'none';
        otherConditionText.value = '';

        // Reset "Apply to all" fields
        applyAllConditionSelect.value = '';
        applyAllOtherConditionText.style.display = 'none';
        applyAllOtherConditionText.value = '';


        if (isBulk) {
            singleItemConditionDiv.style.display = 'none';
            bulkItemConditionsDiv.style.display = 'block';
            document.getElementById('returnModalIntroText').textContent = `ADD RETURN CONDITION FOR ${quantity} ITEMS`;
            fetchBorrowedItems(requestId); // Fetch items for bulk
        } else {
            singleItemConditionDiv.style.display = 'block';
            bulkItemConditionsDiv.style.display = 'none';
            document.getElementById('returnModalIntroText').textContent = `ADD RETURN CONDITION FOR 1 ITEM`;
        }
    });

    // Event listener for single item condition dropdown change
    returnConditionSelect.addEventListener('change', function() {
        if (this.value === 'Other') {
            otherConditionGroup.style.display = 'block';
        } else {
            otherConditionGroup.style.display = 'none';
        }
    });

    // Event listener for "Apply to all" condition dropdown change
    applyAllConditionSelect.addEventListener('change', function() {
        if (this.value === 'Other') {
            applyAllOtherConditionText.style.display = 'block';
            applyAllOtherConditionText.setAttribute('required', 'required');
        } else {
            applyAllOtherConditionText.style.display = 'none';
            applyAllOtherConditionText.removeAttribute('required');
            applyAllOtherConditionText.value = ''; // Clear text if not 'Other'
        }
    });

    // Event listener for "Apply" button click for bulk items
    applyAllBtn.addEventListener('click', function() {
        const selectedCondition = applyAllConditionSelect.value;
        let otherText = applyAllOtherConditionText.value.trim();

        if (selectedCondition === '') {
            showToast('Please select a condition to apply to all items.', 'error');
            return;
        }

        if (selectedCondition === 'Other' && otherText === '') {
            showToast('Please specify the "Other" condition to apply to all items.', 'error');
            return;
        }

        bulkItemsList.querySelectorAll('.item-condition-select').forEach(select => {
            select.value = selectedCondition;
            const otherTextInput = select.closest('.item-condition-entry').querySelector('.item-other-condition-text');
            if (selectedCondition === 'Other') {
                otherTextInput.style.display = 'block';
                otherTextInput.value = otherText;
                otherTextInput.setAttribute('required', 'required');
            } else {
                otherTextInput.style.display = 'none';
                otherTextInput.removeAttribute('required');
                otherTextInput.value = '';
            }
        });
        showToast('Condition applied to all items.', 'success');
    });


    // Function to attach all action button handlers
    function attachActionButtonsHandlers() {
        // Event listener for "Returned" button click
        document.querySelectorAll('.complete-btn').forEach(btn => {
            btn.onclick = function() { // Use onclick to prevent multiple listeners
                // Open the modal using Bootstrap's JS API
                returnConditionModal.show(this); // Pass the button element to the show method
            };
        });
    }

    // Event listener for "Confirm Return" button in modal
    document.getElementById('confirmReturnBtn').addEventListener('click', function() {
        const requestId = document.getElementById('modalRequestId').value;
        const isBulk = document.getElementById('modalIsBulk').value === '1';
        const requestedQuantity = parseInt(document.getElementById('modalRequestedQuantity').value);

        let conditionData;

        if (isBulk) {
            const itemConditionEntries = bulkItemsList.querySelectorAll('.item-condition-entry');
            const returnedItems = [];
            let allConditionsSelected = true;

            itemConditionEntries.forEach(entry => {
                const itemId = entry.getAttribute('data-item-id');
                const conditionSelect = entry.querySelector('.item-condition-select');
                const otherConditionInput = entry.querySelector('.item-other-condition-text');

                let condition = conditionSelect.value;
                let otherText = otherConditionInput.value.trim();

                if (condition === '') {
                    allConditionsSelected = false;
                    return; // Skip this item, will show error later
                }

                if (condition === 'Other') {
                    if (otherText === '') {
                        allConditionsSelected = false;
                        return; // Skip this item, will show error later
                    }
                    condition = otherText; // Use the custom text as the condition
                }

                returnedItems.push({
                    item_id: parseInt(itemId),
                    condition: condition
                });
            });

            if (!allConditionsSelected) {
                showToast('Please specify a condition for all borrowed items.', 'error');
                return;
            }

            if (returnedItems.length !== requestedQuantity) {
                showToast(`Number of items accounted for (${returnedItems.length}) does not match requested quantity (${requestedQuantity}).`, 'error');
                return;
            }

            conditionData = returnedItems; // Array of objects for bulk items
        } else {
            let condition = returnConditionSelect.value;
            if (condition === 'Other') {
                const otherText = otherConditionText.value.trim();
                if (otherText === '') {
                    showToast('Please specify the "Other" condition.', 'error');
                    return;
                }
                condition = otherText; // Use the custom text as the condition
            }
            conditionData = condition; // For single item, it's just a string
        }

        // Find the original button to apply loading state
        const originalButton = document.querySelector(`.complete-btn[data-id="${requestId}"]`);
        if (originalButton) {
            setButtonLoading(originalButton, true);
        }

        returnConditionModal.hide(); // Hide the modal
        updateRequestStatus(requestId, 'complete', conditionData, originalButton);
    });

    function updateRequestStatus(requestId, action, condition, button) {
        // For 'complete' action, send condition as JSON string in POST request
        const formData = new FormData();
        formData.append('action', action);
        formData.append('id', requestId);
        formData.append('condition', JSON.stringify(condition)); // Stringify the condition data

        fetch('../get/process_request_action.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => { throw new Error(err.message || 'Server error'); });
                }
                return response.json();
            })
            .then(data => {
                if (data.status === 'success') {
                    showToast(data.message, 'success');

                    // Remove the row from the table if the status becomes 'Completed'
                    if (data.new_status === 'Completed') {
                        // Find and remove the item from issuedResourcesData
                        const index = issuedResourcesData.findIndex(item => item.request_id == requestId);
                        if (index > -1) {
                            issuedResourcesData.splice(index, 1);
                        }
                        filterAndRenderTable(); // Re-render to update table and no data message
                    }
                } else {
                    showToast(data.message || 'Action failed', 'error');
                }
                if (button) {
                    setButtonLoading(button, false);
                }
            })
            .catch(error => {
                showToast('An error occurred: ' + error.message, 'error');
                console.error('Error:', error);
                if (button) {
                    setButtonLoading(button, false);
                }
            });
    }

    // Purpose Modal Logic (from original issued_resources.php)
    const purposeModal = document.getElementById('purposeModal');
    purposeModal.addEventListener('show.bs.modal', event => {
        const button = event.relatedTarget;
        const purpose = button.getAttribute('data-purpose');
        const modalPurposeText = purposeModal.querySelector('#modalPurposeText');
        modalPurposeText.textContent = purpose;
    });

    // Function to attach event listeners to "View Details" buttons (New, similar to returning.php)
    function attachViewDetailsListeners() {
        document.querySelectorAll('.view-details-btn').forEach(button => {
            button.onclick = function() {
                const requestData = JSON.parse(this.getAttribute('data-details'));
                const modalContent = document.getElementById('modalRequestDetailsContent');
                const photo_src = requestData.res_photo ? '../logic/inventory/uploads/' + requestData.res_photo : 'images/default-item.jpg';
                const status_class = getStatusColorJS(requestData.status);

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
                                    <span>${requestData.resource_name}</span>
                                </div>
                                <div class="detail-item">
                                    <strong>Resource Type:</strong>
                                    <span>${requestData.is_bulk == 1 ? 'Bulk (Multiple Items)' : 'Single Item'}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <div class="request-details">
                                <h6><i class="fas fa-hand-holding me-2"></i> Request Information</h6>
                                <div class="detail-item">
                                    <strong>Request ID:</strong>
                                    <span>#${requestData.request_id}</span>
                                </div>
                                <div class="detail-item">
                                    <strong>Requester Name:</strong>
                                    <span>${requestData.user_full_name}</span>
                                </div>
                                <div class="detail-item">
                                    <strong>Requester Barangay:</strong>
                                    <span>${requestData.requester_brgy_name}</span>
                                </div>
                                <div class="detail-item">
                                    <strong>Quantity Requested:</strong>
                                    <span>${requestData.quantity_requested}</span>
                                </div>
                                <div class="detail-item">
                                    <strong>Request Date:</strong>
                                    <span>${formatDate(requestData.request_date)}</span>
                                </div>
                                <div class="detail-item">
                                    <strong>Expected Return:</strong>
                                    <span>${formatDate(requestData.return_date)}</span>
                                </div>
                                <div class="detail-item">
                                    <strong>Borrowed On:</strong>
                                    <span>${formatDateTime(requestData.borrow_timestamp)}</span>
                                </div>
                                <div class="detail-item">
                                    <strong>Returned On:</strong>
                                    <span>${formatDateTime(requestData.return_timestamp)}</span>
                                </div>
                                <div class="detail-item status-info">
                                    <strong>Status:</strong>
                                    <span class="badge rounded-pill badge-${status_class}">
                                        ${requestData.status}
                                    </span>
                                </div>
                                <div class="detail-item">
                                    <strong>Purpose:</strong>
                                    <span class="purpose-text">${requestData.purpose || 'N/A'}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                requestDetailsModal.show();
            };
        });
    }
});