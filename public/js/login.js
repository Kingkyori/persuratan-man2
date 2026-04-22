// Login Page JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Toggle Password Visibility
    const togglePasswordBtn = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');

    if (togglePasswordBtn && passwordInput) {
        togglePasswordBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Toggle password visibility
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                togglePasswordBtn.textContent = '🙈';  // Closed eye when showing
                togglePasswordBtn.title = 'Sembunyikan password';
            } else {
                passwordInput.type = 'password';
                togglePasswordBtn.textContent = '👁️';  // Open eye when hiding
                togglePasswordBtn.title = 'Tampilkan password';
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
