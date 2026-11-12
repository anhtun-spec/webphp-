<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\User;
use App\Models\Document;
use App\Models\Faculty;
use App\Models\Subject;
use App\Models\DownloadHistory;
use App\Core\Database;

class StatisticsController extends Controller
{
    public function index(array $params = []): void
    {
        $this->requireRole('admin', 'moderator');

        $db = Database::getInstance();

        // Top 10 most downloaded docs
        $topDownloads = $db->fetchAll(
            "SELECT d.id, d.title, d.download_count, d.view_count,
                    s.name AS subject_name, u.full_name AS uploader
             FROM documents d
             LEFT JOIN subjects s ON d.subject_id = s.id
             LEFT JOIN users u ON d.user_id = u.id
             WHERE d.status = 'approved' AND d.deleted_at IS NULL
             ORDER BY d.download_count DESC
             LIMIT 10"
        );

        // Top uploaders
        $topUploaders = $db->fetchAll(
            "SELECT u.id, u.full_name, u.username,
                    COUNT(d.id) AS doc_count,
                    COALESCE(SUM(d.download_count), 0) AS total_downloads
             FROM users u
             JOIN documents d ON d.user_id = u.id AND d.status = 'approved' AND d.deleted_at IS NULL
             GROUP BY u.id
             ORDER BY doc_count DESC
             LIMIT 10"
        );

        // Documents by faculty
        $byFaculty = $db->fetchAll(
            "SELECT f.name AS faculty_name, COUNT(d.id) AS doc_count
             FROM faculties f
             LEFT JOIN subjects s ON s.faculty_id = f.id
             LEFT JOIN documents d ON d.subject_id = s.id AND d.status = 'approved' AND d.deleted_at IS NULL
             GROUP BY f.id
             ORDER BY doc_count DESC"
        );

        // Monthly uploads (last 12 months)
        $monthlyUploads = $db->fetchAll(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, COUNT(*) AS count
             FROM documents
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH) AND deleted_at IS NULL
             GROUP BY month
             ORDER BY month ASC"
        );

        $userModel     = new User();
        $documentModel = new Document();
        $facultyModel  = new Faculty();
        $subjectModel  = new Subject();
        $histModel     = new DownloadHistory();

        $this->view('admin.statistics.index', [
            'title'          => 'Thống kê hệ thống — Admin TMU',
            'userStats'      => $userModel->getStats(),
            'documentStats'  => $documentModel->getStats(),
            'totalFaculties' => $facultyModel->count(['is_active' => 1]),
            'totalSubjects'  => $subjectModel->count(['is_active' => 1]),
            'totalDownloads' => $histModel->getTotalDownloads(),
            'topDownloads'   => $topDownloads,
            'topUploaders'   => $topUploaders,
            'byFaculty'      => $byFaculty,
            'monthlyUploads' => $monthlyUploads,
        ], 'admin');
    }
}

