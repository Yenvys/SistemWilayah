@extends('layouts.app')

@section('title', 'Kelola AI Knowledge & Multi-Provider - SIWIL AI')
@section('page-title', 'Kelola AI Engine & Data Latih')

@section('content')
    @php
        $activeProvider = config('services.ai.provider') ?? env('AI_PROVIDER', 'gemini');
        $activeModel = config('services.ai.model') ?? env('AI_MODEL') ?? env('GEMINI_MODEL', 'gemini-2.5-flash');
        $activeApiKey = config('services.ai.api_key') ?? env('AI_API_KEY') ?? env('GEMINI_API_KEY', '');
        $activeBaseUrl = config('services.ai.base_url') ?? env('AI_BASE_URL', '');
    @endphp

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 class="page-title" style="margin-bottom: 0.25rem;">Konfigurasi AI Engine & Knowledge Base</h1>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin: 0;">
                Atur penyedia API (Gemini, DeepSeek, Groq, Grok, OpenAI), model kecerdasan buatan, dan kelola basis pengetahuan chatbot SIWIL AI.
            </p>
        </div>
    </div>
    
    <div class="alert alert-info" style="border-color: rgba(99, 102, 241, 0.4); margin-bottom: 1.5rem;">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 20px; height: 20px; flex-shrink: 0;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
        <span>
            <strong>Multi-Provider AI Didukung:</strong> Anda kini bebas menggunakan berbagai penyedia API AI seperti <strong>Google Gemini, DeepSeek (V3/R1), Groq (LLaMA 3.3/Mixtral/R1 Distill), xAI Grok, OpenAI</strong>, maupun Custom API. Model akan otomatis menyesuaikan provider yang dipilih.
        </span>
    </div>

    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom: 1.5rem;">{{ session('success') }}</div>
    @endif
    
    @if($errors->any())
        <div class="alert alert-danger" style="margin-bottom: 1.5rem;">
            <ul style="margin: 0; padding-left: 1.25rem;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Konfigurasi API & Model AI Multi-Provider -->
    <div class="data-card" style="margin-bottom: 2rem;">
        <div class="data-card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(37, 99, 235, 0.1); color: var(--accent-1); display: flex; align-items: center; justify-content: center;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px;"><path d="M12 2v4"/><path d="m4.93 4.93 2.83 2.83"/><path d="M2 12h4"/><path d="m4.93 19.07 2.83-2.83"/><path d="M12 22v-4"/><path d="m19.07 19.07-2.83-2.83"/><path d="M22 12h-4"/><path d="m19.07 4.93-2.83 2.83"/></svg>
                </div>
                <div>
                    <span class="data-card-title" style="margin: 0;">Pengaturan Provider & Model AI</span>
                    <div style="font-size: 0.78rem; color: var(--text-muted);">Pilih engine AI yang akan merespons pertanyaan warga di chatbot</div>
                </div>
            </div>
            <span class="badge badge-blue" id="activeProviderBadge" style="text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">
                Aktif: {{ strtoupper($activeProvider) }}
            </span>
        </div>

        <div class="data-card-body" style="padding: 1.75rem;">
            <form action="{{ route('ai-knowledge.config') }}" method="POST" id="aiConfigForm">
                @csrf
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">
                    
                    {{-- 1. PILIH PROVIDER AI --}}
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 700; font-size: 0.95rem; color: var(--text-primary); margin-bottom: 0.4rem; display: flex; align-items: center; gap: 0.4rem;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 15px; height: 15px; color: var(--accent-1);"><circle cx="12" cy="12" r="10"/><path d="m10 15 5-3-5-3v6z"/></svg>
                            Penyedia Layanan AI (Provider) <span style="color: var(--danger);">*</span>
                        </label>
                        <select name="ai_provider" id="ai_provider" class="form-select" required onchange="onProviderChange()" style="width: 100%;">
                            <option value="gemini" {{ $activeProvider === 'gemini' ? 'selected' : '' }}>Google Gemini (Google AI Studio)</option>
                            <option value="deepseek" {{ $activeProvider === 'deepseek' ? 'selected' : '' }}>DeepSeek AI (DeepSeek-V3 & R1)</option>
                            <option value="groq" {{ $activeProvider === 'groq' ? 'selected' : '' }}>Groq (Ultra-Fast LLaMA 3.3 & Mixtral)</option>
                            <option value="xai" {{ $activeProvider === 'xai' ? 'selected' : '' }}>xAI (Grok 2 / Grok Beta)</option>
                            <option value="openai" {{ $activeProvider === 'openai' ? 'selected' : '' }}>OpenAI (ChatGPT / GPT-4o)</option>
                            <option value="custom" {{ $activeProvider === 'custom' ? 'selected' : '' }}>Custom OpenAI-Compatible (Ollama, OpenRouter, dll)</option>
                        </select>
                        <small style="color: var(--text-muted); display: block; margin-top: 6px; font-size: 0.78rem;">
                            Setiap provider memiliki karakteristik, latensi, dan format API key yang berbeda.
                        </small>
                    </div>

                    {{-- 2. PILIH MODEL AI (Disesuaikan otomatis) --}}
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 700; font-size: 0.95rem; color: var(--text-primary); margin-bottom: 0.4rem; display: flex; align-items: center; gap: 0.4rem;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 15px; height: 15px; color: var(--accent-1);"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                            Model AI <span style="color: var(--danger);">*</span>
                        </label>
                        <select name="ai_model_select" id="ai_model_select" class="form-select" onchange="onModelSelectChange()" style="width: 100%;">
                            {{-- Options injected dynamically via JS --}}
                        </select>
                        <small style="color: var(--text-muted); display: block; margin-top: 6px; font-size: 0.78rem;" id="modelHintText">
                            Pilihan model AI yang dioptimalkan untuk provider aktif.
                        </small>
                    </div>

                    {{-- 2b. INPUT MANUAL MODEL (jika pilih custom / lainnya) --}}
                    <div class="form-group" id="customModelBox" style="grid-column: 1 / -1; display: none;">
                        <label class="form-label" style="font-weight: 700; font-size: 0.9rem; color: var(--text-primary); margin-bottom: 0.4rem;">
                            Nama Model Spesifik (Custom Model Name) <span style="color: var(--danger);">*</span>
                        </label>
                        <input type="text" name="ai_model_custom" id="ai_model_custom" class="form-input" 
                            placeholder="Contoh: deepseek-ai/DeepSeek-V3, llama-3.3-70b-versatile, mistral-large" 
                            style="width: 100%; font-family: monospace;">
                        <small style="color: var(--text-muted); display: block; margin-top: 4px; font-size: 0.78rem;">
                            Tuliskan kode/nama model persis sesuai dokumentasi provider Anda.
                        </small>
                    </div>

                    {{-- Hidden final model value --}}
                    <input type="hidden" name="ai_model" id="ai_model" value="{{ $activeModel }}">

                    {{-- 3. BASE URL (Hanya muncul jika custom) --}}
                    <div class="form-group" id="baseUrlBox" style="grid-column: 1 / -1; display: none;">
                        <label class="form-label" style="font-weight: 700; font-size: 0.9rem; color: var(--text-primary); margin-bottom: 0.4rem;">
                            Custom API Endpoint / Base URL <span style="color: var(--danger);">*</span>
                        </label>
                        <input type="text" name="ai_base_url" id="ai_base_url" class="form-input" 
                            value="{{ $activeBaseUrl }}" 
                            placeholder="Contoh: https://openrouter.ai/api/v1/chat/completions atau http://localhost:11434/v1" 
                            style="width: 100%; font-family: monospace;">
                        <small style="color: var(--text-muted); display: block; margin-top: 4px; font-size: 0.78rem;">
                            Masukkan URL endpoint yang mendukung protokol OpenAI Chat Completions.
                        </small>
                    </div>

                    {{-- 4. API KEY --}}
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
                            <label class="form-label" style="font-weight: 700; font-size: 0.95rem; color: var(--text-primary); margin: 0; display: flex; align-items: center; gap: 0.4rem;">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 15px; height: 15px; color: var(--accent-1);"><path d="M21 2l-2 2m-1.5 1.5L14 9l-1.5-1.5-2 2L12 11l-3 3-1.5-1.5L6 14l1.5 1.5L3 20l1 1 4.5-4.5 1.5 1.5 1.5-1.5-1.5-1.5 3-3 1.5 1.5 2-2L16 10l3.5-3.5L22 4l-1-2z"/></svg>
                                API Key <span style="color: var(--danger);">*</span>
                            </label>
                            <button type="button" onclick="toggleApiKeyVisibility()" style="background: none; border: none; color: var(--accent-1); font-size: 0.78rem; font-weight: 600; cursor: pointer; padding: 0;">
                                <span id="toggleKeyText">Tampilkan Key</span>
                            </button>
                        </div>
                        <input type="password" name="ai_api_key" id="ai_api_key" class="form-input" 
                            value="{{ $activeApiKey }}" required 
                            placeholder="Masukkan API Key..." 
                            style="width: 100%; font-family: monospace; letter-spacing: 1px;">
                        <div style="margin-top: 6px; font-size: 0.8rem; color: var(--text-muted);" id="apiKeyHelpText">
                            {{-- Injected dynamically --}}
                        </div>
                    </div>
                </div>

                {{-- Test Connection & Feedback Area --}}
                <div id="testResultBox" style="display: none; margin-top: 1.25rem; padding: 0.85rem 1rem; border-radius: 8px; font-size: 0.85rem;"></div>

                {{-- Action Toolbar --}}
                <div style="margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid var(--border-glass); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                    <button type="button" id="btnTestConnection" onclick="runTestConnection()" class="btn btn-outline" style="display: inline-flex; align-items: center; gap: 0.4rem; font-weight: 600;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 15px; height: 15px;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        Tes Koneksi API
                    </button>

                    <button type="submit" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.65rem 1.75rem; font-weight: 700;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 15px; height: 15px;"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        Simpan Konfigurasi AI
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- AI Knowledge Base Table -->
    <div class="data-card">
        <div class="data-card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
            <div>
                <span class="data-card-title">Basis Pengetahuan (AI Knowledge Base)</span>
                <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 0.2rem;">Data latih lokal khusus mengenai kebijakan, aturan desa, dan kontak darurat wilayah</div>
            </div>
            <a href="{{ route('ai-knowledge.create') }}" class="btn btn-primary btn-sm" style="display: inline-flex; align-items: center; gap: 0.35rem;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:15px;height:15px;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Tambah Referensi
            </a>
        </div>
        @if(isset($knowledge) && $knowledge->count() > 0)
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 25%;">Topik Referensi</th>
                            <th style="width: 45%;">Ringkasan Konten</th>
                            <th style="width: 15%;">Diperbarui Oleh</th>
                            <th style="width: 15%; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($knowledge as $item)
                        <tr>
                            <td style="font-weight: 700; color: var(--text-primary);">{{ $item->topic }}</td>
                            <td><span style="opacity: 0.85; font-size: 0.88rem; line-height: 1.45;">{{ Str::limit($item->content, 110) }}</span></td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <div style="width: 26px; height: 26px; background: var(--accent-1); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: bold;">
                                        {{ strtoupper(substr($item->updater->name ?? 'A', 0, 1)) }}
                                    </div>
                                    <span style="font-size: 0.85rem; font-weight: 500;">{{ $item->updater->name ?? 'Admin' }}</span>
                                </div>
                            </td>
                            <td style="text-align: center;">
                                <div style="display: inline-flex; gap: 0.5rem; align-items: center;">
                                    <a href="{{ route('ai-knowledge.edit', $item->id) }}" class="btn-icon btn-icon-edit" title="Edit Referensi AI">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 15px; height: 15px;">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                        </svg>
                                    </a>
                                    <form action="{{ route('ai-knowledge.destroy', $item->id) }}" method="POST" data-confirm="Apakah Anda yakin ingin menghapus referensi ini? AI tidak akan bisa lagi menjawab menggunakan data ini." style="margin: 0; display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-icon btn-icon-danger" title="Hapus Referensi AI" style="background: none; border: none; cursor: pointer;">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 15px; height: 15px;">
                                                <polyline points="3 6 5 6 21 6"></polyline>
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                <line x1="10" y1="11" x2="10" y2="17"></line>
                                                <line x1="14" y1="11" x2="14" y2="17"></line>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            @if($knowledge->hasPages())
            <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--border-glass);">
                {{ $knowledge->links() }}
            </div>
            @endif
        @else
            <div style="padding: 4rem 1.5rem; text-align: center; color: var(--text-muted);">
                <div style="width: 54px; height: 54px; border-radius: 50%; background: rgba(99, 102, 241, 0.08); color: var(--accent-1); display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="width: 28px; height: 28px;"><path d="m2 14 10-10 10 10"/><path d="M4 12v8h16v-8"/><path d="M10 12v5h4v-5"/></svg>
                </div>
                <h4 style="margin: 0 0 0.35rem 0; font-size: 1.1rem; color: var(--text-primary); font-weight: 700;">Knowledge Base AI Masih Kosong</h4>
                <p style="margin: 0 0 1.25rem 0; font-size: 0.85rem; max-width: 460px; margin-inline: auto;">
                    Tambahkan referensi dokumen desa, informasi layanan surat, atau peraturan agar chatbot AI dapat memberikan jawaban akurat kepada warga.
                </p>
                <a href="{{ route('ai-knowledge.create') }}" class="btn btn-outline btn-sm">
                    + Tambah Referensi Pertama
                </a>
            </div>
        @endif
    </div>

