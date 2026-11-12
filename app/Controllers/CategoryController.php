<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Faculty;
use App\Models\Subject;
use App\Models\Document;
use App\Models\DocumentType;
use App\Core\Config;

class CategoryController extends Controller
{
    public function faculties(array $params = []): void
    {
        $facultyModel = new Faculty();
        $this->view('categories.faculties', [
            'title'    => 'Danh mục Khoa/Viện — TMU Tài Liệu',
            'faculties'=> $facultyModel->getAllWithSubjectCount(),
        ]);
    }

    public function faculty(array $params = []): void
    {
        $facultyModel = new Faculty();
        $subjectModel = new Subject();

        $faculty = $facultyModel->findById((int)$params['id']);
        if (!$faculty || !$faculty['is_active']) {
            $this->redirect('/faculties');
        }

        $this->view('categories.faculty', [
            'title'    => $faculty['name'] . ' — TMU Tài Liệu',
            'faculty'  => $faculty,
            'subjects' => $subjectModel->getByFaculty((int)$params['id']),
        ]);
    }

    public function subject(array $params = []): void
    {
        $subjectModel  = new Subject();
        $documentModel = new Document();
        $typeModel     = new DocumentType();

        $subject = $subjectModel->getWithFaculty((int)$params['id']);
        if (!$subject || !$subject['is_active'] || empty($subject['faculty_is_active'])) {
            $this->redirect('/faculties');
        }

        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = Config::get('pagination.per_page', 12);
        $filters = [
            'subject_id' => (int)$params['id'],
            'type_id'    => (int)($_GET['type'] ?? 0) ?: null,
            'sort'       => $_GET['sort'] ?? 'newest',
        ];

        $this->view('categories.subject', [
            'title'   => $subject['name'] . ' — TMU Tài Liệu',
            'subject' => $subject,
            'docs'    => $documentModel->search(array_filter($filters, fn($v) => $v !== null), $page, $perPage),
            'types'   => $typeModel->getActive(),
            'filters' => $filters,
        ]);
    }

    public function apiSubjects(array $params = []): void
    {
        $facultyId = (int)($_GET['faculty_id'] ?? 0);
        $subjectModel = new Subject();
        if ($facultyId > 0) {
            $faculty = (new Faculty())->findById($facultyId);
            $subjects = ($faculty && $faculty['is_active']) ? $subjectModel->getByFaculty($facultyId) : [];
        } else {
            $subjects = $subjectModel->getActive();
        }
        $this->json($subjects);
    }
}
