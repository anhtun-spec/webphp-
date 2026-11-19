<?php declare(strict_types=1); ?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <i class="bi bi-person-plus-fill text-primary fs-1"></i>
                        <h2 class="fw-bold mt-2">Đăng ký tài khoản</h2>
                        <p class="text-muted">Tham gia cộng đồng học tập sinh viên TMU</p>
                    </div>

                    <form id="registerForm" action="<?= url('/register') ?>" method="POST" novalidate>
                        <?= csrfField() ?>

                        <div class="mb-3">
                            <label for="full_name" class="form-label fw-semibold">
                                Họ và tên <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="full_name" name="full_name"
                                   value="<?= old('full_name') ?>" placeholder="Nguyễn Văn A" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="username" class="form-label fw-semibold">
                                    Tên đăng nhập <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="username" name="username"
                                       value="<?= old('username') ?>" placeholder="nguyenvana" required
                                       minlength="3" maxlength="50">
                                <div class="form-text">3-50 ký tự, bắt đầu bằng chữ cái</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="student_id" class="form-label fw-semibold">
                                    Mã sinh viên <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control text-uppercase" id="student_id" name="student_id"
                                       value="<?= old('student_id') ?>" placeholder="21D123456" maxlength="9" required>
                                <div class="form-text">Định dạng chuẩn TMU (VD: 21D123456)</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">
                                Email sinh viên TMU <span class="text-danger">*</span>
                            </label>
                            <input type="email" class="form-control" id="email" name="email"
                                   value="<?= old('email') ?>" placeholder="21d123456@mailsv.tmu.edu.vn" required>
                            <div class="form-text">Bắt buộc dùng email sinh viên TMU (@mailsv.tmu.edu.vn)</div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">
                                Mật khẩu <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" name="password"
                                       placeholder="Mật khẩu bảo mật" required minlength="8">
                                <button class="btn btn-outline-secondary" type="button" id="togglePwd">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div class="form-text">Ít nhất 8 ký tự, gồm chữ cái, chữ số và ký tự đặc biệt (!@#$%...)</div>
                        </div>

                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label fw-semibold">
                                Xác nhận mật khẩu <span class="text-danger">*</span>
                            </label>
                            <input type="password" class="form-control" id="password_confirmation"
                                   name="password_confirmation" placeholder="Nhập lại mật khẩu" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 fw-semibold py-2">
                            <i class="bi bi-person-check me-2"></i>Tạo tài khoản
                        </button>
                    </form>

                    <hr>
                    <p class="text-center mb-0">
                        Đã có tài khoản?
                        <a href="<?= url('/login') ?>" class="text-primary fw-semibold text-decoration-none">
                            Đăng nhập
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('togglePwd')?.addEventListener('click', function() {
    const pwd = document.getElementById('password');
    const icon = this.querySelector('i');
    pwd.type = pwd.type === 'password' ? 'text' : 'password';
    icon.className = pwd.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
});
</script>
<?php include __DIR__ . '/../components/otp_modal.php'; ?>
<script>
document.getElementById('registerForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = Object.fromEntries(new FormData(this).entries());
    OtpManager.open({
        sendUrl: '<?= url('/register/send-otp') ?>',
        verifyUrl: '<?= url('/register/verify-otp') ?>',
        payload: formData
    });
});
</script>
