<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Verifikasi OTP - SIWIL AI Sistem Informasi Wilayah Terpadu">
    <title>Verifikasi OTP - SIWIL AI</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
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
            margin-bottom: 1.5rem;
        }

        .icon-badge {
            width: 58px; height: 58px;
            background: rgba(37, 211, 102, 0.12);
            border-radius: 18px;
            display: inline-flex; align-items: center; justify-content: center;
            margin-bottom: 1rem;
            color: var(--wa-color);
        }

        .icon-badge svg {
            width: 30px; height: 30px;
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

        .phone-highlight {
            font-weight: 600;
            color: var(--text-primary);
            background: rgba(37, 99, 235, 0.08);
            padding: 0.15rem 0.5rem;
            border-radius: 6px;
            display: inline-block;
            margin-top: 0.25rem;
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
        .alert-warning { background: #fffbeb; border-color: #fde68a; color: #b45309; }
        .alert-info { background: #eff6ff; border-color: #bfdbfe; color: #1e40af; }

        .otp-input-wrapper {
            margin: 1.5rem 0;
            text-align: center;
        }

        .otp-input {
            width: 100%;
            max-width: 280px;
            padding: 0.85rem;
            background: transparent;
            border: 2px solid var(--border-glass);
            border-radius: 14px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 1.85rem;
            font-weight: 700;
            letter-spacing: 0.65rem;
            text-align: center;
            color: var(--text-primary);
            outline: none;
            transition: all 0.2s;
        }

        .otp-input:focus {
            border-color: var(--accent-1);
            box-shadow: 0 0 0 4px rgba(37,99,235,0.12);
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
            background: var(--accent-1);
            color: white;
            box-shadow: 0 4px 12px -2px rgba(37,99,235,0.3);
        }

        .submit-btn:hover {
            filter: brightness(110%);
            transform: translateY(-1px);
        }

        .resend-section {
            margin-top: 1.5rem;
            text-align: center;
            padding-top: 1.25rem;
            border-top: 1px solid var(--border-glass);
            font-size: 0.85rem;
            color: var(--text-secondary);
        }

        .resend-btn {
            background: none;
            border: none;
            color: var(--wa-color);
            font-weight: 600;
            cursor: pointer;
            padding: 0;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            transition: all 0.15s;
            font-family: inherit;
        }

        .resend-btn:hover:not(:disabled) {
            text-decoration: underline;
            color: var(--wa-hover);
        }

        .resend-btn:disabled {
            color: var(--text-muted);
            cursor: not-allowed;
            text-decoration: none;
        }

        .resend-btn svg { width: 15px; height: 15px; }

        .back-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            width: 100%;
            margin-top: 1rem;
            font-size: 0.825rem;
            color: var(--text-muted);
            text-decoration: none;
            transition: color 0.15s;
        }

        .back-link:hover { color: var(--accent-1); }
        .back-link svg { width: 14px; height: 14px; }
    </style>
</head>
<body>
    <div class="page-container">
        <div class="auth-card">
            <div class="card-header">
                <div class="icon-badge">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/>
                    </svg>
                </div>
                <h1>Verifikasi Kode OTP</h1>
                <p>
                    Masukkan 6 digit kode yang dikirim ke nomor WhatsApp:<br>
                    <span class="phone-highlight">{{ $maskedWa }}</span>
                </p>
            </div>

            <div class="stepper">
                <div class="step-dot" title="Langkah 1: Identifikasi"></div>
                <div class="step-dot active" title="Langkah 2: Verifikasi OTP"></div>
                <div class="step-dot" title="Langkah 3: Kata Sandi Baru"></div>
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

            @if(session('info'))
                <div class="alert alert-info">{{ session('info') }}</div>
            @endif

            <form method="POST" action="{{ route('password.otp.verify') }}">
                @csrf
                <div class="otp-input-wrapper">
                    <input
                        type="text"
                        name="otp"
                        id="otp-input"
                        class="otp-input"
                        placeholder="••••••"
                        maxlength="6"
                        inputmode="numeric"
                        pattern="[0-9]{6}"
                        autocomplete="one-time-code"
                        required
                        autofocus
                    >
                </div>

                <button type="submit" class="submit-btn">
                    Verifikasi & Lanjutkan
                </button>
            </form>

            <div class="resend-section">
                <div>Tidak menerima kode OTP?</div>
                <form method="POST" action="{{ route('password.otp.resend') }}" style="margin-top: 0.5rem;">
                    @csrf
                    <button type="submit" id="resend-btn" class="resend-btn" @if($cooldownSeconds > 0) disabled @endif>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="23 4 23 10 17 10"></polyline>
                            <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
                        </svg>
                        <span id="resend-text">
                            @if($cooldownSeconds > 0)
                                Kirim ulang dalam {{ $cooldownSeconds }} detik
                            @else
                                Kirim Ulang OTP via WhatsApp
                            @endif
                        </span>
                    </button>
                </form>
            </div>

            <a href="{{ route('password.request') }}" class="back-link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="19" y1="12" x2="5" y2="12"/>
                    <polyline points="12 19 5 12 12 5"/>
                </svg>
                Ganti NIK / Nomor Lain
            </a>
        </div>
    </div>

    <script>
        // Auto-format only digits
        const otpInput = document.getElementById('otp-input');
        if (otpInput) {
            otpInput.addEventListener('input', function(e) {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
        }

        // Resend countdown timer
        let cooldown = {{ $cooldownSeconds }};
        const resendBtn = document.getElementById('resend-btn');
        const resendText = document.getElementById('resend-text');

        if (cooldown > 0 && resendBtn && resendText) {
            const timer = setInterval(() => {
                cooldown--;
                if (cooldown <= 0) {
                    clearInterval(timer);
                    resendBtn.disabled = false;
                    resendText.textContent = 'Kirim Ulang OTP via WhatsApp';
                } else {
                    resendText.textContent = `Kirim ulang dalam ${cooldown} detik`;
                }
            }, 1000);
        }
    </script>
</body>
</html>