<script>
const PROVIDER_CONFIGS = {
    'gemini': {
        name: 'Google Gemini',
        models: [
            { id: 'gemini-2.5-flash', label: 'Gemini 2.5 Flash (Terbaru - Sangat Cepat & Cerdas)' },
            { id: 'gemini-2.0-flash', label: 'Gemini 2.0 Flash (Cepat & Responsif)' },
            { id: 'gemini-1.5-flash', label: 'Gemini 1.5 Flash (Stabil)' },
            { id: 'gemini-1.5-pro', label: 'Gemini 1.5 Pro (Penalaran Rumit / Kompleks)' },
            { id: 'custom', label: 'Ketik Manual / Model Lainnya...' }
        ],
        defaultModel: 'gemini-2.5-flash',
        keyPlaceholder: 'AIzaSyA...',
        keyHelp: 'Dapatkan API Key gratis di <a href="https://aistudio.google.com/app/apikey" target="_blank" style="color:var(--accent-1); font-weight:600; text-decoration:underline;">Google AI Studio &rarr;</a>',
        requiresBaseUrl: false
    },
    'deepseek': {
        name: 'DeepSeek AI',
        models: [
            { id: 'deepseek-chat', label: 'deepseek-chat (DeepSeek-V3 - Cepat, Ekonomis & Sangat Cerdas)' },
            { id: 'deepseek-reasoner', label: 'deepseek-reasoner (DeepSeek-R1 - Penalaran Logika & Chain-of-Thought)' },
            { id: 'custom', label: 'Ketik Manual / Model Lainnya...' }
        ],
        defaultModel: 'deepseek-chat',
        keyPlaceholder: 'sk-...',
        keyHelp: 'Dapatkan API Key di <a href="https://platform.deepseek.com/api_keys" target="_blank" style="color:var(--accent-1); font-weight:600; text-decoration:underline;">DeepSeek Platform &rarr;</a>',
        requiresBaseUrl: false
    },
    'groq': {
        name: 'Groq (Ultra-Fast Inference)',
        models: [
            { id: 'llama-3.3-70b-versatile', label: 'llama-3.3-70b-versatile (Direkomendasikan - Sangat Cepat & Akurat)' },
            { id: 'llama-3.1-8b-instant', label: 'llama-3.1-8b-instant (Super Kilat)' },
            { id: 'deepseek-r1-distill-llama-70b', label: 'deepseek-r1-distill-llama-70b (DeepSeek R1 via Groq)' },
            { id: 'mixtral-8x7b-32768', label: 'mixtral-8x7b-32768 (Konteks Panjang)' },
            { id: 'gemma2-9b-it', label: 'gemma2-9b-it (Google Gemma via Groq)' },
            { id: 'custom', label: 'Ketik Manual / Model Lainnya...' }
        ],
        defaultModel: 'llama-3.3-70b-versatile',
        keyPlaceholder: 'gsk_...',
        keyHelp: 'Dapatkan API Key gratis berkecepatan tinggi di <a href="https://console.groq.com/keys" target="_blank" style="color:var(--accent-1); font-weight:600; text-decoration:underline;">Groq Console &rarr;</a>',
        requiresBaseUrl: false
    },
    'xai': {
        name: 'xAI (Grok)',
        models: [
            { id: 'grok-2-latest', label: 'grok-2-latest (Grok 2 Terbaru)' },
            { id: 'grok-beta', label: 'grok-beta (Grok Beta)' },
            { id: 'custom', label: 'Ketik Manual / Model Lainnya...' }
        ],
        defaultModel: 'grok-2-latest',
        keyPlaceholder: 'xai-...',
        keyHelp: 'Dapatkan API Key di <a href="https://console.x.ai" target="_blank" style="color:var(--accent-1); font-weight:600; text-decoration:underline;">xAI Console &rarr;</a>',
        requiresBaseUrl: false
    },
    'openai': {
        name: 'OpenAI (ChatGPT)',
        models: [
            { id: 'gpt-4o-mini', label: 'gpt-4o-mini (Cepat, Akurat & Paling Hemat)' },
            { id: 'gpt-4o', label: 'gpt-4o (Kemampuan Tertinggi)' },
            { id: 'gpt-3.5-turbo', label: 'gpt-3.5-turbo (Legacy)' },
            { id: 'custom', label: 'Ketik Manual / Model Lainnya...' }
        ],
        defaultModel: 'gpt-4o-mini',
        keyPlaceholder: 'sk-proj-...',
        keyHelp: 'Dapatkan API Key di <a href="https://platform.openai.com/api-keys" target="_blank" style="color:var(--accent-1); font-weight:600; text-decoration:underline;">OpenAI Platform &rarr;</a>',
        requiresBaseUrl: false
    },
    'custom': {
        name: 'Custom OpenAI-Compatible API',
        models: [
            { id: 'custom', label: 'Gunakan Model Spesifik Sendiri' }
        ],
        defaultModel: 'custom',
        keyPlaceholder: 'API Key atau bearer token...',
        keyHelp: 'Bisa digunakan untuk endpoint lokal (Ollama / vLLM / LocalAI) atau penyedia multi-model seperti OpenRouter.',
        requiresBaseUrl: true
    }
};

