<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Lupa Password - SIWIL AI Sistem Informasi Wilayah Terpadu">
    <title>Lupa Password - SIWIL AI</title>
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
            --wa-color: #25D366;
            --wa-hover: #1eb857;
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
            --wa-color: #25D366;
            --wa-hover: #1eb857;
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
            margin-bottom: 1.75rem;
        }

        .icon-badge {
            width: 58px; height: 58px;
            background: rgba(37, 99, 235, 0.1);
            border-radius: 18px;
            display: inline-flex; align-items: center; justify-content: center;
            margin-bottom: 1rem;
            color: var(--accent-1);
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

        .info-box {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            background: rgba(37, 211, 102, 0.08);
            border: 1px solid rgba(37, 211, 102, 0.25);
            padding: 0.875rem 1rem;
            border-radius: 14px;
            margin-bottom: 1.5rem;
            font-size: 0.825rem;
            color: var(--text-secondary);
            line-height: 1.45;
        }

        .info-box svg {
            width: 20px; height: 20px;
            flex-shrink: 0;
            color: var(--wa-color);
            margin-top: 1px;
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
        .alert-warning { background: #fffbeb; border-color: #fde68a; color: #b45309; }

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

        .form-input::placeholder { color: var(--text-muted); }
        .form-input:focus {
            border-color: var(--accent-1);
            box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
            background: rgba(255,255,255,0.02);
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
            background: var(--wa-color);
            color: white;
            box-shadow: 0 4px 14px -2px rgba(37, 211, 102, 0.35);
        }

        .submit-btn:hover {
            background: var(--wa-hover);
            filter: brightness(105%);
            transform: translateY(-1px);
        }

        .submit-btn svg { width: 18px; height: 18px; }

        .back-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            width: 100%;
            margin-top: 1.25rem;
            font-size: 0.85rem;
            color: var(--text-secondary);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.15s;
        }

        .back-link:hover {
            color: var(--accent-1);
        }

        .back-link svg { width: 14px; height: 14px; }
    </style>
</head>
<body>
    <div class="page-container">
        <div class="auth-card">
            <div class="card-header">
                <div class="icon-badge">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                </div>
                <h1>Lupa Kata Sandi</h1>
                <p>Masukkan NIK, Email, atau No. WhatsApp Anda untuk menerima kode verifikasi OTP.</p>
            </div>

            <div class="stepper">
                <div class="step-dot active" title="Langkah 1: Identifikasi"></div>
                <div class="step-dot" title="Langkah 2: Verifikasi OTP"></div>
                <div class="step-dot" title="Langkah 3: Kata Sandi Baru"></div>
            </div>

            <div class="info-box">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0 0 12.04 2zm.01 1.67c4.55 0 8.24 3.7 8.24 8.24 0 2.2-.86 4.27-2.42 5.82a8.17 8.17 0 0 1-5.82 2.41c-1.46 0-2.9-.38-4.17-1.11l-.3-.18-3.12.82.83-3.04-.2-.31a8.18 8.18 0 0 1-1.26-4.41c0-4.54 3.7-8.24 8.24-8.24z"/>
                </svg>
                <div>
                    Kode OTP 6 digit akan dikirimkan langsung secara otomatis via <strong>Bot WhatsApp SIWIL</strong> ke nomor WhatsApp yang terdaftar pada akun Anda.
                </div>
            </div>

            @if($errors->any())
                <div class="alert alert-error">{{ $errors->first() }}</div>
            @endif

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning">{{ session('warning') }}</div>
            @endif

            <form method="POST" action="{{ route('password.otp.send') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label" for="identifier">NIK / Email / No. WhatsApp</label>
                    <div class="input-wrapper">
                        <span class="input-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                        </span>
                        <input
                            type="text"
                            id="identifier"
                            name="identifier"
                            class="form-input"
                            placeholder="Contoh: 3201... / 0812... / email"
                            value="{{ old('identifier') }}"
                            required
                            autofocus
                        >
                    </div>
                </div>

                <button type="submit" class="submit-btn">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/>
                    </svg>
                    Kirim Kode OTP via WhatsApp
                </button>
            </form>

            <a href="{{ route('login') }}" class="back-link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="19" y1="12" x2="5" y2="12"/>
                    <polyline points="12 19 5 12 12 5"/>
                </svg>
                Kembali ke Halaman Login
            </a>
        </div>
    </div>
</body>
</html>
