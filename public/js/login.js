// Login Page JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Toggle Password Visibility
    const togglePasswordBtn = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');

    if (togglePasswordBtn) {
        togglePasswordBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const type = passwordInput.type === 'password' ? 'text' : 'password';
            passwordInput.type = type;

            // Change icon based on visibility
            if (type === 'password') {
                togglePasswordBtn.textContent = '👁️';
            } else {
                togglePasswordBtn.textContent = '👁️‍🗨️';
            }
        });
    }

    // Form validation
    const loginForm = document.getElementById('loginForm');
    const usernameInput = document.getElementById('username');
    const passwordInputField = document.getElementById('password');

    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            if (!usernameInput.value.trim() || !passwordInputField.value.trim()) {
                e.preventDefault();
                alert('Silakan isi semua field yang diperlukan');
            }
        });
    }
});
