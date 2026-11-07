@extends('layouts.app')

@section('title', 'Pusat Konfigurasi Sistem - SIWIL AI')
@section('page-title', 'Pusat Konfigurasi Sistem')

@section('content')
<div class="config-container">

    {{-- Header Section --}}
    <div class="config-header">
        <div>
            <div class="config-title-wrap">
                <div class="config-icon-badge">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 22px; height: 22px;">
                        <path d="M4 21v-7"/><path d="M4 10V3"/><path d="M12 21v-9"/><path d="M12 8V3"/><path d="M20 21v-5"/><path d="M20 12V3"/>
                        <path d="M1 14h6"/><path d="M9 8h6"/><path d="M17 16h6"/>
                    </svg>
                </div>
                <h1 class="page-title config-main-title">
                    Pusat Konfigurasi Sistem
                </h1>
                <span class="badge badge-blue config-role-badge">
                    Super Admin
                </span>
            </div>
            <p class="config-subtitle">
                Kelola integrasi sentral Sistem Informasi Wilayah (SIWIL): Engine Kecerdasan Buatan (AI) dengan failover multi-key, gateway WhatsApp, bot Telegram, dan pengaturan SMTP Email.
            </p>
        </div>

        <div class="config-header-actions">
            <a href="{{ route('ai-knowledge.index') }}" class="btn btn-outline config-kb-btn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px;"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1-2.5-2.5Z"/><path d="M6 6h10"/><path d="M6 10h10"/></svg>
                <span>Kelola Knowledge Base</span>
                @if(isset($knowledgeCount) && $knowledgeCount > 0)
                    <span class="badge badge-blue" style="padding: 0.15rem 0.45rem; font-size: 0.72rem;">{{ $knowledgeCount }}</span>
                @endif
            </a>
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.75rem; word-break: break-word;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 20px; height: 20px; flex-shrink: 0;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger" style="margin-bottom: 1.25rem; word-break: break-word;">
            <div style="font-weight: 600; margin-bottom: 0.35rem;">Terjadi kesalahan saat menyimpan konfigurasi:</div>
            <ul style="margin: 0; padding-left: 1.25rem;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Tab Navigation Bar --}}
    <div class="config-tabs-nav">
        <button type="button" class="config-tab-btn active" onclick="switchConfigTab('tab-ai', this)">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 17px; height: 17px; flex-shrink: 0;"><path d="M12 2v4"/><path d="m4.93 4.93 2.83 2.83"/><path d="M2 12h4"/><path d="m4.93 19.07 2.83-2.83"/><path d="M12 22v-4"/><path d="m19.07 19.07-2.83-2.83"/><path d="M22 12h-4"/><path d="m19.07 4.93-2.83 2.83"/></svg>
            <span>AI Engine & API Keys</span>
        </button>
        <button type="button" class="config-tab-btn" onclick="switchConfigTab('tab-whatsapp', this)">
            <svg viewBox="0 0 24 24" fill="currentColor" style="width: 17px; height: 17px; flex-shrink: 0;"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91C2.13 13.66 2.59 15.36 3.45 16.86L2.05 22L7.3 20.62C8.75 21.41 10.38 21.83 12.04 21.83C17.5 21.83 21.95 17.38 21.95 11.92C21.95 9.27 20.92 6.78 19.05 4.91C17.18 3.03 14.69 2 12.04 2M12.05 3.67C14.25 3.67 16.31 4.53 17.87 6.09C19.42 7.65 20.28 9.72 20.28 11.92C20.28 16.46 16.58 20.15 12.04 20.15C10.56 20.15 9.11 19.76 7.85 19L7.55 18.83L4.43 19.65L5.26 16.61L5.06 16.29C4.24 15 3.8 13.47 3.8 11.91C3.81 7.37 7.5 3.67 12.05 3.67M9.06 7.04C8.88 7.04 8.58 7.11 8.33 7.38C8.08 7.65 7.37 8.32 7.37 9.68C7.37 11.04 8.36 12.35 8.5 12.53C8.64 12.71 10.43 15.48 13.18 16.67C13.84 16.95 14.34 17.11 14.74 17.24C15.4 17.45 16.01 17.42 16.49 17.35C17.03 17.27 18.14 16.68 18.37 16.03C18.6 15.38 18.6 14.83 18.53 14.71C18.46 14.59 18.28 14.53 18.01 14.39C17.74 14.25 16.42 13.6 16.17 13.51C15.92 13.42 15.74 13.38 15.56 13.65C15.38 13.92 14.86 14.53 14.7 14.71C14.54 14.89 14.38 14.91 14.11 14.78C13.84 14.64 12.97 14.36 11.94 13.44C11.14 12.72 10.6 11.84 10.44 11.57C10.28 11.3 10.42 11.15 10.56 11.02C10.68 10.9 10.83 10.71 10.97 10.55C11.11 10.39 11.15 10.28 11.24 10.1C11.33 9.92 11.29 9.76 11.22 9.63C11.15 9.5 10.62 8.2 10.4 7.67C10.19 7.15 9.97 7.22 9.8 7.21C9.65 7.2 9.47 7.04 9.06 7.04Z"/></svg>
            <span>WhatsApp Gateway</span>
        </button>
        <button type="button" class="config-tab-btn" onclick="switchConfigTab('tab-telegram', this)">
            <svg viewBox="0 0 24 24" fill="currentColor" style="width: 17px; height: 17px; flex-shrink: 0;"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 00-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.75-.55 2.92-1.27 4.86-2.11 5.83-2.51 2.78-1.16 3.35-1.36 3.73-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06.01.24 0 .38z"/></svg>
            <span>Telegram Bot</span>
        </button>
        <button type="button" class="config-tab-btn" onclick="switchConfigTab('tab-email', this)">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 17px; height: 17px; flex-shrink: 0;"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
            <span>Email (SMTP)</span>
        </button>
    </div>

    {{-- Main Form Container --}}
    <form action="{{ route('configuration.update') }}" method="POST" id="mainConfigForm">
        @csrf

        {{-- ================= TAB 1: AI ENGINE & MULTI-KEY FALLBACK ================= --}}
        <div id="tab-ai" class="config-tab-pane active">
            <div class="data-card" style="margin-bottom: 1.5rem;">
                <div class="data-card-header config-card-header">
                    <div style="display: flex; align-items: center; gap: 0.65rem;">
                        <div class="config-section-icon" style="background: rgba(37, 99, 235, 0.1); color: var(--accent-1);">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px;"><path d="M12 2v4"/><path d="m4.93 4.93 2.83 2.83"/><path d="M2 12h4"/><path d="m4.93 19.07 2.83-2.83"/><path d="M12 22v-4"/><path d="m19.07 19.07-2.83-2.83"/><path d="M22 12h-4"/><path d="m19.07 4.93-2.83 2.83"/></svg>
                        </div>
                        <div>
                            <span class="data-card-title" style="margin: 0; font-size: 1.05rem;">Engine AI & Multi-Key Fallback</span>
                            <div style="font-size: 0.78rem; color: var(--text-muted); word-break: break-word;">Konfigurasi provider, model manual, dan failover API Key otomatis</div>
                        </div>
                    </div>
                    <span class="badge badge-blue" id="currentProviderBadge" style="text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px; align-self: flex-start;">
                        Provider: {{ strtoupper(old('ai_provider', $config->ai_provider ?? 'gemini')) }}
                    </span>
                </div>

                <div class="data-card-body config-card-body">
                    {{-- Provider Selection --}}
                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label class="form-label" style="font-weight: 600; display: block; margin-bottom: 0.5rem;">
                            Penyedia Layanan AI (Provider) <span style="color: #ef4444;">*</span>
                        </label>
                        <div class="providers-grid">
                            @php
                                $currentProvider = old('ai_provider', $config->ai_provider ?? 'gemini');
                                $providers = [
                                    ['id' => 'gemini', 'name' => 'Google Gemini', 'badge' => 'Rekomendasi', 'defaultModel' => 'gemini-2.5-flash'],
                                    ['id' => 'deepseek', 'name' => 'DeepSeek', 'badge' => 'Cepat & Hemat', 'defaultModel' => 'deepseek-chat'],
                                    ['id' => 'groq', 'name' => 'Groq LPU', 'badge' => 'Ultra Fast', 'defaultModel' => 'llama-3.3-70b-versatile'],
                                    ['id' => 'xai', 'name' => 'xAI Grok', 'badge' => 'Modern', 'defaultModel' => 'grok-beta'],
                                    ['id' => 'openai', 'name' => 'OpenAI', 'badge' => 'Standar', 'defaultModel' => 'gpt-4o-mini'],
                                    ['id' => 'custom', 'name' => 'Custom API', 'badge' => 'Self-Hosted', 'defaultModel' => 'local-model'],
                                ];
                            @endphp

                            @foreach($providers as $p)
                                <label class="provider-radio-card {{ $currentProvider === $p['id'] ? 'selected' : '' }}" id="provider_card_{{ $p['id'] }}">
                                    <input type="radio" name="ai_provider" value="{{ $p['id'] }}" {{ $currentProvider === $p['id'] ? 'checked' : '' }} onchange="onProviderChanged('{{ $p['id'] }}', '{{ $p['defaultModel'] }}')" style="display: none;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                                        <span style="font-weight: 600; font-size: 0.88rem; color: var(--text-primary); word-break: break-word;">{{ $p['name'] }}</span>
                                        <span class="provider-check-icon">✓</span>
                                    </div>
                                    <span style="font-size: 0.72rem; color: var(--text-muted);">{{ $p['badge'] }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- AI Model Input: TEXT INPUT BIASA (DIKETIK MANUAL, BUKAN DROPDOWN) --}}
                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label for="ai_model" class="form-label" style="font-weight: 600; display: block; margin-bottom: 0.35rem;">
                            Nama Model AI (Ketik Manual) <span style="color: #ef4444;">*</span>
                        </label>
                        <p style="font-size: 0.78rem; color: var(--text-muted); margin-bottom: 0.5rem; line-height: 1.45; word-break: break-word;">
                            Ketik nama model secara bebas sesuai ketersediaan di akun provider Anda. Model diketik manual agar Anda leluasa memakai versi model terbaru.
                        </p>
                        <div style="position: relative; width: 100%;">
                            <input type="text" name="ai_model" id="ai_model" class="form-control" 
                                   value="{{ old('ai_model', $config->ai_model ?? 'gemini-2.5-flash') }}" 
                                   placeholder="Contoh: gemini-2.5-flash, deepseek-chat, llama-3.3-70b-versatile, gpt-4o-mini" 
                                   required 
                                   style="font-family: monospace; font-size: 0.92rem; padding: 0.65rem 0.85rem; border-radius: 8px; width: 100%; box-sizing: border-box;">
                        </div>

                        {{-- Quick Chips for Model Name Suggestions --}}
                        <div style="margin-top: 0.5rem; display: flex; align-items: center; gap: 0.35rem; flex-wrap: wrap;">
                            <span style="font-size: 0.75rem; color: var(--text-muted);">Saran cepat:</span>
                            <button type="button" class="model-chip" onclick="applyModelChip('gemini-2.5-flash')">gemini-2.5-flash</button>
                            <button type="button" class="model-chip" onclick="applyModelChip('gemini-2.0-flash')">gemini-2.0-flash</button>
                            <button type="button" class="model-chip" onclick="applyModelChip('deepseek-chat')">deepseek-chat</button>
                            <button type="button" class="model-chip" onclick="applyModelChip('llama-3.3-70b-versatile')">llama-3.3-70b-versatile</button>
                            <button type="button" class="model-chip" onclick="applyModelChip('gpt-4o-mini')">gpt-4o-mini</button>
                        </div>
                    </div>

                    {{-- Custom Base URL (Only visible for Custom provider) --}}
                    <div class="form-group" id="baseUrlGroup" style="margin-bottom: 1.5rem; display: {{ $currentProvider === 'custom' ? 'block' : 'none' }};">
                        <label for="ai_base_url" class="form-label" style="font-weight: 600; display: block; margin-bottom: 0.35rem;">
                            Custom API Base URL
                        </label>
                        <input type="url" name="ai_base_url" id="ai_base_url" class="form-control" 
                               value="{{ old('ai_base_url', $config->ai_base_url ?? '') }}" 
                               placeholder="https://api.domain-anda.com/v1" 
                               style="font-family: monospace; font-size: 0.9rem; width: 100%; box-sizing: border-box;">
                        <span style="font-size: 0.75rem; color: var(--text-muted); display: block; margin-top: 0.25rem; word-break: break-word;">
                            Gunakan bila Anda memakai gateway OpenAI-compatible, vLLM, Ollama proxy, atau router mandiri.
                        </span>
                    </div>

                    {{-- AI API Keys: DYNAMIC MULTI-KEY FALLBACK LIST --}}
                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <div class="api-keys-header">
                            <div>
                                <label class="form-label" style="font-weight: 600; margin: 0; display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                                    <span>Daftar API Key AI (Sistem Fallback Otomatis)</span>
                                    <span class="badge badge-blue" id="keysCountBadge">
                                        {{ count($maskedKeys) > 0 ? count($maskedKeys) : 1 }} Kunci
                                    </span>
                                </label>
                                <span style="font-size: 0.78rem; color: var(--text-muted); display: block; margin-top: 0.25rem; line-height: 1.45; word-break: break-word;">
                                    Jika kunci pertama terkena <strong>Rate Limit (429)</strong> atau error, sistem otomatis mencoba kunci berikutnya. Karakter disensor sebagian demi keamanan.
                                </span>
                            </div>
                            <button type="button" class="btn btn-outline btn-sm btn-add-key" onclick="addApiKeyRow()">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 15px; height: 15px;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                <span>+ Tambah API Key Cadangan</span>
                            </button>
                        </div>

                        {{-- Dynamic Keys Container --}}
                        <div id="apiKeysContainer" style="display: flex; flex-direction: column; gap: 0.75rem; width: 100%;">
                            @php
                                $displayKeys = !empty($maskedKeys) ? $maskedKeys : [''];
                            @endphp

                            @foreach($displayKeys as $idx => $keyVal)
                                <div class="api-key-row" id="apiKeyRow_{{ $idx }}">
                                    <div class="api-key-header-row">
                                        <div class="key-index-badge" style="color: {{ $idx === 0 ? 'var(--accent-1)' : 'var(--text-muted)' }};">
                                            <span class="key-label">{{ $idx === 0 ? 'Kunci Utama' : 'Fallback #' . $idx }}</span>
                                        </div>
                                        <button type="button" class="btn btn-danger btn-sm remove-key-btn" onclick="removeApiKeyRow(this)" title="Hapus API Key ini">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                                            <span class="remove-text-mobile">Hapus</span>
                                        </button>
                                    </div>
                                    <div class="api-key-input-wrapper">
                                        <input type="text" name="ai_api_keys[]" class="form-control api-key-input" 
                                               value="{{ $keyVal }}" 
                                               placeholder="{{ $idx === 0 ? 'Masukkan API Key utama Anda' : 'Masukkan API Key cadangan fallback' }}" 
                                               autocomplete="off" 
                                               style="font-family: monospace; font-size: 0.88rem; padding: 0.55rem 0.75rem; border-radius: 6px; width: 100%; box-sizing: border-box;">
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <span style="font-size: 0.74rem; color: var(--text-muted); display: block; margin-top: 0.45rem; line-height: 1.4; word-break: break-word;">
                            ℹ️ Nilai dengan simbol <code>••••</code> akan tetap mempertahankan kunci asli di server. Untuk mengganti, ketikkan kunci baru secara lengkap.
                        </span>
                    </div>

                    {{-- Connection Test Panel --}}
                    <div class="test-panel" style="background: rgba(37, 99, 235, 0.04); border: 1px dashed rgba(37, 99, 235, 0.3);">
                        <div style="flex: 1; min-width: 0;">
                            <div style="font-weight: 600; font-size: 0.88rem; color: var(--text-primary); margin-bottom: 0.15rem;">
                                Pengujian Langsung AI (Live Latency & Failover)
                            </div>
                            <div style="font-size: 0.78rem; color: var(--text-muted); line-height: 1.4; word-break: break-word;">
                                Kirim ping uji coba ke provider AI untuk memastikan model dan API Key merespons dengan benar.
                            </div>
                        </div>
                        <div class="test-panel-action">
                            <div id="aiTestResult" class="test-result-text"></div>
                            <button type="button" id="btnTestAi" class="btn btn-outline btn-sm test-panel-btn" onclick="runAiTest()">
                                <svg id="aiTestSpinner" style="display: none; animation: spin 1s linear infinite; width: 14px; height: 14px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                                <span>⚡ Uji Koneksi AI</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================= TAB 2: WHATSAPP GATEWAY ================= --}}
        <div id="tab-whatsapp" class="config-tab-pane" style="display: none;">
            <div class="data-card" style="margin-bottom: 1.5rem;">
                <div class="data-card-header config-card-header">
                    <div style="display: flex; align-items: center; gap: 0.65rem;">
                        <div class="config-section-icon" style="background: rgba(37, 211, 102, 0.12); color: #10b981;">
                            <svg viewBox="0 0 24 24" fill="currentColor" style="width: 18px; height: 18px;"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91C2.13 13.66 2.59 15.36 3.45 16.86L2.05 22L7.3 20.62C8.75 21.41 10.38 21.83 12.04 21.83C17.5 21.83 21.95 17.38 21.95 11.92C21.95 9.27 20.92 6.78 19.05 4.91C17.18 3.03 14.69 2 12.04 2M12.05 3.67C14.25 3.67 16.31 4.53 17.87 6.09C19.42 7.65 20.28 9.72 20.28 11.92C20.28 16.46 16.58 20.15 12.04 20.15C10.56 20.15 9.11 19.76 7.85 19L7.55 18.83L4.43 19.65L5.26 16.61L5.06 16.29C4.24 15 3.8 13.47 3.8 11.91C3.81 7.37 7.5 3.67 12.05 3.67M9.06 7.04C8.88 7.04 8.58 7.11 8.33 7.38C8.08 7.65 7.37 8.32 7.37 9.68C7.37 11.04 8.36 12.35 8.5 12.53C8.64 12.71 10.43 15.48 13.18 16.67C13.84 16.95 14.34 17.11 14.74 17.24C15.4 17.45 16.01 17.42 16.49 17.35C17.03 17.27 18.14 16.68 18.37 16.03C18.6 15.38 18.6 14.83 18.53 14.71C18.46 14.59 18.28 14.53 18.01 14.39C17.74 14.25 16.42 13.6 16.17 13.51C15.92 13.42 15.74 13.38 15.56 13.65C15.38 13.92 14.86 14.53 14.7 14.71C14.54 14.89 14.38 14.91 14.11 14.78C13.84 14.64 12.97 14.36 11.94 13.44C11.14 12.72 10.6 11.84 10.44 11.57C10.28 11.3 10.42 11.15 10.56 11.02C10.68 10.9 10.83 10.71 10.97 10.55C11.11 10.39 11.15 10.28 11.24 10.1C11.33 9.92 11.29 9.76 11.22 9.63C11.15 9.5 10.62 8.2 10.4 7.67C10.19 7.15 9.97 7.22 9.8 7.21C9.65 7.2 9.47 7.04 9.06 7.04Z"/></svg>
                        </div>
                        <div>
                            <span class="data-card-title" style="margin: 0; font-size: 1.05rem;">Integrasi WhatsApp Gateway</span>
                            <div style="font-size: 0.78rem; color: var(--text-muted); word-break: break-word;">Kirim pesan darurat, notifikasi surat warga, dan bot otomatis melalui WhatsApp</div>
                        </div>
                    </div>
                </div>

                <div class="data-card-body config-card-body">
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label for="whatsapp_bot_url" class="form-label" style="font-weight: 600; display: block; margin-bottom: 0.35rem;">
                                Server URL Gateway WhatsApp
                            </label>
                            <input type="text" name="whatsapp_bot_url" id="whatsapp_bot_url" class="form-control" 
                                   value="{{ old('whatsapp_bot_url', $config->whatsapp_bot_url ?? 'http://127.0.0.1:3000') }}" 
                                   placeholder="http://127.0.0.1:3000 atau https://wa.domain-anda.com" 
                                   style="font-family: monospace; font-size: 0.9rem; width: 100%; box-sizing: border-box;">
                            <span style="font-size: 0.75rem; color: var(--text-muted); display: block; margin-top: 0.25rem; word-break: break-word;">
                                URL endpoint service bot Node.js/Baileys WhatsApp (port default: 3000).
                            </span>
                        </div>

                        <div class="form-group">
                            <label for="whatsapp_bot_number" class="form-label" style="font-weight: 600; display: block; margin-bottom: 0.35rem;">
                                Nomor WhatsApp Bot (Format Internasional)
                            </label>
                            <input type="text" name="whatsapp_bot_number" id="whatsapp_bot_number" class="form-control" 
                                   value="{{ old('whatsapp_bot_number', $config->whatsapp_bot_number ?? '628981295171') }}" 
                                   placeholder="628981295171 (tanpa spasi/tanda +)" 
                                   style="font-family: monospace; font-size: 0.9rem; width: 100%; box-sizing: border-box;">
                            <span style="font-size: 0.75rem; color: var(--text-muted); display: block; margin-top: 0.25rem; word-break: break-word;">
                                Nomor resmi bot yang tertera di sidebar dan perpesanan warga.
                            </span>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label for="whatsapp_api_token" class="form-label" style="font-weight: 600; display: block; margin-bottom: 0.35rem;">
                            API Secret Token (Opsional)
                        </label>
                        <input type="text" name="whatsapp_api_token" id="whatsapp_api_token" class="form-control" 
                               value="{{ $maskedWhatsappToken }}" 
                               placeholder="Bearer token jika gateway dilindungi autentikasi" 
                               autocomplete="off"
                               style="font-family: monospace; font-size: 0.9rem; width: 100%; box-sizing: border-box;">
                        <span style="font-size: 0.75rem; color: var(--text-muted); display: block; margin-top: 0.25rem; word-break: break-word;">
                            Biarkan tanda titik <code>••••</code> jika tidak ingin mengganti secret token yang sudah tersimpan.
                        </span>
                    </div>

                    {{-- WhatsApp Test Panel --}}
                    <div class="test-panel" style="background: rgba(16, 185, 129, 0.05); border: 1px dashed rgba(16, 185, 129, 0.3);">
                        <div style="flex: 1; min-width: 0;">
                            <div style="font-weight: 600; font-size: 0.88rem; color: var(--text-primary); margin-bottom: 0.15rem;">
                                Uji Server WhatsApp Gateway
                            </div>
                            <div style="font-size: 0.78rem; color: var(--text-muted); line-height: 1.4; word-break: break-word;">
                                Memeriksa konektivitas ke server gateway WhatsApp dan kesiapan sesi Baileys.
                            </div>
                        </div>
                        <div class="test-panel-action">
                            <div id="waTestResult" class="test-result-text"></div>
                            <button type="button" id="btnTestWa" class="btn btn-outline btn-sm test-panel-btn" onclick="runWaTest()">
                                <svg id="waTestSpinner" style="display: none; animation: spin 1s linear infinite; width: 14px; height: 14px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                                <span>⚡ Uji Gateway WhatsApp</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================= TAB 3: TELEGRAM BOT ================= --}}
        <div id="tab-telegram" class="config-tab-pane" style="display: none;">
            <div class="data-card" style="margin-bottom: 1.5rem;">
                <div class="data-card-header config-card-header">
                    <div style="display: flex; align-items: center; gap: 0.65rem;">
                        <div class="config-section-icon" style="background: rgba(56, 189, 248, 0.15); color: #0284c7;">
                            <svg viewBox="0 0 24 24" fill="currentColor" style="width: 18px; height: 18px;"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 00-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.75-.55 2.92-1.27 4.86-2.11 5.83-2.51 2.78-1.16 3.35-1.36 3.73-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06.01.24 0 .38z"/></svg>
                        </div>
                        <div>
                            <span class="data-card-title" style="margin: 0; font-size: 1.05rem;">Integrasi Telegram Bot Gateway</span>
                            <div style="font-size: 0.78rem; color: var(--text-muted); word-break: break-word;">Notifikasi alert instan, interaksi perintah Telegram, dan forward pesan sistem</div>
                        </div>
                    </div>
                </div>

                <div class="data-card-body config-card-body">
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label for="telegram_bot_token" class="form-label" style="font-weight: 600; display: block; margin-bottom: 0.35rem;">
                                Telegram Bot Token <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="text" name="telegram_bot_token" id="telegram_bot_token" class="form-control" 
                                   value="{{ $maskedTelegramToken }}" 
                                   placeholder="Contoh: 8720264415:AAH0GF1lUXQxSpwEoeKDJawe0MkGs6mdRsk" 
                                   autocomplete="off"
                                   style="font-family: monospace; font-size: 0.9rem; width: 100%; box-sizing: border-box;">
                            <span style="font-size: 0.75rem; color: var(--text-muted); display: block; margin-top: 0.25rem; word-break: break-word;">
                                Dapatkan dari <a href="https://t.me/BotFather" target="_blank" style="color: var(--accent-1); text-decoration: underline;">@BotFather</a> (Bot: <code>cevltBot</code>). Karakter disensor demi keamanan.
                            </span>
                        </div>

                        <div class="form-group">
                            <label for="telegram_bot_username" class="form-label" style="font-weight: 600; display: block; margin-bottom: 0.35rem;">
                                Username Bot Telegram
                            </label>
                            <div style="display: flex; align-items: center; width: 100%;">
                                <span style="background: var(--bg-body); border: 1px solid var(--border-subtle); border-right: none; padding: 0.55rem 0.75rem; border-radius: 8px 0 0 8px; color: var(--text-muted); font-size: 0.9rem; flex-shrink: 0;">@</span>
                                <input type="text" name="telegram_bot_username" id="telegram_bot_username" class="form-control" 
                                       value="{{ old('telegram_bot_username', ltrim($config->telegram_bot_username ?? 'cevltBot', '@')) }}" 
                                       placeholder="cevltBot" 
                                       style="border-radius: 0 8px 8px 0; font-size: 0.9rem; width: 100%; box-sizing: border-box;">
                            </div>
                            <span style="font-size: 0.75rem; color: var(--text-muted); display: block; margin-top: 0.25rem; word-break: break-word;">
                                Username bot tanpa simbol '@', misal <code>cevltBot</code>.
                            </span>
                        </div>
                    </div>

                    {{-- Telegram Test Panel --}}
                    <div class="test-panel" style="background: rgba(2, 132, 199, 0.05); border: 1px dashed rgba(2, 132, 199, 0.3);">
                        <div style="flex: 1; min-width: 0;">
                            <div style="font-weight: 600; font-size: 0.88rem; color: var(--text-primary); margin-bottom: 0.15rem;">
                                Verifikasi Bot Token ke Server Telegram
                            </div>
                            <div style="font-size: 0.78rem; color: var(--text-muted); line-height: 1.4; word-break: break-word;">
                                Memanggil API <code>getMe</code> Telegram untuk memeriksa nama dan username bot secara real-time.
                            </div>
                        </div>
                        <div class="test-panel-action">
                            <div id="tgTestResult" class="test-result-text"></div>
                            <button type="button" id="btnTestTg" class="btn btn-outline btn-sm test-panel-btn" onclick="runTgTest()">
                                <svg id="tgTestSpinner" style="display: none; animation: spin 1s linear infinite; width: 14px; height: 14px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                                <span>⚡ Uji Bot Telegram</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================= TAB 4: EMAIL (SMTP) ================= --}}
        <div id="tab-email" class="config-tab-pane" style="display: none;">
            <div class="data-card" style="margin-bottom: 1.5rem;">
                <div class="data-card-header config-card-header">
                    <div style="display: flex; align-items: center; gap: 0.65rem;">
                        <div class="config-section-icon" style="background: rgba(168, 85, 247, 0.12); color: #9333ea;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px;"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                        </div>
                        <div>
                            <span class="data-card-title" style="margin: 0; font-size: 1.05rem;">Konfigurasi Email Server (SMTP)</span>
                            <div style="font-size: 0.78rem; color: var(--text-muted); word-break: break-word;">Pengiriman email verifikasi, reset kata sandi, dan surat warga</div>
                        </div>
                    </div>
                </div>

                <div class="data-card-body config-card-body">
                    <div class="form-grid-4">
                        <div class="form-group">
                            <label for="mail_mailer" class="form-label" style="font-weight: 600; display: block; margin-bottom: 0.35rem;">
                                Mailer Driver
                            </label>
                            <input type="text" name="mail_mailer" id="mail_mailer" class="form-control" 
                                   value="{{ old('mail_mailer', $config->mail_mailer ?? 'smtp') }}" 
                                   placeholder="smtp" 
                                   style="font-family: monospace; font-size: 0.9rem; width: 100%; box-sizing: border-box;">
                        </div>

                        <div class="form-group">
                            <label for="mail_host" class="form-label" style="font-weight: 600; display: block; margin-bottom: 0.35rem;">
                                SMTP Host
                            </label>
                            <input type="text" name="mail_host" id="mail_host" class="form-control" 
                                   value="{{ old('mail_host', $config->mail_host ?? '127.0.0.1') }}" 
                                   placeholder="Contoh: 127.0.0.1 atau smtp.gmail.com" 
                                   style="font-family: monospace; font-size: 0.9rem; width: 100%; box-sizing: border-box;">
                        </div>

                        <div class="form-group">
                            <label for="mail_port" class="form-label" style="font-weight: 600; display: block; margin-bottom: 0.35rem;">
                                SMTP Port
                            </label>
                            <input type="number" name="mail_port" id="mail_port" class="form-control" 
                                   value="{{ old('mail_port', $config->mail_port ?? 1025) }}" 
                                   placeholder="1025, 587, atau 465" 
                                   style="font-family: monospace; font-size: 0.9rem; width: 100%; box-sizing: border-box;">
                        </div>

                        <div class="form-group">
                            <label for="mail_encryption" class="form-label" style="font-weight: 600; display: block; margin-bottom: 0.35rem;">
                                Enkripsi (TLS / SSL / Kosong)
                            </label>
                            <input type="text" name="mail_encryption" id="mail_encryption" class="form-control" 
                                   value="{{ old('mail_encryption', $config->mail_encryption ?? '') }}" 
                                   placeholder="tls, ssl, atau kosongkan" 
                                   style="font-family: monospace; font-size: 0.9rem; width: 100%; box-sizing: border-box;">
                        </div>
                    </div>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label for="mail_username" class="form-label" style="font-weight: 600; display: block; margin-bottom: 0.35rem;">
                                Username / Akun Email SMTP
                            </label>
                            <input type="text" name="mail_username" id="mail_username" class="form-control" 
                                   value="{{ old('mail_username', $config->mail_username ?? '') }}" 
                                   placeholder="admin@siwil.id atau akun@gmail.com" 
                                   style="font-family: monospace; font-size: 0.9rem; width: 100%; box-sizing: border-box;">
                        </div>

                        <div class="form-group">
                            <label for="mail_password" class="form-label" style="font-weight: 600; display: block; margin-bottom: 0.35rem;">
                                Password SMTP / App Password
                            </label>
                            <input type="text" name="mail_password" id="mail_password" class="form-control" 
                                   value="{{ $maskedMailPassword }}" 
                                   placeholder="Password aplikasi SMTP" 
                                   autocomplete="off"
                                   style="font-family: monospace; font-size: 0.9rem; width: 100%; box-sizing: border-box;">
                            <span style="font-size: 0.75rem; color: var(--text-muted); display: block; margin-top: 0.25rem; word-break: break-word;">
                                Biarkan tanda <code>••••</code> jika tidak ingin mengganti password yang tersimpan.
                            </span>
                        </div>
                    </div>

                    <div class="form-grid-2" style="margin-bottom: 1.5rem;">
                        <div class="form-group">
                            <label for="mail_from_address" class="form-label" style="font-weight: 600; display: block; margin-bottom: 0.35rem;">
                                Email Pengirim (From Address)
                            </label>
                            <input type="email" name="mail_from_address" id="mail_from_address" class="form-control" 
                                   value="{{ old('mail_from_address', $config->mail_from_address ?? 'noreply@siwil.id') }}" 
                                   placeholder="noreply@siwil.id" 
                                   style="font-size: 0.9rem; width: 100%; box-sizing: border-box;">
                        </div>

                        <div class="form-group">
                            <label for="mail_from_name" class="form-label" style="font-weight: 600; display: block; margin-bottom: 0.35rem;">
                                Nama Pengirim (From Name)
                            </label>
                            <input type="text" name="mail_from_name" id="mail_from_name" class="form-control" 
                                   value="{{ old('mail_from_name', $config->mail_from_name ?? 'SIWIL AI Notification') }}" 
                                   placeholder="SIWIL AI Notification" 
                                   style="font-size: 0.9rem; width: 100%; box-sizing: border-box;">
                        </div>
                    </div>

                    {{-- Email Test Panel --}}
                    <div class="test-panel" style="background: rgba(147, 51, 234, 0.05); border: 1px dashed rgba(147, 51, 234, 0.3);">
                        <div style="flex: 1; min-width: 0;">
                            <div style="font-weight: 600; font-size: 0.88rem; color: var(--text-primary); margin-bottom: 0.15rem;">
                                Uji Kirim Email Percobaan
                            </div>
                            <div style="font-size: 0.78rem; color: var(--text-muted); line-height: 1.4; word-break: break-word;">
                                Kirim email uji coba ke alamat Anda (<strong>{{ auth()->user()->email }}</strong>) untuk memverifikasi handshake SMTP.
                            </div>
                        </div>
                        <div class="test-panel-action">
                            <div id="emailTestResult" class="test-result-text"></div>
                            <button type="button" id="btnTestEmail" class="btn btn-outline btn-sm test-panel-btn" onclick="runEmailTest()">
                                <svg id="emailTestSpinner" style="display: none; animation: spin 1s linear infinite; width: 14px; height: 14px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                                <span>⚡ Kirim Email Uji Coba</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sticky / Action Submit Footer --}}
        <div class="config-sticky-footer">
            <div class="config-footer-notice">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; color: var(--accent-1); flex-shrink: 0;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                <span>Perubahan konfigurasi langsung aktif secara global di seluruh modul sistem.</span>
            </div>
            <div class="config-footer-actions">
                <button type="reset" class="btn btn-outline config-btn-reset" onclick="location.reload();">
                    Batal
                </button>
                <button type="submit" class="btn btn-primary config-btn-submit">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px;"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    <span>Simpan Seluruh Konfigurasi</span>
                </button>
            </div>
        </div>
    </form>
