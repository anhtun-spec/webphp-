/* TMU Tài Liệu - Bottom-Left AI Chatbot Widget */

document.addEventListener('DOMContentLoaded', function() {
    const root = document.getElementById('tmuAiChatbot');
    if (!root) return;

    const toggleBtn   = document.getElementById('aiChatToggle');
    const chatWindow  = document.getElementById('aiChatWindow');
    const closeBtn    = document.getElementById('aiChatClose');
    const clearBtn    = document.getElementById('aiChatClear');
    const messagesEl  = document.getElementById('aiChatMessages');
    const chipsEl     = document.getElementById('aiChatChips');
    const formEl      = document.getElementById('aiChatForm');
    const inputEl     = document.getElementById('aiChatInput');
    const sendBtn     = document.getElementById('aiChatSend');
    const docBanner   = document.getElementById('aiChatDocContext');
    const docTitleEl  = document.getElementById('aiChatDocTitle');
    const modeLabelEl = document.getElementById('aiChatModeLabel');

    let isSending = false;
    let historyLoaded = false;

    function getDocContext() {
        return window.TMU_DOC_CONTEXT || null;
    }

    function syncDocContext() {
        const docContext = getDocContext();
        if (docContext && docContext.id && docContext.title) {
            if (docBanner && docTitleEl) {
                docBanner.classList.remove('d-none');
                docTitleEl.textContent = 'Đang xem: ' + docContext.title;
                docTitleEl.title = docContext.title;
            }
        } else if (docBanner) {
            docBanner.classList.add('d-none');
        }
        renderChips();
    }

    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.content : '';
    }

    function escapeHtml(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function parseTableRow(line) {
        return line.trim().replace(/^\|/, '').replace(/\|$/, '').split('|').map(c => c.trim());
    }

    function isTableSeparator(line) {
        return /^\|?\s*:?-{2,}:?\s*(\|\s*:?-{2,}:?\s*)+\|?$/.test(line.trim());
    }

    function renderMarkdownTable(tableLines) {
        const headers = parseTableRow(tableLines[0]);
        const startIdx = (tableLines.length > 1 && isTableSeparator(tableLines[1])) ? 2 : 1;
        let html = '<div class="table-responsive my-2"><table class="table table-sm table-bordered ai-md-table mb-0">';
        html += '<thead><tr>' + headers.map(h => '<th>' + applyInlineMarkdown(h) + '</th>').join('') + '</tr></thead><tbody>';
        for (let r = startIdx; r < tableLines.length; r++) {
            const cells = parseTableRow(tableLines[r]);
            html += '<tr>' + headers.map((_, idx) => '<td>' + applyInlineMarkdown(cells[idx] ?? '') + '</td>').join('') + '</tr>';
        }
        html += '</tbody></table></div>';
        return html;
    }

    function formatAssistantText(text) {
        const lines = escapeHtml(text).split(/\r?\n/);
        const rendered = [];

        for (let i = 0; i < lines.length; i++) {
            const line = lines[i];
            if (line.trim().startsWith('|') && i + 1 < lines.length && isTableSeparator(lines[i + 1])) {
                const tableLines = [line, lines[i + 1]];
                i += 2;
                while (i < lines.length && lines[i].trim().startsWith('|')) {
                    tableLines.push(lines[i]);
                    i++;
                }
                i--;
                rendered.push(renderMarkdownTable(tableLines));
                continue;
            }
            if (/^---+$/.test(line.trim())) {
                rendered.push('<hr class="my-2 opacity-25">');
                continue;
            }
            const heading = line.match(/^#{1,4}\s+(.+)$/);
            if (heading) {
                rendered.push('<div class="fw-bold text-primary mt-2 mb-1">' + applyInlineMarkdown(heading[1]) + '</div>');
                continue;
            }
            const quote = line.match(/^&gt;\s*(.+)$/);
            if (quote) {
                rendered.push('<div class="border-start border-3 border-primary ps-2 text-muted small my-1">' + applyInlineMarkdown(quote[1]) + '</div>');
                continue;
            }
            const bullet = line.match(/^\s*[-*]\s+(.+)$/);
            if (bullet) {
                rendered.push('<div class="d-flex gap-1 ps-1"><span class="text-primary">&bull;</span><span>' + applyInlineMarkdown(bullet[1]) + '</span></div>');
                continue;
            }
            const numbered = line.match(/^\s*(\d+\.)\s+(.+)$/);
            if (numbered) {
                rendered.push('<div class="d-flex gap-1 ps-1"><span class="fw-semibold text-primary">' + numbered[1] + '</span><span>' + applyInlineMarkdown(numbered[2]) + '</span></div>');
                continue;
            }
            rendered.push(line === '' ? '<div class="my-1"></div>' : '<div>' + applyInlineMarkdown(line) + '</div>');
        }

        return rendered.join('');
    }

    function applyInlineMarkdown(line) {
        return line
            .replace(/&lt;br\s*\/?&gt;/gi, '<br>')
            .replace(/\[([^\]]+)\]\(((?:https?:\/\/|\/)[^\s)]+)\)/g, '<a href="$2" class="text-primary fw-semibold text-decoration-underline">$1</a>')
            .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
            .replace(/`([^`]+)`/g, '<code class="px-1 rounded bg-light text-primary">$1</code>')
            .replace(/\*(.+?)\*/g, '<em>$1</em>');
    }

    function renderChips() {
        if (!chipsEl) return;
        const docContext = getDocContext();
        const prompts = docContext && docContext.id
            ? [
                'Tóm tắt tài liệu này',
                'Tạo 5 câu hỏi ôn tập',
                'Giải thích nội dung chính'
            ]
            : [
                'Tìm tài liệu nổi bật',
                'Các Khoa và môn học TMU',
                'Hướng dẫn đăng tài liệu'
            ];

        chipsEl.innerHTML = '';
        prompts.forEach(function(label) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'btn btn-outline-primary btn-sm ai-chip-btn';
            btn.textContent = label;
            btn.addEventListener('click', function() {
                sendMessage(label);
            });
            chipsEl.appendChild(btn);
        });
    }

    function appendWelcomeMessage() {
        const docContext = getDocContext();
        const welcome = docContext && docContext.id
            ? 'Xin chào! Bạn đang xem tài liệu **' + docContext.title + '**. Mình chỉ hỗ trợ tóm tắt, giải thích nội dung và tạo câu hỏi ôn tập cho tài liệu này hoặc hướng dẫn dùng website.'
            : 'Xin chào! Mình là **Trợ lý AI TMU**. Mình chỉ hỗ trợ các câu hỏi về **website TMU Tài Liệu**, **tìm kiếm** và **học tập trên tài liệu/môn học TMU**.';
        appendMessage('assistant', welcome, []);
    }

    function appendMessage(role, text, documents) {
        const wrap = document.createElement('div');
        wrap.className = role === 'user'
            ? 'd-flex justify-content-end mb-2'
            : 'd-flex flex-column align-items-start mb-3';

        const bubble = document.createElement('div');
        bubble.className = role === 'user'
            ? 'ai-msg-bubble ai-msg-user'
            : 'ai-msg-bubble ai-msg-assistant';

        if (role === 'user') {
            bubble.textContent = text;
        } else {
            bubble.innerHTML = formatAssistantText(text);
        }
        wrap.appendChild(bubble);

        if (role === 'assistant' && Array.isArray(documents) && documents.length > 0) {
            const list = document.createElement('div');
            list.className = 'ai-doc-results mt-2 w-100 d-flex flex-column gap-1';
            documents.forEach(function(doc) {
                const link = document.createElement('a');
                link.href = doc.url;
                link.className = 'ai-doc-card text-decoration-none p-2 rounded border bg-white';
                link.innerHTML =
                    '<div class="fw-semibold text-dark small text-truncate">' + escapeHtml(doc.title) + '</div>' +
                    '<div class="d-flex justify-content-between align-items-center mt-1" style="font-size:0.72rem;">' +
                        '<span class="text-primary text-truncate me-2">' + escapeHtml(doc.type_name) + ' • ' + escapeHtml(doc.subject_name) + '</span>' +
                        '<span class="text-muted flex-shrink-0"><i class="bi bi-download me-1"></i>' + Number(doc.download_count || 0) + '</span>' +
                    '</div>';
                list.appendChild(link);
            });
            wrap.appendChild(list);
        }

        messagesEl.appendChild(wrap);
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    function showTyping() {
        const el = document.createElement('div');
        el.id = 'aiTypingIndicator';
        el.className = 'd-flex justify-content-start mb-2';
        el.innerHTML = '<div class="ai-msg-bubble ai-msg-assistant text-muted small"><span class="spinner-grow spinner-grow-sm me-2 text-primary" style="width:.7rem;height:.7rem;"></span>Đang xử lý...</div>';
        messagesEl.appendChild(el);
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    function hideTyping() {
        document.getElementById('aiTypingIndicator')?.remove();
    }

    function modeLabelText(mode) {
        if (mode === 'openrouter') return 'OpenRouter AI • Sẵn sàng';
        if (mode === 'gemini') return 'Gemini AI • Sẵn sàng';
        return 'Tra cứu TMU • Sẵn sàng';
    }

    async function loadHistory() {
        if (historyLoaded) return;
        historyLoaded = true;
        try {
            const res = await fetch(url('api/ai/history'), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();
            if (modeLabelEl) {
                modeLabelEl.textContent = modeLabelText(data.provider || (data.configured ? 'openrouter' : 'local'));
            }
            messagesEl.innerHTML = '';
            if (Array.isArray(data.messages) && data.messages.length > 0) {
                data.messages.forEach(function(msg) {
                    appendMessage(msg.role, msg.text, msg.documents || []);
                });
            } else {
                appendWelcomeMessage();
            }
        } catch (err) {
            messagesEl.innerHTML = '';
            appendWelcomeMessage();
        }
    }

    async function sendMessage(rawText) {
        const message = (rawText || '').trim();
        if (!message || isSending) return;

        isSending = true;
        sendBtn.disabled = true;
        inputEl.value = '';
        inputEl.style.height = 'auto';

        appendMessage('user', message, []);
        showTyping();

        try {
            const body = new URLSearchParams();
            body.append('_csrf_token', getCsrfToken());
            body.append('message', message);
            const docContext = getDocContext();
            if (docContext && docContext.id) {
                body.append('document_id', String(docContext.id));
            }

            const res = await fetch(url('api/ai/chat'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: body.toString()
            });

            const data = await res.json();
            hideTyping();

            if (!res.ok) {
                appendMessage('assistant', data.error || 'Đã xảy ra lỗi, vui lòng thử lại.', []);
            } else {
                if (modeLabelEl) {
                    modeLabelEl.textContent = modeLabelText(data.mode);
                }
                appendMessage('assistant', data.reply, data.documents || []);
            }
        } catch (err) {
            hideTyping();
            appendMessage('assistant', 'Không thể kết nối máy chủ. Vui lòng thử lại sau.', []);
        } finally {
            isSending = false;
            sendBtn.disabled = false;
            inputEl.focus();
        }
    }

    function openChat() {
        chatWindow.classList.remove('d-none');
        toggleBtn.setAttribute('aria-expanded', 'true');
        sessionStorage.setItem('tmu_ai_open', '1');
        syncDocContext();
        loadHistory();
        setTimeout(() => inputEl.focus(), 50);
    }

    function closeChat() {
        chatWindow.classList.add('d-none');
        toggleBtn.setAttribute('aria-expanded', 'false');
        sessionStorage.setItem('tmu_ai_open', '0');
    }

    toggleBtn?.addEventListener('click', function() {
        if (chatWindow.classList.contains('d-none')) {
            openChat();
        } else {
            closeChat();
        }
    });

    closeBtn?.addEventListener('click', closeChat);

    clearBtn?.addEventListener('click', async function() {
        try {
            const body = new URLSearchParams();
            body.append('_csrf_token', getCsrfToken());
            await fetch(url('api/ai/clear'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: body.toString()
            });
        } catch (e) {}
        messagesEl.innerHTML = '';
        appendWelcomeMessage();
    });

    formEl?.addEventListener('submit', function(e) {
        e.preventDefault();
        sendMessage(inputEl.value);
    });

    inputEl?.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage(inputEl.value);
        }
    });

    inputEl?.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 96) + 'px';
    });

    syncDocContext();
    if (sessionStorage.getItem('tmu_ai_open') === '1') {
        openChat();
    }
});