const currentSavedModel = @json($activeModel);

function onProviderChange() {
    const providerSelect = document.getElementById('ai_provider');
    const provider = providerSelect.value;
    const config = PROVIDER_CONFIGS[provider] || PROVIDER_CONFIGS['gemini'];
    
    // Update active provider badge
    const badge = document.getElementById('activeProviderBadge');
    if (badge) badge.innerText = 'AKTIF: ' + provider.toUpperCase();

    // Populate model options
    const modelSelect = document.getElementById('ai_model_select');
    modelSelect.innerHTML = '';
    
    let matchedOption = false;
    config.models.forEach(m => {
        const opt = document.createElement('option');
        opt.value = m.id;
        opt.innerText = m.label;
        if (m.id === currentSavedModel) {
            opt.selected = true;
            matchedOption = true;
        }
        modelSelect.appendChild(opt);
    });

    // If current saved model isn't in predefined list, pick custom
    if (!matchedOption) {
        if (config.models.some(m => m.id === 'custom')) {
            modelSelect.value = 'custom';
            document.getElementById('ai_model_custom').value = currentSavedModel;
        } else {
            modelSelect.value = config.defaultModel;
        }
    }

    // Toggle custom model box & base URL box
    toggleCustomModelField();

    const baseUrlBox = document.getElementById('baseUrlBox');
    if (baseUrlBox) {
        baseUrlBox.style.display = config.requiresBaseUrl ? 'block' : 'none';
        document.getElementById('ai_base_url').required = config.requiresBaseUrl;
    }

    // Update API Key placeholder & help
    const apiKeyInput = document.getElementById('ai_api_key');
    if (apiKeyInput) {
        apiKeyInput.placeholder = config.keyPlaceholder;
    }
    const helpEl = document.getElementById('apiKeyHelpText');
    if (helpEl) {
        helpEl.innerHTML = config.keyHelp;
    }

    syncFinalModelValue();
}

