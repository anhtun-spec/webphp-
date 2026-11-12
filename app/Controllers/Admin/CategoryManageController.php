<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\DocumentType;
use App\Models\Faculty;
use App\Models\Subject;
use App\Models\University;

class CategoryManageController extends Controller
{
    public function index(array $params = []): void
    {
        $this->requireRole('admin', 'moderator');

        $facultyModel = new Faculty();
        $subjectModel = new Subject();
        $typeModel    = new DocumentType();

        $this->view('admin.categories.index', [
            'title'     => 'Quản lý Danh mục — Admin TMU',
            'faculties' => $facultyModel->getAllForAdmin(),
            'subjects'  => $subjectModel->getAllForAdmin(),
            'types'     => $typeModel->getAllForAdmin(),
        ], 'admin');
    }

    // ============================================================
    // KHOA / VIỆN (FACULTIES)
    // ============================================================

    public function storeFaculty(array $params = []): void
    {
        $this->requireRole('admin');
        $this->csrfValidate();

        $name         = trim($_POST['name'] ?? '');
        $shortName    = strtoupper(trim($_POST['short_name'] ?? ''));
        $sortOrder    = (int)($_POST['sort_order'] ?? 0);
        $universityId = (int)($_POST['university_id'] ?? 1);

        if ($name === '' || mb_strlen($name) > 255) {
            flash('error', 'Tên Khoa / Viện không được để trống (tối đa 255 ký tự).');
            $this->redirect('/admin/categories#faculties');
        }

        // Đảm bảo university_id hợp lệ
        $uniModel = new University();
        if (!$uniModel->findById($universityId)) {
            $unis = $uniModel->getActive();
            $universityId = !empty($unis) ? (int)$unis[0]['id'] : 1;
        }

        $facultyModel = new Faculty();
        $baseSlug     = slug($name) ?: ('khoa-' . time());
        $finalSlug    = $baseSlug;
        $suffix       = 2;
        while ($facultyModel->slugExists($finalSlug, $universityId)) {
            $finalSlug = $baseSlug . '-' . $suffix++;
        }

        $facultyModel->create([
            'university_id' => $universityId,
            'name'          => $name,
            'short_name'    => $shortName !== '' ? mb_substr($shortName, 0, 50) : null,
            'slug'          => $finalSlug,
            'is_active'     => 1,
            'sort_order'    => $sortOrder,
        ]);

        flash('success', 'Đã thêm Khoa / Viện mới thành công.');
        $this->redirect('/admin/categories#faculties');
    }

    public function updateFaculty(array $params = []): void
    {
        $this->requireRole('admin');
        $this->csrfValidate();

        $id           = (int)($params['id'] ?? 0);
        $facultyModel = new Faculty();
        $faculty      = $facultyModel->findById($id);

        if (!$faculty) {
            flash('error', 'Khoa / Viện không tồn tại.');
            $this->redirect('/admin/categories#faculties');
        }

        $name      = trim($_POST['name'] ?? '');
        $shortName = strtoupper(trim($_POST['short_name'] ?? ''));
        $sortOrder = (int)($_POST['sort_order'] ?? 0);
        $isActive  = isset($_POST['is_active']) ? 1 : 0;

        if ($name === '' || mb_strlen($name) > 255) {
            flash('error', 'Tên Khoa / Viện không hợp lệ.');
            $this->redirect('/admin/categories#faculties');
        }

        $universityId = (int)($faculty['university_id'] ?? 1);
        $baseSlug     = slug($name) ?: ('khoa-' . $id);
        $finalSlug    = $baseSlug;
        $suffix       = 2;
        while ($facultyModel->slugExists($finalSlug, $universityId, $id)) {
            $finalSlug = $baseSlug . '-' . $suffix++;
        }

        $facultyModel->update($id, [
            'name'       => $name,
            'short_name' => $shortName !== '' ? mb_substr($shortName, 0, 50) : null,
            'slug'       => $finalSlug,
            'sort_order' => $sortOrder,
            'is_active'  => $isActive,
        ]);

        flash('success', 'Đã cập nhật thông tin Khoa / Viện.');
        $this->redirect('/admin/categories#faculties');
    }

    public function toggleFaculty(array $params = []): void
    {
        $this->requireRole('admin');
        $this->csrfValidate();

        $id           = (int)($params['id'] ?? 0);
        $facultyModel = new Faculty();
        $faculty      = $facultyModel->findById($id);

        if (!$faculty) {
            flash('error', 'Khoa / Viện không tồn tại.');
            $this->redirect('/admin/categories#faculties');
        }

        $newState = !empty($faculty['is_active']) ? 0 : 1;
        $facultyModel->update($id, ['is_active' => $newState]);

        flash('success', $newState ? 'Đã hiển thị Khoa / Viện.' : 'Đã ẩn Khoa / Viện.');
        $this->redirect('/admin/categories#faculties');
    }

