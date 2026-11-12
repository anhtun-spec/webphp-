<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Config;
use App\Models\Document;
use App\Models\Faculty;
use App\Models\Subject;
use App\Models\DocumentType;

class SearchController extends Controller
{
    public function index(array $params = []): void
    {
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = Config::get('pagination.per_page', 12);

        $filters = [
            'query'         => trim($_GET['q'] ?? ''),
            'faculty_id'    => (int)($_GET['faculty'] ?? 0) ?: null,
            'subject_id'    => (int)($_GET['subject'] ?? 0) ?: null,
            'type_id'       => (int)($_GET['type'] ?? 0) ?: null,
            'academic_year' => trim($_GET['year'] ?? '') ?: null,
            'sort'          => $_GET['sort'] ?? 'newest',
        ];

        $documentModel = new Document();
        $results = $documentModel->search(
            array_filter($filters, fn($v) => $v !== null && $v !== ''),
            $page,
            $perPage
        );

        $this->view('search.results', [
            'title'     => ($filters['query'] ? '"' . $filters['query'] . '" — ' : '') . 'Tìm kiếm tài liệu — TMU Tài Liệu',
            'results'   => $results,
            'filters'   => $filters,
            'faculties' => (new Faculty())->getActive(),
            'subjects'  => (new Subject())->getActive(),
            'types'     => (new DocumentType())->getActive(),
        ]);
    }
}

