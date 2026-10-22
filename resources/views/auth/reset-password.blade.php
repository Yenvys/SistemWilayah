<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Atur Kata Sandi Baru - SIWIL AI Sistem Informasi Wilayah Terpadu">
    <title>Atur Kata Sandi Baru - SIWIL AI</title>
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

        .page-container {
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            background-image:
                linear-gradient(rgba(37, 99, 235, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(37, 99, 235, 0.03) 1px, transparent 1px);
            background-size: 30px 30px;
            padding: 1.5rem 1rem;
            position: relative; overflow: hidden;
        }

        .auth-card {
            width: 100%; max-width: 440px;
            background: var(--bg-glass);
            border-radius: 24px;
            box-shadow: 0 12px 36px rgba(0,0,0,0.08);
            border: 1px solid var(--border-glass);
            position: relative; z-index: 1;
            backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            padding: 2.25rem 2rem;
        }

        .card-header {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .icon-badge {
            width: 58px; height: 58px;
            background: rgba(16, 185, 129, 0.12);
            border-radius: 18px;
            display: inline-flex; align-items: center; justify-content: center;
            margin-bottom: 1rem;
            color: var(--success);
        }

        .icon-badge svg {
            width: 28px; height: 28px;
        }

        .card-header h1 {
            font-size: 1.4rem; font-weight: 800;
            color: var(--text-primary);
            margin-bottom: 0.35rem;
        }

        .card-header p {
            font-size: 0.85rem; color: var(--text-secondary);
            line-height: 1.45;
        }

        .stepper {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .step-dot {
            width: 28px; height: 6px;
            border-radius: 3px;
            background: var(--border-glass);
            transition: all 0.3s;
        }

        .step-dot.active {
            background: var(--accent-1);
            width: 44px;
        }

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

        .form-group { margin-bottom: 1.25rem; }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.825rem;
            font-weight: 500;
            color: var(--text-primary);
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
            padding: 0.75rem 2.75rem 0.75rem 2.75rem;
            background: transparent;
            border: 1px solid var(--border-glass);
            border-radius: 12px;
            font-size: 0.9rem;
            color: var(--text-primary);
            outline: none;
            transition: all 0.2s;
            font-family: inherit;
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
            margin-top: 1rem;
            background: var(--accent-1);
            color: white;
            box-shadow: 0 4px 12px -2px rgba(37,99,235,0.3);
        }

        .submit-btn:hover {
            filter: brightness(110%);
            transform: translateY(-1px);
        }

        .submit-btn svg { width: 18px; height: 18px; }

        .helper-text {
            font-size: 0.785rem;
            color: var(--text-secondary);
            margin-top: 0.4rem;
            line-height: 1.4;
        }
    </style>
</head>
<body>
    <div class="page-container">
        <div class="auth-card">
            <div class="card-header">
                <div class="icon-badge">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        <path d="m9 12 2 2 4-4"/>
                    </svg>
                </div>
                <h1>Kata Sandi Baru</h1>
                <p>Silakan buat kata sandi baru untuk akun Anda (<strong>{{ $user->resident?->full_name ?? $user->name ?? $user->username }}</strong>).</p>
            </div>

            <div class="stepper">
                <div class="step-dot" title="Langkah 1: Identifikasi"></div>
                <div class="step-dot" title="Langkah 2: Verifikasi OTP"></div>
                <div class="step-dot active" title="Langkah 3: Kata Sandi Baru"></div>
            </div>

            @if($errors->any())
                <div class="alert alert-error">{{ $errors->first() }}</div>
            @endif

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label" for="new-password">Kata Sandi Baru</label>
                    <div class="input-wrapper">
                        <span class="input-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                        </span>
                        <input
                            type="password"
                            id="new-password"
                            name="password"
                            class="form-input"
                            placeholder="Minimal 6 karakter"
                            required
                            autofocus
                            autocomplete="new-password"
                        >
                        <button
                            type="button"
                            class="toggle-password-btn"
                            onclick="togglePasswordVisibility('new-password', this)"
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
                    <div class="helper-text">Gunakan minimal 6 karakter dengan kombinasi huruf dan angka untuk keamanan optimal.</div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="confirm-password">Konfirmasi Kata Sandi Baru</label>
                    <div class="input-wrapper">
                        <span class="input-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                        </span>
                        <input
                            type="password"
                            id="confirm-password"
                            name="password_confirmation"
                            class="form-input"
                            placeholder="Ulangi kata sandi baru"
                            required
                            autocomplete="new-password"
                        >
                        <button
                            type="button"
                            class="toggle-password-btn"
                            onclick="togglePasswordVisibility('confirm-password', this)"
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

                <button type="submit" class="submit-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    Simpan Kata Sandi Baru
                </button>
            </form>
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
    </script>
</body>
</html>
