<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;

class ErrorController extends Controller
{
    public function notFound(array $params = []): void
    {
        http_response_code(404);
        $this->view('errors.404', ['title' => '404 - Không tìm thấy trang'], 'main');
    }

    public function forbidden(array $params = []): void
    {
        http_response_code(403);
        $this->view('errors.403', ['title' => '403 - Truy cập bị từ chối'], 'main');
    }
}
