<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Config;
use App\Core\Controller;
use App\Models\User;
use App\Services\AuthService;
use App\Services\MailService;
use App\Services\OtpService;

class AuthController extends Controller
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    public function loginForm(array $params = []): void
    {
        if (auth()) {
            $this->redirect('/');
        }
        $this->view('auth.login', [
            'title' => 'Đăng nhập — TMU Tài Liệu',
        ]);
    }

    public function login(array $params = []): void
    {
        if (auth()) {
            $this->redirect('/');
        }

        $this->csrfValidate();

        $email    = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';
        $remember = !empty($_POST['remember']);

        $errors = $this->validate(
            ['email' => $email, 'password' => $password],
            ['email' => 'required|email', 'password' => 'required']
        );

        if (!empty($errors)) {
            storeOldInput();
            flash('errors', serialize($errors));
            $this->redirect('/login');
        }

        $result = $this->authService->login($email, $password, $remember);

        if (!$result['success']) {
            storeOldInput();
            flash('error', $result['error']);
            $this->redirect('/login');
        }

        clearOldInput();
        $fullName = e(trim((string)($result['user']['full_name'] ?? '')));
        flash('success', 'Chào mừng bạn quay lại, ' . $fullName . '!');
        $this->redirect('/');
    }

    public function registerForm(array $params = []): void
    {
        if (auth()) {
            $this->redirect('/');
        }
        $this->view('auth.register', [
            'title' => 'Đăng ký tài khoản — TMU Tài Liệu',
        ]);
    }

    public function register(array $params = []): void
    {
        if (auth()) {
            $this->redirect('/');
        }

        $this->csrfValidate();

        // 1. Chuẩn hóa dữ liệu đầu vào
        $data = [
            'username'              => trim($_POST['username'] ?? ''),
            'email'                 => strtolower(trim($_POST['email'] ?? '')),
            'full_name'             => trim($_POST['full_name'] ?? ''),
            'password'              => $_POST['password'] ?? '',
            'password_confirmation' => $_POST['password_confirmation'] ?? '',
            'student_id'            => strtoupper(trim($_POST['student_id'] ?? '')),
        ];

        // 2. Validate bằng bộ quy tắc trong Validator.php
        $errors = $this->validate($data, [
            'full_name'  => 'required|min:2|max:100|vietnamese_name',
            'student_id' => 'required|tmu_student_id',
            'email'      => 'required|email|tmu_student_email',
            'username'   => 'required|custom_username',
            'password'   => 'required|password_rules|confirmed',
        ]);

        $userModel = new User();

        // 3. Kiểm tra chống trùng lặp dữ liệu trong CSDL
        if (empty($errors['student_id']) && $userModel->studentIdExists($data['student_id'])) {
            $errors['student_id'][] = 'Mã sinh viên này đã được đăng ký trên hệ thống.';
        }
        if (empty($errors['username']) && $userModel->usernameExists($data['username'])) {
            $errors['username'][] = 'Tên đăng nhập này đã được sử dụng.';
        }
        if (empty($errors['email']) && $userModel->emailExists($data['email'])) {
            $errors['email'][] = 'Email này đã được sử dụng.';
        }

        // 4. Trả về lỗi nếu có
        if (!empty($errors)) {
            storeOldInput();
            $_SESSION['old_input']['student_id'] = $data['student_id'];
            $_SESSION['old_input']['email']      = $data['email'];
            $_SESSION['old_input']['username']   = $data['username'];
            $_SESSION['old_input']['full_name']  = $data['full_name'];
            flash('errors', serialize($errors));
            $this->redirect('/register');
        }

        // 5. Tạo tài khoản và đăng nhập ngay
        $result = $this->authService->register($data);

        if (!$result['success']) {
            storeOldInput();
            flash('errors', serialize($result['errors']));
            $this->redirect('/register');
        }

        clearOldInput();
        flash('success', 'Đăng ký thành công! Chào mừng bạn đến với TMU Tài Liệu.');
        $this->redirect('/');
    }

    public function logout(array $params = []): void
    {
        $this->csrfValidate();
        $this->authService->logout();
        flash('success', 'Bạn đã đăng xuất thành công.');
        $this->redirect('/login');
    }

    public function forgotForm(array $params = []): void
    {
        $this->view('auth.forgot_password', ['title' => 'Quên mật khẩu — TMU Tài Liệu']);
    }

    public function forgot(array $params = []): void
    {
        $this->csrfValidate();
        $email = strtolower(trim($_POST['email'] ?? ''));

        $errors = $this->validate(['email' => $email], ['email' => 'required|email']);
        if (!empty($errors)) {
            storeOldInput();
            flash('errors', serialize($errors));
            $this->redirect('/forgot-password');
        }

        $rawToken = $this->authService->sendPasswordReset($email);

        if (empty($rawToken)) {
            storeOldInput();
            flash('error', 'Địa chỉ email này không tồn tại trong hệ thống. Vui lòng kiểm tra lại!');
            $this->redirect('/forgot-password');
        }

        clearOldInput();
        $resetLink = url('/reset-password/' . $rawToken);
        flash('success', 'Link đặt lại mật khẩu (hiệu lực 1 giờ): <a href="' .
            $resetLink . '" class="fw-bold text-decoration-underline">' . $resetLink . '</a>');

        $this->redirect('/forgot-password');
    }

    public function resetForm(array $params = []): void
    {
        $token = $params['token'] ?? '';
        $this->view('auth.reset_password', [
            'title' => 'Đặt lại mật khẩu — TMU Tài Liệu',
            'token' => $token,
        ]);
    }

    public function reset(array $params = []): void
    {
        $this->csrfValidate();
        $token    = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $errors   = $this->validate(
            ['password' => $password, 'password_confirmation' => $_POST['password_confirmation'] ?? ''],
            ['password' => 'required|password_rules|confirmed']
        );

        if (!empty($errors)) {
            storeOldInput();
            flash('errors', serialize($errors));
            $this->redirect('/reset-password/' . urlencode($token));
        }

        $success = $this->authService->resetPassword($token, $password);
        if (!$success) {
            flash('error', 'Link đặt lại mật khẩu không hợp lệ hoặc đã hết hạn.');
            $this->redirect('/forgot-password');
        }

        flash('success', 'Mật khẩu đã được đặt lại thành công. Vui lòng đăng nhập.');
        $this->redirect('/login');
    }
    private function jsonResponse(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function getJsonOrPost(): array
    {
        $raw = json_decode(file_get_contents('php://input'), true);
        return is_array($raw) ? $raw : $_POST;
    }

    // --- 1. ĐĂNG KÝ VỚI OTP ---
   public function registerSendOtp(): void
    {
        $data            = $this->getJsonOrPost();
        $fullName        = trim($data['full_name'] ?? '');
        $username        = trim($data['username'] ?? '');
        $studentId       = strtoupper(trim($data['student_id'] ?? ''));
        $email           = trim($data['email'] ?? '');
        $password        = $data['password'] ?? '';
        $passwordConfirm = $data['password_confirmation'] ?? '';

        if (!$fullName || !$username || !$studentId || !$email || !$password) {
            $this->jsonResponse(['success' => false, 'message' => 'Vui lòng nhập đầy đủ tất cả các trường bắt buộc.']);
        }
        if (strlen($password) < 8) {
            $this->jsonResponse(['success' => false, 'message' => 'Mật khẩu phải có ít nhất 8 ký tự.']);
        }
        if ($password !== $passwordConfirm) {
            $this->jsonResponse(['success' => false, 'message' => 'Xác nhận mật khẩu không khớp.']);
        }
        if (!str_ends_with(strtolower($email), '@tmu.edu.vn')) {
            $this->jsonResponse(['success' => false, 'message' => 'Chỉ chấp nhận email sinh viên @tmu.edu.vn.']);
        }
        if ($this->userModel->findByEmail($email)) {
            $this->jsonResponse(['success' => false, 'message' => 'Email này đã tồn tại trên hệ thống.']);
        }

        $otpService = new OtpService();
        $gen = $otpService->generate('register', [
            'full_name'     => $fullName,
            'username'      => $username,
            'student_id'    => $studentId,
            'email'         => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        ]);
        if (!$gen['success']) {
            $this->jsonResponse($gen);
        }

        $mailService = new MailService();
        if (!$mailService->sendOtpEmail($email, $fullName, $gen['otp'], 'Xác thực đăng ký')) {
            $this->jsonResponse(['success' => false, 'message' => 'Gửi email OTP thất bại.']);
        }

        $this->jsonResponse(['success' => true, 'expires_in' => $gen['expires_in'], 'message' => "Mã OTP đã gửi tới {$email}."]);
    }

    public function registerVerifyOtp(): void
    {
        $data = $this->getJsonOrPost();
        $otpService = new OtpService();
        $verify = $otpService->verify('register', $data['otp'] ?? '');

        if (!$verify['success']) {
            $this->jsonResponse($verify);
        }

        $payload = $verify['payload'];
        $payload['status'] = 'active';
        $payload['role']   = 'member';
        $payload['email_verified_at'] = date('Y-m-d H:i:s');

        if (!$this->userModel->create($payload)) {
            $this->jsonResponse(['success' => false, 'message' => 'Không thể lưu tài khoản vào CSDL.']);
        }

        $this->jsonResponse(['success' => true, 'message' => 'Đăng ký thành công!', 'redirect_url' => '/login']);
    }

    // --- 2. ĐỔI MẬT KHẨU VỚI OTP ---
    public function changePasswordSendOtp(): void
    {
        if (empty($_SESSION['user']['id'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Vui lòng đăng nhập lại.'], 401);
        }

        $data = $this->getJsonOrPost();
        $currentPass = $data['current_password'] ?? '';
        $newPass = $data['new_password'] ?? '';
        $confirm = $data['new_password_confirmation'] ?? '';

        if (strlen($newPass) < 6 || $newPass !== $confirm) {
            $this->jsonResponse(['success' => false, 'message' => 'Mật khẩu mới tối thiểu 6 ký tự và phải khớp nhau.']);
        }

        $user = $this->userModel->findById((int)$_SESSION['user']['id']);
        $otpService = new OtpService();
        $gen = $otpService->generate('change_password', [
            'user_id'       => $user['id'],
            'password_hash' => password_hash($newPass, PASSWORD_DEFAULT)
        ]);
        if (!$gen['success']) {
            $this->jsonResponse($gen);
        }

        $mailService = new MailService();
        if (!$mailService->sendOtpEmail($user['email'], $user['full_name'], $gen['otp'], 'Xác nhận đổi mật khẩu')) {
            $this->jsonResponse(['success' => false, 'message' => 'Gửi email OTP thất bại.']);
        }

        $this->jsonResponse(['success' => true, 'expires_in' => $gen['expires_in'], 'message' => "Mã OTP đã gửi tới {$user['email']}."]);
    }

    public function changePasswordVerifyOtp(): void
    {
        $data = $this->getJsonOrPost();
        $otpService = new OtpService();
        $verify = $otpService->verify('change_password', $data['otp'] ?? '');

        if (!$verify['success']) {
            $this->jsonResponse($verify);
        }

        $payload = $verify['payload'];
        $this->userModel->updatePassword((int)$payload['user_id'], $payload['password_hash']);

        $this->jsonResponse(['success' => true, 'message' => 'Đổi mật khẩu thành công!']);
    }

    // --- 3. QUÊN MẬT KHẨU (LINK EMAIL) ---
    public function showForgotPassword(): void
    {
        require __DIR__ . '/../../views/auth/forgot_password.php';
    }

    public function sendResetLink(): void
    {
        $email = trim($_POST['email'] ?? '');
        $user  = $this->userModel->findByEmail($email);

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', time() + 900); // Hạn 15 phút
            $this->userModel->saveResetToken((int)$user['id'], $token, $expiresAt);

            $protocol  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $resetLink = "{$protocol}://{$_SERVER['HTTP_HOST']}/reset-password?token={$token}";

            $mailService = new MailService();
            $mailService->sendResetLinkEmail($user['email'], $user['full_name'], $resetLink);
        }

        $_SESSION['flash_success'] = 'Nếu email tồn tại, liên kết đặt lại mật khẩu đã được gửi vào hộp thư của bạn.';
        header('Location: /forgot-password');
        exit;
    }

    public function showResetPassword(): void
    {
        $token = $_GET['token'] ?? '';
        if (!$this->userModel->findByValidResetToken($token)) {
            $_SESSION['flash_error'] = 'Liên kết không hợp lệ hoặc đã hết hạn.';
            header('Location: /forgot-password');
            exit;
        }
        require __DIR__ . '/../../views/auth/reset_password.php';
    }

    public function handleResetPassword(): void
    {
        $token   = $_POST['token'] ?? '';
        $pass    = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        $user = $this->userModel->findByValidResetToken($token);
        if (!$user) {
            $_SESSION['flash_error'] = 'Liên kết đã hết hạn.';
            header('Location: /forgot-password');
            exit;
        }
        if (strlen($pass) < 6 || $pass !== $confirm) {
            $_SESSION['flash_error'] = 'Mật khẩu tối thiểu 6 ký tự và phải khớp nhau.';
            header("Location: /reset-password?token={$token}");
            exit;
        }

        $this->userModel->updatePassword((int)$user['id'], password_hash($pass, PASSWORD_DEFAULT));
        $_SESSION['flash_success'] = 'Đặt lại mật khẩu thành công. Hãy đăng nhập lại.';
        header('Location: /login');
        exit;
    }
}
