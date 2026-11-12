<?php declare(strict_types=1); ?>
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm p-4">
                <div class="d-flex align-items-center gap-3 mb-4 border-bottom pb-3">
                    <div class="rounded-circle bg-warning bg-opacity-10 text-warning p-3 fs-3">
                        <i class="bi bi-pencil-square"></i>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-0">Chỉnh sửa tài liệu</h3>
                        <p class="text-muted small mb-0">Cập nhật thông tin tài liệu đã đăng</p>
                    </div>
                </div>

                <form action="<?= url('/documents/' . $doc['id'] . '/update') ?>" method="POST" enctype="multipart/form-data" novalidate>
                    <?= csrfField() ?>
                    <?php
                    $valTitle   = old('title', $doc['title']);
                    $valFaculty = old('faculty_id', $doc['faculty_id'] ?? '');
                    $valSubject = old('subject_id', $doc['subject_id']);
                    $valType    = old('document_type_id', $doc['document_type_id']);
                    $valDesc    = old('description', $doc['description'] ?? '');
                    ?>

                    <div class="mb-3">
                        <label for="title" class="form-label fw-semibold">
                            Tên tài liệu <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="title" name="title"
                               value="<?= $valTitle ?>" required minlength="5" maxlength="500">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="faculty_id" class="form-label fw-semibold">Khoa / Viện</label>
                            <select class="form-select" id="faculty_id" name="faculty_id">
                                <option value="">-- Chọn Khoa / Viện để lọc môn học --</option>
                                <?php foreach ($faculties as $f): ?>
                                    <option value="<?= $f['id'] ?>" <?= $valFaculty == $f['id'] ? 'selected' : '' ?>><?= e($f['name']) ?></option>
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
                                    <option value="<?= $s['id'] ?>" data-faculty="<?= $s['faculty_id'] ?>" <?= $valSubject == $s['id'] ? 'selected' : '' ?>>
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
                                    <option value="<?= $t['id'] ?>" <?= $valType == $t['id'] ? 'selected' : '' ?>>
                                        <?= e($t['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Tệp tài liệu thay thế -->
                    <div class="mb-3">
                        <label for="document_file" class="form-label fw-semibold">
                            Thay thế tệp tài liệu <span class="text-muted fw-normal">(Không bắt buộc)</span>
                        </label>
                        <input type="file" class="form-control" id="document_file" name="document_file">
                        <div class="form-text">
                            Tệp hiện tại: <strong><?= e($doc['original_filename']) ?></strong> (<?= formatSize((int)$doc['file_size']) ?>). Chỉ chọn tệp mới nếu bạn muốn thay thế tệp này (Hỗ trợ PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX, TXT, ZIP, RAR tối đa 50MB).
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="description" class="form-label fw-semibold">Mô tả tóm tắt nội dung</label>
                        <textarea class="form-control" id="description" name="description" rows="4"><?= $valDesc ?></textarea>
                    </div>

                    <div class="alert alert-info small mb-4">
                        <i class="bi bi-info-circle me-1"></i> Sau khi lưu, tài liệu sẽ được chuyển về trạng thái <strong>Chờ duyệt</strong> để kiểm tra lại tính chính xác.
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= url('/documents/' . $doc['id']) ?>" class="btn btn-secondary">Quay lại</a>
                        <button type="submit" class="btn btn-primary fw-bold px-4">
                            <i class="bi bi-check-circle me-1"></i>Lưu thay đổi
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
const currentSubjectId = "<?= $valSubject ?>";

function renderSubjects(selectedFacultyId) {
    const currentVal = subjectSelect.value || currentSubjectId;
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

if (facultySelect && facultySelect.value) {
    renderSubjects(facultySelect.value);
}
</script>

