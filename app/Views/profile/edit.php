<?php declare(strict_types=1); ?>
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="mb-3">
                <a href="<?= url('/profile') ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Quay lại hồ sơ
                </a>
            </div>

            <!-- Edit Profile Info -->
            <div class="card border-0 shadow-sm p-4 mb-4">
                <h4 class="fw-bold mb-3"><i class="bi bi-person-gear me-2"></i>Cập nhật thông tin cá nhân</h4>
                <form action="<?= url('/profile/update') ?>" method="POST" enctype="multipart/form-data">
                    <?= csrfField() ?>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Họ và tên <span class="text-danger">*</span></label>
                        <input type="text" name="full_name" class="form-control"
                               value="<?= old('full_name', $user['full_name']) ?>" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Tên đăng nhập</label>
                            <input type="text" class="form-control" value="<?= e($user['username']) ?>" disabled>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" class="form-control" value="<?= e($user['email']) ?>" disabled>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Mã sinh viên <?= ($user['role'] ?? 'member') === 'member' ? '<span class="text-danger">*</span>' : '' ?>
                        </label>
                        <input type="text" name="student_id" class="form-control text-uppercase"
                               value="<?= old('student_id', $user['student_id'] ?? '') ?>"
                               placeholder="Ví dụ: 21D123456" maxlength="9"
                               <?= ($user['role'] ?? 'member') === 'member' ? 'required' : '' ?>>
                        <div class="form-text">Định dạng chuẩn TMU (VD: 21D123456)</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Ảnh đại diện</label>
                        <?php $currentAvatar = avatarUrl((int)$user['id'], $user['avatar'] ?? null); ?>
                        <?php if ($currentAvatar): ?>
                            <div class="mb-2">
                                <img src="<?= e($currentAvatar) ?>" alt="Avatar hiện tại"
                                     class="rounded-circle border" width="64" height="64" style="object-fit: cover;">
                            </div>
                        <?php endif; ?>
                        <input type="file" name="avatar" class="form-control" accept="image/*">
                        <div class="form-text">JPG, PNG, GIF, WEBP tối đa 2MB.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tiểu sử</label>
                        <textarea name="bio" class="form-control" rows="3" maxlength="1000"
                                  placeholder="Giới thiệu đôi nét về bạn..."><?= old('bio', $user['bio'] ?? '') ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary fw-bold">
                        <i class="bi bi-check-circle me-1"></i>Lưu thông tin
                    </button>
                </form>
            </div>

            <!-- Change Password -->
            <div class="card border-0 shadow-sm p-4">
                <h4 class="fw-bold mb-3"><i class="bi bi-shield-lock me-2"></i>Đổi mật khẩu</h4>
                <form id="changePasswordForm" action="<?= url('/profile/change-password') ?>" method="POST">
                    <?= csrfField() ?>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Mật khẩu hiện tại <span class="text-danger">*</span></label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Mật khẩu mới <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" id="new_password" name="new_password" class="form-control"
                                   required minlength="8" placeholder="Mật khẩu bảo mật">
                            <button class="btn btn-outline-secondary" type="button" id="toggleNewPwd">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div class="form-text">Ít nhất 8 ký tự, gồm chữ cái, chữ số và ký tự đặc biệt (!@#$%...)</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Xác nhận mật khẩu mới <span class="text-danger">*</span></label>
                        <input type="password" name="new_password_confirmation" class="form-control" required placeholder="Nhập lại mật khẩu mới">
                    </div>
                    <button type="submit" class="btn btn-warning fw-bold">
                        <i class="bi bi-key me-1"></i>Đổi mật khẩu
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('toggleNewPwd')?.addEventListener('click', function() {
    const pwd = document.getElementById('new_password');
    const icon = this.querySelector('i');
    pwd.type = pwd.type === 'password' ? 'text' : 'password';
    icon.className = pwd.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
});
</script>
<?php include __DIR__ . '/../components/otp_modal.php'; ?>
<script>
document.getElementById('changePasswordForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = Object.fromEntries(new FormData(this).entries());
    OtpManager.open({
        sendUrl: '<?= url('/change-password/send-otp') ?>',
        verifyUrl: '<?= url('/change-password/verify-otp') ?>',
        payload: formData
    });
});
</script>
