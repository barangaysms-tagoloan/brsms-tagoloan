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

document.addEventListener('DOMContentLoaded', function() {
    // Toast Notification Logic (copied and adapted from login.php)
    const actionToast = new bootstrap.Toast(document.getElementById('actionToast'), {
        delay: 3000 // Set delay to 3000 milliseconds (3 seconds)
    });

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
});