function onModelSelectChange() {
    toggleCustomModelField();
    syncFinalModelValue();
}

function toggleCustomModelField() {
    const modelSelect = document.getElementById('ai_model_select');
    const customBox = document.getElementById('customModelBox');
    const isCustom = modelSelect.value === 'custom';
    customBox.style.display = isCustom ? 'block' : 'none';
    document.getElementById('ai_model_custom').required = isCustom;
}

function syncFinalModelValue() {
    const modelSelect = document.getElementById('ai_model_select');
    const customInput = document.getElementById('ai_model_custom');
    const finalInput = document.getElementById('ai_model');

    if (modelSelect.value === 'custom') {
        finalInput.value = customInput.value.trim();
    } else {
        finalInput.value = modelSelect.value;
    }
}

document.getElementById('ai_model_custom').addEventListener('input', syncFinalModelValue);

function toggleApiKeyVisibility() {
    const input = document.getElementById('ai_api_key');
    const text = document.getElementById('toggleKeyText');
    if (input.type === 'password') {
        input.type = 'text';
        text.innerText = 'Sembunyikan Key';
    } else {
        input.type = 'password';
        text.innerText = 'Tampilkan Key';
    }
}

async function runTestConnection() {
    syncFinalModelValue();
    const provider = document.getElementById('ai_provider').value;
    const model = document.getElementById('ai_model').value;
    const apiKey = document.getElementById('ai_api_key').value;
    const baseUrl = document.getElementById('ai_base_url').value;
    const resultBox = document.getElementById('testResultBox');
    const btnTest = document.getElementById('btnTestConnection');

    if (!apiKey) {
        alert('Silakan masukkan API Key terlebih dahulu.');
        document.getElementById('ai_api_key').focus();
        return;
    }

    if (!model) {
        alert('Silakan pilih atau ketik nama model AI.');
        return;
    }

    btnTest.disabled = true;
    btnTest.innerHTML = `
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;animation:spin 1s linear infinite;"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
        Menguji Koneksi ke ${provider.toUpperCase()}...
    `;

    resultBox.style.display = 'block';
    resultBox.style.background = 'rgba(99, 102, 241, 0.08)';
    resultBox.style.border = '1px solid rgba(99, 102, 241, 0.3)';
    resultBox.style.color = 'var(--text-primary)';
    resultBox.innerHTML = `<span>⏳ Mengirim sinyal pengujian ke endpoint ${provider}...</span>`;

    try {
        const response = await fetch("{{ route('ai-knowledge.test') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                ai_provider: provider,
                ai_model: model,
                ai_api_key: apiKey,
                ai_base_url: baseUrl
            })
        });

        const data = await response.json();

        if (response.ok && data.success) {
            resultBox.style.background = 'rgba(16, 185, 129, 0.12)';
            resultBox.style.border = '1px solid #10b981';
            resultBox.style.color = '#10b981';
            resultBox.innerHTML = `<strong>${data.message}</strong> - Siap digunakan oleh chatbot SIWIL.`;
        } else {
            resultBox.style.background = 'rgba(239, 68, 68, 0.12)';
            resultBox.style.border = '1px solid #ef4444';
            resultBox.style.color = '#ef4444';
            resultBox.innerHTML = `<strong>Error:</strong> ${data.message || 'Koneksi gagal.'}`;
        }
    } catch (err) {
        resultBox.style.background = 'rgba(239, 68, 68, 0.12)';
        resultBox.style.border = '1px solid #ef4444';
        resultBox.style.color = '#ef4444';
        resultBox.innerHTML = `<strong>Gagal:</strong> Terjadi kesalahan jaringan: ${err.message}`;
    } finally {
        btnTest.disabled = false;
        btnTest.innerHTML = `
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 15px; height: 15px;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            Tes Koneksi API
        `;
    }
}

// Inisialisasi tampilan saat halaman dimuat
document.addEventListener('DOMContentLoaded', function() {
    onProviderChange();
});
</script>
<style>
@keyframes spin { 100% { transform: rotate(360deg); } }
</style>
@endsection
