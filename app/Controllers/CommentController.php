<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Comment;
use App\Models\Document;

class CommentController extends Controller
{
    public function store(array $params = []): void
    {
        $this->requireLogin();
        $this->csrfValidate();

        $docId    = (int)$params['id'];
        $doc      = (new Document())->getWithDetails($docId);
        if (!$doc || ($doc['status'] !== 'approved' && userId() !== (int)$doc['user_id'] && !isModerator())) {
            flash('error', 'Tài liệu không tồn tại hoặc không thể bình luận.');
            $this->redirect('/documents');
        }

        $content  = trim($_POST['content'] ?? '');
        $parentId = (int)($_POST['parent_id'] ?? 0) ?: null;

        if (mb_strlen($content) < 2) {
            flash('error', 'Bình luận quá ngắn.');
            $this->redirect('/documents/' . $docId . '#comments');
        }
        if (mb_strlen($content) > 2000) {
            flash('error', 'Bình luận quá dài (tối đa 2000 ký tự).');
            $this->redirect('/documents/' . $docId . '#comments');
        }

        $commentModel = new Comment();
        if ($parentId !== null) {
            $parent = $commentModel->findById($parentId);
            if (!$parent || (int)$parent['document_id'] !== $docId) {
                flash('error', 'Bình luận gốc không hợp lệ.');
                $this->redirect('/documents/' . $docId . '#comments');
            }

            // Khống chế tối đa 2 cấp bình luận:
            // Nếu bình luận cha vốn đã là một phản hồi (cấp 2 trở đi),
            // gán parent_id của bình luận mới thành parent_id của bình luận gốc (Root parent).
            if (!empty($parent['parent_id'])) {
                $parentId = $commentModel->getRootParentId($parentId);
            }
        }

        $commentModel->create([
            'document_id' => $docId,
            'user_id'     => userId(),
            'parent_id'   => $parentId,
            'content'     => $content,
        ]);

        flash('success', 'Bình luận đã được đăng.');
        $this->redirect('/documents/' . $docId . '#comments');
    }

    public function destroy(array $params = []): void
    {
        $this->requireLogin();
        $this->csrfValidate();

        $commentModel = new Comment();
        $comment = $commentModel->findById((int)$params['id']);

        if (!$comment) {
            $this->back();
        }

        if (userId() !== (int)$comment['user_id'] && !isModerator()) {
            http_response_code(403);
            die('Từ chối truy cập.');
        }

        $commentModel->delete($comment['id']);
        flash('success', 'Bình luận đã được xóa.');
        $this->redirect('/documents/' . $comment['document_id'] . '#comments');
    }
}

