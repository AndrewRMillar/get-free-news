<?php

declare(strict_types=1);

namespace Infrastructure\Security;

class CsrfGuard
{
    public function check(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        $headers = getallheaders();
        $csrfHeader = $headers['X-CSRF-Token'] ?? '';

        if (!isset($_SESSION['csrf_token']) || $csrfHeader !== $_SESSION['csrf_token']) {
            $this->abort();
        }
    }

    private function abort(): void
    {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode([
            'errors' => [['message' => 'Invalid CSRF token']]
        ]);
        exit;
    }
}
