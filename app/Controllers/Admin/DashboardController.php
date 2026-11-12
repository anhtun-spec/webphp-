<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\User;
use App\Models\Document;
use App\Models\Faculty;
use App\Models\Subject;
use App\Models\DownloadHistory;

class DashboardController extends Controller
{
    public function index(array $params = []): void
    {
        $this->requireRole('admin', 'moderator');

        $userModel     = new User();
        $documentModel = new Document();
        $facultyModel  = new Faculty();
        $subjectModel  = new Subject();
        $histModel     = new DownloadHistory();

        $this->view('admin.dashboard', [
            'title'          => 'Bảng điều khiển — Admin TMU',
            'userStats'      => $userModel->getStats(),
            'documentStats'  => $documentModel->getStats(),
            'totalFaculties' => $facultyModel->count(['is_active' => 1]),
            'totalSubjects'  => $subjectModel->count(['is_active' => 1]),
            'totalDownloads' => $histModel->getTotalDownloads(),
        ], 'admin');
    }
}

