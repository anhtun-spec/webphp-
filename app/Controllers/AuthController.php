<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Config;
use App\Core\Controller;
use App\Models\User;
use App\Services\AuthService;

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
}