    public function deleteFaculty(array $params = []): void
    {
        $this->requireRole('admin');
        $this->csrfValidate();

        $id           = (int)($params['id'] ?? 0);
        $facultyModel = new Faculty();

        try {
            $facultyModel->delete($id);
            flash('success', 'Đã xóa Khoa / Viện.');
        } catch (\Throwable $e) {
            flash('error', 'Không thể xóa Khoa / Viện này vì vẫn còn môn học trực thuộc.');
        }

        $this->redirect('/admin/categories#faculties');
    }

    // ============================================================
    // MÔN HỌC (SUBJECTS)
    // ============================================================

    public function storeSubject(array $params = []): void
    {
        $this->requireRole('admin');
        $this->csrfValidate();

        $facultyId   = (int)($_POST['faculty_id'] ?? 0);
        $name        = trim($_POST['name'] ?? '');
        $code        = strtoupper(trim($_POST['code'] ?? ''));
        $sortOrder   = (int)($_POST['sort_order'] ?? 0);
        $description = trim($_POST['description'] ?? '');

        if ($facultyId <= 0 || !(new Faculty())->findById($facultyId)) {
            flash('error', 'Vui lòng chọn Khoa / Viện hợp lệ.');
            $this->redirect('/admin/categories#subjects');
        }

        if ($name === '' || mb_strlen($name) > 255) {
            flash('error', 'Tên môn học không được để trống (tối đa 255 ký tự).');
            $this->redirect('/admin/categories#subjects');
        }

        $subjectModel = new Subject();
        $baseSlug     = slug($name) ?: ('mon-hoc-' . time());
        $finalSlug    = $baseSlug;
        $suffix       = 2;
        while ($subjectModel->getBySlug($facultyId, $finalSlug) !== null) {
            $finalSlug = $baseSlug . '-' . $suffix++;
        }

        $subjectModel->create([
            'faculty_id'  => $facultyId,
            'name'        => $name,
            'code'        => $code !== '' ? mb_substr($code, 0, 20) : null,
            'slug'        => $finalSlug,
            'description' => $description !== '' ? $description : null,
            'is_active'   => 1,
            'sort_order'  => $sortOrder,
        ]);

        flash('success', 'Đã thêm môn học mới thành công.');
        $this->redirect('/admin/categories#subjects');
    }

