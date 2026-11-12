<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Document;
use App\Models\Faculty;

class HomeController extends Controller
{
    public function index(array $params = []): void
    {
        $documentModel = new Document();
        $facultyModel  = new Faculty();

        $this->view('home.index', [
            'title'           => 'TMU Tài Liệu — Nền tảng chia sẻ tài liệu học tập',
            'recentDocuments' => $documentModel->getRecent(8),
            'popularDocuments'=> $documentModel->getPopular(8),
            'faculties'       => $facultyModel->getAllWithSubjectCount(),
        ]);
    }
}

