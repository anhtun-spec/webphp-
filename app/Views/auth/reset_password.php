<?php declare(strict_types=1); ?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <i class="bi bi-shield-lock-fill text-success fs-1"></i>
                        <h2 class="fw-bold mt-2">Đặt lại mật khẩu</h2>
                    </div>
                    <form action="<?= url('/reset-password') ?>" method="POST">
                        <?= csrfField() ?>
                        <input type="hidden" name="token" value="<?= e($token ?? '') ?>">
                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">Mật khẩu mới</label>
                            <input type="password" class="form-control" id="password" name="password"
                                   placeholder="Tối thiểu 8 ký tự" required minlength="8">
                            <div class="form-text">Bao gồm chữ cái, chữ số và ký tự đặc biệt (!@#$%...)</div>
                        </div>
                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label fw-semibold">Xác nhận mật khẩu mới</label>
                            <input type="password" class="form-control" id="password_confirmation"
                                   name="password_confirmation" placeholder="Nhập lại mật khẩu" required>
                        </div>
                        <button type="submit" class="btn btn-success w-100 fw-semibold py-2">
                            <i class="bi bi-check-circle me-2"></i>Xác nhận đặt lại mật khẩu
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>