    public function updateSubject(array $params = []): void
    {
        $this->requireRole('admin');
        $this->csrfValidate();

        $id           = (int)($params['id'] ?? 0);
        $subjectModel = new Subject();
        $subject      = $subjectModel->findById($id);

        if (!$subject) {
            flash('error', 'Môn học không tồn tại.');
            $this->redirect('/admin/categories#subjects');
        }

        $facultyId   = (int)($_POST['faculty_id'] ?? 0);
        $name        = trim($_POST['name'] ?? '');
        $code        = strtoupper(trim($_POST['code'] ?? ''));
        $sortOrder   = (int)($_POST['sort_order'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $isActive    = isset($_POST['is_active']) ? 1 : 0;

        if ($facultyId <= 0 || !(new Faculty())->findById($facultyId)) {
            flash('error', 'Vui lòng chọn Khoa / Viện hợp lệ.');
            $this->redirect('/admin/categories#subjects');
        }

        if ($name === '' || mb_strlen($name) > 255) {
            flash('error', 'Tên môn học không hợp lệ.');
            $this->redirect('/admin/categories#subjects');
        }

        $baseSlug  = slug($name) ?: ('mon-hoc-' . $id);
        $finalSlug = $baseSlug;
        $suffix    = 2;
        while (($existing = $subjectModel->getBySlug($facultyId, $finalSlug)) !== null && (int)$existing['id'] !== $id) {
            $finalSlug = $baseSlug . '-' . $suffix++;
        }

        $subjectModel->update($id, [
            'faculty_id'  => $facultyId,
            'name'        => $name,
            'code'        => $code !== '' ? mb_substr($code, 0, 20) : null,
            'slug'        => $finalSlug,
            'description' => $description !== '' ? $description : null,
            'sort_order'  => $sortOrder,
            'is_active'   => $isActive,
        ]);

        flash('success', 'Đã cập nhật thông tin môn học.');
        $this->redirect('/admin/categories#subjects');
    }

    public function toggleSubject(array $params = []): void
    {
        $this->requireRole('admin');
        $this->csrfValidate();

        $id           = (int)($params['id'] ?? 0);
        $subjectModel = new Subject();
        $subject      = $subjectModel->findById($id);

        if (!$subject) {
            flash('error', 'Môn học không tồn tại.');
            $this->redirect('/admin/categories#subjects');
        }

        $newState = !empty($subject['is_active']) ? 0 : 1;
        $subjectModel->update($id, ['is_active' => $newState]);

        flash('success', $newState ? 'Đã hiển thị môn học.' : 'Đã ẩn môn học.');
        $this->redirect('/admin/categories#subjects');
    }

    public function deleteSubject(array $params = []): void
    {
        $this->requireRole('admin');
        $this->csrfValidate();

        $id           = (int)($params['id'] ?? 0);
        $subjectModel = new Subject();

        try {
            $subjectModel->delete($id);
            flash('success', 'Đã xóa môn học.');
        } catch (\Throwable $e) {
            flash('error', 'Không thể xóa môn học này vì đang có tài liệu thuộc môn học.');
        }

        $this->redirect('/admin/categories#subjects');
    }

    // ============================================================
    // LOẠI TÀI LIỆU (DOCUMENT TYPES)
    // ============================================================

    public function storeType(array $params = []): void
    {
        $this->requireRole('admin');
        $this->csrfValidate();

        $name      = trim($_POST['name'] ?? '');
        $sortOrder = (int)($_POST['sort_order'] ?? 0);

        if ($name === '' || mb_strlen($name) > 100) {
            flash('error', 'Tên loại tài liệu không được để trống (tối đa 100 ký tự).');
            $this->redirect('/admin/categories#types');
        }

        $typeModel = new DocumentType();
        $baseSlug  = slug($name) ?: ('loai-' . time());
        $finalSlug = $baseSlug;
        $suffix    = 2;
        while (!empty($typeModel->findAll(['slug' => $finalSlug]))) {
            $finalSlug = $baseSlug . '-' . $suffix++;
        }

        $typeModel->create([
            'name'       => $name,
            'slug'       => $finalSlug,
            'is_active'  => 1,
            'sort_order' => $sortOrder,
        ]);

        flash('success', 'Đã thêm loại tài liệu mới thành công.');
        $this->redirect('/admin/categories#types');
    }

    public function updateType(array $params = []): void
    {
        $this->requireRole('admin');
        $this->csrfValidate();

        $id        = (int)($params['id'] ?? 0);
        $typeModel = new DocumentType();
        $type      = $typeModel->findById($id);

        if (!$type) {
            flash('error', 'Loại tài liệu không tồn tại.');
            $this->redirect('/admin/categories#types');
        }

        $name      = trim($_POST['name'] ?? '');
        $sortOrder = (int)($_POST['sort_order'] ?? 0);
        $isActive  = isset($_POST['is_active']) ? 1 : 0;

        if ($name === '' || mb_strlen($name) > 100) {
            flash('error', 'Tên loại tài liệu không hợp lệ.');
            $this->redirect('/admin/categories#types');
        }

        $baseSlug  = slug($name) ?: ('loai-' . $id);
        $finalSlug = $baseSlug;
        $suffix    = 2;
        while (true) {
            $found = $typeModel->findAll(['slug' => $finalSlug]);
            if (empty($found) || (int)$found[0]['id'] === $id) {
                break;
            }
            $finalSlug = $baseSlug . '-' . $suffix++;
        }

        $typeModel->update($id, [
            'name'       => $name,
            'slug'       => $finalSlug,
            'sort_order' => $sortOrder,
            'is_active'  => $isActive,
        ]);

        flash('success', 'Đã cập nhật loại tài liệu.');
        $this->redirect('/admin/categories#types');
    }

    public function toggleType(array $params = []): void
    {
        $this->requireRole('admin');
        $this->csrfValidate();

        $id        = (int)($params['id'] ?? 0);
        $typeModel = new DocumentType();
        $type      = $typeModel->findById($id);

        if (!$type) {
            flash('error', 'Loại tài liệu không tồn tại.');
            $this->redirect('/admin/categories#types');
        }

        $newState = !empty($type['is_active']) ? 0 : 1;
        $typeModel->update($id, ['is_active' => $newState]);

        flash('success', $newState ? 'Đã hiển thị loại tài liệu.' : 'Đã ẩn loại tài liệu.');
        $this->redirect('/admin/categories#types');
    }

    public function deleteType(array $params = []): void
    {
        $this->requireRole('admin');
        $this->csrfValidate();

        $id        = (int)($params['id'] ?? 0);
        $typeModel = new DocumentType();

        try {
            $typeModel->delete($id);
            flash('success', 'Đã xóa loại tài liệu.');
        } catch (\Throwable $e) {
            flash('error', 'Không thể xóa loại tài liệu này vì đang có tài liệu sử dụng.');
        }

        $this->redirect('/admin/categories#types');
    }
}