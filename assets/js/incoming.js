document.addEventListener('DOMContentLoaded', function() {
    const tableView = document.getElementById('tableView');
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const noDataMessageDiv = document.getElementById('noDataMessage');
    const requestDetailsModalElement = document.getElementById('requestDetailsModal');
    const selectItemsModalElement = document.getElementById('selectItemsModal');
    const editQuantityModalElement = document.getElementById('editQuantityModal');
    const editQuantityInput = document.getElementById('editQuantityInput');
    const editRequestIdInput = document.getElementById('editRequestId');
    const saveQuantityBtn = document.getElementById('saveQuantityBtn');
    const quantityErrorDiv = document.getElementById('quantityError');

    let currentRequestId = null;
    let currentResourceName = null;
    let currentResourceId = null;
    let currentRequestedQuantity = null;
    let currentActionCallback = null;
    statusFilter.value = initialStatusFilter;

    // Initial rendering of the table
    filterAndRenderTable();

    // Event listener for search input
    searchInput.addEventListener('keyup', filterAndRenderTable);
    // Event listener for status filter dropdown
    statusFilter.addEventListener('change', filterAndRenderTable);

    requestDetailsModalElement.addEventListener('hidden.bs.modal', function () {
        const backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops.forEach(backdrop => backdrop.remove());
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
    });

    selectItemsModalElement.addEventListener('hidden.bs.modal', function () {
        const backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops.forEach(backdrop => backdrop.remove());
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';

        // Reset the loading state of the button that triggered the modal
        const originalButton = document.querySelector(`.approve-btn[data-id="${currentRequestId}"]`) || document.querySelector(`.borrow-btn[data-id="${currentRequestId}"]`);
        if (originalButton) {
            setButtonLoading(originalButton, false);
        }
    });

    editQuantityModalElement.addEventListener('hidden.bs.modal', function () {
        const backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops.forEach(backdrop => backdrop.remove());
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
        editQuantityInput.classList.remove('is-invalid'); // Clear validation state
        quantityErrorDiv.style.display = 'none';
    });

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
                            <th>Details</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            dataToRender.forEach(request => {
                const photo_src = request.res_photo ? '../logic/inventory/uploads/' + request.res_photo : 'images/default-item.jpg';
                const status_class = getStatusColorJS(request.status);
                const isQuantityClickable = request.status.toLowerCase() === 'approved';
                const quantityCellClass = isQuantityClickable ? 'clickable-quantity' : '';

                tableHtml += `
                    <tr class="incoming-item"
                        data-search="${(request.resource_name + " " + request.user_full_name + " " + request.requester_brgy_name + " " + request.request_id + " " + (request.contact_number_requested || '')).toLowerCase()}"
                        data-status="${request.status.toLowerCase()}"
                        data-request-id="${request.request_id}">
                        <td data-label="Resource" class="resource-name-cell">
                            <img src="${photo_src}" alt="Resource Photo" class="resource-img-sm">
                            ${request.resource_name}
                        </td>
                        <td data-label="Requester">${request.user_full_name}</td>
                        <td data-label="Barangay">${request.requester_brgy_name}</td>
                        <td data-label="Quantity" class="${quantityCellClass}"
                            data-request-id="${request.request_id}"
                            data-current-quantity="${request.quantity_requested}"
                            ${isQuantityClickable ? 'data-bs-toggle="modal" data-bs-target="#editQuantityModal"' : ''}>
                            <span id="quantity-${request.request_id}">${request.quantity_requested}</span>
                        </td>
                        <td data-label="Request Date">${formatDate(request.request_date)}</td>
                        <td data-label="Return Date">${formatDate(request.return_date)}</td>
                        <td data-label="Status">
                            <span class="badge rounded-pill badge-${status_class}">
                                ${request.status}
                            </span>
                        </td>
                        <td data-label="Details">
                            <button class="btn btn-sm btn-outline-info view-details-btn"
                                    data-bs-toggle="modal" data-bs-target="#requestDetailsModal"
                                    data-request='${JSON.stringify(request)}'
                                    title="View Request Details">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                        <td data-label="Actions" class="actions-cell">
                            <div class="action-btn-group">
                                ${request.status.toLowerCase() === 'pending' ? `
                                    <button class="btn btn-success btn-sm approve-btn"
                                            data-id="${request.request_id}"
                                            data-res-id="${request.resource_id}"
                                            data-res-name="${request.resource_name}"
                                            data-req-quantity="${request.quantity_requested}"
                                            data-is-bulk="${request.is_bulk}"
                                            title="Approve">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm reject-btn" data-id="${request.request_id}" title="Reject">
                                        <i class="fas fa-times"></i>
                                    </button>
                                ` : ''}
                                ${request.status.toLowerCase() === 'approved' ? `
                                    <a href="../generate_pdf_receipt.php?request_id=${request.request_id}&source=owner"
                                       class="btn btn-primary btn-sm" target="_blank" title="Generate Receipt">
                                        <i class="fas fa-file-alt"></i>
                                    </a>
                                    <button class="btn btn-info btn-sm borrow-btn"
                                            data-id="${request.request_id}"
                                            data-res-id="${request.resource_id}"
                                            data-res-name="${request.resource_name}"
                                            data-req-quantity="${request.quantity_requested}"
                                            data-is-bulk="${request.is_bulk}"
                                            title="Mark as Borrowed">
                                        <i class="fas fa-hand-holding"></i>
                                    </button>
                                    <button class="btn btn-secondary btn-sm cancel-btn" data-id="${request.request_id}" title="Cancel Request">
                                        <i class="fas fa-ban"></i>
                                    </button>
                                ` : ''}
                                ${request.status.toLowerCase() === 'borrowed' ? `
                                    <span class="text-muted small">Manage in Issued</span>
                                ` : ''}
                                ${['completed', 'rejected', 'cancelled'].includes(request.status.toLowerCase()) ? `
                                    <span class="text-muted small">No actions</span>
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
            noDataMessageDiv.style.display = 'none';
        } else {
            tableView.innerHTML = '';
            noDataMessageDiv.style.display = 'block';
        }
        attachActionButtonsHandlers();
        attachQuantityEditHandlers(); // Attach new handlers
    }

    function filterAndRenderTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedStatus = statusFilter.value.toLowerCase();

        const filteredData = incomingRequestsData.filter(request => {
            const searchString = `${request.resource_name} ${request.user_full_name} ${request.requester_brgy_name} ${request.request_id} ${request.contact_number_requested || ''}`.toLowerCase();
            const statusMatch = selectedStatus === 'all' || request.status.toLowerCase() === selectedStatus;
            const searchMatch = searchString.includes(searchTerm);
            return statusMatch && searchMatch;
        });

        renderTable(filteredData);

        if (filteredData.length === 0) {
            if (incomingRequestsData.length === 0) {
                noDataMessageDiv.querySelector('h5').textContent = 'No Incoming Requests Yet';
                noDataMessageDiv.querySelector('p').textContent = 'There are no records of incoming requests for your barangay.';
            } else {
                noDataMessageDiv.querySelector('h5').textContent = 'No Matching Requests Found';
                noDataMessageDiv.querySelector('p').textContent = 'Your search did not return any incoming requests.';
            }
            noDataMessageDiv.style.display = 'block';
        } else {
            noDataMessageDiv.style.display = 'none';
        }
    }

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
        } else if (type === 'borrowed') {
            iconClass = 'fas fa-hand-holding';
            bgColorClass = 'text-bg-primary';
        } else if (type === 'cancelled') {
            iconClass = 'fas fa-ban';
            bgColorClass = 'text-bg-secondary';
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

        // Store the callback
        currentActionCallback = primaryBtnCallback;

        // Clear previous event listeners and attach new ones
        customAlertPrimaryBtn.onclick = null; // Clear previous click handler
        customAlertSecondaryBtn.onclick = null; // Clear previous click handler
        customAlertCloseBtn.onclick = null; // Clear previous click handler for 'x' button

        customAlertPrimaryBtn.addEventListener('click', function handler() {
            if (currentActionCallback) {
                currentActionCallback();
            }
            hideCustomAlert();
            customAlertPrimaryBtn.removeEventListener('click', handler); // Remove self
        });

        customAlertSecondaryBtn.addEventListener('click', function handler() {
            hideCustomAlert();
            customAlertSecondaryBtn.removeEventListener('click', handler); // Remove self
        });

        customAlertCloseBtn.addEventListener('click', function handler() {
            hideCustomAlert();
            customAlertCloseBtn.removeEventListener('click', handler); // Remove self
        });

        customAlertModalOverlay.classList.add('show');
    }

    function hideCustomAlert() {
        customAlertModalOverlay.classList.remove('show');
        currentActionCallback = null; // Clear the stored callback when modal is hidden
    }

    // Close modal on overlay click
    customAlertModalOverlay.addEventListener('click', (e) => {
        if (e.target === customAlertModalOverlay) {
            hideCustomAlert();
        }
    });

    function showRequestDetailsModal(request) {
        document.getElementById('modalResourcePhoto').src = request.res_photo ? '../logic/inventory/uploads/' + request.res_photo : 'images/default-item.jpg';
        document.getElementById('modalResourceName').textContent = request.resource_name;
        document.getElementById('modalQuantityRequested').textContent = request.quantity_requested;
        document.getElementById('modalIsBulk').textContent = request.is_bulk == 1 ? 'Yes' : 'No';

        document.getElementById('modalRequesterName').textContent = request.user_full_name;
        document.getElementById('modalRequesterBrgy').textContent = request.requester_brgy_name;
        document.getElementById('modalContactNumber').textContent = request.contact_number_requested || 'N/A';
        document.getElementById('modalRequestDate').textContent = formatDate(request.request_date);
        document.getElementById('modalReturnDate').textContent = formatDate(request.return_date);
        document.getElementById('modalBorrowedTimestamp').textContent = formatDateTime(request.borrow_timestamp); // Display borrowed timestamp
        document.getElementById('modalReturnTimestamp').textContent = formatDateTime(request.return_timestamp); // Display return timestamp

        const statusBadge = document.getElementById('modalStatus');
        statusBadge.textContent = request.status;
        statusBadge.className = `badge rounded-pill badge-${getStatusColorJS(request.status)}`;

        document.getElementById('modalPurpose').textContent = request.purpose || 'No purpose provided.';

        const requestDetailsModal = new bootstrap.Modal(requestDetailsModalElement);
        requestDetailsModal.show();
    }

    async function checkAvailabilityAndLoadItems(resId, reqQuantity, requestId, resourceName, currentButton) {
        currentRequestId = requestId;
        currentResourceName = resourceName;
        currentResourceId = resId;
        currentRequestedQuantity = reqQuantity;

        document.getElementById('selectItemsResourceName').textContent = resourceName;
        document.getElementById('requiredQuantity').textContent = reqQuantity;
        document.getElementById('selectedCount').textContent = '0';
        document.getElementById('totalAvailableItems').textContent = '0';
        document.getElementById('selectionError').classList.add('d-none');
        document.getElementById('availableItemsTableBody').innerHTML = '';

        try {
            const response = await fetch(`../get/get_available_items.php?res_id=${resId}`);
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            const items = await response.json();

            document.getElementById('totalAvailableItems').textContent = items.length;

            if (items.length < reqQuantity) {
                showCustomAlert(
                    'warning',
                    'Not Enough Items',
                    `Only ${items.length} item(s) are available, but ${reqQuantity} item(s) are requested. Cannot fulfill this request at this time.`,
                    'OK',
                    () => { /* do nothing */ }
                );
                setButtonLoading(currentButton, false);
                return;
            }

            if (items.length === 0) {
                document.getElementById('availableItemsTableBody').innerHTML = '<tr><td colspan="3" class="text-center">No available items for this resource.</td></tr>';
                document.getElementById('confirmSelectionBtn').disabled = true;
                document.getElementById('selectionError').classList.remove('d-none');
                document.getElementById('selectionError').textContent = 'No available items for this resource. Cannot fulfill request.';
                setButtonLoading(currentButton, false);
                return;
            }

            items.forEach((item) => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td><input type="checkbox" class="item-checkbox-selection" data-item-id="${item.item_id}"></td>
                    <td>${item.serial_number || 'N/A'}</td>
                    <td><a href="qrcodes/${item.qr_code}" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="fas fa-qrcode"></i> View QR</a></td>
                `;
                document.getElementById('availableItemsTableBody').appendChild(row);
            });

            document.getElementById('availableItemsTableBody').querySelectorAll('.item-checkbox-selection').forEach(checkbox => {
                checkbox.addEventListener('change', updateSelectedCount);
            });

            const checkboxes = document.querySelectorAll('#availableItemsTableBody .item-checkbox-selection');
            for (let i = 0; i < reqQuantity && i < checkboxes.length; i++) {
                checkboxes[i].checked = true;
            }

            updateSelectedCount();
            const selectItemsModal = new bootstrap.Modal(selectItemsModalElement);
            selectItemsModal.show();
            setButtonLoading(currentButton, false);
        } catch (error) {
            console.error('Error fetching available items:', error);
            showToast('Error loading available items: ' + error.message, 'error');
            document.getElementById('confirmSelectionBtn').disabled = true;
            document.getElementById('selectionError').classList.remove('d-none');
            document.getElementById('selectionError').textContent = 'Error loading available items. Please try again.';
            setButtonLoading(currentButton, false);
        }
    }

    function updateSelectedCount() {
        const selectedCheckboxes = document.querySelectorAll('#availableItemsTableBody .item-checkbox-selection:checked');
        const selectedCountSpan = document.getElementById('selectedCount');
        const confirmBtn = document.getElementById('confirmSelectionBtn');
        const selectionError = document.getElementById('selectionError');

        selectedCountSpan.textContent = selectedCheckboxes.length;

        if (selectedCheckboxes.length === currentRequestedQuantity) {
            confirmBtn.disabled = false;
            selectionError.classList.add('d-none');
        } else {
            confirmBtn.disabled = true;
            selectionError.classList.remove('d-none');
            selectionError.textContent = `Please select exactly ${selectedCheckboxes.length} items. Currently selected: ${selectedCheckboxes.length}`;
        }
    }

    document.getElementById('confirmSelectionBtn').addEventListener('click', function() {
        const selectedItemIds = Array.from(document.querySelectorAll('#availableItemsTableBody .item-checkbox-selection:checked'))
                                    .map(checkbox => checkbox.dataset.itemId);

        if (selectedItemIds.length !== currentRequestedQuantity) {
            showToast(`Please select exactly ${currentRequestedQuantity} items.`, 'error');
            return;
        }

        const selectItemsModal = bootstrap.Modal.getInstance(selectItemsModalElement);
        selectItemsModal.hide();

        showCustomAlert(
            'borrowed',
            'Confirm Borrow',
            `Are you sure you want to mark this request as Borrowed and assign the selected items?`,
            'Confirm Borrow',
            () => {
                const originalButton = document.querySelector(`.approve-btn[data-id="${currentRequestId}"]`) || document.querySelector(`.borrow-btn[data-id="${currentRequestId}"]`);
                if (originalButton) {
                    setButtonLoading(originalButton, true);
                }
                updateRequestStatus(currentRequestId, 'borrow', originalButton, selectedItemIds);
            }
        );
    });

    function attachActionButtonsHandlers() {
        tableView.querySelectorAll('.approve-btn').forEach(btn => {
            btn.onclick = function() {
                const requestId = this.getAttribute('data-id');
                const currentButton = this;

                showCustomAlert(
                    'success',
                    'Confirm Approval',
                    'Are you sure you want to approve this request?',
                    'Approve',
                    () => {
                        setButtonLoading(currentButton, true);
                        updateRequestStatus(requestId, 'approve', currentButton);
                    }
                );
            };
        });

        tableView.querySelectorAll('.reject-btn').forEach(btn => {
            btn.onclick = function() {
                const requestId = this.getAttribute('data-id');
                const currentButton = this;
                showCustomAlert(
                    'error',
                    'Confirm Rejection',
                    'Are you sure you want to reject this request? This action cannot be undone.',
                    'Reject',
                    () => {
                        setButtonLoading(currentButton, true);
                        updateRequestStatus(requestId, 'reject', currentButton);
                    }
                );
            };
        });

        tableView.querySelectorAll('.borrow-btn').forEach(btn => {
            btn.onclick = function() {
                const requestId = this.getAttribute('data-id');
                const resourceId = this.getAttribute('data-res-id');
                const resourceName = this.getAttribute('data-res-name');
                const requestedQuantity = parseInt(this.getAttribute('data-req-quantity'));
                const isBulk = this.getAttribute('data-is-bulk') === '1';
                const currentButton = this;

                if (isBulk) {
                    setButtonLoading(currentButton, true);
                    checkAvailabilityAndLoadItems(resourceId, requestedQuantity, requestId, resourceName, currentButton);
                } else {
                    showCustomAlert(
                        'borrowed',
                        'Confirm Borrow',
                        'Mark this request as Borrowed? This will mark the resource as borrowed.',
                        'Mark as Borrowed',
                        () => {
                            setButtonLoading(currentButton, true);
                            updateRequestStatus(requestId, 'borrow', currentButton);
                        }
                    );
                }
            };
        });

        tableView.querySelectorAll('.cancel-btn').forEach(btn => {
            btn.onclick = function() {
                const requestId = this.getAttribute('data-id');
                const currentButton = this;
                showCustomAlert(
                    'cancelled',
                    'Confirm Cancellation',
                    'Are you sure you want to cancel this request? This will return the resource to available status.',
                    'Yes, Cancel',
                    () => {
                        setButtonLoading(currentButton, true);
                        updateRequestStatus(requestId, 'cancel', currentButton);
                    }
                );
            };
        });

        tableView.querySelectorAll('.view-details-btn').forEach(btn => {
            btn.onclick = function() {
                const requestData = JSON.parse(this.getAttribute('data-request'));
                showRequestDetailsModal(requestData);
            };
        });
    }

    // New function to attach handlers for quantity editing
    function attachQuantityEditHandlers() {
        tableView.querySelectorAll('.clickable-quantity').forEach(cell => {
            cell.onclick = function() {
                const requestId = this.getAttribute('data-request-id');
                const currentQuantity = this.getAttribute('data-current-quantity');
                
                editRequestIdInput.value = requestId;
                editQuantityInput.value = currentQuantity;
                editQuantityInput.classList.remove('is-invalid'); // Clear previous validation
                quantityErrorDiv.style.display = 'none';

                const editQuantityModal = new bootstrap.Modal(editQuantityModalElement);
                editQuantityModal.show();
            };
        });
    }

    // Event listener for saving quantity changes
    saveQuantityBtn.addEventListener('click', function() {
        const requestId = editRequestIdInput.value;
        const newQuantity = parseInt(editQuantityInput.value);

        // Basic validation
        if (isNaN(newQuantity) || newQuantity < 1) {
            editQuantityInput.classList.add('is-invalid');
            quantityErrorDiv.style.display = 'block';
            return;
        } else {
            editQuantityInput.classList.remove('is-invalid');
            quantityErrorDiv.style.display = 'none';
        }

        // Close the modal
        const editQuantityModal = bootstrap.Modal.getInstance(editQuantityModalElement);
        editQuantityModal.hide();

        // Show confirmation alert
        showCustomAlert(
            'warning',
            'Confirm Quantity Update',
            `Are you sure you want to change the quantity for Request ID ${requestId} to ${newQuantity}?`,
            'Confirm Update',
            () => {
                // Proceed with AJAX update
                updateRequestQuantity(requestId, newQuantity);
            }
        );
    });

    function updateRequestQuantity(requestId, newQuantity) {
        const formData = new FormData();
        formData.append('request_id', requestId);
        formData.append('new_quantity', newQuantity);

        fetch('../get/update_quantity.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                return response.text().then(text => {
                    console.error('Server response was not OK:', text);
                    try {
                        const json = JSON.parse(text);
                        throw new Error(json.message || 'Server error');
                    } catch (e) {
                        throw new Error('Server returned non-JSON error: ' + text.substring(0, 100) + '...');
                    }
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.status === 'success') {
                showToast(data.message, 'success');
                const index = incomingRequestsData.findIndex(item => item.request_id == requestId);
                if (index > -1) {
                    incomingRequestsData[index].quantity_requested = newQuantity;
                }
                filterAndRenderTable(); // Re-render with updated local data
            } else {
                showToast(data.message || 'Failed to update quantity', 'error');
            }
        })
        .catch(error => {
            showToast('An error occurred: ' + error.message, 'error');
            console.error('Error updating quantity:', error);
        });
    }


    function updateRequestStatus(requestId, action, button, selectedItemIds = []) {
        let url = `../get/process_request_action.php?action=${action}&id=${requestId}`;
        let method = 'GET';
        let body = null;

        if (action === 'borrow' && selectedItemIds.length > 0) {
            url = `../get/process_request_action.php`;
            method = 'POST';
            body = new URLSearchParams();
            body.append('action', action);
            body.append('id', requestId);
            selectedItemIds.forEach(itemId => {
                body.append('item_ids[]', itemId);
            });
        }

        fetch(url, {
            method: method,
            body: body,
            headers: {
                'Content-Type': method === 'POST' ? 'application/x-www-form-urlencoded' : undefined
            }
        })
        .then(response => {
            if (!response.ok) {
                return response.text().then(text => {
                    console.error('Server response was not OK:', text);
                    try {
                        const json = JSON.parse(text);
                        throw new Error(json.message || 'Server error');
                    } catch (e) {
                        throw new Error('Server returned non-JSON error: ' + text.substring(0, 100) + '...');
                    }
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.status === 'success') {
                showToast(data.message, 'success');

                // Update the local data and re-render
                const index = incomingRequestsData.findIndex(item => item.request_id == requestId);
                if (index > -1) {
                    incomingRequestsData[index].status = data.new_status;
                    // Update timestamps if available in the response (though not explicitly sent back by process_request_action.php)
                    // For a full update, you might need to re-fetch the specific request or modify process_request_action to return updated timestamps.
                    // For now, we'll rely on the next polling cycle to get the updated data.
                }
                filterAndRenderTable(); // Re-render with updated local data

            } else {
                showToast(data.message || 'Action failed', 'error');
            }
            if (button) {
                setButtonLoading(button, false);
            }
            return data;
        })
        .catch(error => {
            showToast('An error occurred: ' + error.message, 'error');
            console.error('Error:', error);
            if (button) {
                setButtonLoading(button, false);
            }
        });
    }

    // --- Real-time Polling Logic ---
    const pollingInterval = 10000; // Poll every 10 seconds (adjust as needed)
    let lastFetchTimestamp = Date.now(); // Track the last fetch time

    async function fetchAndUpdateTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedStatus = statusFilter.value; // Use raw value for PHP

        try {
            const response = await fetch(`../get/fetch_incoming_requests.php?status_filter=${selectedStatus}`);
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            const result = await response.json();

            if (result.status === 'success') {
                const newIncomingRequestsData = result.data;

                // Check if there are any new requests or changes in existing ones
                const hasChanges = !areArraysEqual(incomingRequestsData, newIncomingRequestsData);

                if (hasChanges) {
                    incomingRequestsData = newIncomingRequestsData; // Update the local data
                    filterAndRenderTable(); // Re-render the table with the new data
                    // Optionally, show a subtle notification if new requests arrived
                    // if (newIncomingRequestsData.length > incomingRequestsData.length) {
                    //     showToast('New incoming requests received!', 'info');
                    // }
                }
            } else {
                console.error('Error fetching real-time data:', result.message);
            }
        } catch (error) {
            console.error('Error during real-time fetch:', error);
        }
    }

    // Helper to compare two arrays of objects for changes (simple comparison)
    function areArraysEqual(arr1, arr2) {
        if (arr1.length !== arr2.length) return false;
        for (let i = 0; i < arr1.length; i++) {
            // A simple stringify comparison might be sufficient for quick check
            // For deeper comparison, you'd need to iterate through properties
            if (JSON.stringify(arr1[i]) !== JSON.stringify(arr2[i])) {
                return false;
            }
        }
        return true;
    }

    // Start polling when the page loads
    setInterval(fetchAndUpdateTable, pollingInterval);
});