</div>

{{-- Styles for Tabs, Provider Cards, Responsive Grids, and Animations --}}
<style>
    /* Container & Base Wrapping */
    .config-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 0.25rem 3rem;
        box-sizing: border-box;
    }
    .config-container,
    .config-container * {
        overflow-wrap: break-word;
        word-break: break-word;
    }
    .config-container code,
    .config-container pre {
        word-break: break-all;
    }

    /* Header Section */
    .config-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1.25rem;
        flex-wrap: wrap;
        gap: 0.85rem;
    }
    .config-title-wrap {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.35rem;
        flex-wrap: wrap;
    }
    .config-icon-badge {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: linear-gradient(135deg, rgba(37, 99, 235, 0.15), rgba(59, 130, 246, 0.25));
        color: var(--accent-1);
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 8px rgba(37, 99, 235, 0.15);
        flex-shrink: 0;
    }
    .config-main-title {
        margin: 0;
        font-size: 1.35rem;
        font-weight: 700;
        color: var(--text-primary);
    }
    .config-role-badge {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .config-subtitle {
        font-size: 0.85rem;
        color: var(--text-muted);
        margin: 0;
        max-width: 780px;
        line-height: 1.5;
    }
    .config-header-actions {
        width: 100%;
        display: flex;
    }
    .config-kb-btn {
        width: 100%;
        justify-content: center;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.85rem;
    }

    /* Tabs Navigation (Swipeable horizontal on mobile) */
    .config-tabs-nav {
        display: flex;
        gap: 0.35rem;
        border-bottom: 2px solid var(--border-subtle);
        margin-bottom: 1.25rem;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
        -ms-overflow-style: none;
        padding-bottom: 2px;
        flex-wrap: nowrap;
    }
    .config-tabs-nav::-webkit-scrollbar {
        display: none;
    }
    .config-tabs-nav .config-tab-btn {
        background: transparent;
        border: none;
        border-bottom: 2px solid transparent;
        margin-bottom: -2px;
        padding: 0.65rem 0.95rem;
        font-family: inherit;
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--text-muted);
        cursor: pointer;
        transition: all 0.2s ease;
        border-radius: 8px 8px 0 0;
        white-space: nowrap;
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
    }
    .config-tabs-nav .config-tab-btn:hover {
        color: var(--text-primary);
        background: rgba(148, 163, 184, 0.08);
    }
    .config-tabs-nav .config-tab-btn.active {
        color: var(--accent-1);
        border-bottom-color: var(--accent-1);
        background: rgba(37, 99, 235, 0.05);
    }

    /* Section Card Header & Body */
    .config-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.65rem;
        padding: 0.85rem 1rem;
    }
    .config-section-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .config-card-body {
        padding: 1rem 0.85rem;
    }

    /* Responsive Grids */
    .providers-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.6rem;
    }
    .provider-radio-card {
        border: 1px solid var(--border-subtle);
        background: var(--bg-card);
        border-radius: 10px;
        padding: 0.75rem;
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
        min-width: 0;
    }
    .provider-radio-card:hover {
        border-color: rgba(37, 99, 235, 0.4);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
    }
    .provider-radio-card.selected {
        border-color: var(--accent-1);
        background: rgba(37, 99, 235, 0.04);
        box-shadow: 0 0 0 1px var(--accent-1);
    }
    .provider-check-icon {
        width: 18px;
        height: 18px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.72rem;
        font-weight: bold;
        background: var(--border-subtle);
        color: transparent;
        transition: all 0.2s ease;
        flex-shrink: 0;
    }
    .provider-radio-card.selected .provider-check-icon {
        background: var(--accent-1);
        color: #ffffff;
    }

    .form-grid-2 {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1rem;
        margin-bottom: 1.25rem;
    }
    .form-grid-4 {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1rem;
        margin-bottom: 1.25rem;
    }

    /* API Keys Section (Mobile-First Full Width) */
    .api-keys-header {
        display: flex;
        flex-direction: column;
        gap: 0.65rem;
        margin-bottom: 0.65rem;
    }
    .btn-add-key {
        width: 100%;
        justify-content: center;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-weight: 600;
        padding: 0.55rem 1rem;
    }
    .api-key-row {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        background: var(--bg-body);
        padding: 0.75rem;
        border-radius: 8px;
        border: 1px solid var(--border-subtle);
        width: 100%;
        box-sizing: border-box;
    }
    .api-key-header-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
    }
    .key-index-badge {
        font-size: 0.78rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
    }
    .api-key-input-wrapper {
        width: 100%;
    }
    .api-key-input-wrapper .api-key-input {
        width: 100% !important;
        box-sizing: border-box;
    }
    .remove-key-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.4rem 0.65rem;
        border-radius: 6px;
    }
    .remove-text-mobile {
        display: inline;
        font-size: 0.75rem;
        font-weight: 600;
    }

    /* Model Chips */
    .model-chip {
        background: var(--bg-body);
        border: 1px solid var(--border-subtle);
        color: var(--text-secondary);
        font-size: 0.75rem;
        font-family: monospace;
        padding: 0.25rem 0.55rem;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .model-chip:hover {
        border-color: var(--accent-1);
        color: var(--accent-1);
        background: rgba(37, 99, 235, 0.06);
    }

    /* Test Panel */
    .test-panel {
        border-radius: 10px;
        padding: 0.85rem;
        display: flex;
        flex-direction: column;
        gap: 0.85rem;
    }
    .test-panel-action {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        width: 100%;
    }
    .test-result-text {
        font-size: 0.8rem;
        line-height: 1.4;
        display: none;
        word-break: break-word;
        overflow-wrap: anywhere;
    }
    .test-panel-btn {
        width: 100%;
        justify-content: center;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        font-weight: 600;
        padding: 0.55rem 1rem;
    }

    /* Sticky Footer */
    .config-sticky-footer {
        position: sticky;
        bottom: 1rem;
        z-index: 20;
        background: var(--bg-card);
        border: 1px solid var(--border-subtle);
        border-radius: 12px;
        padding: 0.85rem 1rem;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.12), 0 8px 10px -6px rgba(0, 0, 0, 0.08);
    }
    .config-footer-notice {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.8rem;
        color: var(--text-muted);
        line-height: 1.4;
    }
    .config-footer-actions {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        width: 100%;
    }
    .config-btn-reset {
        flex: 1 1 30%;
        justify-content: center;
        padding: 0.65rem 1rem;
    }
    .config-btn-submit {
        flex: 2 1 70%;
        justify-content: center;
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        font-weight: 600;
        padding: 0.65rem 1rem;
        font-size: 0.88rem;
    }

    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    /* ================= TABLET & DESKTOP BREAKPOINTS (>= 640px) ================= */
    @media (min-width: 640px) {
        .config-container {
            padding: 0 0.5rem 3rem;
        }
        .config-main-title {
            font-size: 1.5rem;
        }
        .config-header-actions {
            width: auto;
        }
        .config-kb-btn {
            width: auto;
        }
        .config-card-header {
            padding: 0.95rem 1.25rem;
        }
        .config-card-body {
            padding: 1.5rem;
        }
        .providers-grid {
            grid-template-columns: repeat(3, 1fr);
            gap: 0.75rem;
        }
        .form-grid-2 {
            grid-template-columns: repeat(2, 1fr);
            gap: 1.25rem;
        }
        .form-grid-4 {
            grid-template-columns: repeat(2, 1fr);
            gap: 1.25rem;
        }
        .api-keys-header {
            flex-direction: row;
            justify-content: space-between;
            align-items: center;
            gap: 0.5rem;
        }
        .btn-add-key {
            width: auto;
        }
        .api-key-row {
            flex-direction: row;
            align-items: center;
            gap: 0.75rem;
            padding: 0.6rem 0.8rem;
        }
        .api-key-header-row {
            width: auto;
            display: contents;
        }
        .key-index-badge {
            order: 1;
            width: 90px;
            flex-shrink: 0;
            font-size: 0.75rem;
        }
        .api-key-input-wrapper {
            order: 2;
            flex: 1;
        }
        .remove-key-btn {
            order: 3;
            flex-shrink: 0;
            padding: 0.45rem 0.65rem;
        }
        .remove-text-mobile {
            display: none;
        }
        .test-panel {
            flex-direction: row;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
        }
        .test-panel-action {
            flex-direction: row;
            align-items: center;
            gap: 0.75rem;
            width: auto;
        }
        .test-panel-btn {
            width: auto;
        }
        .config-sticky-footer {
            flex-direction: row;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 1.5rem;
        }
        .config-footer-actions {
            width: auto;
        }
        .config-btn-reset,
        .config-btn-submit {
            flex: 0 0 auto;
        }
    }

    /* ================= DESKTOP BREAKPOINTS (>= 1024px) ================= */
    @media (min-width: 1024px) {
        .providers-grid {
            grid-template-columns: repeat(6, 1fr);
        }
        .form-grid-4 {
            grid-template-columns: repeat(4, 1fr);
        }
    }
