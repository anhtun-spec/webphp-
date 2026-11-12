<?php declare(strict_types=1); ?>
<?php
$aiService    = new \App\Services\AiService();
$aiConfigured = $aiService->isConfigured();
$aiProvider   = $aiService->getProvider();
$aiModeText   = match ($aiProvider) {
    'openrouter' => 'OpenRouter AI • Sẵn sàng',
    'gemini'     => 'Gemini AI • Sẵn sàng',
    default      => 'Tra cứu TMU • Sẵn sàng',
};
?>
<!-- AI Chatbot Widget (Bottom-Left) -->
<div id="tmuAiChatbot" class="ai-chatbot-root" data-configured="<?= $aiConfigured ? '1' : '0' ?>">
    <!-- Launcher Button -->
    <button type="button" id="aiChatToggle" class="ai-chat-launcher shadow-lg" aria-expanded="false" aria-controls="aiChatWindow" title="Mở Trợ lý AI TMU">
        <span class="ai-launcher-icon d-inline-flex align-items-center justify-content-center">
            <i class="bi bi-robot fs-5"></i>
        </span>
        <span class="ai-launcher-label d-none d-sm-inline fw-semibold">Trợ lý AI</span>
    </button>

    <!-- Chat Popup Window -->
    <section id="aiChatWindow" class="ai-chat-window card border-0 shadow-lg d-none" aria-label="Trợ lý AI TMU">
        <div class="ai-chat-header d-flex align-items-center justify-content-between px-3 py-2 text-white">
            <div class="d-flex align-items-center gap-2 min-width-0">
                <span class="ai-header-avatar rounded-circle d-inline-flex align-items-center justify-content-center flex-shrink-0">
                    <i class="bi bi-stars"></i>
                </span>
                <div class="text-truncate">
                    <div class="fw-bold small lh-sm">Trợ lý AI TMU</div>
                    <div class="ai-status-text" style="font-size: 0.7rem; opacity: 0.88;" id="aiChatModeLabel">
                        <?= e($aiModeText) ?>
                    </div>
                </div>
            </div>
            <div class="d-flex align-items-center gap-1 flex-shrink-0">
                <button type="button" id="aiChatClear" class="btn btn-sm text-white-50 p-1" title="Xóa đoạn chat">
                    <i class="bi bi-trash3"></i>
                </button>
                <button type="button" id="aiChatClose" class="btn btn-sm text-white p-1" title="Thu gọn">
                    <i class="bi bi-dash-lg"></i>
                </button>
            </div>
        </div>

        <!-- Optional Document Context Banner -->
        <div id="aiChatDocContext" class="ai-doc-context px-3 py-2 small border-bottom d-none">
            <div class="d-flex align-items-center gap-2 text-primary fw-semibold text-truncate">
                <i class="bi bi-file-earmark-text flex-shrink-0"></i>
                <span id="aiChatDocTitle" class="text-truncate"></span>
            </div>
        </div>

        <!-- Message Feed -->
        <div id="aiChatMessages" class="ai-chat-messages p-3 flex-grow-1 overflow-auto"></div>

        <!-- Quick Suggestions -->
        <div id="aiChatChips" class="ai-chat-chips px-3 py-2 border-top bg-light d-flex flex-wrap gap-1"></div>

        <!-- Input Bar -->
        <form id="aiChatForm" class="ai-chat-form p-2 border-top bg-white">
            <div class="input-group input-group-sm align-items-end">
                <textarea id="aiChatInput" class="form-control border-0 bg-light rounded-3 px-3 py-2"
                          rows="1" maxlength="1500"
                          placeholder="Hỏi về tài liệu, môn học TMU..."
                          style="resize: none; max-height: 96px;"></textarea>
                <button type="submit" id="aiChatSend" class="btn btn-primary rounded-3 ms-2 px-3 py-2" title="Gửi">
                    <i class="bi bi-send-fill"></i>
                </button>
            </div>
        </form>
    </section>
</div>
