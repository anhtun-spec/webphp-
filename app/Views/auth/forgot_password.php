<div style="max-width:420px; margin:40px auto; padding:24px; border:1px solid #ddd; border-radius:8px;">
    <h3>Quên mật khẩu</h3>
    <?php if (!empty($_SESSION['flash_error'])): ?>
        <p style="color:#D32F2F;"><?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></p>
    <?php endif; ?>
    <?php if (!empty($_SESSION['flash_success'])): ?>
        <p style="color:#2E7D32;"><?= htmlspecialchars($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?></p>
    <?php endif; ?>

    <form action="/forgot-password" method="POST">
        <label>Email tài khoản (@tmu.edu.vn)</label><br>
        <input type="email" name="email" required style="width:100%; padding:10px; margin:8px 0 14px;">
        <button type="submit" style="width:100%; padding:10px; background:#1565C0; color:#fff; border:none; border-radius:6px; cursor:pointer;">
            Gửi liên kết đặt lại mật khẩu
        </button>
    </form>
</div>
