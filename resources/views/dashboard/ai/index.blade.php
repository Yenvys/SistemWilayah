@extends('layouts.app')

@section('title', 'Layanan Mandiri SIWIL')
@section('page-title', 'Layanan Mandiri SIWIL')

@section('content')
    <style>
        .chat-container {
            display: flex; flex-direction: column;
            height: calc(100vh - 160px);
            background: var(--bg-card);
            border-radius: 20px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), inset 0 1px 0 rgba(255,255,255,0.05);
            overflow: hidden;
            border: 1px solid var(--border-glass);
            color: var(--text-primary);
            font-family: 'Outfit', sans-serif;
            position: relative;
        }
        .chat-header {
            display: flex; justify-content: space-between; align-items: center;
            padding: 1.25rem 1.5rem; 
            background: var(--bg-glass);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border-glass);
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
            z-index: 10;
        }
        .chat-title {
            font-weight: 700; color: var(--accent-1);
            font-size: 1.1rem; letter-spacing: 0.5px;
            display: flex; align-items: center; gap: 8px;
        }
        .chat-title svg { width: 20px; height: 20px; fill: currentColor; }
        
        .reset-btn {
            background: transparent; color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 8px;
            padding: 0.4rem 0.8rem; font-size: 0.85rem; cursor: pointer;
            transition: all 0.3s ease; display: flex; align-items: center; gap: 6px;
        }
        .reset-btn:hover {
            background: #ef4444; color: white;
            box-shadow: 0 0 15px rgba(239, 68, 68, 0.4);
            border-color: #ef4444;
        }

        .chat-box {
            flex: 1; padding: 1.5rem;
            overflow-y: auto;
            background: var(--bg-body);
            display: flex; flex-direction: column; gap: 1.2rem;
            position: relative;
        }
        
        .msg {
            max-width: 85%; padding: 1rem 1.25rem; border-radius: 16px;
            font-size: 0.95rem; line-height: 1.6;
            position: relative; z-index: 1;
        }
        .msg-user {
            align-self: flex-end;
            background: var(--accent-gradient, var(--accent-1)); color: white;
            border-bottom-right-radius: 4px;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.2);
        }
        .msg-ai {
            align-self: flex-start;
            background: var(--bg-card); color: var(--text-primary);
            border: 1px solid var(--border-glass);
            border-bottom-left-radius: 4px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        
        .chat-options-container {
            display: flex; flex-direction: column; gap: 0.6rem;
            margin-top: 1rem;
        }
        .chat-option-btn {
            background: var(--bg-body); color: var(--text-secondary);
            border: 1px solid var(--border-glass); border-radius: 10px;
            padding: 0.75rem 1rem; text-align: left;
            cursor: pointer; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-size: 0.9rem; display: flex; align-items: center; gap: 10px;
        }
        .chat-option-btn:hover {
            background: var(--bg-glass-hover, var(--bg-glass)); color: var(--accent-1);
            border-color: var(--accent-1);
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.1);
            transform: translateX(4px);
        }
        .chat-option-btn svg { width: 18px; height: 18px; fill: currentColor; }
        
        .chat-loading {
            display: none;
            align-self: flex-start;
            background: var(--bg-card);
            border: 1px solid var(--border-glass);
            border-radius: 16px; border-bottom-left-radius: 4px;
            padding: 14px 18px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        
        .typing-indicator {
            display: flex; gap: 4px; align-items: center;
        }
        .typing-indicator span {
            width: 8px; height: 8px; background: var(--text-muted);
            border-radius: 50%;
            animation: bounce 1.4s infinite ease-in-out both;
        }
        .typing-indicator span:nth-child(1) { animation-delay: -0.32s; }
        .typing-indicator span:nth-child(2) { animation-delay: -0.16s; }
        
        @keyframes bounce {
            0%, 80%, 100% { transform: scale(0); opacity: 0.3; }
            40% { transform: scale(1); opacity: 1; }
        }

        .chat-input-area {
            padding: 1.25rem 1.5rem; background: var(--bg-glass);
            backdrop-filter: blur(10px);
            border-top: 1px solid var(--border-glass);
            display: flex; gap: 0.75rem; z-index: 10;
        }
        .chat-input {
            flex: 1; padding: 0.85rem 1.25rem;
            border: 1px solid var(--border-glass); border-radius: 24px;
            outline: none; font-size: 0.95rem;
            background: var(--bg-body); color: var(--text-primary);
            transition: all 0.3s;
        }
        .chat-input:focus { 
            border-color: var(--accent-1); 
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
            background: var(--bg-card);
        }
        .chat-input::placeholder { color: var(--text-muted); }
        
        .chat-btn {
            background: var(--accent-gradient, var(--accent-1)); color: white;
            border: none; border-radius: 24px;
            padding: 0 1.5rem; cursor: pointer;
            font-weight: 600; transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3);
        }
        .chat-btn:hover { 
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.4);
            filter: brightness(110%);
        }
        .chat-btn:disabled { 
            background: var(--border-glass); color: var(--text-muted); 
            box-shadow: none; transform: none; cursor: not-allowed; 
        }

        /* ===== RESPONSIVE ===== */

        /* Tablet */
        @media (max-width: 768px) {
            .chat-container {
                height: calc(100vh - 120px);
                border-radius: 14px;
            }
            .chat-header {
                padding: 1rem 1.25rem;
            }
            .chat-title { font-size: 1rem; }
            .chat-box { padding: 1rem; gap: 1rem; }
            .msg { max-width: 90%; padding: 0.85rem 1rem; font-size: 0.9rem; border-radius: 14px; }
            .chat-option-btn { padding: 0.65rem 0.85rem; font-size: 0.85rem; }
            .chat-input-area { padding: 1rem 1.25rem; }
            .chat-input { padding: 0.75rem 1rem; font-size: 0.9rem; }
            .chat-btn { padding: 0 1.25rem; font-size: 0.9rem; }
        }

        /* Mobile */
        @media (max-width: 480px) {
            .chat-container {
                height: calc(100vh - 100px);
                border-radius: 10px;
            }
            .chat-header {
                padding: 0.75rem 1rem;
            }
            .chat-title { font-size: 0.95rem; gap: 6px; }
            .chat-title svg { width: 16px; height: 16px; }
            .reset-btn { padding: 0.3rem 0.6rem; font-size: 0.8rem; }
            .chat-box { padding: 0.75rem; gap: 0.8rem; }
            .msg { max-width: 92%; padding: 0.75rem 0.85rem; font-size: 0.85rem; border-radius: 12px; line-height: 1.5; }
            .chat-options-container { gap: 0.4rem; margin-top: 0.75rem; }
            .chat-option-btn { padding: 0.6rem 0.75rem; font-size: 0.82rem; gap: 8px; border-radius: 8px; }
            .chat-option-btn svg { width: 16px; height: 16px; }
            .chat-option-btn:hover { transform: none; }
            .chat-input-area { padding: 0.75rem; gap: 0.5rem; }
            .chat-input { padding: 0.65rem 0.85rem; font-size: 0.85rem; border-radius: 20px; }
            .chat-btn { padding: 0 1rem; font-size: 0.85rem; border-radius: 20px; }
            .chat-btn:hover { transform: none; }
        }
    </style>

    <div class="chat-container">
        <div class="chat-header">
            <div class="chat-title">
                <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v6h-2zm0 8h2v2h-2z"/></svg>
                SIWIL AI
            </div>
            <button class="reset-btn" type="button" onclick="resetChat()">
                <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor"><path d="M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z"/></svg>
                Reset
            </button>
        </div>
        <div class="chat-box" id="chatBox">
            <div class="chat-loading" id="chatLoading">
                <div class="typing-indicator">
                    <span></span><span></span><span></span>
                </div>
            </div>
        </div>
        
        <form class="chat-input-area" id="chatForm">
            <input type="text" id="chatInput" class="chat-input" placeholder="Tanya sesuatu tentang data desa..." autocomplete="off" required>
            <button type="submit" id="chatBtn" class="chat-btn">Kirim</button>
        </form>
    </div>

    <script>
        function formatMarkdown(text) {
            let html = text
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');
            html = html.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
            html = html.replace(/`([^`]+)`/g, '<code style="background:rgba(99,102,241,0.1);padding:2px 6px;border-radius:4px;font-size:0.85em;color:var(--accent-1);">$1</code>');
            html = html.replace(/(?<!\w)_(.+?)_(?!\w)/g, '<em>$1</em>');
            html = html.replace(/\n/g, '<br>');
            return html;
        }

        const chatBox = document.getElementById('chatBox');
        const chatLoading = document.getElementById('chatLoading');

        function getSvgIcon(iconName) {
            const icons = {
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
                'default': '<svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>'
            };
            return icons[iconName] || icons['default'];
        }

        // Track input mode state for multi-step flows (e.g., complaint creation)
        let currentInputMode = null;

        function appendBotMessage(text, options, inputMode, inputTarget, inputData) {
            const wrapper = document.createElement('div');
            wrapper.className = 'msg msg-ai';
            
            let html = `<strong style="color: var(--accent-1); display: flex; align-items: center; gap: 6px; margin-bottom: 6px;"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v6h-2zm0 8h2v2h-2z"/></svg>SIWIL AI</strong>${formatMarkdown(text)}`;
            
            if (options && options.length > 0) {
                let optionsHtml = '<div class="chat-options-container">';
                options.forEach((opt, index) => {
                    const dataStr = opt.data ? JSON.stringify(opt.data).replace(/"/g, '&quot;') : '{}';
                    const iconSvg = getSvgIcon(opt.icon);
                    optionsHtml += `<button class="chat-option-btn" onclick="selectOption('${opt.label.replace(/'/g, "\\'").replace(/"/g, '&quot;')}', '${opt.next_node}', ${dataStr})">${iconSvg} <span>${opt.label}</span></button>`;
                });
                optionsHtml += '</div>';
                html += optionsHtml;
            }
            
            wrapper.innerHTML = html;
            chatBox.insertBefore(wrapper, chatLoading);
            chatBox.scrollTop = chatBox.scrollHeight;

            // Set input mode if the server requests text input
            if (inputMode === 'text' && inputTarget) {
                currentInputMode = {
                    target: inputTarget,
                    data: inputData || {}
                };
                document.getElementById('chatInput').placeholder = 'Ketik jawaban Anda di sini...';
                document.getElementById('chatInput').focus();
            } else {
                currentInputMode = null;
                document.getElementById('chatInput').placeholder = 'Tanya sesuatu tentang data desa...';
            }
        }

        function appendUserMessage(text) {
            const wrapper = document.createElement('div');
            wrapper.className = 'msg msg-user';
            wrapper.innerText = text;
            chatBox.insertBefore(wrapper, chatLoading);
            chatBox.scrollTop = chatBox.scrollHeight;
        }

        async function fetchNode(nodeId, data = {}) {
            chatLoading.style.display = 'block';
            chatBox.scrollTop = chatBox.scrollHeight;

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
                chatLoading.style.display = 'none';
                
                if (response.ok) {
                    appendBotMessage(result.message, result.options, result.input_mode, result.input_target, result.input_data);
                } else {
                    appendBotMessage('Terjadi kesalahan: ' + (result.message || 'Server error'), [
                        { label: 'Mulai Ulang', next_node: 'start' }
                    ]);
                }
            } catch (error) {
                chatLoading.style.display = 'none';
                appendBotMessage('Koneksi terputus. Silakan coba lagi.', [
                    { label: 'Mulai Ulang', next_node: 'start' }
                ]);
            }
        }

        window.selectOption = function(label, nextNode, data) {
            // Remove previous options so user can't click them again
            const allOptionContainers = document.querySelectorAll('.chat-options-container');
            if (allOptionContainers.length > 0) {
                const lastContainer = allOptionContainers[allOptionContainers.length - 1];
                lastContainer.remove();
            }

            appendUserMessage(label);
            fetchNode(nextNode, data);
        };

        // Start the conversation
        document.addEventListener('DOMContentLoaded', () => {
            fetchNode('start');
        });

        function resetChat() {
            chatBox.innerHTML = '';
            chatBox.appendChild(chatLoading);
            currentInputMode = null;
            document.getElementById('chatInput').placeholder = 'Tanya sesuatu tentang data desa...';
            fetchNode('start');
        }

        // Handle Free Text Submission
        async function sendFreeTextMessage(messageText) {
            const message = messageText.trim();
            if (!message) return;

            const input = document.getElementById('chatInput');
            const btn = document.getElementById('chatBtn');
            
            // Remove previous options so user can't click them again
            const allOptionContainers = document.querySelectorAll('.chat-options-container');
            if (allOptionContainers.length > 0) {
                const lastContainer = allOptionContainers[allOptionContainers.length - 1];
                lastContainer.remove();
            }

            appendUserMessage(message);
            input.value = '';
            btn.disabled = true;
            btn.innerText = 'Memproses...';
            chatLoading.style.display = 'block';

            // If we're in input mode, send to the target node instead of Gemini AI
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
                    chatLoading.style.display = 'none';

                    if (response.ok) {
                        appendBotMessage(result.message, result.options, result.input_mode, result.input_target, result.input_data);
                    } else {
                        appendBotMessage('Terjadi kesalahan.', [{ label: 'Mulai Ulang', next_node: 'start' }]);
                    }
                } catch (error) {
                    chatLoading.style.display = 'none';
                    appendBotMessage('Koneksi terputus.', [{ label: 'Mulai Ulang', next_node: 'start' }]);
                }

                btn.disabled = false;
                btn.innerText = 'Kirim';
                return;
            }

            // Regular Gemini AI message
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
                chatLoading.style.display = 'none';
                
                if (response.ok) {
                    appendBotMessage(result.message, result.options, result.input_mode, result.input_target, result.input_data);
                } else {
                    appendBotMessage('Terjadi kesalahan saat menghubungi AI.', [{ label: 'Mulai Ulang', next_node: 'start' }]);
                }
            } catch (error) {
                chatLoading.style.display = 'none';
                appendBotMessage('Koneksi terputus. Silakan coba lagi.', [{ label: 'Mulai Ulang', next_node: 'start' }]);
            }

            btn.disabled = false;
            btn.innerText = 'Kirim';
        }

        document.getElementById('chatForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const input = document.getElementById('chatInput');
            sendFreeTextMessage(input.value);
        });
    </script>
@endsection
