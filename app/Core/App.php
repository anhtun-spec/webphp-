<?php
declare(strict_types=1);

namespace App\Core;

class App
{
    private array $config;
    private Router $router;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->configure();
        $this->router = new Router();
        $this->loadRoutes();
    }

    private function configure(): void
    {
        // Timezone
        date_default_timezone_set($this->config['app']['timezone']);

        // Error reporting
        if ($this->config['app']['debug']) {
            error_reporting(E_ALL);
            ini_set('display_errors', '1');
        } else {
            error_reporting(0);
            ini_set('display_errors', '0');
        }

        // Store config globally
        Config::set($this->config);

        // Start session
        if (session_status() === PHP_SESSION_NONE) {
            $sessionConfig = $this->config['session'];
            session_name($sessionConfig['name']);
            session_set_cookie_params([
                'lifetime' => $sessionConfig['lifetime'],
                'path'     => '/',
                'secure'   => $sessionConfig['secure'],
                'httponly' => $sessionConfig['httponly'],
                'samesite' => 'Lax',
            ]);
            session_start();
        }
    }

    private function loadRoutes(): void
    {
        $routes = require ROOT_PATH . '/config/routes.php';
        foreach ($routes as [$method, $path, $handler]) {
            $this->router->add($method, $path, $handler);
        }
    }

    public function run(): void
    {
        // Kiểm tra trạng thái tài khoản theo thời gian thực nếu đang đăng nhập
        if (!empty($_SESSION['user_id'])) {
            $freshUser = (new \App\Models\User())->findById((int)$_SESSION['user_id']);
            if (!$freshUser || ($freshUser['status'] ?? '') === 'locked') {
                (new \App\Services\AuthService())->logout();
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                flash('error', 'Tài khoản của bạn đã bị khóa hoặc không còn tồn tại.');
                header('Location: ' . url('/login'));
                exit;
            }

            // Đồng bộ vai trò và thông tin cơ bản khi Admin vừa thay đổi
            $_SESSION['user']['role']      = $freshUser['role'];
            $_SESSION['user']['status']    = $freshUser['status'];
            $_SESSION['user']['full_name'] = $freshUser['full_name'];
            $_SESSION['user']['avatar']    = $freshUser['avatar'];
        }

        $method = $_SERVER['REQUEST_METHOD'];
        // Support method override via POST field _method
        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }

        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
        $uri = rawurldecode($uri);
        // Strip base path if running in subdirectory
        $basePath = rawurldecode(rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/'));
        if ($basePath !== '' && str_starts_with($uri, $basePath)) {
            $uri = substr($uri, strlen($basePath));
        }
        $uri = '/' . ltrim($uri, '/');

        $this->router->dispatch($method, $uri);
    }
}