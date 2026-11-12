<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Document;
use App\Services\AiService;

class AiChatController extends Controller
{
    public function history(array $params = []): void
    {
        $aiService = new AiService();
        $messages  = $_SESSION['ai_chat'] ?? [];
        $this->json([
            'messages'   => $messages,
            'configured' => $aiService->isConfigured(),
            'provider'   => $aiService->getProvider(),
        ]);
    }

    public function chat(array $params = []): void
    {
        $this->csrfValidate();
        if (function_exists('set_time_limit')) {
            @set_time_limit(90);
        }

        // Simple per-session rate limiting
        $now  = time();
        $last = (int)($_SESSION['ai_chat_last_at'] ?? 0);
        if ($now - $last < 1) {
            $this->json(['error' => 'Bạn đang gửi quá nhanh, vui lòng đợi giây lát.'], 429);
        }
        $_SESSION['ai_chat_last_at'] = $now;

        $message = trim((string)($_POST['message'] ?? ''));
        if ($message === '') {
            $this->json(['error' => 'Vui lòng nhập nội dung câu hỏi.'], 422);
        }
        if (mb_strlen($message) > 1500) {
            $this->json(['error' => 'Câu hỏi quá dài (tối đa 1500 ký tự).'], 422);
        }

        $docId      = (int)($_POST['document_id'] ?? 0);
        $currentDoc = null;
        if ($docId > 0) {
            $doc = (new Document())->getWithDetails($docId);
            if ($doc) {
                $canView = $doc['status'] === 'approved'
                    || (auth() && (userId() === (int)$doc['user_id'] || isModerator()));
                if ($canView) {
                    $currentDoc = $doc;
                }
            }
        }

        $history   = $_SESSION['ai_chat'] ?? [];
        $aiService = new AiService();
        $result    = $aiService->reply($message, $currentDoc, $history);

        $history[] = [
            'role' => 'user',
            'text' => $message,
        ];
        $history[] = [
            'role'      => 'assistant',
            'text'      => $result['reply'],
            'documents' => $result['documents'],
        ];

        // Keep last 20 messages in session
        if (count($history) > 20) {
            $history = array_slice($history, -20);
        }
        $_SESSION['ai_chat'] = $history;

        $this->json($result);
    }

    public function clear(array $params = []): void
    {
        $this->csrfValidate();
        unset($_SESSION['ai_chat']);
        $this->json(['success' => true]);
    }
}
