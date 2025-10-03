document.getElementById('spinLogo').addEventListener('click', function () {
  const logo = this;
  logo.classList.add('spin-animation');
  logo.addEventListener('animationend', () => {
    logo.classList.remove('spin-animation');
  }, { once: true });
});

// Client-side validation for email format and empty fields on initial login form
document.getElementById('loginForm')?.addEventListener('submit', function(event) {
  const usernameInput = document.getElementById('usernameInput');
  const usernameError = document.getElementById('usernameError');
  const passwordInput = document.getElementById('passwordInput'); 
  const passwordError = document.getElementById('passwordError'); 
  const emailPattern = new RegExp("^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}$");
  let isValid = true; 

  // Reset states
  usernameInput.classList.remove('is-invalid');
  usernameError.style.display = 'none';
  passwordInput.classList.remove('is-invalid');
  passwordError.style.display = 'none';

  // Validate Username
  if (usernameInput.value.trim() === '') {
    usernameInput.classList.add('is-invalid');
    usernameError.innerHTML = '<i class="fas fa-exclamation-circle"></i> This field is required.';
    usernameError.style.display = 'block';
    isValid = false;
  } else if (!emailPattern.test(usernameInput.value)) {
    usernameInput.classList.add('is-invalid');
    if (usernameInput.value.indexOf('@') === -1) {
      usernameError.innerHTML = '<i class="fas fa-exclamation-circle"></i> Please include an \'@\' in the email address.';
    } else {
      usernameError.innerHTML = '<i class="fas fa-exclamation-circle"></i> Invalid email format.';
    }
    usernameError.style.display = 'block';
    isValid = false;
  }

  // Validate Password
  if (passwordInput.value.trim() === '') {
    passwordInput.classList.add('is-invalid');
    passwordError.innerHTML = '<i class="fas fa-exclamation-circle"></i> This field is required.';
    passwordError.style.display = 'block';
    isValid = false;
  }

  if (!isValid) {
    event.preventDefault(); 
  }
});

// Hide error on typing
document.getElementById('usernameInput')?.addEventListener('input', function() {
  const usernameError = document.getElementById('usernameError');
  this.classList.remove('is-invalid');
  usernameError.style.display = 'none';
});

document.getElementById('passwordInput')?.addEventListener('input', function() {
  const passwordError = document.getElementById('passwordError');
  this.classList.remove('is-invalid');
  passwordError.style.display = 'none';
});

// OTP Input Logic
document.addEventListener('DOMContentLoaded', function() {
  const otpInputs = document.querySelectorAll('#otpFormSection .otp-input');
  const hiddenOtpInput = document.getElementById('hiddenOtpInput');
  const otpVerificationForm = document.getElementById('otpVerificationForm');

  otpInputs.forEach((input, index) => {
    // Auto-advance when typing a digit
    input.addEventListener('input', function () {
      this.value = this.value.replace(/[^0-9]/g, ''); // only digits
      if (this.value.length === 1 && index < otpInputs.length - 1) {
        otpInputs[index + 1].focus();
      }
      updateHiddenOtpInput();
    });

    // Move back on Backspace
    input.addEventListener('keydown', function (e) {
      if (e.key === 'Backspace' && this.value === '' && index > 0) {
        otpInputs[index - 1].focus();
      }
    });

    // Handle paste (from any box)
    input.addEventListener('paste', function (e) {
      e.preventDefault();
      const pasteData = e.clipboardData.getData('text').trim();
      if (/^\d+$/.test(pasteData)) {
        for (let i = 0; i < otpInputs.length; i++) {
          otpInputs[i].value = pasteData.charAt(i) || '';
        }
        updateHiddenOtpInput();
        const nextEmpty = Array.from(otpInputs).find(inp => inp.value === '');
        (nextEmpty || otpInputs[otpInputs.length - 1]).focus();
      }
    });
  });

  function updateHiddenOtpInput() {
    let combinedOtp = '';
    otpInputs.forEach(input => {
      combinedOtp += input.value;
    });
    hiddenOtpInput.value = combinedOtp;
  }

  if (otpVerificationForm) {
    otpVerificationForm.addEventListener('submit', function(e) {
      updateHiddenOtpInput();
      if (hiddenOtpInput.value.length !== otpInputs.length) {
        alert('Please enter the complete 6-digit OTP.');
        e.preventDefault();
      }
    });
  }

  if (otpInputs.length > 0) {
    otpInputs[0].focus();
  }
});
