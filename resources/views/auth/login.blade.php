<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Login - SIWIL AI Sistem Informasi Wilayah Terpadu">
    <title>Login - SIWIL AI</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        const savedTheme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg-body: #fafafa;
            --bg-glass: rgba(255, 255, 255, 0.88);
            --border-glass: rgba(0, 0, 0, 0.08);
            --text-primary: #18181b;
            --text-secondary: #52525b;
            --text-muted: #a1a1aa;
            --accent-1: #2563eb; 
            --accent-2: #3b82f6; 
            --accent-gradient: linear-gradient(135deg, var(--accent-1), var(--accent-2));
            --success: #10b981;
        }

        [data-theme="dark"] {
            --bg-body: #0f172a;
            --bg-glass: rgba(15, 23, 42, 0.88);
            --border-glass: rgba(255, 255, 255, 0.1);
            --text-primary: #f8fafc;
            --text-secondary: #cbd5e1;
            --text-muted: #94a3b8;
            --accent-1: #3b82f6; 
            --accent-2: #60a5fa; 
            --success: #10b981;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            -webkit-font-smoothing: antialiased;
            background-color: var(--bg-body);
            color: var(--text-primary);
        }

        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .login-page {
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            background-image: 
                linear-gradient(rgba(37, 99, 235, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(37, 99, 235, 0.03) 1px, transparent 1px);
            background-size: 30px 30px;
            padding: 1.5rem 1rem;
            position: relative; overflow: hidden;
        }

        .login-page::before {
            content: '';
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(135deg, #38bdf8, #818cf8, #a78bfa);
            background-size: 200% 200%;
            animation: gradientShift 15s ease infinite;
            opacity: 0.15;
            z-index: 0;
        }

        [data-theme="dark"] .login-page::before {
            background: linear-gradient(135deg, var(--accent-1), #4338ca, var(--accent-2));
            opacity: 0.05;
        }

        .login-card {
            width: 100%; max-width: 440px;
            background: var(--bg-glass);
            border-radius: 24px;
            box-shadow: 0 12px 36px rgba(0,0,0,0.08);
            border: 1px solid var(--border-glass);
            position: relative; z-index: 1;
            backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
        }

        .login-header {
            text-align: center;
            padding: 2.25rem 2rem 1.25rem;
        }

        .logo-icon {
            width: 56px; height: 56px;
            background: var(--accent-gradient);
            border-radius: 16px;
            display: inline-flex; align-items: center; justify-content: center;
            margin-bottom: 0.875rem;
            box-shadow: 0 8px 20px rgba(37,99,235,0.25);
        }

        .logo-icon svg {
            width: 28px; height: 28px;
            fill: none; stroke: white; stroke-width: 2;
        }

        .login-header h1 {
            font-size: 1.5rem; font-weight: 800;
            color: var(--text-primary); letter-spacing: 0.5px;
        }

        .login-header p {
            font-size: 0.85rem; color: var(--text-muted);
            margin-top: 0.25rem;
        }

        .tab-bar {
            display: flex;
            border-bottom: 1px solid var(--border-glass);
        }

        .tab-btn {
            flex: 1;
            padding: 0.875rem 0.5rem;
            background: none; border: none;
            border-bottom: 2px solid transparent;
            color: var(--text-muted);
            font-size: 0.875rem; font-weight: 500;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 0.5rem;
            transition: all 0.2s;
            font-family: inherit;
        }

        .tab-btn:hover { color: var(--text-primary); background: rgba(37, 99, 235, 0.05); }

        .tab-btn.active {
            color: var(--accent-1);
            border-bottom-color: var(--accent-1);
            font-weight: 600;
            background: transparent;
        }

        .tab-btn svg { width: 16px; height: 16px; }

        .form-area { padding: 1.75rem 2rem 2rem; }

        .alert {
            padding: 0.75rem 1rem;
            border-radius: 12px;
            font-size: 0.825rem;
            margin-bottom: 1.25rem;
            border: 1px solid;
            line-height: 1.5;
        }

        .alert-error { background: #fef2f2; border-color: #fecaca; color: #dc2626; }
        .alert-success { background: #f0fdf4; border-color: #bbf7d0; color: #166534; }

        .form-group { margin-bottom: 1.15rem; }

        .form-label-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.45rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.45rem;
            font-size: 0.825rem;
            font-weight: 500;
            color: var(--text-primary);
        }

        .form-label-row .form-label { margin-bottom: 0; }

        .forgot-link {
            font-size: 0.8rem;
            color: var(--accent-1);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.15s;
        }

        .forgot-link:hover {
            text-decoration: underline;
            filter: brightness(115%);
        }

        .input-wrapper { position: relative; }

        .input-icon {
            position: absolute; left: 0.875rem; top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted); pointer-events: none;
        }

        .input-icon svg { width: 16px; height: 16px; }

        .form-input {
            width: 100%;
            padding: 0.75rem 0.875rem 0.75rem 2.75rem;
            background: transparent;
            border: 1px solid var(--border-glass);
            border-radius: 12px;
            font-size: 0.9rem;
            color: var(--text-primary);
            outline: none;
            transition: all 0.2s;
            font-family: inherit;
        }

        .form-input.password-input {
            padding-right: 2.75rem;
        }

        .form-input::placeholder { color: var(--text-muted); }
        .form-input:focus {
            border-color: var(--accent-1);
            box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
            background: rgba(255,255,255,0.02);
        }

        .toggle-password-btn {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            padding: 6px;
            cursor: pointer;
            color: var(--text-muted);
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: color 0.15s, background-color 0.15s;
            line-height: 1;
        }

        .toggle-password-btn:hover {
            color: var(--text-primary);
            background-color: rgba(37, 99, 235, 0.08);
        }

        [data-theme="dark"] .toggle-password-btn:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .toggle-password-btn:focus-visible {
            outline: 2px solid var(--accent-1);
        }

        .toggle-password-btn svg {
            width: 18px;
            height: 18px;
            pointer-events: none;
        }

        .submit-btn {
            width: 100%;
            padding: 0.875rem;
            border: none; border-radius: 12px;
            font-size: 0.925rem; font-weight: 600;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 0.5rem;
            transition: all 0.2s;
            font-family: inherit;
            margin-top: 1.25rem;
        }

        .submit-btn.login-btn {
            background: var(--accent-1); color: white;
            box-shadow: 0 4px 12px -2px rgba(37,99,235,0.25);
        }
        .submit-btn.login-btn:hover { filter: brightness(110%); transform: translateY(-1px); }

        .submit-btn.register-btn {
            background: var(--success); color: white;
            box-shadow: 0 4px 12px -2px rgba(16,185,129,0.25);
        }
        .submit-btn.register-btn:hover { filter: brightness(110%); transform: translateY(-1px); }

        .submit-btn svg { width: 16px; height: 16px; }

        .helper-text {
            font-size: 0.8rem; color: var(--text-secondary);
            padding: 0.75rem 0.875rem;
            background: rgba(37,99,235,0.05);
            border-radius: 12px;
            margin-bottom: 1.15rem;
            line-height: 1.45;
            border: 1px solid var(--border-glass);
        }
        .helper-text strong { color: var(--text-primary); font-weight: 600; }

        .footer-text {
            text-align: center;
            font-size: 0.75rem; color: var(--text-muted);
            margin-top: 1.5rem; padding-top: 1.25rem;
            border-top: 1px solid var(--border-glass);
        }

        #login-form { display: block; }
        #register-form { display: none; }
    </style>
</head>
<body>
    <div class="login-page">
        <div class="login-card">
            {{-- Header --}}
            <div class="login-header">
                <div class="logo-icon">
                    <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>
                </div>
                <h1>SIWIL AI</h1>
                <p>Sistem Informasi Wilayah Terpadu</p>
            </div>

            {{-- Tabs --}}
            <div class="tab-bar">
                <button type="button" class="tab-btn active" id="tab-login" onclick="switchTab('login')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                    Login
                </button>
                <button type="button" class="tab-btn" id="tab-register" onclick="switchTab('register')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
                    Sign Up
                </button>
            </div>

            {{-- Form Area --}}
            <div class="form-area">
                @if($errors->any())
                    <div class="alert alert-error">{{ $errors->first() }}</div>
                @endif

                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                {{-- LOGIN FORM --}}
                <form id="login-form" method="POST" action="{{ url('/login') }}">
                    @csrf
                    <div class="form-group">
                        <label class="form-label" for="login-username">NIK / Email / No. HP</label>
                        <div class="input-wrapper">
                            <span class="input-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            </span>
                            <input
                                type="text"
                                id="login-username"
                                name="username"
                                class="form-input"
                                placeholder="Masukkan NIK, Email, atau No. HP"
                                value="{{ old('username') }}"
                                required
                                autofocus
                            >
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="form-label-row">
                            <label class="form-label" for="login-password">Password</label>
                            <a href="{{ route('password.request') }}" class="forgot-link">Lupa Password?</a>
                        </div>
                        <div class="input-wrapper">
                            <span class="input-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            </span>
                            <input
                                type="password"
                                id="login-password"
                                name="password"
                                class="form-input password-input"
                                placeholder="Masukkan password"
                                required
                                autocomplete="current-password"
                            >
                            <button
                                type="button"
                                class="toggle-password-btn"
                                onclick="togglePasswordVisibility('login-password', this)"
                                aria-label="Lihat password"
                                title="Lihat password"
                            >
                                <svg class="eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                                <svg class="eye-closed" style="display: none;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/>
                                    <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/>
                                    <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/>
                                    <line x1="2" y1="2" x2="22" y2="22"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="submit-btn login-btn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                        Masuk
                    </button>
                </form>

                {{-- REGISTER FORM --}}
                <form id="register-form" method="POST" action="{{ url('/register') }}">
                    @csrf
                    <div class="helper-text">
                        Masukkan <strong>NIK</strong> dan <strong>No. Kartu Keluarga (KK)</strong> Anda yang terdaftar pada data kependudukan RT/RW setempat.
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="reg-nik">NIK (Nomor Induk Kependudukan)</label>
                        <div class="input-wrapper">
                            <span class="input-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                            </span>
                            <input
                                type="text"
                                id="reg-nik"
                                name="nik"
                                class="form-input"
                                placeholder="Masukkan 16 digit NIK"
                                maxlength="16"
                                value="{{ old('nik') }}"
                                required
                                inputmode="numeric"
                            >
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="reg-kk">Nomor Kartu Keluarga (KK)</label>
                        <div class="input-wrapper">
                            <span class="input-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                            </span>
                            <input
                                type="text"
                                id="reg-kk"
                                name="kk_number"
                                class="form-input"
                                placeholder="Masukkan 16 digit No. KK"
                                maxlength="16"
                                value="{{ old('kk_number') }}"
                                required
                                inputmode="numeric"
                            >
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="reg-phone">No. WhatsApp / HP <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: normal;">(Opsional, untuk OTP)</span></label>
                        <div class="input-wrapper">
                            <span class="input-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                            </span>
                            <input
                                type="tel"
                                id="reg-phone"
                                name="phone_number"
                                class="form-input"
                                placeholder="Contoh: 081234567890"
                                value="{{ old('phone_number') }}"
                            >
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="reg-password">Buat Password Baru</label>
                        <div class="input-wrapper">
                            <span class="input-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            </span>
                            <input
                                type="password"
                                id="reg-password"
                                name="password"
                                class="form-input password-input"
                                placeholder="Minimal 6 karakter"
                                required
                                autocomplete="new-password"
                            >
                            <button
                                type="button"
                                class="toggle-password-btn"
                                onclick="togglePasswordVisibility('reg-password', this)"
                                aria-label="Lihat password"
                                title="Lihat password"
                            >
                                <svg class="eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                                <svg class="eye-closed" style="display: none;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/>
                                    <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/>
                                    <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/>
                                    <line x1="2" y1="2" x2="22" y2="22"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="reg-password-confirm">Konfirmasi Password</label>
                        <div class="input-wrapper">
                            <span class="input-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            </span>
                            <input
                                type="password"
                                id="reg-password-confirm"
                                name="password_confirmation"
                                class="form-input password-input"
                                placeholder="Ulangi password"
                                required
                                autocomplete="new-password"
                            >
                            <button
                                type="button"
                                class="toggle-password-btn"
                                onclick="togglePasswordVisibility('reg-password-confirm', this)"
                                aria-label="Lihat password"
                                title="Lihat password"
                            >
                                <svg class="eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                                <svg class="eye-closed" style="display: none;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/>
                                    <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/>
                                    <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/>
                                    <line x1="2" y1="2" x2="22" y2="22"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="submit-btn register-btn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
                        Daftar Akun Warga
                    </button>
                </form>

                <div class="footer-text">
                    &copy; {{ date('Y') }} SIWIL AI &mdash; Sistem Informasi Wilayah Terpadu
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePasswordVisibility(inputId, btn) {
            const input = document.getElementById(inputId);
            if (!input) return;

            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';

            const eyeOpen = btn.querySelector('.eye-open');
            const eyeClosed = btn.querySelector('.eye-closed');

            if (eyeOpen && eyeClosed) {
                if (isPassword) {
                    eyeOpen.style.display = 'none';
                    eyeClosed.style.display = 'inline-block';
                    btn.setAttribute('aria-label', 'Sembunyikan password');
                    btn.setAttribute('title', 'Sembunyikan password');
                } else {
                    eyeOpen.style.display = 'inline-block';
                    eyeClosed.style.display = 'none';
                    btn.setAttribute('aria-label', 'Lihat password');
                    btn.setAttribute('title', 'Lihat password');
                }
            }
        }

        function switchTab(tab) {
            const loginForm = document.getElementById('login-form');
            const registerForm = document.getElementById('register-form');
            const tabLogin = document.getElementById('tab-login');
            const tabRegister = document.getElementById('tab-register');

            if (tab === 'login') {
                loginForm.style.display = 'block';
                registerForm.style.display = 'none';
                tabLogin.classList.add('active');
                tabRegister.classList.remove('active');
            } else {
                loginForm.style.display = 'none';
                registerForm.style.display = 'block';
                tabLogin.classList.remove('active');
                tabRegister.classList.add('active');
            }
        }

        // Auto-switch ke tab register jika diarahkan ke register atau ada error validasi register
        @if(
            ($errors->any() && (request()->is('register') || old('nik') || old('kk_number') || $errors->has('nik') || $errors->has('kk_number') || $errors->has('password_confirmation'))) ||
            (isset($initialTab) && $initialTab === 'register') ||
            request()->query('tab') === 'register'
        )
            document.addEventListener('DOMContentLoaded', function() { switchTab('register'); });
        @endif
    </script>
</body>
</html>
