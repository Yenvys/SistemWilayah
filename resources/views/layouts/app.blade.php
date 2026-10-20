<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SIWIL AI - Sistem Informasi Wilayah Terpadu">
    <title>@yield('title', 'SIWIL AI')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script>
        const savedTheme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            /* Palette: Bright & Crisp Clean */
            --bg-body: #f8fafc;
            --bg-glass: rgba(255, 255, 255, 0.9);
            --bg-glass-hover: rgba(241, 245, 249, 0.9);
            --bg-card: #ffffff;
            --border-glass: rgba(148, 163, 184, 0.22);
            --border-subtle: #e2e8f0;
            
            --text-primary: #0f172a;
            --text-secondary: #334155;
            --text-muted: #64748b;
            
            --accent-1: #2563eb; /* Modern Blue */
            --accent-2: #3b82f6; 
            --accent-gradient: linear-gradient(135deg, var(--accent-1), var(--accent-2));
            
            --header-grad-1: #38bdf8; /* Vibrant Sky */
            --header-grad-2: #818cf8; /* Vibrant Indigo */
            --header-grad-3: #a78bfa; /* Vibrant Purple */
            
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            
            /* Golden Ratio Sizing (Base: 1rem = 16px) */
            --ratio: 1.618;
            
            --space-xs: calc(1rem / (var(--ratio) * var(--ratio))); /* ~0.38rem */
            --space-sm: calc(1rem / var(--ratio)); /* ~0.618rem */
            --space-md: 1rem;
            --space-lg: calc(1rem * var(--ratio)); /* ~1.618rem */
            --space-xl: calc(1rem * var(--ratio) * var(--ratio)); /* ~2.618rem */
            --space-2xl: calc(1rem * var(--ratio) * var(--ratio) * var(--ratio)); /* ~4.236rem */
            
            --font-xs: calc(1rem / var(--ratio));
            --font-sm: 0.875rem;
            --font-md: 1rem;
            --font-lg: calc(1rem * var(--ratio) * 0.8);
            --font-xl: calc(1rem * var(--ratio));
            --font-2xl: calc(1rem * var(--ratio) * var(--ratio));
            
            --radius-base: var(--space-sm);
            --radius-golden: var(--space-md); 
            
            --sidebar-width: 16.18rem;
            --topnav-height: calc(3rem * var(--ratio)); /* ~4.85rem = ~77px */
            
            --shadow-soft: 0 4px 20px -2px rgba(15, 23, 42, 0.06), 0 2px 6px -1px rgba(15, 23, 42, 0.04);
            --shadow-hover: 0 12px 28px -4px rgba(15, 23, 42, 0.12), 0 4px 10px -2px rgba(15, 23, 42, 0.06);
            
            --animation-timing: cubic-bezier(0.4, 0, 0.2, 1);
        }

        [data-theme="dark"] {
            --bg-body: #0b1120;
            --bg-glass: rgba(21, 30, 50, 0.88);
            --bg-glass-hover: rgba(30, 41, 69, 0.95);
            --bg-card: #151e32;
            --border-glass: rgba(255, 255, 255, 0.09);
            --border-subtle: rgba(255, 255, 255, 0.12);
            
            --text-primary: #f8fafc;
            --text-secondary: #cbd5e1;
            --text-muted: #94a3b8;
            
            --accent-1: #3b82f6; 
            --accent-2: #60a5fa; 
            
            --header-grad-1: #1e3a8a;
            --header-grad-2: #312e81;
            --header-grad-3: #1e1b4b;
            
            --shadow-soft: 0 10px 30px -5px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.05);
            --shadow-hover: 0 20px 40px -5px rgba(0, 0, 0, 0.7), 0 0 0 1px rgba(255, 255, 255, 0.1);
        }

        /* Dark Mode Component Overrides */
        [data-theme="dark"] th { background-color: #0f172a; color: #94a3b8; border-color: rgba(255, 255, 255, 0.08); }
        [data-theme="dark"] td { border-color: rgba(255, 255, 255, 0.06); color: #f8fafc; }
        [data-theme="dark"] tr:hover td { background: rgba(255, 255, 255, 0.025); }
        [data-theme="dark"] .data-card { background: var(--bg-card); border-color: var(--border-glass); }
        [data-theme="dark"] .data-card-header { background-color: rgba(255, 255, 255, 0.02); border-color: rgba(255, 255, 255, 0.08); }
        [data-theme="dark"] .stat-card { background: var(--bg-card); border-color: var(--border-glass); }
        [data-theme="dark"] .form-input, [data-theme="dark"] .form-select, [data-theme="dark"] .form-textarea, [data-theme="dark"] .custom-dropdown-trigger { 
            background-color: #1e293b; border-color: #334155; color: #f8fafc; 
        }
        [data-theme="dark"] .form-input:focus, [data-theme="dark"] .form-select:focus, [data-theme="dark"] .form-textarea:focus, [data-theme="dark"] .custom-dropdown-container.open .custom-dropdown-trigger { 
            background-color: #1e293b; border-color: #60a5fa; box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.25);
        }
        [data-theme="dark"] .form-input::placeholder, [data-theme="dark"] .form-textarea::placeholder { color: #64748b; }
        [data-theme="dark"] .form-select { background-image: none; }
        [data-theme="dark"] .btn-outline { color: #f8fafc; background: rgba(255, 255, 255, 0.04); border-color: rgba(255, 255, 255, 0.15); }
        [data-theme="dark"] .btn-outline:hover { background: rgba(255, 255, 255, 0.1); border-color: rgba(255, 255, 255, 0.3); color: #ffffff; }
        [data-theme="dark"] .empty-state { background: rgba(255,255,255,0.02); border: 1px dashed rgba(255, 255, 255, 0.1); }
        
        /* Badges in Dark Mode */
        [data-theme="dark"] .badge-blue { background: rgba(59, 130, 246, 0.18); border: 1px solid rgba(59, 130, 246, 0.35); color: #93c5fd; }
        [data-theme="dark"] .badge-green { background: rgba(16, 185, 129, 0.18); border: 1px solid rgba(16, 185, 129, 0.35); color: #6ee7b7; }
        [data-theme="dark"] .badge-red { background: rgba(239, 68, 68, 0.18); border: 1px solid rgba(239, 68, 68, 0.35); color: #fca5a5; }
        [data-theme="dark"] .badge-yellow { background: rgba(245, 158, 11, 0.18); border: 1px solid rgba(245, 158, 11, 0.35); color: #fcd34d; }
        [data-theme="dark"] .badge-gray { background: rgba(148, 163, 184, 0.15); border: 1px solid rgba(148, 163, 184, 0.25); color: #cbd5e1; }

        /* Modals in Dark Mode */
        [data-theme="dark"] .siwil-modal-overlay { background: rgba(0, 0, 0, 0.75); }
        [data-theme="dark"] .siwil-modal-dialog { background: #151e32; border-color: rgba(255, 255, 255, 0.12); box-shadow: 0 25px 60px rgba(0, 0, 0, 0.7); }
        [data-theme="dark"] .siwil-modal-header { border-bottom-color: rgba(255, 255, 255, 0.08); }
        [data-theme="dark"] .siwil-modal-footer { border-top-color: rgba(255, 255, 255, 0.08); }
        [data-theme="dark"] .siwil-modal-close { background: rgba(255, 255, 255, 0.05); border-color: rgba(255, 255, 255, 0.1); color: #94a3b8; }
        [data-theme="dark"] .siwil-modal-close:hover { background: rgba(239, 68, 68, 0.2); border-color: rgba(239, 68, 68, 0.4); color: #fca5a5; }

        /* Global Custom Dropdown */
        .form-select {
            display: none !important; /* Hide original select */
        }
        .custom-dropdown-container { position: relative; width: 100%; min-width: 140px; }
        .filter-bar .custom-dropdown-container,
        .filter-bar form .custom-dropdown-container,
        form.filter-form .custom-dropdown-container {
            width: auto;
            flex: 0 0 auto;
        }
        .custom-dropdown-container.open { z-index: 9999; }
        .custom-dropdown-trigger {
            width: 100%; 
            padding: var(--space-md) var(--space-lg); padding-right: 2.5rem;
            background: #ffffff; 
            border: 1px solid #d4d4d8;
            border-radius: var(--radius-golden); 
            color: var(--text-primary); 
            font-size: var(--font-sm);
            font-weight: 500;
            font-family: inherit;
            transition: all 0.25s var(--animation-timing); 
            cursor: pointer; display: flex; justify-content: space-between; align-items: center;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .custom-dropdown-trigger:hover {
            border-color: #a1a1aa;
        }
        .custom-dropdown-trigger svg {
            flex-shrink: 0; margin-left: 8px; opacity: 0.7;
        }
        .custom-dropdown-container.open .custom-dropdown-trigger {
            border-color: var(--accent-1); 
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
            background: #ffffff;
        }
        .custom-dropdown-menu {
            position: absolute; top: calc(100% + 8px); left: 0; right: 0; bottom: auto;
            background: var(--bg-card); border: 1px solid var(--border-glass);
            border-radius: 12px;
            box-shadow: 0 15px 35px -5px rgba(0, 0, 0, 0.15), 0 0 0 1px rgba(255,255,255,0.1);
            z-index: 9999; opacity: 0; visibility: hidden; transform: translateY(-10px);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); padding: 4px;
            max-height: 250px; overflow-y: auto;
        }
        .custom-dropdown-container.open .custom-dropdown-menu {
            opacity: 1; visibility: visible; transform: translateY(0);
        }
        .custom-dropdown-item {
            padding: 0.8rem 1rem; color: var(--text-primary); cursor: pointer;
            transition: all 0.2s ease; display: flex; align-items: center; gap: 8px;
            border-radius: 8px; margin-bottom: 2px;
        }
        .custom-dropdown-item:last-child { margin-bottom: 0; }
        .custom-dropdown-item:hover { background: rgba(99, 102, 241, 0.1); }
        .custom-dropdown-item.selected { background: rgba(99, 102, 241, 0.15); font-weight: 600; color: var(--accent-1); }
        .custom-dropdown-item.selected::before { content: '✓'; font-weight: bold; margin-right: 4px; }
        [data-theme="dark"] .custom-dropdown-menu {
            background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255, 255, 255, 0.08); box-shadow: 0 15px 35px -5px rgba(0, 0, 0, 0.4);
        }
        /* Custom scrollbar for menu */
        .custom-dropdown-menu::-webkit-scrollbar { width: 6px; }
        .custom-dropdown-menu::-webkit-scrollbar-track { background: transparent; }
        .custom-dropdown-menu::-webkit-scrollbar-thumb { background: rgba(148, 163, 184, 0.4); border-radius: 3px; }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-primary);
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
            overflow-x: hidden;
            background-image: 
                linear-gradient(rgba(37, 99, 235, 0.02) 1px, transparent 1px),
                linear-gradient(90deg, rgba(37, 99, 235, 0.02) 1px, transparent 1px);
            background-size: 30px 30px;
        }

        h1, h2, h3, h4, h5, h6, .brand-font {
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
        }

        /* ===== LAYOUT & MOTION TOKENS ===== */
        :root {
            --ease-spring: cubic-bezier(0.16, 1, 0.3, 1);
            --ease-bounce: cubic-bezier(0.34, 1.56, 0.64, 1);
            --ease-smooth: cubic-bezier(0.4, 0, 0.2, 1);
        }

        body.sidebar-open-body {
            overflow: hidden !important;
            touch-action: none;
        }

        .layout { 
            display: flex; 
            min-height: 100vh; 
            min-height: 100dvh;
            min-width: 0; 
            width: 100%; 
            position: relative;
        }

        /* ===== SIDEBAR ===== */
        .sidebar {
            width: var(--sidebar-width);
            background: rgba(255, 255, 255, 0.78);
            backdrop-filter: blur(20px); 
            -webkit-backdrop-filter: blur(20px);
            border-right: 1px solid var(--border-glass);
            position: fixed; 
            top: 0; 
            left: 0; 
            bottom: 0;
            height: 100vh;
            height: 100dvh;
            display: flex; 
            flex-direction: column;
            z-index: 50;
            transition: transform 0.35s var(--ease-spring), width 0.35s var(--ease-spring), background 0.3s ease, box-shadow 0.35s ease;
            box-shadow: 2px 0 16px rgba(15, 23, 42, 0.03);
            will-change: transform;
            overscroll-behavior-y: contain;
        }

        [data-theme="dark"] .sidebar {
            background: rgba(15, 23, 42, 0.88);
            box-shadow: 4px 0 24px rgba(0, 0, 0, 0.25);
        }

        .sidebar-logo {
            height: var(--topnav-height);
            min-height: 64px;
            padding: 0 var(--space-md);
            display: flex; 
            align-items: center; 
            justify-content: space-between;
            gap: 0.5rem;
            border-bottom: 1px solid var(--border-glass);
            position: relative;
            flex-shrink: 0;
        }

        .sidebar-brand-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            color: inherit;
            flex: 1;
            min-width: 0;
            border-radius: 8px;
            padding: 4px 6px;
            margin: -4px -6px;
            transition: opacity 0.2s ease;
        }
        .sidebar-brand-link:hover {
            opacity: 0.92;
        }

        .sidebar-logo-icon {
            width: 38px; 
            height: 38px;
            background: var(--accent-gradient);
            border-radius: 10px;
            display: flex; 
            align-items: center; 
            justify-content: center;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.28);
            transition: transform 0.3s var(--ease-bounce), box-shadow 0.3s ease;
            flex-shrink: 0;
        }

        .sidebar-brand-link:hover .sidebar-logo-icon {
            transform: translateY(-2px) scale(1.06) rotate(3deg);
            box-shadow: 0 6px 18px rgba(37, 99, 235, 0.4);
        }

        .sidebar-logo-icon svg { 
            width: 20px; 
            height: 20px; 
            stroke: #ffffff; 
            stroke-width: 2.3; 
        }

        .sidebar-brand-meta {
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .sidebar-logo-text {
            font-family: 'Outfit', sans-serif;
            font-size: 1.12rem; 
            font-weight: 800;
            background: linear-gradient(135deg, #2563eb 0%, #38bdf8 50%, #4f46e5 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -0.01em;
            line-height: 1.15;
        }

        .sidebar-logo-sub {
            font-size: 0.64rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-muted);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Mobile Close Button in Sidebar Header */
        .sidebar-close-btn {
            display: none;
            width: 36px;
            height: 36px;
            border-radius: 8px;
            background: rgba(148, 163, 184, 0.12);
            border: 1px solid var(--border-glass);
            color: var(--text-muted);
            cursor: pointer;
            align-items: center;
            justify-content: center;
            transition: all 0.2s var(--ease-spring);
            flex-shrink: 0;
        }
        .sidebar-close-btn:hover {
            background: rgba(239, 68, 68, 0.12);
            color: var(--danger);
            border-color: rgba(239, 68, 68, 0.25);
            transform: rotate(90deg);
        }
        .sidebar-close-btn svg {
            width: 18px;
            height: 18px;
        }

        .sidebar-nav {
            flex: 1;
            padding: 0.85rem 0.75rem;
            overflow-y: auto;
            scroll-behavior: smooth;
            -webkit-overflow-scrolling: touch;
        }
        
        .sidebar-nav::-webkit-scrollbar { width: 4px; }
        .sidebar-nav::-webkit-scrollbar-track { background: transparent; }
        .sidebar-nav::-webkit-scrollbar-thumb {
            background: rgba(148, 163, 184, 0.22);
            border-radius: 8px;
            transition: background 0.2s ease;
        }
        .sidebar-nav::-webkit-scrollbar-thumb:hover {
            background: rgba(148, 163, 184, 0.45);
        }

        /* Sidebar Category Header */
        .sidebar-category {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.85rem 0.5rem 0.35rem;
            margin-top: 0.35rem;
            user-select: none;
        }
        .sidebar-category:first-of-type {
            margin-top: 0;
            padding-top: 0.15rem;
        }
        .sidebar-category-title {
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--text-muted);
            white-space: nowrap;
            transition: color 0.2s ease;
        }
        .sidebar-category-line {
            flex: 1;
            height: 1px;
            background: linear-gradient(90deg, var(--border-glass), transparent);
            transition: opacity 0.2s ease;
        }
        .sidebar-category:hover .sidebar-category-title {
            color: var(--accent-1);
        }

        /* Nav Item Base with Tactile Interaction & Accessible Target */
        .nav-item {
            position: relative;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            min-height: 44px; /* Accessible 44px minimum touch target */
            padding: 0.625rem 0.75rem;
            border-radius: 10px;
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
            margin-bottom: 3px;
            overflow: hidden;
            transition: transform 0.2s var(--ease-spring),
                        background 0.2s ease,
                        color 0.2s ease,
                        box-shadow 0.2s ease;
        }

        .nav-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%) scaleY(0);
            width: 3.5px;
            height: 55%;
            border-radius: 0 4px 4px 0;
            background: var(--accent-1);
            transition: transform 0.25s var(--ease-bounce);
        }

        .nav-item:hover { 
            color: var(--accent-1); 
            background: var(--bg-glass-hover);
            transform: translateX(4px); 
        }

        .nav-item:active {
            transform: scale(0.98);
        }

        .nav-item:hover svg {
            transform: scale(1.1) rotate(2deg);
            color: var(--accent-1);
        }

        .nav-item.active {
            color: var(--accent-1);
            background: rgba(37, 99, 235, 0.09);
            font-weight: 600;
            box-shadow: 0 1px 4px rgba(37, 99, 235, 0.1);
        }

        .nav-item.active::before {
            transform: translateY(-50%) scaleY(1);
        }

        .nav-item.active svg {
            color: var(--accent-1);
            filter: drop-shadow(0 2px 4px rgba(37, 99, 235, 0.25));
        }

        .nav-item svg {
            width: 1.15rem;
            height: 1.15rem;
            flex-shrink: 0;
            stroke-width: 2;
            transition: transform 0.25s var(--ease-bounce), color 0.2s ease, filter 0.2s ease;
        }

        /* Bot Connection Items */
        .nav-item-bot {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.55rem 0.65rem 0.55rem 0.75rem;
            border: 1px solid transparent;
            transition: all 0.28s cubic-bezier(0.2, 0.8, 0.2, 1);
        }

        .bot-item-left {
            display: flex;
            align-items: center;
            gap: 0.7rem;
            min-width: 0;
        }

        .bot-icon-badge {
            width: 28px;
            height: 28px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .bot-icon-badge svg {
            width: 17px;
            height: 17px;
            flex-shrink: 0;
        }

        .bot-icon-wa {
            background: rgba(37, 211, 102, 0.14);
            color: #25D366;
        }

        .bot-icon-tele {
            background: rgba(34, 158, 217, 0.14);
            color: #229ED9;
        }

        .bot-text-meta {
            display: flex;
            flex-direction: column;
            line-height: 1.15;
            min-width: 0;
        }

        .bot-label {
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--text-primary);
            white-space: nowrap;
        }

        .bot-subtext {
            font-size: 0.68rem;
            color: var(--text-muted);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .bot-actions {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            flex-shrink: 0;
        }

        .bot-status-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            padding: 2px 7px;
            border-radius: 20px;
            font-size: 0.65rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            outline: none;
            transition: all 0.2s ease;
        }

        .bot-status-pill:hover {
            transform: scale(1.06);
        }

        .bot-status-pill-active {
            background: rgba(16, 185, 129, 0.15);
            color: #10b981;
            border: 1px solid rgba(16, 185, 129, 0.28);
        }

        .bot-status-pill-connect {
            background: rgba(59, 130, 246, 0.12);
            color: var(--accent-1);
            border: 1px solid rgba(59, 130, 246, 0.22);
        }
        .bot-status-pill-connect:hover {
            background: var(--accent-1);
            color: #ffffff;
        }

        .live-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            display: inline-block;
        }

        .live-dot-green {
            background: #10b981;
            animation: pulseDotGreen 2s infinite;
        }

        .live-dot-blue {
            background: #229ED9;
            animation: pulseDotBlue 2s infinite;
        }

        @keyframes pulseDotGreen {
            0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
            70% { box-shadow: 0 0 0 5px rgba(16, 185, 129, 0); }
            100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }

        @keyframes pulseDotBlue {
            0% { box-shadow: 0 0 0 0 rgba(34, 158, 217, 0.7); }
            70% { box-shadow: 0 0 0 5px rgba(34, 158, 217, 0); }
            100% { box-shadow: 0 0 0 0 rgba(34, 158, 217, 0); }
        }

        @keyframes pulseDotDanger {
            0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
            70% { box-shadow: 0 0 0 5px rgba(239, 68, 68, 0); }
            100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
        }

        .bot-external-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
            opacity: 0.6;
            transition: all 0.2s ease;
        }

        .bot-external-icon svg {
            width: 13px !important;
            height: 13px !important;
            stroke-width: 2.2 !important;
        }

        .nav-item-wa:hover {
            background: rgba(37, 211, 102, 0.08);
            border-color: rgba(37, 211, 102, 0.25);
            transform: translateX(4px);
        }
        .nav-item-wa:hover .bot-icon-badge {
            transform: scale(1.1) rotate(-4deg);
        }
        .nav-item-wa:hover .bot-external-icon {
            color: #25D366;
            opacity: 1;
            transform: translate(2px, -2px);
        }

        .nav-item-tele:hover {
            background: rgba(34, 158, 217, 0.08);
            border-color: rgba(34, 158, 217, 0.25);
            transform: translateX(4px);
        }
        .nav-item-tele:hover .bot-icon-badge {
            transform: scale(1.1) rotate(6deg);
        }
        .nav-item-tele:hover .bot-external-icon {
            color: #229ED9;
            opacity: 1;
            transform: translate(2px, -2px);
        }

        /* SOS Button Animations */
        .btn-sos {
            animation: sosPulse 2.4s infinite ease-in-out;
            transition: all 0.3s cubic-bezier(0.2, 0.8, 0.2, 1);
        }
        @keyframes sosPulse {
            0% { box-shadow: 0 4px 14px rgba(239, 68, 68, 0.35), 0 0 0 0 rgba(239, 68, 68, 0.45); }
            70% { box-shadow: 0 6px 18px rgba(239, 68, 68, 0.45), 0 0 0 10px rgba(239, 68, 68, 0); }
            100% { box-shadow: 0 4px 14px rgba(239, 68, 68, 0.35), 0 0 0 0 rgba(239, 68, 68, 0); }
        }
        .btn-sos:hover {
            transform: translateY(-2px) scale(1.02);
            box-shadow: 0 8px 24px rgba(239, 68, 68, 0.55) !important;
        }
        .btn-sos:hover svg {
            animation: sosWobble 0.6s ease-in-out infinite alternate;
        }
        @keyframes sosWobble {
            0% { transform: rotate(-8deg); }
            100% { transform: rotate(8deg); }
        }

        .sidebar-footer {
            padding: var(--space-md) var(--space-lg);
            border-top: 1px solid var(--border-glass);
            font-size: var(--font-xs);
            color: var(--text-muted);
            text-align: center;
            background: rgba(0,0,0,0.015);
        }

        
        /* ===== MAIN CONTENT ===== */
        .main-wrapper {
            flex: 1; 
            margin-left: var(--sidebar-width);
            display: flex; 
            flex-direction: column;
            position: relative;
            min-width: 0;
            width: calc(100% - var(--sidebar-width));
            transition: margin-left 0.35s var(--ease-spring), width 0.35s var(--ease-spring);
        }

        .topnav {
            height: var(--topnav-height);
            min-height: 64px;
            background: var(--bg-glass);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--border-glass);
            display: flex; 
            align-items: center; 
            justify-content: space-between;
            padding: 0 var(--space-xl);
            position: sticky; 
            top: 0; 
            z-index: 30;
            transition: padding 0.3s ease;
        }

        .topnav-left { 
            display: flex; 
            align-items: center; 
            gap: var(--space-md);
            min-width: 0;
            flex: 1;
        }

        .menu-toggle {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 42px;
            height: 42px;
            min-width: 42px;
            border-radius: 10px;
            background: rgba(148, 163, 184, 0.08);
            border: 1px solid var(--border-glass);
            color: var(--text-primary);
            cursor: pointer;
            transition: all 0.2s var(--ease-spring);
            flex-shrink: 0;
        }
        .menu-toggle:hover { 
            background: rgba(37, 99, 235, 0.08); 
            border-color: rgba(37, 99, 235, 0.25);
            color: var(--accent-1);
            transform: scale(1.04);
        }
        .menu-toggle:active {
            transform: scale(0.95);
        }
        .menu-toggle svg { 
            width: 20px; 
            height: 20px; 
            stroke: currentColor; 
        }

        .topnav-heading {
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .topnav-title { 
            font-family: 'Outfit', sans-serif; 
            font-size: var(--font-md); 
            font-weight: 700; 
            color: var(--text-primary);
            letter-spacing: -0.01em;
            display: block;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .topnav-right { 
            display: flex; 
            align-items: center; 
            gap: var(--space-md);
            flex-shrink: 0;
        }

        .user-info { 
            display: flex; 
            align-items: center; 
            gap: var(--space-sm); 
            color: var(--text-primary);
            border-radius: 10px;
            padding: 4px 8px;
            transition: background 0.2s ease;
        }
        .user-info:hover {
            background: var(--bg-glass-hover);
        }

        .user-avatar {
            width: 38px; 
            height: 38px;
            background: var(--accent-gradient);
            border-radius: 10px;
            display: flex; 
            align-items: center; 
            justify-content: center;
            color: white; 
            font-weight: 700; 
            font-size: 0.88rem;
            box-shadow: 0 2px 8px rgba(37, 99, 235, 0.2);
            flex-shrink: 0;
        }

        .user-details { display: flex; flex-direction: column; min-width: 0; }
        .user-name { font-size: var(--font-sm); font-weight: 600; color: var(--text-primary); white-space: nowrap; }
        .user-role { font-size: var(--font-xs); color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }

        .logout-btn {
            padding: var(--space-sm) var(--space-md);
            background: rgba(239, 68, 68, 0.05); 
            border: 1px solid rgba(239, 68, 68, 0.1);
            border-radius: var(--radius-base); 
            cursor: pointer;
            font-size: var(--font-sm); 
            font-weight: 600; 
            color: var(--danger);
            transition: all 0.2s var(--ease-spring);
        }
        .logout-btn:hover { 
            background: var(--danger); 
            color: white; 
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2); 
            transform: translateY(-1px);
        }

        /* ===== CONTENT AREA & ANIMATION ===== */
        .content { 
            padding: var(--space-xl); 
            flex: 1; 
            animation: fadeIn 0.4s var(--ease-spring); 
            position: relative; 
            z-index: 1; 
            min-width: 0;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .page-title {
            font-size: clamp(1.4rem, 2.5vw, 1.85rem); 
            font-weight: 800;
            color: var(--text-primary); 
            margin-bottom: var(--space-lg);
            position: relative; 
            display: inline-block;
            letter-spacing: -0.02em;
        }

        /* ===== STAT CARDS WITH REFINED CRAFTSMANSHIP ===== */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.25rem; 
            margin-bottom: var(--space-xl);
        }

        .stat-card {
            background: var(--bg-card);
            border: 1px solid var(--border-glass);
            border-radius: 14px;
            padding: 1.25rem 1.35rem;
            transition: transform 0.28s var(--ease-spring),
                        box-shadow 0.28s ease,
                        border-color 0.28s ease;
            box-shadow: var(--shadow-soft);
            position: relative; 
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        
        .stat-card::before {
            content: ''; 
            position: absolute; 
            top: 0; 
            left: 0; 
            right: 0; 
            height: 3px;
            background: var(--accent-gradient); 
            opacity: 0; 
            transform: scaleX(0.2);
            transform-origin: left center;
            transition: opacity 0.25s ease, transform 0.3s var(--ease-spring);
        }

        .stat-card:hover { 
            transform: translateY(-4px); 
            box-shadow: 0 14px 30px -6px rgba(15, 23, 42, 0.12), 0 0 0 1px rgba(37, 99, 235, 0.25);
            border-color: rgba(37, 99, 235, 0.3);
        }
        
        .stat-card:hover::before { 
            opacity: 1; 
            transform: scaleX(1);
        }

        .stat-card:active {
            transform: translateY(-1px);
        }

        .stat-card:hover .stat-icon {
            transform: scale(1.1) rotate(4deg);
            background: rgba(37, 99, 235, 0.15);
        }

        .stat-header { 
            display: flex; 
            align-items: center; 
            justify-content: space-between; 
            margin-bottom: var(--space-md); 
        }

        .stat-title { 
            font-size: 0.72rem; 
            color: var(--text-muted); 
            font-weight: 700; 
            text-transform: uppercase; 
            letter-spacing: 0.06em; 
        }

        .stat-icon { 
            width: 40px; 
            height: 40px; 
            border-radius: 10px;
            background: rgba(37, 99, 235, 0.08); 
            display: flex; 
            align-items: center; 
            justify-content: center;
            color: var(--accent-1); 
            transition: transform 0.3s var(--ease-bounce), background 0.2s ease;
            flex-shrink: 0;
        }
        .stat-icon svg { width: 20px; height: 20px; stroke-width: 2.2; }

        .stat-value { 
            font-family: 'Outfit', sans-serif; 
            font-size: clamp(1.45rem, 2.2vw, 1.85rem); 
            font-weight: 800; 
            color: var(--text-primary); 
            line-height: 1.2;
            letter-spacing: -0.02em;
        }

        /* ===== CHARTS & DATA CARDS ===== */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: var(--space-lg);
            margin-bottom: var(--space-xl);
        }

        .data-card {
            background: var(--bg-card); 
            border: 1px solid var(--border-glass);
            border-radius: 14px; 
            box-shadow: var(--shadow-soft);
            margin-bottom: var(--space-xl);
            transition: box-shadow 0.28s ease, border-color 0.28s ease, transform 0.28s var(--ease-spring);
            overflow: hidden;
        }
        .data-card:hover { 
            box-shadow: 0 10px 28px -4px rgba(15, 23, 42, 0.08); 
            margin-bottom: var(--space-xl);
            transition: box-shadow 0.3s, transform 0.3s;
        }
        .data-card:hover { 
            box-shadow: 0 8px 25px rgba(37, 99, 235, 0.15); 
        }

        .data-card-header {
            padding: var(--space-lg);
            border-bottom: 1px solid var(--border-glass);
            display: flex; align-items: center; justify-content: space-between;
            background: rgba(255, 255, 255, 0.02);
            border-top-left-radius: 1rem;
            border-top-right-radius: 1rem;
        }
        .data-card-title { font-family: 'Outfit', sans-serif; font-size: var(--font-md); font-weight: 700; color: var(--text-primary); }
        .data-card-body { padding: var(--space-lg); }

        table { width: 100%; border-collapse: separate; border-spacing: 0; }
        th, td { padding: var(--space-md) var(--space-lg); text-align: left; font-size: var(--font-sm); }
        th { 
            background: var(--bg-body); color: var(--text-secondary); 
            font-weight: 600; font-size: calc(var(--font-xs) * 1.1); text-transform: uppercase; 
            letter-spacing: 1px; border-bottom: 1px solid var(--border-glass); 
            border-top: 1px solid var(--border-glass);
        }
        td { border-bottom: 1px solid var(--border-glass); color: var(--text-primary); }
        tr:hover td { background: rgba(0,0,0,0.01); }
        tr:last-child td { border-bottom: none; }

        /* ===== BUTTONS ===== */
        .btn {
            padding: var(--space-sm) var(--space-lg); border: none; border-radius: var(--radius-base);
            font-size: var(--font-sm); font-weight: 600; cursor: pointer;
            display: inline-flex; align-items: center; gap: var(--space-sm);
            font-family: inherit; transition: all 0.3s var(--animation-timing);
            text-decoration: none; justify-content: center;
        }
        .btn-primary { 
            background: var(--accent-1); color: white; 
            box-shadow: 0 4px 10px rgba(37, 99, 235, 0.2);
        }
        .btn-primary:hover { 
            background: #1d4ed8;
            transform: translateY(-2px); 
            box-shadow: 0 6px 15px rgba(37, 99, 235, 0.3); 
        }
        .btn-success { background: #d1fae5; color: #065f46; }
        .btn-success:hover { background: #a7f3d0; }
        .btn-danger { background: #fee2e2; color: #991b1b; }
        .btn-danger:hover { background: #fecaca; }
        .btn-outline { background: transparent; border: 1px solid var(--border-glass); color: var(--text-primary); }
        .btn-outline:hover { background: var(--bg-body); border-color: #d4d4d8; }

        /* ===== ACTION ICON BUTTONS ===== */
        .btn-icon {
            width: 32px;
            height: 32px;
            min-width: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: var(--radius-base, 8px);
            border: 1px solid transparent;
            cursor: pointer;
            transition: all 0.2s var(--animation-timing);
            text-decoration: none;
            flex-shrink: 0;
        }
        .btn-icon svg {
            width: 15px;
            height: 15px;
            stroke-width: 2;
            flex-shrink: 0;
        }

        /* Edit / Primary variant */
        .btn-icon-edit, .btn-icon-primary {
            background: rgba(37, 99, 235, 0.08);
            border-color: rgba(37, 99, 235, 0.25);
            color: #2563eb;
        }
        .btn-icon-edit:hover, .btn-icon-primary:hover {
            background: #2563eb;
            border-color: #2563eb;
            color: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(37, 99, 235, 0.3);
        }

        /* Delete / Danger variant */
        .btn-icon-delete, .btn-icon-danger {
            background: rgba(239, 68, 68, 0.08);
            border-color: rgba(239, 68, 68, 0.25);
            color: #dc2626;
        }
        .btn-icon-delete:hover, .btn-icon-danger:hover {
            background: #dc2626;
            border-color: #dc2626;
            color: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(239, 68, 68, 0.3);
        }

        /* View / Outline / Info variant */
        .btn-icon-view, .btn-icon-outline, .btn-icon-info {
            background: rgba(100, 116, 139, 0.08);
            border-color: rgba(100, 116, 139, 0.22);
            color: #475569;
        }
        .btn-icon-view:hover, .btn-icon-outline:hover, .btn-icon-info:hover {
            background: #475569;
            border-color: #475569;
            color: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(71, 85, 105, 0.3);
        }

        /* Success / Print variant */
        .btn-icon-success {
            background: rgba(16, 185, 129, 0.08);
            border-color: rgba(16, 185, 129, 0.25);
            color: #10b981;
        }
        .btn-icon-success:hover {
            background: #10b981;
            border-color: #10b981;
            color: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(16, 185, 129, 0.3);
        }

        /* Dark Mode overrides for Icon Buttons */
        [data-theme="dark"] .btn-icon-edit, [data-theme="dark"] .btn-icon-primary {
            background: rgba(59, 130, 246, 0.15);
            border-color: rgba(59, 130, 246, 0.35);
            color: #60a5fa;
        }
        [data-theme="dark"] .btn-icon-edit:hover, [data-theme="dark"] .btn-icon-primary:hover {
            background: #3b82f6;
            border-color: #3b82f6;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.45);
        }

        [data-theme="dark"] .btn-icon-delete, [data-theme="dark"] .btn-icon-danger {
            background: rgba(239, 68, 68, 0.15);
            border-color: rgba(239, 68, 68, 0.35);
            color: #f87171;
        }
        [data-theme="dark"] .btn-icon-delete:hover, [data-theme="dark"] .btn-icon-danger:hover {
            background: #ef4444;
            border-color: #ef4444;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.45);
        }

        [data-theme="dark"] .btn-icon-view, [data-theme="dark"] .btn-icon-outline, [data-theme="dark"] .btn-icon-info {
            background: rgba(148, 163, 184, 0.12);
            border-color: rgba(148, 163, 184, 0.25);
            color: #cbd5e1;
        }
        [data-theme="dark"] .btn-icon-view:hover, [data-theme="dark"] .btn-icon-outline:hover, [data-theme="dark"] .btn-icon-info:hover {
            background: #64748b;
            border-color: #64748b;
            color: #ffffff;
        }

        [data-theme="dark"] .btn-icon-success {
            background: rgba(16, 185, 129, 0.15);
            border-color: rgba(16, 185, 129, 0.35);
            color: #34d399;
        }
        [data-theme="dark"] .btn-icon-success:hover {
            background: #10b981;
            border-color: #10b981;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.45);
        }

        /* ===== PAGINATION ===== */
        .siwil-pagination-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
            width: 100%;
        }
        .siwil-pagination-info {
            font-size: var(--font-sm, 0.875rem);
            color: var(--text-muted);
            font-weight: 500;
        }
        .siwil-pagination-highlight {
            font-weight: 700;
            color: var(--text-primary);
        }
        .siwil-pagination-controls {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            flex-wrap: wrap;
        }
        .siwil-page-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            padding: 0;
            font-size: var(--font-sm, 0.875rem);
            font-weight: 600;
            color: var(--text-primary);
            background: var(--bg-card);
            border: 1px solid var(--border-glass);
            border-radius: var(--radius-base, 8px);
            text-decoration: none;
            transition: all 0.2s var(--animation-timing);
            user-select: none;
            flex-shrink: 0;
        }
        .siwil-page-btn:hover:not(.siwil-page-disabled) {
            background: rgba(37, 99, 235, 0.1);
            border-color: var(--accent-1);
            color: var(--accent-1);
            transform: translateY(-1px);
        }
        .siwil-page-btn.siwil-page-disabled {
            opacity: 0.4;
            cursor: not-allowed;
            background: transparent;
            border-color: var(--border-glass);
            color: var(--text-muted);
            pointer-events: none;
        }
        .siwil-page-numbers {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }
        .siwil-page-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 32px;
            height: 32px;
            padding: 0 0.5rem;
            font-size: var(--font-sm, 0.875rem);
            font-weight: 600;
            color: var(--text-secondary);
            background: transparent;
            border: 1px solid transparent;
            border-radius: var(--radius-base, 8px);
            text-decoration: none;
            transition: all 0.2s var(--animation-timing);
        }
        .siwil-page-number:hover:not(.active) {
            background: rgba(255, 255, 255, 0.05);
            border-color: var(--border-glass);
            color: var(--text-primary);
        }
        .siwil-page-number.active {
            background: var(--accent-1);
            color: #ffffff;
            border-color: var(--accent-1);
            box-shadow: 0 2px 8px rgba(37, 99, 235, 0.35);
        }
        .siwil-page-dots {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 32px;
            color: var(--text-muted);
            font-size: 0.875rem;
        }

        /* ===== BADGES ===== */
        .badge { padding: var(--space-xs) var(--space-sm); border-radius: 20px; font-size: var(--font-xs); font-weight: 600; display: inline-block; letter-spacing: 0.3px; }
        .badge-blue { background: #e0f2fe; color: #0369a1; }
        .badge-green { background: #d1fae5; color: #065f46; }
        .badge-red { background: #fee2e2; color: #991b1b; }
        .badge-yellow { background: #fef3c7; color: #b45309; }

        /* ===== ALERTS ===== */
        .alert { padding: var(--space-lg); border-radius: var(--radius-golden); font-size: var(--font-sm); margin-bottom: var(--space-lg); border: 1px solid; display: flex; align-items: center; gap: var(--space-md); font-weight: 500; }
        .alert-error { background: #fef2f2; border-color: #fecaca; color: #991b1b; }
        .alert-success { background: #ecfdf5; border-color: #a7f3d0; color: #065f46; }
        .alert-info { background: #eff6ff; border-color: #bfdbfe; color: #1e40af; }

        /* ===== FORMS & CUSTOM SELECT ===== */
        .form-group { margin-bottom: var(--space-lg); }
        .form-label { display: block; margin-bottom: var(--space-sm); font-size: var(--font-sm); font-weight: 500; color: var(--text-secondary); }
        
        .form-input, .form-select, .form-textarea {
            width: 100%; 
            padding: var(--space-md) var(--space-lg);
            background: #ffffff; 
            border: 1px solid #d4d4d8;
            border-radius: var(--radius-golden); 
            font-size: var(--font-sm);
            font-weight: 500;
            color: var(--text-primary); 
            outline: none;
            transition: all 0.25s var(--animation-timing); 
            font-family: inherit;
        }
        
        .form-input:hover, .form-select:hover, .form-textarea:hover {
            border-color: #a1a1aa;
        }

        .form-input:focus, .form-select:focus, .form-textarea:focus { 
            border-color: var(--accent-1); 
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15); 
        }
        
        .form-input::placeholder { color: #a1a1aa; font-weight: 400; }
        .form-textarea { resize: vertical; min-height: calc(120px * var(--ratio)); }

        .form-select {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='18' height='18' viewBox='0 0 24 24' fill='none' stroke='%232563eb' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right var(--space-lg) center;
            padding-right: var(--space-xl);
            cursor: pointer;
        }

        /* ===== RESPONSIVE & MOBILE OVERLAY ===== */
        .mobile-overlay {
            position: fixed; 
            inset: 0;
            background: rgba(15, 23, 42, 0.45); 
            backdrop-filter: blur(4px); 
            -webkit-backdrop-filter: blur(4px); 
            z-index: 45;
            opacity: 0; 
            pointer-events: none;
            transition: opacity 0.3s var(--ease-spring);
        }
        .mobile-overlay.active {
            opacity: 1;
            pointer-events: auto;
        }
        
        .table-responsive {
            width: 100%; 
            overflow-x: auto; 
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
            scrollbar-color: rgba(148, 163, 184, 0.3) transparent;
        }
        .table-responsive::-webkit-scrollbar {
            height: 6px;
        }
        .table-responsive::-webkit-scrollbar-track {
            background: transparent;
        }
        .table-responsive::-webkit-scrollbar-thumb {
            background: rgba(148, 163, 184, 0.25);
            border-radius: 6px;
        }
        .table-responsive::-webkit-scrollbar-thumb:hover {
            background: rgba(148, 163, 184, 0.45);
        }

        /* Large Desktop (>= 1200px) */
        @media (min-width: 1200px) {
            .stats-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        /* 1025px - 1199px */
        @media (max-width: 1199px) and (min-width: 993px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
            }
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }

        /* ===== DESKTOP SIDEBAR TOGGLE (>= 993px) ===== */
        @media (min-width: 993px) {
            .sidebar.sidebar-closed {
                transform: translateX(-100%);
            }
            .main-wrapper.sidebar-closed {
                margin-left: 0 !important;
                width: 100% !important;
            }
        }

        /* Tablet & Mobile Breakpoint (<= 992px) */
        @media (max-width: 992px) {
            .sidebar { 
                position: fixed;
                top: 0;
                left: 0;
                bottom: 0;
                width: min(300px, 84vw);
                height: 100vh;
                height: 100dvh;
                transform: translateX(-100%);
                z-index: 100;
                box-shadow: none;
            }
            .sidebar.open { 
                transform: translateX(0); 
                box-shadow: 12px 0 35px rgba(0, 0, 0, 0.25);
            }
            .sidebar-close-btn {
                display: flex;
            }
            .main-wrapper { 
                margin-left: 0 !important; 
                width: 100% !important; 
            }
            .dashboard-grid { 
                grid-template-columns: 1fr; 
            }
        }

        /* Medium Tablet (<= 768px) */
        @media (max-width: 768px) {
            .user-details { display: none; }
            .topnav { padding: 0 var(--space-md); }
            .content { padding: 1.25rem 1rem 95px; }
            .page-title { font-size: 1.45rem; margin-bottom: 1.25rem; }
            .stats-grid { 
                grid-template-columns: repeat(2, 1fr); 
                gap: 0.85rem; 
            }
            .data-card-header { 
                flex-direction: column; 
                align-items: flex-start; 
                gap: 0.75rem; 
            }
            .data-card-header > .btn { 
                width: 100%; 
            }
            .card-header-actions { 
                width: 100%; 
                display: flex; 
                gap: 0.5rem; 
            }
            .card-header-actions .btn { 
                flex: 1 1 auto; 
                text-align: center; 
                justify-content: center; 
            }
        }

        /* Small Screen / Phone (<= 640px) */
        @media (max-width: 640px) {
            .content { 
                padding: 1rem 0.75rem 110px; 
            }
            .stats-grid { 
                grid-template-columns: 1fr; 
                gap: 0.75rem; 
            }
            .stat-card { 
                padding: 1.15rem 1.15rem; 
            }
            .stat-value { 
                font-size: 1.55rem; 
            }
            .topnav { 
                padding: 0 0.85rem; 
            }
            .data-card-body { 
                padding: 1rem 0.85rem; 
            }
            th, td { 
                padding: 0.75rem 0.85rem; 
                font-size: 0.8rem; 
            }
        }

        /* Prefers-reduced-motion: Accessibility first (R-19, antislop-human) */
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
                scroll-behavior: auto !important;
            }
        }

        /* ===== UTILITIES ===== */
        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: var(--space-2xl) var(--space-lg);
            text-align: center;
            color: var(--text-secondary);
            background: rgba(0,0,0,0.01);
        }

        /* ===== SIWIL CUSTOM CONFIRMATION MODAL ===== */
        .siwil-confirm-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 100000;
            padding: 1.25rem;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.22s ease, visibility 0.22s ease;
        }
        .siwil-confirm-overlay.active {
            opacity: 1;
            visibility: visible;
        }
        .siwil-confirm-dialog {
            background: var(--bg-card);
            border: 1px solid var(--border-glass, rgba(255, 255, 255, 0.12));
            border-radius: 20px;
            box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.05);
            max-width: 440px;
            width: 100%;
            padding: 2rem 1.75rem 1.75rem;
            text-align: center;
            transform: scale(0.92) translateY(12px);
            transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .siwil-confirm-overlay.active .siwil-confirm-dialog {
            transform: scale(1) translateY(0);
        }
        .siwil-confirm-icon-wrapper {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.25rem;
        }
        .siwil-confirm-icon-wrapper.info {
            background: rgba(59, 130, 246, 0.12);
            color: #3b82f6;
            box-shadow: 0 0 0 8px rgba(59, 130, 246, 0.06);
        }
        .siwil-confirm-icon-wrapper.success {
            background: rgba(16, 185, 129, 0.12);
            color: #10b981;
            box-shadow: 0 0 0 8px rgba(16, 185, 129, 0.06);
        }
        .siwil-confirm-icon-wrapper.warning {
            background: rgba(245, 158, 11, 0.12);
            color: #f59e0b;
            box-shadow: 0 0 0 8px rgba(245, 158, 11, 0.06);
        }
        .siwil-confirm-icon-wrapper.danger {
            background: rgba(239, 68, 68, 0.12);
            color: #ef4444;
            box-shadow: 0 0 0 8px rgba(239, 68, 68, 0.06);
        }
        .siwil-confirm-title {
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--text-primary);
            margin: 0 0 0.5rem 0;
            line-height: 1.3;
        }
        .siwil-confirm-message {
            font-size: 0.92rem;
            color: var(--text-secondary);
            line-height: 1.55;
            margin: 0 0 1.75rem 0;
        }
        .siwil-confirm-actions {
            display: flex;
            gap: 0.75rem;
            width: 100%;
            justify-content: center;
        }
        .siwil-confirm-actions .btn {
            flex: 1;
            padding: 0.75rem 1.25rem;
            font-weight: 700;
            border-radius: 10px;
            font-size: 0.92rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
    </style>
    @yield('styles')
</head>
<body>

    <div class="layout">
        @include('layouts.partials.sidebar')

        <div class="main-wrapper">
            @include('layouts.partials.topnav')

            <main class="content">
                @yield('content')
            </main>
        </div>

        <div class="mobile-overlay" id="mobile-overlay" onclick="toggleSidebar()"></div>
    </div>

    @include('layouts.partials.floating-chat')
    @include('layouts.partials.connect-modal')

    <script>
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const overlay = document.getElementById('mobile-overlay');
            const mainWrapper = document.querySelector('.main-wrapper');

            if (window.innerWidth <= 992) {
                if (!sidebar) return;
                const isOpen = sidebar.classList.toggle('open');
                if (overlay) overlay.classList.toggle('active', isOpen);
                document.body.classList.toggle('sidebar-open-body', isOpen);
            } else {
                if (sidebar) sidebar.classList.toggle('sidebar-closed');
                if (mainWrapper) mainWrapper.classList.toggle('sidebar-closed');
            }
        }

        // Close sidebar on Escape key or outside click
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const sidebar = document.querySelector('.sidebar');
                if (sidebar && sidebar.classList.contains('open')) {
                    toggleSidebar();
                }
                if (typeof closeAllTopnavDropdowns === 'function') {
                    closeAllTopnavDropdowns();
                }
            }
        });

        // Clean up mobile drawer state if screen is resized to desktop width
        window.addEventListener('resize', function() {
            if (window.innerWidth > 992) {
                const sidebar = document.querySelector('.sidebar');
                const overlay = document.getElementById('mobile-overlay');
                if (sidebar && sidebar.classList.contains('open')) {
                    sidebar.classList.remove('open');
                }
                if (overlay && overlay.classList.contains('active')) {
                    overlay.classList.remove('active');
                }
                document.body.classList.remove('sidebar-open-body');
            }
        });


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
            
            // Auto-wrap all tables to make them responsive on mobile
            document.querySelectorAll('table').forEach(table => {
                if (!table.parentElement.classList.contains('table-responsive')) {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'table-responsive';
                    table.parentNode.insertBefore(wrapper, table);
                    wrapper.appendChild(table);
                }
            });

            // Initialize custom dropdowns
            window.initCustomDropdowns = function(root = document) {
                root.querySelectorAll('select.form-select').forEach(select => {
                    if (select.closest('.custom-dropdown-container')) return;
                    window.buildCustomDropdown(select);
                });
            };

            window.buildCustomDropdown = function(select) {
                if (select.closest('.custom-dropdown-container')) return;

                const container = document.createElement('div');
                container.className = 'custom-dropdown-container';
                
                // Inherit sizing from original select
                const selectStyle = select.getAttribute('style') || '';
                const maxWidthMatch = selectStyle.match(/max-width\s*:\s*([^;]+)/);
                const widthMatch = selectStyle.match(/(?<![a-z-])width\s*:\s*([^;]+)/);
                const minWidthMatch = selectStyle.match(/min-width\s*:\s*([^;]+)/);
                if (maxWidthMatch) container.style.maxWidth = maxWidthMatch[1].trim();
                if (minWidthMatch) container.style.minWidth = minWidthMatch[1].trim();
                if (widthMatch) {
                    container.style.width = widthMatch[1].trim();
                } else if (select.closest('.filter-bar') || select.closest('form')) {
                    const parentForm = select.closest('form');
                    const formDisplay = parentForm ? window.getComputedStyle(parentForm).display : '';
                    if (formDisplay.includes('flex') || select.closest('.filter-bar')) {
                        container.style.width = 'auto';
                    }
                }
                container.style.display = 'inline-block';
                container.style.flexShrink = '0';
                
                const trigger = document.createElement('div');
                trigger.className = 'custom-dropdown-trigger';
                
                const textSpan = document.createElement('span');
                textSpan.className = 'custom-dropdown-text';
                textSpan.style.overflow = 'hidden';
                textSpan.style.textOverflow = 'ellipsis';
                textSpan.innerText = select.options[select.selectedIndex]?.text || 'Pilih...';
                
                const icon = document.createElement('div');
                icon.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>';
                
                trigger.appendChild(textSpan);
                trigger.appendChild(icon.firstChild);
                
                const menu = document.createElement('div');
                menu.className = 'custom-dropdown-menu';
                
                container.appendChild(trigger);
                container.appendChild(menu);
                
                // Insert container before select, then move select into container (hidden via CSS)
                select.parentNode.insertBefore(container, select);
                container.appendChild(select);

                window.populateCustomDropdownMenu(select, container);
                
                // Toggle logic
                trigger.addEventListener('click', (e) => {
                    e.stopPropagation();
                    
                    document.querySelectorAll('.custom-dropdown-container').forEach(c => {
                        if (c !== container) {
                            c.classList.remove('open');
                            const otherCard = c.closest('.data-card');
                            if (otherCard) otherCard.style.zIndex = '';
                        }
                    });
                    
                    container.classList.toggle('open');
                    
                    const parentCard = container.closest('.data-card');
                    if (parentCard) {
                        if (container.classList.contains('open')) {
                            parentCard.style.position = 'relative';
                            parentCard.style.zIndex = '999';
                        } else {
                            parentCard.style.zIndex = '';
                        }
                    }
                });
            };

            window.populateCustomDropdownMenu = function(select, container) {
                const menu = container.querySelector('.custom-dropdown-menu');
                const textSpan = container.querySelector('.custom-dropdown-text');
                if (!menu) return;

                menu.innerHTML = '';
                Array.from(select.options).forEach((opt) => {
                    if (opt.hidden || opt.disabled || opt.style.display === 'none') return;

                    const item = document.createElement('div');
                    item.className = 'custom-dropdown-item';
                    if (opt.selected) item.classList.add('selected');
                    item.innerText = opt.text;
                    item.dataset.value = opt.value;
                    
                    item.addEventListener('click', (e) => {
                        e.stopPropagation();
                        select.value = opt.value;
                        select.dispatchEvent(new Event('change', { bubbles: true }));
                        
                        if (textSpan) textSpan.innerText = opt.text;
                        menu.querySelectorAll('.custom-dropdown-item').forEach(i => i.classList.remove('selected'));
                        item.classList.add('selected');
                        container.classList.remove('open');
                        
                        const parentCard = container.closest('.data-card');
                        if (parentCard) parentCard.style.zIndex = '';
                    });
                    menu.appendChild(item);
                });

                if (textSpan) {
                    textSpan.innerText = select.options[select.selectedIndex]?.text || 'Pilih...';
                }
            };

            window.rebuildCustomDropdown = function(select) {
                const container = select.closest('.custom-dropdown-container');
                if (container) {
                    window.populateCustomDropdownMenu(select, container);
                } else {
                    window.buildCustomDropdown(select);
                }
            };

            // Global Universal AJAX Filter Engine
            window.initAjaxFilters = function() {
                const filterForms = document.querySelectorAll('form[data-ajax-filter="true"], .filter-bar form');

                filterForms.forEach(form => {
                    if (form.dataset.ajaxInitialized === 'true') return;
                    form.dataset.ajaxInitialized = 'true';

                    let targetSelector = form.dataset.ajaxTarget;
                    if (!targetSelector) {
                        const nextCard = form.closest('.data-card')?.nextElementSibling;
                        if (nextCard && nextCard.classList.contains('data-card')) {
                            if (!nextCard.id) nextCard.id = 'autoFilterTarget_' + Math.random().toString(36).substr(2, 9);
                            targetSelector = '#' + nextCard.id;
                        } else {
                            const parentCard = form.closest('.data-card');
                            if (parentCard) {
                                if (!parentCard.id) parentCard.id = 'autoFilterTarget_' + Math.random().toString(36).substr(2, 9);
                                targetSelector = '#' + parentCard.id;
                            }
                        }
                    }
                    if (!targetSelector) return;

                    let debounceTimer = null;
                    let abortCtrl = null;

                    const performFilter = (newUrl = null) => {
                        const targetEl = document.querySelector(targetSelector);
                        if (!targetEl) return;

                        let fetchUrl;
                        if (newUrl) {
                            fetchUrl = newUrl;
                        } else {
                            const url = new URL(form.action || window.location.href, window.location.origin);
                            const formData = new FormData(form);

                            for (const [key, val] of formData.entries()) {
                                if (val !== null && val.toString().trim() !== '') {
                                    url.searchParams.set(key, val);
                                } else {
                                    url.searchParams.delete(key);
                                }
                            }
                            // Reset pagination parameter on filter change
                            const pageParams = ['page', 'penduduk_page', 'desa_page', 'rw_page', 'rt_page'];
                            pageParams.forEach(p => {
                                if (!formData.has(p) && url.searchParams.has(p)) {
                                    url.searchParams.delete(p);
                                }
                            });
                            fetchUrl = url.toString();
                        }

                        if (abortCtrl) abortCtrl.abort();
                        abortCtrl = new AbortController();

                        // Set loading state smoothly
                        targetEl.style.transition = 'opacity 0.2s ease';
                        targetEl.style.opacity = '0.45';
                        targetEl.style.pointerEvents = 'none';

                        fetch(fetchUrl, {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' },
                            signal: abortCtrl.signal
                        })
                        .then(res => res.text())
                        .then(html => {
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(html, 'text/html');

                            const newTargetEl = doc.querySelector(targetSelector);
                            if (newTargetEl) {
                                targetEl.innerHTML = newTargetEl.innerHTML;
                            }

                            // Update count badge if specified
                            const badgeSelector = form.dataset.countBadge;
                            if (badgeSelector) {
                                const oldBadge = document.querySelector(badgeSelector);
                                const newBadge = doc.querySelector(badgeSelector);
                                if (oldBadge && newBadge) oldBadge.innerHTML = newBadge.innerHTML;
                            }

                            // Update URL history without page reload
                            window.history.replaceState(null, '', fetchUrl);

                            // Toggle reset button visibility if present
                            const resetBtn = form.querySelector('.reset-filter-btn') || form.querySelector('a.btn-outline[href]');
                            if (resetBtn) {
                                const hasActiveVal = Array.from(form.elements).some(el => {
                                    if (el.tagName === 'BUTTON' || el.type === 'submit') return false;
                                    return el.value && el.value.toString().trim() !== '';
                                });
                                resetBtn.style.display = hasActiveVal ? 'inline-flex' : 'none';
                            }

                            // Bind pagination links inside new container
                            bindPaginationLinks(targetEl, performFilter);

                            // Re-init any custom dropdowns inside targetEl
                            window.initCustomDropdowns(targetEl);
                        })
                        .catch(err => {
                            if (err.name !== 'AbortError') {
                                console.error('SIWIL AJAX filter error:', err);
                            }
                        })
                        .finally(() => {
                            targetEl.style.opacity = '1';
                            targetEl.style.pointerEvents = 'auto';
                        });
                    };

                    // Live filter on select change
                    form.querySelectorAll('select').forEach(select => {
                        select.addEventListener('change', () => {
                            if (debounceTimer) clearTimeout(debounceTimer);
                            performFilter();
                        });
                    });

                    // Live filter on text input typing with debounce
                    form.querySelectorAll('input[type="text"], input[type="search"]').forEach(input => {
                        input.addEventListener('input', () => {
                            if (debounceTimer) clearTimeout(debounceTimer);
                            debounceTimer = setTimeout(() => {
                                performFilter();
                            }, 350);
                        });
                    });

                    // Form submit immediately
                    form.addEventListener('submit', (e) => {
                        e.preventDefault();
                        if (debounceTimer) clearTimeout(debounceTimer);
                        performFilter();
                    });

                    // Intercept reset button
                    const resetBtn = form.querySelector('.reset-filter-btn') || form.querySelector('a.btn-outline[href]');
                    if (resetBtn) {
                        resetBtn.addEventListener('click', (e) => {
                            e.preventDefault();
                            form.reset();
                            form.querySelectorAll('input[type="text"], input[type="search"]').forEach(i => i.value = '');
                            form.querySelectorAll('select').forEach(s => {
                                s.value = '';
                                if (typeof window.rebuildCustomDropdown === 'function') {
                                    window.rebuildCustomDropdown(s);
                                }
                            });

                            const rwSel = form.querySelector('#kependudukanRwFilter');
                            if (rwSel) rwSel.dispatchEvent(new Event('change'));

                            performFilter(form.getAttribute('action') || window.location.pathname);
                        });
                    }

                    // Initial pagination binding
                    const targetEl = document.querySelector(targetSelector);
                    if (targetEl) {
                        bindPaginationLinks(targetEl, performFilter);
                    }
                });

                function bindPaginationLinks(container, filterCallback) {
                    container.querySelectorAll('.pagination a, a.page-link, [aria-label*="Pagination"] a').forEach(link => {
                        link.addEventListener('click', (e) => {
                            e.preventDefault();
                            filterCallback(link.href);
                        });
                    });
                }
            };

            // Run initial custom dropdowns and AJAX filter engine
            window.initCustomDropdowns();
            window.initAjaxFilters();

            // Close all custom dropdowns when clicking outside
            document.addEventListener('click', () => {
                document.querySelectorAll('.custom-dropdown-container.open').forEach(c => {
                    c.classList.remove('open');
                    const parentCard = c.closest('.data-card');
                    if (parentCard) parentCard.style.zIndex = '';
                });
            });
        });

        // Global Custom SIWIL Confirmation Modal Promise
        window.siwilConfirm = function(options = {}) {
            return new Promise((resolve) => {
                const overlay = document.getElementById('siwilConfirmOverlay');
                const iconWrapper = document.getElementById('siwilConfirmIconWrapper');
                const titleEl = document.getElementById('siwilConfirmTitle');
                const messageEl = document.getElementById('siwilConfirmMessage');
                const cancelBtn = document.getElementById('siwilConfirmCancelBtn');
                const okBtn = document.getElementById('siwilConfirmOkBtn');

                if (!overlay || !okBtn || !cancelBtn || !titleEl || !messageEl) {
                    // Fallback if modal not present
                    resolve(window.confirm(options.message || 'Apakah Anda yakin?'));
                    return;
                }

                const type = options.type || 'info';
                const title = options.title || 'Konfirmasi Tindakan';
                const message = options.message || 'Apakah Anda yakin ingin melanjutkan tindakan ini?';
                const confirmText = options.confirmText || 'Lanjutkan';
                const cancelText = options.cancelText || 'Batal';

                titleEl.textContent = title;
                messageEl.textContent = message;
                cancelBtn.textContent = cancelText;
                okBtn.textContent = confirmText;

                // Reset Class
                iconWrapper.className = 'siwil-confirm-icon-wrapper ' + type;
                okBtn.className = 'btn ' + (type === 'danger' ? 'btn-danger' : (type === 'success' ? 'btn-success' : 'btn-primary'));

                const icons = {
                    info: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="width:28px;height:28px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>',
                    success: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="width:28px;height:28px;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>',
                    warning: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="width:28px;height:28px;"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
                    danger: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="width:28px;height:28px;"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>'
                };
                iconWrapper.innerHTML = icons[type] || icons.info;

                overlay.style.display = 'flex';
                // Trigger reflow for smooth transition
                void overlay.offsetWidth;
                overlay.classList.add('active');

                function cleanup() {
                    overlay.classList.remove('active');
                    setTimeout(() => {
                        overlay.style.display = 'none';
                    }, 240);
                    okBtn.removeEventListener('click', onOk);
                    cancelBtn.removeEventListener('click', onCancel);
                    overlay.removeEventListener('click', onOverlay);
                    document.removeEventListener('keydown', onKey);
                }

                function onOk() {
                    cleanup();
                    resolve(true);
                }

                function onCancel() {
                    cleanup();
                    resolve(false);
                }

                function onOverlay(e) {
                    if (e.target === overlay) {
                        cleanup();
                        resolve(false);
                    }
                }

                function onKey(e) {
                    if (e.key === 'Escape') {
                        cleanup();
                        resolve(false);
                    }
                }

                okBtn.addEventListener('click', onOk);
                cancelBtn.addEventListener('click', onCancel);
                overlay.addEventListener('click', onOverlay);
                document.addEventListener('keydown', onKey);
            });
        };
    </script>

    {{-- Global Custom SIWIL Confirmation Modal DOM --}}
    <div id="siwilConfirmOverlay" class="siwil-confirm-overlay" style="display: none;">
        <div class="siwil-confirm-dialog">
            <div id="siwilConfirmIconWrapper" class="siwil-confirm-icon-wrapper info">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="width:28px;height:28px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
            </div>
            <h3 id="siwilConfirmTitle" class="siwil-confirm-title">Konfirmasi Tindakan</h3>
            <p id="siwilConfirmMessage" class="siwil-confirm-message">Apakah Anda yakin ingin melanjutkan tindakan ini?</p>
            <div class="siwil-confirm-actions">
                <button type="button" id="siwilConfirmCancelBtn" class="btn btn-outline">Batal</button>
                <button type="button" id="siwilConfirmOkBtn" class="btn btn-primary">Lanjutkan</button>
            </div>
        </div>
    </div>

    @yield('scripts')
</body>
</html>
