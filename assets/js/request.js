        let currentResourceId = null;
        let currentResourceDetails = {};
        let resourceAvailabilityData = {};
        let currentResourceView = 'cards';
        const customAlertModalOverlay = document.getElementById('customAlertModalOverlay');
        const customAlertModal = document.getElementById('customAlertModal');
        const customAlertIcon = document.getElementById('customAlertIcon');
        const customAlertTitle = document.getElementById('customAlertTitle');
        const customAlertMessage = document.getElementById('customAlertMessage');
        const customAlertPrimaryBtn = document.getElementById('customAlertPrimaryBtn');

        function showCustomAlert(type, title, message, primaryBtnText = 'OK', primaryBtnCallback = null) {
            customAlertModal.classList.remove('success', 'error', 'warning', 'info', 'borrowed', 'cancelled');
            customAlertIcon.className = 'modal-icon';

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
            }

            customAlertTitle.textContent = title;
            customAlertMessage.textContent = message;
            customAlertPrimaryBtn.textContent = primaryBtnText;

            customAlertPrimaryBtn.onclick = null;
            customAlertPrimaryBtn.addEventListener('click', () => {
                hideCustomAlert();
                if (primaryBtnCallback) {
                    primaryBtnCallback();
                }
            });

            customAlertModalOverlay.classList.add('show');
        }

        function hideCustomAlert() {
            customAlertModalOverlay.classList.remove('show');
        }

        customAlertModalOverlay.addEventListener('click', (e) => {
            if (e.target === customAlertModalOverlay) {
                hideCustomAlert();
            }
        });

        const partnerBarangaysSection = document.getElementById('partnerBarangaysSection');
        const resourcesFromSelectedBarangaySection = document.getElementById('resourcesFromSelectedBarangaySection');
        const requestFormContainer = document.getElementById('requestFormContainer');
        const selectedBrgyNameDisplay = document.getElementById('selectedBrgyNameDisplay');
        const resourcesContainer = document.getElementById('resourcesContainer');

        function showPartnerBarangays() {
            partnerBarangaysSection.style.display = 'flex';
            resourcesFromSelectedBarangaySection.style.display = 'none';
            requestFormContainer.style.display = 'none';
            currentSelectedBrgyId = null;
            currentSelectedBrgyName = "Choose a barangay...";
            document.getElementById('partnerBrgySearchInput').value = '';
            filterPartnerBarangays();
        }

        function loadResources(brgyId, brgyName) {
            currentSelectedBrgyId = brgyId;
            currentSelectedBrgyName = brgyName;
            selectedBrgyNameDisplay.textContent = brgyName;

            partnerBarangaysSection.style.display = 'none';
            resourcesFromSelectedBarangaySection.style.display = 'flex';
            requestFormContainer.style.display = 'none'; // Hide form when showing resources

            resourcesContainer.innerHTML = `
                <div class="no-resources-message">
                    <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <h5 class="mt-3 text-muted">Loading resources from ${brgyName}...</h5>
                </div>
            `;
            document.getElementById('resourceSearchInput').value = ''; // Clear resource search

            fetch(`../get/get_resources.php?brgy_id=${brgyId}&view_type=${currentResourceView}`) // Pass view_type
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.text();
                })
                .then(html => {
                    resourcesContainer.innerHTML = html;
                    if (currentResourceView === 'table') {
                        resourcesContainer.classList.add('table-view');
                    } else {
                        resourcesContainer.classList.remove('table-view');
                    }
                    filterResources()
                })
                .catch(error => {
                    console.error('Error:', error);
                    resourcesContainer.innerHTML = `
                        <div class="no-resources-message">
                            <i class="fas fa-exclamation-triangle fa-3x mb-3 text-danger"></i>
                            <h5>Error Loading Resources</h5>
                            <p class="mb-0">Failed to load resources from ${brgyName}. Please try again.</p>
                        </div>
                    `;
                });
        }

        function hideRequestForm() {
            requestFormContainer.style.display = 'none';
            resourcesFromSelectedBarangaySection.style.display = 'flex'; // Show resources section again
        }

        // Function to fetch and update datepicker availability
        function fetchResourceAvailability(resId, callback) {
            currentResourceId = resId;
            const today = new Date();
            const startDate = new Date(today.getFullYear(), today.getMonth(), 1);
            const endDate = new Date(today.getFullYear() + 1, today.getMonth(), 0);

            $.ajax({
                url: '../get/get_resource_availability.php',
                method: 'GET',
                data: {
                    res_id: resId,
                    start_date: startDate.toISOString().slice(0, 10),
                    end_date: endDate.toISOString().slice(0, 10)
                },
                dataType: 'json',
                success: function(data) {
                    if (data.error) {
                        showCustomAlert('error', 'Availability Error', data.error);
                        resourceAvailabilityData = {};
                    } else {
                        resourceAvailabilityData = data;
                    }
                    if (callback) callback();
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    showCustomAlert('error', 'Network Error', 'Failed to fetch resource availability.');
                    resourceAvailabilityData = {};
                    if (callback) callback();
                }
            });
        }

        // Function to update datepicker styling based on availability
        function updateDatepickerAvailability(date) {
            const dateString = $.datepicker.formatDate('yy-mm-dd', date);
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            let isSelectable = true;
            let className = '';
            let tooltip = '';

            if (date < today) {
                isSelectable = false;
                className = 'ui-datepicker-unselectable ui-state-disabled';
                tooltip = 'Past date';
            } else if (resourceAvailabilityData[dateString]) {
                const data = resourceAvailabilityData[dateString];
                if (data.available_quantity <= 0) {
                    isSelectable = false;
                    className = 'fully-booked ui-datepicker-unselectable ui-state-disabled';
                    tooltip = 'Fully booked';
                } else if (data.user_has_booking) {
                    className = 'user-booked';
                    tooltip = `You have a booking. Available: ${data.available_quantity}`;
                } else {
                    className = 'available-date';
                    tooltip = `Available: ${data.available_quantity}`;
                }
            } else {
                // If no specific data for the date, assume available
                className = 'available-date';
                tooltip = 'Available';
            }
            return [isSelectable, className, tooltip];
        }

        // Function to show the Notice Modal first
        function showNoticeModal(resId, resName, resDesc, resQty, brgyId, brgyName) {
            currentResourceDetails = {
                resId: resId,
                resName: resName,
                resDesc: resDesc, // Still pass description for now, though not used in modal
                resQty: resQty,
                brgyId: brgyId,
                brgyName: brgyName
            };

            const noticeModal = new bootstrap.Modal(document.getElementById('noticeModal'));
            const confirmNoticeCheckbox = document.getElementById('confirmNotice');
            const continueButton = document.getElementById('continueToRequestBtn');

            confirmNoticeCheckbox.checked = false;
            continueButton.disabled = true;

            confirmNoticeCheckbox.onchange = function() {
                continueButton.disabled = !this.checked;
            };

            continueButton.onclick = function() {
                noticeModal.hide();
                showRequestForm(
                    currentResourceDetails.resId,
                    currentResourceDetails.resName,
                    currentResourceDetails.resQty, // Pass quantity
                    currentResourceDetails.brgyId,
                    currentResourceDetails.brgyName
                );
            };

            noticeModal.show();
        }

        // Helper function to get dates in a range
        function getDatesInRange(startDate, endDate) {
            const dates = [];
            let currentDate = new Date(startDate);
            while (currentDate <= endDate) {
                dates.push(new Date(currentDate));
                currentDate.setDate(currentDate.getDate() + 1);
            }
            return dates;
        }

        // Function to update the available quantity display and max attribute of request quantity
        function updateAvailableQuantityDisplay() {
            const reqDateStr = $('input[name="req_date"]').val(); // Get YYYY-MM-DD from hidden field
            const returnDateStr = $('input[name="return_date"]').val(); // Get YYYY-MM-DD from hidden field
            const originalTotalQty = parseInt($('#modalOriginalTotalQty').val());

            let minAvailableQuantity = originalTotalQty; // Start with the maximum possible

            if (reqDateStr && returnDateStr) {
                const reqDate = new Date(reqDateStr);
                const returnDate = new Date(returnDateStr);

                if (returnDate < reqDate) {
                    // If return date is before request date, display 0 or an error
                    minAvailableQuantity = 0;
                } else {
                    const datesInRange = getDatesInRange(reqDate, returnDate);
                    for (const date of datesInRange) {
                        const dateString = $.datepicker.formatDate('yy-mm-dd', date);
                        if (resourceAvailabilityData[dateString]) {
                            minAvailableQuantity = Math.min(minAvailableQuantity, resourceAvailabilityData[dateString].available_quantity);
                        } else {
                            // If a date in the range has no specific availability data, assume full original quantity
                            minAvailableQuantity = Math.min(minAvailableQuantity, originalTotalQty);
                        }
                    }
                }
            } else if (reqDateStr) { // If only request date is selected
                const dateString = reqDateStr;
                if (resourceAvailabilityData[dateString]) {
                    minAvailableQuantity = resourceAvailabilityData[dateString].available_quantity;
                } else {
                    minAvailableQuantity = originalTotalQty;
                }
            } else if (returnDateStr) { // If only return date is selected
                const dateString = returnDateStr;
                if (resourceAvailabilityData[dateString]) {
                    minAvailableQuantity = resourceAvailabilityData[dateString].available_quantity;
                } else {
                    minAvailableQuantity = originalTotalQty;
                }
            } else {
                // If no dates are selected, revert to original total quantity
                minAvailableQuantity = originalTotalQty;
            }

            $('#modalAvailableQtyDisplay').val(minAvailableQuantity);
            $('#requestQuantity').attr('max', minAvailableQuantity);

            // Adjust requested quantity if it exceeds the new max
            let currentRequestedQty = parseInt($('#requestQuantity').val());
            if (isNaN(currentRequestedQty) || currentRequestedQty <= 0) {
                $('#requestQuantity').val(1);
            } else if (currentRequestedQty > minAvailableQuantity) {
                $('#requestQuantity').val(minAvailableQuantity);
            }
            validateFormAndEnableSubmit(); // Re-validate after updating quantity
        }


        // Modified showRequestForm to be called after notice confirmation
        function showRequestForm(resId, resName, resQty, brgyId, brgyName) {
            resourcesFromSelectedBarangaySection.style.display = 'none'; // Hide resources section
            requestFormContainer.style.display = 'flex'; // Show the request form container

            document.getElementById('modalResourceId').value = resId;
            document.getElementById('modalResourceName').value = resName;
            document.getElementById('modalOriginalTotalQty').value = resQty; // Store original total quantity
            document.getElementById('modalBrgyId').value = brgyId;
            document.getElementById('modalResourceOwner').value = brgyName;

            // Initialize display quantity and request quantity
            document.getElementById('modalAvailableQtyDisplay').value = resQty;
            document.getElementById('requestQuantity').value = 1;
            document.getElementById('requestQuantity').max = resQty;

            // Clear date fields and their hidden counterparts
            $('#requestDate').val('');
            $('input[name="req_date"]').remove(); // Remove existing hidden field if any
            $('#returnDate').val('');
            $('input[name="return_date"]').remove(); // Remove existing hidden field if any

            $('#dateRangeError').addClass('d-none').text('');
            $('#requestQuantity').removeClass('is-invalid');
            $('#quantityFeedback').text('');
            $('#requestContactNumber').val(''); // Clear contact number field
            $('#requestContactNumber').removeClass('is-invalid');
            $('#contactNumberFeedback').text('');
            $('#requestPurpose').val(''); // Clear purpose field
            $('#requestPurpose').removeClass('is-invalid');
            $('#purposeFeedback').text('');

            // Clear date specific feedback
            $('#requestDate').removeClass('is-invalid');
            $('#requestDateFeedback').text('Please select a date for borrowing.');
            $('#returnDate').removeClass('is-invalid');
            $('#returnDateFeedback').text('');


            const acceptTermsCheckbox = document.getElementById('acceptTermsCheckbox');
            const submitBtn = document.getElementById('submitBtn');
            acceptTermsCheckbox.checked = false;
            acceptTermsCheckbox.disabled = true; // Ensure checkbox is disabled initially
            submitBtn.disabled = true;

            acceptTermsCheckbox.onchange = function() {
                validateFormAndEnableSubmit();
            };

            fetchResourceAvailability(resId, function() {
                $(".datepicker").datepicker("destroy");
                $(".datepicker").datepicker({
                    dateFormat: 'yy-mm-dd', // This is the format the datepicker internally uses and passes to onSelect
                    altFormat: 'MM dd, yy', // This is the format for the altField
                    minDate: 0,
                    beforeShowDay: updateDatepickerAvailability,
                    onSelect: function(dateText, inst) {
                        // 'dateText' is already in 'yy-mm-dd' format due to dateFormat option
                        const selectedDate = new Date(dateText);
                        const formattedDisplayDate = $.datepicker.formatDate('MM dd, yy', selectedDate);

                        // Set the visible input field's value to the formatted display date
                        $(this).val(formattedDisplayDate);

                        // Create or update a hidden input field with the YYYY-MM-DD format for submission
                        // The original 'name' attribute for the visible input was 'req_date_display' or 'return_date_display'
                        // We want the hidden input to have the 'name' attribute 'req_date' or 'return_date'
                        const originalName = $(this).attr('id') === 'requestDate' ? 'req_date' : 'return_date';
                        let hiddenInput = $(`input[name="${originalName}"]`); // Check if hidden input already exists

                        if (hiddenInput.length === 0) {
                            hiddenInput = $('<input>')
                                .attr('type', 'hidden')
                                .attr('name', originalName) // Use the correct name for the hidden field
                                .appendTo($(this).parent());
                        }
                        hiddenInput.val(dateText); // Store YYYY-MM-DD in the hidden field

                        // The visible input's name is already different ('req_date_display'), so no need to remove it.
                        // This ensures the visible input doesn't get submitted, and the hidden one does.

                        updateAvailableQuantityDisplay(); // Update display and max quantity
                        validateDateRangeAndQuantity(); // Validate date range and quantity
                        validateFormAndEnableSubmit(); // Re-validate form
                    },
                    onChangeMonthYear: function(year, month, inst) {
                        // When month/year changes, re-fetch availability for the new range
                        fetchResourceAvailability(currentResourceId, function() {
                            $(inst.input).datepicker('refresh'); // Refresh the datepicker to apply new styles
                            updateAvailableQuantityDisplay(); // Update display and max quantity
                            validateDateRangeAndQuantity(); // Validate date range and quantity
                        });
                    }
                });
                updateAvailableQuantityDisplay(); // Initial update when form opens
                validateFormAndEnableSubmit(); // Initial validation to enable/disable checkbox
            });

            document.getElementById('submitText').classList.remove('d-d-none');
            document.getElementById('submitSpinner').classList.add('d-none');
        }

        function validateDateRangeAndQuantity() {
            // Get YYYY-MM-DD from the hidden fields for validation logic
            const reqDateStr = $('input[name="req_date"]').val();
            const returnDateStr = $('input[name="return_date"]').val();
            const requestedQty = parseInt($('#requestQuantity').val());
            const currentAvailableQty = parseInt($('#modalAvailableQtyDisplay').val()); // Use the displayed available quantity

            $('#dateRangeError').addClass('d-none').text('');
            $('#requestQuantity').removeClass('is-invalid');
            $('#quantityFeedback').text('');

            // Date specific validation
            if (!reqDateStr) {
                $('#requestDate').addClass('is-invalid');
                $('#requestDateFeedback').text('Please select a date for borrowing.');
            } else {
                $('#requestDate').removeClass('is-invalid');
                $('#requestDateFeedback').text('');
            }

            if (!returnDateStr) {
                $('#returnDate').addClass('is-invalid');
                $('#returnDateFeedback').text('Please select a date for returning.');
            } else {
                $('#returnDate').removeClass('is-invalid');
                $('#returnDateFeedback').text('');
            }

            if (!reqDateStr || !returnDateStr || isNaN(requestedQty) || requestedQty <= 0) {
                return false; // Not all fields are filled, so cannot fully validate date range yet
            }

            const reqDate = new Date(reqDateStr);
            const returnDate = new Date(returnDateStr);

            if (returnDate < reqDate) {
                $('#dateRangeError').removeClass('d-none').text('Return date cannot be before request date.');
                return false;
            }

            // Check if requested quantity exceeds the currently displayed available quantity
            if (requestedQty > currentAvailableQty) {
                $('#requestQuantity').addClass('is-invalid');
                $('#quantityFeedback').text(`Requested quantity (${requestedQty}) exceeds available quantity (${currentAvailableQty}) for the selected date range.`);
                return false;
            }

            let hasError = false;
            let errorMessage = '';
            let tempDate = new Date(reqDate);

            while (tempDate <= returnDate) {
                const dateString = $.datepicker.formatDate('yy-mm-dd', tempDate);
                const dayAvailability = resourceAvailabilityData[dateString];

                if (!dayAvailability) {
                    // If no specific availability data for the date, assume full original quantity
                    const originalTotalQty = parseInt($('#modalOriginalTotalQty').val());
                    if (requestedQty > originalTotalQty) {
                        errorMessage = `Requested quantity (${requestedQty}) exceeds total resource capacity (${originalTotalQty}) on ${dateString}.`;
                        hasError = true;
                        break;
                    }
                } else {
                    if (requestedQty > dayAvailability.available_quantity) {
                        errorMessage = `Only ${dayAvailability.available_quantity} units available on ${dateString}.`;
                        hasError = true;
                        break;
                    }
                }
                tempDate.setDate(tempDate.getDate() + 1);
            }

            if (hasError) {
                $('#dateRangeError').removeClass('d-none').text(errorMessage);
                $('#requestQuantity').addClass('is-invalid');
                $('#quantityFeedback').text(errorMessage);
                return false;
            }
            return true;
        }

        function validateFormAndEnableSubmit() {
            // Get YYYY-MM-DD from the hidden fields for validation logic
            const reqDateStr = $('input[name="req_date"]').val();
            const returnDateStr = $('input[name="return_date"]').val();
            const requestedQty = parseInt($('#requestQuantity').val());
            const requestPurpose = $('#requestPurpose').val();
            const requestContactNumber = $('#requestContactNumber').val();
            const acceptTermsCheckbox = $('#acceptTermsCheckbox');
            const submitBtn = $('#submitBtn');

            let allFieldsValid = true;

            // Validate Quantity
            const currentAvailableQty = parseInt($('#modalAvailableQtyDisplay').val());
            if (isNaN(requestedQty) || requestedQty <= 0 || requestedQty > currentAvailableQty) {
                $('#requestQuantity').addClass('is-invalid');
                $('#quantityFeedback').text('Please enter a valid quantity within the available range.');
                allFieldsValid = false;
            } else {
                $('#requestQuantity').removeClass('is-invalid');
                $('#quantityFeedback').text('');
            }

            // Validate Contact Number (Philippine mobile numbers: 09xxxxxxxxx, 11 digits)
            const phoneRegex = /^(09|\+639)\d{9}$/;
            if (!requestContactNumber.trim() || !phoneRegex.test(requestContactNumber)) {
                $('#requestContactNumber').addClass('is-invalid');
                $('#contactNumberFeedback').text('Please enter a valid Philippine mobile number (e.g., 09123456789 or +639123456789).');
                allFieldsValid = false;
            } else {
                $('#requestContactNumber').removeClass('is-invalid');
                $('#contactNumberFeedback').text('');
            }

            // Validate Purpose (e.g., not empty, minimum length)
            if (!requestPurpose.trim() || requestPurpose.trim().length < 15) { // Example: minimum 15 characters
                $('#requestPurpose').addClass('is-invalid');
                $('#purposeFeedback').text('Purpose is required and must be valid.');
                allFieldsValid = false;
            } else {
                $('#requestPurpose').removeClass('is-invalid');
                $('#purposeFeedback').text('');
            }

            // Validate Dates
            if (!reqDateStr) {
                $('#requestDate').addClass('is-invalid');
                $('#requestDateFeedback').text('Please select a date for borrowing.');
                allFieldsValid = false;
            } else {
                $('#requestDate').removeClass('is-invalid');
                $('#requestDateFeedback').text('');
            }

            if (!returnDateStr) {
                $('#returnDate').addClass('is-invalid');
                $('#returnDateFeedback').text('Please select a date for returning.');
                allFieldsValid = false;
            } else {
                $('#returnDate').removeClass('is-invalid');
                $('#returnDateFeedback').text('');
            }

            // Also run the date range and quantity availability validation if dates are selected
            if (reqDateStr && returnDateStr) {
                if (!validateDateRangeAndQuantity()) {
                    allFieldsValid = false;
                }
            }


            // Enable/disable acceptTermsCheckbox based on all other fields' validity
            if (allFieldsValid) {
                acceptTermsCheckbox.prop('disabled', false);
            } else {
                acceptTermsCheckbox.prop('disabled', true);
                acceptTermsCheckbox.prop('checked', false); // Uncheck if fields become invalid
            }

            // Enable/disable submit button based on all fields validity AND checkbox checked state
            if (allFieldsValid && acceptTermsCheckbox.is(':checked')) {
                submitBtn.prop('disabled', false);
            } else {
                submitBtn.prop('disabled', true);
            }
        }

        // Attach validation to all relevant fields
        // Note: We attach to the visible date inputs, but their validation logic uses the hidden values
        $('#requestDate, #returnDate, #requestQuantity, #requestPurpose, #requestContactNumber').on('change keyup', validateFormAndEnableSubmit);
        $('#acceptTermsCheckbox').on('change', validateFormAndEnableSubmit); // Also validate when checkbox changes

        document.getElementById('requestForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const submitBtn = document.getElementById('submitBtn');
            const submitText = document.getElementById('submitText');
            const submitSpinner = document.getElementById('submitSpinner');

            validateFormAndEnableSubmit(); // Re-validate just before submission

            if (submitBtn.disabled) {
                return;
            }

            submitBtn.disabled = true;
            submitText.classList.add('d-none');
            submitSpinner.classList.remove('d-none'); // Show spinner

            fetch('../get/process_request.php', {
                method: 'POST',
                body: new FormData(this)
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => { throw err; });
                }
                return response.json();
            })
            .then(data => {
                if (data.status === 'success') {
                    hideRequestForm(); // Hide the form container
                    showCustomAlert('success', 'Request Submitted!', data.message || 'Your request has been successfully submitted.', 'View Status', () => {
                        // Redirect to request_status.php after user clicks OK on success alert
                        window.location.href = 'request_status.php';
                    });
                } else {
                    // For error, do NOT redirect. Just show the alert and let the user stay on the page.
                    showCustomAlert('error', 'Request Failed!', data.message || 'There was an error submitting your request.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                let errorMessage = 'An unexpected error occurred. Please try again.';
                if (error && error.message) {
                    errorMessage = error.message;
                }
                // For error, do NOT redirect. Just show the alert and let the user stay on the page.
                showCustomAlert('error', 'Request Failed!', errorMessage);
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitText.classList.remove('d-none');
                submitSpinner.classList.add('d-none');
            });
        });

        // Filter for resources within a selected barangay
        document.getElementById('resourceSearchInput').addEventListener('keyup', filterResources);

        function filterResources() {
            const searchTerm = document.getElementById('resourceSearchInput').value.toLowerCase();
            let items;
            let anyVisible = false;

            if (currentResourceView === 'cards') {
                items = resourcesContainer.querySelectorAll('.resource-card');
                items.forEach(card => {
                    const resourceName = card.querySelector('h5').textContent.toLowerCase();
                    const resourceDesc = card.querySelector('p').textContent.toLowerCase();

                    if (resourceName.includes(searchTerm) || resourceDesc.includes(searchTerm)) {
                        card.style.display = 'flex';
                        anyVisible = true;
                    } else {
                        card.style.display = 'none';
                    }
                });
            } else if (currentResourceView === 'table') {
                items = resourcesContainer.querySelectorAll('.resource-table tbody tr');
                items.forEach(row => {
                    const resourceName = row.cells[1].textContent.toLowerCase(); // Assuming name is in the second column
                    const resourceDesc = row.cells[2].textContent.toLowerCase(); // Assuming description is in the third column

                    if (resourceName.includes(searchTerm) || resourceDesc.includes(searchTerm)) {
                        row.style.display = '';
                        anyVisible = true;
                    } else {
                        row.style.display = 'none';
                    }
                });
            }


            let noResourcesMessage = resourcesContainer.querySelector('.no-resources-message');

            // Remove existing "No Matching Resources" message if it's there
            if (noResourcesMessage && noResourcesMessage.dataset.type === 'no-match') {
                noResourcesMessage.remove();
                noResourcesMessage = null;
            }

            if (!anyVisible) {
                if (!noResourcesMessage) { // Only add if it doesn't exist
                    resourcesContainer.innerHTML = `
                        <div class="no-resources-message" data-type="no-match">
                            <i class="fas fa-exclamation-circle fa-3x mb-3 text-warning"></i>
                            <h5>No Matching Resources</h5>
                            <p class="mb-0">No resources found matching your search criteria from ${currentSelectedBrgyName}.</p>
                        </div>
                    `;
                } else {
                    noResourcesMessage.style.display = 'flex';
                }
            } else if (anyVisible && noResourcesMessage && noResourcesMessage.dataset.type === 'no-match') {
                // If resources are visible and a "No Matching Resources" message exists, remove it
                noResourcesMessage.remove();
            }
        }

        // Filter for partner barangays
        document.getElementById('partnerBrgySearchInput').addEventListener('keyup', filterPartnerBarangays);

        function filterPartnerBarangays() {
            const searchTerm = document.getElementById('partnerBrgySearchInput').value.toLowerCase();
            const barangayCards = document.querySelectorAll('#partnerBarangaysGrid .barangay-card');
            let anyVisible = false;

            barangayCards.forEach(card => {
                const brgyName = card.dataset.brgyName.toLowerCase(); // Corrected dataset access
                if (brgyName.includes(searchTerm)) {
                    card.style.display = 'flex';
                    anyVisible = true;
                } else {
                    card.style.display = 'none';
                }
            });

            const noPartnersMessage = document.querySelector('#partnerBarangaysGrid .no-resources-message.col-12');
            if (noPartnersMessage) {
                noPartnersMessage.style.display = anyVisible ? 'none' : 'flex';
            } else if (!anyVisible && document.querySelectorAll('#partnerBarangaysGrid .barangay-card').length > 0) {
                // If there are cards but none match, and no "no results" message exists, add one
                document.getElementById('partnerBarangaysGrid').innerHTML += `
                    <div class="no-resources-message col-12" data-type="no-match-partner">
                        <i class="fas fa-exclamation-circle fa-3x mb-3 text-warning"></i>
                        <h5>No Matching Partner Barangays</h5>
                        <p class="mb-0">No partner barangays found matching your search criteria.</p>
                    </div>
                `;
            } else if (anyVisible && document.querySelector('#partnerBarangaysGrid .no-resources-message[data-type="no-match-partner"]')) {
                // If cards become visible, remove the "no results" message
                document.querySelector('#partnerBarangaysGrid .no-resources-message[data-type="no-match-partner"]').remove();
            }
        }

        // Function to toggle resource view
        function toggleResourceView(viewType) {
            if (currentResourceView === viewType) return; // No change needed

            currentResourceView = viewType;

            // Update active state of buttons
            document.getElementById('cardsViewBtn').classList.remove('active');
            document.getElementById('tableViewBtn').classList.remove('active');
            document.getElementById(`${viewType}ViewBtn`).classList.add('active');

            // Reload resources with the new view type
            if (currentSelectedBrgyId) {
                loadResources(currentSelectedBrgyId, currentSelectedBrgyName);
            }
        }


        document.addEventListener('DOMContentLoaded', function() {
            const mainContentWrapper = document.getElementById('mainContentWrapper');

            const termsModalElement = document.getElementById('termsAndConditionsModal');
            const termsModal = new bootstrap.Modal(termsModalElement);
            const agreeButton = document.getElementById('agreeButton');
            const termsModalBody = termsModalElement.querySelector('.modal-body');

            function showTermsAndConditionsModal() {
                termsModal.show();
                agreeButton.disabled = true;
                termsModalBody.scrollTop = 0;
            }

            function redirectToDashboard() {
                window.location.href = 'dashboard.php';
            }

            termsModalBody.addEventListener('scroll', function() {
                // Check if scrolled to the very bottom
                if (termsModalBody.scrollHeight - termsModalBody.scrollTop <= termsModalBody.clientHeight + 1) {
                    agreeButton.disabled = false;
                } else {
                    agreeButton.disabled = true;
                }
            });

            agreeButton.addEventListener('click', function() {
                termsModal.hide();
                mainContentWrapper.style.display = 'flex'; // Changed to 'flex' to maintain column layout for main-content
            });

            termsModalElement.querySelector('.btn-cancel').addEventListener('click', redirectToDashboard);

            showTermsAndConditionsModal();

            // Initial state based on PHP pre-selection
            if (currentSelectedBrgyId !== null) {
                loadResources(currentSelectedBrgyId, currentSelectedBrgyName);
            } else {
                showPartnerBarangays(); // Show partner barangays by default
            }

            document.querySelectorAll('.modal').forEach(modalElement => {
                modalElement.addEventListener('hidden.bs.modal', function () {
                    const openModals = document.querySelectorAll('.modal.show');
                    if (openModals.length === 0) {
                        document.body.style.overflow = '';
                        document.body.style.paddingRight = '';
                        const backdrop = document.querySelector('.modal-backdrop');
                        if (backdrop) {
                            backdrop.remove();
                        }
                    }
                });
            });
        });