</style>

{{-- Client-Side Logic: Tab Switching, Dynamic Multi-Key Rows, Live Tests --}}
<script>
    // Tab Switching
    function switchConfigTab(targetTabId, btn) {
        document.querySelectorAll('.config-tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.config-tab-pane').forEach(p => p.style.display = 'none');
        
        btn.classList.add('active');
        const target = document.getElementById(targetTabId);
        if (target) {
            target.style.display = 'block';
        }
    }

    // Provider Selector Logic
    function onProviderChanged(providerId, defaultModel) {
        document.querySelectorAll('.provider-radio-card').forEach(c => c.classList.remove('selected'));
        const card = document.getElementById('provider_card_' + providerId);
        if (card) card.classList.add('selected');

        const badge = document.getElementById('currentProviderBadge');
        if (badge) badge.textContent = 'Provider: ' + providerId.toUpperCase();

        const baseUrlGroup = document.getElementById('baseUrlGroup');
        if (baseUrlGroup) {
            baseUrlGroup.style.display = (providerId === 'custom') ? 'block' : 'none';
        }

        // If model input is empty or matches previous default, fill with newly chosen provider default
        const modelInput = document.getElementById('ai_model');
        const defaultModels = ['gemini-2.5-flash', 'deepseek-chat', 'llama-3.3-70b-versatile', 'grok-beta', 'gpt-4o-mini', 'local-model'];
        if (modelInput && (!modelInput.value || defaultModels.includes(modelInput.value))) {
            modelInput.value = defaultModel;
        }
    }

    // Model Chip click helper
    function applyModelChip(name) {
        const modelInput = document.getElementById('ai_model');
        if (modelInput) {
            modelInput.value = name;
            modelInput.focus();
        }
    }

    // DYNAMIC MULTI-KEY ROW LOGIC
    function updateKeyBadges() {
        const container = document.getElementById('apiKeysContainer');
        const rows = container.querySelectorAll('.api-key-row');
        rows.forEach((row, index) => {
            const label = row.querySelector('.key-label');
            const badge = row.querySelector('.key-index-badge');
            if (label && badge) {
                if (index === 0) {
                    label.textContent = 'Kunci Utama';
                    badge.style.color = 'var(--accent-1)';
                } else {
                    label.textContent = 'Fallback #' + index;
                    badge.style.color = 'var(--text-muted)';
                }
            }
        });

        const countBadge = document.getElementById('keysCountBadge');
        if (countBadge) {
            countBadge.textContent = rows.length + ' Kunci';
        }
    }

    function addApiKeyRow() {
        const container = document.getElementById('apiKeysContainer');
        const rowsCount = container.querySelectorAll('.api-key-row').length;
        const newIdx = rowsCount;

        const newRow = document.createElement('div');
        newRow.className = 'api-key-row';
        newRow.id = 'apiKeyRow_' + newIdx;

        newRow.innerHTML = `
            <div class="api-key-header-row">
                <div class="key-index-badge" style="color: var(--text-muted);">
                    <span class="key-label">Fallback #${newIdx}</span>
                </div>
                <button type="button" class="btn btn-danger btn-sm remove-key-btn" onclick="removeApiKeyRow(this)" title="Hapus API Key ini">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px;"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                    <span class="remove-text-mobile">Hapus</span>
                </button>
            </div>
            <div class="api-key-input-wrapper">
                <input type="text" name="ai_api_keys[]" class="form-control api-key-input" 
                       value="" 
                       placeholder="Masukkan API Key cadangan fallback" 
                       autocomplete="off" 
                       style="font-family: monospace; font-size: 0.88rem; padding: 0.55rem 0.75rem; border-radius: 6px; width: 100%; box-sizing: border-box;">
            </div>
        `;

        container.appendChild(newRow);
        updateKeyBadges();

        const input = newRow.querySelector('input');
        if (input) input.focus();
    }

    function removeApiKeyRow(button) {
        const container = document.getElementById('apiKeysContainer');
        const rows = container.querySelectorAll('.api-key-row');
        if (rows.length <= 1) {
            // If only 1 row left, clear its value instead of removing the whole row
            const input = rows[0].querySelector('input');
            if (input) input.value = '';
            return;
        }

        const row = button.closest('.api-key-row');
        if (row) {
            row.remove();
            updateKeyBadges();
        }
    }

    // LIVE TESTING: AI CONNECTION
    async function runAiTest() {
        const btn = document.getElementById('btnTestAi');
        const spinner = document.getElementById('aiTestSpinner');
        const result = document.getElementById('aiTestResult');

        const provider = document.querySelector('input[name="ai_provider"]:checked')?.value || 'gemini';
        const model = document.getElementById('ai_model')?.value || '';
        const baseUrl = document.getElementById('ai_base_url')?.value || '';

        const keyInputs = document.querySelectorAll('input[name="ai_api_keys[]"]');
        const keys = Array.from(keyInputs).map(inp => inp.value.trim()).filter(v => v !== '');

        btn.disabled = true;
        spinner.style.display = 'inline-block';
        result.style.display = 'block';
        result.className = 'test-result-text';
        result.style.color = 'var(--text-muted)';
        result.textContent = 'Menghubungkan ke ' + provider + ' [' + model + ']...';

        try {
            const resp = await fetch("{{ route('configuration.test-ai') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Accept": "application/json"
                },
                body: JSON.stringify({
                    ai_provider: provider,
                    ai_model: model,
                    ai_base_url: baseUrl,
                    ai_api_keys: keys
                })
            });

            const data = await resp.json();
            if (resp.ok && data.success) {
                result.style.color = '#10b981';
                result.textContent = data.message;
            } else {
                result.style.color = '#ef4444';
                result.textContent = 'Gagal: ' + (data.message || 'Respons tidak valid');
            }
        } catch (err) {
            result.style.color = '#ef4444';
            result.textContent = 'Error: ' + err.message;
        } finally {
            btn.disabled = false;
            spinner.style.display = 'none';
        }
    }

    // LIVE TESTING: WHATSAPP GATEWAY
    async function runWaTest() {
        const btn = document.getElementById('btnTestWa');
        const spinner = document.getElementById('waTestSpinner');
        const result = document.getElementById('waTestResult');

        const url = document.getElementById('whatsapp_bot_url')?.value || '';
        const token = document.getElementById('whatsapp_api_token')?.value || '';

        btn.disabled = true;
        spinner.style.display = 'inline-block';
        result.style.display = 'block';
        result.className = 'test-result-text';
        result.style.color = 'var(--text-muted)';
        result.textContent = 'Memeriksa gateway WhatsApp...';

        try {
            const resp = await fetch("{{ route('configuration.test-whatsapp') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Accept": "application/json"
                },
                body: JSON.stringify({
                    whatsapp_bot_url: url,
                    whatsapp_api_token: token
                })
            });

            const data = await resp.json();
            if (resp.ok && data.success) {
                result.style.color = '#10b981';
                result.textContent = data.message;
            } else {
                result.style.color = '#ef4444';
                result.textContent = 'Gagal: ' + (data.message || 'Tidak dapat terhubung');
            }
        } catch (err) {
            result.style.color = '#ef4444';
            result.textContent = 'Error: ' + err.message;
        } finally {
            btn.disabled = false;
            spinner.style.display = 'none';
        }
    }

    // LIVE TESTING: TELEGRAM BOT
    async function runTgTest() {
        const btn = document.getElementById('btnTestTg');
        const spinner = document.getElementById('tgTestSpinner');
        const result = document.getElementById('tgTestResult');

        const token = document.getElementById('telegram_bot_token')?.value || '';

        btn.disabled = true;
        spinner.style.display = 'inline-block';
        result.style.display = 'block';
        result.className = 'test-result-text';
        result.style.color = 'var(--text-muted)';
        result.textContent = 'Memeriksa Bot Token ke Telegram...';

        try {
            const resp = await fetch("{{ route('configuration.test-telegram') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Accept": "application/json"
                },
                body: JSON.stringify({
                    telegram_bot_token: token
                })
            });

            const data = await resp.json();
            if (resp.ok && data.success) {
                result.style.color = '#10b981';
                result.textContent = data.message;
            } else {
                result.style.color = '#ef4444';
                result.textContent = 'Gagal: ' + (data.message || 'Token tidak valid');
            }
        } catch (err) {
            result.style.color = '#ef4444';
            result.textContent = 'Error: ' + err.message;
        } finally {
            btn.disabled = false;
            spinner.style.display = 'none';
        }
    }

    // LIVE TESTING: EMAIL SMTP
    async function runEmailTest() {
        const btn = document.getElementById('btnTestEmail');
        const spinner = document.getElementById('emailTestSpinner');
        const result = document.getElementById('emailTestResult');

        btn.disabled = true;
        spinner.style.display = 'inline-block';
        result.style.display = 'block';
        result.className = 'test-result-text';
        result.style.color = 'var(--text-muted)';
        result.textContent = 'Mengirim email uji coba...';

        try {
            const resp = await fetch("{{ route('configuration.test-email') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Accept": "application/json"
                },
                body: JSON.stringify({
                    mail_mailer: document.getElementById('mail_mailer')?.value || 'smtp',
                    mail_host: document.getElementById('mail_host')?.value || '',
                    mail_port: document.getElementById('mail_port')?.value || '',
                    mail_username: document.getElementById('mail_username')?.value || '',
                    mail_password: document.getElementById('mail_password')?.value || '',
                    mail_encryption: document.getElementById('mail_encryption')?.value || '',
                    mail_from_address: document.getElementById('mail_from_address')?.value || '',
                    mail_from_name: document.getElementById('mail_from_name')?.value || ''
                })
            });

            const data = await resp.json();
            if (resp.ok && data.success) {
                result.style.color = '#10b981';
                result.textContent = data.message;
            } else {
                result.style.color = '#ef4444';
                result.textContent = 'Gagal: ' + (data.message || 'Gagal mengirim email');
            }
        } catch (err) {
            result.style.color = '#ef4444';
            result.textContent = 'Error: ' + err.message;
        } finally {
            btn.disabled = false;
            spinner.style.display = 'none';
        }
    }
</script>
@endsection
