<?php declare(strict_types=1); ?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <i class="bi bi-key-fill text-warning fs-1"></i>
                        <h2 class="fw-bold mt-2">Quên mật khẩu</h2>
                        <p class="text-muted">Nhập email để nhận hướng dẫn đặt lại mật khẩu</p>
                    </div>
                    <form action="<?= url('/forgot-password') ?>" method="POST">
                        <?= csrfField() ?>
                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">Email đăng ký</label>
                            <input type="email" class="form-control" id="email" name="email"
                                   value="<?= old('email') ?>" placeholder="nguyenvana@mailsv.tmu.edu.vn" required autofocus>
                        </div>
                        <button type="submit" class="btn btn-warning w-100 fw-semibold py-2">
                            <i class="bi bi-send me-2"></i>Gửi yêu cầu
                        </button>
                    </form>
                    <hr>
                    <p class="text-center mb-0">
                        <a href="<?= url('/login') ?>" class="text-primary text-decoration-none">
                            <i class="bi bi-arrow-left me-1"></i>Quay lại đăng nhập
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

