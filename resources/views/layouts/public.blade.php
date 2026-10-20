<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Portal SIWIL')</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <script>
        const savedTheme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
    <style>
        :root {
            /* Palette: Bright & Clean */
            --bg-body: #f8fafc;
            --bg-glass: rgba(255, 255, 255, 0.9);
            --bg-card: #ffffff;
            --border-glass: rgba(148, 163, 184, 0.22);
            
            --text-primary: #0f172a;
            --text-secondary: #334155;
            --text-muted: #64748b;
            
            --accent-1: #2563eb; /* Modern Blue */
            --accent-2: #3b82f6; 
            
            /* Golden Ratio Sizing (Base: 1rem = 16px) */
            --ratio: 1.618;
            
            --space-xs: calc(1rem / (var(--ratio) * var(--ratio)));
            --space-sm: calc(1rem / var(--ratio));
            --space-md: 1rem;
            --space-lg: calc(1rem * var(--ratio));
            --space-xl: calc(1rem * var(--ratio) * var(--ratio));
            --space-2xl: calc(1rem * var(--ratio) * var(--ratio) * var(--ratio));
            
            --font-xs: calc(1rem / var(--ratio));
            --font-sm: 0.875rem;
            --font-md: 1rem;
            --font-lg: calc(1rem * var(--ratio) * 0.8);
            --font-xl: calc(1rem * var(--ratio));
            --font-2xl: calc(1rem * var(--ratio) * var(--ratio));
            
            --radius-base: var(--space-sm);
            --radius-golden: var(--space-md); 
            
            --shadow-soft: 0 4px 20px -2px rgba(15, 23, 42, 0.06), 0 2px 6px -1px rgba(15, 23, 42, 0.04);
            --shadow-hover: 0 12px 28px -4px rgba(15, 23, 42, 0.12), 0 4px 10px -2px rgba(15, 23, 42, 0.06);
            --animation-timing: cubic-bezier(0.4, 0, 0.2, 1);
        }

        [data-theme="dark"] {
            --bg-body: #0b1120;
            --bg-glass: rgba(21, 30, 50, 0.88);
            --bg-card: #151e32;
            --border-glass: rgba(255, 255, 255, 0.09);
            
            --text-primary: #f8fafc;
            --text-secondary: #cbd5e1;
            --text-muted: #94a3b8;
            
            --accent-1: #3b82f6; 
            --accent-2: #60a5fa; 
            
            --shadow-soft: 0 10px 30px -5px rgba(0, 0, 0, 0.5);
            --shadow-hover: 0 20px 40px -5px rgba(0, 0, 0, 0.7);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: var(--bg-body); color: var(--text-primary); -webkit-font-smoothing: antialiased; }
        h1, h2, h3, h4, h5, h6, .brand-font { font-family: 'Outfit', sans-serif; font-weight: 700; }
        
        /* Navbar */
        nav { 
            background: var(--bg-glass); 
            backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);
            padding: var(--space-md) var(--space-xl); 
            border-bottom: 1px solid var(--border-glass);
            position: sticky; top: 0; z-index: 1000; 
            display: flex; justify-content: space-between; align-items: center; 
        }
        .logo { font-family: 'Outfit', sans-serif; font-size: var(--font-lg); font-weight: 800; color: var(--accent-1); text-decoration: none; }
        .nav-links { display: flex; gap: var(--space-xl); align-items: center; }
        .nav-link { color: var(--text-secondary); text-decoration: none; font-weight: 500; font-size: var(--font-sm); transition: color 0.3s; }
        .nav-link:hover, .nav-link.active { color: var(--accent-1); }
        .btn-login { 
            background: var(--accent-1); color: white; 
            padding: var(--space-sm) var(--space-lg); border-radius: var(--radius-base); 
            text-decoration: none; font-weight: 600; font-size: var(--font-sm);
            transition: all 0.3s var(--animation-timing); 
            box-shadow: 0 4px 10px rgba(37, 99, 235, 0.2);
        }
        .btn-login:hover { background: #1d4ed8; transform: translateY(-2px); box-shadow: 0 6px 15px rgba(37, 99, 235, 0.3); }
        
        /* Main Layout */
        main { max-width: calc(100vw / var(--ratio)); margin: var(--space-2xl) auto; padding: 0 var(--space-lg); min-height: calc(100vh - 160px); width: 100%; }
        
        /* Typography */
        .page-header { margin-bottom: var(--space-xl); text-align: center; }
        .page-title { font-size: var(--font-2xl); font-weight: 700; color: var(--text-primary); margin-bottom: var(--space-sm); }
        .page-subtitle { font-size: var(--font-md); color: var(--text-secondary); }

        /* Cards */
        .card { 
            background: var(--bg-card); border-radius: var(--radius-golden); 
            padding: var(--space-lg); 
            box-shadow: var(--shadow-soft); border: 1px solid var(--border-glass); 
            transition: all 0.4s var(--animation-timing);
        }
        .card:hover { box-shadow: var(--shadow-hover); transform: translateY(calc(var(--space-xs) * -1)); }
        
        /* Footer */
        footer { background: var(--bg-card); text-align: center; padding: var(--space-xl); border-top: 1px solid var(--border-glass); color: var(--text-muted); margin-top: auto; font-size: var(--font-sm); }
        
        @media (max-width: 992px) {
            main { max-width: 90%; }
        }
        @media (max-width: 768px) {
            .nav-links { display: none; } /* Simplified for mobile */
            main { margin: var(--space-lg) auto; max-width: 100%; }
            nav { padding: var(--space-md) var(--space-lg); }
        }
    </style>
    @yield('styles')
</head>
<body>
    <nav>
        <a href="{{ url('/') }}" class="logo">SIWIL PORTAL</a>
        <div class="nav-links">
            <button id="themeToggle" onclick="toggleTheme()" title="Ubah Tema" style="background:transparent; border:none; cursor:pointer; color:var(--text-primary); display:flex; align-items:center; justify-content:center; padding:8px; border-radius:50%; transition:background 0.2s;">
                <svg id="themeIconLight" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:20px;height:20px; display:none;"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
                <svg id="themeIconDark" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:20px;height:20px; display:none;"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
            </button>
            <a href="{{ url('/') }}" class="nav-link {{ request()->is('/') ? 'active' : '' }}">Beranda</a>
            <a href="{{ route('public.apbdes') }}" class="nav-link {{ request()->is('portal/apbdes') ? 'active' : '' }}">APBDes</a>
            <a href="{{ route('public.statistik') }}" class="nav-link {{ request()->is('portal/statistik') ? 'active' : '' }}">Statistik</a>
            <a href="{{ route('public.pembangunan') }}" class="nav-link {{ request()->is('portal/pembangunan') ? 'active' : '' }}">Pembangunan</a>
            @auth
                <a href="{{ url('/dashboard') }}" class="btn-login">Dashboard</a>
            @else
                <a href="{{ url('/login') }}" class="btn-login">Login / Masuk</a>
            @endauth
        </div>
    </nav>

    <main>
        <div class="page-header">
            <h1 class="page-title">@yield('page-title')</h1>
            <p class="page-subtitle">@yield('page-subtitle')</p>
        </div>
        @yield('content')
    </main>

    <footer>
        <p>&copy; {{ date('Y') }} Portal Desa {{ \App\Models\Village::first()->name ?? '' }} — Sistem Informasi Warga Pintar & Layanan Administrasi (SIWIL).</p>
    </footer>

    <script>
        function toggleTheme() {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcon(newTheme);
        }

        function updateThemeIcon(theme) {
            const lightIcon = document.getElementById('themeIconLight');
            const darkIcon = document.getElementById('themeIconDark');
            if(lightIcon && darkIcon) {
                if(theme === 'dark') {
                    lightIcon.style.display = 'block';
                    darkIcon.style.display = 'none';
                } else {
                    lightIcon.style.display = 'none';
                    darkIcon.style.display = 'block';
                }
            }
        }
        
        document.addEventListener('DOMContentLoaded', () => {
            updateThemeIcon(document.documentElement.getAttribute('data-theme'));
        });
    </script>
    @yield('scripts')
</body>
</html>
