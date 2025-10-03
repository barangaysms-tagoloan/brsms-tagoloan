document.addEventListener('DOMContentLoaded', function() {
    const tableView = document.getElementById('tableView');
    const searchInput = document.getElementById('searchInput');
    const conditionFilter = document.getElementById('conditionFilter');
    const noDataMessageDiv = document.getElementById('noDataMessage');

    // Initial rendering of the table
    filterReturns();

    // Helper functions
    function formatDate(dateString) {
        if (!dateString || dateString === '0000-00-00') return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
        });
    }

    // ADDED: Helper function to format date and time
    function formatDateTime(dateTimeString) {
        if (!dateTimeString || dateTimeString === '0000-00-00 00:00:00' || dateTimeString === '0000-00-00') return 'N/A';
        const date = new Date(dateTimeString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: 'numeric',
            minute: 'numeric',
            hour12: true
        });
    }

    function getConditionColorJS(condition) {
        const condition_lower = condition.toLowerCase();
        if (condition_lower.includes('good')) {
            return 'success';
        } else if (condition_lower.includes('scratch') || condition_lower.includes('minor')) {
            return 'warning';
        } else if (condition_lower.includes('damaged') || condition_lower.includes('lost')) {
            return 'danger';
        } else {
            return 'secondary';
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
                            <th>Quantity</th>
                            <th>Borrowed On:</th> <!-- UPDATED COLUMN -->
                            <th>Returned On:</th> <!-- UPDATED COLUMN -->
                            <th>Condition</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            dataToRender.forEach(returnItem => {
                const photo_src = returnItem.res_photo ? '../logic/inventory/uploads/' + returnItem.res_photo : 'images/default-item.jpg';
                const condition_class = getConditionColorJS(returnItem.return_condition);
                tableHtml += `
                    <tr class="return-item"
                        data-condition="${returnItem.return_condition.toLowerCase()}"
                        data-search="${(returnItem.res_name + " " + returnItem.requester_name + " " + returnItem.requester_brgy).toLowerCase()}"
                        data-return-id="${returnItem.return_id}">
                        <td data-label="Resource" class="resource-name-cell">
                            <img src="${photo_src}" alt="Resource Photo" class="resource-img-sm">
                            ${returnItem.res_name}
                        </td>
                        <td data-label="Requester">${returnItem.requester_name} (${returnItem.requester_brgy})</td>
                        <td data-label="Quantity">${returnItem.req_quantity}</td>
                        <td data-label="Borrowed On">${formatDateTime(returnItem.borrow_timestamp)}</td> <!-- UPDATED DATA -->
                        <td data-label="Returned On">${formatDateTime(returnItem.return_timestamp)}</td> <!-- UPDATED DATA -->
                        <td data-label="Condition">
                            <span class="badge badge-${condition_class}">
                                ${returnItem.return_condition}
                            </span>
                        </td>
                        <td data-label="Actions" class="actions-cell">
                            <button class="btn btn-sm btn-outline-primary view-details"
                                    data-details='${JSON.stringify(returnItem)}'
                                    title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                            ${returnItem.is_bulk && returnItem.grouped_items && returnItem.grouped_items.length > 0 ? `
                            <button class="btn btn-sm btn-outline-info view-items-by-condition"
                                    data-condition-group='${JSON.stringify(returnItem.grouped_items)}'
                                    data-condition-name="${returnItem.return_condition}"
                                    title="View Items for this Condition">
                                <i class="fas fa-list"></i>
                            </button>
                            ` : ''}
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
        attachViewDetailsListeners(); // Re-attach listeners for new elements
        attachViewItemsByConditionListeners(); // Attach listeners for the new button
    }

    // Function to attach event listeners to "View Details" buttons
    function attachViewDetailsListeners() {
        document.querySelectorAll('.view-details').forEach(button => {
            button.onclick = function() {
                const returnData = JSON.parse(this.getAttribute('data-details'));
                const modalContent = document.getElementById('modalDetailsContent');
                const photo_src = returnData.res_photo ? '../logic/inventory/uploads/' + returnData.res_photo : 'images/default-item.jpg';
                const condition_class = getConditionColorJS(returnData.return_condition);

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
                                    <span>${returnData.res_name}</span>
                                </div>
                                <div class="detail-item">
                                    <strong>Resource Description:</strong>
                                    <span>${returnData.res_description || 'N/A'}</span>
                                </div>
                                <div class="detail-item">
                                    <strong>Resource Type:</strong>
                                    <span>${returnData.is_bulk ? 'Bulk (Multiple Items)' : 'Single Item'}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <div class="return-request-details">
                                <h6><i class="fas fa-exchange-alt me-2"></i> Return & Request Information</h6>
                                <div class="detail-item">
                                    <strong>Return ID:</strong>
                                    <span>#${returnData.return_id}</span>
                                </div>
                                <div class="detail-item">
                                    <strong>Quantity Returned:</strong>
                                    <span>${returnData.req_quantity}</span>
                                </div>
                                <div class="detail-item">
                                    <strong>Return Date:</strong>
                                    <span>${formatDate(returnData.return_date)}</span>
                                </div>
                                <div class="detail-item">
                                    <strong>Borrowed On:</strong>
                                    <span>${formatDateTime(returnData.borrow_timestamp)}</span> <!-- UPDATED DATA -->
                                </div>
                                <div class="detail-item">
                                    <strong>Returned On:</strong>
                                    <span>${formatDateTime(returnData.return_timestamp)}</span> <!-- UPDATED DATA -->
                                </div>
                                <div class="detail-item status-info">
                                    <strong>Condition:</strong>
                                    <span class="badge badge-${condition_class}">
                                        ${returnData.return_condition}
                                    </span>
                                </div>
                                ${returnData.notes ? `
                                <div class="detail-item">
                                    <strong>Notes:</strong>
                                    <span class="purpose-text">${returnData.notes}</span>
                                </div>` : ''}
                                <hr>
                                <div class="detail-item">
                                    <strong>Requester Name:</strong>
                                    <span>${returnData.requester_name}</span>
                                </div>
                                <div class="detail-item">
                                    <strong>Requester Barangay:</strong>
                                    <span>${returnData.requester_brgy}</span>
                                </div>
                                <div class="detail-item">
                                    <strong>Request Date:</strong>
                                    <span>${formatDate(returnData.req_date)}</span>
                                </div>
                                <div class="detail-item">
                                    <strong>Contact Number:</strong>
                                    <span>${returnData.req_contact_number || 'N/A'}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                const detailsModal = new bootstrap.Modal(document.getElementById('detailsModal'));
                detailsModal.show();
            };
        });
    }

    // Function to attach event listeners to "View Items by Condition" buttons
    function attachViewItemsByConditionListeners() {
        document.querySelectorAll('.view-items-by-condition').forEach(button => {
            button.onclick = function() {
                const groupedItems = JSON.parse(this.getAttribute('data-condition-group'));
                const conditionName = this.getAttribute('data-condition-name');
                const conditionClass = getConditionColorJS(conditionName); // Get class for header color

                const itemDetailsModalHeader = document.getElementById('itemDetailsModalHeader');
                const itemDetailsCondition = document.getElementById('itemDetailsCondition');
                const itemDetailsTableBody = document.getElementById('itemDetailsTableBody');

                // Update modal header background color
                itemDetailsModalHeader.classList.remove('bg-success', 'bg-warning', 'bg-danger', 'bg-secondary', 'bg-info'); // Remove existing
                itemDetailsModalHeader.classList.add(`bg-${conditionClass}`); // Add new

                itemDetailsCondition.textContent = conditionName;
                itemDetailsTableBody.innerHTML = ''; // Clear previous content

                if (groupedItems && groupedItems.length > 0) {
                    groupedItems.forEach(item => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${item.serial_number || 'N/A'}</td>
                            <td>${item.condition}</td>
                        `;
                        itemDetailsTableBody.appendChild(row);
                    });
                } else {
                    itemDetailsTableBody.innerHTML = '<tr><td colspan="2" class="text-center">No specific items recorded for this condition.</td></tr>';
                }

                const itemDetailsModal = new bootstrap.Modal(document.getElementById('itemDetailsModal'));
                itemDetailsModal.show();
            };
        });
    }


    // Filter and Search functionality
    function filterReturns() {
        const condition = conditionFilter.value.toLowerCase();
        const searchTerm = searchInput.value.toLowerCase();

        const filteredData = returnsData.filter(item => {
            const itemCondition = item.return_condition.toLowerCase();
            const itemSearch = `${item.res_name} ${item.requester_name} ${item.requester_brgy}`.toLowerCase();

            let conditionMatch = false;
            if (condition === 'all') {
                conditionMatch = true;
            } else if (condition === 'good') {
                conditionMatch = itemCondition.includes('good');
            } else if (itemCondition.includes('minor scratches') && condition === 'minor scratches') {
                conditionMatch = true;
            } else if (itemCondition.includes('damaged') && condition === 'damaged') {
                conditionMatch = true;
            } else if (itemCondition.includes('lost') && condition === 'lost') {
                conditionMatch = true;
            } else if (condition === 'other') {
                // Check if the condition is NOT one of the predefined ones
                conditionMatch = !(itemCondition.includes('good') ||
                                   itemCondition.includes('minor scratches') ||
                                   itemCondition.includes('damaged') ||
                                   itemCondition.includes('lost'));
            }

            const searchMatch = itemSearch.includes(searchTerm);

            return conditionMatch && searchMatch;
        });

        renderTable(filteredData); // Render the table with filtered data

        // Handle no data message
        if (filteredData.length === 0) {
            if (returnsData.length === 0) {
                noDataMessageDiv.querySelector('h5').textContent = 'No Returned Resources Yet';
                noDataMessageDiv.querySelector('p').textContent = 'There are no records of returned resources in your barangay.';
            } else {
                noDataMessageDiv.querySelector('h5').textContent = 'No Matching Returns Found';
                noDataMessageDiv.querySelector('p').textContent = 'Your current filters did not return any matching resources.';
            }
            noDataMessageDiv.style.display = 'block';
        } else {
            noDataMessageDiv.style.display = 'none';
        }
    }

    // Event Listeners
    conditionFilter.addEventListener('change', filterReturns);
    searchInput.addEventListener('keyup', filterReturns);

});