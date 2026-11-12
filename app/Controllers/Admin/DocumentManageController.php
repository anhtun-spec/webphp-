<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Document;
use App\Services\FileService;

class DocumentManageController extends Controller
{
    public function index(array $params = []): void
    {
        $this->requireRole('admin', 'moderator');

        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $status  = trim($_GET['status'] ?? '');
        $allowedStatuses = ['approved', 'pending', 'rejected', 'hidden', 'draft', 'archived'];
        $validStatus = in_array($status, $allowedStatuses, true) ? $status : '';

        $filters = [
            'query'  => trim($_GET['q'] ?? ''),
            'status' => $validStatus,
            'sort'   => 'newest',
        ];

        $searchFilters = [
            'query'  => $filters['query'],
            'status' => $validStatus !== '' ? $validStatus : 'all',
            'sort'   => 'newest',
        ];

        $docModel = new Document();
        $allDocs  = $docModel->search($searchFilters, $page, $perPage);

        $this->view('admin.documents.index', [
            'title'   => 'Quản lý tài liệu — Admin TMU',
            'docs'    => $allDocs,
            'filters' => $filters,
        ], 'admin');
    }

    public function destroy(array $params = []): void
    {
        $this->requireRole('admin');
        $this->csrfValidate();

        $docId    = (int)$params['id'];
        $docModel = new Document();
        $doc      = $docModel->findById($docId);

        if ($doc && empty($doc['deleted_at'])) {
            $docModel->softDelete($docId);
            if (!empty($doc['file_path']) && !$docModel->isFilePathInUse($doc['file_path'], $docId)) {
                $fileService = new FileService();
                $fileService->deleteFile($doc['file_path']);
            }
            flash('success', 'Tài liệu đã được xóa.');
        }

        $this->redirect('/admin/documents');
    }
}

