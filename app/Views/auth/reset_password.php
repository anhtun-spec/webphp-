<div style="max-width:420px; margin:40px auto; padding:24px; border:1px solid #ddd; border-radius:8px;">
    <h3>Đặt lại mật khẩu mới</h3>
    <?php if (!empty($_SESSION['flash_error'])): ?>
        <p style="color:#D32F2F;"><?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></p>
    <?php endif; ?>

    <form action="/reset-password" method="POST">
        <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token'] ?? '') ?>">
        <label>Mật khẩu mới (tối thiểu 6 ký tự)</label><br>
        <input type="password" name="password" minlength="6" required style="width:100%; padding:10px; margin:8px 0 12px;">
        <label>Nhập lại mật khẩu mới</label><br>
        <input type="password" name="confirm_password" minlength="6" required style="width:100%; padding:10px; margin:8px 0 14px;">
        <button type="submit" style="width:100%; padding:10px; background:#1565C0; color:#fff; border:none; border-radius:6px; cursor:pointer;">
            Xác nhận đổi mật khẩu
        </button>
    </form>
</div>
