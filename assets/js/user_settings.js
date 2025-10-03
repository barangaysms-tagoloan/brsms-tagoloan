// Toggle password visibility
function togglePasswordVisibility(inputId) {
    const passwordInput = document.getElementById(inputId);
    const passwordToggle = passwordInput.nextElementSibling;
    const eyeIcon = passwordToggle.querySelector('i');

    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        eyeIcon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        eyeIcon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

// Toast Notification
function showToast(message, type = 'success') {
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container';
        document.body.appendChild(toastContainer);
    }

    const toastEl = document.createElement('div');
    toastEl.className = 'toast';
    toastEl.setAttribute('role', 'alert');
    toastEl.setAttribute('aria-live', 'assertive');
    toastEl.setAttribute('aria-atomic', 'true');
    toastEl.setAttribute('data-bs-delay', '3000');

    let iconClass = '';
    let bgColorClass = '';

    switch (type) {
        case 'success':
            iconClass = 'fas fa-check-circle';
            bgColorClass = 'text-bg-success';
            break;
        case 'error':
            iconClass = 'fas fa-times-circle';
            bgColorClass = 'text-bg-danger';
            break;
        case 'warning':
            iconClass = 'fas fa-exclamation-triangle';
            bgColorClass = 'text-bg-warning';
            break;
        case 'info':
            iconClass = 'fas fa-info-circle';
            bgColorClass = 'text-bg-info';
            break;
        default:
            iconClass = 'fas fa-info-circle';
            bgColorClass = 'text-bg-secondary';
    }

    toastEl.classList.add(bgColorClass);
    toastEl.innerHTML = `
        <div class="toast-body">
            <i class="${iconClass}"></i> <span>${message}</span>
        </div>
    `;

    toastContainer.appendChild(toastEl);
    const newToast = new bootstrap.Toast(toastEl);
    newToast.show();

    toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
}

// Logout confirmation
function confirmLogout() {
    Swal.fire({
        title: 'Confirm Logout',
        text: 'Are you sure you want to log out?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Logout',
        cancelButtonText: 'Cancel'
    }).then(result => {
        if (result.isConfirmed) {
            window.location.href = 'logout.php';
        }
    });
}

// Clear all inline error messages
function clearInlineErrors() {
    document.querySelectorAll('.inline-error').forEach(errorDiv => {
        errorDiv.textContent = '';
        errorDiv.style.display = 'none';
    });
    document.querySelectorAll('.form-control').forEach(input => {
        input.classList.remove('is-invalid');
    });
}

// Display inline errors
function displayInlineErrors(errors) {
    clearInlineErrors();
    for (const fieldId in errors) {
        const errorDiv = document.getElementById(fieldId + '_error');
        const inputField = document.getElementById(fieldId);

        if (errorDiv) {
            errorDiv.textContent = errors[fieldId];
            errorDiv.style.display = 'block';
        }
        if (inputField) {
            inputField.classList.add('is-invalid');
        }
    }
}

document.addEventListener('DOMContentLoaded', function () {
    if (successMessage) {
        showToast(successMessage, 'success');
    }

    if (Object.keys(errorMessages).length > 0) {
        displayInlineErrors(errorMessages);
        if (errorMessages.db_error) {
            showToast(errorMessages.db_error, 'error');
        } else {
            showToast("Please correct the errors in the form.", 'error');
        }
    }

    // Handle showing/hiding sections
    const settingsCards = document.querySelectorAll('.settings-card');
    const settingsFormContainers = document.querySelectorAll('.settings-form-container');

    settingsCards.forEach(card => {
        card.addEventListener('click', function () {
            const targetSectionId = this.dataset.targetSection;
            const targetSection = document.getElementById(targetSectionId);

            settingsFormContainers.forEach(container => {
                if (container.id !== targetSectionId) {
                    container.classList.remove('active');
                    container.querySelectorAll('.inline-error').forEach(err => err.textContent = '');
                    container.querySelectorAll('.form-control').forEach(input => input.classList.remove('is-invalid'));
                }
            });

            if (targetSection) {
                targetSection.classList.toggle('active');
            }
        });
    });

    // Close sections with "X" button
    document.querySelectorAll('.btn-close-form').forEach(button => {
        button.addEventListener('click', function () {
            const sectionToClose = document.getElementById(this.dataset.closeSection);
            if (sectionToClose) {
                sectionToClose.classList.remove('active');
                sectionToClose.querySelectorAll('.inline-error').forEach(err => err.textContent = '');
                sectionToClose.querySelectorAll('.form-control').forEach(input => input.classList.remove('is-invalid'));
            }
        });
    });

    // Clear inline errors on input (password form)
    document.getElementById('passwordChangeForm')?.querySelectorAll('.form-control').forEach(input => {
        input.addEventListener('input', function () {
            const errorDiv = document.getElementById(this.id + '_error');
            if (errorDiv) {
                errorDiv.textContent = '';
                errorDiv.style.display = 'none';
            }
            this.classList.remove('is-invalid');
        });
    });

    // Clear inline errors on input (profile form)
    document.getElementById('profileUpdateForm')?.querySelectorAll('.form-control').forEach(input => {
        input.addEventListener('input', function () {
            const errorDiv = document.getElementById(this.id + '_error');
            if (errorDiv) {
                errorDiv.textContent = '';
                errorDiv.style.display = 'none';
            }
            this.classList.remove('is-invalid');
        });
    });

    // Profile picture preview
    const profilePictureInput = document.getElementById('profile_picture_input');
    const profilePicturePreview = document.getElementById('profilePicturePreview');

    if (profilePictureInput && profilePicturePreview) {
        profilePictureInput.addEventListener('change', function (event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = e => profilePicturePreview.src = e.target.result;
                reader.readAsDataURL(file);
            } else {
                profilePicturePreview.src = "<?php echo htmlspecialchars($profile_photo_path); ?>";
            }

            const errorDiv = document.getElementById('profile_picture_error');
            if (errorDiv) {
                errorDiv.textContent = '';
                errorDiv.style.display = 'none';
            }
            this.classList.remove('is-invalid');
        });
    }

    // Logout button
    document.getElementById('logoutButton').addEventListener('click', function (event) {
        event.preventDefault();
        confirmLogout();
    });
});
