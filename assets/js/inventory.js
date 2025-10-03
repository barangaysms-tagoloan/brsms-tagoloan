// Logout Confirmation Function using SweetAlert2
    function confirmLogout() {
        Swal.fire({
            title: 'Confirm Logout',
            text: 'Are you sure you want to log out?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#dc3545', // Red for logout
            cancelButtonColor: '#6c757d', // Grey for cancel
            confirmButtonText: 'Yes, Logout',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'logout.php'; // Redirect to logout.php if confirmed
            }
        });
    }

    // Unified Custom Alert Modal Functions
    function showCustomAlert(type, title, message, actions) {
        const overlay = document.getElementById('unifiedCustomAlertOverlay');
        const modal = document.getElementById('unifiedCustomAlertModal');
        const iconDiv = modal.querySelector('.modal-icon');
        const titleDiv = modal.querySelector('.modal-title');
        const messageDiv = modal.querySelector('.modal-message');
        const actionsDiv = modal.querySelector('.modal-actions');

        // Reset classes and content
        modal.classList.remove('success', 'error', 'warning', 'info', 'borrowed', 'qr-view');
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
        } else if (type === 'qr-view') { // Custom type for QR view
            modal.classList.add('qr-view');
            // No icon for qr-view, so iconDiv remains empty
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
        // Re-open the items modal if it was the one that triggered the QR view
        if (window.itemsModalOpen) {
            $('#itemsModal').modal('show');
        }
    }

    // Close modal on overlay click
    document.getElementById('unifiedCustomAlertOverlay').addEventListener('click', (e) => {
        if (e.target === document.getElementById('unifiedCustomAlertOverlay')) {
            hideCustomAlert();
        }
    });


    $(document).ready(function() {
        let currentResId = null;
        let currentFilterStatus = 'All'; // Store the current filter status
        // Removed searchTimeout as we're doing client-side filtering without delay

        // Global flag to indicate if itemsModal is open and triggered the QR view
        window.itemsModalOpen = false;

        // Display PHP messages on page load using the custom alert
        const phpSuccessMessage = $('#php_success_message').val();
        const phpErrorMessage = $('#php_error_message').val();

        if (phpSuccessMessage) {
            showCustomAlert('success', 'Success!', phpSuccessMessage, [{ text: 'OK', class: 'btn-primary-action' }]);
        } else if (phpErrorMessage) {
            showCustomAlert('error', 'Error!', phpErrorMessage, [{ text: 'OK', class: 'btn-primary-action' }]);
        }

        // Handle Add Resource Form Submission
        $('#addModal form').submit(function(e) {
            var $button = $(this).find('button[type="submit"]');
            // Disable the button and show a spinner
            $button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Adding...');
        });

        // Re-enable the button when the modal is hidden (after form submission or cancellation)
        $('#addModal').on('hidden.bs.modal', function () {
            $('#addModal form').find('button[type="submit"]').prop('disabled', false).text('Add Resource');
        });

        // Handle Edit Resource Form Submission
        $('#editModal form').submit(function(e) {
            var $button = $(this).find('button[type="submit"]');
            // Disable the button and show a spinner
            $button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Updating...');
        });

        // Re-enable the button when the modal is hidden (after form submission or cancellation)
        $('#editModal').on('hidden.bs.modal', function () {
            $('#editModal form').find('button[type="submit"]').prop('disabled', false).text('Update Resource');
        });

        // NEW FUNCTION: Update status cards in the items modal
        function updateStatusCards(resId) {
            $.get('../get/get_resource_breakdown.php?res_id=' + resId, function(breakdown) {
                $('#availableItemsCount').text(breakdown.available);
                $('#borrowedItemsCount').text(breakdown.borrowed);
                $('#maintenanceItemsCount').text(breakdown.maintenance);
                $('#lostItemsCount').text(breakdown.lost); // Update lost items count
            }).fail(function(jqXHR, textStatus, errorThrown) {
                console.error("Error fetching resource breakdown:", textStatus, errorThrown);
            });
        }

        function loadItems(resId, filterStatus = 'All') {
            currentResId = resId;
            currentFilterStatus = filterStatus; // Update stored filter status
            const tableBody = $('#itemsTableBody');
            tableBody.empty();
            $('#selectAllItems').prop('checked', false);

            let url = '../get/get_items.php?res_id=' + resId;
            if (filterStatus !== 'All') {
                url += '&item_status=' + filterStatus;
            }

            $.get(url, function(items) {
                if (items.length > 0) {
                    // Sort items by item_id to ensure consistent numbering
                    items.sort((a, b) => a.item_id - b.item_id);

                    $.each(items, function(index, item) {
                        const qrCodePath = item.qr_code ? '/logic/inventory/qrcodes/' + item.qr_code : 'qrcodes/default_qr.png';

                        const row = $('<tr>').append(
                            $('<td>').append($('<input>').attr({
                                type: 'checkbox',
                                class: 'item-checkbox',
                                'data-itemid': item.item_id
                            })),
                            $('<td>').text(item.serial_number || 'N/A'), // Display serial number
                            $('<td>').append(
                                $('<select>').addClass('form-select form-select-sm item-status').data('itemid', item.item_id).append(
                                    $('<option>').val('Available').text('Available').prop('selected', item.item_status === 'Available'),
                                    $('<option>').val('Borrowed').text('Borrowed').prop('selected', item.item_status === 'Borrowed'),
                                    $('<option>').val('Under Maintenance').text('Under Maintenance').prop('selected', item.item_status === 'Under Maintenance'),
                                    $('<option>').val('Lost').text('Lost').prop('selected', item.item_status === 'Lost') // Added 'Lost'
                                )
                            ),
                            $('<td>').append(
                                $('<button>').addClass('btn btn-sm btn-primary view-qr').data('qrcode', qrCodePath).data('itemname', item.serial_number || 'N/A').html('<i class="fas fa-qrcode"></i> View QR')
                            ),
                            $('<td>').append(
                                $('<button>').addClass('btn btn-sm btn-primary update-status').data('itemid', item.item_id).html('<i class="fas fa-sync-alt"></i> Update')
                            )
                        );
                        tableBody.append(row);
                    });
                } else {
                    tableBody.append('<tr><td colspan="5" class="text-center text-muted py-3">No individual items found for this status.</td></tr>');
                }
            }).fail(function(jqXHR, textStatus, errorThrown) {
                let errorMessage = 'Error loading items. Please try again.';
                try {
                    let response = JSON.parse(jqXHR.responseText);
                    if (response && response.error) {
                        errorMessage = 'Error: ' + response.error;
                    }
                } catch (e) {
                }
                showCustomAlert('error', 'Loading Error', errorMessage, [{ text: 'OK', class: 'btn-primary-action' }]);
            });
        }

        // Function to load categories into the Manage Categories Modal
        function loadCategories() {
            const tableBody = $('#categoriesTableBody');
            tableBody.empty(); // Clear existing categories

            $.get('../get/get_categories.php', function(categories) {
                if (categories.length > 0) {
                    $.each(categories, function(index, category) {
                        const row = $('<tr>').append(
                            $('<td>').text(category.category_name),
                            $('<td>').append(
                                $('<button>').addClass('btn btn-sm btn-danger delete-category-btn').data('categoryid', category.category_id).data('categoryname', category.category_name).html('<i class="fas fa-trash"></i> Delete')
                            )
                        );
                        tableBody.append(row);
                    });
                } else {
                    tableBody.append('<tr><td colspan="2" class="text-center text-muted py-3">No categories found.</td></tr>');
                }
            }).fail(function(jqXHR, textStatus, errorThrown) {
                showCustomAlert('error', 'Loading Error', 'Error loading categories. Please try again.', [{ text: 'OK', class: 'btn-primary-action' }]);
            });
        }

        // Edit modal setup
        $('#editModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var modal = $(this);

            modal.find('#edit_id').val(button.data('id'));
            modal.find('#edit_name').val(button.data('name'));
            modal.find('#edit_description').val(button.data('description'));
            modal.find('#edit_quantity').val(button.data('quantity'));
            modal.find('#current_photo').attr('src', button.data('photo'));
            modal.find('#edit_category_id').val(button.data('categoryid'));
        });

        // Items modal setup
        $('#itemsModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var resId = button.data('resid');

            $('#itemsResourceName').text(button.data('name'));
            // Initial load of status counts
            updateStatusCards(resId);

            $('#selectAllItems').prop('checked', false);
            $('#bulk_status_select').val('');

            loadItems(resId, 'All'); // Load all items by default when modal opens
        });

        // Manage Categories Modal setup
        $('#manageCategoriesModal').on('show.bs.modal', function(event) {
            loadCategories(); // Load categories when the modal is shown
            $('#new_category_name').val(''); // Clear the add category input
        });

        // Handle click on status cards to filter items
        $(document).on('click', '#itemsModal .status-card', function() {
            const filterStatus = $(this).data('filter-status');
            if (currentResId) {
                loadItems(currentResId, filterStatus);
            }
        });

        // Handle single item status updates
        $(document).on('click', '.update-status', function() {
            var itemId = $(this).data('itemid');
            var newStatus = $(this).closest('tr').find('.item-status').val();
            var $button = $(this);

            $button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Updating...');

            $.ajax({
                url: '../pages/inventory.php',
                method: 'POST',
                data: {
                    update_item_status: true,
                    item_id: itemId,
                    status: newStatus
                },
                dataType: 'json', // Expect JSON response
                success: function(data) {
                    if (data.success) {
                        showCustomAlert('success', 'Success!', data.message, [{ text: 'OK', class: 'btn-primary-action' }]);
                        if (currentResId) {
                            // Reload items with the current filter to maintain state
                            loadItems(currentResId, currentFilterStatus);
                            // NEW: Update status cards after successful single item update
                            updateStatusCards(currentResId);
                        }
                    } else {
                        showCustomAlert('error', 'Error!', data.message, [{ text: 'OK', class: 'btn-primary-action' }]);
                        $button.prop('disabled', false).html('<i class="fas fa-sync-alt"></i> Update');
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    let errorMessage = 'An error occurred while updating status. Please try again.';
                    try {
                        let response = JSON.parse(jqXHR.responseText);
                        if (response && response.message) {
                            errorMessage = 'Error: ' + response.message;
                        }
                    } catch (e) {
                    }
                    showCustomAlert('error', 'Network Error', errorMessage, [{ text: 'OK', class: 'btn-primary-action' }]);
                    $button.prop('disabled', false).html('<i class="fas fa-sync-alt"></i> Update');
                }
            });
        });

        // Handle "Select All" checkbox
        $('#selectAllItems').change(function() {
            $('.item-checkbox').prop('checked', $(this).is(':checked'));
        });

        // Handle individual item checkbox change (to uncheck "Select All" if not all are selected)
        $(document).on('change', '.item-checkbox', function() {
            if (!$(this).is(':checked')) {
                $('#selectAllItems').prop('checked', false);
            } else {
                if ($('.item-checkbox:checked').length === $('.item-checkbox').length) {
                    $('#selectAllItems').prop('checked', true);
                }
            }
        });

        // Handle Bulk Update Button
        $('#bulkUpdateBtn').click(function() {
            var selectedItemIds = [];
            $('.item-checkbox:checked').each(function() {
                selectedItemIds.push($(this).data('itemid'));
            });

            var newStatus = $('#bulk_status_select').val();

            if (selectedItemIds.length === 0) {
                showCustomAlert('warning', 'No Items Selected', 'Please select at least one item to update.', [{ text: 'OK', class: 'btn-primary-action' }]);
                return;
            }

            if (newStatus === '') {
                showCustomAlert('warning', 'No Status Selected', 'Please select a status for the bulk update.', [{ text: 'OK', class: 'btn-primary-action' }]);
                return;
            }

            showCustomAlert(
                'warning',
                'Confirm Update',
                'Are you sure you want to update the status of ' + selectedItemIds.length + ' selected items to "<strong>' + newStatus + '</strong>"? This action cannot be undone.',
                [
                    { text: 'Cancel', class: 'btn-secondary-action' },
                    { text: 'Update', class: 'btn-primary-action', callback: () => {
                        var $button = $(this);
                        $button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Applying...');

                        $.ajax({
                            url: '../pages/inventory.php',
                            method: 'POST',
                            data: {
                                update_item_status: true,
                                item_id: selectedItemIds,
                                status: newStatus
                            },
                            dataType: 'json',
                            success: function(data) {
                                if (data.success) {
                                    showCustomAlert('success', 'Success!', data.message, [{ text: 'OK', class: 'btn-primary-action' }]);
                                    if (currentResId) {
                                        // Reload items with the current filter to maintain state
                                        loadItems(currentResId, currentFilterStatus);
                                        // NEW: Update status cards after successful bulk item update
                                        updateStatusCards(currentResId);
                                    }
                                } else {
                                    showCustomAlert('error', 'Error!', data.message, [{ text: 'OK', class: 'btn-primary-action' }]);
                                }
                                $button.prop('disabled', false).text('Apply to Selected');
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                let errorMessage = 'An error occurred during the bulk update. Please try again.';
                                try {
                                    let response = JSON.parse(jqXHR.responseText);
                                    if (response && response.message) {
                                        errorMessage = 'Error: ' + response.message;
                                    }
                                } catch (e) {
                                }
                                showCustomAlert('error', 'Network Error', errorMessage, [{ text: 'OK', class: 'btn-primary-action' }]);
                                $button.prop('disabled', false).text('Apply to Selected');
                            }
                        });
                    }}
                ]
            );
        });

        $(document).on('click', '.delete-resource-btn', function() {
            var resourceId = $(this).data('id');
            var resourceName = $(this).data('name');

            showCustomAlert(
                'error', // Type for styling
                'Confirm Deletion',
                'Are you sure you want to delete resource "<strong>' + resourceName + '</strong>"?<br><span class="text-danger small">This will permanently delete the resource and all its associated individual items.</span>',
                [
                    { text: 'Cancel', class: 'btn-secondary-action' },
                    { text: 'Delete', class: 'btn-primary-action', callback: () => {
                        // Submit the form for resource deletion
                        // We need to create a temporary form or use a hidden one
                        const form = $('<form method="POST" action="../pages/inventory.php" style="display:none;">' +
                                       '<input type="hidden" name="delete_id" value="' + resourceId + '">' +
                                       '<input type="hidden" name="delete_resource" value="1">' +
                                       '</form>');
                        $('body').append(form);
                        form.submit();
                    }}
                ]
            );
        });

        // --- Custom Category Delete Confirmation Modal Logic ---
        $(document).on('click', '.delete-category-btn', function() {
            var categoryId = $(this).data('categoryid');
            var categoryName = $(this).data('categoryname');

            showCustomAlert(
                'error', // Type for styling
                'Confirm Category Deletion',
                'Are you sure you want to delete the category "<strong>' + categoryName + '</strong>"?<br><span class="text-danger small">This action cannot be undone. You can only delete categories that have no resources assigned to them.</span>',
                [
                    { text: 'Cancel', class: 'btn-secondary-action' },
                    { text: 'Delete Category', class: 'btn-primary-action', callback: () => {
                        var $button = $(this);
                        $button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Deleting...');

                        $.ajax({
                            url: '../pages/inventory.php',
                            method: 'POST',
                            data: {
                                delete_category_submit: true,
                                category_id_to_delete: categoryId
                            },
                            dataType: 'json',
                            success: function(data) {
                                if (data.success) {
                                    showCustomAlert('success', 'Success!', data.message, [{ text: 'OK', class: 'btn-primary-action' }]);
                                    loadCategories(); // Reload categories in the modal
                                    $('#new_category_name').val(''); // Clear the input field
                                    updateCategoryDropdowns(); // Update all category dropdowns on the page
                                } else {
                                    showCustomAlert('error', 'Error!', data.message, [{ text: 'OK', class: 'btn-primary-action' }]);
                                }
                                $button.prop('disabled', false).html('<i class="fas fa-trash"></i> Delete');
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                let errorMessage = 'An error occurred during category deletion. Please try again.';
                                try {
                                    let response = JSON.parse(jqXHR.responseText);
                                    if (response && response.message) {
                                        errorMessage = 'Error: ' + response.message;
                                    }
                                } catch (e) {
                                }
                                showCustomAlert('error', 'Network Error', errorMessage, [{ text: 'OK', class: 'btn-primary-action' }]);
                                $button.prop('disabled', false).html('<i class="fas fa-trash"></i> Delete');
                            }
                        });
                    }}
                ]
            );
        });

        // NEW: Function to update category dropdowns on the main page and in modals
        function updateCategoryDropdowns() {
            $.get('../get/get_categories.php', function(categories) {
                // Update Add Resource Modal category dropdown
                const addCategorySelect = $('#addModal #category_id');
                addCategorySelect.empty().append('<option value="">Select a Category</option>');
                $.each(categories, function(index, category) {
                    addCategorySelect.append($('<option>').val(category.category_id).text(category.category_name));
                });

                // Update Edit Resource Modal category dropdown
                const editCategorySelect = $('#editModal #edit_category_id');
                editCategorySelect.empty().append('<option value="">Select a Category</option>');
                $.each(categories, function(index, category) {
                    editCategorySelect.append($('<option>').val(category.category_id).text(category.category_name));
                });

                // Update main inventory filter dropdown
                const filterCategorySelect = $('#category_filter');
                const currentFilterValue = filterCategorySelect.val(); // Preserve current selection
                filterCategorySelect.empty().append('<option value="All">All CATEGORY</option>');
                $.each(categories, function(index, category) {
                    filterCategorySelect.append($('<option>').val(category.category_id).text(category.category_name));
                });
                filterCategorySelect.val(currentFilterValue); // Restore selection
            }).fail(function(jqXHR, textStatus, errorThrown) {
                console.error("Error updating category dropdowns:", textStatus, errorThrown);
            });
        }


        // --- Filter and Search Logic (Client-side for search, server-side for category) ---
        function filterResourcesTable() {
            var searchInput = $('#searchInput').val().toLowerCase();
            // The category filter is still handled by PHP on page load/reload.
            // This client-side function will only filter the already loaded data.

            $('#inventoryTable tbody tr').each(function() {
                var resourceName = $(this).find('td:nth-child(2)').text().toLowerCase(); // Resource Name is in the second column (index 1)

                var showRow = true;

                // Filter by search input (resource name)
                if (searchInput && !resourceName.includes(searchInput)) {
                    showRow = false;
                }

                if (showRow) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }

        // Event Listeners for search and filter
        $('#searchInput').on('keyup', function() { // Changed to keyup for immediate filtering
            filterResourcesTable();
        });

        // The category filter still triggers a full page reload to apply server-side filtering.
        // If you want this to be client-side as well, you'd need to load all resources initially
        // and manage both filters purely in JavaScript.
        $('#category_filter').on('change', function() {
            // This will still trigger a page reload to apply the server-side category filter.
            // If you want client-side category filtering, you'd call filterResourcesTable() here
            // and ensure all data is loaded on page load.
            var url = new URL(window.location.href);
            // Preserve the current search input when changing category filter
            var currentSearch = $('#searchInput').val();
            if (currentSearch) {
                url.searchParams.set('search', currentSearch);
            } else {
                url.searchParams.delete('search');
            }
            url.searchParams.set('category_filter', $(this).val());
            window.location.href = url.toString();
        });


        // Add event handler for QR code viewing
        $(document).on('click', '.view-qr', function() {
            const qrCodePath = $(this).data('qrcode');
            const itemName = $(this).data('itemname'); // Get the item name from data attribute

            // Set flag that itemsModal is open and triggering QR view
            window.itemsModalOpen = true;
            // Hide the items modal without destroying it
            $('#itemsModal').modal('hide');

            // Show QR code in a modal or popup
            showCustomAlert(
                'qr-view', // Use the new custom type
                itemName, // Use the individual item name (which is now the serial number) as the title
                `<div class="qr-code-display"><img src="${qrCodePath}" alt="QR Code"><br>
                <a href="${qrCodePath}" download class="btn qr-download-btn"><i class="fas fa-download"></i> Download QR Code</a></div>`,
                [] // No actions (buttons) needed, as per request
            );
        });

        // Listen for the hidden.bs.modal event on the itemsModal
        // This ensures that if itemsModal is closed by its own close button,
        // the flag is reset.
        $('#itemsModal').on('hidden.bs.modal', function () {
            window.itemsModalOpen = false;
        });

        // Handle Add Category Form Submission via AJAX
        $('#addCategoryForm').submit(function(e) {
            e.preventDefault(); // Prevent default form submission

            var $form = $(this);
            var $button = $form.find('button[type="submit"]');
            $button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Adding...');

            $.ajax({
                url: '../pages/inventory.php', // The same PHP file handles the submission
                method: 'POST',
                data: $form.serialize(), // Serialize form data
                dataType: 'json', // Expect JSON response
                success: function(response) {
                    if (response.success) {
                        showCustomAlert('success', 'Success!', response.message, [{ text: 'OK', class: 'btn-primary-action' }]);
                        loadCategories(); // Reload categories in the modal
                        $('#new_category_name').val(''); // Clear the input field
                        updateCategoryDropdowns(); // Update all category dropdowns on the page
                    } else {
                        showCustomAlert('error', 'Error!', response.message, [{ text: 'OK', class: 'btn-primary-action' }]);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    let errorMessage = 'An error occurred while adding the category. Please try again.';
                    try {
                        let response = JSON.parse(jqXHR.responseText);
                        if (response && response.message) {
                            errorMessage = 'Error: ' + response.message;
                        }
                    } catch (e) {
                        // If response is not JSON, it might be a PHP error or unexpected output
                        errorMessage = 'An unexpected error occurred. Response: ' + jqXHR.responseText;
                    }
                    showCustomAlert('error', 'Network Error', errorMessage, [{ text: 'OK', class: 'btn-primary-action' }]);
                },
                complete: function() {
                    $button.prop('disabled', false).html('<i class="fas fa-plus me-1"></i> Add Category');
                }
            });
        });

        // Attach event listener to the logout button
        document.getElementById('logoutButton').addEventListener('click', function(event) {
            event.preventDefault(); // Prevent default link behavior
            confirmLogout(); // Call the confirmation function
        });
    });
