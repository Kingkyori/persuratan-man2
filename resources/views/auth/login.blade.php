<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MAN 2 Surakarta</title>
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <!-- Logo -->
            <div class="logo-container">
                <div class="logo">📋</div>
                <div class="logo-text">MAN 2 Surakarta</div>
                <div class="logo-subtitle">INSTITUTIONAL PORTAL</div>
            </div>

            <!-- Welcome Text -->
            <div class="welcome-text">
                <h1 class="welcome-title">Selamat Datang</h1>
                <p class="welcome-description">Silakan masuk untuk mengakses sistem administrasi.</p>
            </div>

            <!-- Error Message -->
            @if ($errors->any())
                <div class="error-message show">
                    <strong>Gagal Login!</strong>
                    {{ $errors->first('username') }}
                </div>
            @endif

            <!-- Form -->
            <form method="POST" action="{{ route('login') }}" id="loginForm">
                @csrf

                <!-- Username -->
                <div class="form-group">
                    <label for="username" class="form-label">Username atau Email</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        class="form-input" 
                        placeholder="Masukkan username"
                        value="{{ old('username') }}"
                        required
                        autofocus
                    >
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label for="password" class="form-label">Kata Sandi</label>
                    <div class="password-input-wrapper">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="form-input" 
                            placeholder="Masukkan kata sandi"
                            required
                        >
                        <button type="button" class="toggle-password" id="togglePassword">
                            👁️
                        </button>
                    </div>
                </div>

                <!-- Form Footer -->
                <div class="form-footer">
                    <label class="form-checkbox">
                        <input type="checkbox" name="remember" id="remember">
                        <label for="remember">Ingat saya</label>
                    </label>
                    <a href="#" class="forgot-password">Lupa sandi?</a>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-login">Masuk ke Sistem</button>

                <!-- Help Link -->
                <a href="#" class="btn-help">❓ BUTUH BANTUAN?</a>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <span>© 2024 MAN 2 Surakarta Administrative System. All rights reserved.</span>
        <a href="#">Privacy Policy</a>
        <a href="#">Terms of Service</a>
        <a href="#">Support</a>
    </footer>

    <script src="{{ asset('js/login.js') }}"></script>
</body>
</html>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <!-- Logo -->
            <div class="logo-container">
                <div class="logo">📋</div>
                <div class="logo-text">MAN 2 Surakarta</div>
                <div class="logo-subtitle">INSTITUTIONAL PORTAL</div>
            </div>

            <!-- Welcome Text -->
            <div class="welcome-text">
                <h1 class="welcome-title">Selamat Datang</h1>
                <p class="welcome-description">Silakan masuk untuk mengakses sistem administrasi.</p>
            </div>

            <!-- Error Message -->
            @if ($errors->any())
                <div class="error-message show">
                    <strong>Gagal Login!</strong><br>
                    {{ $errors->first('username') }}
                </div>
            @endif

            <!-- Form -->
            <form method="POST" action="{{ route('login') }}" id="loginForm">
                @csrf

                <!-- Username/Email -->
                <div class="form-group">
                    <label for="username" class="form-label">Username atau Email</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        class="form-input" 
                        placeholder="Masukkan username"
                        value="{{ old('username') }}"
                        required
                        autofocus
                    >
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label for="password" class="form-label">Kata Sandi</label>
                    <div class="password-input-wrapper">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="form-input" 
                            placeholder="Masukkan kata sandi"
                            required
                        >
                        <button type="button" class="toggle-password" id="togglePassword">
                            👁️
                        </button>
                    </div>
                </div>

                <!-- Form Footer -->
                <div class="form-footer">
                    <label class="form-checkbox">
                        <input type="checkbox" name="remember" id="remember">
                        <label for="remember">Ingat saya</label>
                    </label>
                    <a href="#" class="forgot-password">Lupa sandi?</a>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-login">Masuk ke Sistem</button>

                <!-- Help Link -->
                <a href="#" class="btn-help">❓ BUTUH BANTUAN?</a>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <div style="margin-top: 80px;"></div>
    <footer class="footer">
        <span style="margin-right: 20px;">© 2024 MAN 2 Surakarta Administrative System. All rights reserved.</span>
        <a href="#">Privacy Policy</a>
        <a href="#">Terms of Service</a>
        <a href="#">Support</a>
    </footer>

    <script>
        // Toggle Password Visibility
        const togglePasswordBtn = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');

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

        // Form validation
        const loginForm = document.getElementById('loginForm');
        const usernameInput = document.getElementById('username');
        const passwordInputField = document.getElementById('password');

        loginForm.addEventListener('submit', function(e) {
            if (!usernameInput.value.trim() || !passwordInputField.value.trim()) {
                e.preventDefault();
                alert('Silakan isi semua field yang diperlukan');
            }
        });
    </script>
</body>
</html>
