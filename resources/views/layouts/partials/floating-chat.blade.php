<style>
    /* ===== FLOATING CUSTOMER SERVICE CHATBOT WIDGET ===== */
    .siwil-floating-widget {
        position: fixed;
        bottom: 24px;
        right: 24px;
        z-index: 9999;
        font-family: 'Outfit', sans-serif;
    }

    /* Floating Launcher Button */
    .siwil-chat-launcher {
        display: flex;
        align-items: center;
        gap: 10px;
        background: var(--accent-gradient, linear-gradient(135deg, #2563eb, #3b82f6));
        color: #ffffff;
        border: none;
        padding: 12px 18px 12px 14px;
        border-radius: 999px;
        cursor: pointer;
        box-shadow: 0 8px 25px -4px rgba(37, 99, 235, 0.45), 0 0 0 1px rgba(255, 255, 255, 0.2);
        transition: all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
        position: relative;
    }

    .siwil-chat-launcher:hover {
        transform: translateY(-3px) scale(1.03);
        box-shadow: 0 12px 30px -4px rgba(37, 99, 235, 0.6), 0 0 0 2px rgba(255, 255, 255, 0.3);
    }

    .siwil-chat-launcher:active {
        transform: translateY(0) scale(0.97);
    }

    .siwil-launcher-icon-box {
        position: relative;
        width: 36px;
        height: 36px;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(4px);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .siwil-launcher-icon-box svg {
        width: 20px;
        height: 20px;
        fill: currentColor;
    }

    .siwil-online-pulse {
        position: absolute;
        bottom: 0px;
        right: 0px;
        width: 10px;
        height: 10px;
        background: #10b981;
        border: 2px solid #ffffff;
        border-radius: 50%;
        box-shadow: 0 0 8px rgba(16, 185, 129, 0.8);
    }

    .siwil-launcher-text {
        display: flex;
        flex-direction: column;
        text-align: left;
    }

    .siwil-launcher-title {
        font-weight: 700;
        font-size: 0.92rem;
        letter-spacing: 0.3px;
        line-height: 1.2;
    }

    .siwil-launcher-sub {
        font-size: 0.72rem;
        opacity: 0.85;
        font-weight: 400;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    /* Mobile Chat Backdrop */
    .siwil-chat-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.45);
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
        z-index: 10004;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.25s ease, visibility 0.25s ease;
    }

    .siwil-chat-backdrop.active {
        opacity: 1;
        visibility: visible;
    }

    /* Floating Chat Window Modal */
    .siwil-chat-window {
        position: fixed;
        bottom: 88px;
        right: 24px;
        width: 390px;
        max-width: calc(100vw - 32px);
        height: 580px;
        max-height: calc(100vh - 110px);
        background: var(--bg-card, #ffffff);
        border: 1px solid var(--border-glass, rgba(0, 0, 0, 0.08));
        border-radius: 20px;
        box-shadow: 0 20px 45px -10px rgba(0, 0, 0, 0.22), 0 0 0 1px rgba(255, 255, 255, 0.15);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        z-index: 10005;
        opacity: 0;
        visibility: hidden;
        transform: translateY(20px) scale(0.95);
        transform-origin: bottom right;
        transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        font-family: 'Outfit', sans-serif;
    }

    .siwil-chat-window.active {
        opacity: 1;
        visibility: visible;
        transform: translateY(0) scale(1);
    }

    /* Window Header */
    .siwil-chat-header {
        background: var(--accent-gradient, linear-gradient(135deg, #2563eb, #3b82f6));
        color: #ffffff;
        padding: 0.9rem 1.15rem;
        display: flex;
        flex-direction: column;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        position: relative;
        flex-shrink: 0;
    }

    .siwil-mobile-handle {
        display: none;
    }

    .siwil-header-content {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
    }

    .siwil-header-agent {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .siwil-agent-avatar {
        width: 38px;
        height: 38px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        backdrop-filter: blur(8px);
        box-shadow: inset 0 1px 1px rgba(255, 255, 255, 0.3);
        flex-shrink: 0;
    }

    .siwil-agent-avatar svg {
        width: 20px;
        height: 20px;
        fill: #ffffff;
    }

    .siwil-agent-status {
        position: absolute;
        bottom: -2px;
        right: -2px;
        width: 10px;
        height: 10px;
        background: #10b981;
        border: 2px solid #2563eb;
        border-radius: 50%;
    }

    .siwil-agent-info h4 {
        margin: 0;
        font-size: 0.95rem;
        font-weight: 700;
        color: #ffffff;
        line-height: 1.2;
    }

    .siwil-agent-info p {
        margin: 0;
        font-size: 0.74rem;
        color: rgba(255, 255, 255, 0.88);
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .siwil-agent-live-dot {
        color: #34d399;
        font-size: 0.65rem;
    }

    .siwil-header-actions {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .siwil-header-btn {
        background: rgba(255, 255, 255, 0.15);
        border: none;
        color: #ffffff;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
    }

    .siwil-header-btn:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: scale(1.05);
    }

    .siwil-header-btn svg {
        width: 16px;
        height: 16px;
        fill: currentColor;
    }

    /* Chat Messages Body */
    .siwil-chat-body {
        flex: 1;
        padding: 1.15rem;
        overflow-y: auto;
        background: var(--bg-body, #f8fafc);
        display: flex;
        flex-direction: column;
        gap: 0.9rem;
        font-size: 0.88rem;
    }

    .siwil-chat-body::-webkit-scrollbar {
        width: 5px;
    }

    .siwil-chat-body::-webkit-scrollbar-thumb {
        background: rgba(0, 0, 0, 0.12);
        border-radius: 4px;
    }

    .siwil-msg {
        max-width: 88%;
        padding: 0.85rem 1rem;
        border-radius: 14px;
        line-height: 1.55;
        position: relative;
        word-break: break-word;
    }

    .siwil-msg-ai {
        align-self: flex-start;
        background: var(--bg-card, #ffffff);
        color: var(--text-primary, #0f172a);
        border: 1px solid var(--border-glass, rgba(0, 0, 0, 0.08));
        border-bottom-left-radius: 4px;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.04);
        line-height: 1.6;
    }

    .siwil-msg-ai strong {
        color: var(--text-primary, #0f172a);
        font-weight: 700;
    }

    .siwil-chat-note {
        display: block;
        margin-top: 8px;
        font-size: 0.78rem;
        color: var(--text-secondary, #64748b);
        font-style: italic;
        border-left: 2px solid var(--accent-1, #2563eb);
        padding-left: 8px;
        line-height: 1.45;
    }

    .siwil-chat-list-item {
        display: flex;
        align-items: flex-start;
        gap: 6px;
        margin: 5px 0;
    }

    .siwil-chat-bullet {
        color: var(--accent-1, #2563eb);
        font-weight: 700;
        flex-shrink: 0;
        margin-top: 1px;
    }

    .siwil-chat-item-content {
        flex: 1;
    }

    .siwil-chat-link {
        color: var(--accent-1, #2563eb);
        text-decoration: underline;
        font-weight: 600;
    }

    .siwil-chat-link:hover {
        color: var(--accent-2, #3b82f6);
    }

    .siwil-chat-code {
        background: rgba(37, 99, 235, 0.1);
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 0.85em;
        color: var(--accent-1, #2563eb);
        font-weight: 600;
        font-family: monospace;
    }

    .siwil-msg-ai-tag {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 0.72rem;
        font-weight: 700;
        color: var(--accent-1, #2563eb);
        margin-bottom: 6px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .siwil-msg-ai-tag svg {
        width: 14px;
        height: 14px;
        fill: currentColor;
    }

    .siwil-msg-user {
        align-self: flex-end;
        background: var(--accent-gradient, linear-gradient(135deg, #2563eb, #3b82f6));
        color: #ffffff;
        border-bottom-right-radius: 4px;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
    }

    /* Option Action Buttons inside Chat */
    .siwil-options-list {
        display: flex;
        flex-direction: column;
        gap: 6px;
        margin-top: 10px;
    }

    .siwil-option-btn {
        background: var(--bg-body, #f8fafc);
        color: var(--text-primary, #0f172a);
        border: 1px solid var(--border-glass, rgba(0, 0, 0, 0.08));
        border-radius: 10px;
        padding: 8px 12px;
        font-size: 0.83rem;
        font-family: inherit;
        font-weight: 500;
        text-align: left;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        width: 100%;
    }

    .siwil-option-icon {
        width: 26px;
        height: 26px;
        border-radius: 7px;
        background: rgba(37, 99, 235, 0.08);
        color: var(--accent-1, #2563eb);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .siwil-option-icon svg {
        width: 15px;
        height: 15px;
        fill: currentColor;
    }

    .siwil-option-label {
        flex: 1;
        line-height: 1.35;
    }

    .siwil-option-arrow {
        width: 16px;
        height: 16px;
        fill: var(--text-muted, #94a3b8);
        opacity: 0.6;
        transition: transform 0.2s, opacity 0.2s;
        flex-shrink: 0;
    }

    .siwil-option-btn:hover {
        background: rgba(37, 99, 235, 0.06);
        border-color: rgba(37, 99, 235, 0.3);
        color: var(--accent-1, #2563eb);
        transform: translateX(2px);
    }

    .siwil-option-btn:hover .siwil-option-arrow {
        opacity: 1;
        fill: var(--accent-1, #2563eb);
        transform: translateX(2px);
    }

    .siwil-option-btn:active {
        transform: scale(0.99);
    }

    /* Typing Indicator */
    .siwil-chat-typing {
        display: none;
        align-self: flex-start;
        background: var(--bg-card, #ffffff);
        border: 1px solid var(--border-glass, rgba(0, 0, 0, 0.08));
        border-radius: 14px;
        border-bottom-left-radius: 4px;
        padding: 10px 14px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
    }

    .siwil-dots {
        display: flex;
        gap: 4px;
        align-items: center;
    }

    .siwil-dots span {
        width: 6px;
        height: 6px;
        background: var(--text-muted, #a1a1aa);
        border-radius: 50%;
        animation: siwilBounce 1.4s infinite ease-in-out both;
    }

    .siwil-dots span:nth-child(1) { animation-delay: -0.32s; }
    .siwil-dots span:nth-child(2) { animation-delay: -0.16s; }

    @keyframes siwilBounce {
        0%, 80%, 100% { transform: scale(0); opacity: 0.3; }
        40% { transform: scale(1); opacity: 1; }
    }

    /* Quick Action Chips Footer */
    .siwil-quick-chips {
        padding: 8px 12px;
        background: var(--bg-card, #ffffff);
        border-top: 1px solid var(--border-glass, rgba(0, 0, 0, 0.06));
        display: flex;
        gap: 6px;
        overflow-x: auto;
        white-space: nowrap;
        scrollbar-width: none;
        -webkit-overflow-scrolling: touch;
        flex-shrink: 0;
        mask-image: linear-gradient(to right, black 85%, transparent 100%);
        -webkit-mask-image: linear-gradient(to right, black 88%, transparent 100%);
    }

    .siwil-quick-chips::-webkit-scrollbar {
        display: none;
        height: 0px;
    }

    .siwil-chip {
        background: var(--bg-body, #f1f5f9);
        color: var(--text-secondary, #334155);
        border: 1px solid var(--border-glass, rgba(0,0,0,0.06));
        border-radius: 999px;
        padding: 5px 12px;
        font-size: 0.74rem;
        font-weight: 500;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        flex-shrink: 0;
        transition: all 0.2s;
        user-select: none;
    }

    .siwil-chip:hover {
        background: rgba(37, 99, 235, 0.1);
        color: var(--accent-1, #2563eb);
        border-color: rgba(37, 99, 235, 0.25);
        transform: translateY(-1px);
    }

    .siwil-chip:active {
        transform: scale(0.96);
    }

    /* Chat Input Form */
    .siwil-chat-input-box {
        padding: 0.75rem 1rem;
        background: var(--bg-card, #ffffff);
        border-top: 1px solid var(--border-glass, rgba(0, 0, 0, 0.08));
        display: flex;
        gap: 8px;
        align-items: center;
        flex-shrink: 0;
    }

    .siwil-chat-input {
        flex: 1;
        padding: 9px 16px;
        border: 1px solid var(--border-glass, rgba(0, 0, 0, 0.12));
        border-radius: 999px;
        outline: none;
        font-size: 0.88rem;
        background: var(--bg-body, #f8fafc);
        color: var(--text-primary, #0f172a);
        font-family: inherit;
        transition: all 0.2s;
    }

    .siwil-chat-input:focus {
        border-color: var(--accent-1, #2563eb);
        background: var(--bg-card, #ffffff);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
    }

    .siwil-chat-submit {
        background: var(--accent-gradient, linear-gradient(135deg, #2563eb, #3b82f6));
        color: #ffffff;
        border: none;
        width: 38px;
        height: 38px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
        box-shadow: 0 3px 10px rgba(37, 99, 235, 0.3);
        flex-shrink: 0;
    }

    .siwil-chat-submit:hover {
        transform: scale(1.08);
        filter: brightness(110%);
    }

    .siwil-chat-submit:active {
        transform: scale(0.95);
    }

    .siwil-chat-submit:disabled {
        background: var(--border-glass, #d4d4d8);
        color: var(--text-muted, #a1a1aa);
        cursor: not-allowed;
        box-shadow: none;
        transform: none;
    }

    .siwil-chat-submit svg {
        width: 16px;
        height: 16px;
        fill: currentColor;
    }

    /* Dark Mode Overrides */
    [data-theme="dark"] .siwil-chat-window {
        background: var(--bg-card, #151e32);
        border-color: var(--border-glass, rgba(255, 255, 255, 0.1));
    }
    [data-theme="dark"] .siwil-chat-body {
        background: var(--bg-body, #0b1120);
    }
    [data-theme="dark"] .siwil-msg-ai {
        background: #151e32;
        color: #f8fafc;
        border-color: rgba(255, 255, 255, 0.08);
    }
    [data-theme="dark"] .siwil-msg-ai strong {
        color: #f8fafc;
    }
    [data-theme="dark"] .siwil-option-btn {
        background: #0f172a;
        color: #f1f5f9;
        border-color: rgba(255, 255, 255, 0.08);
    }
    [data-theme="dark"] .siwil-option-btn:hover {
        background: rgba(59, 130, 246, 0.15);
        border-color: rgba(59, 130, 246, 0.4);
        color: #93c5fd;
    }
    [data-theme="dark"] .siwil-option-icon {
        background: rgba(59, 130, 246, 0.15);
        color: #60a5fa;
    }
    [data-theme="dark"] .siwil-quick-chips {
        background: #151e32;
        border-color: rgba(255, 255, 255, 0.08);
    }
    [data-theme="dark"] .siwil-chip {
        background: #0f172a;
        color: #cbd5e1;
        border-color: rgba(255, 255, 255, 0.08);
    }
    [data-theme="dark"] .siwil-chip:hover {
        background: rgba(59, 130, 246, 0.2);
        color: #93c5fd;
        border-color: rgba(59, 130, 246, 0.4);
    }
    [data-theme="dark"] .siwil-chat-input-box {
        background: #151e32;
        border-color: rgba(255, 255, 255, 0.08);
    }
    [data-theme="dark"] .siwil-chat-input {
        background: #0f172a;
        color: #f8fafc;
        border-color: rgba(255, 255, 255, 0.12);
    }
    [data-theme="dark"] .siwil-chat-input:focus {
        background: #151e32;
        border-color: #60a5fa;
    }

    /* Mobile adjustments: Bottom sheet drawer (tablet & phone <= 768px) */
    @media (max-width: 768px) {
        .siwil-floating-widget {
            bottom: 16px;
            right: 16px;
            z-index: 9999;
        }

        .siwil-chat-launcher {
            padding: 8px;
            width: 48px;
            height: 48px;
            border-radius: 50%;
            justify-content: center;
            box-shadow: 0 4px 16px rgba(37, 99, 235, 0.45);
        }

        .siwil-launcher-icon-box {
            width: 24px;
            height: 24px;
            background: transparent;
        }

        .siwil-launcher-text {
            display: none;
        }

        .siwil-online-pulse {
            width: 8px;
            height: 8px;
            bottom: -1px;
            right: -1px;
        }

        /* Hide launcher button cleanly when chat is open on mobile */
        body.siwil-chat-open #siwilLauncherBtn {
            opacity: 0 !important;
            pointer-events: none !important;
            transform: scale(0.6) !important;
        }

        /* Mobile Bottom Sheet */
        .siwil-chat-window {
            position: fixed !important;
            inset: auto 0 0 0 !important;
            width: 100% !important;
            max-width: 100vw !important;
            height: 84vh !important;
            max-height: 88dvh !important;
            border-radius: 24px 24px 0 0 !important;
            border: none !important;
            border-top: 1px solid var(--border-glass, rgba(255, 255, 255, 0.2)) !important;
            box-shadow: 0 -12px 40px rgba(0, 0, 0, 0.3) !important;
            z-index: 10005 !important;
            transform: translateY(100%) !important;
            transform-origin: bottom center !important;
            transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.25s ease !important;
        }

        .siwil-chat-window.active {
            transform: translateY(0) !important;
            opacity: 1 !important;
            visibility: visible !important;
        }

        .siwil-chat-header {
            padding: 0.75rem 1rem 0.85rem 1rem !important;
        }

        .siwil-mobile-handle {
            display: block !important;
            width: 38px !important;
            height: 4px !important;
            background: rgba(255, 255, 255, 0.5) !important;
            border-radius: 99px !important;
            margin: 0 auto 8px auto !important;
            cursor: pointer;
        }

        .siwil-chat-body {
            padding: 1rem 0.85rem !important;
            gap: 0.85rem !important;
        }

        .siwil-msg {
            max-width: 92% !important;
            padding: 0.75rem 0.85rem !important;
            font-size: 0.86rem !important;
        }

        .siwil-quick-chips {
            padding: 6px 10px !important;
            gap: 6px !important;
        }

        .siwil-chat-input-box {
            padding: 0.65rem 0.85rem !important;
            padding-bottom: max(0.85rem, env(safe-area-inset-bottom, 16px)) !important;
        }
    }
</style>

<!-- Mobile Chat Backdrop -->
<div id="siwilChatBackdrop" class="siwil-chat-backdrop" onclick="closeFloatingChat()"></div>

<!-- Floating Launcher Button -->
<div class="siwil-floating-widget" id="siwilFloatingWidget">
    <button type="button" class="siwil-chat-launcher" id="siwilLauncherBtn" onclick="toggleFloatingChat()" title="Buka Layanan Asisten SIWIL AI">
        <div class="siwil-launcher-icon-box">
            <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v6h-2zm0 8h2v2h-2z"/></svg>
            <span class="siwil-online-pulse"></span>
        </div>
        <div class="siwil-launcher-text">
            <span class="siwil-launcher-title">Bantuan SIWIL AI</span>
            <span class="siwil-launcher-sub">CS Virtual • Online</span>
        </div>
    </button>
</div>

<!-- Floating Chat Window -->
<div class="siwil-chat-window" id="siwilChatWindow">
    <!-- Header -->
    <div class="siwil-chat-header">
        <div class="siwil-mobile-handle" onclick="closeFloatingChat()"></div>
        <div class="siwil-header-content">
            <div class="siwil-header-agent">
                <div class="siwil-agent-avatar">
                    <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v6h-2zm0 8h2v2h-2z"/></svg>
                    <span class="siwil-agent-status"></span>
                </div>
                <div class="siwil-agent-info">
                    <h4>SIWIL Assistant</h4>
                    <p>
                        <span class="siwil-agent-live-dot">●</span> 
                        Layanan Mandiri {{ ucwords(str_replace('_', ' ', strtolower(Auth::user()->role === 'WARGA' ? 'Warga' : Auth::user()->role))) }}
                    </p>
                </div>
            </div>
            <div class="siwil-header-actions">
                <button type="button" class="siwil-header-btn" onclick="resetFloatingChat()" title="Reset Percakapan">
                    <svg viewBox="0 0 24 24"><path d="M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z"/></svg>
                </button>
                <button type="button" class="siwil-header-btn" onclick="closeFloatingChat()" title="Tutup Chat">
                    <svg viewBox="0 0 24 24"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Messages Box -->
    <div class="siwil-chat-body" id="siwilChatBody">
        <div class="siwil-chat-typing" id="siwilChatTyping">
            <div class="siwil-dots">
                <span></span><span></span><span></span>
            </div>
        </div>
    </div>

    <!-- Quick Prompt Chips -->
    <div class="siwil-quick-chips">
        @php $currentRole = Auth::user()->role; @endphp
        @if($currentRole === 'SUPER_ADMIN')
            <span class="siwil-chip" onclick="handleFloatingChatSubmitManual('apa yang ada di dashboard')">📊 Dashboard</span>
            <span class="siwil-chip" onclick="quickSelectNode('db_overview')">🗄️ Database</span>
            <span class="siwil-chip" onclick="quickSelectNode('system_info')">🖥️ Sistem</span>
            <span class="siwil-chip" onclick="handleFloatingChatSubmitManual('siapa saja warganya')">👥 Daftar Warga</span>
            <span class="siwil-chip" onclick="handleFloatingChatSubmitManual('siapa nama rt nya')">🏛️ Pengurus RT</span>
        @elseif(in_array($currentRole, ['ADMIN_DESA', 'DESA']))
            <span class="siwil-chip" onclick="handleFloatingChatSubmitManual('apa yang ada di dashboard')">📊 Dashboard Desa</span>
            <span class="siwil-chip" onclick="handleFloatingChatSubmitManual('siapa saja warganya')">👥 Warga Desa</span>
            <span class="siwil-chip" onclick="handleFloatingChatSubmitManual('surat masuk')">📄 Persuratan</span>
            <span class="siwil-chip" onclick="handleFloatingChatSubmitManual('pengaduan warga')">📢 Pengaduan</span>
            <span class="siwil-chip" onclick="handleFloatingChatSubmitManual('apbdes')">💰 APBDes</span>
        @elseif($currentRole === 'RW')
            <span class="siwil-chip" onclick="handleFloatingChatSubmitManual('apa yang ada di dashboard')">📊 Dashboard RW</span>
            <span class="siwil-chip" onclick="handleFloatingChatSubmitManual('siapa saja warganya')">👥 Warga RW</span>
            <span class="siwil-chip" onclick="handleFloatingChatSubmitManual('verifikasi surat')">📄 Surat Masuk</span>
            <span class="siwil-chip" onclick="handleFloatingChatSubmitManual('pengaduan warga')">📢 Pengaduan</span>
            <span class="siwil-chip" onclick="handleFloatingChatSubmitManual('daftar ketua rt')">🏛️ Kontak RT</span>
        @elseif($currentRole === 'RT')
            <span class="siwil-chip" onclick="handleFloatingChatSubmitManual('apa yang ada di dashboard')">📊 Dashboard RT</span>
            <span class="siwil-chip" onclick="handleFloatingChatSubmitManual('siapa saja warganya')">👥 Warga RT</span>
            <span class="siwil-chip" onclick="handleFloatingChatSubmitManual('verifikasi surat')">📄 Surat Masuk</span>
            <span class="siwil-chip" onclick="handleFloatingChatSubmitManual('pengaduan warga')">📢 Pengaduan RT</span>
            <span class="siwil-chip" onclick="handleFloatingChatSubmitManual('siapa ketua rw')">📞 Kontak RW</span>
        @else
            <span class="siwil-chip" onclick="handleFloatingChatSubmitManual('apa yang ada di dashboard')">📊 Dashboard</span>
            <span class="siwil-chip" onclick="quickSelectNode('info_kontak')">📞 Kontak Pengurus</span>
            <span class="siwil-chip" onclick="quickSelectNode('surat_pilih_jenis')">📄 Buat Surat</span>
            <span class="siwil-chip" onclick="quickSelectNode('lapor_mulai')">📢 Lapor Masalah</span>
            <span class="siwil-chip" onclick="quickSelectNode('cek_status_pilih')">🔍 Cek Status</span>
        @endif
    </div>

    <!-- Chat Input Form -->
    <form class="siwil-chat-input-box" id="siwilChatForm" onsubmit="handleFloatingChatSubmit(event)">
        <input type="text" id="siwilChatInput" class="siwil-chat-input" placeholder="Tanya sesuatu ke AI SIWIL..." autocomplete="off" required>
        <button type="submit" id="siwilChatBtn" class="siwil-chat-submit" title="Kirim">
            <svg viewBox="0 0 24 24"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
        </button>
    </form>
</div>

<script>
(function() {
    let chatOpen = false;
    let chatLoaded = false;
    let currentInputMode = null;

    const chatWindow = document.getElementById('siwilChatWindow');
    const chatBackdrop = document.getElementById('siwilChatBackdrop');
    const chatBody = document.getElementById('siwilChatBody');
    const chatTyping = document.getElementById('siwilChatTyping');
    const chatInput = document.getElementById('siwilChatInput');
    const chatBtn = document.getElementById('siwilChatBtn');

    window.openFloatingChat = function() {
        chatOpen = true;
        chatWindow.classList.add('active');
        if (chatBackdrop) chatBackdrop.classList.add('active');
        document.body.classList.add('siwil-chat-open');
        if (!chatLoaded) {
            chatLoaded = true;
            fetchFloatingNode('start');
        }
        setTimeout(() => chatInput.focus(), 250);
    };

    window.closeFloatingChat = function() {
        chatOpen = false;
        chatWindow.classList.remove('active');
        if (chatBackdrop) chatBackdrop.classList.remove('active');
        document.body.classList.remove('siwil-chat-open');
    };

    window.toggleFloatingChat = function() {
        if (chatOpen) {
            window.closeFloatingChat();
        } else {
            window.openFloatingChat();
        }
    };

    window.resetFloatingChat = function() {
        chatBody.innerHTML = '';
        chatBody.appendChild(chatTyping);
        currentInputMode = null;
        chatInput.placeholder = 'Tanya sesuatu ke AI SIWIL...';
        fetchFloatingNode('start');
    };

    window.handleFloatingChatSubmitManual = function(promptText) {
        if (!chatOpen) {
            window.openFloatingChat();
        }
        chatInput.value = promptText;
        const fakeEvent = { preventDefault: () => {} };
        window.handleFloatingChatSubmit(fakeEvent);
    };

    window.quickSelectNode = function(nodeId) {
        if (!chatOpen) {
            window.openFloatingChat();
        }
        // Remove previous options
        const allOptionContainers = chatBody.querySelectorAll('.siwil-options-list');
        if (allOptionContainers.length > 0) {
            allOptionContainers[allOptionContainers.length - 1].remove();
        }
        fetchFloatingNode(nodeId);
    };

    function formatFloatingMarkdown(text) {
        if (!text) return '';
        let escaped = text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');

        escaped = escaped.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
        escaped = escaped.replace(/`([^`]+)`/g, '<code class="siwil-chat-code">$1</code>');

        const rawLines = escaped.split('\n');
        const formatted = [];

        for (let i = 0; i < rawLines.length; i++) {
            let line = rawLines[i].trim();
            if (!line) {
                formatted.push('<div style="height: 6px;"></div>');
                continue;
            }

            // Italic note lines starting and ending with _
            if (line.startsWith('_') && line.endsWith('_')) {
                const noteText = line.substring(1, line.length - 1);
                formatted.push(`<span class="siwil-chat-note">${noteText}</span>`);
                continue;
            }

            // List item bullet: •, -, *, or 1., 2.
            const listMatch = line.match(/^([•\-\*]|\d+\.)\s+(.+)$/);
            if (listMatch) {
                const bullet = listMatch[1];
                let content = listMatch[2];
                content = content.replace(/(?<!\w)_(.+?)_(?!\w)/g, '<em>$1</em>');
                content = content.replace(/(08[0-9]{8,12})/g, '<a href="https://wa.me/62$1" target="_blank" class="siwil-chat-link" title="Buka WhatsApp">$1</a>');
                content = content.replace(/([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/g, '<a href="mailto:$1" class="siwil-chat-link">$1</a>');
                formatted.push(`<div class="siwil-chat-list-item"><span class="siwil-chat-bullet">${bullet}</span><span class="siwil-chat-item-content">${content}</span></div>`);
                continue;
            }

            // Normal text line
            line = line.replace(/(?<!\w)_(.+?)_(?!\w)/g, '<em>$1</em>');
            line = line.replace(/([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/g, '<a href="mailto:$1" class="siwil-chat-link">$1</a>');
            formatted.push(`<div>${line}</div>`);
        }

        return formatted.join('');
    }

    function getFloatingSvgIcon(iconName) {
        const icons = {
            'user': '<svg viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>',
            'document': '<svg viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>',
            'alert': '<svg viewBox="0 0 24 24"><path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/></svg>',
            'search': '<svg viewBox="0 0 24 24"><path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>',
            'phone': '<svg viewBox="0 0 24 24"><path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/></svg>',
            'file': '<svg viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>',
            'check': '<svg viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>',
            'close': '<svg viewBox="0 0 24 24"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>',
            'back': '<svg viewBox="0 0 24 24"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg>',
            'home': '<svg viewBox="0 0 24 24"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>',
            'refresh': '<svg viewBox="0 0 24 24"><path d="M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z"/></svg>',
            'vote': '<svg viewBox="0 0 24 24"><path d="M18 13h-.68l-2 2h1.91L19 17H5l1.78-2h2.05l-2-2H6l-3 3v2c0 1.1.89 2 1.99 2H19c1.1 0 2-.9 2-2v-2l-3-3zm-1-5.05l-4.95 4.95-3.54-3.54 4.95-4.95L16.99 7.95zm-4.24-5.66L6.39 8.66a.996.996 0 000 1.41l4.95 4.95c.39.39 1.02.39 1.41 0l6.36-6.36a.996.996 0 000-1.41l-4.95-4.95a.996.996 0 00-1.41 0z"/></svg>',
            'server': '<svg viewBox="0 0 24 24"><path d="M2 20h20v-4H2v4zm2-3h2v2H4v-2zM2 4v4h20V4H2zm4 3H4V5h2v2zm-4 7h20v-4H2v4zm2-3h2v2H4v-2z"/></svg>',
            'database': '<svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 3.79 2 6v12c0 2.21 4.48 4 10 4s10-1.79 10-4V6c0-2.21-4.48-4-10-4zm0 2c4.42 0 8 1.34 8 2s-3.58 2-8 2-8-1.34-8-2 3.58-2 8-2zm8 14c0 .66-3.58 2-8 2s-8-1.34-8-2v-2.17c2.14 1.31 5.38 2.17 8 2.17s5.86-.86 8-2.17V18zm0-4c0 .66-3.58 2-8 2s-8-1.34-8-2v-2.17c2.14 1.31 5.38 2.17 8 2.17s5.86-.86 8-2.17V14z"/></svg>',
            'default': '<svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>'
        };
        return icons[iconName] || icons['default'];
    }

    function appendFloatingBotMessage(text, options, inputMode, inputTarget, inputData) {
        const wrapper = document.createElement('div');
        wrapper.className = 'siwil-msg siwil-msg-ai';

        let html = `
            <div class="siwil-msg-ai-tag">
                <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v6h-2zm0 8h2v2h-2z"/></svg>
                SIWIL AI
            </div>
            <div>${formatFloatingMarkdown(text)}</div>
        `;

        if (options && options.length > 0) {
            let optionsHtml = '<div class="siwil-options-list">';
            options.forEach(opt => {
                const dataStr = opt.data ? JSON.stringify(opt.data).replace(/"/g, '&quot;') : '{}';
                const iconSvg = getFloatingSvgIcon(opt.icon);
                optionsHtml += `<button type="button" class="siwil-option-btn" onclick="selectFloatingOption('${opt.label.replace(/'/g, "\\'").replace(/"/g, '&quot;')}', '${opt.next_node}', ${dataStr})"><div class="siwil-option-icon">${iconSvg}</div><span class="siwil-option-label">${opt.label}</span><svg class="siwil-option-arrow" viewBox="0 0 24 24"><path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z"/></svg></button>`;
            });
            optionsHtml += '</div>';
            html += optionsHtml;
        }

        wrapper.innerHTML = html;
        chatBody.insertBefore(wrapper, chatTyping);
        chatBody.scrollTop = chatBody.scrollHeight;

        if (inputMode === 'text' && inputTarget) {
            currentInputMode = {
                target: inputTarget,
                data: inputData || {}
            };
            chatInput.placeholder = 'Ketik jawaban Anda di sini...';
            chatInput.focus();
        } else {
            currentInputMode = null;
            chatInput.placeholder = 'Tanya sesuatu ke AI SIWIL...';
        }
    }

    function appendFloatingUserMessage(text) {
        const wrapper = document.createElement('div');
        wrapper.className = 'siwil-msg siwil-msg-user';
        wrapper.innerText = text;
        chatBody.insertBefore(wrapper, chatTyping);
        chatBody.scrollTop = chatBody.scrollHeight;
    }

    async function fetchFloatingNode(nodeId, data = {}) {
        chatTyping.style.display = 'block';
        chatBody.scrollTop = chatBody.scrollHeight;

        try {
            const response = await fetch('{{ route("ai.chat") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ node: nodeId, data: data })
            });

            const result = await response.json();
            chatTyping.style.display = 'none';

            if (response.ok) {
                appendFloatingBotMessage(result.message, result.options, result.input_mode, result.input_target, result.input_data);
            } else {
                appendFloatingBotMessage('Terjadi kesalahan: ' + (result.message || 'Server error'), [
                    { label: 'Kembali ke Menu Awal', next_node: 'start' }
                ]);
            }
        } catch (error) {
            chatTyping.style.display = 'none';
            appendFloatingBotMessage('Koneksi terputus. Silakan coba lagi.', [
                { label: 'Kembali ke Menu Awal', next_node: 'start' }
            ]);
        }
    }

    window.selectFloatingOption = function(label, nextNode, data) {
        const allOptionContainers = chatBody.querySelectorAll('.siwil-options-list');
        if (allOptionContainers.length > 0) {
            allOptionContainers[allOptionContainers.length - 1].remove();
        }

        appendFloatingUserMessage(label);
        fetchFloatingNode(nextNode, data);
    };

    window.handleFloatingChatSubmit = async function(e) {
        e.preventDefault();
        const message = chatInput.value.trim();
        if (!message) return;

        // Remove previous options
        const allOptionContainers = chatBody.querySelectorAll('.siwil-options-list');
        if (allOptionContainers.length > 0) {
            allOptionContainers[allOptionContainers.length - 1].remove();
        }

        appendFloatingUserMessage(message);
        chatInput.value = '';
        chatBtn.disabled = true;
        chatTyping.style.display = 'block';
        chatBody.scrollTop = chatBody.scrollHeight;

        if (currentInputMode) {
            const targetNode = currentInputMode.target;
            const targetData = { ...currentInputMode.data, input_text: message };
            currentInputMode = null;

            try {
                const response = await fetch('{{ route("ai.chat") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ node: targetNode, data: targetData })
                });

                const result = await response.json();
                chatTyping.style.display = 'none';
                chatBtn.disabled = false;

                if (response.ok) {
                    appendFloatingBotMessage(result.message, result.options, result.input_mode, result.input_target, result.input_data);
                } else {
                    appendFloatingBotMessage('Terjadi kendala memproses jawaban.', [{ label: 'Kembali ke Menu Awal', next_node: 'start' }]);
                }
            } catch (err) {
                chatTyping.style.display = 'none';
                chatBtn.disabled = false;
                appendFloatingBotMessage('Koneksi terputus.', [{ label: 'Kembali ke Menu Awal', next_node: 'start' }]);
            }
            return;
        }

        // Regular AI chat message
        try {
            const response = await fetch('{{ route("ai.chat") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ message: message })
            });

            const result = await response.json();
            chatTyping.style.display = 'none';
            chatBtn.disabled = false;

            if (response.ok) {
                if (result.error) {
                    appendFloatingBotMessage('⚠️ ' + result.error, [{ label: 'Kembali ke Menu Awal', next_node: 'start' }]);
                } else {
                    appendFloatingBotMessage(result.message, result.options, result.input_mode, result.input_target, result.input_data);
                }
            } else {
                appendFloatingBotMessage('Maaf, sistem AI sedang sibuk. Silakan gunakan menu opsi di bawah:', [{ label: 'Kembali ke Menu Awal', next_node: 'start' }]);
            }
        } catch (err) {
            chatTyping.style.display = 'none';
            chatBtn.disabled = false;
            appendFloatingBotMessage('Koneksi terputus. Silakan coba lagi.', [{ label: 'Kembali ke Menu Awal', next_node: 'start' }]);
        }
    };

    // Auto open if url param has ?open_chat=1
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('open_chat') === '1') {
            window.openFloatingChat();
        }
    });
})();
</script>
