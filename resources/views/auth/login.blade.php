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
            <div class="logo-container">
                <div class="logo">
                    <img src="{{ asset('images/man2.png') }}" alt="Logo MAN 2" class="logo-image">
                </div>
                <div class="logo-text">MAN 2 Surakarta</div>
                <div class="logo-subtitle">Institutional Portal</div>
            </div>

            <div class="welcome-text">
                <h1 class="welcome-title">Selamat Datang</h1>
                <p class="welcome-description">Silakan masuk untuk mengakses sistem administrasi.</p>
            </div>

            @if ($errors->any())
                <div class="error-message show">
                    <strong>Gagal Login!</strong>
                    {{ $errors->first('username') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" id="loginForm">
                @csrf

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
                        autocomplete="username"
                    >
                </div>

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
                            autocomplete="current-password"
                        >
                        <button
                            type="button"
                            class="toggle-password"
                            id="togglePassword"
                            aria-label="Tampilkan kata sandi"
                            aria-controls="password"
                            aria-pressed="false"
                        >
                            <span class="toggle-password__icon toggle-password__icon--show" aria-hidden="true">
                                <svg viewBox="0 0 24 24" focusable="false">
                                    <path d="M12 5C6.5 5 2.1 8.5 1 12c1.1 3.5 5.5 7 11 7s9.9-3.5 11-7c-1.1-3.5-5.5-7-11-7Zm0 11.2A4.2 4.2 0 1 1 12 7.8a4.2 4.2 0 0 1 0 8.4Zm0-2.2a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z"/>
                                </svg>
                            </span>
                            <span class="toggle-password__icon toggle-password__icon--hide" aria-hidden="true">
                                <svg viewBox="0 0 24 24" focusable="false">
                                    <path d="m3.3 2 18.7 18.7-1.4 1.4-3.4-3.4A13.9 13.9 0 0 1 12 19c-5.5 0-9.9-3.5-11-7A12.7 12.7 0 0 1 6.2 6.1L1.9 3.4 3.3 2Zm5 6.4 1.6 1.6a3 3 0 0 0 4 4l1.6 1.6A5.2 5.2 0 0 1 8.3 8.4Zm3.6-3.4c5.5 0 9.9 3.5 11 7a12.8 12.8 0 0 1-3.8 5.1l-1.4-1.4A10.6 10.6 0 0 0 21 12c-1.1-2.4-4.4-5-9.1-5-.9 0-1.7.1-2.5.3L7.7 5.6c1.3-.4 2.7-.6 4.2-.6Zm-.4 2.2a5.2 5.2 0 0 1 5.3 5.3c0 .8-.2 1.6-.5 2.2l-1.7-1.7c0-.2.1-.4.1-.5a3 3 0 0 0-3-3c-.2 0-.3 0-.5.1L9.5 7.8c.6-.4 1.3-.6 2-.6Z"/>
                                </svg>
                            </span>
                            <span class="sr-only">Tampilkan atau sembunyikan kata sandi</span>
                        </button>
                    </div>
                </div>

                <div class="form-footer">
                    <label class="form-checkbox" for="remember">
                        <input type="checkbox" name="remember" id="remember">
                        <span>Ingat saya</span>
                    </label>
                </div>

                <button type="submit" class="btn-login">Masuk ke Sistem</button>

                <a href="#" class="btn-help">Butuh bantuan?</a>
            </form>
        </div>
    </div>

    <footer class="footer">
        <span>&copy; 2024 MAN 2 Surakarta Administrative System. All rights reserved.</span>
        <a href="#">Privacy Policy</a>
        <a href="#">Terms of Service</a>
        <a href="#">Support</a>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const passwordInput = document.getElementById('password');
            const togglePasswordButton = document.getElementById('togglePassword');

            if (!passwordInput || !togglePasswordButton) {
                return;
            }

            togglePasswordButton.addEventListener('click', function () {
                const isHidden = passwordInput.getAttribute('type') === 'password';

                passwordInput.setAttribute('type', isHidden ? 'text' : 'password');
                togglePasswordButton.setAttribute('aria-pressed', String(isHidden));
                togglePasswordButton.setAttribute(
                    'aria-label',
                    isHidden ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'
                );
            });
        });
    </script>
</body>
</html>
