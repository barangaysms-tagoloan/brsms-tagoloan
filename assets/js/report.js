function updateStatusOptions(reportType) {
    const statusSelect = document.getElementById('statusSelect');
    const monthFilter = document.getElementById('monthFilter');
    const yearFilter = document.getElementById('yearFilter');
    const monthSelect = monthFilter.querySelector('select');
    const yearSelect = yearFilter.querySelector('select');

    // Preserve current selection if possible, otherwise default to 'all'
    const currentStatus = statusSelect.value;
    let newOptionsHtml = '<option value="" disabled selected>Select Status</option>'; // Always start with "Select Status"

    if (reportType === 'requests') {
        newOptionsHtml += `
            <option value="all">All Statuses</option>
            <option value="Pending">Pending</option>
            <option value="Approved">Approved</option>
            <option value="Rejected">Rejected</option>
            <option value="Borrowed">Borrowed</option>
            <option value="Cancelled">Cancelled</option>
            <option value="Completed">Completed</option>
        `;
        monthFilter.style.display = 'block'; // Show month filter
        yearFilter.style.display = 'block';  // Show year filter
        monthSelect.disabled = false;
        yearSelect.disabled = false;
    } else if (reportType === 'inventory') {
        newOptionsHtml += `
            <option value="all">All Statuses</option>
            <option value="Available">Available</option>
            <option value="Borrowed">Borrowed</option>
            <option value="Under Maintenance">Under Maintenance</option>
        `;
        monthFilter.style.display = 'none'; // Hide month filter
        yearFilter.style.display = 'none';  // Hide year filter
        monthSelect.disabled = true; // Disable month select
        yearSelect.disabled = true;  // Disable year select
        monthSelect.value = ''; // Clear month selection
        yearSelect.value = '';  // Clear year selection
    } else if (reportType === 'returns') { // returns
        newOptionsHtml += `
            <option value="all">All Conditions</option>
            <option value="good">Good</option>
            <option value="minor scratches">Minor Scratches</option>
            <option value="damaged">Damaged</option>
            <option value="lost">Lost</option>
            <option value="other">Other</option>
        `;
        monthFilter.style.display = 'block'; // Show month filter
        yearFilter.style.display = 'block';  // Show year filter
        monthSelect.disabled = false;
        yearSelect.disabled = false;
    }
    statusSelect.innerHTML = newOptionsHtml;
    // Attempt to re-select the previously selected status if it exists in new options
    if ([...statusSelect.options].some(option => option.value === currentStatus)) {
        statusSelect.value = currentStatus;
    } else {
        // If the previous status is not available, and it's not the initial empty state,
        // default to 'all' if 'all' is an option, otherwise keep 'Select Status'.
        if (reportType !== '' && [...statusSelect.options].some(option => option.value === 'all')) {
            statusSelect.value = 'all';
        } else {
            statusSelect.value = ''; // Keep "Select Status" selected
        }
    }
}

// Call on page load to ensure correct options are set based on initial report_type
document.addEventListener('DOMContentLoaded', function() {
    updateStatusOptions(document.querySelector('select[name="report_type"]').value);
});