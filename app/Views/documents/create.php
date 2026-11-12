<?php declare(strict_types=1); ?>
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm p-4">
                <div class="d-flex align-items-center gap-3 mb-4 border-bottom pb-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3 fs-3">
                        <i class="bi bi-cloud-arrow-up"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-0">Đóng góp tài liệu học tập</h3>
                        <p class="text-muted small mb-0">Chia sẻ kiến thức cùng sinh viên Trường Đại học Thương mại</p>
                    </div>
                </div>

                <form action="<?= url('/documents') ?>" method="POST" enctype="multipart/form-data" novalidate>
                    <?= csrfField() ?>

                    <div class="mb-3">
                        <label for="title" class="form-label fw-semibold">
                            Tên tài liệu <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="title" name="title"
                               value="<?= old('title') ?>"
                               placeholder="Ví dụ: Slide bài giảng Thương mại điện tử căn bản - Chương 1" required minlength="5" maxlength="500">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="faculty_id" class="form-label fw-semibold">
                                Khoa / Viện
                            </label>
                            <select class="form-select" id="faculty_id" name="faculty_id">
                                <option value="">-- Chọn Khoa / Viện để lọc môn học --</option>
                                <?php foreach ($faculties as $f): ?>
                                    <option value="<?= $f['id'] ?>" <?= old('faculty_id') == $f['id'] ? 'selected' : '' ?>><?= e($f['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="subject_id" class="form-label fw-semibold">
                                Môn học <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="subject_id" name="subject_id" required>
                                <option value="">-- Chọn môn học --</option>
                                <?php foreach ($subjects as $s): ?>
                                    <option value="<?= $s['id'] ?>" data-faculty="<?= $s['faculty_id'] ?>" <?= old('subject_id') == $s['id'] ? 'selected' : '' ?>>
                                        <?= e($s['name']) ?><?= !empty($s['code']) ? ' (' . e($s['code']) . ')' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="mb-3">
                            <label for="document_type_id" class="form-label fw-semibold">
                                Loại tài liệu <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="document_type_id" name="document_type_id" required>
                                <option value="">-- Chọn loại tài liệu --</option>
                                <?php foreach ($types as $t): ?>
                                    <option value="<?= $t['id'] ?>" <?= old('document_type_id') == $t['id'] ? 'selected' : '' ?>><?= e($t['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="document_file" class="form-label fw-semibold">
                            Tệp tài liệu <span class="text-danger">*</span>
                        </label>
                        <input type="file" class="form-control" id="document_file" name="document_file" required>
                        <div class="form-text">
                            Hỗ trợ: <strong>PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX, TXT, ZIP, RAR</strong>. Dung lượng tối đa: <strong>50MB</strong>.
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="description" class="form-label fw-semibold">Mô tả tóm tắt nội dung</label>
                        <textarea class="form-control" id="description" name="description" rows="4"
                                  placeholder="Mô tả nội dung, đề cương môn học, tài liệu gồm những chương nào..."><?= old('description') ?></textarea>
                    </div>

                    <div class="alert alert-warning small mb-4">
                        <i class="bi bi-shield-exclamation me-1"></i>
                        <strong>Lưu ý kiểm duyệt:</strong> Tài liệu sau khi đăng tải sẽ ở trạng thái <em>Chờ duyệt</em>. Kiểm duyệt viên TMU sẽ xem xét trước khi tài liệu xuất hiện công khai trên hệ thống.
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= url('/documents') ?>" class="btn btn-secondary">Hủy bỏ</a>
                        <button type="submit" class="btn btn-primary fw-bold px-4">
                            <i class="bi bi-check-circle me-1"></i>Gửi tài liệu
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const allSubjectOptions = <?= json_encode(array_map(function($s) {
    return [
        'id' => $s['id'],
        'faculty_id' => $s['faculty_id'],
        'name' => $s['name'] . (!empty($s['code']) ? ' (' . $s['code'] . ')' : '')
    ];
}, $subjects), JSON_UNESCAPED_UNICODE) ?>;

const facultySelect = document.getElementById('faculty_id');
const subjectSelect = document.getElementById('subject_id');
const oldSubjectId  = "<?= old('subject_id') ?>";

function renderSubjects(selectedFacultyId) {
    const currentVal = subjectSelect.value || oldSubjectId;
    subjectSelect.innerHTML = '<option value="">-- Chọn môn học --</option>';
    
    const filtered = selectedFacultyId 
        ? allSubjectOptions.filter(s => s.faculty_id == selectedFacultyId)
        : allSubjectOptions;
        
    filtered.forEach(s => {
        const opt = document.createElement('option');
        opt.value = s.id;
        opt.textContent = s.name;
        if (s.id == currentVal) {
            opt.selected = true;
        }
        subjectSelect.appendChild(opt);
    });
}

facultySelect?.addEventListener('change', function() {
    renderSubjects(this.value);
});

// If faculty is already chosen or old input exists
if (facultySelect && facultySelect.value) {
    renderSubjects(facultySelect.value);
}
</script>

